<?php
/**
 * Widget Name: Gallery Listing
 * Description: Different style of gallery listing layouts.
 * Author: Theplus
 * Author URI: https://posimyth.com
 *
 * @package ThePlus
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper;

if ( ! class_exists( '\ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper' ) ) {
	include_once L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-scroll-animation-helper.php';
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class L_ThePlus_Gallery_ListOut
 */
class L_ThePlus_Gallery_ListOut extends Widget_Base {

	/**
	 * Document Link For Need help.
	 *
	 * @var tp_doc of the class.
	 */
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
	 * @version 5.4.2
	 */
	public function get_name() {
		return 'tp-gallery-listout';
	}

	/**
	 * Get Widget Title.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	public function get_title() {
		return esc_html__( 'Gallery Listing', 'tpebl' );
	}

	/**
	 * Get Widget Icon.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	public function get_icon() {
		return 'theplus-i-gallery-listing tpae-editor-logo';
	}

	/**
	 * Get Widget categories.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	public function get_categories() {
		return array( 'plus-listing' );
	}

	/**
	 * Get Widget keywords.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	public function get_keywords() {
		return array( 'Tp Gallery Listing', 'Image Gallery', 'Masonry Gallery', 'Metro Gallery', 'Carousel Gallery', 'Filterable Gallery', 'Repeater Gallery', 'ACF Gallery' );
	}

	/**
	 * Update is_reload_preview_required.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	public function is_reload_preview_required() {
		return true;
	}

	/**
	 * Get Widget categories.
	 *
	 * @since 1.0.0
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
	 * @version 5.5.4
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'layout_content_section',
			array(
				'label' => esc_html__( 'Layout', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tpae_preset_controller',
			array(
				'type'        => 'tpae_preset_button',
				'temp_id'     => 17481,
				'label_block' => true,
			)
		);
		$this->add_control(
			'style',
			array(
				'label'       => esc_html__( 'Style', 'tpebl' ),
				'label_block' => true,
				'type'        => Controls_Manager::VISUAL_CHOICE,
				'default'     => 'style-1',
				'options'     => array(
					'style-1' => array(
						'title' => esc_html__( 'Style 1', 'tpebl' ),
						'image' => esc_url( L_THEPLUS_ASSETS_URL . 'images/widget-style/gallery-listing/style-1.svg' ),
					),
					'style-2' => array(
						'title' => esc_html__( 'Style 2', 'tpebl' ),
						'image' => esc_url( L_THEPLUS_ASSETS_URL . 'images/widget-style/gallery-listing/style-2.svg' ),
					),
					'style-3' => array(
						'title' => esc_html__( 'Style 3 (PRO)', 'tpebl' ),
						'image' => esc_url( L_THEPLUS_ASSETS_URL . 'images/widget-style/gallery-listing/style-3-pro.svg' ),
					),
					'style-4' => array(
						'title' => esc_html__( 'Style 4 (PRO)', 'tpebl' ),
						'image' => esc_url( L_THEPLUS_ASSETS_URL . 'images/widget-style/gallery-listing/style-4-pro.svg' ),
					),
				),
				'columns'     => 4,
				'classes'     => 'tpae-visual_choice',
			)
		);
		$this->add_control(
			'style_pro_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'style!' => array( 'style-1', 'style-2' ),
				),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'       => esc_html__( 'Layout', 'tpebl' ),
				'label_block' => true,
				'type'        => Controls_Manager::VISUAL_CHOICE,
				'default'     => 'grid',
				'options'     => array(
					'grid'     => array(
						'title' => esc_html__( 'Grid', 'tpebl' ),
						'image' => L_THEPLUS_ASSETS_URL . 'images/widget-style/listing-layout/grid.svg',
					),
					'masonry'  => array(
						'title' => esc_html__( 'Masonry', 'tpebl' ),
						'image' => L_THEPLUS_ASSETS_URL . 'images/widget-style/listing-layout/masonry.svg',
					),
					'metro'    => array(
						'title' => esc_html__( 'Metro', 'tpebl' ),
						'image' => L_THEPLUS_ASSETS_URL . 'images/widget-style/listing-layout/metro.svg',
					),
					'carousel' => array(
						'title' => esc_html__( 'Carousel (PRO)', 'tpebl' ),
						'image' => L_THEPLUS_ASSETS_URL . 'images/widget-style/listing-layout/carousel-pro.svg',
					),
				),
				'columns'     => 4,
				'classes'     => 'tpae-visual_choice',
			)
		);
		$this->add_control(
			'layout_pro',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'condition'   => array(
					'layout' => 'carousel',
				),
			)
		);
		$this->add_control(
			'how_it_works_masonry',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'create-elementor-masonry-image-gallery/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'layout' => array( 'masonry' ),
				),
			)
		);
		$this->add_control(
			'how_it_works_grid',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'create-image-gallery-in-grid-layout-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'layout' => array( 'grid' ),
				),
			)
		);
		$this->add_control(
			'how_it_works_metro',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'create-elementor-image-gallery-in-metro-layout/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'layout' => array( 'metro' ),
				),
			)
		);
		$this->add_control(
			'popup_style',
			array(
				'label'     => esc_html__( 'Popup Layout', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'separator' => 'before',
				'options'   => array(
					'default' => esc_html__( 'Default Light-box', 'tpebl' ),
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Content', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'gallery_options',
			array(
				'label'   => esc_html__( 'Select Option', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'normal',
				'options' => array(
					'normal'      => esc_html__( 'Normal', 'tpebl' ),
					'repeater'    => esc_html__( 'Repeater (PRO)', 'tpebl' ),
					'acf_gallery' => esc_html__( 'ACF Gallery (PRO)', 'tpebl' ),
				),
			)
		);
		$this->add_control(
			'gallery_images',
			array(
				'label'     => esc_html__( 'Add Images', 'tpebl' ),
				'type'      => Controls_Manager::GALLERY,
				'default'   => array(),
				'condition' => array(
					'gallery_options' => array( 'normal' ),
				),
			)
		);
		$this->add_control(
			'loop_gallery_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'gallery_options' => array( 'repeater', 'acf_gallery' ),
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'columns_section',
			array(
				'label'     => esc_html__( 'Columns Manage', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'layout!' => array( 'carousel' ),
				),
			)
		);
		$this->add_control(
			'desktop_column',
			array(
				'label'     => esc_html__( 'Desktop Column', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => l_theplus_get_columns_list(),
				'condition' => array(
					'layout!' => array( 'metro', 'carousel' ),
				),
			)
		);
		$this->add_control(
			'tablet_column',
			array(
				'label'     => esc_html__( 'Tablet Column', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '4',
				'options'   => l_theplus_get_columns_list(),
				'condition' => array(
					'layout!' => array( 'metro', 'carousel' ),
				),
			)
		);
		$this->add_control(
			'mobile_column',
			array(
				'label'     => esc_html__( 'Mobile Column', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '6',
				'options'   => l_theplus_get_columns_list(),
				'condition' => array(
					'layout!' => array( 'metro', 'carousel' ),
				),
			)
		);
		$this->add_control(
			'metro_column',
			array(
				'label'     => esc_html__( 'Metro Column', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => array(
					'3' => esc_html__( 'Column 3', 'tpebl' ),
					'4' => esc_html__( 'Column 4 (PRO)', 'tpebl' ),
					'5' => esc_html__( 'Column 5 (PRO)', 'tpebl' ),
					'6' => esc_html__( 'Column 6 (PRO)', 'tpebl' ),
				),
				'condition' => array(
					'layout' => array( 'metro' ),
				),
			)
		);
		$this->add_control(
			'metro_style_3',
			array(
				'label'     => esc_html__( 'Metro Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'style-1',
				'options'   => l_theplus_get_style_list( 1 ),
				'condition' => array(
					'metro_column' => '3',
					'layout'       => array( 'metro' ),
				),
			)
		);
		$this->add_responsive_control(
			'columns_gap',
			array(
				'label'      => esc_html__( 'Columns Gap/Space Between', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'    => '15',
					'right'  => '15',
					'bottom' => '15',
					'left'   => '15',
				),
				'separator'  => 'before',
				'condition'  => array(
					'layout!' => array( 'carousel' ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'extra_option_section',
			array(
				'label' => esc_html__( 'Extra Options', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'display_title',
			array(
				'label'     => esc_html__( 'Display Title', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
			)
		);
		$this->add_control(
			'display_title_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text">%s</p>',
						esc_html__( 'Turn this on to show image titles directly inside your gallery items.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'post_title_tag',
			array(
				'label'     => esc_html__( 'Title Tag', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => l_theplus_get_tags_options(),
				'condition' => array(
					'display_title' => 'yes',
				),
			)
		);
		$this->add_control(
			'post_title_tag_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text">%s</p>',
						esc_html__( 'Select the proper heading tag for your image titles to keep your design and SEO consistent.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'display_title' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_excerpt',
			array(
				'label'     => esc_html__( 'Display Excerpt/Content', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'display_excerpt_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text">%s</p>',
						esc_html__( 'Show short captions or descriptions below your gallery images for added context.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'display_box_link',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <img class="pro-badge-img" src="%s" alt="PRO" style="width:32px; vertical-align:middle;" />',
						esc_html__( 'Box Link', 'tpebl' ),
						esc_url( L_THEPLUS_URL . 'assets/images/pro-features/pro-tag.svg' )
					)
				),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'display_box_link_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text">%s</p>',
						esc_html__( ' Activate this to make each gallery item clickable and lead users to the desired link.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'display_box_link_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'display_box_link' => array( 'yes' ),
				),
			)
		);
		$this->add_control(
			'filter_category',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Category Wise Filter', 'tpebl' ),
						esc_url( $this->tp_doc . 'create-filterable-image-gallery-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
				'condition' => array(
					'gallery_options' => 'repeater',
				),
			)
		);
		$this->add_control(
			'filter_category_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'gallery_options' => 'repeater',
					'filter_category' => array( 'yes' ),
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
						'url'   => 'https://theplusaddons.com/help/gallery-listing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget',
					),
					array(
						'label' => esc_html__( 'Watch Video', 'tpebl' ),
						'url'   => 'https://www.youtube.com/watch?v=tw7aIjUKbIk',
					),
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'section_icon_zoom_style',
			array(
				'label' => esc_html__( 'Popup Icon', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'display_icon_zoom',
			array(
				'label'     => esc_html__( 'Display Icon', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
			)
		);
		$this->add_control(
			'custom_icon_image',
			array(
				'label'     => esc_html__( 'Custom Icon', 'tpebl' ),
				'type'      => Controls_Manager::MEDIA,
				'ai'        => false,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Icon Size', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 5,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 22,
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .gallery-list .meta-search-icon a' => 'font-size: {{SIZE}}{{UNIT}};max-width:calc({{SIZE}}{{UNIT}} + 10px );',
				),
				'condition'   => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'icon_bottom_space',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Bottom Space', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 80,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 12,
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .gallery-list .meta-search-icon' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'separator'   => 'before',
				'condition'   => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_icon_style' );
		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);
		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .meta-search-icon a' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);
		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content:hover .meta-search-icon a' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'display_icon_zoom' => 'yes',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_repeat_icon_style',
			array(
				'label'     => esc_html__( 'Extra Icon', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'gallery_options' => 'repeater',
				),
			)
		);
		$this->add_control(
			'section_repeat_icon_style_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => esc_html__( 'Title', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .gallery-list .post-inner-loop .post-title,{{WRAPPER}} .gallery-list .post-inner-loop .post-title a',
			)
		);
		$this->start_controls_tabs( 'tabs_title_style' );
		$this->start_controls_tab(
			'tab_title_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Title Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .post-title,{{WRAPPER}} .gallery-list .post-inner-loop .post-title a' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_title_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_control(
			'title_hover_color',
			array(
				'label'     => esc_html__( 'Title Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content:hover .post-title,{{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content:hover .post-title a' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_excerpt_style',
			array(
				'label'     => esc_html__( 'Excerpt/Content', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'display_excerpt' => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'content_top_space',
			array(
				'type'        => Controls_Manager::SLIDER,
				'label'       => esc_html__( 'Top Space', 'tpebl' ),
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 10,
				),
				'render_type' => 'ui',
				'selectors'   => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .entry-content' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .gallery-list .post-inner-loop .entry-content',
			)
		);
		$this->start_controls_tabs( 'tabs_excerpt_style' );
		$this->start_controls_tab(
			'tab_excerpt_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_control(
			'excerpt_color',
			array(
				'label'     => esc_html__( 'Content Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .entry-content,{{WRAPPER}} .gallery-list .post-inner-loop .entry-content p' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_excerpt_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_control(
			'excerpt_hover_color',
			array(
				'label'     => esc_html__( 'Content Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content:hover .entry-content,{{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content:hover .entry-content p' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_bg_style',
			array(
				'label' => esc_html__( 'Content Background', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'tabs_content_bg_style' );
		$this->start_controls_tab(
			'tab_content_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'contnet_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .gallery-list.gallery-style-1 .gallery-list-content .post-content-center',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_content_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'content_hover_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .gallery-list.gallery-style-1 .gallery-list-content:hover .post-content-center',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_post_image_style',
			array(
				'label' => esc_html__( 'Featured Image', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'hover_image_style',
			array(
				'label'   => esc_html__( 'Image Hover Effect', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => l_theplus_get_style_list( 1 ),
			)
		);
		$this->start_controls_tabs( 'tabs_image_style' );
		$this->start_controls_tab(
			'tab_image_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overlay_image_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .gallery-list .gallery-list-content .gallery-image:before,{{WRAPPER}} .gallery-list.list-isotope-metro .gallery-list-content .gallery-bg-image-metro:before',
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'      => 'css_filters',
				'selector'  => '{{WRAPPER}} .gallery-list .gallery-list-content .gallery-image img,{{WRAPPER}} .gallery-list .gallery-list-content  .gallery-bg-image-metro',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content .gallery-bg-image-metro,
					 {{WRAPPER}} .gallery-list-content .attachment-tp-image-grid.size-tp-image-grid ,
					 {{WRAPPER}} .gallery-list-content .attachment-full.size-full' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_image_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overlay_image_hover_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .gallery-list.gallery-style-1 .grid-item .post-content-center,{{WRAPPER}} .gallery-list .gallery-list-content:hover .gallery-image:before,{{WRAPPER}} .gallery-list.list-isotope-metro .gallery-list-content:hover .gallery-bg-image-metro:before',
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'      => 'hover_css_filters',
				'selector'  => '{{WRAPPER}} .gallery-list .gallery-list-content:hover .gallery-image img,{{WRAPPER}} .gallery-list .gallery-list-content:hover  .gallery-bg-image-metro',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'hover_image_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-list .gallery-list-content:hover, {{WRAPPER}} .gallery-list .post-inner-loop .gallery-list-content:hover .gallery-bg-image-metro, {{WRAPPER}} .gallery-list-content:hover .attachment-tp-image-grid.size-tp-image-grid, {{WRAPPER}} .gallery-list-content:hover .attachment-full.size-full' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_filter_category_styling',
			array(
				'label'     => esc_html__( 'Filter Category', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'filter_category' => 'yes',
					'gallery_options' => 'repeater',
				),
			)
		);
		$this->add_control(
			'section_filter_category_styling_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_box_loop_styling',
			array(
				'label' => esc_html__( 'Box Loop Background Style', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'box_border',
			array(
				'label'     => esc_html__( 'Box Border', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);

		$this->add_control(
			'border_style',
			array(
				'label'     => esc_html__( 'Border Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => l_theplus_get_border_style(),
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content' => 'border-style: {{VALUE}};',
				),
				'condition' => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'box_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'    => 1,
					'right'  => 1,
					'bottom' => 1,
					'left'   => 1,
				),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_border_style' );
		$this->start_controls_tab(
			'tab_border_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->add_control(
			'box_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#252525',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'box_border' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_border_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->add_control(
			'box_border_hover_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#252525',
				'selectors' => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'border_hover_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'shadow_options',
			array(
				'label'     => esc_html__( 'Box Shadow Options', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->start_controls_tabs( 'tabs_shadow_style' );
		$this->start_controls_tab(
			'tab_shadow_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_shadow_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_active_shadow',
				'selector' => '{{WRAPPER}} .gallery-list .post-inner-loop .grid-item .gallery-list-content:hover',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_carousel_options_styling',
			array(
				'label'     => esc_html__( 'Carousel Options', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);
		$this->add_control(
			'section_carousel_options_styling_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_extra_options_styling',
			array(
				'label' => esc_html__( 'Extra Options', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'plus_tilt_parallax',
			array(
				'label'       => wp_kses_post(
					sprintf(
						'%s <img class="pro-badge-img" src="%s" alt="PRO" style="width:32px; vertical-align:middle;" /> <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Tilt 3D Parallax', 'tpebl' ),
						esc_url( L_THEPLUS_URL . 'assets/images/pro-features/pro-tag.svg' ),
						esc_url( $this->tp_doc . 'add-3d-tilt-effect-to-elementor-gallery-listing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Show', 'tpebl' ),
				'label_off'   => esc_html__( 'Hide', 'tpebl' ),
				'render_type' => 'template',
				'separator'   => 'before',
			)
		);
		$this->add_control(
			'plus_tilt_parallax_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'plus_tilt_parallax' => array( 'yes' ),
				),
			)
		);
		$this->add_control(
			'plus_mouse_move_parallax',
			array(
				'label'       => wp_kses_post(
					sprintf(
						'%s <img class="pro-badge-img" src="%s" alt="PRO" style="width:32px; vertical-align:middle;" /> <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Mouse Move Parallax', 'tpebl' ),
						esc_url( L_THEPLUS_URL . 'assets/images/pro-features/pro-tag.svg' ),
						esc_url( $this->tp_doc . 'add-parallax-effect-to-elementor-gallery-listing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Show', 'tpebl' ),
				'label_off'   => esc_html__( 'Hide', 'tpebl' ),
				'render_type' => 'template',
				'separator'   => 'before',
			)
		);
		$this->add_control(
			'plus_mouse_move_parallax_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'plus_mouse_move_parallax' => array( 'yes' ),
				),
			)
		);
		$this->add_control(
			'messy_column',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <img class="pro-badge-img" src="%s" alt="PRO" style="width:32px; vertical-align:middle;" />',
						esc_html__( 'Messy Columns', 'tpebl' ),
						esc_url( L_THEPLUS_URL . 'assets/images/pro-features/pro-tag.svg' )
					)
				),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'messy_column_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'messy_column' => array( 'yes' ),
				),
			)
		);
		$this->end_controls_section();

		$Plus_Listing_block                = 'Plus_Listing_block';
		$tp_enable_global_scroll_animation = true;
		include L_THEPLUS_PATH . 'modules/widgets/theplus-widget-animation.php';

		include L_THEPLUS_PATH . 'modules/widgets/theplus-profeatures.php';
	}

	/**
	 * Gallery Listout render.
	 *
	 * @since 1.0.0
	 * @version 5.5.4
	 */
	protected function render() {

		$settings        = $this->get_settings_for_display();
		$settings        = TP_Global_Scroll_Animation_Helper::resolve_widget_settings( $settings );
		$style           = ! empty( $settings['style'] ) ? $settings['style'] : 'style-1';
		$layout          = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';
		$display_title   = ! empty( $settings['display_title'] ) ? $settings['display_title'] : '';
		$post_title_tag  = ! empty( $settings['post_title_tag'] ) ? $settings['post_title_tag'] : 'h3';
		$popup_style     = ! empty( $settings['popup_style'] ) ? $settings['popup_style'] : '';
		$metro_columns   = ! empty( $settings['metro_column'] ) ? $settings['metro_column'] : '';
		$popup_attr      = '';
		$popup_attr_icon = '';

		if ( 'default' === $popup_style ) {
			$popup_attr       = 'data-elementor-open-lightbox="default" data-elementor-lightbox-slideshow="' . $this->get_id() . '"';
			$popup_attr_icon  = 'data-elementor-open-lightbox="default" data-elementor-lightbox-slideshow="icn-' . $this->get_id() . '"';
			$popup_attr_icon1 = 'data-elementor-open-lightbox="default" data-elementor-lightbox-slideshow="icn1-' . $this->get_id() . '"';
		}

		$display_icon_zoom = ! empty( $settings['display_icon_zoom'] ) ? $settings['display_icon_zoom'] : '';
		$display_excerpt   = ! empty( $settings['display_excerpt'] ) ? $settings['display_excerpt'] : '';

		$animation_effects = ! empty( $settings['animation_effects'] ) ? $settings['animation_effects'] : '';
		$animation_delay   = ! empty( $settings['animation_delay']['size'] ) ? $settings['animation_delay']['size'] : 50;
		$animation_stagger = ! empty( $settings['animation_stagger']['size'] ) ? $settings['animation_stagger']['size'] : 150;
		$animated_columns  = '';

		if ( 'no-animation' === $animation_effects ) {
			$animated_class = '';
			$animation_attr = '';
		} else {
			$animate_offset  = '85%';
			$animated_class  = 'animate-general';
			$animation_attr  = ' data-animate-type="' . esc_attr( $animation_effects ) . '" data-animate-delay="' . esc_attr( $animation_delay ) . '"';
			$animation_attr .= ' data-animate-offset="' . esc_attr( $animate_offset ) . '"';

			$ani_column       = ! empty( $settings['animated_column_list'] ) ? $settings['animated_column_list'] : '';
			$ani_duration     = ! empty( $settings['animation_duration_default'] ) ? $settings['animation_duration_default'] : '';
			$animate_duration = ! empty( $settings['animate_duration']['size'] ) ? $settings['animate_duration']['size'] : 50;
			$out_effect       = ! empty( $settings['animation_out_effects'] ) ? $settings['animation_out_effects'] : '';
			$ani_delay        = ! empty( $settings['animation_out_delay']['size'] ) ? $settings['animation_out_delay']['size'] : 50;
			$out_duration     = ! empty( $settings['animation_out_duration_default'] ) ? $settings['animation_out_duration_default'] : '';
			$out_speed        = ! empty( $settings['animation_out_duration']['size'] ) ? $settings['animation_out_duration']['size'] : 50;

			if ( 'stagger' === $ani_column ) {
				$animated_columns = 'animated-columns';
				$animation_attr  .= ' data-animate-columns="stagger"';
				$animation_attr  .= ' data-animate-stagger="' . esc_attr( $animation_stagger ) . '"';
			} elseif ( 'columns' === $ani_column ) {
				$animated_columns = 'animated-columns';
				$animation_attr  .= ' data-animate-columns="columns"';
			}
			if ( 'yes' === $ani_duration ) {
				$animation_attr .= ' data-animate-duration="' . esc_attr( $animate_duration ) . '"';
			}
			if ( 'no-animation' !== $out_effect ) {
				$animation_attr .= ' data-animate-out-type="' . esc_attr( $out_effect ) . '" data-animate-out-delay="' . esc_attr( $ani_delay ) . '"';
				if ( 'yes' === $out_duration ) {
					$animation_attr .= ' data-animate-out-duration="' . esc_attr( $out_speed ) . '"';
				}
			}
		}

		$desktop_class = '';
		$tablet_class  = '';
		$mobile_class  = '';

		if ( 'carousel' !== $layout && 'metro' !== $layout ) {
			$desktop_class = 'tp-col-lg-' . esc_attr( $settings['desktop_column'] );
			$tablet_class  = 'tp-col-md-' . esc_attr( $settings['tablet_column'] );
			$mobile_class  = 'tp-col-sm-' . esc_attr( $settings['mobile_column'] );
			$mobile_class .= ' tp-col-' . esc_attr( $settings['mobile_column'] );
		}

		$gallery_img = ! empty( $settings['gallery_images'] ) ? $settings['gallery_images'] : '';
		$gal_options = ! empty( $settings['gallery_options'] ) ? $settings['gallery_options'] : '';

		$layout_attr = '';
		$data_class  = '';

		if ( ! empty( $layout ) ) {
			$data_class .= l_theplus_get_layout_list_class( $layout );
			$layout_attr = l_theplus_get_layout_list_attr( $layout );
		} else {
			$data_class .= ' list-isotope';
		}
		if ( 'metro' === $layout ) {
			$layout_attr .= ' data-metro-columns="' . esc_attr( $metro_columns ) . '" ';
			$layout_attr .= ' data-metro-style="' . esc_attr( $settings[ 'metro_style_' . $metro_columns ] ) . '" ';
		}

		$data_class .= ' gallery-' . $style;
		$data_class .= ' hover-image-' . $settings['hover_image_style'];

		$output    = '';
		$data_attr = '';

		$ji  = 1;
		$ij  = 0;
		$uid = uniqid( 'post' );

		$data_attr .= ' data-id="' . esc_attr( $uid ) . '"';
		$data_attr .= ' data-style="' . esc_attr( $style ) . '"';

		$tablet_metro_class = '';

		$tablet_ij = '';

		if ( 'normal' === $metro_columns ) {
			$output .= '<h3 class="theplus-posts-not-found">' . esc_html__( 'Please select a multiple images gallery', 'tpebl' ) . '</h3>';
		} elseif ( 'style-1' === $style || 'style-2' === $style && 'carousel' !== $layout ) {
				$output .= '<div id="pt-plus-gallery-list" class="gallery-list ' . esc_attr( $uid ) . ' ' . esc_attr( $data_class ) . ' ' . esc_attr( $animated_class ) . ' " ' . $layout_attr . ' ' . $data_attr . ' ' . $animation_attr . ' data-enable-isotope="1">';

				$output .= '<div id="' . esc_attr( $uid ) . '" class="tp-row post-inner-loop ' . esc_attr( $uid ) . '">';

			if ( ! empty( $gallery_img ) && 'normal' === $gal_options ) {
				foreach ( $gallery_img as $image ) {
					$image_id    = $image['id'];
					$attachment  = get_post( $image_id );
					$title       = '';
					$description = '';
					$caption     = '';
					$image_alt   = '';
					$target      = '';
					$nofollow    = '';

					if ( $attachment ) {
						$image_alt   = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
						$caption     = $attachment->post_excerpt;
						$description = $attachment->post_content;
						$title       = $attachment->post_title;
					}

					if ( 'metro' === $layout ) {

						if ( ! empty( $settings[ 'metro_style_' . $metro_columns ] ) ) {
							$ij = l_theplus_metro_style_layout( $ji, $settings['metro_column'], $settings[ 'metro_style_' . $metro_columns ] );
						}
					}

					$output .= '<div class="grid-item metro-item' . esc_attr( $ij ) . ' ' . $desktop_class . ' ' . $tablet_class . ' ' . $mobile_class . ' ' . esc_attr( $animated_columns ) . '" >';
					if ( ! empty( $style ) ) {
						ob_start();
							include L_THEPLUS_WSTYLES . 'gallery/gallery-' . sanitize_file_name( $style ) . '.php';
							$output .= ob_get_contents();
						ob_end_clean();
					}
					$output .= '</div>';

					++$ji;
				}
			}
				$output .= '</div>';
				$output .= '</div>';
		} elseif ( 'style-1' !== $style || 'carousel' === $layout ) {
			$output .= '<h3 class="theplus-posts-not-found">' . esc_html__( 'This Style Premium Version', 'tpebl' ) . '</h3>';
		}
		echo $output;
		wp_reset_postdata();
	}
}
