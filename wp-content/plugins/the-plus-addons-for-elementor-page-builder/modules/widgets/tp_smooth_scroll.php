<?php
/**
 * Widget Name: Smooth Scroll
 * Description: smooth page scroll.
 * Author: Theplus
 * Author URI: https://posimyth.com
 *
 * @package ThePlus
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ThePlus_Smooth_Scroll
 */
class ThePlus_Smooth_Scroll extends Widget_Base {

	/**
	 * Get Widget Name.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	public function get_name() {
		return 'tp-smooth-scroll';
	}

	/**
	 * Get Widget Title.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	public function get_title() {
		return esc_html__( 'Smooth Scroll', 'tpebl' );
	}

	/**
	 * Get Widget Icon.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	public function get_icon() {
		return 'theplus-i-smooth-scroll tpae-editor-logo';
	}

	/**
	 * Get Widget categories.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	public function get_categories() {
		return array( 'plus-creative' );
	}

	/**
	 * Get Widget keywords.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	public function get_keywords() {
		return array( 'Tp Smooth Scroll Effect', 'Smooth Scrolling', 'Keyboard Smooth Scrolling', 'Touchpad Smooth Scroll', 'Page Smooth Scroll', 'Scroll Navigation', 'Infinite Scroll', 'Scrolling Effects', 'Scrolling Animation' );
	}

	/**
	 * Get Widget Custom Help Url.
	 *
	 * @version 5.4.2
	 */
	public function get_custom_help_url() {
		if ( defined( 'L_THEPLUS_VERSION' ) && ! defined( 'THEPLUS_VERSION' ) ) {
			$help_url = L_THEPLUS_HELP;
		} else {
			$help_url = THEPLUS_HELP;
		}

		return esc_url( $help_url );
	}

	/**
	 * It is use for widget add in catch or not.
	 *
	 * @since 6.1.0
	 */
	public function is_dynamic_content(): bool {
		return false;
	}

	/**
	 * It is use for adds.
	 *
	 * @since 6.1.0
	 */
	public function get_upsale_data() {
		$val = false;

		if ( ! defined( 'THEPLUS_VERSION' ) ) {
			$val = true;
		}

		return array(
			'condition'    => $val,
			'image'        => esc_url( L_THEPLUS_ASSETS_URL . 'images/pro-features/upgrade-proo.png' ),
			'image_alt'    => esc_attr__( 'Upgrade', 'tpebl' ),
			'title'        => esc_html__( 'Unlock all Features', 'tpebl' ),
			'upgrade_url'  => esc_url( 'https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links' ),
			'upgrade_text' => esc_html__( 'Upgrade to Pro!', 'tpebl' ),
		);
	}

