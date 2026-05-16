<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since v6.4.5
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Advanced Shadows Theplus.
 */
if ( ! class_exists( 'Tpae_Gsap_Main_Animation' ) ) {

	/**
	 * Define Tpae_Gsap_Main_Animation class
	 *
	 * @since v6.4.5
	 */
	class Tpae_Gsap_Main_Animation {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since v6.4.5
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns a singleton instance of the class.
		 *
		 * This method ensures that only one instance of the class is created (singleton pattern).
		 * If an instance doesn't exist, it creates one using the provided shortcodes.
		 *
		 * @since v6.4.5
		 *
		 * @param array $shortcodes Optional. An array of shortcodes to initialize the instance with.
		 * @return self The single instance of the class.
		 */
		public static function get_instance( $shortcodes = array() ) {

			if ( null === self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;
		}

		/**
		 * Initalize integration hooks
		 *
		 * @since v6.4.5
		 * @return void
		 */
		public function __construct() {

			$theplus_options = get_option( 'theplus_options' );

			$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

			if ( ! defined( 'THEPLUS_VERSION' ) && in_array( 'plus_adv_scroll_interactions', $extras_elements ) ) {
				include L_THEPLUS_PATH . 'modules/extensions/animation/class-tpae-gsap-animation.php';
			}

			if ( in_array( 'plus_adv_scroll_interactions', $extras_elements ) ) {

			add_action( 'elementor/frontend/before_render', array( $this, 'tp_load_js_file' ), 10 );
			
			wp_register_script( 'plus-adv-gsap-main', L_THEPLUS_URL . 'assets/js/extra/gsap/gsap.min.js', array( 'jquery' ), L_THEPLUS_VERSION, true );
			wp_register_script( 'plus-adv-gsap-scrollto', L_THEPLUS_URL . 'assets/js/extra/gsap/ScrollToPlugin.min.js', array( 'jquery', 'plus-adv-gsap-main' ), L_THEPLUS_VERSION, true );
			wp_register_script( 'plus-adv-gsap-trigger', L_THEPLUS_URL . 'assets/js/extra/gsap/ScrollTrigger.min.js', array( 'jquery', 'plus-adv-gsap-main', 'plus-adv-gsap-scrollto' ), L_THEPLUS_VERSION, true );
			wp_register_script( 'plus-adv-gsap-scrambletextplugin', L_THEPLUS_URL . 'assets/js/extra/gsap/ScrambleText.js', array( 'jquery', 'plus-adv-gsap-main', 'plus-adv-gsap-scrollto' ), L_THEPLUS_VERSION, true );
			wp_register_script( 'plus-adv-gsap-splittext', L_THEPLUS_URL . 'assets/js/extra/gsap/SplitText.min.js', array( 'jquery', 'plus-adv-gsap-main', 'plus-adv-gsap-scrollto' ), L_THEPLUS_VERSION, true );
			wp_register_script( 'plus-adv-gsap-textplugin', L_THEPLUS_URL . 'assets/js/extra/gsap/TextPlugin.min.js', array( 'jquery', 'plus-adv-gsap-main', 'plus-adv-gsap-scrollto' ), L_THEPLUS_VERSION, true );

			
				add_action(
					'elementor/frontend/after_enqueue_scripts',
					function () {
						wp_register_script( 'tp-gsap-frontend', L_THEPLUS_URL . 'modules/extensions/animation/tp-gsap-frontend.js', array( 'jquery', 'plus-adv-gsap-main', 'plus-adv-gsap-scrollto' ), L_THEPLUS_VERSION, true );
					}
				);
			}

			add_action( 'elementor/kit/register_tabs', array( $this, 'register_setting_tabs' ) );

			add_action(
				'elementor/editor/after_enqueue_scripts',
				function () {
					// wp_enqueue_script(
					// 'tp-gsap-global-admin',
					// L_THEPLUS_URL . 'modules/extensions/animation/tp-gsap-global-admin.js',
					// array( 'jquery', 'elementor-editor' ),
					// L_THEPLUS_VERSION,
					// true
					// );

					wp_enqueue_style(
						'tp-gsap-global-admin',
						L_THEPLUS_URL . 'modules/extensions/animation/tp-gsap-global-admin.css',
						array(),
						L_THEPLUS_VERSION
					);
				}
			);
		}

		public function tp_load_js_file($element) {

			$settings = $element->get_settings_for_display();
			$adv_magic_scroll = ! empty( $settings['plus_gsap_animation_type'] ) ? $settings['plus_gsap_animation_type'] : '';

			if('tp_basic' === $adv_magic_scroll || 'tp_global' === $adv_magic_scroll) {
				wp_enqueue_script( 'plus-adv-gsap-main' );
				wp_enqueue_script( 'plus-adv-gsap-scrollto' );
				wp_enqueue_script( 'plus-adv-gsap-trigger' );
				wp_enqueue_script( 'plus-adv-gsap-scrambletextplugin' );
				wp_enqueue_script( 'plus-adv-gsap-splittext' );
				wp_enqueue_script( 'plus-adv-gsap-textplugin' );
				wp_enqueue_script( 'tp-gsap-frontend' );
			}
		}


		public function register_setting_tabs( $base ) {

			$theplus_options = get_option( 'theplus_options' );
			$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

			$tabs = array(
				array(
					'db_key' => 'plus_adv_scroll_interactions',
					'file'   => 'modules/extensions/global-control/class-tp-basic-global-controller.php',
					'key'    => 'settings-tp-gsap-global',
					'class'  => 'ThePlusAddons\\Elementor\\TP_GSAP_Global',
				),
				array(
					'db_key' => 'plus_text_global_animation',
					'file'   => 'modules/extensions/global-control/class-tp-text-global-controller.php',
					'key'    => 'settings-tp-text-gsap-global',
					'class'  => 'ThePlusAddons\\Elementor\\Text\\TP_GSAP_Text_Global',
				),
				array(
					'db_key' => 'plus_image_global_animation',
					'file'   => 'modules/extensions/global-control/class-tp-image-global-controller.php',
					'key'    => 'settings-tp-image-gsap-global',
					'class'  => 'ThePlusAddons\\Elementor\\Image\\TP_GSAP_Image_Global',
				),
			);

			foreach ( $tabs as $tab ) {

				if ( in_array( $tab['db_key'], $extras_elements ) ) {

					$path = L_THEPLUS_PATH . $tab['file'];

					if ( file_exists( $path ) ) {
						include_once $path;
					}

					if ( class_exists( $tab['class'] ) ) {
						$base->register_tab( $tab['key'], $tab['class'] );
					}
				}
			}
		}
	}
}

Tpae_Gsap_Main_Animation::get_instance();
