<?php

/**
 * Gravity Flow Form Submission Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_Form_Submission extends Gravity_Flow_Step {
		public $_step_type = 'form_submission';

		public function get_label() {
			return esc_html__( 'Form Submission', 'gravityflowformconnector' );
		}

		public function get_settings() {

			$forms = $this->get_forms();
			$form_choices[] = array( 'label' => esc_html__( 'Select a Form', 'gravityflowformconnector' ), 'value' => '' );
			foreach ( $forms  as $form ) {
				$form_choices[] = array( 'label' => $form->title, 'value' => $form->id );
			}

			$account_choices = gravity_flow()->get_users_as_choices();

			$type_field_choices = array(
				array( 'label' => __( 'Select', 'gravityflowformconnector' ), 'value' => 'select' ),
				array( 'label' => __( 'Conditional Routing', 'gravityflowformconnector' ), 'value' => 'routing' ),
			);

			$page_choices = $this->get_page_choices();

			$settings = array(
				'title'  => esc_html__( 'Form Submission', 'gravityflowformconnector' ),
				'fields' => array(
					array(
						'name' => 'target_form_id',
						'label' => esc_html__( 'Form', 'gravityflowformconnector' ),
						'type' => 'select',
						'onchange'    => "jQuery(this).closest('form').submit();",
						'choices' => $form_choices,
					),
					array(
						'name'          => 'type',
						'label'         => __( 'Assign To:', 'gravityflowformconnector' ),
						'type'          => 'radio',
						'default_value' => 'select',
						'horizontal'    => true,
						'choices'       => $type_field_choices,
					),
					array(
						'id'       => 'assignees',
						'name'     => 'assignees[]',
						'multiple' => 'multiple',
						'label'    => esc_html__( 'Select Assignees', 'gravityflowformconnector' ),
						'type'     => 'select',
						'choices'  => $account_choices,
					),
					array(
						'name'  => 'routing',
						'label' => 'Assignee Routing',
						'type'  => 'routing',
					),
					array(
						'id'            => 'assignee_policy',
						'name'          => 'assignee_policy',
						'label'         => __( 'Assignee Policy', 'gravityflowformconnector' ),
						'tooltip'       => __( 'Define how this step should be processed. If all assignees must complete this step then the entry will require input from every assignee before the step can be completed. If the step is assigned to a role only one user in that role needs to complete the step.', 'gravityflowformconnector' ),
						'type'          => 'radio',
						'default_value' => 'all',
						'choices'       => array(
							array(
								'label' => __( 'At least one assignee must complete this step', 'gravityflowformconnector' ),
								'value' => 'any',
							),
							array(
								'label' => __( 'All assignees must complete this step', 'gravityflowformconnector' ),
								'value' => 'all',
							),
						),
					),
					array(
						'name'     => 'instructions',
						'label'    => __( 'Instructions', 'gravityflowformconnector' ),
						'type'     => 'checkbox_and_textarea',
						'tooltip'  => esc_html__( 'Activate this setting to display instructions to the user for the current step.', 'gravityflowformconnector' ),
						'checkbox' => array(
							'label' => esc_html__( 'Display instructions', 'gravityflowformconnector' ),
						),
						'textarea' => array(
							'use_editor'    => true,
							'default_value' => esc_html__( 'Instructions: please review the values in the fields below and click on the Approve or Reject button', 'gravityflowformconnector' ),
						),
					),
					array(
						'name'    => 'display_fields',
						'label'   => __( 'Display Fields', 'gravityflowformconnector' ),
						'tooltip' => __( 'Select the fields to hide or display.', 'gravityflowformconnector' ),
						'type'    => 'display_fields',
					),
					array(
						'name'    => 'assignee_notification_enabled',
						'label'   => __( 'Email', 'gravityflowformconnector' ),
						'type'    => 'checkbox',
						'choices' => array(
							array(
								'label'         => __( 'Send Email to the assignee(s).', 'gravityflowformconnector' ),
								'tooltip'       => __( 'Enable this setting to send email to each of the assignees as soon as the entry has been assigned. If a role is configured to receive emails then all the users with that role will receive the email.', 'gravityflowformconnector' ),
								'name'          => 'assignee_notification_enabled',
								'default_value' => false,
							),
						),
					),
					array(
						'name'  => 'assignee_notification_from_name',
						'class' => 'fieldwidth-2 merge-tag-support mt-hide_all_fields mt-position-right ui-autocomplete-input',
						'label' => __( 'From Name', 'gravityflowformconnector' ),
						'type'  => 'text',
					),
					array(
						'name'          => 'assignee_notification_from_email',
						'class'         => 'fieldwidth-2 merge-tag-support mt-hide_all_fields mt-position-right ui-autocomplete-input',
						'label'         => __( 'From Email', 'gravityflowformconnector' ),
						'type'          => 'text',
						'default_value' => '{admin_email}',
					),
					array(
						'name'  => 'assignee_notification_reply_to',
						'class' => 'fieldwidth-2 merge-tag-support mt-hide_all_fields mt-position-right ui-autocomplete-input',
						'label' => __( 'Reply To', 'gravityflowformconnector' ),
						'type'  => 'text',
					),
					array(
						'name'  => 'assignee_notification_bcc',
						'class' => 'fieldwidth-2 merge-tag-support mt-hide_all_fields mt-position-right ui-autocomplete-input',
						'label' => __( 'BCC', 'gravityflowformconnector' ),
						'type'  => 'text',
					),
					array(
						'name'  => 'assignee_notification_subject',
						'class' => 'large fieldwidth-1 merge-tag-support mt-hide_all_fields mt-position-right ui-autocomplete-input',
						'label' => __( 'Subject', 'gravityflowformconnector' ),
						'type'  => 'text',
					),
					array(
						'name'          => 'assignee_notification_message',
						'label'         => __( 'Message to Assignee(s)', 'gravityflowformconnector' ),
						'type'          => 'visual_editor',
						'default_value' => __( 'Please submit the following form: {workflow_form_submission_link}', 'gravityflowformconnector' ),
					),
					array(
						'name'    => 'assignee_notification_autoformat',
						'label'   => '',
						'type'    => 'checkbox',
						'choices' => array(
							array(
								'label'         => __( 'Disable auto-formatting', 'gravityflowformconnector' ),
								'name'          => 'assignee_notification_disable_autoformat',
								'default_value' => false,
								'tooltip'       => __( 'Disable auto-formatting to prevent paragraph breaks being automatically inserted when using HTML to create the email message.', 'gravityflowformconnector' ),

							),
						),
					),
					array(
						'name'     => 'resend_assignee_email',
						'label'    => '',
						'type'     => 'checkbox_and_text',
						'checkbox' => array(
							'label' => __( 'Send reminder', 'gravityflowformconnector' ),
						),
						'text'     => array(
							'default_value' => 7,
							'before_input'  => __( 'Resend the assignee email after', 'gravityflowformconnector' ),
							'after_input'   => ' ' . __( 'day(s)', 'gravityflowformconnector' ),
						),
					),
					array(
						'name'          => 'submit_page',
						'tooltip'       => __( 'Select the page to be used for the form submission. This can be the Workflow Submit Page in the WordPress Admin Dashboard or you can choose a page with either a Gravity Flow submit shortcode or a Gravity Forms shortcode.', 'gravityflowformconnector' ),
						'label'         => __( 'Submit Page', 'gravityflowformconnector' ),
						'type'          => 'select',
						'default_value' => 'admin',
						'choices'       => $page_choices,
					),
				),
			);

			// Use Generic Map setting to allow custom values.
			$mapping_field = array(
				'name' => 'mappings',
				'label' => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
				'type' => 'generic_map',
				'enable_custom_key' => false,
				'enable_custom_value' => true,
				'key_field_title' => esc_html__( 'Field', 'gravityflowformconnector' ),
				'value_field_title' => esc_html__( 'Value', 'gravityflowformconnector' ),
				'value_choices' => $this->value_mappings(),
				'key_choices' => $this->field_mappings(),
				'tooltip'   => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form' , 'gravityflowformconnector' ),
				'dependency' => array(
					'field'  => 'target_form_id',
					'values' => array( '_notempty_' ),
				),
			);

			$settings['fields'][] = $mapping_field;

			return $settings;
		}

		/**
		 * Prepare field map.
		 *
		 * @return array
		 */
		public function field_mappings() {

			$target_form_id = $this->get_setting( 'target_form_id' );

			if ( empty( $target_form_id ) ) {
				return false;
			}

			$target_form = $this->get_target_form();

			if ( empty( $target_form ) ) {
				return false;
			}

			$fields = $this->get_field_map_choices( $target_form );
			return $fields;
		}

		/**
		 * Prepare value map.
		 *
		 * @return array
		 */
		public function value_mappings() {

			$form = $this->get_form();

			$fields = $this->get_field_map_choices( $form );
			return $fields;
		}

		function process() {
			$complete = $this->assign();
			$note = $this->get_name() . ': ' . esc_html__( 'Waiting.', 'gravityflowformconnector' );
			$this->add_note( $note, 0, $this->get_type() );
			return $complete;
		}

		public function evaluate_status() {

			if ( $this->is_queued() ) {
				return 'queued';
			}

			$assignee_details = $this->get_assignees();

			$step_status = 'complete';

			foreach ( $assignee_details as $assignee ) {
				$user_status = $assignee->get_status();

				if ( empty( $user_status ) || $user_status == 'pending' ) {
					$step_status = 'pending';
				}
			}

			return $step_status;
		}

		public function get_forms() {
			$forms = GFFormsModel::get_forms();
			return $forms;
		}

		public function get_target_form() {
			$target_form_id = $this->get_setting( 'target_form_id' );
			$form = GFAPI::get_form( $target_form_id );
			return $form;
		}

		public function get_field_map_choices( $form, $field_type = null, $exclude_field_types = null ) {

			$fields = array();

			// Setup first choice
			if ( rgblank( $field_type ) || ( is_array( $field_type ) && count( $field_type ) > 1 ) ) {

				$first_choice_label = __( 'Select a Field', 'gravityflowformconnector' );

			} else {

				$type = is_array( $field_type ) ? $field_type[0] : $field_type;
				$type = ucfirst( GF_Fields::get( $type )->get_form_editor_field_title() );

				$first_choice_label = sprintf( __( 'Select a %s Field', 'gravityflowformconnector' ), $type );

			}

			$fields[] = array( 'value' => '', 'label' => $first_choice_label );

			// if field types not restricted add the default fields and entry meta
			if ( is_null( $field_type ) ) {
				$fields[] = array( 'value' => 'id', 'label' => esc_html__( 'Entry ID', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'date_created', 'label' => esc_html__( 'Entry Date', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'ip', 'label' => esc_html__( 'User IP', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'source_url', 'label' => esc_html__( 'Source Url', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'created_by', 'label' => esc_html__( 'Created By', 'gravityflowformconnector' ) );

				$entry_meta = GFFormsModel::get_entry_meta( $form['id'] );
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
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityflowformconnector' ) . ')',
							);
						}
						//If this is a name field, add full name to the list
						if ( $input_type == 'name' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityflowformconnector' ) . ')',
							);
						}
						//If this is a checkbox field, add to the list
						if ( $input_type == 'checkbox' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Selected', 'gravityflowformconnector' ) . ')',
							);
						}

						foreach ( $inputs as $input ) {
							$fields[] = array(
								'value' => $input['id'],
								'label' => GFCommon::get_label( $field, $input['id'] ),
							);
						}
					} elseif ( $input_type == 'list' && $field->enableColumns && $field_is_valid_type && ! $exclude_field ) {
						$fields[] = array(
							'value' => $field->id,
							'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityflowformconnector' ) . ')',
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

			$target_form = $this->get_target_form();

			if ( ! $target_form ) {
				$this->log_debug( __METHOD__ . '(): aborting; unable to get target form.' );

				return $new_entry;
			}

			foreach ( $this->mappings as $mapping ) {
				if ( rgblank( $mapping['key'] ) ) {
					continue;
				}

				$new_entry = $this->add_mapping_to_entry( $mapping, $entry, $new_entry, $form, $target_form );
			}

			return apply_filters( 'gravityflowformconnector_' . $this->get_type(), $new_entry, $entry, $form, $target_form, $this );
		}

		/**
		 * Add the mapped value to the new entry.
		 *
		 * @param array $mapping The properties for the mapping being processed.
		 * @param array $entry The entry being processed by this step.
		 * @param array $new_entry The entry to be added or updated.
		 * @param array $form The form being processed by this step.
		 * @param array $target_form The target form for the entry being added or updated.
		 *
		 * @return array
		 */
		public function add_mapping_to_entry( $mapping, $entry, $new_entry, $form, $target_form ) {
			$target_field_id = trim( $mapping['key'] );
			$source_field_id = (string) $mapping['value'];

			$source_field = GFFormsModel::get_field( $form, $source_field_id );

			if ( is_object( $source_field ) ) {
				$is_full_source      = $source_field_id === (string) intval( $source_field_id );
				$source_field_inputs = $source_field->get_entry_inputs();
				$target_field        = GFFormsModel::get_field( $target_form, $target_field_id );

				if ( $is_full_source && is_array( $source_field_inputs ) ) {
					$is_full_target      = $target_field_id === (string) intval( $target_field_id );
					$target_field_inputs = is_object( $target_field ) ? $target_field->get_entry_inputs() : false;

					if ( $is_full_target && is_array( $target_field_inputs ) ) {
						foreach ( $source_field_inputs as $input ) {
							$input_id               = str_replace( $source_field_id . '.', $target_field_id . '.', $input['id'] );
							$source_field_value     = $this->get_source_field_value( $entry, $source_field, $input['id'] );
							$new_entry[ $input_id ] = $this->get_target_field_value( $source_field_value, $target_field, $input_id );
						}
					} else {
						$new_entry[ $target_field_id ] = $source_field->get_value_export( $entry, $source_field_id, true );
					}
				} else {
					$source_field_value            = $this->get_source_field_value( $entry, $source_field, $source_field_id );
					$new_entry[ $target_field_id ] = $this->get_target_field_value( $source_field_value, $target_field, $target_field_id );
				}
			} elseif ( $source_field_id == 'gf_custom' ) {
				$new_entry[ $target_field_id ] = GFCommon::replace_variables( $mapping['custom_value'], $form, $entry, false, false, false, 'text' );
			} else {
				$new_entry[ $target_field_id ] = $entry[ $source_field_id ];
			}

			return $new_entry;
		}

		/**
		 * Get the source field value.
		 *
		 * Returns the choice text instead of the unique value for choice based poll, quiz and survey fields.
		 *
		 * The source field choice unique value will not match the target field unique value.
		 *
		 * @param array $entry The entry being processed by this step.
		 * @param GF_Field $source_field The source field being processed.
		 * @param string $source_field_id The ID of the source field or input.
		 *
		 * @return string
		 */
		public function get_source_field_value( $entry, $source_field, $source_field_id ) {

			if ( ! isset( $entry[ $source_field_id ] ) ) {
				return '';
			}
			$field_value = $entry[ $source_field_id ];

			if ( in_array( $source_field->type, array( 'poll', 'quiz', 'survey' ) ) ) {
				if ( $source_field->inputType == 'rank' ) {
					$values = explode( ',', $field_value );
					foreach ( $values as &$value ) {
						$value = $this->get_source_choice_text( $value, $source_field );
					}

					return implode( ',', $values );
				}

				if ( $source_field->inputType == 'likert' && $source_field->gsurveyLikertEnableMultipleRows ) {
					list( $row_value, $field_value ) = rgexplode( ':', $field_value, 2 );
				}

				return $this->get_source_choice_text( $field_value, $source_field );
			}

			return $field_value;
		}

		/**
		 * Get the value to be set for the target field.
		 *
		 * Returns the target fields choice unique value instead of the source field choice text for choice based poll, quiz and survey fields.
		 *
		 * @param string $field_value The source field value.
		 * @param GF_Field $target_field The target field being processed.
		 * @param string $target_field_id The ID of the target field or input.
		 *
		 * @return string
		 */
		public function get_target_field_value( $field_value, $target_field, $target_field_id ) {
			if ( is_object( $target_field ) && in_array( $target_field->type, array( 'poll', 'quiz', 'survey' ) ) ) {
				if ( $target_field->inputType == 'rank' ) {
					$values = explode( ',', $field_value );
					foreach ( $values as &$value ) {
						$value = $this->get_target_choice_value( $value, $target_field );
					}

					return implode( ',', $values );
				}

				$field_value = $this->get_target_choice_value( $field_value, $target_field );

				if ( $target_field->inputType == 'likert' && $target_field->gsurveyLikertEnableMultipleRows ) {
					$row_value   = $target_field->get_row_id( $target_field_id );
					$field_value = sprintf( '%s:%s', $row_value, $field_value );
				}
			}

			return $field_value;
		}

		/**
		 * Gets the choice text for the supplied choice value.
		 *
		 * @param string $selected_choice The choice value from the source field.
		 * @param GF_Field $source_field The source field being processed.
		 *
		 * @return string
		 */
		public function get_source_choice_text( $selected_choice, $source_field ) {
			return $this->get_choice_property( $selected_choice, $source_field->choices, 'value', 'text' );
		}

		/**
		 * Gets the choice value for the supplied choice text.
		 *
		 * @param string $selected_choice The choice text from the source field.
		 * @param GF_Field $target_field The target field being processed.
		 *
		 * @return string
		 */
		public function get_target_choice_value( $selected_choice, $target_field ) {
			return $this->get_choice_property( $selected_choice, $target_field->choices, 'text', 'value' );
		}

		/**
		 * Helper to get the specified choice property for the selected choice.
		 *
		 * @param string $selected_choice The selected choice value or text.
		 * @param array $choices The field choices.
		 * @param string $compare_property The choice property the $selected_choice is to be compared against.
		 * @param string $return_property The choice property to be returned.
		 *
		 * @return string
		 */
		public function get_choice_property( $selected_choice, $choices, $compare_property, $return_property ) {
			if ( $selected_choice && is_array( $choices ) ) {
				foreach ( $choices as $choice ) {
					if ( $choice[ $compare_property ] == $selected_choice ) {
						return $choice[ $return_property ];
					}
				}
			}

			return $selected_choice;
		}

		/**
		 * Display the workflow detail box for this step.
		 *
		 * @param array $form The current form.
		 * @param array $args The page arguments.
		 */
		public function workflow_detail_box( $form, $args ) {
			?>
			<div>
				<?php

				$this->maybe_display_assignee_status_list( $args, $form );

				$assignee_status = $this->get_current_assignee_status();
				list( $role, $role_status ) = $this->get_current_role_status();
				$can_submit = $assignee_status == 'pending' || $role_status == 'pending';

				if ( $can_submit ) {
					$assignee_key = gravity_flow()->get_current_user_assignee_key();
					$assignee = new Gravity_Flow_Assignee( $assignee_key );
					$url  = $this->get_target_form_url( $this->submit_page, $assignee );
					$text = esc_html__( 'Open Form', 'gravityflowformconnector' );
					echo '<br /><div class="gravityflow-action-buttons">';
					echo sprintf( '<a href="%s" target="_blank" class="button button-large button-primary">%s</a><br><br>', $url, $text );
					echo '</div>';
				}

				?>
			</div>
			<?php
		}


		/**
		 * If applicable display the assignee status list.
		 *
		 * @param array $args The page arguments.
		 * @param array $form The current form.
		 */
		public function maybe_display_assignee_status_list( $args, $form ) {
			$display_step_status = (bool) $args['step_status'];

			/**
			 * Allows the assignee status list to be hidden.
			 *
			 * @param array $form
			 * @param array $entry
			 * @param Gravity_Flow_Step $current_step
			 */
			$display_assignee_status_list = apply_filters( 'gravityflow_assignee_status_list_form_submission', $display_step_status, $form, $this );
			if ( ! $display_assignee_status_list ) {
				return;
			}

			echo sprintf( '<h4 style="margin-bottom:10px;">%s (%s)</h4>', $this->get_name(), $this->get_status_string() );

			echo '<ul>';

			$assignees = $this->get_assignees();

			$this->log_debug( __METHOD__ . '(): assignee details: ' . print_r( $assignees, true ) );

			foreach ( $assignees as $assignee ) {
				$assignee_status = $assignee->get_status();

				$this->log_debug( __METHOD__ . '(): showing status for: ' . $assignee->get_key() );
				$this->log_debug( __METHOD__ . '(): assignee status: ' . $assignee_status );

				if ( ! empty( $assignee_status ) ) {

					$assignee_type = $assignee->get_type();
					$assignee_id   = $assignee->get_id();

					if ( $assignee_type == 'user_id' ) {
						$user_info    = get_user_by( 'id', $assignee_id );
						$status_label = $this->get_status_label( $assignee_status );
						echo sprintf( '<li>%s: %s (%s)</li>', esc_html__( 'User', 'gravityflowformconnector' ), $user_info->display_name, $status_label );
					} elseif ( $assignee_type == 'email' ) {
						$email        = $assignee_id;
						$status_label = $this->get_status_label( $assignee_status );
						echo sprintf( '<li>%s: %s (%s)</li>', esc_html__( 'Email', 'gravityflowformconnector' ), $email, $status_label );
					} elseif ( $assignee_type == 'role' ) {
						$status_label = $this->get_status_label( $assignee_status );
						$role_name    = translate_user_role( $assignee_id );
						echo sprintf( '<li>%s: (%s)</li>', esc_html__( 'Role', 'gravityflowformconnector' ), $role_name, $status_label );
						echo '<li>' . $role_name . ': ' . $assignee_status . '</li>';
					}
				}
			}

			echo '</ul>';

		}

		/**
		 * Get the status string, including icon (if complete).
		 *
		 * @return string
		 */
		public function get_status_string() {
			$input_step_status = $this->get_status();
			$status_str        = __( 'Pending Input', 'gravityflowformconnector' );

			if ( $input_step_status == 'complete' ) {
				$approve_icon = '<i class="fa fa-check" style="color:green"></i>';
				$status_str   = $approve_icon . __( 'Complete', 'gravityflowformconnector' );
			} elseif ( $input_step_status == 'queued' ) {
				$status_str = __( 'Queued', 'gravityflowformconnector' );
			}

			return $status_str;
		}

		/**
		 * Returns the URL for the target form.
		 *
		 * @param int|string $page_id
		 * @return string
		 */
		public function get_target_form_url( $page_id = null, $assignee = null ) {
			$args = array(
				'id' => $this->target_form_id,
				'workflow_parent_entry_id' => $this->get_entry_id(),
				'workflow_hash' => gravity_flow_form_connector()->get_workflow_hash( $this->get_entry_id(), $this ),
			);

			if ( $page_id == 'admin' ) {
				$args['page'] = 'gravityflow-submit';
			}

			return Gravity_Flow_Common::get_workflow_url( $args, $page_id, $assignee );
		}

		public function supports_expiration() {
			return true;
		}

		/**
		 * @param $text
		 * @param Gravity_Flow_Assignee $assignee
		 *
		 * @return mixed
		 */
		public function replace_variables( $text, $assignee ) {
			$text    = parent::replace_variables( $text, $assignee );
			$comment = rgpost( 'gravityflow_note' );
			$text    = str_replace( '{workflow_note}', $comment, $text );

			preg_match_all( '/{workflow_form_submission_url(:(.*?))?}/', $text, $matches, PREG_SET_ORDER );
			if ( is_array( $matches ) ) {
				foreach ( $matches as $match ) {
					$full_tag       = $match[0];
					$options_string = isset( $match[2] ) ? $match[2] : '';
					$options        = shortcode_parse_atts( $options_string );

					$args = shortcode_atts(
						array(
							'page_id' => $this->submit_page,
						), $options
					);

					$submission_url = $this->get_target_form_url( $args['page_id'], $assignee );
					$submission_url = esc_url_raw( $submission_url );

					$text = str_replace( $full_tag, $submission_url, $text );
				}
			}

			preg_match_all( '/{workflow_form_submission_link(:(.*?))?}/', $text, $matches, PREG_SET_ORDER );
			if ( is_array( $matches ) ) {
				foreach ( $matches as $match ) {
					$full_tag       = $match[0];
					$options_string = isset( $match[2] ) ? $match[2] : '';
					$options        = shortcode_parse_atts( $options_string );

					$form = $this->get_form();


					$a = shortcode_atts(
						array(
							'page_id' => $this->submit_page,
							'text'    => $form['title'],
						), $options
					);

					$submission_url = $this->get_target_form_url( $args['page_id'], $assignee );
					$submission_url  = esc_url_raw( $submission_url );
					$submission_link = sprintf( '<a href="%s">%s</a>', $submission_url, esc_html( $a['text'] ) );
					$text         = str_replace( $full_tag, $submission_link, $text );
				}
			}

			return $text;
		}

		/**
		 * Returns the choices for the Submit Page setting.
		 *
		 * @return array
		 */
		public function get_page_choices() {
			$choices = array(
				array(
					'label' => __( 'Default - WordPress Admin Dashboard: Workflow Submit Page', 'gravityflowformconnector' ),
					'value' => 'admin',
				),
			);

			$pages = get_pages();
			foreach( $pages as $page ) {
				$choices[] = array(
					'label' => $page->post_title,
					'value' => $page->ID,
				);
			}

			return $choices;
		}
	}

}
