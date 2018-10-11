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

	/**
	 * Tests that the workflow_form_submission_url merge tag outputs the expected URL.
	 */
	public function test_workflow_form_submission_url() {
		$this->_add_form_submission_step();
		$entry    = $this->_create_entry();
		$step     = $this->api->get_current_step( $entry );
		$assignee = $step->get_assignee( 'user_id|1' );
		$args     = array(
			'step'     => $step,
			'entry'    => $entry,
			'assignee' => $assignee,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		// Verify the merge tag is replaced with the admin URL.
		$text_in  = "{workflow_form_submission_url}";
		$text_out = $merge_tag->replace( $text_in );
		$this->assertStringStartsWith( admin_url( 'admin.php' ), $text_out, $this->_get_message( $text_in ) );

		// Get the query string arguments.
		$actual_query_args = $this->_parse_workflow_url( $text_out );

		// Verify the query args are correct.
		$expected_query_args = array(
			'id'                       => $this->form_id,
			'workflow_parent_entry_id' => $entry['id'],
			'workflow_hash'            => gravity_flow_form_connector()->get_workflow_hash( $entry['id'], $step ),
			'page'                     => 'gravityflow-submit'
		);
		$this->assertEquals( $expected_query_args, $actual_query_args, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_url merge tag outputs the expected URL when using the page_id attribute.
	 */
	public function test_workflow_form_submission_url_page_id_attr() {
		$this->_add_form_submission_step();
		$entry    = $this->_create_entry();
		$step     = $this->api->get_current_step( $entry );
		$assignee = $step->get_assignee( 'user_id|1' );
		$args     = array(
			'step'     => $step,
			'entry'    => $entry,
			'assignee' => $assignee,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$post_id = $this->_create_post();

		// Verify the merge tag is replaced with the URL for the specified front-end page.
		$text_in  = "{workflow_form_submission_url: page_id='{$post_id}'}";
		$text_out = $merge_tag->replace( $text_in );
		$this->assertStringStartsWith( get_permalink( $post_id ), $text_out, $this->_get_message( $text_in ) );

		// Get the query string arguments.
		$actual_query_args = $this->_parse_workflow_url( $text_out );

		// Verify the query args are correct.
		$expected_query_args = array(
			'id'                       => $this->form_id,
			'workflow_parent_entry_id' => $entry['id'],
			'workflow_hash'            => gravity_flow_form_connector()->get_workflow_hash( $entry['id'], $step ),
			'p'                        => $post_id
		);
		$this->assertEquals( $expected_query_args, $actual_query_args, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_url token merge tag does not output content when the step and assignee are not passed.
	 */
	public function test_workflow_form_submission_url_token_attr_no_step_no_assignee() {
		$entry = $this->_create_entry();
		$args  = array(
			'entry' => $entry,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$text_in  = '{workflow_form_submission_url: token=true}';
		$text_out = $merge_tag->replace( $text_in );
		$this->assertEmpty( $text_out, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_url merge tag does not output content when the token and step attributes are used and the assignee is not passed.
	 */
	public function test_workflow_form_submission_url_token_attr_step_attr_no_assignee() {
		$step_id = $this->_add_form_submission_step();
		$entry   = $this->_create_entry();
		$args    = array(
			'entry' => $entry,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$text_in  = "{workflow_form_submission_url: token=true step='{$step_id}'}";
		$text_out = $merge_tag->replace( $text_in );
		$this->assertEmpty( $text_out, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_url merge tag does not output content when the token and assignee attributes are used and the step is not passed.
	 */
	public function test_workflow_form_submission_url_token_attr_assignee_attr_no_step() {
		$entry = $this->_create_entry();
		$args  = array(
			'entry' => $entry,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$text_in  = "{workflow_form_submission_url: token=true assignee='user_id|1'}";
		$text_out = $merge_tag->replace( $text_in );
		$this->assertEmpty( $text_out, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_url merge tag outputs the expected URL when using the token attribute.
	 */
	public function test_workflow_form_submission_url_token_attr() {
		$this->_add_form_submission_step();
		$entry    = $this->_create_entry();
		$step     = $this->api->get_current_step( $entry );
		$assignee = $step->get_assignee( 'user_id|1' );
		$args     = array(
			'step'     => $step,
			'entry'    => $entry,
			'assignee' => $assignee,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$text_in  = '{workflow_form_submission_url: token=true}';
		$text_out = $merge_tag->replace( $text_in );
		$this->assertNotEmpty( $text_out, $this->_get_message( $text_in ) );

		// Get the query string arguments.
		$actual_query_args = $this->_parse_workflow_url( $text_out );

		// Verify the access token is present.
		$access_token = rgar( $actual_query_args, 'gflow_access_token' );
		$this->assertNotEmpty( $access_token, $this->_get_message( $text_in ) );

		// Verify the access token belongs to the correct assignee.
		$actual_assignee = gravity_flow()->parse_token_assignee( gravity_flow()->decode_access_token( $access_token ) );
		$this->assertEquals( $assignee->get_key(), $actual_assignee->get_key() );

		// Remove the access token and verify the remaining arguments are correct.
		unset( $actual_query_args['gflow_access_token'] );
		$expected_query_args = array(
			'id'                       => $this->form_id,
			'workflow_parent_entry_id' => $entry['id'],
			'workflow_hash'            => gravity_flow_form_connector()->get_workflow_hash( $entry['id'], $step ),
			'page'                     => 'gravityflow-submit'
		);
		$this->assertEquals( $expected_query_args, $actual_query_args, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_url merge tag outputs the expected URL when using the token, assignee, and step attributes.
	 */
	public function test_workflow_form_submission_url_token_attr_assignee_attr_step_attr() {
		$step_id  = $this->_add_form_submission_step();
		$entry    = $this->_create_entry();
		$step     = $this->api->get_current_step( $entry );
		$assignee = $step->get_assignee( 'user_id|1' );
		$args     = array(
			'entry' => $entry,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		$text_in  = "{workflow_form_submission_url: token=true assignee='user_id|1' step='{$step_id}'}";
		$text_out = $merge_tag->replace( $text_in );
		$this->assertNotEmpty( $text_out, $this->_get_message( $text_in ) );

		// Get the query string arguments.
		$actual_query_args = $this->_parse_workflow_url( $text_out );

		// Verify the access token is present.
		$access_token = rgar( $actual_query_args, 'gflow_access_token' );
		$this->assertNotEmpty( $access_token, $this->_get_message( $text_in ) );

		// Verify the access token belongs to the correct assignee.
		$actual_assignee = gravity_flow()->parse_token_assignee( gravity_flow()->decode_access_token( $access_token ) );
		$this->assertEquals( $assignee->get_key(), $actual_assignee->get_key() );

		// Remove the access token and verify the remaining arguments are correct.
		unset( $actual_query_args['gflow_access_token'] );
		$expected_query_args = array(
			'id'                       => $this->form_id,
			'workflow_parent_entry_id' => $entry['id'],
			'workflow_hash'            => gravity_flow_form_connector()->get_workflow_hash( $entry['id'], $step ),
			'page'                     => 'gravityflow-submit'
		);
		$this->assertEquals( $expected_query_args, $actual_query_args, $this->_get_message( $text_in ) );
	}

	/**
	 * Tests that the workflow_form_submission_link merge tags output the expected content.
	 */
	public function test_workflow_form_submission_link() {
		$this->_add_form_submission_step();
		$entry    = $this->_create_entry();
		$step     = $this->api->get_current_step( $entry );
		$assignee = $step->get_assignee( 'user_id|1' );
		$args     = array(
			'step'     => $step,
			'entry'    => $entry,
			'assignee' => $assignee,
		);

		$merge_tag = $this->_get_merge_tag( 'workflow_form_submission_url', $args );

		// Verify the merge tag was replaced.
		$text_in  = '{workflow_form_submission_link}';
		$text_out = $merge_tag->replace( $text_in );
		$this->assertNotEmpty( $text_out );

		// Verify the link HTML matches the expected pattern.
		$this->assertRegExp( '/<a(.*)href="([^"]*)">Standard test<\/a>/', $text_out, $this->_get_message( $text_in ) );

		// Verify the merge tag was replaced.
		$text_in  = '{workflow_form_submission_link: text=testing}';
		$text_out = $merge_tag->replace( $text_in );
		$this->assertNotEmpty( $text_out, $text_in );

		// Verify the link HTML matches the expected pattern.
		$this->assertRegExp( '/<a(.*)href="([^"]*)">testing<\/a>/', $text_out, $this->_get_message( $text_in ) );
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
			'target_form_id'                          => $this->form_id,
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
