<?php

/**
 * Gravity Flow Update Entry Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_Update_Entry extends Gravity_Flow_Step_New_Entry {
		public $_step_type = 'update_entry';

		public function get_label() {
			return esc_html__( 'Update an Entry', 'gravityflowformconnector' );
		}

		public function get_settings() {

			$forms          = $this->get_forms();
			$form_choices[] = array(
				'label' => esc_html__( 'Select a Form', 'gravityflowformconnector' ),
				'value' => '',
			);
			foreach ( $forms as $form ) {
				$form_choices[] = array( 'label' => $form->title, 'value' => $form->id );
			}

			$action_choices = $this->action_choices();

			$settings = array(
				'title'  => esc_html__( 'Update an Entry', 'gravityflow' ),
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
						'name'     => 'target_form_id',
						'label'    => esc_html__( 'Form', 'gravityflowformconnector' ),
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
						'name'       => 'update_entry_id',
						'label'      => esc_html__( 'Entry ID Field', 'gravityflowformconnector' ),
						'type'       => 'field_select',
						'tooltip'   => __( 'Select the field which will contain the entry ID of the entry that will be updated. This is used to lookup the entry so it can be updated.', 'gravityflowformconnector' ),
						'required'   => true,
						'dependency' => array(
							'field'  => 'action',
							'values' => array( 'update', 'approval', 'user_input' ),
						),
					),
					array(
						'name'       => 'approval_status_field',
						'label'      => esc_html__( 'Approval Status Field', 'gravityflowformconnector' ),
						'type'       => 'field_select',
						'dependency' => array(
							'field'  => 'action',
							'values' => array( 'approval' ),
						),
					),
				),
			);

			if ( version_compare( gravity_flow()->_version, '1.3.0.10', '>=' ) ) {
				// Use Generic Map setting to allow custom values.
				$mapping_field = array(
					'name'                => 'mappings',
					'label'               => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
					'type'                => 'generic_map',
					'enable_custom_key'   => false,
					'enable_custom_value' => true,
					'key_field_title'     => esc_html__( 'Field', 'gravityflowformconnector' ),
					'value_field_title'   => esc_html__( 'Value', 'gravityflowformconnector' ),
					'value_choices'       => $this->value_mappings(),
					'key_choices'         => $this->field_mappings(),
					'tooltip'             => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form', 'gravityflowformconnector' ),
					'dependency'          => array(
						'field'  => 'action',
						'values' => array( 'update', 'user_input' ),
					),
				);
			} else {
				$mapping_field = array(
					'name'           => 'mappings',
					'label'          => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
					'type'           => 'dynamic_field_map',
					'disable_custom' => true,
					'field_map'      => $this->field_mappings(),
					'tooltip'        => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form', 'gravityflowformconnector' ),
					'dependency'     => array(
						'field'  => 'action',
						'values' => array( 'update', 'user_input' ),
					),
				);
			}

			$settings['fields'][] = $mapping_field;

			$action   = $this->get_setting( 'action' );

			if ( $this->get_setting( 'server_type' ) == 'remote' && in_array( $action, array(
					'approval',
					'user_input',
				) )
			) {
				$target_form_id = $this->get_setting( 'target_form_id' );
				if ( ! empty ( $target_form_id ) ) {
					$settings['fields'][] = array(
						'name'    => 'remote_assignee',
						'label'   => esc_html__( 'Assignee', 'gravityflowformconnector' ),
						'type'    => 'select',
						'choices' => $this->get_remote_assignee_choices( $target_form_id ),
					);
				}
			}

			return $settings;
		}

		public function action_choices() {
			$choices = array(
				array( 'label' => esc_html__( 'Update an Entry', 'gravityflow' ), 'value' => 'update' ),
			);

			$target_form_id = $this->get_setting( 'target_form_id' );

			if ( empty( $target_form_id ) ) {
				return $choices;
			}

			$has_approval_step   = false;
			$has_user_input_step = false;

			if ( $this->get_setting( 'server_type' ) == 'remote' ) {
				$steps = $this->get_remote_steps( $target_form_id );
				if ( $steps ) {
					foreach ( $steps as $step ) {
						if ( $step['type'] == 'approval' ) {
							$has_approval_step = true;
						} elseif ( $step['type'] == 'user_input' ) {
							$has_user_input_step = true;
						}
					}
				}
			} else {

				$api   = new Gravity_Flow_API( $target_form_id );
				$steps = $api->get_steps();

				foreach ( $steps as $step ) {
					if ( $step->get_type() == 'approval' ) {
						$has_approval_step = true;
					} elseif ( $step->get_type() == 'user_input' ) {
						$has_user_input_step = true;
					}
				}
			}

			if ( $has_approval_step ) {
				$choices[] = array( 'label' => esc_html__( 'Approval', 'gravityflow' ), 'value' => 'approval' );
			}
			if ( $has_user_input_step ) {
				$choices[] = array( 'label' => esc_html__( 'User Input', 'gravityflow' ), 'value' => 'user_input' );
			}

			return $choices;
		}

		public function process_local_action() {
			$entry = $this->get_entry();

			$api   = new Gravity_Flow_API( $this->target_form_id );

			$steps = $api->get_steps();

			$form = $this->get_form();

			$new_entry = $this->do_mapping( $form, $entry );

			$new_entry['form_id'] = $this->target_form_id;

			switch ( $this->action ) {
				case 'update' :
				case 'user_input' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );
					$target_entry    = GFAPI::get_entry( $target_entry_id );

					if ( ! is_wp_error( $target_entry ) ) {
						foreach ( $new_entry as $key => $value ) {
							$target_entry[ $key ] = $value;
						}
						GFAPI::update_entry( $target_entry );
					}

					break;
				case 'approval' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );
					$target_entry    = GFAPI::get_entry( $target_entry_id );
					break;
			}

			if ( empty( $target_entry_id ) || empty( $target_entry ) ) {
				return true;
			}

			if ( in_array( $this->action, array( 'approval', 'user_input' ) ) && $steps ) {

				if ( $target_entry['workflow_final_status'] == 'pending' ) {
					$current_step = $api->get_current_step( $target_entry );

					if ( $current_step ) {

						$status = ( $this->action == 'approval' ) ? strtolower( rgar( $entry, $this->approval_status_field ) ) : 'complete';

						if ( $token = gravity_flow()->decode_access_token() ) {
							$assignee_key = sanitize_text_field( $token['sub'] );

						} else {
							$user         = wp_get_current_user();
							$assignee_key = 'user_id|' . $user->ID;
						}
						$assignee = new Gravity_Flow_Assignee( $assignee_key, $current_step );

						$form = GFAPI::get_form( $this->target_form_id );

						$result = $current_step->process_assignee_status( $assignee, $status, $form );

						if ( $result ) {
							$api->process_workflow( $target_entry_id );
						}
					}
				}
			}

			return true;
		}

		public function process_remote_action() {
			$entry = $this->get_entry();

			$form = $this->get_form();

			$new_entry = $this->do_mapping( $form, $entry );

			$new_entry['form_id'] = $this->target_form_id;

			switch ( $this->action ) {
				case 'update' :
				case 'user_input' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );

					$target_entry = $this->get_remote_entry( $target_entry_id );

					foreach ( $new_entry as $key => $value ) {
						$target_entry[ $key ] = $value;
					}

					$result = $this->update_remote_entry( $target_entry );

					$this->log_debug( __METHOD__ . '(): update result - ' . print_r( $result, true ) );

					if ( $this->action == 'user_input' ) {
						$route = 'entries/' . $target_entry_id . '/assignees/' . $this->remote_assignee;
						$body  = json_encode( array( 'status' => 'complete' ) );

						$assignee_update_result = $this->remote_request( $route, 'POST', $body );
						$this->log_debug( __METHOD__ . '(): update assignee result - ' . print_r( $assignee_update_result, true ) );
					}

					break;
				case 'approval' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );
					$assignee_key    = sanitize_text_field( $this->remote_assignee );
					$status          = sanitize_text_field( strtolower( rgar( $entry, $this->approval_status_field ) ) );
					$route           = sprintf( 'entries/%d/assignees/%s', $target_entry_id, $assignee_key );
					$body            = json_encode( array( 'status' => $status ) );
					$this->remote_request( $route, 'POST', $body );
			}

			return true;
		}

		public function get_remote_entry( $entry_id ) {
			$route  = 'entries/' . $entry_id;
			$result = $this->remote_request( $route );

			return $result;
		}

		public function update_remote_entry( $entry ) {
			$route  = 'entries/' . absint( $entry['id'] );
			$method = 'PUT';
			$body   = json_encode( $entry );

			$result = $this->remote_request( $route, $method, $body );

			return $result;
		}

		public function get_remote_steps( $form_id ) {
			$route = 'forms/' . $form_id . '/steps';
			$steps = $this->remote_request( $route );

			return $steps;
		}

		public function get_remote_assignee_choices( $form_id ) {
			$steps         = $this->get_remote_steps( $form_id );
			if ( empty( $steps ) ) {
				return array();
			}
			$assignee_keys = $choices = array();
			foreach ( $steps as $step ) {
				foreach ( $step['assignees'] as $assignee ) {
					$assignee_keys[ $assignee['key'] ] = $assignee['display_name'];
				}
			}

			foreach ( $assignee_keys as $assignee_key => $display_name ) {
				$choices[] = array( 'label' => $display_name, 'value' => $assignee_key );
			}

			return $choices;
		}
	}
}



