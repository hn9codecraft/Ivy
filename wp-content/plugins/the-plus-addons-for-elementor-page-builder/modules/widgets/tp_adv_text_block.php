<?php
/**
 * Widget Name: TP Text Block
 * Description: Content of text text block.
 * Author: Theplus
 * Author URI: https://posimyth.com
 *
 * @package ThePlus
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ThePlus_Adv_Text_Block
 */
class ThePlus_Adv_Text_Block extends Widget_Base {

	/**
	 * Document Link For Need help.
	 *
	 * @since 5.3.3
	 *
	 * @var tp_doc of the class.
	 */
	public $tp_doc = L_THEPLUS_TPDOC;

	/**
	 * Get Widget Name.
	 *
	 * @since 1.0.0
	 */
	public function get_name() {
		return 'tp-adv-text-block';
	}

	/**
	 * Get Widget Title.
	 *
	 * @since 1.0.0
	 */
	public function get_title() {
		return esc_html__( 'Text Block', 'tpebl' );
	}

	/**
	 * Get Widget Icon.
	 *
	 * @since 1.0.0
	 */
	public function get_icon() {
		return 'theplus-i-advance-text-block tpae-editor-logo';
	}

	/**
	 * Get Widget categories.
	 *
	 * @since 1.0.0
	 */
	public function get_categories() {
		return array( 'plus-essential' );
	}

	/**
	 * Get Widget keywords.
	 *
	 * @since 1.0.0
	 */
	public function get_keywords() {
		return array( 'Advanced Text Block', 'Tp Text Block', 'Word Limit Text', 'Character Limit Text', 'Text Ellipsis' );
	}

