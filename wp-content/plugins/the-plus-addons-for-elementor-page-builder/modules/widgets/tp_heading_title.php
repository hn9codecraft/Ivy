<?php
/**
 * Widget Name: Heading Title
 * Description: Creative Heading Options.
 * Author: Theplus
 * Author URI: https://posimyth.com
 *
 * @package ThePlus
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;// Exit if accessed directly.
}

/**
 * Class L_Theplus_Ele_Heading_Title
 */
class L_Theplus_Ele_Heading_Title extends Widget_Base {

	public $tp_doc = L_THEPLUS_TPDOC;

	/**
	 * Helpdesk Link For Need help.
	 *
	 * @var tp_help of the class.
	 */
	public $tp_help = L_THEPLUS_HELP;

	/**
	 * Get Widget Name.
	 *
	 * @since 1.0.0
	 */
	public function get_name() {
		return 'tp-heading-title';
	}

	/**
	 * Get Widget Title.
	 *
	 * @since 1.0.0
	 */
	public function get_title() {
		return esc_html__( 'Heading Title', 'tpebl' );
	}

	/**
	 * Get Widget Icon.
	 *
	 * @since 1.0.0
	 */
	public function get_icon() {
		return 'theplus-i-heading-title tpae-editor-logo';
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
		return array( 'Tp Heading Title', 'Styled Heading', 'Animated Heading', 'Split Title', 'Split Heading', 'Prefix Title', 'Postfix Title', 'Subtitle Heading', 'Extra Title' );
	}

