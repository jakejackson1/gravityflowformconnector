<?php
/*
 * Purpose: Test the {workflow_form_submission_link} merge tag for the Form Submission type step in the form confirmation message.
 */

$I = new AcceptanceTester( $scenario );

$I->wantTo( 'Test the {workflow_form_submission_link} merge tag in the form confirmation message.' );

// Make sure we're logged out
$I->logOut();
$I->resetCookie( 'gflow_access_token' );

// Submit the form
$I->amOnPage( '/0004-form-submission-merge-tag-source' );
$I->see( '0004 Form Submission Merge Tag Source' );
$I->seeInField( 'Source', 'The value' );
$I->seeInField( 'Email', 'test@test.test' );
$I->click( [ 'css' => 'input[type=submit]' ]);
// Verify the merge tag included a link in the confirmation message.
$I->waitForText( 'We will get in touch with you shortly.', 3 );
$I->dontSee( "Form Submission Merge Tag: {workflow_form_submission_link: assignee='email_field|2'}" );
$I->see( 'Form Submission Merge Tag:' );
$I->seeLink( '0004 Form Submission Merge Tag Target' );

// Click the link and verify the submit page loads with the target form populated.
$I->click( '0004 Form Submission Merge Tag Target' );
$I->waitForElement( [ 'css' => 'input[type=submit]' ] );

$I->seeInField( 'Target', 'The value' );
