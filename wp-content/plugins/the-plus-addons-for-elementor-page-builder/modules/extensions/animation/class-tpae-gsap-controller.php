<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since   1.0.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace TheplusAddons\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Background;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

$widget->add_control(
	'plus_gsap_animation_type',
	array(
		'label' => esc_html__('Animation Type', 'tpebl'),
		'type' => Controls_Manager::SELECT,
		'default' => 'none',
		'options' => array(
			'none' => esc_html__('None', 'tpebl'),
			'tp_global' => esc_html__('Global', 'tpebl'),
			'tp_basic' => esc_html__('Basic', 'tpebl'),
			'tp_custom' => esc_html__('Advanced', 'tpebl'),
		),
		'render_type' => 'ui',
		'frontend_available' => true,
	)
);
$widget->add_control(
	'plus_gsap_animation_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i>%s</i></p>',
				esc_html__('GSAP (GreenSock Animation Platform) is a powerful animation library used to create smooth and high-performance animations. This option lets you add scroll-based or on page load animations to your elements - use Global for reusable animation, Basic for simple animations, or Advanced for full customization.', 'tpebl')
			)
		),
		'label_block' => true,
	)
);

$global_animations = array();
if (class_exists('\ThePlusAddons\Elementor\TP_GSAP_Global')) {
	$global_animations = \ThePlusAddons\Elementor\TP_GSAP_Global::get_global_gsap_list();
}

$global_options = array();

$global_options = array('' => esc_html__('Select Animation', 'tpebl')) + $global_options;


