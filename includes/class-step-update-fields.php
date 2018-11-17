<?php

/**
 * Gravity Flow Update Field Values Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.4.3
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_Update_Field_Values extends Gravity_Flow_Step_Update_Entry {
		public $_step_type = 'update_field_values';

		public function get_label() {
			return esc_html__( 'Update Fields', 'gravityflowformconnector' );
		}

		/**
		 * Returns the array of settings for this step.
		 *
		 * @return array
		 */
		public function get_settings() {

			$forms          = $this->get_forms();
			$form_choices[] = array(
				'label' => esc_html__( 'Select a Form', 'gravityflowformconnector' ),
				'value' => '',
			);
			foreach ( $forms as $form ) {
				$form_choices[] = array(
					'label' => $form->title,
					'value' => $form->id,
				);
			}

			$action_choices = $this->action_choices();

			$settings = array(
				'title'  => esc_html__( 'Update Field Values', 'gravityflow' ),
				'fields' => array(
					array(
						'name'          => 'server_type',
						'label'         => esc_html__( 'Site', 'gravityflowformconnector' ),
						'type'          => 'radio',
						'default_value' => 'local',
						'horizontal'    => true,
						'onchange'      => 'jQuery(this).closest("form").submit();',
						'choices'       => array(
							array( 'label' => esc_html__( 'This site', 'gravityflowformconnector' ), 'value' => 'local' ),
							array( 'label' => esc_html__( 'A different site', 'gravityflowformconnector' ), 'value' => 'remote' ),
						),
					),
					array(
						'name'       => 'remote_site_url',
						'label'      => esc_html__( 'Site Url', 'gravityflowformconnector' ),
						'type'       => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name'       => 'remote_public_key',
						'label'      => esc_html__( 'Public Key', 'gravityflowformconnector' ),
						'type'       => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name'       => 'remote_private_key',
						'label'      => esc_html__( 'Private Key', 'gravityflowformconnector' ),
						'type'       => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name'     => 'source_form_id',
						'label'    => esc_html__( 'Source Form', 'gravityflowformconnector' ),
						'type'     => 'select',
						'onchange' => "jQuery('#action').val('update');jQuery(this).closest('form').submit();",
						'choices'  => $form_choices,
					),
					array(
						'name'       => 'action',
						'label'      => esc_html__( 'Action', 'gravityflowformconnector' ),
						'type'       => count( $action_choices ) == 1 ? 'hidden' : 'select',
						'default_value' => 'update',
						'horizontal' => true,
						'onchange'   => "jQuery(this).closest('form').submit();",
						'choices'    => $action_choices,
					),
					array(
						'name'          => 'lookup_method',
						'label'         => esc_html__( 'Entry Lookup', 'gravityflowformconnector' ),
						'type'          => 'radio',
						'default_value' => 'search',
						'horizontal'    => true,
						'onchange'      => 'jQuery(this).closest("form").submit();',
						'choices'       => array(
							array( 'label' => esc_html__( 'Conditional Logic', 'gravityflowformconnector' ), 'value' => 'filter' ),
							array( 'label' => esc_html__( 'Select a field containing the source entry ID.', 'gravityflowformconnector' ), 'value' => 'select_entry_id_field' ),
						),
						'dependency' => array(
							'field'  => 'action',
							'values' => array( 'update' ),
						),
					),
					array(
						'name'                 => 'entry_filter',
						'show_sorting_options' => true,
						'form_id'              => $this->get_setting( 'source_form_id' ),
						'label'                => esc_html__( 'Lookup Conditional Logic', 'gravityflowformconnector' ),
						'type'                 => 'entry_filter',
						'filter_text'          => esc_html__( 'Look up the first entry matching {0} of the following criteria:', 'gravityflowformconnector' ),
						'dependency'           => array(
							'field'  => 'lookup_method',
							'values' => array( 'filter' ),
						),
					),
				),
			);

			$lookup_setting = $this->get_setting( 'lookup_method' );

			if ( empty( $lookup_setting ) || $lookup_setting == 'select_entry_id_field' ) {
				$entry_id_field = array(
					'name'       => 'source_entry_id',
					'label'      => esc_html__( 'Entry ID Field', 'gravityflowformconnector' ),
					'type'       => 'field_select',
					'tooltip'    => __( 'Select the field which will contain the entry ID of the entry that values will be copied from.', 'gravityflowformconnector' ),
					'required'   => true,
					'dependency' => array(
						'field'  => 'action',
						'values' => array( 'update' ),
					),
				);

				if ( function_exists( 'gravity_flow_parent_child' ) ) {
					$parent_form_choices = array();
					$entry_meta          = gravity_flow_parent_child()->get_entry_meta( array(), rgget( 'id' ) );

					foreach ( $entry_meta as $meta_key => $meta ) {
						$parent_form_choices[] = array(
							'value' => $meta_key,
							'label' => $meta['label'],
						);
					}

					if ( ! empty( $parent_form_choices ) ) {
						$entry_id_field['args']['append_choices'] = $parent_form_choices;
					}
				}

				if ( $this->get_setting( 'source_form_id' ) == $this->get_form_id() ) {
					$self_entry_id_choice = array( array( 'label' => esc_html__( 'Entry ID (Self)', 'gravityflowformconnector' ), 'value' => 'id' ) );
					if ( ! isset( $entry_id_field['args']['append_choices'] ) ) {
						$entry_id_field['args']['append_choices'] = array();
					}
					$entry_id_field['args']['append_choices'] = array_merge( $entry_id_field['args']['append_choices'], $self_entry_id_choice );
				}

				$settings['fields'][] = $entry_id_field;
			}

			$mapping_field = array(
				'name'                => 'mappings',
				'label'               => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
				'type'                => 'generic_map',
				'enable_custom_key'   => false,
				'enable_custom_value' => true,
				'key_field_title'     => esc_html__( 'Field', 'gravityflowformconnector' ),
				'value_field_title'   => esc_html__( 'Value', 'gravityflowformconnector' ),
				'value_choices'       => $this->field_mappings( 'source_form_id' ),
				'key_choices'         => $this->value_mappings(),
				'tooltip'             => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form', 'gravityflowformconnector' ),
				'dependency'          => array(
					'field'  => 'action',
					'values' => array( 'update' ),
				),
			);

			$settings['fields'][] = $mapping_field;

			return $settings;
		}

		/**
		 * Returns the array of choices for the action setting.
		 *
		 * @return array
		 */
		public function action_choices() {
			$choices = array(
				array(
					'label' => esc_html__( 'Update Field Values', 'gravityflow' ),
					'value' => 'update',
				),
			);

			return $choices;
		}

		/**
		 * Prepare field map.
		 *
		 * @return array
		 */
		public function field_mappings() {

			$source_form_id = $this->get_setting( 'source_form_id' );

			if ( empty( $source_form_id ) ) {
				return false;
			}

			$source_form = $this->get_target_form( $source_form_id );

			if ( empty( $source_form ) ) {
				return false;
			}

			$fields = $this->get_field_map_choices( $source_form );
			return $fields;
		}

		/**
		 * @param $form
		 * @param $entry
		 *
		 * @return array $new_entry
		 */
		public function do_mapping( $form, $entry ) {
			$new_entry = array();

			if ( ! is_array( $this->mappings ) ) {

				return $new_entry;
			}

			$source_form = $this->get_target_form( $this->source_form_id );

			if ( ! $source_form ) {
				$this->log_debug( __METHOD__ . '(): aborting; unable to get source form.' );

				return $new_entry;
			}

			foreach ( $this->mappings as $mapping ) {
				if ( rgblank( $mapping['key'] ) ) {
					continue;
				}

				$new_entry = $this->add_mapping_to_entry( $mapping, $entry, $new_entry, $form, $source_form );
			}

			return apply_filters( 'gravityflowformconnector_' . $this->get_type(), $new_entry, $entry, $form, $source_form, $this );
		}

		public function process_local_action() {

			$entry = $this->get_entry();

			$source_form_id = $this->source_form_id;

			$form = $this->get_form();

			$source_form = GFAPI::get_form( $source_form_id );

			$source_entry_id = rgar( $entry, $this->source_entry_id );

			$source_entry = $this->get_local_entry( $source_entry_id, $source_form_id, $entry, $form );

			if ( is_wp_error( $source_entry ) || $source_entry == false ) {
				return true;
			}

			$new_entry = $this->do_mapping( $source_form, $source_entry );

			foreach ( $new_entry as $key => $value ) {
				$entry[ (string) $key ] = $value;
			}
			GFAPI::update_entry( $entry );

			return true;
		}

		/**
		 * Updates a remote entry.
		 *
		 *
		 * @return bool Has the step finished?
		 */
		public function process_remote_action() {
			$entry = $this->get_entry();

			$form = $this->get_form();

			$source_form_id = $this->source_form_id;

			$source_form = $this->get_target_form( $source_form_id );

			if ( empty( $source_form ) ) {
				return true;
			}

			$source_entry_id = rgar( $entry, $this->source_entry_id );

			$source_entry = $this->get_remote_entry( $source_entry_id, $source_form_id, $entry, $form );

			if ( is_wp_error( $source_entry ) || $source_entry == false ) {
				return true;
			}

			$new_entry = $this->do_mapping( $source_form, $source_entry );

			foreach ( $new_entry as $key => $value ) {
				$entry[ (string) $key ] = $value;
			}
			GFAPI::update_entry( $entry );

			return true;
		}
	}
}

Gravity_Flow_Steps::register( new Gravity_Flow_Step_Update_Field_Values() );
