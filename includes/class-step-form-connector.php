<?php

/**
 * Gravity Flow Form Connector Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_Form_Connector extends Gravity_Flow_Step {
		public $_step_type = 'form_connector';

		public function get_label() {
			return esc_html__( 'Form Connector', 'gravityflowformconnector' );
		}

		public function get_settings(){

			$forms = $this->get_forms();
			$form_choices[] = array( 'label' => esc_html__( 'Select a Form', 'gravityflowformconnector' ), 'value' => '' );
			foreach ( $forms  as $form ) {
				$form_choices[] = array( 'label' => $form->title, 'value' => $form->id );
			}


			$settings = array(
				'title'  => 'Form Connector',
				'fields' => array(
					array(
						'name' => 'server_type',
						'label' => esc_html__( 'Server', 'gravityflowformconnector' ),
						'type' => 'radio',
						'default_value' => 'local',
						'horizontal' => true,
						'onchange' => 'jQuery(this).closest("form").submit();',
						'choices' => array(
							array( 'label' => esc_html__( 'Local', 'gravityflowformconnector' ), 'value' => 'local' ),
							array( 'label' => esc_html__( 'Remote', 'gravityflowformconnector' ), 'value' => 'remote' ),
						),
					),
					array(
						'name' => 'remote_site_url',
						'label' => esc_html__( 'Site Url', 'gravityflowformconnector' ),
						'type' => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name' => 'remote_public_key',
						'label' => esc_html__( 'Public Key', 'gravityflowformconnector' ),
						'type' => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name' => 'remote_private_key',
						'label' => esc_html__( 'Private Key', 'gravityflowformconnector' ),
						'type' => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name' => 'target_form_id',
						'label' => esc_html__( 'Target form', 'gravityflowformconnector' ),
						'type' => 'select',
						'onchange'    => "jQuery(this).closest('form').submit();",
						'choices' => $form_choices,
					),
					array(
						'name'       => 'action',
						'label'      => esc_html__( 'Action', 'gravityflowformconnector' ),
						'type'       => 'radio',
						'horizontal' => true,
						'onclick'    => "jQuery(this).closest('form').submit();",
						'choices'    => $this->action_choices(),
						'dependency' => array(
							'field'  => 'target_form_id',
							'values' => array( '_notempty_' ),
						),
					),
					array(
						'name' => 'update_entry_id',
						'label' => esc_html__( 'Entry ID Field', 'gravityflowformconnector' ),
						'type' => 'field_select',
						'dependency' => array(
							'field'  => 'action',
							'values' => array( 'update', 'approval', 'user_input' ),
						),
					),
					array(
						'name' => 'approval_status_field',
						'label' => esc_html__( 'Approval Status Field', 'gravityflowformconnector' ),
						'type' => 'field_select',
						'dependency' => array(
							'field'  => 'action',
							'values' => array( 'approval' ),
						),
					),
					array(
						'name' => 'mappings',
						'label' => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
						'type'      => 'dynamic_field_map',
						'disable_custom' => true,
						'field_map' => $this->field_mappings(),
						'tooltip'   => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form' , 'gravityflowformconnector' ),
						'dependency' => array(
							'field'  => 'action',
							'values' => array( 'create', 'update', 'user_input' ),
						),
					),
				),
			);
			$action = $this->get_setting( 'action' );

			if ( $this->get_setting( 'server_type' ) == 'remote' && in_array( $action, array(
					'approval',
					'user_input'
				) )
			) {
				$target_form_id = $this->get_setting( 'target_form_id' );
				if ( ! empty ( $target_form_id ) ) {
					$settings['fields'][] = array(
						'name'     => 'remote_assignee',
						'label'    => esc_html__( 'Assignee', 'gravityflowformconnector' ),
						'type'     => 'select',
						'choices'  => $this->get_remote_assignee_choices( $target_form_id ),
					);
				}

			}

			return $settings;
		}

		public function action_choices(){
			$choices = array(
				array( 'label' => esc_html__( 'Create an Entry', 'gravityflow' ), 'value' => 'create' ),
				array( 'label' => esc_html__( 'Update an Entry', 'gravityflow' ), 'value' => 'update' ),
			);

			$target_form_id = $this->get_setting( 'target_form_id' );


			if ( empty( $target_form_id ) ) {
				return $choices;
			}

			$has_approval_step = false;
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

	            $api = new Gravity_Flow_API( $target_form_id );
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

		/**
		 * Prepare field map.
		 *
		 * @return array
		 */
		public function field_mappings() {

			$fields = array(
				array(
					'label' => esc_html__( 'Select a Target Field', 'gravityformsformconnector' ),
					'value' => '',
				),
			);

			$target_form_id = $this->get_setting( 'target_form_id' );

			if ( empty( $target_form_id ) ) {
				return false;
			}

			$remote_form = $this->get_target_form( $target_form_id );

			if ( empty( $remote_form) ) {
				return false;
			}
			$remote_fields = $remote_form['fields'];

			foreach ( $remote_fields as $field ) {

				$fields[] = array(
					'label' => $field->label,
					'value' => $field->id,
				);
			}

			$server_type = $this->get_setting( 'server_type' );

			$target_form_entry_metas = $server_type == 'remote' ? array() : GFFormsModel::get_entry_meta( $target_form_id );

			foreach ( $target_form_entry_metas as $key => $target_form_entry_meta ) {

				$fields[] = array(
					'label' => $target_form_entry_meta['label'],
					'value' => $key,
				);

			}

			return $fields;

		}

		function process(){
			$server_type = $this->server_type;
			if ( $server_type == 'remote' ) {
				$result = $this->process_remote_action();
			} else {
				$result = $this->process_local_action();
			}
            $note = $this->get_name() . ': ' . esc_html__( 'Processed.', 'gravityflow' );
            $this->add_note( $note, 0, $this->get_type() );
			return $result;
		}

		public function process_local_action() {
			$entry = $this->get_entry();

			$new_entry = array(
				'form_id' => $this->target_form_id,
			);

			$api = new Gravity_Flow_API( $this->target_form_id );
			$steps = $api->get_steps();

			if ( is_array( $this->mappings ) ) {
				foreach ( $this->mappings as $mapping ) {

					if ( rgblank( $mapping['key'] ) ) {
						continue;
					}

					$target_field_id = trim( $mapping['key'] );
					$external_field_id = $mapping['value'];
					$new_entry[ $target_field_id ] = $entry[ $external_field_id ];

				}
			}

			switch ( $this->action ) {
				case 'create' :
					$target_entry_id = GFAPI::add_entry( $new_entry );

					return true;
					break;
				case 'update' :
				case 'user_input' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );
					$target_entry = GFAPI::get_entry( $target_entry_id );

					foreach ( $new_entry as $key => $value ) {
						$target_entry[ $key ] = $value ;
					}
					GFAPI::update_entry( $target_entry );
					break;
				case 'approval' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );
					$target_entry = GFAPI::get_entry( $target_entry_id );
					break;
			}

			if ( empty ( $target_entry_id ) || empty ( $target_entry ) ) {
				return true;
			}

			if ( in_array( $this->action, array( 'approval', 'user_input' ) ) && $steps ) {

				if ( $target_entry['workflow_final_status'] != 'pending' ) {
					$current_step = $api->get_current_step( $target_entry );

					if ( $current_step ) {
						$current_user_status = $current_step->get_user_status();

						$status = ( $this->action == 'approval' ) ? strtolower( rgar( $entry, $this->approval_status_field ) ) : 'complete';

						$current_role_status = false;
						$role = false;
						foreach ( gravity_flow()->get_user_roles() as $role ) {
							$current_role_status = $current_step->get_role_status( $role );
							if ( $current_role_status == 'pending' ) {
								break;
							}
						}
						if ( $current_user_status == 'pending' ) {
							if ( $token = gravity_flow()->decode_access_token() ) {
								$assignee_key = sanitize_text_field( $token['sub'] );

							} else {
								$user = wp_get_current_user();
								$assignee_key = 'user_id|' . $user->ID;
							}
							$assignee = new Gravity_Flow_Assignee( $assignee_key, $current_step );
							$assignee->update_status( $status );
						}

						if ( $current_role_status == 'pending' ) {
							$current_step->update_role_status( $role, $status );
						}

						$api->process_workflow( $target_entry_id );
					}
				}
			}

			return true;
		}

		public function process_remote_action() {
			$entry = $this->get_entry();

			$new_entry = array(
				'form_id' => $this->target_form_id,
			);

			if ( is_array( $this->mappings ) ) {
				foreach ( $this->mappings as $mapping ) {

					if ( rgblank( $mapping['key'] ) ) {
						continue;
					}

					$target_field_id = trim( $mapping['key'] );
					$external_field_id = $mapping['value'];
					$new_entry[ $target_field_id ] = $entry[ $external_field_id ];

				}
			}

			switch ( $this->action ) {
				case 'create' :
					$target_entry_ids = $this->add_remote_entry( $new_entry );

					return true;
					break;
				case 'update' :
				case 'user_input' :
					$target_entry_id = rgar( $entry, $this->update_entry_id );

					$target_entry = $this->get_remote_entry( $target_entry_id );

					foreach ( $new_entry as $key => $value ) {
						$target_entry[ $key ] = $value ;
					}
					$result = $this->update_remote_entry( $target_entry );

					break;
			}

			if ( empty ( $target_entry_id ) || empty ( $target_entry ) ) {
				return true;
			}


			return true;
		}

		public function get_forms(){
			$server_type = $this->get_setting( 'server_type' );
			if ( $server_type == 'remote' ) {
				$forms = $this->get_remote_forms();
				$forms = json_decode( json_encode( $forms ) );
			} else {
				$forms = GFFormsModel::get_forms();
			}
			return $forms;
		}

		public function get_remote_forms(){
			$forms = $this->remote_request( 'forms' );

			if ( empty ( $forms ) || is_wp_error( $forms ) ) {
				$forms = array();
			}

			return $forms;
		}

		function calculate_signature( $string, $private_key ) {
			$hash = hash_hmac( 'sha1', $string, $private_key, true );
			$sig = rawurlencode( base64_encode( $hash ) );
			return $sig;
		}

		public function get_target_form( $form_id ) {
			$server_type = $this->get_setting( 'server_type' );
			if ( $server_type == 'remote' ) {
				$form = $this->get_remote_form( $form_id );
			} else {
				$form = GFAPI::get_form( $form_id );
			}
			return $form;
		}

		public function get_remote_form( $form_id ) {
			$form = $this->remote_request( 'forms/' . $form_id );
			if ( empty ( $form ) || is_wp_error( $form ) ) {
				$form = false;
			}
			$form = GFFormsModel::convert_field_objects( $form );
			return $form;
		}

		public function remote_request( $route, $method = 'GET', $body = null, $query_args = array() ) {
			$site_url = $this->get_setting( 'remote_site_url' );
			$api_key = $this->get_setting( 'remote_public_key' );
			$private_key = $this->get_setting( 'remote_private_key' );

			if ( empty ( $site_url ) || empty ( $api_key ) || empty( $private_key ) ) {
				return false;
			}

			$expires = strtotime( '+5 mins' );
			$string_to_sign = sprintf( '%s:%s:%s:%s', $api_key, $method, $route, $expires );
			$sig = $this->calculate_signature( $string_to_sign, $private_key );
			$site_url = trailingslashit( $site_url );
			$route = trailingslashit( $route );
			$url = $site_url . 'gravityformsapi/' . $route . '?api_key=' . $api_key . '&signature=' . $sig . '&expires=' . $expires;
			if ( ! empty ( $query_args  ) ) {
				$url .= http_build_query( $query_args );
			}

			$args = array( 'method' => $method );

			if ( in_array( $method, array( 'POST', 'PUT' ) ) ) {
				$args['body'] = $body;
			}

			$response = wp_remote_request( $url, $args );
			if ( wp_remote_retrieve_response_code( $response ) != 200 || ( empty( wp_remote_retrieve_body( $response ) ) ) ){
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $body['status'] > 202 ){
				return false;
			}

			return $body['response'];
		}

		public function add_remote_entry( $entry ) {
			$target_form_id = $this->target_form_id;
			$route = 'forms/' . $target_form_id . '/entries';
			$method = 'POST';
			$body = json_encode( array( $entry ) );
			$entry_ids = $this->remote_request( $route, $method, $body );
			return $entry_ids;
		}

		public function get_remote_entry( $entry_id ) {
			$route = 'entries/' . $entry_id;
			$result = $this->remote_request( $route );
			return $result;
		}

		public function update_remote_entry( $entry ) {
			$route = 'entries/' . absint( $entry['id'] );
			$method = 'PUT';
			$body = json_encode( $entry );
			if ( in_array( $this->action, array( 'approval', 'user_input' ) ) ) {
				$query_args = array(
					'action' => $this->action,
					'status' => ( $this->action == 'approval' ) ? strtolower( rgar( $entry, $this->approval_status_field ) ) : 'complete'
				);
			} else {
				$query_args = array();
			}

			$result = $this->remote_request( $route, $method, $body, $query_args );
			return $result;
		}

		public function get_setting( $setting ) {
			$meta = $this->get_feed_meta();

			if ( empty ( $meta ) ) {
				$value = gravity_flow()->get_setting( $setting );
			} else {
				$value = $this->{$setting};
			}
			return $value;
		}

		public function get_remote_steps( $form_id ) {
			$route = 'forms/' . $form_id . '/steps';
			$steps = $this->remote_request( $route );
			return $steps;
		}

		public function get_remote_assignee_choices( $form_id ) {
			$steps = $this->get_remote_steps( $form_id );
			$assignee_keys = $choices = array();
			foreach( $steps as $step ) {
				foreach ( $step['assignees'] as $assignee ) {
					$assignee_keys[ $assignee['key'] ] = $assignee['display_name'];
				}
			}

			foreach( $assignee_keys as $assignee_key => $display_name ) {
				$choices[] = array( 'label' => $display_name, 'value' => $assignee_key );
			}
			return $choices;
		}

	}
}



