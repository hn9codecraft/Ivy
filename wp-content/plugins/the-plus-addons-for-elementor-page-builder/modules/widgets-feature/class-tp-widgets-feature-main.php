<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://posimyth.com/
 * @since      5.6.7
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TP_Widgets_Feature_Main' ) ) {

	/**
	 * It is Main Class for load all widet feature.
	 *
	 * @since 5.6.7
	 */
	class TP_Widgets_Feature_Main {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */private static $instance;
		

		/**
		 *  Initiator
		 *
		 *  @since 5.6.7
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Define the core functionality of the plugin.
		 *
		 * @since 5.6.7
		 */
		public function __construct() {
			$this->tp_get_widgets();
		}

		/**
		 * Manage Widget feature ajax.
		 *
		 * @since 5.6.7
		 */
		public function tp_get_widgets() {

			$elements = l_theplus_get_option( 'general', 'check_elements' );

			if ( ! empty( $elements ) ) {

				if( in_array( 'tp_plus_form', $elements ) ){
					require_once L_THEPLUS_PATH . "modules/widgets-feature/class-tp-form-handler.php";
				}

				foreach ( $elements as $key => $value ) {

					if( 'tp_blog_listout' === $value ) {
						require_once L_THEPLUS_PATH . "modules/widgets-feature/class-tp-load-more.php";
				    }

					if ( 'tp_accordion' === $value || 'tp_carousel_anything' === $value || 'tp_countdown' === $value || 'tp_coupon_code' === $value || 'tp_dynamic_device' === $value || 'tp_dynamic_listing' === $value || 'tp_header_extras' === $value || 'tp_mobile_menu' === $value || 'tp_navigation_menu_lite' === $value || 'tp_navigation_menu' === $value || 'tp_off_canvas' === $value || 'tp_page_scroll' === $value ||  'tp_product_listout' === $value || 'tp_protected_content' === $value || 'tp_style_list' === $value || 'tp_switcher' === $value || 'tp_tabs_tours' === $value || 'tp_timeline' === $value || 'tp_unfold' === $value || 'tp_wp_login_register' === $value || 'tp_wp_quickview' === $value ) {
						include L_THEPLUS_PATH . "modules/widgets-feature/template-editor/class-tp-create-template.php";
					}

				}
			}
		}
	}

	return TP_Widgets_Feature_Main::get_instance();
}