	/**
	 * Disable Elementor's default inner wrapper for custom HTML control.
	 *
	 * @since 6.3.3
	 */
	public function has_widget_inner_wrapper(): bool {
		return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	/**
	 * Register controls.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Scrolling Core', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'scroll_option_type',
			array(
				'label'   => esc_html__( 'Scroll Type', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'basic',
				'options' => array(
					'basic'    => esc_html__( 'Basic', 'tpebl' ),
					'advanced' => esc_html__( 'Lenis Smooth Scroll', 'tpebl' ),
				),
			)
		);
		$this->add_control(
			'frameRate',
			array(
				'label'      => esc_html__( 'Frame Rate', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'Hz' ),
				'range'      => array(
					'Hz' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 2,
					),
				),
				'default'    => array(
					'unit' => 'Hz',
					'size' => 150,
				),
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'frameRate_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Controls how smooth the scrolling animation feels.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'animationTime',
			array(
				'label'      => esc_html__( 'Animation Time', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'ms' ),
				'range'      => array(
					'ms' => array(
						'min'  => 300,
						'max'  => 10000,
						'step' => 100,
					),
				),
				'default'    => array(
					'unit' => 'ms',
					'size' => 1000,
				),
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'animationTime_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Adjusts how long the scroll animation takes to complete.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'stepSize',
			array(
				'label'      => esc_html__( 'Step Size', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 100,
				),
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'stepSize_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Defines how much the page scrolls in each step for smoother movement.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_pulse_section',
			array(
				'label'     => esc_html__( 'Pulse ratio of "Tail" to "Acceleration" ', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'pulseAlgorithm',
			array(
				'label'        => esc_html__( 'Plus Algorithm', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'tpebl' ),
				'label_off'    => esc_html__( 'Hide', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'pulseAlgorithm_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Activate this to make scrolling smoother and more natural. Best for long pages or content-heavy sections', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'pulseScale',
			array(
				'label'      => esc_html__( 'Pulse Scale', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 4,
				),
			)
		);
		$this->add_control(
			'pulseScale_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Adjust how strong the pulse effect feels while scrolling.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'pulseNormalize',
			array(
				'label'      => esc_html__( 'Pulse Normalize', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 1,
				),
			)
		);
		$this->add_control(
			'pulseNormalize_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Control how quickly the pulse effect settles for smoother, consistent scrolling.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_acceleration_section',
			array(
				'label'     => esc_html__( 'Acceleration', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'accelerationDelta',
			array(
				'label'      => esc_html__( 'Acceleration Delta', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 50,
				),
			)
		);
		$this->add_control(
			'accelerationDelta_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Controls how quickly the scroll accelerates when you start scrolling.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'accelerationMax',
			array(
				'label'      => esc_html__( 'Acceleration Max', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 3,
				),
			)
		);
		$this->add_control(
			'accelerationMax_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Sets the maximum speed limit for the acceleration effect during scrolling.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_keyboard_settings_section',
			array(
				'label'     => esc_html__( 'Keyboard Settings', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'keyboardSupport',
			array(
				'label'        => esc_html__( 'Keyboard Support', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'tpebl' ),
				'label_off'    => esc_html__( 'Hide', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'keyboardSupport_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Enable keyboard navigation so users can scroll using their keyboard.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'arrowScroll',
			array(
				'label'      => esc_html__( 'Arrow Scroll', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 2,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 50,
				),
			)
		);
		$this->add_control(
			'arrowScroll_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Set how far the page scrolls when the up/down arrow keys are pressed.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'content_lenis_options_section',
			array(
				'label'     => esc_html__( 'Lenis Options', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'scroll_option_type' => 'advanced',
				),
			)
		);
		$this->add_control(
			'lenis_duration',
			array(
				'label'      => esc_html__( 'Scroll Duration', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'range'      => array(
					's' => array(
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					),
				),
				'default'    => array(
					'unit' => 's',
					'size' => 1.2,
				),
				'description' => esc_html__( 'Set the time (in seconds) it takes for a scroll to finish. Higher values create more "weight" or longer glide.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_lerp',
			array(
				'label'      => esc_html__( 'Smoothing (Lerp)', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ), 
				'range'      => array(
					'px' => array(
						'min'  => 0.01,
						'max'  => 1.0,
						'step' => 0.01,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0.1,
				),
				'description' => esc_html__( 'Interpolation factor. Lower values (e.g., 0.05) make the scroll feel smoother and heavier. Higher values (e.g., 0.2) make it more responsive but less smooth.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_orientation',
			array(
				'label'   => esc_html__( 'Scroll Orientation', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => array(
					'vertical'   => esc_html__( 'Vertical', 'tpebl' ),
					'horizontal' => esc_html__( 'Horizontal', 'tpebl' ),
				),
				'description' => esc_html__( 'Choose whether you want the page to scroll vertically or horizontally.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_gestureOrientation',
			array(
				'label'   => esc_html__( 'Gesture Orientation', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => array(
					'vertical'   => esc_html__( 'Vertical', 'tpebl' ),
					'horizontal' => esc_html__( 'Horizontal', 'tpebl' ),
					'both'       => esc_html__( 'Both', 'tpebl' ),
				),
				'description' => esc_html__( 'Defines which gesture direction (touch/trackpad) will trigger the scroll.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_smoothWheel',
			array(
				'label'        => esc_html__( 'Smooth Wheel', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'tpebl' ),
				'label_off'    => esc_html__( 'Off', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Enable smooth scrolling for mouse wheel input.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_wheelMultiplier',
			array(
				'label'      => esc_html__( 'Wheel Multiplier', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'default'    => array(
					'size' => 1,
				),
				'condition' => array(
					'lenis_smoothWheel' => 'yes',
				),
				'description' => esc_html__( 'Modify the speed of the mouse wheel scroll. 1 is default, 2 is double speed.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_smoothTouch',
			array(
				'label'        => esc_html__( 'Smooth Touch', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'tpebl' ),
				'label_off'    => esc_html__( 'Off', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable smooth scrolling for touch devices (Mobile/Tablets). WARNING: This breaks native mobile scroll feel.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_touchMultiplier',
			array(
				'label'      => esc_html__( 'Touch Multiplier', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'default'    => array(
					'size' => 1.2,
				),
				'condition' => array(
					'lenis_smoothTouch' => 'yes',
				),
				'description' => esc_html__( 'Modify the speed of touch-based scrolling.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_infinite',
			array(
				'label'        => esc_html__( 'Infinite Scroll', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'tpebl' ),
				'label_off'    => esc_html__( 'Off', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable infinite looping of page content. Requires specific layout setup.', 'tpebl' ),
			)
		);
		$this->add_control(
			'lenis_autoResize',
			array(
				'label'        => esc_html__( 'Auto Resize', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'tpebl' ),
				'label_off'    => esc_html__( 'Off', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Automatically recalculate scroll dimensions when content or window size changes.', 'tpebl' ),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_other_section',
			array(
				'label' => esc_html__( 'Other Settings', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'touchpadSupport',
			array(
				'label'        => esc_html__( 'Touch pad Support', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'tpebl' ),
				'label_off'    => esc_html__( 'Hide', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'touchpadSupport_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Enable this if you want smooth scrolling to work on touch-pad devices like laptops.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'fixedBackground',
			array(
				'label'        => esc_html__( 'Fixed Support', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'tpebl' ),
				'label_off'    => esc_html__( 'Hide', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'fixedBackground_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Turn this on to support fixed elements during scrolling.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'browsers',
			array(
				'label'    => __( 'Allowed Browsers', 'tpebl' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => array(
					'mobile'  => __( 'Mobile Browsers', 'tpebl' ),
					'ieWin7'  => __( 'IeWin7', 'tpebl' ),
					'edge'    => __( 'Edge', 'tpebl' ),
					'chrome'  => __( 'Chrome', 'tpebl' ),
					'safari'  => __( 'Safari', 'tpebl' ),
					'firefox' => __( 'Firefox', 'tpebl' ),
					'other'   => __( 'Other', 'tpebl' ),
				),
				'default'  => array(),
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->add_control(
			'browsers_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Select which browsers should have smooth scrolling enabled.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'scroll_option_type' => 'basic',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_responsive_section',
			array(
				'label' => esc_html__( 'Responsive', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tablet_off_scroll',
			array(
				'label'     => esc_html__( 'Tablet/Mobile Smooth Scroll', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);
		$this->add_control(
			'tablet_off_scroll_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Enable smooth scrolling on smaller devices for a better mobile experience.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'tpebl_section_needhelp',
			array(
				'label' => esc_html__( 'Need Help?', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tpebl_help_control',
			array(
				'label' => __( 'Need Help', 'tpebl' ),
				'type'  => 'tpae_need_help',
			)
		);
		$this->end_controls_section();

		if ( ! tpae_wl_pluginads_enabled() ) {
			$this->start_controls_section(
				'tpae_theme_builder_sec',
				array(
					'label' => esc_html__( 'Use with Theme Builder', 'tpebl' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);
			$this->add_control(
				'tpae_theme_builder',
				array(
					'type'        => 'tpae_theme_builder',
					'notice'      => esc_html__( 'We recommend using this widget in the Single Template to load it globally on all pages.', 'tpebl' ),
					'button_text' => esc_html__( 'Create Single Page', 'tpebl' ),
					'page_type'   => 'tp_singular_page',
				)
			);
			$this->end_controls_section();
		}

		if ( defined( 'L_THEPLUS_VERSION' ) && ! defined( 'THEPLUS_VERSION' ) ) {
			include L_THEPLUS_PATH . 'modules/widgets/theplus-profeatures.php';
		}
	}

	/**
	 * Render
	 *
	 * Written in PHP and HTML.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$scroll_type = ! empty( $settings['scroll_option_type'] ) ? $settings['scroll_option_type'] : 'basic';

		$step_size = ! empty( $settings['stepSize']['size'] ) ? $settings['stepSize']['size'] : 100;
		$pl_algo   = ! empty( $settings['pulseAlgorithm'] ) ? $settings['pulseAlgorithm'] : '';

		$frame_rate   = ! empty( $settings['frameRate']['size'] ) ? $settings['frameRate']['size'] : 150;
		$pulse_scale  = ! empty( $settings['pulseScale']['size'] ) ? $settings['pulseScale']['size'] : 4;
		$arrow_scroll = ! empty( $settings['arrowScroll']['size'] ) ? $settings['arrowScroll']['size'] : 50;

		$animation_time  = ! empty( $settings['animationTime']['size'] ) ? $settings['animationTime']['size'] : 100;
		$pulse_algorithm = 'yes' === $pl_algo ? '1' : '0';

		$pulse_normalize    = ! empty( $settings['pulseNormalize']['size'] ) ? $settings['pulseNormalize']['size'] : 1;
		$acceleration_delta = ! empty( $settings['accelerationDelta']['size'] ) ? $settings['accelerationDelta']['size'] : 50;
		$acceleration_max   = ! empty( $settings['accelerationMax']['size'] ) ? $settings['accelerationMax']['size'] : 3;

		$keyboard_support = 'yes' === $settings['keyboardSupport'] ? '1' : '0';
		$touchpad_support = 'yes' === $settings['touchpadSupport'] ? '1' : '0';
		$fixed_background = 'yes' === $settings['fixedBackground'] ? '1' : '0';

		$browsers = ! empty( $settings['browsers'] ) ? $settings['browsers'] : array( 'ieWin7', 'chrome', 'firefox', 'safari' );
		$browsers = wp_json_encode( $browsers );

		$smooth_scroll_array = array(
			'Browsers' => ! empty( $settings['browsers'] ) ? $settings['browsers'] : array( 'ieWin7', 'chrome', 'firefox', 'safari' ),
		);

		$smooth_scroll_data = htmlspecialchars( wp_json_encode( $smooth_scroll_array ), ENT_QUOTES, 'UTF-8' );

		// Lenis Settings
		$lenis_duration        = ! empty( $settings['lenis_duration']['size'] ) ? $settings['lenis_duration']['size'] : 1.2;
		$lenis_orientation     = ! empty( $settings['lenis_orientation'] ) ? $settings['lenis_orientation'] : 'vertical';
		$lenis_smoothWheel     = ! empty( $settings['lenis_smoothWheel'] ) ? $settings['lenis_smoothWheel'] : 'yes';
		$lenis_smoothTouch     = ! empty( $settings['lenis_smoothTouch'] ) ? $settings['lenis_smoothTouch'] : 'no';
		$lenis_touchMultiplier = ! empty( $settings['lenis_touchMultiplier']['size'] ) ? $settings['lenis_touchMultiplier']['size'] : 1.5;
		$lenis_infinite        = ! empty( $settings['lenis_infinite'] ) ? $settings['lenis_infinite'] : 'no';

        $lenis_lerp               = ! empty( $settings['lenis_lerp']['size'] ) ? $settings['lenis_lerp']['size'] : 0.1;
        $lenis_wheelMultiplier    = ! empty( $settings['lenis_wheelMultiplier']['size'] ) ? $settings['lenis_wheelMultiplier']['size'] : 1;
        $lenis_gestureOrientation = ! empty( $settings['lenis_gestureOrientation'] ) ? $settings['lenis_gestureOrientation'] : 'vertical';
        $lenis_autoResize         = ! empty( $settings['lenis_autoResize'] ) ? $settings['lenis_autoResize'] : 'yes';

		$tbl_on = ! empty( $settings['tablet_off_scroll'] ) ? $settings['tablet_off_scroll'] : '';

		if ( 'yes' === $tbl_on ) {
			$tablet_off = ' data-tablet-off="yes"';
		} else {
			$tablet_off = ' data-tablet-off="no"';
		}

		$output_attributes = 'data-scroll-type="' . esc_attr( $scroll_type ) . '" ';

		if ( 'advanced' === $scroll_type ) {
			$output_attributes .= 'data-lenis-duration="' . esc_attr( $lenis_duration ) . '" ';
			$output_attributes .= 'data-lenis-orientation="' . esc_attr( $lenis_orientation ) . '" ';
			$output_attributes .= 'data-lenis-smoothWheel="' . esc_attr( $lenis_smoothWheel ) . '" ';
			$output_attributes .= 'data-lenis-smoothTouch="' . esc_attr( $lenis_smoothTouch ) . '" ';
			$output_attributes .= 'data-lenis-touchMultiplier="' . esc_attr( $lenis_touchMultiplier ) . '" ';
			$output_attributes .= 'data-lenis-infinite="' . esc_attr( $lenis_infinite ) . '" ';
            $output_attributes .= 'data-lenis-lerp="' . esc_attr( $lenis_lerp ) . '" ';
            $output_attributes .= 'data-lenis-wheelMultiplier="' . esc_attr( $lenis_wheelMultiplier ) . '" ';
            $output_attributes .= 'data-lenis-gestureOrientation="' . esc_attr( $lenis_gestureOrientation ) . '" ';
            $output_attributes .= 'data-lenis-autoResize="' . esc_attr( $lenis_autoResize ) . '" ';
		} else {
			$output_attributes .= 'data-frameRate="' . esc_attr( $frame_rate ) . '" ';
			$output_attributes .= 'data-animationTime="' . esc_attr( $animation_time ) . '" ';
			$output_attributes .= 'data-stepSize="' . esc_attr( $step_size ) . '" ';
			$output_attributes .= 'data-pulseAlgorithm="' . esc_attr( $pulse_algorithm ) . '" ';
			$output_attributes .= 'data-pulseScale="' . esc_attr( $pulse_scale ) . '" ';
			$output_attributes .= 'data-pulseNormalize="' . esc_attr( $pulse_normalize ) . '" ';
			$output_attributes .= 'data-accelerationDelta="' . esc_attr( $acceleration_delta ) . '" ';
			$output_attributes .= 'data-accelerationMax="' . esc_attr( $acceleration_max ) . '" ';
			$output_attributes .= 'data-keyboardSupport="' . esc_attr( $keyboard_support ) . '" ';
			$output_attributes .= 'data-arrowScroll="' . esc_attr( $arrow_scroll ) . '" ';
			$output_attributes .= 'data-touchpadSupport="' . esc_attr( $touchpad_support ) . '" ';
			$output_attributes .= 'data-fixedBackground="' . esc_attr( $fixed_background ) . '" ';
			$output_attributes .= 'data-basicdata="' . esc_attr( $smooth_scroll_data ) . '" ';
		}

		echo '<div class="plus-smooth-scroll" ' . $output_attributes . ' ' . esc_attr( $tablet_off ) . ' >';

		if ( 'basic' === $scroll_type ) {
			echo '<script>var smoothAllowedBrowsers = ' . $browsers . '</script>';
		}

		echo '</div>';
	}
}