	/**
	 * Get Widget Custom Help Url.
	 *
	 * @since 1.0.0
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
	 * @since 6.0.6
	 */
	public function is_dynamic_content(): bool {
		return false;
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
	 * Register controls.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Content', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tpae_preset_controller',
			array(
				'type'        => 'tpae_preset_button',
				'temp_id'     => 16552,
				'label_block' => true,
			)
		);
		$this->add_control(
			'content_description',
			array(
				'label'       => wp_kses_post(
					sprintf(
						'%s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Description', 'tpebl' ),
						esc_url( $this->tp_doc . 'advanced-text-block-elementor?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'        => Controls_Manager::WYSIWYG,
				'ai'          => false,
				'default'     => esc_html__( 'I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'tpebl' ),
				'placeholder' => esc_html__( 'Type your description here', 'tpebl' ),
				'dynamic'     => array(
					'active' => true,
				),
			)
		);
		$this->add_responsive_control(
			'content_align',
			array(
				'label'        => esc_html__( 'Alignment', 'tpebl' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'tpebl' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => esc_html__( 'Center', 'tpebl' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'tpebl' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => esc_html__( 'Justify', 'tpebl' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'devices'      => array( 'desktop', 'tablet', 'mobile' ),
				'selectors'    => array(
					'{{WRAPPER}} .pt-plus-text-block-wrapper .text-content-block' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'display_count',
			array(
				'label'     => esc_html__( 'Description Limit', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
				'description' => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
						esc_html__( 'Enable this option to limit the amount of text shown in the content.', 'tpebl' ),
						esc_url( $this->tp_doc . 'limit-wordcount-text-widget-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn More', 'tpebl' ),
					)
				),
			)
		);
		$this->add_control(
			'display_count_by',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Limit on', 'tpebl' ),
				'default'   => 'char',
				'options'   => array(
					'char' => esc_html__( 'Character', 'tpebl' ),
					'word' => esc_html__( 'Word', 'tpebl' ),
				),
				'condition' => array(
					'display_count' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_count_input',
			array(
				'label'     => esc_html__( 'Count', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 1000,
				'step'      => 1,
				'condition' => array(
					'display_count' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_count_input_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Set the maximum number of characters or words to display.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'display_count' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_3_dots',
			array(
				'label'     => esc_html__( 'Display Dots', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
				'condition' => array(
					'display_count' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_dots_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Show ellipsis (...) at the end of the text.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'display_count' => 'yes',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'tpebl_animated_text',
			array(
				'label' => esc_html__( 'Text Animation', 'tpebl' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_text_animation',
			array(
				'label'        => esc_html__( 'Enable Animation', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Toggle to enable GSAP-powered text animations for the block.', 'tpebl' ),
			)
		);
		$this->add_control(
			'text_animations',
			array(
				'label'     => esc_html__( 'Animation', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'tp_basic',
				'options'   => array(
					'tp_basic'  => esc_html__( 'Basic', 'tpebl' ),
					'tp_global' => esc_html__( 'Global', 'tpebl' ),
				),
				'description' => esc_html__( 'Select between basic animation global animations defined in Site Settings.', 'tpebl' ),
				'condition'   => array(
					'enable_text_animation' => 'yes',
				),
			)
		);

		$theplus_options = get_option( 'theplus_options' );
		$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

		$text_global_enabled = in_array( 'plus_text_global_animation', $extras_elements );

		$global_animations = array();
		$global_options    = array();

		$global_options = array( '' => esc_html__( 'Select Animation', 'tpebl' ) ) + $global_options;

		if ( $text_global_enabled && class_exists( '\ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global' ) ) {
			$global_animations = \ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global::get_text_global_gsap_list();

			if ( ! empty( $global_animations ) ) {
				foreach ( $global_animations as $animation ) {
					$id                    = $animation['_id'] ?? '';
					$name                  = $animation['name'] ?? esc_html__( 'Unnamed', 'tpebl' );
					$global_options[ $id ] = $name;
				}
			}
		}

		if ( $text_global_enabled ) {
			$this->add_control(
				'tp_select_text_global_animation',
				array(
					'label'     => esc_html__( 'Global Animation', 'tpebl' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $global_options,
					'default'   => '',
					'description' => esc_html__( 'Choose a text animation previously created in the Global Scroll Interactions section of Site Settings.', 'tpebl' ),
					'condition' => array(
						'text_animations'       => 'tp_global',
						'enable_text_animation' => 'yes',
					),
				)
			);
		} else {
			$this->add_control(
				'tp_text_global_animation_notice',
				array(
					'type'        => Controls_Manager::RAW_HTML,
					'raw'         => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text">
								<i>
									%s<br>
									<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>
								</i>
							</p>',
							esc_html__( 'Text Global Animation is disabled. Please enable it from Dashboard → Extensions.', 'tpebl' ),
							esc_url( admin_url( 'admin.php?page=theplus_welcome_page#/extension' ) ),
							esc_html__( 'Click here to enable', 'tpebl' )
						)
					),
					'label_block' => true,
					'condition'   => array(
						'text_animations' => 'tp_global',
					),
				)
			);
		}
		$this->add_control(
			'text_animation_type',
			array(
				'label'     => esc_html__( 'Animation Type', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'normal',
				'options'   => array(
					'normal'       => esc_html__( 'Normal', 'tpebl' ),
					'explode'      => esc_html__( 'Explode / Scatter', 'tpebl' ),
					'scramble'     => esc_html__( 'Scramble Text', 'tpebl' ),
					'typing'       => esc_html__( 'Typing Effect', 'tpebl' ),
					'tp_text_swap' => esc_html__( 'Text Style Swap', 'tpebl' ),
				),
				'description' => esc_html__( 'Select the desired animation style, ranging from simple transitions to complex scatter effects.', 'tpebl' ),
				'condition'   => array(
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'tp_tansformtion_toggel',
			array(
				'label'        => esc_html__( 'Transform Effects', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( "Enable custom transformation properties like position, scale, and rotation for the 'Normal' animation type.", 'tpebl' ),
				'condition'    => array(
					'text_animation_type'   => 'normal',
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);

		$this->start_popover();
		$this->add_control(
			'transform_x',
			array(
				'label'      => esc_html__( 'X Position', 'tpebl' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => -500,
						'max'  => 500,
						'step' => 1,
					),
					'%'  => array(
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 0,
					'unit' => 'px',
				),
			)
		);
		$this->add_control(
			'transform_y',
			array(
				'label'      => esc_html__( 'Y Position', 'tpebl' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => -500,
						'max'  => 500,
						'step' => 1,
					),
					'%'  => array(
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 0,
					'unit' => 'px',
				),
			)
		);
		$this->add_control(
			'transform_skewx',
			array(
				'label'   => esc_html__( 'Skew X', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'min'  => -180,
					'max'  => 180,
					'step' => 1,
				),
				'default' => array( 'size' => 0 ),
			)
		);
		$this->add_control(
			'transform_skewy',
			array(
				'label'   => esc_html__( 'Skew Y', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'min'  => -180,
					'max'  => 180,
					'step' => 1,
				),
				'default' => array( 'size' => 0 ),
			)
		);
		$this->add_control(
			'transform_scale',
			array(
				'label'   => esc_html__( 'Scale', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'min'  => 0,
					'max'  => 5,
					'step' => 0.01,
				),
				'default' => array( 'size' => 1 ),
			)
		);
		$this->add_control(
			'transform_rotation',
			array(
				'label'   => esc_html__( 'Rotation', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'min'  => -360,
					'max'  => 360,
					'step' => 1,
				),
				'default' => array( 'size' => 0 ),
			)
		);
		$this->add_control(
			'transform_origin',
			array(
				'label'   => esc_html__( 'Transform Origin', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '50% 50%',
				'options' => array(
					'0% 0%'     => esc_html__( 'Top Left', 'tpebl' ),
					'50% 0%'    => esc_html__( 'Top Center', 'tpebl' ),
					'100% 0%'   => esc_html__( 'Top Right', 'tpebl' ),
					'0% 50%'    => esc_html__( 'Center Left', 'tpebl' ),
					'50% 50%'   => esc_html__( 'Center', 'tpebl' ),
					'100% 50%'  => esc_html__( 'Center Right', 'tpebl' ),
					'0% 100%'   => esc_html__( 'Bottom Left', 'tpebl' ),
					'50% 100%'  => esc_html__( 'Bottom Center', 'tpebl' ),
					'100% 100%' => esc_html__( 'Bottom Right', 'tpebl' ),
				),
			)
		);

		$this->end_popover();
		$this->add_control(
			'split_type',
			array(
				'label'     => esc_html__( 'Split Type', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'chars',
				'options'   => array(
					'chars' => esc_html__( 'Characters', 'tpebl' ),
					'words' => esc_html__( 'Words', 'tpebl' ),
				),
				'description' => esc_html__( 'Determine how the text is divided for the animation (by characters or by words).', 'tpebl' ),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animation_type!'  => array( 'typing', 'scramble' ),
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'text_trigger',
			array(
				'label'     => esc_html__( 'Animation Trigger', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'onload',
				'options'   => array(
					'onload'   => esc_html__( 'On Load', 'tpebl' ),
					'onscroll' => esc_html__( 'On Scroll', 'tpebl' ),
					'onhover'  => esc_html__( 'On Hover', 'tpebl' ),
				),
				'description' => esc_html__( 'Set when the animation starts: on initial page load, as the user scrolls, or when hovering.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'tp_scrub',
			array(
				'label'        => esc_html__( 'Enable Scroll Scrub', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable this to sync the animation progress directly with the page scroll speed.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation' => 'yes',
					'text_trigger'          => 'onscroll',
					'text_animation_type!'  => array( 'typing', 'scramble' ),
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'text_animation_controls',
			array(
				'label'        => esc_html__( 'Animation Controls', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Enable', 'tpebl' ),
				'label_on'     => esc_html__( 'Disable', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable custom duration, delay, and stagger timing for basic presets.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->start_popover();
		$this->add_control(
			'text_duration',
			array(
				'label'     => esc_html__( 'Duration', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 1.2,
				'description' => esc_html__( 'Total time in seconds for the animation to complete.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'text_delay',
			array(
				'label'     => esc_html__( 'Delay', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0.3,
				'description' => esc_html__( 'Idle time in seconds before the animation begins.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'text_stagger',
			array(
				'label'     => esc_html__( 'Stagger', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0.04,
				'description' => esc_html__( 'Time interval between each individual part (character or word) of the animation.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animation_type!'  => array( 'typing', 'scramble' ),
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'text_animation_controls_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Customize animation timing behavior', 'tpebl' )
					)
				),
				'label_block' => true,
				'condition'   => array(
					'enable_text_animation' => 'yes',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'text_ease',
			array(
				'label'     => esc_html__( 'Animation Effects', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'power1.out',
				'options'   => array(
					'power1.out'  => esc_html__( 'Power 1 Out', 'tpebl' ),
					'power2.out'  => esc_html__( 'Power 2 Out', 'tpebl' ),
					'power3.out'  => esc_html__( 'Power 3 Out', 'tpebl' ),
					'power4.out'  => esc_html__( 'Power 4 Out', 'tpebl' ),
					'sine.out'    => esc_html__( 'Sine Out', 'tpebl' ),
					'expo.out'    => esc_html__( 'Expo Out', 'tpebl' ),
					'circ.out'    => esc_html__( 'Circular Out', 'tpebl' ),
					'back.out'    => esc_html__( 'Back Out', 'tpebl' ),
					'elastic.out' => esc_html__( 'Elastic Out', 'tpebl' ),
					'bounce.out'  => esc_html__( 'Bounce Out', 'tpebl' ),
				),
				'description' => esc_html__( 'Define the acceleration and deceleration curve for the animation movement.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animation_type!'  => 'typing',
					'text_animations!'      => 'tp_global',
				),
			)
		);
		$this->add_control(
			'text_repeat',
			array(
				'label'        => esc_html__( 'Repeat', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Allow the animation to play every time the trigger condition is met.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation' => 'yes',
					// 'text_animation_type!'  => 'typing',
					'text_animations!'      => 'tp_global',
				),
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
				'label'   => esc_html__( 'Need Help', 'tpebl' ),
				'type'    => 'tpae_need_help',
				'default' => array(
					array(
						'label' => esc_html__( 'Read Docs', 'tpebl' ),
						'url'   => 'https://theplusaddons.com/help/advanced-text/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget',
					),
					array(
						'label' => esc_html__( 'Watch Video', 'tpebl' ),
						'url'   => 'https://www.youtube.com/watch?v=SsyUaK_f3pQ&t',
					),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_styling',
			array(
				'label' => esc_html__( 'Text Style', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .pt_plus_adv_text_block .text-content-block,{{WRAPPER}} .pt_plus_adv_text_block .text-content-block p',
			)
		);
		$this->add_control(
			'content_color',
			array(
				'label'     => esc_html__( 'Text Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#888',
				'selectors' => array(
					'{{WRAPPER}} .pt_plus_adv_text_block .text-content-block p,{{WRAPPER}} .pt_plus_adv_text_block .text-content-block' => 'color:{{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_swap_styling',
			array(
				'label' => esc_html__( 'Text Swap Style', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'text_animation_type' => 'tp_text_swap',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'   => 'swap_txt_typography',
				'label'  => esc_html__( 'Typography', 'tpebl' ),
				'global' => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
			)
		);
		$this->add_control(
			'swap_txt_color',
			array(
				'label'   => esc_html__( 'Text Color', 'tpebl' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#0885f2ff',
			)
		);
		$this->end_controls_section();

		if ( defined( 'THEPLUS_VERSION' ) ) {
			$this->start_controls_section(
				'section_plus_extra_adv',
				array(
					'label' => esc_html__( 'Plus Extras', 'tpebl' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);
			$this->end_controls_section();
		}

		include L_THEPLUS_PATH . 'modules/widgets/theplus-widget-animation.php';

		if ( defined( 'L_THEPLUS_VERSION' ) && ! defined( 'THEPLUS_VERSION' ) ) {
			include L_THEPLUS_PATH . 'modules/widgets/theplus-profeatures.php';
		}
	}

	/**
	 * Limit Words.
	 *
	 * @since 1.0.0
	 * @version 6.1.0
	 * @param string $text The input string to limit words in.
	 * @param int    $limit The maximum number of words to allow in the string.
	 */
	protected function l_limit_words( $text, $limit ) {
		$words = explode( ' ', $text );

		return implode( ' ', array_splice( $words, 0, $limit ) );
	}

	/**
	 * Render Progress Bar Written in PHP and HTML.
	 *
	 * @since 1.0.0
	 * @version 6.1.0
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		/*--OnScroll View Animation ---*/
		include L_THEPLUS_PATH . 'modules/widgets/theplus-widget-animation-attr.php';

		if ( defined( 'THEPLUS_VERSION' ) ) {
			/*--Plus Extra ---*/
			$PlusExtra_Class = 'plus-adv-text-widget';
			include THEPLUS_PATH . 'modules/widgets/theplus-widgets-extra.php';
		}

		$limit_on     = ! empty( $settings['display_count_by'] ) ? $settings['display_count_by'] : 'char';
		$dis_count    = ! empty( $settings['display_count'] ) ? $settings['display_count'] : '';
		$content_desc = ! empty( $settings['content_description'] ) ? ( $settings['content_description'] ) : '';

		$dots  = ! empty( $settings['display_3_dots'] ) ? $settings['display_3_dots'] : '';
		$count = ! empty( $settings['display_count_input'] ) ? $settings['display_count_input'] : '';

		if ( 'yes' === $dis_count && ! empty( $count ) ) {

			if ( 'char' === $limit_on ) {
				$description = substr( $content_desc, 0, $count );

				if ( strlen( $content_desc ) > $count && 'yes' === $dots ) {
					$description .= '...';
				}
			} elseif ( 'word' === $limit_on ) {
				$description = $this->l_limit_words( $content_desc, $count );

				if ( str_word_count( $content_desc ) > $count && 'yes' === $dots ) {
					$description .= '...';
				}
			}
		} else {
			$description = $content_desc;
		}

		$tp_text_gsap = ! empty( $settings['enable_text_animation'] ) ? $settings['enable_text_animation'] : 'no';

		$data_json = '';

		if ( 'yes' === $tp_text_gsap ) {

			$theplus_options = get_option( 'theplus_options' );
			$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

			$text_global_enabled = in_array( 'plus_text_global_animation', $extras_elements );

			if ( 'tp_global' === $settings['text_animations'] && $text_global_enabled ) {

				$text_global_list = array();

				if ( class_exists( '\ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global' ) ) {
					$text_global_list = \ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global::get_text_global_gsap_list();
				}

				$selected_global = $settings['tp_select_text_global_animation'] ?? '';

				foreach ( $text_global_list as $ani ) {

					if ( isset( $ani['_id'] ) && $ani['_id'] === $selected_global ) {

							$swap_typography = array();
							$typo_prefix     = 'swap_txt_typography_';

						if ( ! empty( $ani[ $typo_prefix . 'font_family' ] ) ) {
							$swap_typography['fontFamily'] = $ani[ $typo_prefix . 'font_family' ];
						}

						if ( ! empty( $ani[ $typo_prefix . 'font_weight' ] ) ) {
							$swap_typography['fontWeight'] = $ani[ $typo_prefix . 'font_weight' ];
						}

						if ( ! empty( $ani[ $typo_prefix . 'font_style' ] ) ) {
							$swap_typography['fontStyle'] = $ani[ $typo_prefix . 'font_style' ];
						}

						if ( ! empty( $ani[ $typo_prefix . 'font_size' ]['size'] ) ) {
							$swap_typography['fontSize'] = $ani[ $typo_prefix . 'font_size' ]['size'] . $ani[ $typo_prefix . 'font_size' ]['unit'];
						}

						if ( ! empty( $ani[ $typo_prefix . 'text_transform' ] ) ) {
							$swap_typography['textTransform'] = $ani[ $typo_prefix . 'text_transform' ];
						}

						if ( ! empty( $ani[ $typo_prefix . 'text_decoration' ] ) ) {
							$swap_typography['textDecoration'] = $ani[ $typo_prefix . 'text_decoration' ];
						}

						if ( ! empty( $ani[ $typo_prefix . 'line_height' ]['size'] ) ) {
							$swap_typography['lineHeight'] = $ani[ $typo_prefix . 'line_height' ]['size'] . $ani[ $typo_prefix . 'line_height' ]['unit'];
						}

						if ( ! empty( $ani[ $typo_prefix . 'letter_spacing' ]['size'] ) ) {
							$swap_typography['letterSpacing'] = $ani[ $typo_prefix . 'letter_spacing' ]['size'] . $ani[ $typo_prefix . 'letter_spacing' ]['unit'];
						}

							$gsap_config = array(
								// Basic Animation
								'tp_enable_ani'      => $settings['enable_text_animation'] ?? 'no',
								'tp_effect'          => $ani['text_animation_type'] ?? 'normal',
								'tp_split_type'      => $ani['split_type'] ?? 'chars',
								'tp_trigger'         => $ani['text_trigger'] ?? 'onload',
								'tp_duration'        => ! empty( $ani['text_duration'] ) ? $ani['text_duration'] : 1.2,
								'tp_delay'           => ! empty( $ani['text_delay'] ) ? $ani['text_delay'] : 0.3,
								'tp_stagger'         => ! empty( $ani['text_stagger'] ) ? $ani['text_stagger'] : 0.04,
								'tp_ease'            => $ani['text_ease'] ?? 'power3.out',
								'tp_repeat'          => $ani['text_repeat'] ?? 'no',
								'tp_scrub'           => $ani['tp_scrub'] ?? '',
								'tp_swap_txt_color'  => isset( $ani['swap_txt_color'] ) ? $ani['swap_txt_color'] : '',
								'tp_swap_typography' => $swap_typography,

								// Transform Options
								'transform_toggle'   => $ani['tp_tansformtion_toggel'] ?? 'no',
								'transform_x'        => ! empty( $ani['transform_x']['size'] ) ? $ani['transform_x']['size'] : 0,
								'transform_x_unit'   => $ani['transform_x']['unit'] ?? 'px',
								'transform_y'        => ! empty( $ani['transform_y']['size'] ) ? $ani['transform_y']['size'] : 0,
								'transform_y_unit'   => $ani['transform_y']['unit'] ?? 'px',
								'transform_skewx'    => ! empty( $ani['transform_skewx']['size'] ) ? $ani['transform_skewx']['size'] : 0,
								'transform_skewy'    => ! empty( $ani['transform_skewy']['size'] ) ? $ani['transform_skewy']['size'] : 0,
								'transform_scale'    => ! empty( $ani['transform_scale']['size'] ) ? $ani['transform_scale']['size'] : 1,
								'transform_rotation' => ! empty( $ani['transform_rotation']['size'] ) ? $ani['transform_rotation']['size'] : 0,
								'transform_origin'   => $ani['transform_origin'] ?? '50% 50%',
							);
							$data_json   = wp_json_encode( $gsap_config );
					}
				}
			} else {

				$swap_typography = array();
				$typo_prefix     = 'swap_txt_typography_';

				if ( ! empty( $settings[ $typo_prefix . 'font_family' ] ) ) {
					$swap_typography['fontFamily'] = $settings[ $typo_prefix . 'font_family' ];
				}

				if ( ! empty( $settings[ $typo_prefix . 'font_weight' ] ) ) {
					$swap_typography['fontWeight'] = $settings[ $typo_prefix . 'font_weight' ];
				}

				if ( ! empty( $settings[ $typo_prefix . 'font_style' ] ) ) {
					$swap_typography['fontStyle'] = $settings[ $typo_prefix . 'font_style' ];
				}

				if ( ! empty( $settings[ $typo_prefix . 'font_size' ]['size'] ) ) {
					$swap_typography['fontSize'] = $settings[ $typo_prefix . 'font_size' ]['size'] . $settings[ $typo_prefix . 'font_size' ]['unit'];
				}

				if ( ! empty( $settings[ $typo_prefix . 'text_transform' ] ) ) {
					$swap_typography['textTransform'] = $settings[ $typo_prefix . 'text_transform' ];
				}

				if ( ! empty( $settings[ $typo_prefix . 'text_decoration' ] ) ) {
					$swap_typography['textDecoration'] = $settings[ $typo_prefix . 'text_decoration' ];
				}

				if ( ! empty( $settings[ $typo_prefix . 'line_height' ]['size'] ) ) {
					$swap_typography['lineHeight'] = $settings[ $typo_prefix . 'line_height' ]['size'] . $settings[ $typo_prefix . 'line_height' ]['unit'];
				}

				if ( ! empty( $settings[ $typo_prefix . 'letter_spacing' ]['size'] ) ) {
					$swap_typography['letterSpacing'] = $settings[ $typo_prefix . 'letter_spacing' ]['size'] . $settings[ $typo_prefix . 'letter_spacing' ]['unit'];
				}

				$gsap_config = array(

					'tp_enable_ani'      => $settings['enable_text_animation'] ?? 'no',
					'tp_effect'          => $settings['text_animation_type'] ?? 'normal',
					'tp_split_type'      => $settings['split_type'] ?? 'chars',
					'tp_trigger'         => $settings['text_trigger'] ?? 'onload',
					'tp_duration'        => ! empty( $settings['text_duration'] ) ? $settings['text_duration'] : 1.2,
					'tp_delay'           => ! empty( $settings['text_delay'] ) ? $settings['text_delay'] : 0.3,
					'tp_stagger'         => ! empty( $settings['text_stagger'] ) ? $settings['text_stagger'] : 0.04,
					'tp_ease'            => $settings['text_ease'] ?? 'power3.out',
					'tp_repeat'          => $settings['text_repeat'] ?? 'no',
					'tp_scrub'           => $settings['tp_scrub'] ?? '',
					'tp_swap_txt_color'  => isset( $settings['swap_txt_color'] ) ? $settings['swap_txt_color'] : '',
					'tp_swap_typography' => $swap_typography,

					'transform_toggle'   => $settings['tp_tansformtion_toggel'] ?? 'no',
					'transform_x'        => ! empty( $settings['transform_x']['size'] ) ? $settings['transform_x']['size'] : 0,
					'transform_x_unit'   => $settings['transform_x']['unit'] ?? 'px',
					'transform_y'        => ! empty( $settings['transform_y']['size'] ) ? $settings['transform_y']['size'] : 0,
					'transform_y_unit'   => $settings['transform_y']['unit'] ?? 'px',
					'transform_skewx'    => ! empty( $settings['transform_skewx']['size'] ) ? $settings['transform_skewx']['size'] : 0,
					'transform_skewy'    => ! empty( $settings['transform_skewy']['size'] ) ? $settings['transform_skewy']['size'] : 0,
					'transform_scale'    => ! empty( $settings['transform_scale']['size'] ) ? $settings['transform_scale']['size'] : 1,
					'transform_rotation' => ! empty( $settings['transform_rotation']['size'] ) ? $settings['transform_rotation']['size'] : 0,
					'transform_origin'   => $settings['transform_origin'] ?? '50% 50%',
				);

				$data_json = wp_json_encode( $gsap_config );

			}
		}

		$text_block = '<div class="pt-plus-text-block-wrapper" data-tp-gsap-textblock="' . esc_attr( $data_json ) . '" >';

			$text_block .= '<div class="text_block_parallax">';

				$text_block .= '<div class="pt_plus_adv_text_block ' . esc_attr( $animated_class ) . '" ' . $animation_attr . '>';

					$text_block .= '<div class="text-content-block">' . wp_kses_post( $description ) . '</div>';

				$text_block .= '</div>';

			$text_block .= '</div>';

		$text_block .= '</div>';

		if ( defined( 'THEPLUS_VERSION' ) ) {
			echo $before_content . $text_block . $after_content;
		} else {
			echo $text_block;
		}
	}
}
