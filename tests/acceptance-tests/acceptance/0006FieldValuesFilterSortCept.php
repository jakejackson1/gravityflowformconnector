<?php
/*
 * Purpose: Test the Form Connector with Update Field - Local + Remote by ID and Filter
 */
use \Codeception\Util\Locator;

$site_url = get_option( 'siteurl' );

$api_public_key = 'c1f1037e97';
$api_private_key = '4ac6e84ff06db56';

$api_settings = update_option('gravityformsaddon_gravityformswebapi_settings', array(
	'enabled'             => true,
	'public_key'          => $api_public_key,
	'private_key'         => $api_private_key,
	'impersonate_account' => 1,
) );

$feed_steps = array( 34, 36, 41, 42 );

foreach ( $feed_steps as $step_id ) {
	$remote_step = GFAPI::get_feeds( $step_id, 11 );

	if ( ! empty( $remote_step ) ) {
		$remote_meta = $remote_step[0]['meta'];
		$remote_meta['remote_site_url'] = $site_url;
		GFAPI::update_feed( $step_id, $remote_meta, 11 );
	}
}

$I = new AcceptanceTester( $scenario );

$I->wantTo( 'Test the Form Connector with a local form for field values (lookup) step' );

// Submit the Source Form for LocalByID Data
$I->amOnPage( '/0006-update-fields-source' );
$I->see( '0006 - Update Fields - Source' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver
$I->selectOption( 'select[name="input_5"]', 'LocalByID' );
$I->selectOption( 'input[name="input_6"]', 'LocalByID' );
$I->checkOption( 'input[name=input_7\\.1]' );
$I->fillField( 'input_8.1', 'Street-LocalByID' );
$I->fillField( 'input_8.3', 'City-LocalByID' );
$I->fillField( 'input_2', 'A-LocalByID' );
$I->scrollTo( [ 'css' => 'input[type=submit]' ] ); // needed for chromedriver
$I->click( 'Submit' );
$I->waitForText( 'We will get in touch with you shortly.', 3 );
$localByID = $I->grabTextFrom( '#new-entry-id' );

// Submit the Source Form for LocalByFilter Data
$I->amOnPage( '/0006-update-fields-source' );
$I->see( '0006 - Update Fields - Source' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver
$I->selectOption( 'select[name="input_5"]', 'LocalByFilter' );
$I->selectOption( 'input[name="input_6"]', 'LocalByFilter' );
$I->checkOption( 'input[name=input_7\\.2]' );
$I->fillField( 'input_8.1', 'Street-LocalByFilter' );
$I->fillField( 'input_8.3', 'City-LocalByFilter' );
$I->fillField( 'input_3', 'B-LocalByFilter' );
$I->scrollTo( [ 'css' => 'input[type=submit]' ] ); // needed for chromedriver
$I->click( 'Submit' );
$I->waitForText( 'We will get in touch with you shortly.', 3 );
$localByFilter = $I->grabTextFrom( '#new-entry-id' );

// Submit the Source Form for RemoteByID Data
$I->amOnPage( '/0006-update-fields-source' );
$I->see( '0006 - Update Fields - Source' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver
$I->selectOption( 'select[name="input_5"]', 'RemoteByID' );
$I->selectOption( 'input[name="input_6"]', 'RemoteByID' );
$I->checkOption( 'input[name=input_7\\.3]' );
$I->fillField( 'input_8.1', 'Street-RemoteByID' );
$I->fillField( 'input_8.3', 'City-RemoteByID' );
$I->fillField( 'input_4', 'C-RemoteByID' );
$I->scrollTo( [ 'css' => 'input[type=submit]' ] ); // needed for chromedriver
$I->click( 'Submit' );
$I->waitForText( 'We will get in touch with you shortly.', 3 );
$remoteByID = $I->grabTextFrom( '#new-entry-id' );

// Submit the Source Form for RemoteByFilter Data
$I->amOnPage( '/0006-update-fields-source' );
$I->see( '0006 - Update Fields - Source' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver
$I->selectOption( 'select[name="input_5"]', 'RemoteByFilter' );
$I->selectOption( 'input[name="input_6"]', 'RemoteByFilter' );
$I->checkOption( 'input[name=input_7\\.4]' );
$I->fillField( 'input_8.1', 'Street-RemoteByFilter' );
$I->fillField( 'input_8.3', 'City-RemoteByFilter' );
$I->fillField( 'input_9', 'D-RemoteByFilter' );
$I->scrollTo( [ 'css' => 'input[type=submit]' ] ); // needed for chromedriver
$I->click( 'Submit' );
$I->waitForText( 'We will get in touch with you shortly.', 3 );
$remoteByFilter = $I->grabTextFrom( '#new-entry-id' );

// Submit the Destination Form Data
$I->amOnPage( '/0006-update-fields-destination' );
$I->see( '0006 - Update Fields - Destination' );
$I->scrollTo( [ 'css' => '.gform_title' ] ); // needed for chromedriver
$I->fillField( 'input_4', $localByID );
$I->fillField( 'input_10', $remoteByID );
$I->scrollTo( [ 'css' => 'input[type=submit]' ] ); // needed for chromedriver
$I->click( 'Submit' );
$I->waitForText( 'We will get in touch with you shortly.', 3 );

// Login to wp-admin
$I->loginAsAdmin();

// Go to Inbox
$I->amOnWorkflowPage( 'Inbox' );

//Check Values - LocalByID
$I->click( 'Post Check - Local - By Entry ID' );
$I->waitForText( 'Status: Pending', 3 );
$I->see( 'LocalByID', '//table[1]/tbody/tr[2]/td' );
$I->see( 'LocalByID', '//table[1]/tbody/tr[4]/td' );
$I->see( 'LocalByID', '//table[1]/tbody/tr[6]/td' );
$I->see( 'Street-LocalByID', '//table[1]/tbody/tr[8]/td' );
$I->see( 'City-LocalByID', '//table[1]/tbody/tr[8]/td' );
$I->see( 'A-LocalByID', '//table[1]/tbody/tr[10]/td' );
$I->see( $localByID, '//table[1]/tbody/tr[18]/td' );
$I->click( 'button[value=approved]' );

//Check Values - LocalByFilter
$I->waitForText( 'Status: Pending', 3 );
$I->see( 'Post Check - Local - By Conditional Filter' );
$I->see( 'LocalByFilter', '//table[1]/tbody/tr[2]/td' );
$I->see( 'LocalByFilter', '//table[1]/tbody/tr[4]/td' );
$I->see( 'LocalByFilter', '//table[1]/tbody/tr[6]/td' );
$I->see( 'Street-LocalByFilter', '//table[1]/tbody/tr[8]/td' );
$I->see( 'City-LocalByFilter', '//table[1]/tbody/tr[8]/td' );
$I->see( 'B-LocalByFilter', '//table[1]/tbody/tr[12]/td' );
$I->see( $localByFilter, '//table[1]/tbody/tr[20]/td' );
$I->click( 'button[value=approved]' );

//GF Settings must set Public/Private API Keys before remote steps will complete

//Check Values - RemoteByID
$I->waitForText( 'Status: Pending', 3 );
$I->see( 'Post Check - Remote - By Entry ID' );
$I->see( 'RemoteByID', '//table[1]/tbody/tr[2]/td' );
$I->see( 'RemoteByID', '//table[1]/tbody/tr[4]/td' );
$I->see( 'RemoteByID', '//table[1]/tbody/tr[6]/td' );
$I->see( 'Street-RemoteByID', '//table[1]/tbody/tr[8]/td' );
$I->see( 'City-RemoteByID', '//table[1]/tbody/tr[8]/td' );
$I->see( 'C-RemoteByID', '//table[1]/tbody/tr[14]/td' );
$I->see( $remoteByID, '//table[1]/tbody/tr[22]/td' );
$I->click( 'button[value=approved]' );

//Check Values - RemoteByFilter
$I->waitForText( 'Status: Pending', 3 );
$I->see( 'Post Check - Remote - By Conditional Filter' );
$I->see( 'RemoteByFilter', '//table[1]/tbody/tr[2]/td' );
$I->see( 'RemoteByFilter', '//table[1]/tbody/tr[4]/td' );
$I->see( 'RemoteByFilter', '//table[1]/tbody/tr[6]/td' );
$I->see( 'Street-RemoteByFilter', '//table[1]/tbody/tr[8]/td' );
$I->see( 'City-RemoteByFilter', '//table[1]/tbody/tr[8]/td' );
$I->see( 'D-RemoteByFilter', '//table[1]/tbody/tr[16]/td' );
$I->see( $remoteByFilter, '//table[1]/tbody/tr[24]/td' );
$I->click( 'button[value=approved]' );

//Check Values - LocalSortFilter
$I->waitForText( 'Status: Pending', 3 );
$I->see( 'Post Local - Conditional Sort Check' );
$I->see( $localByFilter, '//table[1]/tbody/tr[26]/td' );
$I->see( $remoteByID, '//table[1]/tbody/tr[28]/td' );
$I->click( 'button[value=approved]' );

//Check Values - RemoteSortFilter
$I->waitForText( 'Status: Pending', 3 );
$I->see( 'Post Remote - Conditional Sort Check' );
$I->see( $localByID, '//table[1]/tbody/tr[30]/td' );
$I->see( $remoteByFilter, '//table[1]/tbody/tr[32]/td' );
$I->click( 'button[value=approved]' );
