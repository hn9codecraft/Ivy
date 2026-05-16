<?php
/**
 * It is Main File to load all Notice, Upgrade Menu and all
 *
 * @link       https://posimyth.com/
 * @since      6.4.1
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Theplus\Notices;

/**
 * 
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tpae_Update_Popup' ) ) {

	/**
	 * This class used for Update Popup
	 *
	 * @since 6.4.1
	 */
	class Tpae_Update_Popup{

		/**
		 * Singleton Instance of the Class.
		 *
		 * @since 6.4.1
		 * @access private
		 * @static
		 * @var null|instance $instance An instance of the class or null if not instantiated yet.
		 */
		private static $instance = null;

		/**
		 * Singleton Instance Creation Method.
		 *
		 * This public static method ensures that only one instance of the class is loaded or can be loaded.
		 * It follows the Singleton design pattern to create or return the existing instance of the class.
		 *
		 * @since 6.4.1
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
		 * Constructor Method for Compatibility Checks and Actions Initialization.
		 *
		 *
		 * @since 6.4.1
		 * @access public
		 */
		public function __construct() {

			$get_notification = get_option( 'tpae_menu_notification' );

			if ( $get_notification !== TPAE_MENU_NOTIFICETIONS && get_option('tpae_onbording_end') ) {
				add_action( 'adminmenu', array( $this, 'tpae_update_form' ) );

				add_action( 'wp_enqueue_scripts', array( $this, 'tpae_update_form_scripts' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'tpae_update_form_scripts' ) );
			}

			add_action( 'wp_ajax_tpae_update_popup_dismiss', array( $this, 'tpae_update_popup_dismiss' ) );
		}
		
		/**
		 * Add div for the plugin update popup.
		 *
		 *
		 * @since 6.4.1
		 * @access public
		 */
        public function tpae_update_form() { 

			$screen = get_current_screen();

			if ( $screen && $screen->id === 'toplevel_page_theplus_welcome_page' ) {
            ?>
            	<div class="tpae-update-popup-container"> </div>
            <?php

			}
		}

		/**
		 * Load JS for the popup.
		 *
		 * @since 6.4.1
		 * @access public
		 */
        public function tpae_update_form_scripts() {

            wp_enqueue_script( 'tpae-update-popup',  L_THEPLUS_URL . 'includes/user-experience/update-popup/tp-update-plugin-popup.js', [], L_THEPLUS_VERSION . time(), true );
			
            wp_localize_script(
				'tpae-update-popup',
				'tpaeUpdatePopup',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'tpae_updatepopup_nonce' ),
				)
			);
		}

		/**
		 * Database update on close of the popup.
		 *
		 * @since 6.4.1
		 * @access public
		 */
		public function tpae_update_popup_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae_updatepopup_nonce' ) ) {
				wp_die( 'Security checked!' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			// update_option( 'tp_update_popup_dismiss', true );
			update_option( 'tpae_menu_notification', TPAE_MENU_NOTIFICETIONS );

			update_option( 'tpae_whats_new_notification', TPAE_WHATS_NEW_NOTIFICETIONS );

			wp_send_json_success();
		}
	}

	Tpae_Update_Popup::instance();
}