	/**
	 * Get Widget Custom Help Url.
	 *
	 * @version 5.4.2
	 */
	public function get_custom_help_url() {
		$help_url = $this->tp_help;

		return esc_url( $help_url );
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
			'heading_title_layout_section',
			array(
				'label' => esc_html__( 'Layout', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tpae_preset_controller',
			array(
				'type'        => 'tpae_preset_button',
				'temp_id'     => 12427,
				'label_block' => true,
			)
		);
		$this->add_control(
			'heading_style',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Style', 'tpebl' ),
				'default' => 'style_1',
				'options' => array(
					'style_1' => esc_html__( 'Modern', 'tpebl' ),
					'style_2' => esc_html__( 'Simple', 'tpebl' ),
					'style_4' => esc_html__( 'Classic', 'tpebl' ),
					'style_5' => esc_html__( 'Double Border', 'tpebl' ),
					'style_6' => esc_html__( 'Vertical Border', 'tpebl' ),
					'style_7' => esc_html__( 'Dashing Dots', 'tpebl' ),
					'style_8' => esc_html__( 'Unique', 'tpebl' ),
					'style_9' => esc_html__( 'Stylish', 'tpebl' ),
				),
			)
		);
		$this->add_control(
			'style1_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Display the heading with a clean and modern design style.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_1',
				),
			)
		);
		$this->add_control(
			'style2_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Display the heading with a minimal and simple layout.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_2',
				),
			)
		);
		$this->add_control(
			'style4_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Display the heading with a traditional, classic look.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_4',
				),
			)
		);
		$this->add_control(
			'style5_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Add a double border style around the heading.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_5',
				),
			)
		);
		$this->add_control(
			'style6_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Add a vertical border design to the heading.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_6',
				),
			)
		);
		$this->add_control(
			'style7_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Display the heading with dotted decorative styling.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_7',
				),
			)
		);
		$this->add_control(
			'style8_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Apply a unique visual style to highlight the heading.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_8',
				),
			)
		);
		$this->add_control(
			'style9_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'Display the heading with a stylish and eye-catching design.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition' => array(
					'heading_style' => 'style_9',
				),
			)
		);
		$this->add_control(
			'select_heading',
			array(
				'label'   => esc_html__( 'Select Heading', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default'    => esc_html__( 'Default', 'tpebl' ),
					'page_title' => esc_html__( 'Page Title', 'tpebl' ),
				),
			)
		);
		$this->add_control(
			'how_it_works_page_title',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'slide-out-discount-code-card-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'select_heading' => array( 'page_title' ),
				),
			)
		);
		$this->add_responsive_control(
			'sub_title_align',
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
				'prefix_class' => 'text-%s',
				'default'      => 'center',
				'separator'    => 'before',
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'heading_title_content_section',
			array(
				'label' => esc_html__( 'Content', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'title',
			array(
				'type'      => Controls_Manager::TEXT,
				'label'     => esc_html__( 'Heading Title', 'tpebl' ),
				'ai'        => false,
				'default'   => esc_html__( 'Heading', 'tpebl' ),
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'select_heading' => 'default',
				),
			)
		);
		$this->add_control(
			'sub_title',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Sub Title', 'tpebl' ),
				'ai'      => false,
				'default' => esc_html__( 'Sub Title', 'tpebl' ),
				'dynamic' => array(
					'active' => true,
				),
			)
		);
		$this->add_control(
			'title_s',
			array(
				'type'      => Controls_Manager::TEXT,
				'label'     => esc_html__( 'Extra Title', 'tpebl' ),
				'ai'        => false,
				'default'   => esc_html__( 'Title', 'tpebl' ),
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'heading_style' => 'style_1',
				),
			)
		);
		$this->add_control(
			'heading_s_style',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Extra Title Position', 'tpebl' ),
				'default'   => 'text_after',
				'options'   => array(
					'text_after'  => esc_html__( 'Prefix', 'tpebl' ),
					'text_before' => esc_html__( 'Postfix', 'tpebl' ),
				),
				'condition' => array(
					'heading_style' => 'style_1',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'heading_title_extra_section',
			array(
				'label' => esc_html__( 'Extra Option', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'heading_title_subtitle_limit',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Heading & Sub Title Limit', 'tpebl' ),
						esc_url( $this->tp_doc . 'limit-word-count-in-heading-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);
		$this->add_control(
			'display_heading_title_limit',
			array(
				'label'        => esc_html__( 'Heading Title Limit', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_on'     => esc_html__( 'Show', 'tpebl' ),
				'label_off'    => esc_html__( 'Hide', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'heading_title_subtitle_limit' => 'yes',
				),
			)
		);
		$this->start_popover();
		$this->add_control(
			'heading_title_limit_heading',
			array(
				'label'     => esc_html__( 'Heading Title Limit', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			)
		);
		$this->add_control(
			'display_heading_title_by',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Limit on', 'tpebl' ),
				'default'   => 'char',
				'options'   => array(
					'char' => esc_html__( 'Character', 'tpebl' ),
					'word' => esc_html__( 'Word', 'tpebl' ),
				),
				'condition' => array(
					'heading_title_subtitle_limit' => 'yes',
					'display_heading_title_limit'  => 'yes',
				),
			)
		);
		$this->add_control(
			'display_heading_title_input',
			array(
				'label'     => esc_html__( 'Heading Title Count', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 1000,
				'step'      => 1,
				'condition' => array(
					'heading_title_subtitle_limit' => 'yes',
					'display_heading_title_limit'  => 'yes',
				),
			)
		);
		$this->add_control(
			'display_title_3_dots',
			array(
				'label'     => esc_html__( 'Display Dots', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
				'condition' => array(
					'heading_title_subtitle_limit' => 'yes',
					'display_heading_title_limit'  => 'yes',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'display_sub_title_limit',
			array(
				'label'        => esc_html__( 'Sub Title Limit', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_on'     => esc_html__( 'Show', 'tpebl' ),
				'label_off'    => esc_html__( 'Hide', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'heading_title_subtitle_limit' => 'yes',
				),
			)
		);
		$this->start_popover();
		$this->add_control(
			'sub_title_limit_heading',
			array(
				'label'     => esc_html__( 'Sub Title Limit', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			)
		);
		$this->add_control(
			'display_sub_title_by',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Limit on', 'tpebl' ),
				'default'   => 'char',
				'options'   => array(
					'char' => esc_html__( 'Character', 'tpebl' ),
					'word' => esc_html__( 'Word', 'tpebl' ),
				),
				'condition' => array(
					'heading_title_subtitle_limit' => 'yes',
					'display_sub_title_limit'      => 'yes',
				),
			)
		);
		$this->add_control(
			'display_sub_title_input',
			array(
				'label'     => esc_html__( 'Sub Title Count', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 1000,
				'step'      => 1,
				'condition' => array(
					'heading_title_subtitle_limit' => 'yes',
					'display_sub_title_limit'      => 'yes',
				),
			)
		);
		$this->add_control(
			'display_sub_title_3_dots',
			array(
				'label'     => esc_html__( 'Display Dots', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
				'condition' => array(
					'heading_title_subtitle_limit' => 'yes',
					'display_sub_title_limit'      => 'yes',
				),
			)
		);
		$this->end_popover();
		$this->end_controls_section();
		$this->start_controls_section(
			'tpebl_animated_text',
			array(
				'label' => esc_html__( 'Title Text Animation', 'tpebl' ),
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
				'description'  => esc_html__( 'Toggle to enable GSAP-powered text animations for the title.', 'tpebl' ),
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
				'description' => esc_html__( 'Select between basic animation presets or global animations defined in Site Settings.', 'tpebl' ),
				'condition' => array(
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
					$name                  = $animation['name'] ?? 'Unnamed';
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
					'condition' => array(
						'text_animations'       => 'tp_global',
						'enable_text_animation' => 'yes',
					),
					'description' => esc_html__( 'Choose a text animation previously created in the Global Scroll Interactions section of Site Settings.', 'tpebl' ),
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
				'description' => esc_html__( 'Select the desired animation style, ranging from simple transitions to complex scatter effects.', 'tpebl' ),
				'options'   => array(
					'normal'   => esc_html__( 'Normal', 'tpebl' ),
					'explode'  => esc_html__( 'Explode / Scatter', 'tpebl' ),
					'scramble' => esc_html__( 'Scramble Text', 'tpebl' ),
					'typing'   => esc_html__( 'Typing Effect', 'tpebl' ),
				),
				'condition' => array(
					'enable_text_animation' => 'yes',
					'text_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'tp_tansformtion_toggel',
			array(
				'label'        => esc_html__( 'Transform Effects ', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( "Enable custom transformation properties like position, scale, and rotation for the 'Normal' animation type.", 'tpebl' ),
				'condition'    => array(
					'text_animation_type'   => 'normal',
					'enable_text_animation' => 'yes',
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'tp_scrub',
			array(
				'label'        => __( 'Enable Scroll Scrub', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'tpebl' ),
				'label_off'    => __( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable this to sync the animation progress directly with the page scroll speed.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation' => 'yes',
					'text_trigger'          => 'onscroll',
					'text_animation_type!'  => array( 'typing', 'scramble' ),
					'text_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'heading_animation_controls',
			array(
				'label'        => __( 'Animation Controls', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => __( 'Enable', 'tpebl' ),
				'label_on'     => __( 'Disable', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable custom duration, delay, and stagger timing for basic presets.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation' => 'yes',
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
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
					'text_animations'       => 'tp_basic',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'tpebl_animated_text_sub_txt',
			array(
				'label' => esc_html__( 'Sub Title Text Animation', 'tpebl' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_text_animation_sub_txt',
			array(
				'label'        => esc_html__( 'Enable Animation', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Toggle to enable GSAP-powered text animations for the sub-title.', 'tpebl' ),
			)
		);
		$this->add_control(
			'sub_text_animations',
			array(
				'label'     => esc_html__( 'Animation', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'tp_basic',
				'options'   => array(
					'tp_basic'  => esc_html__( 'Basic', 'tpebl' ),
					'tp_global' => esc_html__( 'Global', 'tpebl' ),
				),
				'description' => esc_html__( 'Select between basic animation presets or global animations defined in Site Settings.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
				),
			)
		);

		$theplus_options = get_option( 'theplus_options' );
		$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

		$text_global_enabled = in_array( 'plus_text_global_animation', $extras_elements );

		$sub_global_animations = array();
		$sub_global_options    = array();

		$sub_global_options = array( '' => esc_html__( 'Select Animation', 'tpebl' ) ) + $sub_global_options;

		if ( $text_global_enabled && class_exists( '\ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global' ) ) {
			$sub_global_animations = \ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global::get_text_global_gsap_list();

			if ( ! empty( $sub_global_animations ) ) {
				foreach ( $sub_global_animations as $animation ) {
					$id                        = $animation['_id'] ?? '';
					$name                      = $animation['name'] ?? 'Unnamed';
					$sub_global_options[ $id ] = $name;
				}
			}
		}

		if ( $text_global_enabled ) {
			$this->add_control(
				'tp_select_sub_text_global_animation',
				array(
					'label'     => esc_html__( 'Global Animation', 'tpebl' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $sub_global_options,
					'default'   => '',
					'condition' => array(
						'sub_text_animations'           => 'tp_global',
						'enable_text_animation_sub_txt' => 'yes',
					),
					'description' => esc_html__( 'Choose a text animation previously created in the Global Scroll Interactions section of Site Settings.', 'tpebl' ),
				)
			);
		} else {
			$this->add_control(
				'tp_sub_text_global_animation_notice',
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
						'sub_text_animations' => 'tp_global',
					),
				)
			);
		}
		$this->add_control(
			'text_animation_type_sub_txt',
			array(
				'label'     => esc_html__( 'Animation Type', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'normal',
				'description' => esc_html__( 'Select the desired animation style for the sub-title text.', 'tpebl' ),
				'options'   => array(
					'normal'   => esc_html__( 'Normal', 'tpebl' ),
					'explode'  => esc_html__( 'Explode / Scatter', 'tpebl' ),
					'scramble' => esc_html__( 'Scramble Text', 'tpebl' ),
					'typing'   => esc_html__( 'Typing Effect', 'tpebl' ),
				),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'tp_tansformtion_toggel_sub_txt',
			array(
				'label'        => esc_html__( 'Transform Effects ', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( "Enable custom transformation properties for the 'Normal' animation type on sub-title.", 'tpebl' ),
				'condition'    => array(
					'text_animation_type_sub_txt'   => 'normal',
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);

		$this->start_popover();
		$this->add_control(
			'transform_x_sub_txt',
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
			'transform_y_sub_txt',
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
			'transform_skewx_sub_txt',
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
			'transform_skewy_sub_txt',
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
			'transform_scale_sub_txt',
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
			'transform_rotation_sub_txt',
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
			'transform_origin_sub_txt',
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
			'split_type_sub_txt',
			array(
				'label'     => esc_html__( 'Split Type', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'chars',
				'options'   => array(
					'chars' => esc_html__( 'Characters', 'tpebl' ),
					'words' => esc_html__( 'Words', 'tpebl' ),
				),
				'description' => esc_html__( 'Determine how the sub-title text is divided for the animation.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'text_animation_type_sub_txt!'  => array( 'typing', 'scramble' ),
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'text_trigger_sub_txt',
			array(
				'label'     => esc_html__( 'Animation Trigger', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'onload',
				'options'   => array(
					'onload'   => esc_html__( 'On Load', 'tpebl' ),
					'onscroll' => esc_html__( 'On Scroll', 'tpebl' ),
					'onhover'  => esc_html__( 'On Hover', 'tpebl' ),
				),
				'description' => esc_html__( 'Set when the sub-title animation starts.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'tp_scrub_sub_txt',
			array(
				'label'        => __( 'Enable Scroll Scrub', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'tpebl' ),
				'label_off'    => __( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable this to sync the sub-title animation progress directly with the page scroll.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation_sub_txt' => 'yes',
					'text_trigger_sub_txt'          => 'onscroll',
					'text_animation_type_sub_txt!'  => array( 'typing', 'scramble' ),
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'sub_animation_controls',
			array(
				'label'        => __( 'Animation Controls', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => __( 'Enable', 'tpebl' ),
				'label_on'     => __( 'Disable', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable custom timing controls for the sub-title animation.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->start_popover();
		$this->add_control(
			'text_duration_sub_txt',
			array(
				'label'     => esc_html__( 'Duration', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 1.2,
				'description' => esc_html__( 'Total time for the sub-title animation to complete.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'text_delay_sub_txt',
			array(
				'label'     => esc_html__( 'Delay', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0.3,
				'description' => esc_html__( 'Delay time before the sub-title animation begins.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'text_stagger_sub_txt',
			array(
				'label'     => esc_html__( 'Stagger', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0.04,
				'description' => esc_html__( 'Time interval between each animated part of the sub-title.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'text_animation_type_sub_txt!'  => array( 'typing', 'scramble' ),
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'sub_text_animation_controls_label',
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
					'enable_text_animation_sub_txt' => 'yes',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'text_ease_sub_txt',
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
				'description' => esc_html__( 'Select an easing effect for the sub-title animation.', 'tpebl' ),
				'condition' => array(
					'enable_text_animation_sub_txt' => 'yes',
					'text_animation_type_sub_txt!'  => 'typing',
					'sub_text_animations'           => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'text_repeat_sub_txt',
			array(
				'label'        => esc_html__( 'Repeat', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Allow the sub-title animation to play repeatedly.', 'tpebl' ),
				'condition'    => array(
					'enable_text_animation_sub_txt' => 'yes',
					// 'text_animation_type_sub_txt!'  => 'typing',
					'sub_text_animations'           => 'tp_basic',
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
						'url'   => 'https://theplusaddons.com/help/heading-title/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget',
					),
					array(
						'label' => esc_html__( 'Watch Video', 'tpebl' ),
						'url'   => 'https://www.youtube.com/watch?v=OcJUA6gL_0Q&t',
					),
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_styling',
			array(
				'label'     => esc_html__( 'Separator Settings', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'heading_style!' => array( 'style_1', 'style_2', 'style_8' ),
				),
			)
		);
		$this->add_control(
			'sep_img',
			array(
				'label'     => esc_html__( 'Separator With Image', 'tpebl' ),
				'ai'        => false,
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					'heading_style' => 'style_4',
				),
			)
		);
		$this->add_control(
			'input_margin',
			array(
				'label'      => esc_html__( 'Margin', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .heading .sub-style .vertical-divider,
					 {{WRAPPER}} .heading-title .separator,
					 {{WRAPPER}} .heading.style-5 .heading-title:after,.heading.style-5 .heading-title:before,
					 {{WRAPPER}} .heading.style-7 .head-title:after,
					 {{WRAPPER}} .heading.style-9 .head-title .seprator.sep-l ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'double_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4d4d4d',
				'selectors' => array(
					'{{WRAPPER}} .heading.style-5 .heading-title:before,{{WRAPPER}} .heading.style-5 .heading-title:after' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'heading_style' => 'style_5',
				),
			)
		);
		$this->add_control(
			'double_top',
			array(
				'label'     => esc_html__( 'Top Separator Height', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => -50,
				'step'      => 1,
				'default'   => 6,
				'condition' => array(
					'heading_style' => 'style_5',
				),
				'selectors' => array(
					'{{WRAPPER}} .heading.style-5 .heading-title:before' => 'height: {{VALUE}}px;',
				),

			)
		);
		$this->add_control(
			'double_bottom',
			array(
				'label'     => esc_html__( 'Bottom Separator Height', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => -50,
				'step'      => 1,
				'default'   => 2,
				'condition' => array(
					'heading_style' => 'style_5',
				),
				'selectors' => array(
					'{{WRAPPER}} .heading.style-5 .heading-title:after' => 'height: {{VALUE}}px;',
				),

			)
		);
		$this->add_control(
			'sep_clr',
			array(
				'label'     => esc_html__( 'Separator Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4099c3',
				'selectors' => array(
					'{{WRAPPER}} .heading .title-sep' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'heading_style' => array( 'style_4', 'style_9' ),
				),
			)
		);
		$this->add_responsive_control(
			'sep_width',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Separator Width', 'tpebl' ),
				'size_units'  => array( '%', 'px' ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'range'       => array(
					'' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 2,
					),
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .heading .title-sep,{{WRAPPER}} .heading .seprator' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'heading_style' => array( 'style_4', 'style_9' ),
				),
			)
		);
		$this->add_control(
			'dot_color',
			array(
				'label'     => esc_html__( 'Separator Dot Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ca2b2b',
				'selectors' => array(
					'{{WRAPPER}} .heading .sep-dot' => 'color: {{VALUE}};',
					'{{WRAPPER}} .heading.style-7 .head-title:after' => 'color: {{VALUE}}; text-shadow: 15px 0 {{VALUE}}, -15px 0 {{VALUE}};',
				),
				'condition' => array(
					'heading_style' => array( 'style_7', 'style_9' ),
				),
			)
		);
		$this->add_responsive_control(
			'sep_height',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Separator Height', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'default'     => array(
					'unit' => 'px',
					'size' => 2,
				),
				'range'       => array(
					'' => array(
						'min'  => 0,
						'max'  => 10,
						'step' => 1,
					),
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .heading .title-sep' => 'border-width: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'heading_style' => 'style_4',
				),
			)
		);
		$this->add_control(
			'top_clr_width',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Width', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 2,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'heading_style' => 'style_6',
				),
				'selectors'   => array(
					'{{WRAPPER}} .heading .vertical-divider' => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->add_control(
			'top_clr_height',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Height', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 30,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'heading_style' => 'style_6',
				),
				'selectors'   => array(
					'{{WRAPPER}} .heading .vertical-divider' => 'height: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->add_control(
			'top_clr',
			array(
				'label'     => esc_html__( 'Separator Vertical Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e73be',
				'selectors' => array(
					'{{WRAPPER}} .heading .vertical-divider' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'heading_style' => 'style_6',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_title_styling',
			array(
				'label'     => esc_html__( 'Main Title', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'title!' => '',
				),

			)
		);
		$this->add_responsive_control(
			'title_sep_spacing',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Separator Spacing', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 5,
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .heading.heading_style .head-title > .heading-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'condition'   => array(
					'heading_style' => 'style_4',
				),
			)
		);
		$this->add_control(
			'title_h',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Title Tag', 'tpebl' ),
				'default' => 'h2',
				'options' => l_theplus_get_tags_options( 'a' ),
			)
		);
		$this->add_control(
			'title_link',
			array(
				'label'       => esc_html__( 'Heading Title Link', 'tpebl' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array(
					'active' => true,
				),
				'placeholder' => esc_html__( 'https://www.demo-link.com', 'tpebl' ),
				'condition'   => array(
					'title_h' => 'a',
				),
			)
		);
		$this->add_responsive_control(
			's_maintitle_pg',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .heading_style .heading-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'heading_style' => array( 'style_1', 'style_2' ),
				),
			)
		);
		$this->start_controls_tabs( 'tabs_title_style' );
		$this->start_controls_tab(
			'tab_title_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title Typography', 'tpebl' ),
				'selector' => '{{WRAPPER}} .heading .heading-title',
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'       => esc_html__( 'Title Color', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'solid'    => array(
						'title' => esc_html__( 'Solid', 'tpebl' ),
						'icon'  => 'eicon-paint-brush',
					),
					'gradient' => array(
						'title' => esc_html__( 'Gradient', 'tpebl' ),
						'icon'  => 'eicon-barcode',
					),
				),
				'label_block' => false,
				'default'     => 'solid',
				'toggle'      => true,
			)
		);
		$this->add_control(
			'title_solid_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .heading .heading-title' => 'color: {{VALUE}};',
				),
				'default'   => '#313131',
				'condition' => array(
					'title_color' => array( 'solid' ),
				),
			)
		);
		$this->add_control(
			'title_gradient_color1',
			array(
				'label'     => esc_html__( 'Color 1', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'orange',
				'condition' => array(
					'title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'title_gradient_color1_control',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Color 1 Location', 'tpebl' ),
				'size_units'  => array( '%' ),
				'default'     => array(
					'unit' => '%',
					'size' => 0,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'title_color' => 'gradient',
				),
				'of_type'     => 'gradient',
			)
		);
		$this->add_control(
			'title_gradient_color2',
			array(
				'label'     => esc_html__( 'Color 2', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'cyan',
				'condition' => array(
					'title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'title_gradient_color2_control',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Color 2 Location', 'tpebl' ),
				'size_units'  => array( '%' ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'title_color' => 'gradient',
				),
				'of_type'     => 'gradient',
			)
		);
		$this->add_control(
			'title_gradient_style',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Gradient Style', 'tpebl' ),
				'default'   => 'linear',
				'options'   => l_theplus_get_gradient_styles(),
				'condition' => array(
					'title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'title_gradient_angle',
			array(
				'type'       => Controls_Manager::SLIDER,
				'label'      => esc_html__( 'Gradient Angle', 'tpebl' ),
				'size_units' => array( 'deg' ),
				'default'    => array(
					'unit' => 'deg',
					'size' => 180,
				),
				'range'      => array(
					'deg' => array(
						'step' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .heading .heading-title' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{title_gradient_color1.VALUE}} {{title_gradient_color1_control.SIZE}}{{title_gradient_color1_control.UNIT}}, {{title_gradient_color2.VALUE}} {{title_gradient_color2_control.SIZE}}{{title_gradient_color2_control.UNIT}})',
				),
				'condition'  => array(
					'title_color'          => array( 'gradient' ),
					'title_gradient_style' => array( 'linear' ),
				),
				'of_type'    => 'gradient',
			)
		);
		$this->add_control(
			'title_gradient_position',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Position', 'tpebl' ),
				'options'   => l_theplus_get_position_options(),
				'default'   => 'center center',
				'selectors' => array(
					'{{WRAPPER}} .heading .heading-title' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{title_gradient_color1.VALUE}} {{title_gradient_color1_control.SIZE}}{{title_gradient_color1_control.UNIT}}, {{title_gradient_color2.VALUE}} {{title_gradient_color2_control.SIZE}}{{title_gradient_color2_control.UNIT}})',
				),
				'condition' => array(
					'title_color'          => array( 'gradient' ),
					'title_gradient_style' => 'radial',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_title_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_hover_typography',
				'label'    => esc_html__( 'Title Typography', 'tpebl' ),
				'selector' => '{{WRAPPER}} .heading .heading-title:hover',
			)
		);
		$this->add_control(
			'title_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .heading .heading-title:hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'title_shadow',
				'selectors' => '{{WRAPPER}} .heading .heading-title',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'special_effect',
			array(
				'label'     => esc_html__( 'Special Effect', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
				'condition' => array(
					'heading_style' => array( 'style_1', 'style_2', 'style_8' ),
				),
			)
		);
		$this->add_control(
			'special_effect_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'condition'   => array(
					'heading_style'  => array( 'style_1', 'style_2', 'style_8' ),
					'special_effect' => 'yes',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_sub_title_styling',
			array(
				'label'     => esc_html__( 'Sub Title', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'sub_title!' => '',
				),
			)
		);
		$this->add_responsive_control(
			'subtitle_sep_spacing',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Separator Spacing', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 5,
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .heading.heading_style .sub-heading > .heading-sub-title' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
				'condition'   => array(
					'heading_style' => 'style_4',
				),
			)
		);
		$this->add_control(
			'sub_title_tag',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Subtitle Tag', 'tpebl' ),
				'default' => 'h3',
				'options' => l_theplus_get_tags_options(),
			)
		);
		$this->add_responsive_control(
			's_subtitle_pg',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .heading_style .heading-sub-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'heading_style' => array( 'style_1', 'style_2' ),
				),
			)
		);
		$this->start_controls_tabs( 'tabs_sub_title_style' );
		$this->start_controls_tab(
			'tab_sub_title_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sub_title_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'selector' => '{{WRAPPER}} .heading .heading-sub-title',
			)
		);
		$this->add_control(
			'sub_title_color',
			array(
				'label'       => esc_html__( 'Subtitle Title Color', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'solid'    => array(
						'title' => esc_html__( 'Solid', 'tpebl' ),
						'icon'  => 'eicon-paint-brush',
					),
					'gradient' => array(
						'title' => esc_html__( 'Gradient', 'tpebl' ),
						'icon'  => 'eicon-barcode',
					),
				),
				'label_block' => false,
				'default'     => 'solid',
				'toggle'      => true,
			)
		);
		$this->add_control(
			'sub_title_solid_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .heading .heading-sub-title' => 'color: {{VALUE}};',
				),
				'default'   => '#313131',
				'condition' => array(
					'sub_title_color' => array( 'solid' ),
				),
			)
		);
		$this->add_control(
			'sub_title_gradient_color1',
			array(
				'label'     => esc_html__( 'Color 1', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'orange',
				'condition' => array(
					'sub_title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'sub_title_gradient_color1_control',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Color 1 Location', 'tpebl' ),
				'size_units'  => array( '%' ),
				'default'     => array(
					'unit' => '%',
					'size' => 0,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'sub_title_color' => 'gradient',
				),
				'of_type'     => 'gradient',
			)
		);
		$this->add_control(
			'sub_title_gradient_color2',
			array(
				'label'     => esc_html__( 'Color 2', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'cyan',
				'condition' => array(
					'sub_title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'sub_title_gradient_color2_control',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Color 2 Location', 'tpebl' ),
				'size_units'  => array( '%' ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'sub_title_color' => 'gradient',
				),
				'of_type'     => 'gradient',
			)
		);
		$this->add_control(
			'sub_title_gradient_style',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Gradient Style', 'tpebl' ),
				'default'   => 'linear',
				'options'   => l_theplus_get_gradient_styles(),
				'condition' => array(
					'sub_title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'sub_title_gradient_angle',
			array(
				'type'       => Controls_Manager::SLIDER,
				'label'      => esc_html__( 'Gradient Angle', 'tpebl' ),
				'size_units' => array( 'deg' ),
				'default'    => array(
					'unit' => 'deg',
					'size' => 180,
				),
				'range'      => array(
					'deg' => array(
						'step' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .heading .heading-sub-title' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{sub_title_gradient_color1.VALUE}} {{sub_title_gradient_color1_control.SIZE}}{{sub_title_gradient_color1_control.UNIT}}, {{sub_title_gradient_color2.VALUE}} {{sub_title_gradient_color2_control.SIZE}}{{sub_title_gradient_color2_control.UNIT}})',
				),
				'condition'  => array(
					'sub_title_color'          => array( 'gradient' ),
					'sub_title_gradient_style' => array( 'linear' ),
				),
				'of_type'    => 'gradient',
			)
		);
		$this->add_control(
			'sub_title_gradient_position',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Position', 'tpebl' ),
				'options'   => l_theplus_get_position_options(),
				'default'   => 'center center',
				'selectors' => array(
					'{{WRAPPER}} .heading .heading-sub-title' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{sub_title_gradient_color1.VALUE}} {{sub_title_gradient_color1_control.SIZE}}{{sub_title_gradient_color1_control.UNIT}}, {{sub_title_gradient_color2.VALUE}} {{sub_title_gradient_color2_control.SIZE}}{{sub_title_gradient_color2_control.UNIT}})',
				),
				'condition' => array(
					'sub_title_color'          => array( 'gradient' ),
					'sub_title_gradient_style' => 'radial',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_sub_title_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sub_title_hover_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'selector' => '{{WRAPPER}} .heading .heading-sub-title:hover',
			)
		);
		$this->add_control(
			'sub_title_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .heading .heading-sub-title:hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
		$this->start_controls_section(
			'section_extra_title_styling',
			array(
				'label'     => esc_html__( 'Extra Title', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'heading_style' => 'style_1',
					'title_s!'      => '',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_ex_title_style' );
		$this->start_controls_tab(
			'tab_ex_title_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'ex_title_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'selector' => '{{WRAPPER}} .heading .title-s',
			)
		);
		$this->add_control(
			'ex_title_color',
			array(
				'label'       => esc_html__( 'Extra Title Color', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'solid'    => array(
						'title' => esc_html__( 'Solid', 'tpebl' ),
						'icon'  => 'eicon-paint-brush',
					),
					'gradient' => array(
						'title' => esc_html__( 'Gradient', 'tpebl' ),
						'icon'  => 'eicon-barcode',
					),
				),
				'label_block' => false,
				'default'     => 'solid',
				'toggle'      => true,
			)
		);
		$this->add_control(
			'ex_title_solid_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .heading .title-s' => 'color: {{VALUE}};',
				),
				'default'   => '#313131',
				'condition' => array(
					'ex_title_color' => array( 'solid' ),
				),
			)
		);
		$this->add_control(
			'ex_title_gradient_color1',
			array(
				'label'     => esc_html__( 'Color 1', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'orange',
				'condition' => array(
					'ex_title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'ex_title_gradient_color1_control',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Color 1 Location', 'tpebl' ),
				'size_units'  => array( '%' ),
				'default'     => array(
					'unit' => '%',
					'size' => 0,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'ex_title_color' => 'gradient',
				),
				'of_type'     => 'gradient',
			)
		);
		$this->add_control(
			'ex_title_gradient_color2',
			array(
				'label'     => esc_html__( 'Color 2', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'cyan',
				'condition' => array(
					'ex_title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'ex_title_gradient_color2_control',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Color 2 Location', 'tpebl' ),
				'size_units'  => array( '%' ),
				'default'     => array(
					'unit' => '%',
					'size' => 100,
				),
				'render_type' => 'ui',
				'condition'   => array(
					'ex_title_color' => 'gradient',
				),
				'of_type'     => 'gradient',
			)
		);
		$this->add_control(
			'ex_title_gradient_style',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Gradient Style', 'tpebl' ),
				'default'   => 'linear',
				'options'   => l_theplus_get_gradient_styles(),
				'condition' => array(
					'ex_title_color' => 'gradient',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->add_control(
			'ex_title_gradient_angle',
			array(
				'type'       => Controls_Manager::SLIDER,
				'label'      => esc_html__( 'Gradient Angle', 'tpebl' ),
				'size_units' => array( 'deg' ),
				'default'    => array(
					'unit' => 'deg',
					'size' => 180,
				),
				'range'      => array(
					'deg' => array(
						'step' => 10,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .heading .title-s' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{ex_title_gradient_color1.VALUE}} {{ex_title_gradient_color1_control.SIZE}}{{ex_title_gradient_color1_control.UNIT}}, {{ex_title_gradient_color2.VALUE}} {{ex_title_gradient_color2_control.SIZE}}{{ex_title_gradient_color2_control.UNIT}})',
				),
				'condition'  => array(
					'ex_title_color'          => array( 'gradient' ),
					'ex_title_gradient_style' => array( 'linear' ),
				),
				'of_type'    => 'gradient',
			)
		);
		$this->add_control(
			'ex_title_gradient_position',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Position', 'tpebl' ),
				'options'   => l_theplus_get_position_options(),
				'default'   => 'center center',
				'selectors' => array(
					'{{WRAPPER}} .heading .title-s' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{ex_title_gradient_color1.VALUE}} {{ex_title_gradient_color1_control.SIZE}}{{ex_title_gradient_color1_control.UNIT}}, {{ex_title_gradient_color2.VALUE}} {{ex_title_gradient_color2_control.SIZE}}{{ex_title_gradient_color2_control.UNIT}})',
				),
				'condition' => array(
					'ex_title_color'          => array( 'gradient' ),
					'ex_title_gradient_style' => 'radial',
				),
				'of_type'   => 'gradient',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_ex_title_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'ex_title_hover_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'selector' => '{{WRAPPER}} .heading .title-s:hover',
			)
		);
		$this->add_control(
			'ex_title_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .heading .title-s:hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
		$this->start_controls_section(
			'section_settings_option_styling',
			array(
				'label' => esc_html__( 'Advanced', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'position',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Title Position', 'tpebl' ),
				'default' => 'after',
				'options' => array(
					'before' => esc_html__( 'Before Title', 'tpebl' ),
					'after'  => esc_html__( 'After Title', 'tpebl' ),
				),
			)
		);
		$this->add_control(
			'mobile_center_align',
			array(
				'type'      => Controls_Manager::SWITCHER,
				'label'     => esc_html__( 'Center Alignment In Mobile', 'tpebl' ),
				'default'   => 'no',
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
			)
		);
		$this->end_controls_section();
		include L_THEPLUS_PATH . 'modules/widgets/theplus-widget-animation.php';

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

		include L_THEPLUS_PATH . 'modules/widgets/theplus-profeatures.php';
	}

	/**
	 * Load Widget limit Words
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 *
	 * @param string $string The string to limit the words for.
	 * @param int    $word_limit The maximum number of words to keep in the string.
	 * @return string The modified string with the limited number of words.
	 */
	protected function l_limit_words( $string, $word_limit ) {
		$words = explode( ' ', $string );
		return implode( ' ', array_splice( $words, 0, $word_limit ) );
	}

	/**
	 * Render Progress Bar
	 *
	 * Written in PHP and HTML.
	 *
	 * @since 1.0.0
	 *
	 * @version 5.4.2
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$heading_style = ! empty( $settings['heading_style'] ) ? $settings['heading_style'] : 'style_1';
		$select_head   = ! empty( $settings['select_heading'] ) ? $settings['select_heading'] : 'default';
		$title         = ! empty( $settings['title'] ) ? $settings['title'] : '';
		$head_limit    = ! empty( $settings['display_heading_title_limit'] ) ? $settings['display_heading_title_limit'] : '';
		$head_count    = ! empty( $settings['display_heading_title_input'] ) ? $settings['display_heading_title_input'] : '';

		$head_by   = ! empty( $settings['display_heading_title_by'] ) ? $settings['display_heading_title_by'] : 'char';
		$dots      = ! empty( $settings['display_title_3_dots'] ) ? $settings['display_title_3_dots'] : '';
		$sep_img   = ! empty( $settings['sep_img']['url'] ) ? $settings['sep_img']['url'] : '';
		$t_color   = ! empty( $settings['title_color'] ) ? $settings['title_color'] : '';
		$ex_color  = ! empty( $settings['ex_title_color'] ) ? $settings['ex_title_color'] : '';
		$sub_color = ! empty( $settings['sub_title_color'] ) ? $settings['sub_title_color'] : '';

		$animation_effects = ! empty( $settings['animation_effects'] ) ? $settings['animation_effects'] : '';
		$animation_delay   = ! empty( $settings['animation_delay']['size'] ) ? $settings['animation_delay']['size'] : 50;
		$ani_duration      = ! empty( $settings['animation_duration_default'] ) ? $settings['animation_duration_default'] : '';

		$ani_size  = ! empty( $settings['animate_duration']['size'] ) ? $settings['animate_duration']['size'] : 50;
		$out_ani   = ! empty( $settings['animation_out_effects'] ) ? $settings['animation_out_effects'] : '';
		$ani_delay = ! empty( $settings['animation_out_delay']['size'] ) ? $settings['animation_out_delay']['size'] : '';
		$out_d_ani = ! empty( $settings['animation_out_duration_default'] ) ? $settings['animation_out_duration_default'] : '';
		$out_speed = ! empty( $settings['animation_out_duration']['size'] ) ? $settings['animation_out_duration']['size'] : 50;

		$sub_title_count = ! empty( $settings['display_sub_title_input'] ) ? $settings['display_sub_title_input'] : '';
		$mobaile_align   = ! empty( $settings['mobile_center_align'] ) ? $settings['mobile_center_align'] : '';
		$sub_limit_by    = ! empty( $settings['display_sub_title_by'] ) ? $settings['display_sub_title_by'] : 'char';

		$title_link = ! empty( $settings['title_link']['url'] ) ? $settings['title_link']['url'] : '';
		$title_h    = ! empty( $settings['title_h'] ) ? $settings['title_h'] : 'h2';
		$sub_title  = ! empty( $settings['sub_title'] ) ? $settings['sub_title'] : '';
		$sub_limit  = ! empty( $settings['display_sub_title_limit'] ) ? $settings['display_sub_title_limit'] : '';
		$sub_dots   = ! empty( $settings['display_sub_title_3_dots'] ) ? $settings['display_sub_title_3_dots'] : '';
		$sub_tag    = ! empty( $settings['sub_title_tag'] ) ? $settings['sub_title_tag'] : 'h3';
		$position   = ! empty( $settings['position'] ) ? $settings['position'] : 'after';
		$title_s    = ! empty( $settings['title_s'] ) ? $settings['title_s'] : '';

		$heading_title_text = '';
		if ( 'page_title' === $select_head ) {
			$heading_title_text = get_the_title();
		} elseif ( ! empty( $title ) ) {

			if ( ( 'yes' === $head_limit ) && ! empty( $head_count ) ) {

				if ( ! empty( $head_by ) ) {
					if ( 'char' === $head_by ) {
						$heading_title_text = substr( $title, 0, $head_count );

					} elseif ( 'word' === $head_by ) {
						$heading_title_text = $this->l_limit_words( $title, $head_count );
					}
				}

				if ( 'char' === $head_by ) {

					if ( strlen( $title ) > $head_count ) {
						if ( 'yes' === $dots ) {
							$heading_title_text .= '...';
						}
					}
				} elseif ( 'word' === $head_by ) {

					if ( str_word_count( $title ) > $head_count ) {

						if ( 'yes' === $dots ) {
							$heading_title_text .= '...';
						}
					}
				}
			} else {
				$heading_title_text = $title;
			}
		}

		$img_src = '';

		$sub_gradient_cass     = '';
		$title_s_gradient_cass = '';
		$title_gradient_cass   = '';

		if ( ! empty( $sep_img ) ) {
			$image_id = $settings['sep_img']['id'];
			$img_src  = tp_get_image_rander( $image_id, 'full', array( 'class' => 'service-img' ) );
		}

		if ( 'gradient' === $t_color ) {
			$title_gradient_cass = 'heading-title-gradient';
		}
		if ( 'gradient' === $ex_color ) {
			$title_s_gradient_cass = 'heading-title-gradient';
		}
		if ( 'gradient' === $sub_color ) {
			$sub_gradient_cass = 'heading-title-gradient';
		}

		if ( 'no-animation' === $animation_effects ) {
			$animated_class = '';
			$animation_attr = '';
		} else {
			$animate_offset  = '85%';
			$animated_class  = 'animate-general';
			$animation_attr  = ' data-animate-type="' . esc_attr( $animation_effects ) . '" data-animate-delay="' . esc_attr( $animation_delay ) . '"';
			$animation_attr .= ' data-animate-offset="' . esc_attr( $animate_offset ) . '"';

			if ( 'yes' === $ani_duration ) {
				$animate_duration = $ani_size;
				$animation_attr  .= ' data-animate-duration="' . esc_attr( $animate_duration ) . '"';
			}

			if ( 'no-animation' !== $out_ani ) {
				$animation_attr .= ' data-animate-out-type="' . esc_attr( $out_ani ) . '" data-animate-out-delay="' . esc_attr( $ani_delay ) . '"';

				if ( 'yes' === $out_d_ani ) {
					$animation_attr .= ' data-animate-out-duration="' . esc_attr( $out_speed ) . '"';
				}
			}
		}

		$style_class = '';
		if ( 'style_1' === $heading_style ) {
			$style_class = 'style-1';
		} elseif ( 'style_2' === $heading_style ) {
			$style_class = 'style-2';
		} elseif ( 'style_4' === $heading_style ) {
			$style_class = 'style-4';
		} elseif ( 'style_5' === $heading_style ) {
			$style_class = 'style-5';
		} elseif ( 'style_6' === $heading_style ) {
			$style_class = 'style-6';
		} elseif ( 'style_7' === $heading_style ) {
			$style_class = 'style-7';
		} elseif ( 'style_8' === $heading_style ) {
			$style_class = 'style-8';
		} elseif ( 'style_9' === $heading_style ) {
			$style_class = 'style-9';
		} elseif ( 'style_10' === $heading_style ) {
			$style_class = 'style-10';
		} elseif ( 'style_11' === $heading_style ) {
			$style_class = 'style-11';
		}

		$uid = uniqid( 'heading_style' );

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
				$gsap_config = array(
					// Basic Animation
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

					// Transform Options
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
				$data_json   = wp_json_encode( $gsap_config );
			}
		}

		$tp_sub_text_gsap = ! empty( $settings['enable_text_animation_sub_txt'] ) ? $settings['enable_text_animation_sub_txt'] : 'no';

		$data_json_sub_txt = '';

		if ( 'yes' === $tp_sub_text_gsap ) {

			$theplus_options = get_option( 'theplus_options' );
			$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

			$text_global_enabled = in_array( 'plus_text_global_animation', $extras_elements );

			if ( 'tp_global' === $settings['sub_text_animations'] && $text_global_enabled ) {

				$sub_text_global_list = array();
				if ( class_exists( '\ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global' ) ) {
					$sub_text_global_list = \ThePlusAddons\Elementor\Text\TP_GSAP_Text_Global::get_text_global_gsap_list();
				}
				$sub_text_selected_global = $settings['tp_select_sub_text_global_animation'] ?? '';

				foreach ( $sub_text_global_list as $ani ) {

					if ( isset( $ani['_id'] ) && $ani['_id'] === $sub_text_selected_global ) {

						$gsap_config_sub_txt = array(
							// Basic Animation
							'tp_enable_ani_sub_txt'      => $settings['enable_text_animation_sub_txt'] ?? 'no',
							'tp_effect_sub_txt'          => $ani['text_animation_type'] ?? 'normal',
							'tp_split_type_sub_txt'      => $ani['split_type'] ?? 'chars',
							'tp_trigger_sub_txt'         => $ani['text_trigger'] ?? 'onload',
							'tp_duration_sub_txt'        => ! empty( $ani['text_duration'] ) ? $ani['text_duration'] : 1.2,
							'tp_delay_sub_txt'           => ! empty( $ani['text_delay'] ) ? $ani['text_delay'] : 0.3,
							'tp_stagger_sub_txt'         => ! empty( $ani['text_stagger'] ) ? $ani['text_stagger'] : 0.04,
							'tp_ease_sub_txt'            => $ani['text_ease'] ?? 'power3.out',
							'tp_repeat_sub_txt'          => $ani['text_repeat'] ?? 'no',
							'tp_scrub_sub_txt'           => $ani['tp_scrub'] ?? '',

							// Transform Options
							'transform_toggle_sub_txt'   => $ani['tp_tansformtion_toggel'] ?? 'no',
							'transform_x_sub_txt'        => ! empty( $ani['transform_x']['size'] ) ? $ani['transform_x']['size'] : 0,
							'transform_x_unit_sub_txt'   => $ani['transform_x']['unit'] ?? 'px',
							'transform_y_sub_txt'        => ! empty( $ani['transform_y']['size'] ) ? $ani['transform_y']['size'] : 0,
							'transform_y_unit_sub_txt'   => $ani['transform_y']['unit'] ?? 'px',
							'transform_skewx_sub_txt'    => ! empty( $ani['transform_skewx']['size'] ) ? $ani['transform_skewx']['size'] : 0,
							'transform_skewy_sub_txt'    => ! empty( $ani['transform_skewy']['size'] ) ? $ani['transform_skewy']['size'] : 0,
							'transform_scale_sub_txt'    => ! empty( $ani['transform_scale']['size'] ) ? $ani['transform_scale']['size'] : 1,
							'transform_rotation_sub_txt' => ! empty( $ani['transform_rotation']['size'] ) ? $ani['transform_rotation']['size'] : 0,
							'transform_origin_sub_txt'   => $ani['transform_origin'] ?? '50% 50%',
						);

						$data_json_sub_txt = wp_json_encode( $gsap_config_sub_txt );

					}
				}
			} else {
				$gsap_config_sub_txt = array(
					'tp_enable_ani_sub_txt'      => $settings['enable_text_animation_sub_txt'] ?? 'no',
					'tp_effect_sub_txt'          => $settings['text_animation_type_sub_txt'] ?? 'normal',
					'tp_split_type_sub_txt'      => $settings['split_type_sub_txt'] ?? 'chars',
					'tp_trigger_sub_txt'         => $settings['text_trigger_sub_txt'] ?? 'onload',
					'tp_duration_sub_txt'        => ! empty( $settings['text_duration_sub_txt'] ) ? $settings['text_duration_sub_txt'] : 1.2,
					'tp_delay_sub_txt'           => ! empty( $settings['text_delay_sub_txt'] ) ? $settings['text_delay_sub_txt'] : 0.3,
					'tp_stagger_sub_txt'         => ! empty( $settings['text_stagger_sub_txt'] ) ? $settings['text_stagger_sub_txt'] : 0.04,
					'tp_ease_sub_txt'            => $settings['text_ease_sub_txt'] ?? 'power3.out',
					'tp_repeat_sub_txt'          => $settings['text_repeat_sub_txt'] ?? 'no',
					'tp_scrub_sub_txt'           => $settings['tp_scrub_sub_txt'] ?? '',

					'transform_toggle_sub_txt'   => $settings['tp_tansformtion_toggel_sub_txt'] ?? 'no',
					'transform_x_sub_txt'        => ! empty( $settings['transform_x_sub_txt']['size'] ) ? $settings['transform_x_sub_txt']['size'] : 0,
					'transform_x_unit_sub_txt'   => $settings['transform_x_sub_txt']['unit'] ?? 'px',
					'transform_y_sub_txt'        => ! empty( $settings['transform_y_sub_txt']['size'] ) ? $settings['transform_y_sub_txt']['size'] : 0,
					'transform_y_unit_sub_txt'   => $settings['transform_y_sub_txt']['unit'] ?? 'px',
					'transform_skewx_sub_txt'    => ! empty( $settings['transform_skewx_sub_txt']['size'] ) ? $settings['transform_skewx_sub_txt']['size'] : 0,
					'transform_skewy_sub_txt'    => ! empty( $settings['transform_skewy_sub_txt']['size'] ) ? $settings['transform_skewy_sub_txt']['size'] : 0,
					'transform_scale_sub_txt'    => ! empty( $settings['transform_scale_sub_txt']['size'] ) ? $settings['transform_scale_sub_txt']['size'] : 1,
					'transform_rotation_sub_txt' => ! empty( $settings['transform_rotation_sub_txt']['size'] ) ? $settings['transform_rotation_sub_txt']['size'] : 0,
					'transform_origin_sub_txt'   => $settings['transform_origin_sub_txt'] ?? '50% 50%',
				);

				$data_json_sub_txt = wp_json_encode( $gsap_config_sub_txt );
			}
		}

		$heading = '<div class="heading heading_style ' . esc_attr( $uid ) . ' ' . esc_attr( $style_class ) . ' ' . esc_attr( $animated_class ) . '" ' . $animation_attr . ' data-tp-gsap-heading-text="' . esc_attr( $data_json ) . '" data-tp-gsap-sub-heading-text="' . esc_attr( $data_json_sub_txt ) . '">';

		$mobile_center = '';

		if ( 'yes' === $mobaile_align ) {

			if ( 'style_1' === $heading_style || 'style_2' === $heading_style || 'style_4' === $heading_style || 'style_5' === $heading_style || 'style_7' === $heading_style || 'style_9' === $heading_style ) {
				$mobile_center = 'heading-mobile-center';
			}
		}
		$heading .= '<div class="sub-style" >';

		if ( 'style_6' === $heading_style ) {
			$heading .= '<div class="vertical-divider top"> </div>';
		}
		$title_con      = '';
		$s_title_con    = '';
		$title_s_before = '';

		if ( 'style_1' === $heading_style ) {
			$title_s_before .= '<span class="title-s ' . esc_attr( $title_s_gradient_cass ) . '"> ' . wp_kses_post( $title_s ) . ' </span>';
		}

		if ( ! empty( $heading_title_text ) ) {

			if ( ! empty( $title_link ) && 'a' === $title_h ) {
				$this->add_render_attribute( 'titlehref', 'href', esc_url( $title_link ) );

				if ( $settings['title_link']['is_external'] ) {
					$this->add_render_attribute( 'titlehref', 'target', '_blank' );
				}

				if ( $settings['title_link']['nofollow'] ) {
					$this->add_render_attribute( 'titlehref', 'rel', 'nofollow' );
				}
			}

			$title_con      = '<div class="head-title ' . esc_attr( $mobile_center ) . '" > ';
				$title_con .= '<' . esc_attr( l_theplus_validate_html_tag( $title_h ) ) . ' ' . $this->get_render_attribute_string( 'titlehref' ) . ' class="heading-title ' . esc_attr( $mobile_center ) . '  ' . esc_attr( $title_gradient_cass ) . '"  data-hover="' . esc_attr( $heading_title_text ) . '">';

			$hed_text_st = ! empty( $settings['heading_s_style'] ) ? $settings['heading_s_style'] : '';

			if ( 'text_before' === $hed_text_st ) {
				$title_con .= $title_s_before . wp_kses_post( $heading_title_text );
			} else {
				$title_con .= wp_kses_post( $heading_title_text ) . $title_s_before;
			}
				$title_con .= '</' . esc_attr( l_theplus_validate_html_tag( $title_h ) ) . '>';

			if ( 'style_4' === $heading_style || 'style_9' === $heading_style ) {
				$title_con .= '<div class="seprator sep-l" >';
				$title_con .= '<span class="title-sep sep-l" ></span>';

				if ( 'style_9' === $heading_style ) {
					$title_con .= '<div class="sep-dot">.</div>';
				} elseif ( ! empty( $img_src ) ) {
					$title_con .= '<div class="sep-mg">' . $img_src . '</div>';
				}
				$title_con .= '<span class="title-sep sep-r" ></span>';
				$title_con .= '</div>';
			}
			$title_con .= '</div>';
		}
			$sub_title_dis = '';
		if ( ! empty( $sub_title ) ) {
			if ( 'yes' === $sub_limit && ! empty( $sub_title_count ) ) {

				if ( ! empty( $sub_limit_by ) ) {

					if ( 'char' === $sub_limit_by ) {
						$sub_title_dis = substr( $sub_title, 0, $sub_title_count );
						if ( strlen( $sub_title ) > $sub_title_count ) {
							if ( 'yes' === $sub_dots ) {
								$sub_title_dis .= '...';
							}
						}
					} elseif ( 'word' === $sub_limit_by ) {
						$sub_title_dis = $this->l_limit_words( $sub_title, $sub_title_count );
						if ( str_word_count( $sub_title ) > $sub_title_count ) {
							if ( 'yes' === $sub_dots ) {
								$sub_title_dis .= '...';
							}
						}
					}
				}
			} else {
				$sub_title_dis = $sub_title;
			}
			$s_title_con  = '<div class="sub-heading">';
			$s_title_con .= '<' . esc_attr( l_theplus_validate_html_tag( $sub_tag ) ) . ' class="heading-sub-title ' . esc_attr( $mobile_center ) . ' ' . esc_attr( $sub_gradient_cass ) . '"> ' . esc_html( $sub_title_dis ) . ' </' . esc_attr( l_theplus_validate_html_tag( $sub_tag ) ) . '>';
			$s_title_con .= '</div>';
		}
		if ( 'before' === $position ) {
			$heading .= $s_title_con . $title_con;

		}if ( 'after' === $position ) {
			$heading .= $title_con . $s_title_con;
		}
		if ( 'style_6' === $heading_style ) {
			$heading .= '<div class="vertical-divider bottom"> </div>';
		}
				$heading .= '</div>';
			$heading     .= '</div>';

		echo $heading;
	}
}
