=== Gravity Flow Form Connector Extension ===
Contributors: stevehenty
Tags: gravity forms, approvals, workflow
Requires at least: 4.0
Tested up to: 4.7.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create, update and link entries in Gravity Flow.

== Description ==

The Gravity Flow Form Connector Extension is an advanced extension for Gravity Flow.

Gravity Flow is a premium Add-On for [Gravity Forms](https://gravityflow.io/gravityforms)

Facebook: [Steven Henty](https://www.facebook.com/gravityflow.io)

= Requirements =

1. [Purchase and install Gravity Forms](https://gravityflow.io/gravityforms)
1. [Purchase and install Gravity Flow](https://gravityflow.io)
1. Wordpress 4.3+
1. Gravity Forms 1.9.4+
1. Gravity Flow 1.1.0.4+


= Support =
If you find any that needs fixing, or if you have any ideas for improvements, please get in touch:
https://gravityflow.io/contact/


== Installation ==

1.  Download the zipped file.
1.  Extract and upload the contents of the folder to /wp-contents/plugins/ folder
1.  Go to the Plugin management page of WordPress admin section and enable the 'Gravity Flow Form Connector Extension' plugin.

== Frequently Asked Questions ==

= Which license of Gravity Flow do I need? =
The Gravity Flow Form Connector Extension will work with any license of [Gravity Flow](https://gravityflow.io).


== ChangeLog ==

= 1.2 =
- Added the Store New Entry ID setting to the New Entry step settings.
- Added the gravityflowformconnector_update_entry_id filter to allow the target entry ID to be modified.
  Example:
  add_filter( 'gravityflowformconnector_update_entry_id', 'sh_gravityflowformconnector_update_entry_id', 10, 5);
  function sh_gravityflowformconnector_update_entry_id( $target_entry_id, $target_form_id, $entry, $form, $step ) {
      // Custom search for the target entry ID based on the value of field ID 4.
      $search_criteria['status'] = 'active';
      $search_criteria['field_filters'][] = array( 'key' => '2', 'value' => $entry['4'] );
      $entries = GFAPI::get_entries( $target_form_id, $search_criteria );
      // Return the ID of the first entry in the results.
      return $entries[0]['id'];
  }
- Added support for updating the same entry when the target and source forms are the same. Select Entry ID (Self) in the Entry ID field setting.
- Fixed an issue with the approval action of the Update Entry Step for entries created with the New Entry step.
- Fixed an issue with the field mappings which may affect some forms.
- Fixed an issue with the update step when triggered by a schedule or the expiration of a previous step where the approval or user input action does not complete.
- Fixed an issue with the update step where remote approval and user input steps can fail on some servers. Requires Gravity Flow 1.6.2-dev+.


= 1.1 =
- Added translations for Chinese (China) and Dutch (Netherlands).
- Added integration with the Gravity Flow Parent-Child Forms Extension; a parent form can now be selected for the 'Entry ID Field' setting on the 'Update an Entry' step.
- Added the Form Submission step.
- Fixed an issue with the value for choice based Poll, Quiz, and Survey fields in the new or updated entry.

= 1.0.1.2 =
- Added support for mapping the created_by field in the target entry.
- Fixed a fatal error which could occur when using the 'Update an Entry' step type and the 'Entry ID Field' setting was not configured.

= 1.0.1.1 =
- Added support for merge tag processing in the custom values fields of mappings.

= 1.0.1 =
- Added support for custom values in the mapping. Requires Gravity Flow 1.3.0.10.

= 1.0.0 =
All new!
