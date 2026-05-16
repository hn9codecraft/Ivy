<?php
/**
 * Extended Box Shadow Group Control
 *
 * Overrides Elementor's built-in box-shadow group control to inject a
 * "Global Shadow Preset" SELECT into every add_group_control(
 * Group_Control_Box_Shadow::get_type(), ... ) call automatically.
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\BoxShadow;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ThePlusAddons\Elementor\BoxShadow\TP_Group_Control_Box_Shadow' ) ) {

	/**
	 * Registers as the 'box-shadow' group control, replacing Elementor's default.
	 * Adds a Global Shadow Preset dropdown to every box-shadow group control.
	 *
	 * @since v6.5.0
	 */
	class TP_Group_Control_Box_Shadow extends Group_Control_Box_Shadow {

		/**
		 * Keep the same type so this replaces the built-in group control.
		 * 
		 * @since v6.5.0
		 */
		public static function get_type() {
			return 'box-shadow';
		}

		/**
		 * Append the Global Preset SELECT after all standard shadow fields.
		 * 
		 * @since v6.5.0
		 */
		protected function init_fields() {
			$fields = parent::init_fields();

			$options = array( '' => esc_html__( 'None', 'tpebl' ) );

			if ( class_exists( 'ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global' ) ) {
				$options += TP_Box_Shadow_Global::get_preset_options();
			}

			$fields['tp_bs_global_preset'] = array(
				'label'          => esc_html__( 'Global Shadow', 'tpebl' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => $options,
				'default'        => '',
				'style_transfer' => true,
				'separator'      => 'before',
			);

			return $fields;
		}

		/**
		 * After the parent prefixes all field keys, inject a hidden field that
		 * stores the group control's CSS selector so the render hook knows exactly
		 * which element to target — not just the outer wrapper.
		 * 
		 * @since v6.5.0
		 */
		protected function prepare_fields( $fields ) {
			$fields = parent::prepare_fields( $fields );

			$args     = $this->get_args();
			$selector = isset( $args['selector'] ) ? $args['selector'] : '{{WRAPPER}}';
			$name     = isset( $args['name'] ) ? $args['name'] : '';

			// Build the prefixed key the same way parent does for every other field.
			$key = ( $name ? $name . '_' : '' ) . 'tp_bs_selector_store';

			$fields[ $key ] = array(
				'type'    => Controls_Manager::HIDDEN,
				'default' => $selector,
			);

			return $fields;
		}
	}
}
