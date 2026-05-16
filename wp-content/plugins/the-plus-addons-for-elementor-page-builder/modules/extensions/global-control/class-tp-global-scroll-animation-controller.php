<?php
/**
 * Global Scroll Animation Controller
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\ScrollAnimation;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Plugin;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TP_Global_Scroll_Animation_Controller extends Tab_Base {

	public function get_id() {
		return 'tp-global-scroll-animation';
	}

	public function get_title() {
		return esc_html__( 'Global Scroll Animation', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-animation';
	}

	public function get_help_url() {
		return 'https://posimyth.com/';
	}

	protected function get_animation_options() {
		return array(
			'no-animation'                  => esc_html__( 'No-animation', 'tpebl' ),
			'transition.fadeIn'             => esc_html__( 'FadeIn', 'tpebl' ),
			'transition.flipXIn'            => esc_html__( 'FlipXIn', 'tpebl' ),
			'transition.flipYIn'            => esc_html__( 'FlipYIn', 'tpebl' ),
			'transition.flipBounceXIn'      => esc_html__( 'FlipBounceXIn', 'tpebl' ),
			'transition.flipBounceYIn'      => esc_html__( 'FlipBounceYIn', 'tpebl' ),
			'transition.swoopIn'            => esc_html__( 'SwoopIn', 'tpebl' ),
			'transition.whirlIn'            => esc_html__( 'WhirlIn', 'tpebl' ),
			'transition.shrinkIn'           => esc_html__( 'ShrinkIn', 'tpebl' ),
			'transition.expandIn'           => esc_html__( 'ExpandIn', 'tpebl' ),
			'transition.bounceIn'           => esc_html__( 'BounceIn', 'tpebl' ),
			'transition.bounceUpIn'         => esc_html__( 'BounceUpIn', 'tpebl' ),
			'transition.bounceDownIn'       => esc_html__( 'BounceDownIn', 'tpebl' ),
			'transition.bounceLeftIn'       => esc_html__( 'BounceLeftIn', 'tpebl' ),
			'transition.bounceRightIn'      => esc_html__( 'BounceRightIn', 'tpebl' ),
			'transition.slideUpIn'          => esc_html__( 'SlideUpIn', 'tpebl' ),
			'transition.slideDownIn'        => esc_html__( 'SlideDownIn', 'tpebl' ),
			'transition.slideLeftIn'        => esc_html__( 'SlideLeftIn', 'tpebl' ),
			'transition.slideRightIn'       => esc_html__( 'SlideRightIn', 'tpebl' ),
			'transition.slideUpBigIn'       => esc_html__( 'SlideUpBigIn', 'tpebl' ),
			'transition.slideDownBigIn'     => esc_html__( 'SlideDownBigIn', 'tpebl' ),
			'transition.slideLeftBigIn'     => esc_html__( 'SlideLeftBigIn', 'tpebl' ),
			'transition.slideRightBigIn'    => esc_html__( 'SlideRightBigIn', 'tpebl' ),
			'transition.perspectiveUpIn'    => esc_html__( 'PerspectiveUpIn', 'tpebl' ),
			'transition.perspectiveDownIn'  => esc_html__( 'PerspectiveDownIn', 'tpebl' ),
			'transition.perspectiveLeftIn'  => esc_html__( 'PerspectiveLeftIn', 'tpebl' ),
			'transition.perspectiveRightIn' => esc_html__( 'PerspectiveRightIn', 'tpebl' ),
			);
	}

	protected function get_out_animation_options() {
		return array(
			'no-animation'                   => esc_html__( 'No-animation', 'tpebl' ),
			'transition.fadeOut'             => esc_html__( 'FadeOut', 'tpebl' ),
			'transition.flipXOut'            => esc_html__( 'FlipXOut', 'tpebl' ),
			'transition.flipYOut'            => esc_html__( 'FlipYOut', 'tpebl' ),
			'transition.flipBounceXOut'      => esc_html__( 'FlipBounceXOut', 'tpebl' ),
			'transition.flipBounceYOut'      => esc_html__( 'FlipBounceYOut', 'tpebl' ),
			'transition.swoopOut'            => esc_html__( 'SwoopOut', 'tpebl' ),
			'transition.whirlOut'            => esc_html__( 'WhirlOut', 'tpebl' ),
			'transition.shrinkOut'           => esc_html__( 'ShrinkOut', 'tpebl' ),
			'transition.expandOut'           => esc_html__( 'ExpandOut', 'tpebl' ),
			'transition.bounceOut'           => esc_html__( 'BounceOut', 'tpebl' ),
			'transition.bounceUpOut'         => esc_html__( 'BounceUpOut', 'tpebl' ),
			'transition.bounceDownOut'       => esc_html__( 'BounceDownOut', 'tpebl' ),
			'transition.bounceLeftOut'       => esc_html__( 'BounceLeftOut', 'tpebl' ),
			'transition.bounceRightOut'      => esc_html__( 'BounceRightOut', 'tpebl' ),
			'transition.slideUpOut'          => esc_html__( 'SlideUpOut', 'tpebl' ),
			'transition.slideDownOut'        => esc_html__( 'SlideDownOut', 'tpebl' ),
			'transition.slideLeftOut'        => esc_html__( 'SlideLeftOut', 'tpebl' ),
			'transition.slideRightOut'       => esc_html__( 'SlideRightOut', 'tpebl' ),
			'transition.slideUpBigOut'       => esc_html__( 'SlideUpBigOut', 'tpebl' ),
			'transition.slideDownBigOut'     => esc_html__( 'SlideDownBigOut', 'tpebl' ),
			'transition.slideLeftBigOut'     => esc_html__( 'SlideLeftBigOut', 'tpebl' ),
			'transition.slideRightBigOut'    => esc_html__( 'SlideRightBigOut', 'tpebl' ),
			'transition.perspectiveUpOut'    => esc_html__( 'PerspectiveUpOut', 'tpebl' ),
			'transition.perspectiveDownOut'  => esc_html__( 'PerspectiveDownOut', 'tpebl' ),
			'transition.perspectiveLeftOut'  => esc_html__( 'PerspectiveLeftOut', 'tpebl' ),
			'transition.perspectiveRightOut' => esc_html__( 'PerspectiveRightOut', 'tpebl' ),
			);
	}

	protected function register_tab_controls() {
		$this->start_controls_section(
			'section_tp_global_scroll_animation',
			array(
				'label' => esc_html__( 'Global Scroll Animation', 'tpebl' ),
				'tab'   => $this->get_id(),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Global Name', 'tpebl' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Listing Animation', 'tpebl' ),
				'ai'      => false,
			)
		);

		$repeater->add_control(
			'animation_effects',
			array(
				'label'   => esc_html__( 'In Animation Effect', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'no-animation',
				'options' => $this->get_animation_options(),
			)
		);

		$repeater->add_control(
			'animation_delay',
			array(
				'type'      => Controls_Manager::SLIDER,
				'label'     => esc_html__( 'Animation Delay', 'tpebl' ),
				'default'   => array(
					'unit' => '',
					'size' => 50,
				),
				'range'     => array(
					'' => array(
						'min'  => 0,
						'max'  => 4000,
						'step' => 15,
					),
				),
				'condition' => array(
					'animation_effects!' => 'no-animation',
				),
			)
		);

		$repeater->add_control(
			'animated_column_list',
			array(
				'label'     => esc_html__( 'List Load Animation', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''        => esc_html__( 'Content Animation Block', 'tpebl' ),
					'stagger' => esc_html__( 'Stagger Based Animation', 'tpebl' ),
					'columns' => esc_html__( 'Columns Based Animation', 'tpebl' ),
				),
				'condition' => array(
					'animation_effects!' => array( 'no-animation' ),
				),
			)
		);

		$repeater->add_control(
			'animation_stagger',
			array(
				'type'      => Controls_Manager::SLIDER,
				'label'     => esc_html__( 'Animation Stagger', 'tpebl' ),
				'default'   => array(
					'unit' => '',
					'size' => 150,
				),
				'range'     => array(
					'' => array(
						'min'  => 0,
						'max'  => 6000,
						'step' => 10,
					),
				),
				'condition' => array(
					'animation_effects!'   => array( 'no-animation' ),
					'animated_column_list' => 'stagger',
				),
			)
		);

		$repeater->add_control(
			'animation_duration_default',
			array(
				'label'     => esc_html__( 'Animation Duration', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => array(
					'animation_effects!' => 'no-animation',
				),
			)
		);

		$repeater->add_control(
			'animate_duration',
			array(
				'type'      => Controls_Manager::SLIDER,
				'label'     => esc_html__( 'Duration Speed', 'tpebl' ),
				'default'   => array(
					'unit' => 'px',
					'size' => 50,
				),
				'range'     => array(
					'px' => array(
						'min'  => 100,
						'max'  => 10000,
						'step' => 100,
					),
				),
				'condition' => array(
					'animation_effects!'         => 'no-animation',
					'animation_duration_default' => 'yes',
				),
			)
		);

		$repeater->add_control(
			'animation_out_effects',
			array(
				'label'     => esc_html__( 'Out Animation Effect', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'no-animation',
				'options'   => $this->get_out_animation_options(),
				'separator' => 'before',
				'condition' => array(
					'animation_effects!' => 'no-animation',
				),
			)
		);

		$repeater->add_control(
			'animation_out_delay',
			array(
				'type'      => Controls_Manager::SLIDER,
				'label'     => esc_html__( 'Out Animation Delay', 'tpebl' ),
				'default'   => array(
					'unit' => '',
					'size' => 50,
				),
				'range'     => array(
					'' => array(
						'min'  => 0,
						'max'  => 4000,
						'step' => 15,
					),
				),
				'condition' => array(
					'animation_effects!'     => 'no-animation',
					'animation_out_effects!' => 'no-animation',
				),
			)
		);

		$repeater->add_control(
			'animation_out_duration_default',
			array(
				'label'     => esc_html__( 'Out Animation Duration', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => array(
					'animation_effects!'     => 'no-animation',
					'animation_out_effects!' => 'no-animation',
				),
			)
		);

		$repeater->add_control(
			'animation_out_duration',
			array(
				'type'      => Controls_Manager::SLIDER,
				'label'     => esc_html__( 'Duration Speed', 'tpebl' ),
				'default'   => array(
					'unit' => 'px',
					'size' => 50,
				),
				'range'     => array(
					'px' => array(
						'min'  => 100,
						'max'  => 10000,
						'step' => 100,
					),
				),
				'condition' => array(
					'animation_effects!'             => 'no-animation',
					'animation_out_effects!'         => 'no-animation',
					'animation_out_duration_default' => 'yes',
				),
			)
		);

		$this->add_control(
			'tp_global_scroll_animation_list',
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

	public static function get_global_scroll_animation_list() {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$kit = Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit ) {
			$cache = array();
			return $cache;
		}

		$list  = $kit->get_settings( 'tp_global_scroll_animation_list' );
		$cache = ! empty( $list ) ? $list : array();

		return $cache;
	}

	public static function get_preset_options() {
		$options = array( '' => esc_html__( 'Select Global Animation', 'tpebl' ) );

		foreach ( self::get_global_scroll_animation_list() as $preset ) {
			if ( empty( $preset['_id'] ) ) {
				continue;
			}

			$options[ $preset['_id'] ] = ! empty( $preset['name'] ) ? $preset['name'] : esc_html__( 'Unnamed', 'tpebl' );
		}

		return $options;
	}

	public static function get_preset( $preset_id ) {
		if ( empty( $preset_id ) ) {
			return array();
		}

		foreach ( self::get_global_scroll_animation_list() as $preset ) {
			if ( ! empty( $preset['_id'] ) && $preset['_id'] === $preset_id ) {
				return $preset;
			}
		}

		return array();
	}
}
