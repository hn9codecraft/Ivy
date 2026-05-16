<?php
/**
 * The file that defines pro widgets
 *
 * @link       https://posimyth.com/
 * @since      6.4.1
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */

use Elementor\Widgets_Manager;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TP_Widgets_Promotion_Main' ) ) {

	/**
	 * It is Main Class for load all widet feature.
	 *
	 * @since 6.4.1
	 */
	class TP_Widgets_Promotion_Main {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;


		/**
		 *  Initiator
		 *
		 *  @since6.4.1
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
		 * @since 6.4.1
		 */
		public function __construct() {
			if ( ! defined( 'THEPLUS_VERSION' ) ) {
				$this->init();
			}
			$this->tp_load_ajax_files();
		}

		/**
		 * Initalize integration hooks
		 *
		 * @return void
		 */
		public function init() {
			add_action( 'elementor/widgets/register', array( $this, 'add_widgets' ) );
		}

		/**
		 * Add new controls.
		 *
		 * @param  object $widgets_manager Controls manager instance.
		 * @return void
		 */
		public function add_widgets( $widgets_manager ) {
			if ( ! defined( 'THEPLUS_VERSION' ) ) {
				include L_THEPLUS_PATH . 'modules/widget-promotion/tp-widget-promotion/class-tp-widget-promotion.php';
			}
		}

		public function tp_load_ajax_files() {
			$file_path = L_THEPLUS_PATH . 'modules/widget-promotion/tp-widgets-show/class-tp-widget-show.php';
			if ( file_exists( $file_path ) ) {
				include_once $file_path;
			}
		}
	}

	return TP_Widgets_Promotion_Main::get_instance();
}