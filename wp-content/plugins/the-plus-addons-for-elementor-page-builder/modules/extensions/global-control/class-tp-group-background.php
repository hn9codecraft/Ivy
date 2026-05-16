<?php
/**
 * Extended Background Group Control
 *
 * Overrides Elementor's built-in background group control to inject a
 * "Global Gradient Preset" SELECT when Gradient type is selected.
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */
namespace ThePlusAddons\Elementor\Gradient;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ThePlusAddons\Elementor\Gradient\TP_Group_Control_Background' ) ) {

	/**
	 * Registers as the 'background' group control, replacing Elementor's default.
	 * Adds a Global Gradient Preset dropdown when Gradient type is selected.
	 *
	 * @since v6.5.0
	 */
	class TP_Group_Control_Background extends Group_Control_Background {

		/**
		 * Keep the same type so this replaces the built-in group control.
		 * 
		 * @since v6.5.0
		 */
		public static function get_type() {
			return 'background';
		}

		/**
		 * Inject the Global Gradient Preset SELECT — visible only when
		 * the background type is set to "Gradient".
		 * 
		 * @since v6.5.0
		 */
		public function init_fields() {
			$fields = parent::init_fields();

			$options = array( '' => esc_html__( 'None', 'tpebl' ) );

			if ( class_exists( 'ThePlusAddons\Elementor\Gradient\TP_Gradient_Global' ) ) {
				$options += TP_Gradient_Global::get_preset_options();
			}

			$fields['tp_gg_global_preset'] = array(
				'label'          => esc_html__( 'Global Gradient', 'tpebl' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => $options,
				'default'        => '',
				'style_transfer' => true,
				'separator'      => 'before',
				'condition'      => array(
					'background' => array( 'gradient' ),
				),
			);

			return $fields;
		}

		/**
		 * After the parent prefixes all field keys, inject a hidden field that
		 * stores the group control's CSS selector so the render hook knows exactly
		 * which element to target.
		 * 
		 * @since v6.5.0
		 */
		protected function prepare_fields( $fields ) {
			$fields = parent::prepare_fields( $fields );

			$args     = $this->get_args();
			$selector = isset( $args['selector'] ) ? $args['selector'] : '{{WRAPPER}}';
			$name     = isset( $args['name'] ) ? $args['name'] : '';

			$key = ( $name ? $name . '_' : '' ) . 'tp_gg_selector_store';

			$fields[ $key ] = array(
				'type'    => Controls_Manager::HIDDEN,
				'default' => $selector,
			);

			return $fields;
		}
	}
}
