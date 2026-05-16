<?php
/**
 * Global Scroll Animation Helper
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\ScrollAnimation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper' ) ) {
	class TP_Global_Scroll_Animation_Helper {

		protected static function ensure_controller() {
			if ( class_exists( '\ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Controller' ) ) {
				return;
			}

			$path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-scroll-animation-controller.php';

			if ( file_exists( $path ) ) {
				include_once $path;
			}
		}

		public static function get_preset_options() {
			self::ensure_controller();

			if ( class_exists( '\ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Controller' ) ) {
				return TP_Global_Scroll_Animation_Controller::get_preset_options();
			}

			return array( '' => esc_html__( 'Select Global Animation', 'tpebl' ) );
		}

		public static function resolve_widget_settings( $settings ) {
			if ( empty( $settings['plus_scroll_animation_type'] ) || 'global' !== $settings['plus_scroll_animation_type'] ) {
				return $settings;
			}

			$preset_id = ! empty( $settings['tp_select_scroll_global_animation'] ) ? $settings['tp_select_scroll_global_animation'] : '';
			if ( empty( $preset_id ) ) {
				return $settings;
			}

			self::ensure_controller();

			if ( ! class_exists( '\ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Controller' ) ) {
				return $settings;
			}

			$preset = TP_Global_Scroll_Animation_Controller::get_preset( $preset_id );
			if ( empty( $preset ) || ! is_array( $preset ) ) {
				return $settings;
			}

			$allowed_keys = array(
				'animation_effects',
				'animation_delay',
				'animated_column_list',
				'animation_stagger',
				'animation_duration_default',
				'animate_duration',
				'animation_out_effects',
				'animation_out_delay',
				'animation_out_duration_default',
				'animation_out_duration',
			);

			foreach ( $allowed_keys as $key ) {
				if ( array_key_exists( $key, $preset ) ) {
					$settings[ $key ] = $preset[ $key ];
				}
			}

			return $settings;
		}
	}
}
