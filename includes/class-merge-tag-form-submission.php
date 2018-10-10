<?php

/**
 * Gravity Flow Form Submission Merge Tag
 *
 * @package     GravityFlow
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Class Gravity_Flow_Merge_Tag_Workflow_Url
 *
 * @since 1.4.2
 */
class Gravity_Flow_Merge_Tag_Form_Submission_Url extends Gravity_Flow_Merge_Tag_Assignee_Base {

	/**
	 * The name of the merge tag.
	 *
	 * @since 1.4.2
	 *
	 * @var string
	 */
	public $name = 'workflow_form_submission_url';

	/**
	 * The regular expression to use for the matching.
	 *
	 * @since 1.4.2
	 *
	 * @var string
	 */
	protected $regex = '/{workflow_form_submission_(url|link)(:(.*?))?}/';

	/**
	 * Replace the {workflow_form_submission_url} and {workflow_form_submission_link} merge tags.
	 *
	 * @since 1.4.2
	 *
	 * @param string $text The text being processed.
	 *
	 * @return string
	 */
	public function replace( $text ) {

		$matches = $this->get_matches( $text );

		if ( ! empty( $matches ) ) {

			foreach ( $matches as $match ) {
				$full_tag       = $match[0];
				$type           = $match[1];
				$options_string = isset( $match[3] ) ? $match[3] : '';

				$a = $this->get_attributes( $options_string, array(
					'page_id'  => '',
					'text'     => '',
					'token'    => false,
					'assignee' => '',
					'step'     => '',
				) );

				$original_step = $this->step;

				if ( ! empty( $a['step'] ) ) {
					$this->step = gravity_flow()->get_step( $a['step'], $this->entry );
				}

				if ( ! ( $this->step && $this->step instanceof Gravity_Flow_Step_Form_Submission ) ) {
					$text       = str_replace( $full_tag, '', $text );
					$this->step = $original_step;
					continue;
				}

				if ( empty( $a['page_id'] ) ) {
					$a['page_id'] = $this->step->submit_page;
				}

				if ( $type == 'link' && empty( $a['text'] ) ) {
					$target_form_id = $this->step->get_setting( 'target_form_id' );
					$form           = GFAPI::get_form( $target_form_id );
					$a['text']      = $form['title'];
				}

				$original_assignee = $this->assignee;

				if ( ! empty( $a['assignee'] ) ) {
					$this->assignee = $this->step->get_assignee( $a['assignee'] );
				}

				if ( empty( $this->assignee ) ) {
					$text           = str_replace( $full_tag, '', $text );
					$this->assignee = $original_assignee;
					continue;
				}

				$token = $this->get_workflow_url_access_token( $a );

				$submission_url = $this->step->get_target_form_url( $a['page_id'], $this->assignee, $token );

				$url = $this->format_value( $submission_url );

				if ( $type == 'link' ) {
					$url = sprintf( '<a href="%s">%s</a>', $url, $a['text'] );
				}

				$text = str_replace( $full_tag, $url, $text );

				$this->assignee = $original_assignee;

				$this->step = $original_step;
			}
		}

		return $text;
	}

	/**
	 * Get the access token if the token is required by the attributes.
	 *
	 * @since 1.4.2
	 *
	 * @param array $a The merge tag attributes.
	 *
	 * @return string
	 */
	private function get_workflow_url_access_token( $a ) {
		$force_token = $a['token'];
		$token       = '';

		if ( $this->assignee && $force_token ) {
			$token = $this->get_token();
		}

		return $token;
	}

}

Gravity_Flow_Merge_Tags::register( new Gravity_Flow_Merge_Tag_Form_Submission_Url );
