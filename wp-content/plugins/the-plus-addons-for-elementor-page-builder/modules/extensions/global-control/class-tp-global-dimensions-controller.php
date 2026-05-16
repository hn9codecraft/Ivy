<?php
/**
 * Global Dimensions Controller
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\Dimensions;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Repeater;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global Dimensions Tab
 * Appears under: Site Settings → Global
 *
 * @since v6.5.0
 */
class TP_Dimensions_Global extends Tab_Base {

	public function get_id() {
		return 'tp-global-dimensions';
	}

	public function get_title() {
		return esc_html__( 'Global Dimensions', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-spacer';
	}

	public function get_help_url() {
		return 'https://posimyth.com/';
	}

	protected function register_tab_controls() {

		$this->start_controls_section(
			'section_tp_global_dimensions',
			array(
				'label' => esc_html__( 'Global Dimensions', 'tpebl' ),
				'tab'   => $this->get_id(),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Global Name', 'tpebl' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'My Dimensions', 'tpebl' ),
				'ai'      => false,
			)
		);

		$repeater->add_control(
			'tdm_values',
			array(
				'label'      => esc_html__( 'Values', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%', 'vw' ),
				'default'    => array(
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => true,
				),
			)
		);

		$this->add_control(
			'tp_global_dimensions_list',
			array(
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'default'       => array(),
				'title_field'   => '{{{ name }}}',
				'prevent_empty' => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get all saved global dimensions presets.
	 *
	 * @since v6.5.0
	 * @return array
	 */
	public static function get_global_dimensions_list() {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$kit = Plugin::$instance->kits_manager->get_active_kit_for_frontend();

		if ( ! $kit ) {
			// Do not cache empty — Kit may not be ready yet during early hooks.
			return array();
		}

		$list = $kit->get_settings_for_display( 'tp_global_dimensions_list' );

		if ( empty( $list ) ) {
			// Do not cache empty — settings may not be initialized yet.
			return array();
		}

		$cache = $list;

		return $cache;
	}

	/**
	 * Get preset options array for use in SELECT controls.
	 *
	 * Usage: 'options' => TP_Dimensions_Global::get_preset_options()
	 *
	 * @since v6.5.0
	 * @return array  [ '' => 'Select Preset', '_id' => 'Name', ... ]
	 */
	public static function get_preset_options() {
		$options = array( '' => esc_html__( 'Select Global', 'tpebl' ) );
		foreach ( self::get_global_dimensions_list() as $preset ) {
			$options[ $preset['_id'] ] = ! empty( $preset['name'] ) ? $preset['name'] : 'Unnamed';
		}
		return $options;
	}

	/**
	 * Build the CSS shorthand value string for a given preset ID.
	 *
	 * Returns: "10px 20px 10px 20px"
	 * Use for: padding, margin, border-radius, inset, etc.
	 *
	 * Usage: $css = TP_Dimensions_Global::get_preset_css( $preset_id );
	 * Apply: padding: {$css};  or  margin: {$css};
	 *
	 * @since v6.5.0
	 * @param  string $preset_id  The _id value stored in the widget setting.
	 * @return string             e.g. "10px 20px 10px 20px"  or '' if not found.
	 */
	public static function get_preset_css( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return '';
		}

		foreach ( self::get_global_dimensions_list() as $preset ) {
			if ( ! isset( $preset['_id'] ) || $preset['_id'] !== $preset_id ) {
				continue;
			}

			$val    = isset( $preset['tdm_values'] ) ? $preset['tdm_values'] : array();
			$unit   = ! empty( $val['unit'] )   ? $val['unit']   : 'px';
			$top    = isset( $val['top'] )    ? (float) $val['top']    : 0;
			$right  = isset( $val['right'] )  ? (float) $val['right']  : 0;
			$bottom = isset( $val['bottom'] ) ? (float) $val['bottom'] : 0;
			$left   = isset( $val['left'] )   ? (float) $val['left']   : 0;

			return $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit;
		}

		return '';
	}

	/**
	 * Get individual dimension values for a preset.
	 *
	 * Useful when top/right/bottom/left need to be applied separately.
	 *
	 * @since v6.5.0
	 * @param  string $preset_id
	 * @return array  [ 'top' => '10px', 'right' => '20px', 'bottom' => '10px', 'left' => '20px' ]
	 *                or empty array if not found.
	 */
	public static function get_preset_values( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return array();
		}

		foreach ( self::get_global_dimensions_list() as $preset ) {
			if ( ! isset( $preset['_id'] ) || $preset['_id'] !== $preset_id ) {
				continue;
			}

			$val  = isset( $preset['tdm_values'] ) ? $preset['tdm_values'] : array();
			$unit = ! empty( $val['unit'] ) ? $val['unit'] : 'px';

			return array(
				'top'    => ( isset( $val['top'] )    ? (float) $val['top']    : 0 ) . $unit,
				'right'  => ( isset( $val['right'] )  ? (float) $val['right']  : 0 ) . $unit,
				'bottom' => ( isset( $val['bottom'] ) ? (float) $val['bottom'] : 0 ) . $unit,
				'left'   => ( isset( $val['left'] )   ? (float) $val['left']   : 0 ) . $unit,
			);
		}

		return array();
	}

	/**
	 * Get unformatted dimension values for selector replacements.
	 *
	 * Elementor's DIMENSIONS control already appends `{{UNIT}}` in selector
	 * templates, so global preset resolution needs the raw numeric values plus unit.
	 *
	 * @since v6.5.0
	 *
	 * @param string $preset_id Preset ID.
	 * @return array
	 */
	public static function get_preset_raw_values( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return array();
		}

		foreach ( self::get_global_dimensions_list() as $preset ) {
			if ( ! isset( $preset['_id'] ) || $preset['_id'] !== $preset_id ) {
				continue;
			}

			$val = isset( $preset['tdm_values'] ) ? $preset['tdm_values'] : array();

			return array(
				'top'      => ( isset( $val['top'] ) && '' !== $val['top'] ) ? $val['top'] : '0',
				'right'    => ( isset( $val['right'] ) && '' !== $val['right'] ) ? $val['right'] : '0',
				'bottom'   => ( isset( $val['bottom'] ) && '' !== $val['bottom'] ) ? $val['bottom'] : '0',
				'left'     => ( isset( $val['left'] ) && '' !== $val['left'] ) ? $val['left'] : '0',
				'unit'     => ! empty( $val['unit'] ) ? $val['unit'] : 'px',
				'isLinked' => ! empty( $val['isLinked'] ),
			);
		}

		return array();
	}
}
