<?php
/*
 * Purpose: Test the Form Connector with Form (New Entry / Update Entry / Field Values Local + Remote)
 */
use \Codeception\Util\Locator;

$I = new AcceptanceTester( $scenario );

$I->wantTo( 'Test the Form Connector with a local form for field values (lookup) step' );

// Submit the form
$I->amOnPage( '/0005-field-values-active' );

$I->see( '0005 Field Values - Active' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver

$I->scrollTo( [ 'css' => 'input[type=submit]' ] ); // needed for chromedriver
$I->click( 'Submit' );

$I->waitForText( 'We will get in touch with you shortly.', 3 );

// Login to wp-admin
$I->loginAsAdmin();

// Go to Inbox
$I->amOnWorkflowPage( 'Inbox' );

//Move Active - Complete Update Entry Step
$I->click( 'StopCheck - Before Update Entry' );
$I->waitForText( 'Status: Pending', 3 );
$I->see( '12345', '//table[1]/tbody/tr[4]/td' );
$I->click( 'button[value=approved]' );

$I->amOnWorkflowPage( 'Inbox' );

// Verify Connected Form Values (Local)
$I->click( 'Passive Approval' );
$I->waitForText( 'Status: Pending', 3 );
$I->see( '12345', '//table[1]/tbody/tr[4]/td' );
$I->see( '12345', '//table[1]/tbody/tr[6]/td' );

$I->amOnWorkflowPage( 'Inbox' );

//Move Active - Complete Update Local Field Values Step
$I->click( 'StopCheck - Before Local Field Values Update' );
$I->waitForText( 'Status: Pending', 3 );
$I->click( 'button[value=approved]' );
$I->waitForText( 'Entry Approved', 3 );
$I->see( '12345', '//table[1]/tbody/tr[4]/td' );
$I->see( '42',    '//table[1]/tbody/tr[6]/td' );

$I->amOnWorkflowPage( 'Inbox' );

// Update Passive Form Values
$I->click( 'Passive Approval' );
$I->waitForText( 'Status: Pending', 3 );
$I->click( 'button[value=approved]' );
$I->fillField( 'input_5', '54321' );
$I->scrollTo( [ 'css' => '.gravityflow-step-user_input' ] ); // needed for chromedriver
$I->click( '#gravityflow_update_button' );
$I->waitForText( 'Entry updated', 3 );

$I->amOnWorkflowPage( 'Inbox' );

//Move Active - Complete Update Remote Field Values Step
$I->click( 'StopCheck - Before Remote Field Values Update' );

$I->waitForText( 'Status: Pending', 3 );
$I->click( 'button[value=approved]' );
$I->waitForText( 'Entry Approved', 3 );
$I->see( '12345', '//table[1]/tbody/tr[4]/td' );
$I->see( '42',    '//table[1]/tbody/tr[6]/td' );
