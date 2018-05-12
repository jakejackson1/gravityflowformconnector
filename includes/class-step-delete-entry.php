<?php

/**
 * Gravity Flow Delete Entry Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.3.1-dev
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_Delete_Entry extends Gravity_Flow_Step {
		public $_step_type = 'delete_entry';

		public function get_label() {
			return esc_html__( 'Delete an Entry', 'gravityflowformconnector' );
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
				),
			);

			$entry_id_field = array(
				'name'       => 'update_entry_id',
				'label'      => esc_html__( 'Entry ID Field', 'gravityflowformconnector' ),
				'type'       => 'field_select',
				'tooltip'    => __( 'Select the field which will contain the entry ID of the entry that will be updated. This is used to lookup the entry so it can be updated.', 'gravityflowformconnector' ),
				'required'   => true,
				'dependency' => array(
					'field'  => 'action',
					'values' => array( 'update', 'approval', 'user_input' ),
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

			return $settings;
		}

		/**
		 * Deletes a local entry.
		 *
		 * @return bool Has the step finished?
		 */
		public function process_local_action() {
			$entry           = $this->get_entry();
			$target_entry_id = rgar( $entry, $this->update_entry_id );

			if ( empty( $target_entry_id ) ) {
				return true;
			}

			$result = GFAPI::delete_entry( $target_entry_id );

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

			$new_entry = $this->do_mapping( $form, $entry );

			$target_form_id = $this->target_form_id;

			$new_entry['form_id'] = $target_form_id;

			$target_entry_id = rgar( $entry, $this->update_entry_id );

			$target_entry_id = apply_filters( 'gravityflowformconnector_update_entry_id', $target_entry_id, $target_form_id, $entry, $form, $this );

			if ( empty( $target_entry_id ) ) {
				return true;
			}

			$this->delete_remote_entry( $target_entry_id );

			return true;
		}

		public function delete_remote_entry( $entry_id ) {
			$route  = 'entries/' . absint( $entry_id );
			$method = 'DELETE';

			$result = $this->remote_request( $route, $method );

			return $result;
		}

	}
}



