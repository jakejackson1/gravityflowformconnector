<?php

/**
 * Gravity Flow Add Entry Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_New_Entry extends Gravity_Flow_Step {
		public $_step_type = 'new_entry';

		public function get_label() {
			return esc_html__( 'New Entry', 'gravityflowformconnector' );
		}

		public function get_settings() {

			$forms = $this->get_forms();
			$form_choices[] = array( 'label' => esc_html__( 'Select a Form', 'gravityflowformconnector' ), 'value' => '' );
			foreach ( $forms  as $form ) {
				$form_choices[] = array( 'label' => $form->title, 'value' => $form->id );
			}


			$settings = array(
				'title'  => esc_html__( 'New Entry', 'gravityflow' ),
				'fields' => array(
					array(
						'name' => 'server_type',
						'label' => esc_html__( 'Server', 'gravityflowformconnector' ),
						'type' => 'radio',
						'default_value' => 'local',
						'horizontal' => true,
						'onchange' => 'jQuery(this).closest("form").submit();',
						'choices' => array(
							array( 'label' => esc_html__( 'This server', 'gravityflowformconnector' ), 'value' => 'local' ),
							array( 'label' => esc_html__( 'A different server', 'gravityflowformconnector' ), 'value' => 'remote' ),
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
						'label' => esc_html__( 'Form', 'gravityflowformconnector' ),
						'type' => 'select',
						'onchange'    => "jQuery(this).closest('form').submit();",
						'choices' => $form_choices,
					),
					array(
						'name' => 'mappings',
						'label' => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
						'type'      => 'dynamic_field_map',
						'disable_custom' => true,
						'field_map' => $this->field_mappings(),
						'tooltip'   => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form' , 'gravityflowformconnector' ),
						'dependency' => array(
							'field'  => 'target_form_id',
							'values' => array( '_notempty_' ),
						),
					),
				),
			);
			return $settings;
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

			if ( empty( $remote_form ) ) {
				return false;
			}

			$fields = $this->get_field_map_choices( $remote_form );
			return $fields;
		}

		function process() {
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

			$form = $this->get_form();

			if ( is_array( $this->mappings ) ) {
				foreach ( $this->mappings as $mapping ) {

					if ( rgblank( $mapping['key'] ) ) {
						continue;
					}

					$target_field_id = trim( $mapping['key'] );
					$source_field_id = $mapping['value'];

					if ( $source_field_id == intval( $source_field_id ) ) {
						$source_field = GFFormsModel::get_field( $form, $source_field_id );
						$inputs = $source_field->get_entry_inputs();
						if ( is_array( $inputs ) ) {
							foreach ( $inputs as $input ) {
								$input_id = str_replace( $source_field_id, $target_field_id, $input['id'] );
								$new_entry[ $input_id ] = $entry[ $input['id'] ];
							}
						} else {
							$new_entry[ $target_field_id ] = $entry[ $source_field_id ];
						}
					} else {
						$new_entry[ $target_field_id ] = $entry[ $source_field_id ];
					}
				}
			}

			$target_entry_id = GFAPI::add_entry( $new_entry );

			return true;
		}

		public function process_remote_action() {
			$entry = $this->get_entry();

			$new_entry = array(
				'form_id' => $this->target_form_id,
			);

			$form = $this->get_form();

			if ( is_array( $this->mappings ) ) {
				foreach ( $this->mappings as $mapping ) {

					if ( rgblank( $mapping['key'] ) ) {
						continue;
					}

					$target_field_id = trim( $mapping['key'] );
					$source_field_id = $mapping['value'];

					if ( $source_field_id == intval( $source_field_id ) ) {
						$source_field = GFFormsModel::get_field( $form, $source_field_id );
						$inputs = $source_field->get_entry_inputs();
						if ( is_array( $inputs ) ) {
							foreach ( $inputs as $input ) {
								$input_id = str_replace( $source_field_id, $target_field_id, $input['id'] );
								$new_entry[ $input_id ] = $entry[ $input['id'] ];
							}
						} else {
							$new_entry[ $target_field_id ] = $entry[ $source_field_id ];
						}
					} else {
						$new_entry[ $target_field_id ] = $entry[ $source_field_id ];
					}
				}
			}

			$target_entry_ids = $this->add_remote_entry( $new_entry );

			return true;
		}

		public function get_forms() {
			$server_type = $this->get_setting( 'server_type' );
			if ( $server_type == 'remote' ) {
				$forms = $this->get_remote_forms();
				$forms = json_decode( json_encode( $forms ) );
			} else {
				$forms = GFFormsModel::get_forms();
			}
			return $forms;
		}

		public function get_remote_forms() {
			$forms = $this->remote_request( 'forms' );

			if ( empty( $forms ) || is_wp_error( $forms ) ) {
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
			if ( empty( $form ) || is_wp_error( $form ) ) {
				$form = false;
			}
			$form = GFFormsModel::convert_field_objects( $form );
			return $form;
		}

		public function remote_request( $route, $method = 'GET', $body = null, $query_args = array() ) {
			$site_url = $this->get_setting( 'remote_site_url' );
			$api_key = $this->get_setting( 'remote_public_key' );
			$private_key = $this->get_setting( 'remote_private_key' );

			if ( empty( $site_url ) || empty( $api_key ) || empty( $private_key ) ) {
				return false;
			}

			$expires = strtotime( '+5 mins' );
			$string_to_sign = sprintf( '%s:%s:%s:%s', $api_key, $method, $route, $expires );
			$sig = $this->calculate_signature( $string_to_sign, $private_key );
			$site_url = trailingslashit( $site_url );
			$route = trailingslashit( $route );
			$url = $site_url . 'gravityformsapi/' . $route . '?api_key=' . $api_key . '&signature=' . $sig . '&expires=' . $expires;
			if ( ! empty( $query_args ) ) {
				$url .= '&' . http_build_query( $query_args );
			}

			$args = array( 'method' => $method );

			if ( in_array( $method, array( 'POST', 'PUT' ) ) ) {
				$args['body'] = $body;
			}

			$response = wp_remote_request( $url, $args );
			if ( wp_remote_retrieve_response_code( $response ) != 200 || ( empty( wp_remote_retrieve_body( $response ) ) ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $body['status'] > 202 ) {
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

		public function get_field_map_choices( $form, $field_type = null, $exclude_field_types = null ) {

			$fields = array();

			// Setup first choice
			if ( rgblank( $field_type ) || ( is_array( $field_type ) && count( $field_type ) > 1 ) ) {

				$first_choice_label = __( 'Select a Field', 'gravityforms' );

			} else {

				$type = is_array( $field_type ) ? $field_type[0] : $field_type;
				$type = ucfirst( GF_Fields::get( $type )->get_form_editor_field_title() );

				$first_choice_label = sprintf( __( 'Select a %s Field', 'gravityforms' ), $type );

			}

			$fields[] = array( 'value' => '', 'label' => $first_choice_label );

			// if field types not restricted add the default fields and entry meta
			if ( is_null( $field_type ) ) {
				$fields[] = array( 'value' => 'id', 'label' => esc_html__( 'Entry ID', 'gravityforms' ) );
				$fields[] = array( 'value' => 'date_created', 'label' => esc_html__( 'Entry Date', 'gravityforms' ) );
				$fields[] = array( 'value' => 'ip', 'label' => esc_html__( 'User IP', 'gravityforms' ) );
				$fields[] = array( 'value' => 'source_url', 'label' => esc_html__( 'Source Url', 'gravityforms' ) );

				$server_type = $this->get_setting( 'server_type' );
				$entry_meta = $server_type == 'remote' ? array() : GFFormsModel::get_entry_meta( $form['id'] );
				foreach ( $entry_meta as $meta_key => $meta ) {
					$fields[] = array( 'value' => $meta_key, 'label' => rgars( $entry_meta, "{$meta_key}/label" ) );
				}
			}

			// Populate form fields
			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					$input_type = $field->get_input_type();
					$inputs     = $field->get_entry_inputs();
					$field_is_valid_type = ( empty( $field_type ) || ( is_array( $field_type ) && in_array( $input_type, $field_type ) ) || ( ! empty( $field_type ) && $input_type == $field_type ) );

					if ( is_null( $exclude_field_types ) ) {
						$exclude_field = false;
					} elseif ( is_array( $exclude_field_types ) ) {
						if ( in_array( $input_type, $exclude_field_types ) ) {
							$exclude_field = true;
						} else {
							$exclude_field = false;
						}
					} else {
						//not array, so should be single string
						if ( $input_type == $exclude_field_types ) {
							$exclude_field = true;
						} else {
							$exclude_field = false;
						}
					}

					if ( is_array( $inputs ) && $field_is_valid_type && ! $exclude_field ) {
						//If this is an address field, add full name to the list
						if ( $input_type == 'address' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityforms' ) . ')',
							);
						}
						//If this is a name field, add full name to the list
						if ( $input_type == 'name' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityforms' ) . ')',
							);
						}
						//If this is a checkbox field, add to the list
						if ( $input_type == 'checkbox' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Selected', 'gravityforms' ) . ')',
							);
						}

						foreach ( $inputs as $input ) {
							$fields[] = array(
								'value' => $input['id'],
								'label' => GFCommon::get_label( $field, $input['id'] )
							);
						}
					} elseif ( $input_type == 'list' && $field->enableColumns && $field_is_valid_type && ! $exclude_field ) {
						$fields[] = array(
							'value' => $field->id,
							'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityforms' ) . ')',
						);
						$col_index = 0;
						foreach ( $field->choices as $column ) {
							$fields[] = array(
								'value' => $field->id . '.' . $col_index,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html( rgar( $column, 'text' ) ) . ')',
							);
							$col_index ++;
						}
					} elseif ( ! rgar( $field, 'displayOnly' ) && $field_is_valid_type && ! $exclude_field ) {
						$fields[] = array( 'value' => $field->id, 'label' => GFCommon::get_label( $field ) );
					}
				}
			}

			return $fields;
		}
	}
}



