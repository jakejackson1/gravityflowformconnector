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

if ( ! class_exists( 'Gravity_Flow_Step_New_Entry' ) ) {
	require_once( 'class-step-new-entry.php' );
}

class Gravity_Flow_Step_Delete_Entry extends Gravity_Flow_Step_New_Entry {
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
		$settings = array(
			'title'  => esc_html__( 'Delete an Entry', 'gravityflow' ),
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
			),
		);

		$entry_id_field = array(
			'name'       => 'delete_entry_id',
			'label'      => esc_html__( 'Entry ID Field', 'gravityflowformconnector' ),
			'type'       => 'field_select',
			'tooltip'    => __( 'Select the field which will contain the entry ID of the entry that will be deleted. This is used to lookup the entry so it can be deleted.', 'gravityflowformconnector' ),
			'required'   => true,
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

		$self_entry_id_choice = array(
			array(
				'label' => esc_html__( 'Entry ID (Self)', 'gravityflowformconnector' ),
				'value' => 'id'
			)
		);
		if ( ! isset( $entry_id_field['args']['append_choices'] ) ) {
			$entry_id_field['args']['append_choices'] = array();
		}
		$entry_id_field['args']['append_choices'] = array_merge( $entry_id_field['args']['append_choices'], $self_entry_id_choice );

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
		$target_entry_id = rgar( $entry, $this->delete_entry_id );

		if ( empty( $target_entry_id ) ) {
			return true;
		}

		$this->log_debug( __METHOD__ . '(): running for entry #' . $target_entry_id );

		if ( $this->trash_entry ) {
			$result = GFAPI::update_entry_property( $target_entry_id, 'status', 'trash' );
			$this->log_debug( __METHOD__ . '() trashed entry: ' . var_export( $result, 1 ) );
		} elseif ( $target_entry_id == $entry['id'] ) {
			gform_add_meta( $target_entry_id, 'workflow_delete_entry', 1 );
			$this->log_debug( __METHOD__ . '(): scheduled for deletion.' );
		} else {
			$result = GFAPI::delete_entry( $target_entry_id );
			$this->log_debug( __METHOD__ . '(): deleted entry => ' . var_export( $result, 1 ) );
		}

		return true;
	}

	/**
	 * Deletes a remote entry.
	 *
	 * @return bool Has the step finished?
	 */
	public function process_remote_action() {
		$entry = $this->get_entry();
		$form  = $this->get_form();

		$target_entry_id = rgar( $entry, $this->delete_entry_id );
		$target_entry_id = apply_filters( 'gravityflowformconnector_delete_entry_id', $target_entry_id, $entry, $form, $this );

		if ( empty( $target_entry_id ) ) {
			return true;
		}

		$this->delete_remote_entry( $target_entry_id );

		return true;
	}

	/**
	 * Sends a request to delete a remote entry.
	 *
	 * @param int $entry_id The ID of the entry to be deleted.
	 *
	 * @return bool
	 */
	public function delete_remote_entry( $entry_id ) {
		$route  = 'entries/' . absint( $entry_id );
		$method = 'DELETE';

		$this->log_debug( __METHOD__ . '(): running for entry #' . $entry_id );
		$result = $this->remote_request( $route, $method );
		$this->log_debug( __METHOD__ . '(): result => ' . print_r( $result, 1 ) );

		return $result;
	}

	public static function cron_delete_local_entries() {

		$form_ids = gravity_flow()->get_workflow_form_ids();

		if ( empty( $form_ids ) ) {
			gravity_flow_form_connector()->log_debug( __METHOD__ . '(): aborting; no forms.' );
			return;
		}

		$criteria = array(
			'status'        => 'active',
			'field_filters' => array(
				array(
					'key'   => 'workflow_delete_entry',
					'value' => 1,
				),
			),
		);

		$entry_ids = GFAPI::get_entry_ids( 0, $criteria );

		gravity_flow_form_connector()->log_debug( __METHOD__ . '(): IDs => ' . print_r( $entry_ids, 1 ) );

		foreach ( $entry_ids as $entry_id ) {
			gravity_flow_form_connector()->log_debug( __METHOD__ . '(): deleting entry #' . $entry_id );
			$result = GFAPI::delete_entry( $entry_id );
			gravity_flow_form_connector()->log_debug( __METHOD__ . '(): result => ' . print_r( $result, 1 ) );
		}

	}

}
