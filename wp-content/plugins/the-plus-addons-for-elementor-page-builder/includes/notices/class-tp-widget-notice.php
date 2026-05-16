<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      5.3.3
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\WidgetNotice;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Widget_Notice' ) ) {

	/**
	 * This class used for only load widget notice
	 *
	 * @since 5.3.3
	 */
	class Tp_Widget_Notice {

		/**
		 * Instance
		 *
		 * @since 5.3.3
		 * @access private
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 5.3.3
		 * @access public
		 * @static
		 * @return instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Perform some compatibility checks to make sure basic requirements are meet.
		 *
		 * @since 5.2.3
		 * @version 5.3.3
		 */
		public function __construct() {
			if ( defined( 'THEPLUS_VERSION' ) ) {
				add_action( 'admin_notices', array( $this, 'l_theplus_elementor_cache_notice' ) );
				add_action( 'admin_notices', array( $this, 'tpae_dashboard_notice' ) );
				add_action( 'admin_notices', array( $this, 'tpae_widget_dashboard_notice' ) );
			}
		}

		/**
		 * Version Update Notice
		 *
		 * @since 5.4.0
		 * @version 5.4.0
		 * @access public
		 */
		public function l_theplus_elementor_cache_notice() {
			if ( is_admin() && version_compare( THEPLUS_VERSION, '5.5.3', '<' ) ) {
				echo '<div class="notice notice-error tp-update-notice is-dismissible"><p>' . esc_html__( 'This is major Version Release. That is required to have latest version of The Plus Addons for Elementor Pro 5.5.3 Install Latest version Now.', 'tpebl' ) . '</p></div>';
			}
		}

		/**
		 * Version Update Notice
		 *
		 * @since 6.0.0
		 */
		public function tpae_dashboard_notice() {
			if ( version_compare( THEPLUS_VERSION, '6.0.0', '<' ) ) {
				echo '<div class="notice notice-error tp-update-notice is-dismissible"><p>' . esc_html__( 'This is major Version Release. That is required to have latest version of The Plus Addons for Elementor Pro 6.0.0 Install Latest version Now.', 'tpebl' ) . '</p></div>';
			}
		}

		/**
		 * Version Update Notice
		 *
		 * @since 6.1.0
		 */
		public function tpae_widget_dashboard_notice() {
			if ( version_compare( THEPLUS_VERSION, '6.1.0', '<' ) ) {
				echo '<div class="notice notice-error tp-update-notice is-dismissible"><p>' . esc_html__( 'This is major Version Release. That is required to have latest version of The Plus Addons for Elementor Pro 6.1.0 Install Latest version Now.', 'tpebl' ) . '</p></div>';
			}
		}
	}

	Tp_Widget_Notice::instance();
}
