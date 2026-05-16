<?php
/**
 * Global Button Style Controller
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\ButtonStyle;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Repeater;
use Elementor\Plugin;
use ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global Button Styles Tab
 *
 * Stores reusable style presets for the Button widget.
 *
 * @since v6.5.0
 */
class TP_Button_Style_Global extends Tab_Base {

	protected function ensure_global_box_shadow_controller() {
		if ( class_exists( '\ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global' ) ) {
			return;
		}

		$path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-box-shadow-controller.php';

		if ( file_exists( $path ) ) {
			include_once $path;
		}
	}

	protected function get_global_box_shadow_options() {
		$this->ensure_global_box_shadow_controller();

		if ( class_exists( '\ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global' ) ) {
			return TP_Box_Shadow_Global::get_preset_options();
		}

		return array( '' => esc_html__( 'Select Global Shadow', 'tpebl' ) );
	}

	public function get_id() {
		return 'tp-global-button-styles';
	}

	public function get_title() {
		return esc_html__( 'Global Button Styles', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_help_url() {
		return 'https://posimyth.com/';
	}

	protected function register_tab_controls() {
		$this->start_controls_section(
			'section_tp_global_button_styles',
			array(
				'label' => esc_html__( 'Global Button Styles', 'tpebl' ),
				'tab'   => $this->get_id(),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Global Name', 'tpebl' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Primary Button', 'tpebl' ),
				'ai'      => false,
			)
		);

		$repeater->add_control(
			'layout_heading',
			array(
				'label'     => esc_html__( 'Spacing', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_responsive_control(
			'margin',
			array(
				'label'      => esc_html__( 'Margin', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				),
			)
		);

		$repeater->add_responsive_control(
			'padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => '15',
					'right'    => '30',
					'bottom'   => '15',
					'left'     => '30',
					'unit'     => 'px',
					'isLinked' => false,
				),
			)
		);

		$repeater->add_control(
			'normal_heading',
			array(
				'label'     => esc_html__( 'Normal State', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'text_color',
			array(
				'label' => esc_html__( 'Text Color', 'tpebl' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'icon_color',
			array(
				'label' => esc_html__( 'Icon Color', 'tpebl' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'background_color',
			array(
				'label' => esc_html__( 'Background Color', 'tpebl' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'border_style',
			array(
				'label'   => esc_html__( 'Border Style', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => array(
					'none'   => esc_html__( 'None', 'tpebl' ),
					'solid'  => esc_html__( 'Solid', 'tpebl' ),
					'dotted' => esc_html__( 'Dotted', 'tpebl' ),
					'dashed' => esc_html__( 'Dashed', 'tpebl' ),
					'double' => esc_html__( 'Double', 'tpebl' ),
					'groove' => esc_html__( 'Groove', 'tpebl' ),
				),
			)
		);

		$repeater->add_responsive_control(
			'border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => '1',
					'right'    => '1',
					'bottom'   => '1',
					'left'     => '1',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'condition'  => array(
					'border_style!' => 'none',
				),
			)
		);

		$repeater->add_control(
			'border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'border_style!' => 'none',
				),
			)
		);

		$repeater->add_responsive_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
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

		$repeater->add_control(
			'box_shadow_heading',
			array(
				'label'     => esc_html__( 'Box Shadow', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'shadow_type',
			array(
				'label'   => esc_html__( 'Shadow Type', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bst_outset',
				'options' => array(
					'bst_outset' => esc_html__( 'Outset', 'tpebl' ),
					'bst_inset'  => esc_html__( 'Inset', 'tpebl' ),
				),
			)
		);

		$repeater->add_control(
			'shadow_x',
			array(
				'label'      => esc_html__( 'Shadow X', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -250,
						'max' => 250,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$repeater->add_control(
			'shadow_y',
			array(
				'label'      => esc_html__( 'Shadow Y', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -250,
						'max' => 250,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$repeater->add_control(
			'shadow_blur',
			array(
				'label'      => esc_html__( 'Shadow Blur', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
			)
		);

		$repeater->add_control(
			'shadow_spread',
			array(
				'label'      => esc_html__( 'Shadow Spread', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -50,
						'max' => 50,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$repeater->add_control(
			'shadow_color',
			array(
				'label'   => esc_html__( 'Shadow Color', 'tpebl' ),
				'type'    => Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.2)',
			)
		);

		$repeater->add_control(
			'shadow_global_preset',
			array(
				'label'     => esc_html__( 'Global Shadow', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_global_box_shadow_options(),
				'default'   => '',
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'hover_heading',
			array(
				'label'     => esc_html__( 'Hover State', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'hover_text_color',
			array(
				'label' => esc_html__( 'Text Color', 'tpebl' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'hover_icon_color',
			array(
				'label' => esc_html__( 'Icon Color', 'tpebl' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'hover_background_color',
			array(
				'label' => esc_html__( 'Background Color', 'tpebl' ),
				'type'  => Controls_Manager::COLOR,
			)
		);

		$repeater->add_control(
			'hover_border_style',
			array(
				'label'   => esc_html__( 'Border Style', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => array(
					'none'   => esc_html__( 'None', 'tpebl' ),
					'solid'  => esc_html__( 'Solid', 'tpebl' ),
					'dotted' => esc_html__( 'Dotted', 'tpebl' ),
					'dashed' => esc_html__( 'Dashed', 'tpebl' ),
					'double' => esc_html__( 'Double', 'tpebl' ),
					'groove' => esc_html__( 'Groove', 'tpebl' ),
				),
			)
		);

		$repeater->add_responsive_control(
			'hover_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => '1',
					'right'    => '1',
					'bottom'   => '1',
					'left'     => '1',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'condition'  => array(
					'hover_border_style!' => 'none',
				),
			)
		);

		$repeater->add_control(
			'hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'hover_border_style!' => 'none',
				),
			)
		);

		$repeater->add_responsive_control(
			'hover_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
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

		$repeater->add_control(
			'hover_shadow_heading',
			array(
				'label'     => esc_html__( 'Hover Box Shadow', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'hover_shadow_type',
			array(
				'label'   => esc_html__( 'Shadow Type', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bst_outset',
				'options' => array(
					'bst_outset' => esc_html__( 'Outset', 'tpebl' ),
					'bst_inset'  => esc_html__( 'Inset', 'tpebl' ),
				),
			)
		);

		$repeater->add_control(
			'hover_shadow_x',
			array(
				'label'      => esc_html__( 'Shadow X', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -250,
						'max' => 250,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$repeater->add_control(
			'hover_shadow_y',
			array(
				'label'      => esc_html__( 'Shadow Y', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -250,
						'max' => 250,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$repeater->add_control(
			'hover_shadow_blur',
			array(
				'label'      => esc_html__( 'Shadow Blur', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 10,
				),
			)
		);

		$repeater->add_control(
			'hover_shadow_spread',
			array(
				'label'      => esc_html__( 'Shadow Spread', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -50,
						'max' => 50,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$repeater->add_control(
			'hover_shadow_color',
			array(
				'label'   => esc_html__( 'Shadow Color', 'tpebl' ),
				'type'    => Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.2)',
			)
		);

		$repeater->add_control(
			'hover_shadow_global_preset',
			array(
				'label'     => esc_html__( 'Global Hover Shadow', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_global_box_shadow_options(),
				'default'   => '',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'tp_global_button_style_list',
			array(
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'title_field'   => '{{{ name }}}',
				'default'       => array(),
				'prevent_empty' => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get all saved global button style presets.
	 *
	 * @since v6.5.0
	 * @return array
	 */
	public static function get_global_button_style_list() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit ) {
			return array();
		}

		$list = $kit->get_settings( 'tp_global_button_style_list' );
		return ! empty( $list ) ? $list : array();
	}

	/**
	 * Get preset options array for use in future SELECT controls.
	 *
	 * @since v6.5.0
	 * @return array
	 */
	public static function get_preset_options() {
		$presets = array();

		foreach ( self::get_global_button_style_list() as $preset ) {
			$id = isset( $preset['_id'] ) ? (string) $preset['_id'] : '';
			if ( '' === $id ) {
				continue;
			}
			$presets[ $id ] = ! empty( $preset['name'] ) ? $preset['name'] : esc_html__( 'Unnamed', 'tpebl' );
		}

		return array( '' => esc_html__( 'Select Global Button', 'tpebl' ) ) + $presets;
	}

	/**
	 * Get a single saved global button style preset.
	 *
	 * @since v6.5.0
	 *
	 * @param string $preset_id Preset ID.
	 * @return array
	 */
	public static function get_preset( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return array();
		}

		foreach ( self::get_global_button_style_list() as $preset ) {
			if ( ! empty( $preset['_id'] ) && $preset['_id'] === $preset_id ) {
				return $preset;
			}
		}

		return array();
	}
}
