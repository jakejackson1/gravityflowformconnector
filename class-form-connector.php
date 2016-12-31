<?php
/**
 * Gravity Flow Form Connector
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Extension
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

// Make sure Gravity Forms is active and already loaded.
if ( class_exists( 'GFForms' ) ) {

	class Gravity_Flow_Form_Connector extends Gravity_Flow_Extension {

		private static $_instance = null;

		public $_version = GRAVITY_FLOW_FORM_CONNECTOR_VERSION;

		public $edd_item_name = GRAVITY_FLOW_FORM_CONNECTOR_EDD_ITEM_NAME;

		// The Framework will display an appropriate message on the plugins page if necessary
		protected $_min_gravityforms_version = '1.9.10';

		protected $_slug = 'gravityflowformconnector';

		protected $_path = 'gravityflowformconnector/formconnector.php';

		protected $_full_path = __FILE__;

		// Title of the plugin to be used on the settings page, form settings and plugins page.
		protected $_title = 'Form Connector Extension';

		// Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
		protected $_short_title = 'Form Connector';

		protected $_capabilities = array(
			'gravityflowformconnector_uninstall',
			'gravityflowformconnector_settings',
		);

		protected $_capabilities_app_settings = 'gravityflowformconnector_settings';
		protected $_capabilities_uninstall = 'gravityflowformconnector_uninstall';

		public static $form_submission_validation_error = '';

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Gravity_Flow_Form_Connector();
			}

			return self::$_instance;
		}

		private function __clone() {
		} /* do nothing */


		public function init() {
			parent::init();
			add_filter( 'gform_pre_render', array( $this, 'filter_gform_pre_render' ) );
			add_action( 'gform_after_submission', array( $this, 'action_gform_after_submission' ), 10, 2 );
			add_filter( 'gform_form_tag', array( $this, 'filter_gform_form_tag' ), 10, 2 );
			add_filter( 'gform_validation', array( $this, 'filter_gform_validation' ) );
			add_filter( 'gform_save_field_value', array( $this, 'filter_save_field_value' ), 10, 5 );
			add_filter( 'gform_pre_replace_merge_tags', array( $this, 'filter_gform_pre_replace_merge_tags' ), 10, 7 );
		}

		public function upgrade( $previous_version ) {
			if ( ! empty( $previous_version ) && version_compare( '1.0-beta-2', $previous_version, '<' ) ) {
				$this->upgrade_steps();
			}
		}

		public function upgrade_steps() {
			$forms = GFAPI::get_forms();
			foreach ( $forms as $form ) {
				$feeds = gravity_flow()->get_feeds( $form['id'] );
				foreach ( $feeds as $feed ) {
					if ( $feed['meta']['step_type'] == 'form_connector' ) {
						if ( $feed['meta']['action'] == 'create' ) {
							$feed['meta']['step_type'] = 'new_entry';
						} else {
							$feed['meta']['step_type'] = 'update_entry';
						}
						gravity_flow()->update_feed_meta( $feed['id'], $feed['meta'] );
					}
				}
			}
		}

		public function filter_gform_pre_render( $form ) {

			$parent_entry_id = absint( rgget( 'workflow_parent_entry_id' ) );

			if ( empty( $parent_entry_id ) ) {
				return $form;
			}

			$parent_entry = GFAPI::get_entry( $parent_entry_id );

			$api = new Gravity_Flow_API( $parent_entry['form_id'] );

			$parent_entry_current_step = $api->get_current_step( $parent_entry );

			if ( empty( $parent_entry_current_step ) ) {
				return $form;
			}

			if ( $parent_entry_current_step->get_type() != 'form_submission' ) {
				return $form;
			}

			$current_user_assignee_key = gravity_flow()->get_current_user_assignee_key();

			if ( $current_user_assignee_key == 'user_id|0' ) {
				return $form;
			}

			$assignee = new Gravity_Flow_Assignee( $current_user_assignee_key );

			if ( $assignee->get_type() == 'user_id' ) {
				$user_id = $assignee->get_id();
			} else {
				$user_id = 0;
			}
			$form = $this->prepopulate_form( $form, $parent_entry_current_step, $user_id );

			return $form;
		}

		/**
		 * Set up dynamic population to map the default values from the parent entry.
		 *
		 * @param $form
		 * @param Gravity_Flow_Step_Form_Submission $parent_entry_current_step
		 * @param bool $user_id
		 *
		 * @return mixed
		 */
		public function prepopulate_form( $form, $parent_entry_current_step, $user_id = false ) {
			$parent_entry = $parent_entry_current_step->get_entry();
			$parent_form = GFAPI::get_form( $parent_entry['form_id'] );
			$mapped_fields = $parent_entry_current_step->do_mapping( $parent_form, $parent_entry );

			$mapped_field_ids = array_map( 'intval', array_keys( $mapped_fields ) );

			foreach ( $form['fields'] as &$field ) {

				if ( ! in_array( $field->id, $mapped_field_ids ) ) {
					continue;
				}

				$value = false;

				switch ( $field->get_input_type() ) {

					case 'checkbox':

						$value = rgar( $mapped_fields, $field->id );

						if ( empty( $value ) ) {
							foreach ( $field->inputs as $input ) {
								$val = rgar( $mapped_fields, (string) $input['id'] );
								if ( is_array( $val ) ) {
									$val = GFCommon::implode_non_blank( ',', $val );
								}
								$value[] = $val;
							}
						}

						if ( is_array( $value ) ) {
							$value = GFCommon::implode_non_blank( ',', $value );
						}

						break;

					case 'list':

						$value = rgar( $mapped_fields, $field->id );
						if ( is_serialized( $value ) ) {
							$value       = unserialize( $value );
							$list_values = array();

							if ( is_array( $value ) ) {
								foreach ( $value as $vals ) {
									if ( ! is_array( $vals ) ) {
										$vals = array( $vals );
									}
									$list_values = array_merge( $list_values, array_values( $vals ) );
								}
								$value = $list_values;
							}
						} else {
							$value = array_map( 'trim', explode( ',', $value ) );
						}

						break;

					case 'date':
						$value = GFCommon::date_display( rgar( $mapped_fields, $field->id ), $field->dateFormat, false );
						break;

					default:

						// handle complex fields
						$inputs = $field->get_entry_inputs();
						if ( is_array( $inputs ) ) {
							foreach ( $inputs as &$input ) {
								$filter_name              = $this->prepopulate_input( $input['id'], rgar( $mapped_fields, (string) $input['id'] ) );
								$field->allowsPrepopulate = true;
								$input['name']            = $filter_name;
							}
							$field->inputs = $inputs;
						} else {

							$value = is_array( rgar( $mapped_fields, $field->id ) ) ? implode( ',', rgar( $mapped_fields, $field->id ) ) : rgar( $mapped_fields, $field->id );

						}
				}

				if ( rgblank( $value ) ) {
					continue;
				}

				$filter_name              = self::prepopulate_input( $field->id, $value );
				$field->allowsPrepopulate = true;
				$field->inputName         = $filter_name;

			}

			return $form;
		}

		/**
		 * Add the filter to populate the default field value.
		 *
		 * @param $input_id
		 * @param $value
		 *
		 * @return string
		 */
		public function prepopulate_input( $input_id, $value ) {

			$filter_name = 'gravityflow_field_' . str_replace( '.', '_', $input_id );
			add_filter( "gform_field_value_{$filter_name}", create_function( "", "return maybe_unserialize('" . str_replace( "'", "\'", maybe_serialize( $value ) ) . "');" ) );

			return $filter_name;
		}

		/**
		 * Callback for the gform_after_submission action.
		 *
		 * If appropriate, completes the step for the current assignee and processes the workflow.
		 *
		 * @param $entry
		 * @param $form
		 */
		public function action_gform_after_submission( $entry, $form ) {
			if ( ! isset( $_POST['workflow_parent_entry_id'] ) ) {
				return;
			}

			$parent_entry_id = absint( rgpost( 'workflow_parent_entry_id' ) );

			$hash = rgpost( 'workflow_hash' );

			if ( empty( $hash ) ) {
				return;
			}

			$parent_entry = GFAPI::get_entry( $parent_entry_id );

			$api = new Gravity_Flow_API( $parent_entry['form_id'] );

			$current_step = $api->get_current_step( $parent_entry );

			if ( empty( $current_step ) || $current_step->get_type() != 'form_submission' ) {
				return;
			}

			$verify_hash = $this->get_workflow_hash( $parent_entry_id, $current_step );
			if ( ! hash_equals( $hash, $verify_hash ) ) {
				return;
			}

			$assignee_key = gravity_flow()->get_current_user_assignee_key();
			$is_assignee = $current_step->is_assignee( $assignee_key );
			if ( ! $is_assignee ) {
				return;
			}

			$assignee = new Gravity_Flow_Assignee( $assignee_key, $current_step );

			$note = esc_html__( 'Submission received.', 'gravityflowformconnector' );

			$current_step->add_note( $note, false, $current_step->get_type() );

			$assignee->update_status( 'complete' );

			$api->process_workflow( $parent_entry_id );
		}

		/**
		 * Target for the gform_form_tag filter. Adds the parent entry ID and hash as a hidden fields.
		 *
		 * @param $form_tag
		 * @param $form
		 *
		 * @return string
		 */
		public function filter_gform_form_tag( $form_tag, $form ) {
			if ( ! isset( $_REQUEST['workflow_parent_entry_id'] ) ) {
				return $form_tag;
			}

			$parent_entry_id = absint( rgget( 'workflow_parent_entry_id' ) );

			$parent_entry = GFAPI::get_entry( $parent_entry_id );

			$api = new Gravity_Flow_API( $parent_entry['form_id'] );

			$current_step = $api->get_current_step( $parent_entry );

			if ( empty( $current_step ) ) {
				return $form_tag;
			}

			$assignee_key = gravity_flow()->get_current_user_assignee_key();
			$is_assignee = $current_step->is_assignee( $assignee_key );
			if ( ! $is_assignee ) {
				return $form_tag;
			}

			$hash = sanitize_text_field( rgget( 'workflow_hash' ) );

			$hash_tag = sprintf( '<input type="hidden" name="workflow_hash" value="%s"/>', $hash );
			$parent_entry_id_tag = sprintf( '<input type="hidden" name="workflow_parent_entry_id" value="%s"/>',  $parent_entry_id );

			return $form_tag . $parent_entry_id_tag . $hash_tag;
		}


		/**
		 * Callback for the gform_validation filter.
		 *
		 * Validates that the parent ID is valid and that the entry is on a form submission step.
		 *
		 * @param $validation_result
		 *
		 * @return mixed
		 */
		public function filter_gform_validation( $validation_result ) {
			$parent_entry_id = absint( rgpost( 'workflow_parent_entry_id' ) );

			if ( empty( $parent_entry_id ) ) {
				return $validation_result;
			}

			$hash = rgpost( 'workflow_hash' );

			if ( empty( $hash ) ) {
				return $validation_result;
			}

			$parent_entry = GFAPI::get_entry( $parent_entry_id );

			if ( is_wp_error( $parent_entry ) ) {
				$validation_result['is_valid'] = false;
				$this->customize_validation_message( __( 'This form is no longer valid.', 'gravityflowformconnector' ) );
				add_filter( 'gform_validation_message', array( $this, 'filter_gform_validation_message' ), 10, 2 );
				return $validation_result;
			}

			$api = new Gravity_Flow_API( $parent_entry['form_id'] );

			$current_step = $api->get_current_step( $parent_entry );

			if ( empty( $current_step ) ) {
				$this->customize_validation_message( __( 'This form is no longer accepting submissions.', 'gravityflowformconnector' ) );
				$validation_result['is_valid'] = false;
				return $validation_result;
			}

			$assignee_key = gravity_flow()->get_current_user_assignee_key();
			$is_assignee = $current_step->is_assignee( $assignee_key );
			if ( ! $is_assignee ) {
				$validation_result['is_valid'] = false;
				$this->customize_validation_message( __( 'This form is no longer expecting your input.', 'gravityflowformconnector' ) );
				return $validation_result;
			}

			$verify_hash = $this->get_workflow_hash( $parent_entry_id, $current_step );
			if ( ! hash_equals( $hash, $verify_hash ) ) {
				$this->customize_validation_message( __( 'There was a problem with you submission. Please use the link provided.', 'gravityflowformconnector' ) );
				$validation_result['is_valid'] = false;
			}

			return $validation_result;
		}

		/**
		 * Returns a hash based on the current entry ID and the step timestamp.
		 *
		 * @param int $parent_entry_id
		 * @param Gravity_Flow_Step $step
		 *
		 * @return string
		 */
		public function get_workflow_hash( $parent_entry_id, $step ) {
			return wp_hash( 'workflow_parent_entry_id:' . $parent_entry_id . $step->get_step_timestamp() );

		}

		/**
		 * Sets up the custom validation message.
		 *
		 * @param $message
		 */
		public function customize_validation_message( $message ) {
			self::$form_submission_validation_error = $message;
			add_filter( 'gform_validation_message', array( $this, 'filter_gform_validation_message' ), 10, 2 );
		}

		/**
		 * Callback for the gform_validation_message filter.
		 *
		 * Customizes the validation message.
		 *
		 * @param $message
		 * @param $form
		 *
		 * @return string
		 */
		public function filter_gform_validation_message( $message, $form ) {

			return "<div class='validation_error'>" . esc_html( self::$form_submission_validation_error ) . '</div>';
		}

		/**
		 * @param string $value
		 * @param array $entry
		 * @param GF_Field $field
		 * @param array $form
		 * @param string $input_id
		 *
		 * @return mixed
		 */
		public function filter_save_field_value( $value, $entry, $field, $form, $input_id ) {
			$parent_entry_id = absint( rgpost( 'workflow_parent_entry_id' ) );

			if ( empty( $parent_entry_id ) ) {
				return $value;
			}

			$hash = rgpost( 'workflow_hash' );

			if ( empty( $hash ) ) {
				return $value;
			}

			if ( ! ( $field->get_input_type() == 'hidden' || $field->is_administrative() || $field->visibility == 'hidden' ) ) {
				return $value;
			}

			$parent_entry = GFAPI::get_entry( $parent_entry_id );

			if ( is_wp_error( $parent_entry ) ) {
				return $value;
			}

			$api = new Gravity_Flow_API( $parent_entry['form_id'] );

			$current_step = $api->get_current_step( $parent_entry );

			if ( empty( $current_step ) || $current_step->get_type() != 'form_submission' ) {
				return $value;
			}

			$parent_entry = $current_step->get_entry();
			$mapped_entry = $current_step->do_mapping( $form, $parent_entry );

			return isset( $mapped_entry[ $input_id ] ) ? $mapped_entry[ $input_id ] : $value;
		}

		/**
		 * Target for the gform_pre_replace_merge_tags filter. Replaces the workflow_timeline and created_by merge tags.
		 *
		 *
		 * @param string $text
		 * @param array $form
		 * @param array $entry
		 * @param bool $url_encode
		 * @param bool $esc_html
		 * @param bool $nl2br
		 * @param string $format
		 *
		 * @return string
		 */
		public function filter_gform_pre_replace_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {


			$api = new Gravity_Flow_API( $form['id'] );

			$step = $api->get_current_step( $entry );

			if ( empty( $step ) ) {
				return $text;
			}

			if ( $step->get_type() != 'form_submission' ) {
				return $text;
			}

			$assignee_key = gravity_flow()->get_current_user_assignee_key();
			$is_assignee = $step->is_assignee( $assignee_key );
			if ( ! $is_assignee ) {
				return $text;
			}


			$assignee = new Gravity_Flow_Assignee( $assignee_key, $entry );

			$text = $step->replace_variables( $text, $assignee );

			return $text;

			$parent_entry_id = absint( rgpost( 'workflow_parent_entry_id' ) );

			if ( empty( $parent_entry_id ) ) {
				return $text;
			}

			$hash = rgpost( 'workflow_hash' );

			if ( empty( $hash ) ) {
				return $text;
			}

			$parent_entry = GFAPI::get_entry( $parent_entry_id );

			if ( is_wp_error( $parent_entry ) ) {
				return $text;
			}

			$api = new Gravity_Flow_API( $parent_entry['form_id'] );

			$current_step = $api->get_current_step( $parent_entry );

			if ( empty( $current_step ) ) {
				return $text;
			}

			if ( $current_step->get_type() != 'form_submission' ) {
				return $text;
			}

			$assignee_key = gravity_flow()->get_current_user_assignee_key();
			$is_assignee = $current_step->is_assignee( $assignee_key );
			if ( ! $is_assignee ) {
				return $text;
			}

			$verify_hash = $this->get_workflow_hash( $parent_entry_id, $current_step );
			if ( ! hash_equals( $hash, $verify_hash ) ) {
				$this->customize_validation_message( __( 'There was a problem with you submission. Please use the link provided.', 'gravityflowformconnector' ) );
				return $text;
			}

			$assignee = new Gravity_Flow_Assignee( $assignee_key, $entry );

			$text = $current_step->replace_variables( $text, $assignee );

			return $text;
		}
	}
}
