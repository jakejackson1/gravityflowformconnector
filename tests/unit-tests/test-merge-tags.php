<?php

/**
 * Testing the Gravity Flow merge tags.
 *
 * @group testsuite
 */
class Tests_Gravity_Flow_Form_Connector_Merge_Tags extends GF_UnitTestCase {

	/**
	 * @var int
	 */
	protected $form_id;

	/**
	 * @var Gravity_Flow_API
	 */
	protected $api;

	/**
	 * Creates a form and uses it to initialise the Gravity Flow API.
	 */
	public function setUp() {
		parent::setUp();

		$this->form_id = $this->factory->form->create();
		$this->api     = new Gravity_Flow_API( $this->form_id );
	}


	// # FORM SUBMISSION ----------------------------------------------------------------------------------------------


	/**
	 * Tests the text is not replaced when the merge tag is not found.
	 */
	public function test_workflow_form_submission_url_invalid_text() {
		$this->_add_form_submission_step();
		$entry = $this->_create_entry();
		$step  = $this->api->get_current_step( $entry );
		$args  = array(
			'step'  => $step,
			'entry' => $entry,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$text_in  = 'no matching {merge_tag} here';
		$text_out = $merge_tag->replace( $text_in );
		$this->assertEquals( $text_in, $text_out );
	}


	// # HELPERS ------------------------------------------------------------------------------------------------------


	/**
	 * Returns an array of query string arguments from the supplied URL.
	 *
	 * @param string $url The URL from the merge tag.
	 *
	 * @return array
	 */
	public function _parse_workflow_url( $url ) {
		$url_query_string = parse_url( str_replace( '&amp;', '&', $url ), PHP_URL_QUERY );
		parse_str( $url_query_string, $query_args );

		return $query_args;
	}

	/**
	 * Returns the assertion failure message.
	 *
	 * @param string $merge_tag The merge tag which was processed.
	 *
	 * @return string
	 */
	public function _get_message( $merge_tag ) {
		return 'Unexpected output for ' . $merge_tag;
	}

	/**
	 * Returns the requested merge tag object.
	 *
	 * @param string $name The merge tag name.
	 * @param array $args The merge tag init arguments.
	 *
	 * @return false|Gravity_Flow_Merge_Tag
	 */
	public function _get_merge_tag( $name, $args = array() ) {
		$args['form'] = $this->factory->form->get_form_by_id( $this->form_id );

		return Gravity_Flow_Merge_Tags::get( $name, $args );
	}

	/**
	 * Creates and returns a random entry.
	 *
	 * @return array|WP_Error
	 */
	public function _create_entry() {
		$form                           = $this->factory->form->get_form_by_id( $this->form_id );
		$random_entry_object            = $this->factory->entry->generate_random_entry_object( $form );
		$random_entry_object['form_id'] = $form['id'];
		$entry_id                       = $this->factory->entry->create( $random_entry_object );

		return $this->factory->entry->get_entry_by_id( $entry_id );
	}

	/**
	 * Creates a Form Submission type step.
	 *
	 * @param array $override_settings The additional step settings.
	 *
	 * @return mixed
	 */
	function _add_form_submission_step( $override_settings = array() ) {
		$default_settings = array(
			'step_name'                               => 'Form Submission',
			'step_type'                               => 'form_submission',
			'feed_condition_logic_conditional_logic'  => false,
			'feed_condition_conditional_logic_object' => array(),
			'type'                                    => 'select',
			'assignees'                               => array( 'user_id|1' ),
			'destination_complete'                    => 'next',
			'destination_rejected'                    => 'complete',
			'destination_approved'                    => 'next',
			'target_form_id'                          => '1',
			'submit_page'                             => 'admin',
		);

		$settings = wp_parse_args( $override_settings, $default_settings );

		return $this->api->add_step( $settings );
	}

	/**
	 * Creates an empty post and returns the ID.
	 *
	 * @return int|WP_Error
	 */
	public function _create_post() {
		return wp_insert_post( array( 'post_title' => 'test' ) );
	}

}
