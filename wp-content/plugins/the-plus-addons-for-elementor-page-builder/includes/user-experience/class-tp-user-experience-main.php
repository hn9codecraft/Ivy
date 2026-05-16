<?php
/**
 * It is Main File to load all Notice, Upgrade Menu and all
 *
 * @link       https://posimyth.com/
 * @since      5.3.3
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Theplus\Notices;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_User_Experience_Main' ) ) {

	/**
	 * This class used for only load All Notice Files
	 *
	 * @since 5.3.3
	 */
	class Tp_User_Experience_Main {

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
		 * White Label Option Property.
		 *
		 * @var string
		 */
		public $whitelabel = '';

		/**
		 * White Label Option Property.
		 *
		 * @var string
		 */
		public $hidden_label = '';

		/**
		 * Singleton Instance Creation Method.
		 *
		 * This public static method ensures that only one instance of the class is loaded or can be loaded.
		 * It follows the Singleton design pattern to create or return the existing instance of the class.
		 *
		 * @since 5.3.3
		 * @access public
		 * @static
		 * @return self Instance of the class.
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
		 * @since 5.3.3
		 * @access public
		 */
		public function __construct() {
			$this->tp_white_label();
			$this->tp_user_experience();
		}

		/**
		 * Here add globel class varible for white label
		 *
		 * @since 5.3.3
		 * @access public
		 */
		public function tp_white_label() {
			$this->whitelabel   = get_option( 'theplus_white_label' );
			$this->hidden_label = ! empty( $this->whitelabel['tp_hidden_label'] ) ? $this->whitelabel['tp_hidden_label'] : '';
		}

		/**
		 * Initiate our hooks
		 *
		 * @since 5.3.3
		 * @access public
		 */
		public function tp_user_experience() {

			include L_THEPLUS_PATH . 'includes/user-experience/class-tp-deactivate-feedback.php';

			if ( ( empty( $this->whitelabel['plugin_news'] ) || 'on' !== $this->whitelabel['plugin_news'] ) || ( empty( $this->whitelabel['help_link'] ) || 'on' !== $this->whitelabel['help_link'] ) || ( empty( $this->whitelabel['plugin_ads'] ) || 'on' !== $this->whitelabel['plugin_ads'] ) ) {
				include L_THEPLUS_PATH . 'includes/user-experience/update-popup/class-tp-update-popup.php';
			}
		}
	}

	Tp_User_Experience_Main::instance();
}
