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

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Gravity_Flow_Form_Connector();
			}

			return self::$_instance;
		}

		private function __clone() {
		} /* do nothing */


		public function init(){
			parent::init();
			add_action( 'gravityflow_workflow_detail_sidebar', array( $this, 'action_gravityflow_entry_detail' ), 10, 2 );
		}

		public function init_ajax(){
			parent::init_ajax();
			add_action( 'wp_ajax_gravtiyflowformconnector_get_form', array( $this, 'ajax_get_form' ) );
		}

		public function get_entry_meta( $entry_meta, $form_id ){

			$parent_form_ids = $this->get_parent_form_ids( $form_id );

			foreach ( $parent_form_ids as $parent_form_id ) {
				$entry_meta[ 'workflow_parent_form_id_' . $parent_form_id . '_entry_id' ] = array(
					'label'                      => __( 'Parent Entry ID', 'gravityflowformconnector' ),
					'is_numeric'                 => true,
					'update_entry_meta_callback' => array( $this, 'update_entry_meta_callback' ),
					'is_default_column'          => true, // this column will be displayed by default on the entry list
					'filter'                     => array(
						'operators' => array( 'is' ),
					)
				);
			}


			return $entry_meta;
		}

		public function update_entry_meta_callback( $key, $entry, $form ){
			if ( isset( $_REQUEST['workflow_parent_entry_id'] ) ) {
				return absint( $_REQUEST['workflow_parent_entry_id'] );
			}
			return '';
		}

		public function form_settings_fields( $form ){
			$forms = GFAPI::get_forms();
			$settings_fields = $choices = array();
			foreach ( $forms  as $form ) {
				$choices[] = array( 'label' => $form['title'], 'name' => 'parent_form_' . $form['id'] );
			}
			$settings_fields[] = array(
				'name' => 'parent_form_' . $form['id'],
				'label' => esc_html__( 'Parent Forms', 'gravityflowformconnector' ),
				'type' => 'checkbox',
				'choices' => $choices,
			);
			return array(
				array(
					'title'  => esc_html__( 'Form Connector', 'gravityflow' ),
					'fields' => $settings_fields,
				),
			);
		}

		public function action_gravityflow_entry_detail( $form, $entry ) {

			$connected_forms = GFAPI::get_forms();

			$child_forms = array();

			foreach ( $connected_forms as $connected_form ) {
				$settings = $this->get_form_settings( $connected_form );
				if ( rgar( $settings, 'parent_form_' . $form['id'] ) ) {
					$child_forms[ $connected_form['id'] ] = array( 'title' => $connected_form['title'] );
				}
			}

			if ( empty( $child_forms ) ) {
				return;
			}

			$page_size = 20;
			foreach ( $child_forms as $child_form_id => &$child_form  ) {
				$search_criteria = array(
					'status' => 'active',
					'field_filters' => array(
						array( 'key' => 'workflow_parent_form_id_' . $form['id'] . '_entry_id', 'value' => $entry['id'] ),
					)
				);
				$sorting = array( 'key' => 'date_created', 'direction' => 'DESC' );
				$paging          = array( 'offset' => 0, 'page_size' => $page_size );

				$entries = GFAPI::get_entries( $child_form_id, $search_criteria, $sorting, $paging, $total_count );

				$title = rgar( $child_form, 'title' );
				$field_id = 'parent_form_' . $child_form_id;
				$view_all_url = admin_url( sprintf( 'admin.php?page=gravityflow-status&form-id=%d&f[0]=%s&o[0]=is&v[0]=%d', $child_form_id, $field_id, $entry['id'] ) );
				$count_link = $total_count > $page_size ? sprintf( '<a href="%s" />%s (%d)</a>', $view_all_url, esc_html__( 'View all', 'gravityflow' ), $total_count ) : '';
				$form_url = admin_url( 'admin-ajax.php' ) . '?action=gravtiyflowformconnector_get_form&form_id=' . $child_form_id . '&workflow_parent_entry_id=' . $entry['id'];
				?>

			<div class="postbox">
				<h3 class="hndle" style="cursor:default;">
					<span><?php echo esc_html( $title ); ?></span>
					<span style="float:right"><a href="<?php echo $form_url; ?>&TB_iframe=true&width=600&height=550" class="thickbox"><i class="fa fa-plus gravityflow-connected-form-new-entry" data-form_url="<?php echo $form_url; ?>"></i></a></span>
				</h3>

				<div id="submitcomment" class="submitbox">
					<div id="minor-publishing" style="padding:10px;">
						<div class="gravityflow-detail-form-count">
							<table>
								<thead>
									<th>
										<?php esc_html_e( 'Date' ) ?>
									</th>
									<th>
										<?php esc_html_e( 'Submitter' ) ?>
									</th>
								</thead>
								<tbody>
								<?php
								foreach ( $entries as $entry ) {
									$entry_url = add_query_arg( array( 'lid' => absint( $entry['id'] ) ) );

									$user_id = $entry['created_by'];
									if ( $user_id ) {
										$user         = get_user_by( 'id', $user_id );
										$display_name = $user->display_name;
									} else {
										$display_name = $entry['ip'];
									}
									?>
									<tr>
										<td>
											<a href="<?php echo esc_url( $entry_url ) ?>"><?php echo GFCommon::format_date( $entry['date_created'] ); ?></a>
										</td>
										<td>
											<a href="<?php echo esc_url( $entry_url ) ?>"><?php echo esc_html( $display_name ); ?></a>
										</td>
									</tr>
									<?php
								}
								?>
								</tbody>

							</table>
							<?php echo $count_link ?>
						</div>
					</div>
				</div>
			</div>

				<?php add_thickbox(); ?>


			<?php
			}

		}

		public function ajax_get_form(){
			$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

			$field_id = sanitize_text_field( rgget( 'field_id' ) );

			$entry_id = absint( rgget( 'entry_id' ) );

			$field_values = array( $field_id => $entry_id );

			gravity_form_enqueue_scripts( $form_id, true );

			$is_admin = isset( $_GET['is_admin'] );
			if ( $is_admin ) {
				wp_enqueue_style( 'common', site_url() . '/wp-admin/css/common.css', array(), $this->_version );
			} else {
				wp_enqueue_style( 'common', get_stylesheet_directory_uri() . '/style.css', array(), $this->_version );
			}

			wp_print_styles();
			wp_print_scripts();
			// Render an AJAX-enabled form.
			// https://www.gravityhelp.com/documentation/article/embedding-a-form/#function-call
			$html = gravity_form( $form_id, true, false, false, $field_values, true, 1, false );
			printf( "<div id='gravityflow-child-form-wrapper' style='padding:10px;'>%s</div>", $html );
			die();
		}

		public function get_parent_form_ids( $form_id ){
			$parent_form_ids = array();

			$form = GFAPI::get_form( $form_id );
			$settings = $this->get_form_settings( $form );

			if ( empty( $settings ) || ! is_array( $settings ) ) {
				return $parent_form_ids;
			}
			foreach ( $settings as $key => $setting ) {
				if ( strstr( $key, 'parent_form_' ) && $setting == 1 ) {
					$form_id = str_replace( 'parent_form_', '', $key );
					$parent_form_ids[] = absint( $form_id );
				}
			}

			return $parent_form_ids;
		}




	}


}
