<?php
/**
 * The file that defines the widget plugin.
 *
 * @link       https://posimyth.com/
 * @since      1.0.0
 *
 * @package    ThePlus
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tp_enable_global_scroll_animation = ! empty( $tp_enable_global_scroll_animation );
$tp_animation_basic_condition      = array();
$tp_animation_plus_listing_condition = ! empty( $tp_animation_plus_listing_condition ) ? $tp_animation_plus_listing_condition : array();

$this->start_controls_section(
	'section_animation_styling',
	array(
		'label' => esc_html__( 'On Scroll View Animation', 'tpebl' ),
		'tab'   => Controls_Manager::TAB_STYLE,
	)
);

if ( $tp_enable_global_scroll_animation ) {
	if ( ! class_exists( '\ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper' ) ) {
		include_once L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-scroll-animation-helper.php';
	}

	$this->add_control(
		'plus_scroll_animation_type',
		array(
			'label'       => esc_html__( 'Animation Type', 'tpebl' ),
			'type'        => Controls_Manager::CHOOSE,
			'toggle'      => false,
			'default'     => 'basic',
			'label_block' => false,
			'options'     => array(
				'basic'  => array(
					'title' => esc_html__( 'Basic', 'tpebl' ),
					'icon'  => 'eicon-animation',
				),
				'global' => array(
					'title' => esc_html__( 'Global', 'tpebl' ),
					'icon'  => 'eicon-globe',
				),
			),
		)
	);

	$this->add_control(
		'tp_select_scroll_global_animation',
		array(
			'label'       => esc_html__( 'Global Animation', 'tpebl' ),
			'type'        => Controls_Manager::SELECT,
			'label_block' => false,
			'default'     => '',
			'options'     => \ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper::get_preset_options(),
			'condition'   => array(
				'plus_scroll_animation_type' => 'global',
			),
		)
	);

	$tp_animation_basic_condition = array(
		'plus_scroll_animation_type' => 'basic',
	);
}
$this->add_control(
	'animation_effects',
	array(
		'label'   => esc_html__( 'In Animation Effect', 'tpebl' ),
		'type'    => Controls_Manager::SELECT,
		'default' => 'no-animation',
		'options' => l_theplus_get_animation_options(),
		'condition' => $tp_animation_basic_condition,
	)
);
$this->add_control(
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
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!' => 'no-animation',
			)
		),
	)
);

$tp_hide_columns_animation = ! empty( $tp_hide_columns_animation );

if ( ! empty( $Plus_Listing_block ) && 'Plus_Listing_block' === $Plus_Listing_block ) {

	$tp_animated_column_list_options = array(
		''        => esc_html__( 'Content Animation Block', 'tpebl' ),
		'stagger' => esc_html__( 'Stagger Based Animation', 'tpebl' ),
	);

	if ( ! $tp_hide_columns_animation ) {
		$tp_animated_column_list_options['columns'] = esc_html__( 'Columns Based Animation', 'tpebl' );
	}

	$this->add_control(
		'animated_column_list',
		array(
			'label'     => esc_html__( 'List Load Animation', 'tpebl' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '',
			'options'   => $tp_animated_column_list_options,
			'condition' => array_merge(
				$tp_animation_basic_condition,
				$tp_animation_plus_listing_condition,
				array(
				'animation_effects!' => array( 'no-animation' ),
				)
			),
		)
	);
	$this->add_control(
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
			'condition' => array_merge(
				$tp_animation_basic_condition,
				$tp_animation_plus_listing_condition,
				array(
				'animation_effects!'   => array( 'no-animation' ),
				'animated_column_list' => 'stagger',
				)
			),
		)
	);
}

$this->add_control(
	'animation_duration_default',
	array(
		'label'     => esc_html__( 'Animation Duration', 'tpebl' ),
		'type'      => Controls_Manager::SWITCHER,
		'default'   => 'no',
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!' => 'no-animation',
			)
		),
	)
);
$this->add_control(
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
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!'         => 'no-animation',
			'animation_duration_default' => 'yes',
			)
		),
	)
);
$this->add_control(
	'animation_out_effects',
	array(
		'label'     => esc_html__( 'Out Animation Effect', 'tpebl' ),
		'type'      => Controls_Manager::SELECT,
		'default'   => 'no-animation',
		'options'   => l_theplus_get_out_animation_options(),
		'separator' => 'before',
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!' => 'no-animation',
			)
		),
	)
);
$this->add_control(
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
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!'     => 'no-animation',
			'animation_out_effects!' => 'no-animation',
			)
		),
	)
);
$this->add_control(
	'animation_out_duration_default',
	array(
		'label'     => esc_html__( 'Out Animation Duration', 'tpebl' ),
		'type'      => Controls_Manager::SWITCHER,
		'default'   => 'no',
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!'     => 'no-animation',
			'animation_out_effects!' => 'no-animation',
			)
		),
	)
);
$this->add_control(
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
		'condition' => array_merge(
			$tp_animation_basic_condition,
			array(
			'animation_effects!'             => 'no-animation',
			'animation_out_effects!'         => 'no-animation',
			'animation_out_duration_default' => 'yes',
			)
		),
	)
);
$this->end_controls_section();
