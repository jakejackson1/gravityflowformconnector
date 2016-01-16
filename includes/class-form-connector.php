<?php
/**
 * Gravity Flow Form Connector
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Extension
 * @copyright   Copyright (c) 2015, Steven Henty
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

// Make sure Gravity Forms is active and already loaded.
if ( class_exists( 'GFForms' ) ) {

	class Gravity_Flow_Form_Connector extends Gravity_Flow_Extension {

		private static $_instance = null;

		public $_version = GRAVITY_FLOW_FORM_CONNECTOR_VERSION;

		public $edd_item_name = GRAVITY_FLOW_FORM_CONNECTOR_EDD_ITEM_NAME;

		// The Framework will display an appropriate message on the plugins page if necessary
		protected $_min_gravityforms_version = '1.9.10';

		protected $_slug = 'gravityflowformconnector';

		protected $_path = 'gravityflowformconnector/formconnector.php';

		// Title of the plugin to be used on the settings page, form settings and plugins page.
		protected $_title = 'Form Connector Extension';

		// Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
		protected $_short_title = 'Form Connector';

		protected $_capabilities = array(
			'gravityflowformconnector_uninstall',
			'gravityflowformconnector_settings',
		);

		protected $_capabilities_app_settings = 'gravityflowformconnector_settings';
		protected $_capabilities_uninstall = 'gravityflowformconnector_uninstall';

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Gravity_Flow_Form_Connector();
			}

			return self::$_instance;
		}

		private function __clone() {
		} /* do nothing */

		public function upgrade( $previous_version ) {
			if ( ! empty( $previous_version ) && version_compare( '1.0-beta-2', $previous_version, '<' ) ) {
				$this->upgrade_steps();
			}
		}

		public function upgrade_steps() {
			$forms = GFAPI::get_forms();
			foreach ( $forms as $form ) {
				$feeds = gravity_flow()->get_feeds( $form['id'] );
				foreach ( $feeds as $feed ) {
					if ( $feed['meta']['step_type'] == 'form_connector' ) {
						if ( $feed['meta']['action'] == 'create' ) {
							$feed['meta']['step_type'] = 'new_entry';
						} else {
							$feed['meta']['step_type'] = 'update_entry';
						}
						gravity_flow()->update_feed_meta( $feed['id'], $feed['meta'] );
					}
				}
			}
		}
	}
}
