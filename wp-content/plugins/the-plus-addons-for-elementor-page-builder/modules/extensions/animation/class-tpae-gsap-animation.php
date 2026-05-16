<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since   6.4.2
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
if ( ! class_exists( 'Tpae_Gsap_animation' ) ) {

	/**
	 * Define Tpae_Gsap_animation class
	 *
	 * @since 6.4.2
	 */
	class Tpae_Gsap_animation {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 6.4.2
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns a singleton instance of the class.
		 *
		 * This method ensures that only one instance of the class is created (singleton pattern).
		 * If an instance doesn't exist, it creates one using the provided shortcodes.
		 *
		 * @since 6.4.2
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
		 * Get the widget name.
		 *
		 * @since 6.4.2
		 */
		public function get_name() {
			return 'plus-gsap-animation';
		}

		/**
		 * Initalize integration hooks
		 *
		 * @since 6.4.2
		 * @return void
		 */
		public function __construct() {

			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'plus_gsap_animation_controls' ), 2, 2 );
			add_action( 'elementor/element/column/_section_responsive/after_section_end', array( $this, 'plus_gsap_animation_controls' ), 2, 2 );
			add_action( 'elementor/element/common/section_custom_css_pro/after_section_end', array( $this, 'plus_gsap_animation_controls' ), 2, 2 );

			if ( \Elementor\Plugin::instance()->experiments->is_feature_active( 'container' ) ) {
				add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'plus_gsap_animation_controls' ), 2, 2 );
			}

			add_filter( 'elementor/widget/render_content', array( $this, 'plus_gsap_render_wrapper' ), 10, 2 );
			add_action( 'elementor/frontend/before_render', array( $this, 'tp_gsap_before_container_new' ), 10 );

			// add_action( 'elementor/frontend/before_render', array( $this, 'tp_gsap_before_container' ), 10 );
			// add_action( 'elementor/frontend/after_render', array( $this, 'tp_gsap_after_container' ), 10 );
		}

		/**
		 * Register GSAP Controller
		 *
		 * @since 6.4.2
		 */
		public function plus_gsap_animation_controls( $widget ) {

			$widget->start_controls_section(
				'plus_gsap_animation_sections',
				array(
					'label' => esc_html__( 'GSAP Scroll Interactions (Beta)', 'tpebl' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);

			include L_THEPLUS_PATH . 'modules/extensions/animation/class-tpae-gsap-controller.php';

			$widget->add_control(
				'layout_pro',
				array(
					'type'        => 'tpae_pro_feature',
					'label_block' => true,
					'condition'   => array(
						'plus_gsap_animation_type' => 'tp_custom',
					),
				)
			);
			$widget->end_controls_section();
		}

		/**
		 * Generate GSAP JSON data from Elementor widget settings.
		 *
		 * @param array $settings Elementor element settings.
		 * @return string JSON encoded GSAP data.
		 */
		public function tp_get_gsap_json_data( $settings ) {

			if ( 'tp_global' === ( $settings['plus_gsap_animation_type'] ?? '' ) && ! empty( $settings['tp_select_global_animation'] ) ) {

				$global_animations = array();

				if ( class_exists( '\ThePlusAddons\Elementor\TP_GSAP_Global' ) ) {

					$global_list = \ThePlusAddons\Elementor\TP_GSAP_Global::get_global_gsap_list();

				}

				foreach ( $global_list as $ani ) {
					if ( $ani['_id'] === $settings['tp_select_global_animation'] ) {
						return " data-tp_gsap_control='" . esc_attr( wp_json_encode( $ani ) ) . "'";
					}
				}
			}

			$gsap_data = array(
				'tp_animation_type' => $settings['plus_gsap_animation_type'] ?? '',
				'tp_trigger'        => $settings['tp_gsap_trigger'] ?? '',
				'tp_delay'          => $settings['tp_delay'] ?? '',
				'tp_fade_from'      => $settings['tp_fade_from'] ?? '',
				'tp_duration'       => $settings['tp_duration'] ?? '',
				'tp_ease'           => $settings['tp_ease'] ?? 'power2.out',
				'tp_fade_offset'    => $settings['tp_fade_offset'] ?? '',
				'tp_stagger'        => $settings['tp_stagger'] ?? '',
				'tp_repeat'         => $settings['tp_repeat'] ?? '',
				'tp_ani_type'         => $settings['tp_ani_type'] ?? '',
				// 'tp_yoyo'           => $settings['tp_yoyo'] ?? '',
			);

			return " data-tp_gsap_control='" . esc_attr( wp_json_encode( $gsap_data ) ) . "'";
			// return wp_json_encode( $gsap_data );
		}

		public function tp_gsap_before_container_new( $element ) {

            if ( 'container' === $element->get_name() ) {

				$settings  = $element->get_settings_for_display();
				$gsap_type = ! empty( $settings['plus_gsap_animation_type'] ) ? $settings['plus_gsap_animation_type'] : '';

				if ( 'none' === $gsap_type || 'tp_custom' === $gsap_type ) {
					return;
				}

				$json_data = $this->tp_get_gsap_json_data( $settings );

				$element->add_render_attribute( '_wrapper', 'class', 'plus-widget-inner-wrap tp-standard-gsap' );

				$element->add_render_attribute(
					'_wrapper',
					$json_data
				);
			}
        }

		// public function tp_gsap_before_container( $element ) {
		// 	if ( 'container' === $element->get_name() ) {

		// 		$settings  = $element->get_settings_for_display();
		// 		$gsap_type = ! empty( $settings['plus_gsap_animation_type'] ) ? $settings['plus_gsap_animation_type'] : '';

		// 		if ( 'none' === $gsap_type || 'tp_custom' === $gsap_type ) {
		// 			return;
		// 		}

		// 		$json_data = $this->tp_get_gsap_json_data( $settings );

		// 		echo '<div class="plus-widget-inner-wrap tp-standard-gsap" data-tp_gsap_control=\'' . $json_data . '\'>';
		// 	}
		// }

		// public function tp_gsap_after_container( $element ) {
		// 	if ( 'container' === $element->get_name() ) {
		// 		$settings = $element->get_settings_for_display();

		// 		$gsap_type = ! empty( $settings['plus_gsap_animation_type'] ) ? $settings['plus_gsap_animation_type'] : '';

		// 		if ( 'none' === $gsap_type || 'tp_custom' === $gsap_type ) {
		// 			return;
		// 		}

		// 		echo '</div>';
		// 	}
		// }

		public function plus_gsap_render_wrapper( $content, $widget ) {

			$settings = $widget->get_settings_for_display();

			$gsap_type = ! empty( $settings['plus_gsap_animation_type'] ) ? $settings['plus_gsap_animation_type'] : '';

			if ( 'none' === $gsap_type || 'tp_custom' === $gsap_type ) {
				return $content;
			}

			$json_data = $this->tp_get_gsap_json_data( $settings );

			$before = '<div class="plus-widget-inner-wrap tp-standard-gsap"' . $json_data . '>';
			$after  = '</div>';

			$output = $before . $content . $after;

			echo $output;
		}
	}
}

Tpae_Gsap_animation::get_instance();
