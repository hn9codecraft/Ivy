<?php
/**
 * Global Box Shadow Controller
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\BoxShadow;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Repeater;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global Box Shadow Tab
 * Appears under: Site Settings → Global
 *
 * @since v6.5.0
 */
class TP_Box_Shadow_Global extends Tab_Base {

	public function get_id() {
		return 'tp-box-shadow-global';
	}

	public function get_title() {
		return esc_html__( 'Global Box Shadows', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-inner-section';
	}

	protected function register_tab_controls() {

		$this->start_controls_section(
			'section_' . $this->get_id(),
			array(
				'label' => $this->get_title(),
				'tab'   => $this->get_id(),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Global Name', 'tpebl' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'My Shadow', 'tpebl' ),
				'ai'      => false,
			)
		);

		$repeater->add_control(
			'gbs_type',
			array(
				'label'   => esc_html__( 'Type', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'bst_outset' => esc_html__( 'Outset', 'tpebl' ),
					'bst_inset'  => esc_html__( 'Inset', 'tpebl' ),
				),
				'default' => 'bst_outset',
			)
		);

		$repeater->add_control(
			'gbs_x',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'X', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => -250,
						'max'  => 250,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 0,
					'unit' => 'px',
				),
			)
		);

		$repeater->add_control(
			'gbs_y',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Y', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => -250,
						'max'  => 250,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 0,
					'unit' => 'px',
				),
			)
		);

		$repeater->add_control(
			'gbs_blur',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Blur', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 10,
					'unit' => 'px',
				),
			)
		);

		$repeater->add_control(
			'gbs_spread',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Spread', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => -50,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 0,
					'unit' => 'px',
				),
			)
		);

		$repeater->add_control(
			'gbs_color',
			array(
				'label'   => esc_html__( 'Color', 'tpebl' ),
				'type'    => Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.2)',
			)
		);

		$this->add_control(
			'tp_global_box_shadow_list',
			array(
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'title_field'   => '{{{ name }}}',
				'button_text'   => esc_html__( 'Add Shadow', 'tpebl' ),
				'prevent_empty' => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get all saved global box shadow presets.
	 *
	 * @since v6.5.0
	 * @return array
	 */
	public static function get_global_box_shadow_list() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit ) {
			return array();
		}

		$list = $kit->get_settings( 'tp_global_box_shadow_list' );

		return ! empty( $list ) ? $list : array();
	}

	/**
	 * Get preset options array for use in widget SELECT controls.
	 *
	 * Usage: 'options' => TP_Box_Shadow_Global::get_preset_options()
	 *
	 * @since v6.5.0
	 * @return array  [ '' => 'Select Box Shadow', '_id' => 'Name', ... ]
	 */
	public static function get_preset_options() {
		$options = array( '' => esc_html__( 'Select Box Shadow', 'tpebl' ) );

		foreach ( self::get_global_box_shadow_list() as $preset ) {
			$options[ $preset['_id'] ] = ! empty( $preset['name'] ) ? $preset['name'] : 'Unnamed';
		}

		return $options;
	}

	/**
	 * Build the CSS box-shadow value string for a given preset ID.
	 *
	 * Usage in render(): $css = TP_Box_Shadow_Global::get_preset_css( $settings['my_control_global'] );
	 *
	 * @since v6.5.0
	 * @param  string $preset_id  The _id value stored in the widget setting.
	 * @return string             e.g. "0px 4px 10px 0px rgba(0,0,0,0.2)"  or '' if not found.
	 */
	public static function get_preset_css( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return '';
		}

		foreach ( self::get_global_box_shadow_list() as $preset ) {
			if ( isset( $preset['_id'] ) && $preset['_id'] === $preset_id ) {
				$type   = ( ! empty( $preset['gbs_type'] ) && 'bst_inset' === $preset['gbs_type'] ) ? 'inset ' : '';
				$x      = isset( $preset['gbs_x']['size'] ) ? $preset['gbs_x']['size'] . 'px' : '0px';
				$y      = isset( $preset['gbs_y']['size'] ) ? $preset['gbs_y']['size'] . 'px' : '0px';
				$blur   = isset( $preset['gbs_blur']['size'] ) ? $preset['gbs_blur']['size'] . 'px' : '10px';
				$spread = isset( $preset['gbs_spread']['size'] ) ? $preset['gbs_spread']['size'] . 'px' : '0px';
				// $color  = ! empty( $preset['gbs_color'] ) ? $preset['gbs_color'] : 'rgba(0,0,0,0.2)';

				$color = 'rgba(0,0,0,0.2)';

				if ( ! empty( $preset['__globals__']['gbs_color'] ) ) {
					// Global color selected — parse the ID and build a CSS custom property
					$global_ref = $preset['__globals__']['gbs_color'];
					$parsed     = wp_parse_url( $global_ref );
					if ( ! empty( $parsed['query'] ) ) {
						parse_str( $parsed['query'], $query_args );
						if ( ! empty( $query_args['id'] ) ) {
							$color = 'var(--e-global-color-' . sanitize_key( $query_args['id'] ) . ')';
						}
					}
				} elseif ( ! empty( $preset['gbs_color'] ) ) {
					$color = $preset['gbs_color'];
				}


				return $type . $x . ' ' . $y . ' ' . $blur . ' ' . $spread . ' ' . $color;
			}
		}

		return '';
	}
}
