<?php
/*
 * Purpose: Test the Form Connector with Local Form (New Entry / Update Entry / Delete Entry)
 */
use \Codeception\Util\Locator;

$I = new AcceptanceTester( $scenario );

$I->wantTo( 'Test the Form Connector with a submission step' );

// Submit the form
$I->amOnPage( '/0003-formconnector-source' );

$I->see( '0003 FormConnector - Source' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver

$I->attachFile( 'input[name=input_16]', 'gravityflow-logo.png' );
$I->click( '.add_list_item' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 1 ), 'ABC' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 2 ), 'DEF' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 3 ), 'GHI' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 4 ), '123' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 5 ), '456' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 6 ), '789' );
$I->attachFile( 'input[name=input_23]', 'gravityflow-logo.png' );
$I->selectOption( 'select[name="input_31[]"]', array( 'admin1 admin1', 'admin2 admin2', 'admin3 admin3' ) );

$I->click( [ 'css' => 'input[type=submit]' ]);

$I->waitForText( 'We will get in touch with you shortly.', 3 );

// Login to wp-admin
$I->loginAsAdmin();

// Go to Status
$I->amOnWorkflowPage( 'Status' );

// Verify Connected Form Values (Local)
$I->click( 'Form Submission Source' );
$I->waitForText( 'Status: Pending', 3 );

$I->see( '12345',                                '//table[1]/tbody/tr[2]/td' );
$I->see( 'The world is your oyster',             '//table[1]/tbody/tr[4]/td' );
$I->see( 'Newfoundland & Labrador',              '//table[1]/tbody/tr[6]/td' );
$I->see( 'ON',                                   '//table[1]/tbody/tr[8]/td' );
$I->see( '42',                                   '//table[1]/tbody/tr[10]/td' );
$I->see( 'Wednesday',                            '//table[1]/tbody/tr[12]/td' );
$I->see( 'Under 18',                             '//table[1]/tbody/tr[14]/td' );
$I->see( 'Rick Astley',                          '//table[1]/tbody/tr[18]/td' );
$I->see( date( 'm/d/Y' ),                        '//table[1]/tbody/tr[20]/td' );
$I->see( '04:20 pm',                             '//table[1]/tbody/tr[22]/td' );
$I->see( '(111) 867-5309',                       '//table[1]/tbody/tr[24]/td' );
$I->see( '10 Downing Street',                    '//table[1]/tbody/tr[26]/td' );
$I->see( 'United Kingdom',                       '//table[1]/tbody/tr[26]/td' );
$I->see( 'https://gravityflow.io',               '//table[1]/tbody/tr[28]/td' );
$I->see( 'rick@astley.com',                      '//table[1]/tbody/tr[30]/td' );
$I->see( 'gravityflow-logo',                     '//table[1]/tbody/tr[32]/td' );
$I->see( 'ABC',                                  '//table[1]/tbody/tr[34]/td' );
$I->see( 'DEF',                                  '//table[1]/tbody/tr[34]/td' );
$I->see( 'GHI',                                  '//table[1]/tbody/tr[34]/td' );
$I->see( '123',                                  '//table[1]/tbody/tr[34]/td' );
$I->see( '456',                                  '//table[1]/tbody/tr[34]/td' );
$I->see( '789',                                  '//table[1]/tbody/tr[34]/td' );

$I->waitForElement( 'a.button-primary', 3 );
$I->click( 'Open Form' );

$I->switchToNextTab();

$I->see( '0003 FormConnector - Destination' );

$I->fillField( 'input_1', '54321' );
$I->fillField( 'input_2', 'Modified textarea from update' );
$I->selectOption( 'select[name="input_3"]', 'Nunavut' );
$I->selectOption( 'select[name="input_4[]"]', array( 'Quebec', 'Yukon' ) );
$I->fillField( 'input_5', '24' );
$I->checkOption( 'input[name=input_6\\.1]' );
$I->checkOption( 'input[name=input_6\\.7]' );
$I->fillField( 'input_9.3', 'John' );
$I->fillField( 'input_9.6', 'Doe' );
$I->click( '.add_list_item' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 1 ), 'IHG' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 2 ), 'FED' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 3 ), 'CBA' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 4 ), '987' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 5 ), '654' );
$I->fillField( Locator::elementAt( 'input[name="input_17[]"]', 6 ), '321' );

$I->click( [ 'css' => 'input[type=submit]' ]);

$I->waitForText( 'We will get in touch with you shortly.', 3 );
$I->closeTab();
$I->reloadPage();
$I->waitForText( 'Status: Complete', 3 );

// Go to Status
$I->amOnWorkflowPage( 'Inbox' );

// Verify Form Submission Values
$I->click( 'Verify After Submission New Entry' );
$I->waitForText( 'Status: Pending', 3 );

$I->see( '54321',                                '//table[1]/tbody/tr[2]/td' );
$I->see( 'Modified textarea from update',        '//table[1]/tbody/tr[4]/td' );
$I->see( 'Nunavut',                              '//table[1]/tbody/tr[6]/td' );
$I->see( 'QB',                                   '//table[1]/tbody/tr[8]/td' );
$I->see( '24',                                   '//table[1]/tbody/tr[10]/td' );
$I->see( 'Sunday',                               '//table[1]/tbody/tr[12]/td' );
$I->see( 'John Doe',                             '//table[1]/tbody/tr[18]/td' );
$I->see( 'IHG',                                  '//table[1]/tbody/tr[32]/td' );
$I->see( 'FED',                                  '//table[1]/tbody/tr[32]/td' );
$I->see( 'CBA',                                  '//table[1]/tbody/tr[32]/td' );
$I->see( '987',                                  '//table[1]/tbody/tr[32]/td' );
$I->see( '654',                                  '//table[1]/tbody/tr[32]/td' );
$I->see( '321',                                  '//table[1]/tbody/tr[32]/td' );

$I->waitForElement( 'button[value=approved]', 3 );
$I->click( 'button[value=approved]' );
