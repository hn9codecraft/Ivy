<?php
/**
 * Global Gradient Controller
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */
namespace ThePlusAddons\Elementor\Gradient;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Repeater;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global Gradient Tab
 * Appears under: Site Settings → Global
 *
 * @since v6.5.0
 */
class TP_Gradient_Global extends Tab_Base {

	public function get_id() {
		return 'tp-global-gradient-color';
	}

	public function get_title() {
		return esc_html__( 'Global Gradient Colors', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-barcode';
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
				'default' => esc_html__( 'My Gradient', 'tpebl' ),
				'ai'      => false,
			)
		);

		$repeater->add_control(
			'gg_type',
			array(
				'label'   => esc_html__( 'Type', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'linear' => esc_html__( 'Linear', 'tpebl' ),
					'radial' => esc_html__( 'Radial', 'tpebl' ),
				),
				'default' => 'linear',
			)
		);

		$repeater->add_control(
			'gg_angle',
			array(
				'label'      => esc_html__( 'Angle', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'range'      => array(
					'deg' => array(
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 135,
					'unit' => 'deg',
				),
				'condition'  => array(
					'gg_type' => 'linear',
				),
			)
		);

		$repeater->add_control(
			'gg_radial_shape',
			array(
				'label'     => esc_html__( 'Shape', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'circle'  => esc_html__( 'Circle', 'tpebl' ),
					'ellipse' => esc_html__( 'Ellipse', 'tpebl' ),
				),
				'default'   => 'circle',
				'condition' => array(
					'gg_type' => 'radial',
				),
			)
		);

		/* ---- Color Stop 1 ---- */
		$repeater->add_control(
			'gg_color1_heading',
			array(
				'label'     => esc_html__( 'Color Stop 1', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'gg_color1',
			array(
				'label'   => esc_html__( 'Color', 'tpebl' ),
				'type'    => Controls_Manager::COLOR,
				'default' => 'rgba(115,3,255,1)',
			)
		);

		$repeater->add_control(
			'gg_color1_pos',
			array(
				'label'      => esc_html__( 'Position', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 0,
					'unit' => '%',
				),
			)
		);

		/* ---- Color Stop 2 ---- */
		$repeater->add_control(
			'gg_color2_heading',
			array(
				'label'     => esc_html__( 'Color Stop 2', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'gg_color2',
			array(
				'label'   => esc_html__( 'Color', 'tpebl' ),
				'type'    => Controls_Manager::COLOR,
				'default' => 'rgba(0,195,255,1)',
			)
		);

		$repeater->add_control(
			'gg_color2_pos',
			array(
				'label'      => esc_html__( 'Position', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 100,
					'unit' => '%',
				),
			)
		);

		$this->add_control(
			'tp_global_gradient_list',
			array(
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'title_field'   => '{{{ name }}}',
				'button_text'   => esc_html__( 'Add Gradient', 'tpebl' ),
				'prevent_empty' => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get all saved global gradient presets.
	 *
	 * @since v6.5.0
	 * @return array
	 */
	public static function get_global_gradient_list() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit ) {
			return array();
		}
		$list = $kit->get_settings( 'tp_global_gradient_list' );
		return ! empty( $list ) ? $list : array();
	}

	/**
	 * Get preset options array for use in widget SELECT controls.
	 *
	 * Usage: 'options' => TP_Gradient_Global::get_preset_options()
	 *
	 * @since v6.5.0
	 * @return array  [ '' => 'Select Preset', '_id' => 'Name', ... ]
	 */
	public static function get_preset_options() {
		$options = array( '' => esc_html__( 'Select Global', 'tpebl' ) );
		foreach ( self::get_global_gradient_list() as $preset ) {
			$options[ $preset['_id'] ] = ! empty( $preset['name'] ) ? $preset['name'] : 'Unnamed';
		}
		return $options;
	}

	/**
	 * Build the CSS gradient value string for a given preset ID.
	 *
	 * Usage: $css = TP_Gradient_Global::get_preset_css( $preset_id );
	 * Apply:  background-image: {$css};  or  background: {$css};
	 *
	 * @since v6.5.0
	 * @param  string $preset_id  The _id value stored in the widget setting.
	 * @return string             e.g. "linear-gradient(135deg, rgba(115,3,255,1) 0%, rgba(0,195,255,1) 100%)"
	 *                            or '' if not found.
	 */
	public static function get_preset_css( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return '';
		}

		foreach ( self::get_global_gradient_list() as $preset ) {
			if ( ! isset( $preset['_id'] ) || $preset['_id'] !== $preset_id ) {
				continue;
			}

			$type   = ! empty( $preset['gg_type'] ) ? $preset['gg_type'] : 'linear';
			$pos1   = isset( $preset['gg_color1_pos']['size'] ) ? $preset['gg_color1_pos']['size'] . '%' : '0%';
			$pos2   = isset( $preset['gg_color2_pos']['size'] ) ? $preset['gg_color2_pos']['size'] . '%' : '100%';

			// Helper: resolve a COLOR control value — handles global color references.
			$resolve_color = function( $preset, $key, $fallback ) {
				if ( ! empty( $preset['__globals__'][ $key ] ) ) {
					$parsed = wp_parse_url( $preset['__globals__'][ $key ] );
					if ( ! empty( $parsed['query'] ) ) {
						parse_str( $parsed['query'], $q );
						if ( ! empty( $q['id'] ) ) {
							return 'var(--e-global-color-' . sanitize_key( $q['id'] ) . ')';
						}
					}
				}
				return ! empty( $preset[ $key ] ) ? $preset[ $key ] : $fallback;
			};

			$color1 = $resolve_color( $preset, 'gg_color1', 'rgba(115,3,255,1)' );
			$color2 = $resolve_color( $preset, 'gg_color2', 'rgba(0,195,255,1)' );


			$stops = $color1 . ' ' . $pos1 . ', ' . $color2 . ' ' . $pos2;

			if ( 'radial' === $type ) {
				$shape = ! empty( $preset['gg_radial_shape'] ) ? $preset['gg_radial_shape'] : 'circle';
				return 'radial-gradient(' . $shape . ', ' . $stops . ')';
			}

			// Linear
			$angle = isset( $preset['gg_angle']['size'] ) ? $preset['gg_angle']['size'] . 'deg' : '135deg';
			return 'linear-gradient(' . $angle . ', ' . $stops . ')';
		}

		return '';
	}
}
