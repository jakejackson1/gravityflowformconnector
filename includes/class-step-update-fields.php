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

	class Gravity_Flow_Step_Update_Field_Values extends Gravity_Flow_Step_New_Entry {
		public $_step_type = 'update_field_values';

		public function get_label() {
			return esc_html__( 'Update Field Values', 'gravityflowformconnector' );
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
				$form_choices[] = array( 'label' => $form->title, 'value' => $form->id );
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
				),
			);

			$entry_id_field = array(
				'name'       => 'target_entry_id',
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
					$parent_form_choices[] = array( 'value' => $meta_key, 'label' => $meta['label'] );
				}

				if ( ! empty( $parent_form_choices ) ) {
					$entry_id_field['args']['append_choices'] = $parent_form_choices;
				}
			}

			if ( $this->get_setting( 'target_form_id' ) == $this->get_form_id() ) {
				$self_entry_id_choice = array( array( 'label' => esc_html__( 'Entry ID (Self)', 'gravityflowformconnector' ), 'value' => 'id' ) );
				if ( ! isset( $entry_id_field['args']['append_choices'] ) ) {
					$entry_id_field['args']['append_choices'] = array();
				}
				$entry_id_field['args']['append_choices'] = array_merge( $entry_id_field['args']['append_choices'], $self_entry_id_choice );
			}

			$settings['fields'][] = $entry_id_field;

			$mapping_field = array(
				'name'                => 'mappings',
				'label'               => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
				'type'                => 'generic_map',
				'enable_custom_key'   => false,
				'enable_custom_value' => true,
				'key_field_title'     => esc_html__( 'Field', 'gravityflowformconnector' ),
				'value_field_title'   => esc_html__( 'Value', 'gravityflowformconnector' ),
				'value_choices'       => $this->field_mappings(),
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
				array( 'label' => esc_html__( 'Update Field Values', 'gravityflow' ), 'value' => 'update' ),
			);

			return $choices;
		}

		public function process_local_action() {

			$entry = $this->get_entry();

			$target_form_id = $this->target_form_id;

			$form = $this->get_form();

			$target_form = GFAPI::get_form( $target_form_id );

			$target_entry_id = rgar( $entry, $this->target_entry_id );

			$target_entry_id = apply_filters( 'gravityflowformconnector_target_entry_id', $target_entry_id, $target_form_id, $entry, $form, $this );

			if ( empty( $target_entry_id ) ) {
				return true;
			}

			$target_entry = GFAPI::get_entry( $target_entry_id );

			if ( is_wp_error( $target_entry ) ) {
				return true;
			}

			$new_entry = $this->do_mapping( $target_form, $target_entry );

			if ( ! is_wp_error( $target_entry ) ) {
				foreach ( $new_entry as $key => $value ) {
					$entry[ (string) $key ] = $value;
				}
				GFAPI::update_entry( $entry );
			}

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

			$target_form_id = $this->target_form_id;

			$target_form = $this->get_target_form( $target_form_id );

			if ( empty( $target_form ) ) {
				return true;
			}

			$target_entry_id = rgar( $entry, $this->target_entry_id );

			$target_entry_id = apply_filters( 'gravityflowformconnector_target_entry_id', $target_entry_id, $target_form_id, $entry, $form, $this );

			if ( empty( $target_entry_id ) ) {
				return true;
			}

			$target_entry = $this->get_remote_entry( $target_entry_id );

			$new_entry = $this->do_mapping( $target_form, $target_entry );

			if ( ! is_wp_error( $target_entry ) ) {
				foreach ( $new_entry as $key => $value ) {
					$entry[ (string) $key ] = $value;
				}
				GFAPI::update_entry( $entry );
			}

			return true;
		}

		/**
		 * Returns a remote entry.
		 *
		 * @param $entry_id
		 *
		 * @return bool
		 */
		public function get_remote_entry( $entry_id ) {
			$route  = 'entries/' . $entry_id;
			$result = $this->remote_request( $route );

			return $result;
		}
	}
}

Gravity_Flow_Steps::register( new Gravity_Flow_Step_Update_Field_Values() );
