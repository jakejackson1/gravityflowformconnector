<?php // Make sure Gravity Forms is active and already loaded.
if ( class_exists( 'GFForms' ) ) {
	GFForms::include_addon_framework();
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

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Gravity_Flow_Form_Connector();
			}

			return self::$_instance;
		}

		private function __clone() {
		} /* do nothing */

	}
}