if (!empty($global_animations)) {
	foreach ($global_animations as $animation) {
		$id = $animation['_id'] ?? '';
		$name = $animation['name'] ?? 'Unnamed';
		$global_options[$id] = $name;
	}
}
$widget->add_control(
	'tp_select_global_animation',
	array(
		'label' => esc_html__('Global Animation', 'tpebl'),
		'type' => Controls_Manager::SELECT,
		'options' => $global_options,
		'default' => '',
		'condition' => array(
			'plus_gsap_animation_type' => 'tp_global',
		),
		'render_type' => 'ui',
		'frontend_available' => true,
		'classes' => 'tp-ai-select',
	)
);
$widget->add_control(
	'plus_gsap_global_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
				esc_html__('Use this option to apply an animation that you have already created as a global animation. Create the animation once, then select it here to reuse the same effect. This helps you keep animations consistent and saves time.', 'tpebl'),
				esc_url( L_THEPLUS_TPDOC . 'add-global-gsap-animation-in-elementor/#Global-Scroll-Interaction/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget'),
				esc_html__('Learn More', 'tpebl'),
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => 'tp_global',
		),
	)
);
$widget->add_control(
	'tp_gsap_trigger',
	array(
		'label' => esc_html__('Trigger', 'tpebl'),
		'type' => Controls_Manager::SELECT,
		'default' => 'on_scroll',
		'render_type' => 'ui',
		'default' => 'tp_on_load',
		'options' => array(
			'tp_on_load' => esc_html__('On Load', 'tpebl'),
			'tp_on_scroll' => esc_html__('On Scroll', 'tpebl'),
		),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'render_type' => 'ui',
		'frontend_available' => true,
	)
);
$widget->add_control(
	'plus_gsap_trigger_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i>%s</i></p>',
				esc_html__('Choose when the animation should start on your page. Use page load to play the animation as soon as the page opens, or scroll to trigger it as the user scrolls down.', 'tpebl')
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->add_control(
	'animation_controls',
	array(
		'label' => __('Animation Controls', 'tpebl'),
		'type' => Controls_Manager::POPOVER_TOGGLE,
		'label_off' => __('Enable', 'tpebl'),
		'label_on' => __('Disable', 'tpebl'),
		'return_value' => 'yes',
		'default' => 'no',
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->start_popover();
$widget->add_control(
	'tp_delay',
	array(
		'label' => esc_html__('Delay', 'tpebl'),
		'type' => Controls_Manager::NUMBER,
		'min' => 0,
		'max' => 10,
		'step' => 0.1,
		'default' => .15,
		'render_type' => 'ui',
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_delay_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i>%s</i></p>',
				esc_html__('Use this option to decide how long the animation waits before starting. A small delay can help the animation appear at the right moment on the page.', 'tpebl')
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->add_control(
	'tp_duration',
	array(
		'label' => esc_html__('Duration', 'tpebl'),
		'type' => Controls_Manager::NUMBER,
		'default' => 1.5,
		'render_type' => 'ui',
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_duration_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i>%s</i></p>',
				esc_html__('Use this option to control how long the animation runs. A shorter duration feels quicker, while a longer one makes the movement slower and smoother.', 'tpebl')
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->add_control(
	'tp_fade_offset',
	array(
		'label' => esc_html__('offset', 'tpebl'),
		'type' => Controls_Manager::NUMBER,
		'default' => 50,
		'render_type' => 'ui',
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_fade_offset_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"> %s </p>',
				esc_html__('Use this option to choose how far from the top the animation should start. This helps you control the exact point where the animation begins as you scroll.', 'tpebl'),
			)
		),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'label_block' => true,
	)
);
$widget->end_popover();
$widget->add_control(
	'plus_gsap_animation_controls_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i>%s</i></p>',
				esc_html__('Use this option to control how the animation behaves over time. Set a delay before it starts, adjust how long it runs, and choose where it begins on the page.', 'tpebl')
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->add_control(
	'tp_animation_popover',
	array(
		'label' => esc_html__('Motion Settings', 'tpebl'),
		'type' => Controls_Manager::POPOVER_TOGGLE,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->start_popover();
$widget->add_control(
	'tp_ani_type',
	array(
		'label' => esc_html__('Style', 'tpebl'),
		'type' => Controls_Manager::SELECT,
		'default' => 'tp_fade',
		'render_type' => 'ui',
		'options' => array(
			'tp_fade' => esc_html__('Fade', 'tpebl'),
			'tp_slide' => esc_html__('Slide', 'tpebl'),
			'tp_scale' => esc_html__('Scale', 'tpebl'),
		),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_ease',
	array(
		'label' => esc_html__('Effect', 'tpebl'),
		'type' => Controls_Manager::SELECT,
		'default' => 'power1.out',
		'render_type' => 'ui',
		'options' => array(
			'power1.out' => esc_html__('Power1 Out', 'tpebl'),
			'power2.out' => esc_html__('Power2 Out', 'tpebl'),
			'power3.out' => esc_html__('Power3 Out', 'tpebl'),
			'power4.out' => esc_html__('Power4 Out', 'tpebl'),
			'sine.out' => esc_html__('Sine Out', 'tpebl'),
			'expo.out' => esc_html__('Expo Out', 'tpebl'),
			'circ.out' => esc_html__('Circ Out', 'tpebl'),
			'back.out' => esc_html__('Back Out', 'tpebl'),
			'elastic.out' => esc_html__('Elastic Out', 'tpebl'),
			'bounce.out' => esc_html__('Bounce Out', 'tpebl'),
		),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_fade_from',
	array(
		'label' => esc_html__('Direction', 'tpebl'),
		'type' => Controls_Manager::CHOOSE,
		'options' => array(
			'top' => array(
				'title' => esc_html__('From Top', 'tpebl'),
				'icon' => 'eicon-arrow-down',
			),
			'bottom' => array(
				'title' => esc_html__('From Bottom', 'tpebl'),
				'icon' => 'eicon-arrow-up',
			),
			'left' => array(
				'title' => esc_html__('From Left', 'tpebl'),
				'icon' => 'eicon-arrow-right',
			),
			'right' => array(
				'title' => esc_html__('From Right', 'tpebl'),
				'icon' => 'eicon-arrow-left',
			),
		),
		'default' => 'bottom',
		'toggle' => false,
		'label_block' => false,
		'frontend_available' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->add_control(
	'tp_fade_from_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"> %s </p>',
				esc_html__('Use this option to choose the direction the animation moves from. It helps you control how the element enters or moves on the screen.', 'tpebl'),
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->end_popover();
$widget->add_control(
	'plus_gsap_motion_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"><i>%s</i></p>',
				esc_html__('Use this option to decide how the animation looks and moves. Choose the animation style, the type of effect, and the direction it plays from.', 'tpebl')
			)
		),
		'label_block' => true,
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
	)
);
$widget->add_control(
	'tp_stagger',
	array(
		'label' => esc_html__('Stagger Effect', 'tpebl'),
		'type' => Controls_Manager::SWITCHER,
		'default' => 'label_off',
		'label_on' => esc_html__('Enable', 'tpebl'),
		'label_off' => esc_html__('Disable', 'tpebl'),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_stagger_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"> %s </p>',
				esc_html__('Turn this on to animate multiple elements one after another instead of all at once. This creates a small delay between each item, making the motion feel smoother and more natural.', 'tpebl'),
			)
		),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'label_block' => true,
	)
);
$widget->add_control(
	'tp_repeat',
	array(
		'label' => esc_html__('Repeat', 'tpebl'),
		'type' => Controls_Manager::SWITCHER,
		'default' => 'label_off',
		'label_on' => esc_html__('Enable', 'tpebl'),
		'label_off' => esc_html__('Disable', 'tpebl'),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'frontend_available' => true,
	)
);
$widget->add_control(
	'tp_repeat_label',
	array(
		'type' => Controls_Manager::RAW_HTML,
		'raw' => wp_kses_post(
			sprintf(
				'<p class="tp-controller-label-text"> %s </p>',
				esc_html__('Turn this on if you want the animation to play again instead of running just once. This is useful for looping effects or animations that should keep repeating.', 'tpebl'),
			)
		),
		'condition' => array(
			'plus_gsap_animation_type' => array('tp_basic'),
		),
		'label_block' => true,
	)
);
// $widget->add_control(
// 	'tp_play_animation',
// 	array(
// 		'label' => __('Play Animation', 'tpebl'),
// 		'type' => Controls_Manager::BUTTON,
// 		'text' => __('Preview Animation', 'tpebl'),
// 		'event' => 'tp:play_gsap_animation',
// 		'classes' => 'tp-preview-animation-button',
// 		'condition' => array(
// 			'plus_gsap_animation_type' => array('tp_basic'),
// 		),
// 	)
// );
// $widget->add_control(
// 	'tp_play_animation_label',
// 	array(
// 		'type' => Controls_Manager::RAW_HTML,
// 		'raw' => wp_kses_post(
// 			sprintf(
// 				'<p class="tp-controller-label-text"> %s </p>',
// 				esc_html__('Click this button to see how the animation looks. For the most accurate result, preview it on the live page instead of the editor.', 'tpebl'),
// 			)
// 		),
// 		'condition' => array(
// 			'plus_gsap_animation_type' => array('tp_basic'),
// 		),
// 		'label_block' => true,
// 	)
// );
