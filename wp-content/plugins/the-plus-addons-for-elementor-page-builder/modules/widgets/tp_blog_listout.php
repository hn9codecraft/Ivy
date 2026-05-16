<?php
/**
 * Widget Name: Blog Post Listing
 * Description: Different style of Blog Post listing layouts.
 * Author: Theplus
 * Author URI: https://posimyth.com
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use ThePlusAddons\Elementor\ButtonStyle\TP_Global_Button_Style_Helper;
use ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper;

if ( ! trait_exists( '\ThePlusAddons\Elementor\ButtonStyle\TP_Global_Button_Style_Helper' ) ) {
	include_once L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-button-style-helper.php';
}

if ( ! class_exists( '\ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Helper' ) ) {
	include_once L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-scroll-animation-helper.php';
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class L_ThePlus_Blog_ListOut
 */
class L_ThePlus_Blog_ListOut extends Widget_Base {
	use TP_Global_Button_Style_Helper;

	/**
	 * Document Link For Need help.
	 *
	 * @since 5.3.3
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
	 */
	public function get_name() {
		return 'tp-blog-listout';
	}

	/**
	 * Get Widget Title.
	 *
	 * @since 1.0.0
	 */
	public function get_title() {
		return esc_html__( 'Blog/Post Listing', 'tpebl' );
	}

	/**
	 * Get Widget Icon.
	 *
	 * @since 1.0.0
	 */
	public function get_icon() {
		return 'theplus-i-blog-listing tpae-editor-logo';
	}

	/**
	 * Get Widget categories.
	 *
	 * @since 1.0.0
	 */
	public function get_categories() {
		return array( 'plus-listing', 'plus-archive' );
	}

	/**
	 * Get Widget keywords.
	 *
	 * @since 1.0.0
	 */
	public function get_keywords() {
		return array( 'Tp Blog Listing', 'Post Listing', 'Post Grid', 'Masonry Blog', 'Metro Blog Layout', 'Post Carousel', 'Post Filter', 'Load More Posts', 'Post Pagination', 'Lazy Load Posts', 'Smart Loop Post', 'Order Posts' );
	}

	/**
	 * Get Docs Url.
	 *
	 * @since 1.0.0
	 */
	public function get_custom_help_url() {
		$help_url = $this->tp_help;

		return esc_url( $help_url );
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
	 * @version 5.5.4
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Layout', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tpae_preset_controller',
			array(
				'type'        => 'tpae_preset_button',
				'temp_id'     => 17849,
				'label_block' => true,
			)
		);
		$this->add_control(
			'style',
			array(
				'label'       => esc_html__( 'Style', 'tpebl' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::VISUAL_CHOICE,
				'default'     => 'style-1',
				'options'     => array(
					'style-1'            => array(
						'title' => esc_html__( 'Style 1', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/blog-listing/style-1.svg',
					),
					'style-2'            => array(
						'title' => esc_html__( 'Style 2 (PRO)', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/blog-listing/style-2-pro.svg',
					),
					'style-3'            => array(
						'title' => esc_html__( 'Style 3 (PRO)', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/blog-listing/style-3-pro.svg',
					),
					'style-4'            => array(
						'title' => esc_html__( 'Style 4 (PRO)', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/blog-listing/style-4-pro.svg',
					),
					'style-5'            => array(
						'title' => esc_html__( 'Style 5', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/blog-listing/style-5.svg',
					),
					'smart-loop-builder' => array(
						'title' => esc_html__( 'Smart Loop Builder', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/blog-listing/smart-loop-builder.svg',
					),
				),
				'columns'     => 3,
				'classes'     => 'tpae-visual_choice',
			)
		);
		$this->add_control(
			'smart_loop_builder_note',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
						esc_html__( 'This is first version of Smart Loop Builder, We will have more dynamic options, ACF support and release in more listing widgets coming up next..', 'tpebl' ),
						esc_url( $this->tp_doc . 'create-custom-elementor-post-loop-skin-with-smart-loop-builder/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn More', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'style' => array( 'smart-loop-builder' ),
				),
			)
		);
		$this->add_control(
			'style_pro_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'style!' => array( 'style-1', 'style-5', 'smart-loop-builder' ),
				),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'       => esc_html__( 'Layout', 'tpebl' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::VISUAL_CHOICE,
				'default'     => 'grid',
				'options'     => array(
					'grid'     => array(
						'title' => esc_html__( 'Grid', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/grid.svg',
					),
					'masonry'  => array(
						'title' => esc_html__( 'Masonry', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/masonry.svg',
					),
					'metro'    => array(
						'title' => esc_html__( 'Metro', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/metro.svg',
					),
					'carousel' => array(
						'title' => esc_html__( 'Carousel (PRO)', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/carousel-pro.svg',
					),
				),
				'columns'     => 4,
				'classes'     => 'tpae-visual_choice',
			)
		);
		$this->add_control(
			'how_it_works_grid',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'show-blog-posts-in-grid-layout-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'layout' => 'grid',
				),
			)
		);
		$this->add_control(
			'how_it_works_Masonry',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'show-blog-posts-in-masonry-grid-layout-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'layout' => 'masonry',
				),
			)
		);
		$this->add_control(
			'how_it_works_Metro',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'<a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s <i class="eicon-help-o"></i></a>',
						esc_url( $this->tp_doc . 'show-blog-posts-in-metro-layout-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn How it works', 'tpebl' )
					)
				),
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'layout' => 'metro',
				),
			)
		);
		$this->add_control(
			'layout_pro_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'layout' => 'carousel',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'smart_loop_builder_section',
			array(
				'label'     => esc_html__( 'Smart Loop Builder (Beta)', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'style' => array( 'smart-loop-builder' ),
				),
			)
		);

		$this->add_control(
			'smart-loop-builder-button',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => '<a class="tp-loopbuilder-editor-raw" id="tp-loopbuilder-editor-raw" data-tp_name="Blog Listing" data-tp_filter_visible="true" data-wd_type="tp-blog-listout">' . esc_html__( 'Ready Presets', 'tpebl' ) . '</a>',
				// 'content_classes' => 'tp-preset-editor-btn',
				'label_block' => true,
			)
		);
		$this->add_control(
			'content_html',
			array(
				'label'     => esc_html__( 'HTML', 'tpebl' ),
				'type'      => Controls_Manager::CODE,
				'language'  => 'html',
				'separator' => 'before',
				'rows'      => 10,
			)
		);
		$this->add_control(
			'content_css',
			array(
				'label'    => esc_html__( 'CSS', 'tpebl' ),
				'type'     => Controls_Manager::CODE,
				'language' => 'css',
				'rows'     => 10,
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'content_source_section',
			array(
				'label' => esc_html__( 'Content Source', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'display_posts',
			array(
				'label'   => esc_html__( 'Maximum Posts Display', 'tpebl' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 200,
				'step'    => 1,
				'default' => 8,
			)
		);
		$this->add_control(
			'post_offset',
			array(
				'label'   => esc_html__( 'Offset Posts', 'tpebl' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'max'     => 50,
				'step'    => 1,
				'default' => '',
			)
		);
		$this->add_control(
			'post_offset_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
						esc_html__( 'Add a number here to hide posts from the beginning of your recent posts listing', 'tpebl' ),
						esc_url( $this->tp_doc . 'hide-recent-blog-post-from-list-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn More', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'post_category',
			array(
				'type'        => Controls_Manager::SELECT2,
				'label'       => esc_html__( 'Select Category', 'tpebl' ),
				'default'     => '',
				'label_block' => true,
				'multiple'    => true,
				'options'     => $this->l_theplus_get_categories(),
			)
		);
		$this->add_control(
			'post_tags',
			array(
				'type'        => Controls_Manager::SELECT2,
				'label'       => esc_html__( 'Select Tags', 'tpebl' ),
				'default'     => '',
				'label_block' => true,
				'multiple'    => true,
				'options'     => l_theplus_get_tags(),
			)
		);
		$this->add_control(
			'post_order_by',
			array(
				'label'   => wp_kses_post(
					sprintf(
						'%s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Order By', 'tpebl' ),
						esc_url( $this->tp_doc . 'show-recent-blog-posts-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => l_theplus_orderby_arr(),
			)
		);
		$this->add_control(
			'post_order',
			array(
				'label'   => esc_html__( 'Order', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => l_theplus_order_arr(),
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
		$this->add_control(
			'plus_pro_metro_column_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'metro_column!' => '3',
					'layout'        => array( 'metro' ),
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
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'morepost_option_section',
			array(
				'label'     => esc_html__( 'More Post Options', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'layout!' => array( 'carousel' ),
				),
			)
		);
		$this->add_control(
			'post_extra_option',
			array(
				'label'       => esc_html__( 'Post Loading Options', 'tpebl' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::VISUAL_CHOICE,
				'default'     => 'none',
				'options'     => array(
					'none'       => array(
						'title' => esc_html__( 'None', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/none.svg',
					),
					'pagination' => array(
						'title' => esc_html__( 'Pagination (Pro)', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/pagination-pro.svg',
					),
					'load_more'  => array(
						'title' => esc_html__( 'Load More', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/load-more.svg',
					),
					'lazy_load'  => array(
						'title' => esc_html__( 'Lazy Load (Pro)', 'tpebl' ),
						'image' => L_THEPLUS_URL . 'assets/images/widget-style/listing-layout/lazy-load-pro.svg',
					),
				),
				'columns'     => 4,
				'classes'     => 'tpae-visual_choice',
			)
		);
		$this->add_control(
			'post_extra_option_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s</i></p>',
						esc_html__( 'You can choose how additional posts load on your page, either through Pagination, Load More, or Lazy Load.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'pagination_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
						esc_html__( 'Display your posts in multiple pages with numbered navigation below the listing.', 'tpebl' ),
						esc_url( $this->tp_doc . 'add-pagination-in-blog-posts-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn More', 'tpebl' ),
					)
				),
				'condition' => array(
					'post_extra_option' => 'pagination',
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'load_more_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
						esc_html__( 'Add a Load More button that lets visitors reveal more posts without refreshing the page.', 'tpebl' ),
						esc_url( $this->tp_doc . 'add-read-more-button-in-blog-posts-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn More', 'tpebl' ),
					)
				),
				'condition' => array(
					'post_extra_option' => 'load_more',
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'lazy_load_label',
			array(
				'type'  => Controls_Manager::RAW_HTML,
				'raw'   => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
						esc_html__( 'Automatically load more posts as users scroll down, creating a smooth infinite scrolling effect.', 'tpebl' ),
						esc_url( $this->tp_doc . 'add-infinite-scroll-for-blog-posts-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
						esc_html__( 'Learn More', 'tpebl' ),
					)
				),
				'condition' => array(
					'post_extra_option' => 'lazy_load',
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'post_extra_pro_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'post_extra_option!' => array( 'none', 'load_more' ),
				),
			)
		);
		$this->add_control(
			'load_more_btn_text',
			array(
				'label'     => esc_html__( 'Button Text', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'ai'        => false,
				'default'   => esc_html__( 'Load More', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
				),
			)
		);
		$this->add_control(
			'load_more_btn_type_switch',
			array(
				'label'     => esc_html__( 'Button Type', 'tpebl' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'basic',
				'options'   => array(
					'basic'  => array(
						'title' => esc_html__( 'Basic', 'tpebl' ),
						'icon'  => 'eicon-button',
					),
					'global' => array(
						'title' => esc_html__( 'Global', 'tpebl' ),
						'icon'  => 'eicon-globe',
					),
				),
				'toggle'    => false,
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
				),
			)
		);
		$this->add_control(
			'load_more_btn_global_style_preset',
			array(
				'label'     => esc_html__( 'Global Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_global_button_style_options(),
				'default'   => '',
				'condition' => array(
					'layout!'                   => array( 'carousel' ),
					'post_extra_option'         => 'load_more',
					'load_more_btn_type_switch' => 'global',
				),
			)
		);
		$this->add_control(
			'tp_loading_text',
			array(
				'label'     => esc_html__( 'Loading Text', 'tpebl' ),
				'type'      => Controls_Manager::TEXT,
				'ai'        => false,
				'default'   => esc_html__( 'Loading...', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => array( 'load_more' ),
				),
			)
		);
		$this->add_control(
			'loaded_posts_text',
			array(
				'label'     => esc_html__( 'All Posts Loaded Text', 'tpebl' ),
				'type'      => Controls_Manager::TEXT,
				'ai'        => false,
				'default'   => esc_html__( 'All done!', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => array( 'load_more' ),
				),
			)
		);
		$this->add_control(
			'load_more_post',
			array(
				'label'     => esc_html__( 'More posts on click/scroll', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 30,
				'step'      => 1,
				'default'   => 4,
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => array( 'load_more' ),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'load_more_typography',
				'label'     => esc_html__( 'Load More Typography', 'tpebl' ),
				'global'    => array(
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector'  => '{{WRAPPER}} .ajax_load_more .post-load-more',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'loaded_posts_typo',
				'label'     => esc_html__( 'Loaded All Posts Typography', 'tpebl' ),
				'global'    => array(
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector'  => '{{WRAPPER}} .plus-all-posts-loaded',
				'separator' => 'before',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => array( 'load_more' ),
				),
			)
		);
		$this->add_control(
			'load_more_border',
			array(
				'label'     => esc_html__( 'Load More Border', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_control(
			'load_more_border_style',
			array(
				'label'     => esc_html__( 'Border Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => L_theplus_get_border_style(),
				'selectors' => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more' => 'border-style: {{VALUE}};',
				),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_responsive_control(
			'load_more_border_width',
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
					'{{WRAPPER}} .ajax_load_more .post-load-more' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->start_controls_tabs(
			'tabs_load_more_border_style',
			array(
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->start_controls_tab(
			'tab_load_more_border_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_control(
			'load_more_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#252525',
				'selectors' => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_responsive_control(
			'load_more_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'after',
				'condition'  => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_load_more_border_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_control(
			'load_more_border_hover_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#252525',
				'selectors' => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_responsive_control(
			'load_more_border_hover_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_border'  => 'yes',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->start_controls_tabs(
			'tabs_load_more_style',
			array(
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->start_controls_tab(
			'tab_load_more_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_control(
			'load_more_color',
			array(
				'label'     => esc_html__( 'Text Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_control(
			'loaded_posts_color',
			array(
				'label'     => esc_html__( 'Loaded Posts Text Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .plus-all-posts-loaded' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => array( 'load_more', 'lazy_load' ),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'load_more_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .ajax_load_more .post-load-more',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'load_more_shadow',
				'selector'  => '{{WRAPPER}} .ajax_load_more .post-load-more',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_load_more_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_control(
			'load_more_color_hover',
			array(
				'label'     => esc_html__( 'Text Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .ajax_load_more .post-load-more:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'load_more_hover_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .ajax_load_more .post-load-more:hover',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
					'load_more_btn_type_switch' => 'basic',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'load_more_hover_shadow',
				'selector'  => '{{WRAPPER}} .ajax_load_more .post-load-more:hover',
				'condition' => array(
					'layout!'           => array( 'carousel' ),
					'post_extra_option' => 'load_more',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
		$this->start_controls_section(
			'extra_option_section',
			array(
				'label' => esc_html__( 'Extra Option', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'post_title_tag',
			array(
				'label'   => esc_html__( 'Title Tag', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => l_theplus_get_tags_options(),
			)
		);
		$this->add_control(
			'post_title_tag_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text">%s</p>',
						esc_html__( 'Select the heading tag for your post title. Choose based on your page hierarchy to keep your website SEO-friendly and structured.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'display_title_limit',
			array(
				'label'     => esc_html__( 'Title Limit', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);
		$this->add_control(
			'display_title_limit_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text">%s</p>',
						esc_html__( 'Enable this to limit long post titles and keep your layout clean. Perfect when you want all cards or listings to look uniform.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'display_title_by',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Limit on', 'tpebl' ),
				'default'   => 'char',
				'options'   => array(
					'char' => esc_html__( 'Character', 'tpebl' ),
					'word' => esc_html__( 'Word', 'tpebl' ),
				),
				'condition' => array(
					'display_title_limit' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_title_input',
			array(
				'label'     => esc_html__( 'Title Count', 'tpebl' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 1000,
				'step'      => 1,
				'condition' => array(
					'display_title_limit' => 'yes',
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
				'default'   => 'no',
				'condition' => array(
					'display_title_limit' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_post_category',
			array(
				'label'     => esc_html__( 'Display Category Post', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
				'condition' => array(
					'style!' => array( 'style-1' ),
				),
			)
		);
		$this->add_control(
			'post_category_style',
			array(
				'label'     => esc_html__( 'Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'style-1',
				'options'   => l_theplus_get_style_list( 2 ),
				'condition' => array(
					'style!'                => array( 'style-1' ),
					'display_post_category' => 'yes',
				),
			)
		);
		$this->add_control(
			'display_post_category_all',
			array(
				'label'     => esc_html__( 'Display All Category', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'after',
				'condition' => array(
					'style!'                => array( 'style-1' ),
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
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
			)
		);
		$this->add_control(
			'post_excerpt_count',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Excerpt/Content Count', 'tpebl' ),
						esc_url( $this->tp_doc . 'limit-post-excerpt-in-elementor-blog-posts/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 5,
				'max'       => 500,
				'step'      => 2,
				'default'   => 30,
				'separator' => 'after',
				'condition' => array(
					'display_excerpt' => 'yes',
				),
			)
		);
		$this->add_control(
			'show_post_date',
			array(
				'label'     => esc_html__( 'Date & Time', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);
		$this->add_control(
			'show_post_date_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'Turn this on to display the post’s published date or last updated time below the title.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'show_post_author',
			array(
				'label'     => esc_html__( 'Author Name', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
			)
		);
		$this->add_control(
			'show_post_author_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'Enable this to show the author name under each post.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'show_read_time',
			array(
				'label'     => esc_html__( 'Read Time', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);
		$this->add_control(
			'show_read_time_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'Display an estimated reading time for each post to help visitors gauge their reading commitment.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'display_button',
			array(
				'label'     => esc_html__( 'Button', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'separator' => 'before',
				'condition' => array(
					'style' => array( 'style-2', 'style-3', 'style-5' ),
				),
			)
		);
		$this->add_control(
			'button_style',
			array(
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__( 'Button Style', 'tpebl' ),
				'default'   => 'style-7',
				'options'   => array(
					'style-7' => esc_html__( 'Style 1', 'tpebl' ),
					'style-8' => esc_html__( 'Style 2', 'tpebl' ),
					'style-9' => esc_html__( 'Style 3', 'tpebl' ),
				),
				'condition' => array(
					'style'          => array( 'style-2', 'style-3', 'style-5' ),
					'display_button' => 'yes',
				),
			)
		);
		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Text', 'tpebl' ),
				'type'        => Controls_Manager::TEXT,
				'ai'          => false,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => esc_html__( 'Read More', 'tpebl' ),
				'placeholder' => esc_html__( 'Read More', 'tpebl' ),
				'condition'   => array(
					'style'          => array( 'style-2', 'style-3', 'style-5' ),
					'display_button' => 'yes',
				),
			)
		);
		$this->add_control(
			'button_icon_style',
			array(
				'label'     => esc_html__( 'Icon Font', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'font_awesome',
				'options'   => array(
					''             => esc_html__( 'None', 'tpebl' ),
					'font_awesome' => esc_html__( 'Font Awesome', 'tpebl' ),
				),
				'condition' => array(
					'style'          => array( 'style-2', 'style-3', 'style-5' ),
					'button_style!'  => array( 'style-7', 'style-9' ),
					'display_button' => 'yes',
				),
			)
		);
		$this->add_control(
			'button_icon',
			array(
				'label'     => esc_html__( 'Icon', 'tpebl' ),
				'type'      => Controls_Manager::ICON,
				'default'   => 'fa fa-chevron-right',
				'condition' => array(
					'style'             => array( 'style-2', 'style-3', 'style-5' ),
					'display_button'    => 'yes',
					'button_style!'     => array( 'style-7', 'style-9' ),
					'button_icon_style' => 'font_awesome',
				),
			)
		);
		$this->add_control(
			'before_after',
			array(
				'label'     => esc_html__( 'Icon Position', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => array(
					'after'  => esc_html__( 'After', 'tpebl' ),
					'before' => esc_html__( 'Before', 'tpebl' ),
				),
				'condition' => array(
					'style'              => array( 'style-2', 'style-3', 'style-5' ),
					'display_button'     => 'yes',
					'button_style!'      => array( 'style-7', 'style-9' ),
					'button_icon_style!' => '',
				),
			)
		);
		$this->add_control(
			'icon_spacing',
			array(
				'label'     => esc_html__( 'Icon Spacing', 'tpebl' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 100,
					),
				),
				'condition' => array(
					'style'              => array( 'style-2', 'style-3', 'style-5' ),
					'display_button'     => 'yes',
					'button_style!'      => array( 'style-7', 'style-9' ),
					'button_icon_style!' => '',
				),
				'selectors' => array(
					'{{WRAPPER}} .button-link-wrap i.button-after' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .button-link-wrap i.button-before' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .button-link-wrap .button-after svg' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .button-link-wrap .button-before svg' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'display_thumbnail',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <img class="pro-badge-img" src="%s" alt="PRO" style="width:32px; vertical-align:middle;" />',
						esc_html__( 'Display Image Size', 'tpebl' ),
						esc_url( L_THEPLUS_URL . 'assets/images/pro-features/pro-tag.svg' )
					)
				),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'layout!' => 'carousel',
				),
			)
		);
		$this->add_control(
			'display_thumbnail_options',
			array(
				'type'        => 'tpae_pro_feature',
				'label_block' => true,
				'default'     => '',
				'condition'   => array(
					'layout!'           => 'carousel',
					'display_thumbnail' => array( 'yes' ),
				),
			)
		);
		$this->add_control(
			'display_post_meta',
			array(
				'label'     => esc_html__( 'Display Post Meta', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'yes',
			)
		);
		$this->add_control(
			'display_post_meta_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'Enable this to show extra post details like date, author, and other meta information for better context.', 'tpebl' ),
					)
				),
				'label_block' => true,
			)
		);
		$this->add_control(
			'post_meta_tag_style',
			array(
				'label'     => esc_html__( 'Post Meta Tag', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'style-1',
				'options'   => l_theplus_get_style_list( 1 ),
				'condition' => array(
					'display_post_meta' => 'yes',
				),
			)
		);
		$this->add_control(
			'author_prefix',
			array(
				'label'       => esc_html__( 'Author Prefix', 'tpebl' ),
				'type'        => Controls_Manager::TEXT,
				'ai'          => false,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => esc_html__( 'By', 'tpebl' ),
				'placeholder' => esc_html__( 'Enter Prefix Text', 'tpebl' ),
				'condition'   => array(
					'display_post_meta'    => 'yes',
					'post_meta_tag_style!' => 'style-3',
					'style'                => 'style-5',
				),
			)
		);
		$this->add_control(
			'filter_category',
			array(
				'label'     => wp_kses_post(
					sprintf(
						'%s <img class="pro-badge-img" src="%s" alt="PRO" style="width:32px; vertical-align:middle;" /> <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer"><i class="eicon-help-o"></i></a>',
						esc_html__( 'Category Wise Filter', 'tpebl' ),
						esc_url( L_THEPLUS_URL . 'assets/images/pro-features/pro-tag.svg' ),
						esc_url( $this->tp_doc . 'add-category-wise-filter-in-blog-post-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' )
					)
				),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'layout!' => 'carousel',
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
					'layout!'         => 'carousel',
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
						'url'   => 'https://theplusaddons.com/help/blog-listing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget',
					),
					array(
						'label' => esc_html__( 'Watch Video', 'tpebl' ),
						'url'   => 'https://www.youtube.com/watch?v=o2a8zVx2ztc',
					),
				),
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
					'notice'      => esc_html__( 'We recommend using this widget in the Archive Template to load it for all categories & tags.', 'tpebl' ),
					'button_text' => esc_html__( 'Create Archive Page', 'tpebl' ),
					'page_type'   => 'tp_archives',
				)
			);
			$this->end_controls_section();
		}

		$this->start_controls_section(
			'section_meta_tag_style',
			array(
				'label'     => esc_html__( 'Post Meta Tag', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'display_post_meta' => 'yes',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'meta_tag_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '
				 {{WRAPPER}} .blog-list .post-inner-loop .post-meta-info span,
				 {{WRAPPER}} .blog-list .post-inner-loop .tpae-blog-meta span,
				 {{WRAPPER}} .blog-list .post-inner-loop .tpae-blog-meta-overflow span,
				 {{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-meta-tag',
			)
		);
		$this->start_controls_tabs( 'tabs_post_meta_style' );
		$this->start_controls_tab(
			'tab_post_meta_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_control(
			'post_meta_color',
			array(
				'label'     => esc_html__( 'Post Meta Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-meta-info span,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-blog-meta span,
					 {{WRAPPER}} .blog-list .post-inner-loop .tpae-blog-meta-overflow span,
					{{WRAPPER}} .blog-list .post-inner-loop .post-meta-info span a,
					 {{WRAPPER}} .blog-list .post-inner-loop .tpae-blog-meta-overflow span a,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-blog-meta span a,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-meta-tag' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_post_meta_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_control(
			'post_meta_color_hover',
			array(
				'label'     => esc_html__( 'Post Meta Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .post-meta-info span,
					{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .post-meta-info span a,
					{{WRAPPER}} ..blog-list .post-inner-loop .tpae-blog-content:hover .tpae-blog-meta-overflow span a,
					{{WRAPPER}} ..blog-list .post-inner-loop .tpae-blog-content:hover .tpae-blog-meta span a,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-meta-tag:hover' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_portable_style',
			array(
				'label'     => esc_html__( 'Customize Layout', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'style' => 'style-5',
				),
			)
		);
		$this->add_control(
			'blog_card_layout',
			array(
				'label'        => __( 'Layout', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->start_popover();
		$this->add_responsive_control(
			'wrap_flex_direction',
			array(
				'label'       => esc_html__( 'Layout Direction', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'column'         => array(
						'title' => esc_html__( 'Column', 'tpebl' ),
						'icon'  => 'eicon-arrow-down',
					),
					'row'            => array(
						'title' => esc_html__( 'Row', 'tpebl' ),
						'icon'  => 'eicon-arrow-right',
					),
					'column-reverse' => array(
						'title' => esc_html__( 'Column Reverse', 'tpebl' ),
						'icon'  => 'eicon-v-align-bottom',
					),
					'row-reverse'    => array(
						'title' => esc_html__( 'Row Reverse', 'tpebl' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'     => 'column',
				'toggle'      => false,
				'label_block' => false,
				'selectors'   => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap' => 'flex-direction: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'tp_row_one_width',
			array(
				'label'          => esc_html__( 'Image Box', 'tpebl' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( '%' ),
				'default'        => array(
					'size' => '50',
					'unit' => '%',
				),
				'tablet_default' => array(
					'size' => 50,
					'unit' => '%',
				),
				'mobile_default' => array(
					'size' => 50,
					'unit' => '%',
				),
				'condition'      => array( 'wrap_flex_direction' => array( 'row', 'row-reverse' ) ),
				'selectors'      => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap .tpae-blog-image' => 'width: {{SIZE}}%;',
				),
			)
		);
		$this->add_responsive_control(
			'tp_row_two_width',
			array(
				'label'          => esc_html__( 'Content Box', 'tpebl' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( '%' ),
				'default'        => array(
					'size' => '50',
					'unit' => '%',
				),
				'tablet_default' => array(
					'size' => 50,
					'unit' => '%',
				),
				'mobile_default' => array(
					'size' => 50,
					'unit' => '%',
				),
				'condition'      => array( 'wrap_flex_direction' => array( 'row', 'row-reverse' ) ),
				'selectors'      => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap .tpae-blog-content' => 'width: {{SIZE}}%;',
				),
			)
		);
		$this->add_responsive_control(
			'tp_content_alignment',
			array(
				'label'       => esc_html__( 'Text Alignment', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'tpebl' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'tpebl' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'tpebl' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'     => 'left',
				'toggle'      => false,
				'label_block' => false,
				'selectors'   => array(
					'{{WRAPPER}} .tpae-blog-content' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'tp_item_content_alignment',
			array(
				'label'       => esc_html__( 'Layout Alignment', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'tpebl' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'tpebl' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Right', 'tpebl' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'     => 'left',
				'toggle'      => false,
				'label_block' => false,
				'selectors'   => array(
					'{{WRAPPER}} .tpae-blog-content' => 'align-items: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'wrap_gap',
			array(
				'label'      => esc_html__( 'Item Gap', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 100,
					),
					'em'  => array(
						'min' => 0,
						'max' => 10,
					),
					'rem' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => 20,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'tp_absolute_layout',
			array(
				'label'     => esc_html__( 'Layout Position', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
			)
		);
		$this->add_responsive_control(
			'tp_top_card',
			array(
				'label'      => esc_html__( 'Left', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'em' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => '',
					'unit' => 'px',
				),
				'condition'  => array( 'tp_absolute_layout' => array( 'yes' ) ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap .tpae-blog-content.tpae-protebal-absolute' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'tp_bottom_card',
			array(
				'label'      => esc_html__( 'bottom', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'em' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => '',
					'unit' => 'px',
				),
				'condition'  => array( 'tp_absolute_layout' => array( 'yes' ) ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap .tpae-blog-content' => 'bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'tp_width_card',
			array(
				'label'      => esc_html__( 'Width', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'em' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'default'    => array(
					'size' => '100',
					'unit' => '%',
				),
				'condition'  => array( 'tp_absolute_layout' => array( 'yes' ) ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-compect-blog-wrap .tpae-blog-content' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'content_background_options',
			array(
				'label'     => esc_html__( 'Content', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'content_direction',
			array(
				'label'       => esc_html__( 'Content Direction', 'tpebl' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'row'            => array(
						'title' => esc_html__( 'Row', 'tpebl' ),
						'icon'  => 'eicon-h-align-left',
					),
					'row-reverse'    => array(
						'title' => esc_html__( 'Row Reverse', 'tpebl' ),
						'icon'  => 'eicon-h-align-right',
					),
					'column'         => array(
						'title' => esc_html__( 'Column', 'tpebl' ),
						'icon'  => 'eicon-v-align-top',
					),
					'column-reverse' => array(
						'title' => esc_html__( 'Column Reverse', 'tpebl' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'     => 'column',
				'toggle'      => false,
				'label_block' => false,
				'selectors'   => array(
					'{{WRAPPER}} .tpae-blog-content' => 'display: flex; flex-direction: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'content_vertical_justify',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'tpebl' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start'    => array(
						'title' => esc_html__( 'Start', 'tpebl' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'        => array(
						'title' => esc_html__( 'Center', 'tpebl' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'      => array(
						'title' => esc_html__( 'End', 'tpebl' ),
						'icon'  => 'eicon-v-align-bottom',
					),
					'space-between' => array(
						'title' => esc_html__( 'Space Between', 'tpebl' ),
						'icon'  => 'eicon-justify-space-between-v',
					),
					'space-around'  => array(
						'title' => esc_html__( 'Space Around', 'tpebl' ),
						'icon'  => 'eicon-justify-space-around-v',
					),
				),
				'default'   => 'flex-start',
				'selectors' => array(
					'{{WRAPPER}} .tpae-blog-content' => 'justify-content: {{VALUE}};',
				),
				'condition' => array(
					'content_direction' => array( 'column', 'column-reverse' ),
				),
			)
		);
		$this->add_responsive_control(
			'blog_content_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => '',
					'bottom' => '',
					'left'   => '',
					'right'  => '',
					'unit'   => 'px',
				),
				'condition'  => array(
					'style' => array( 'style-5' ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'blog_content_margin',
			array(
				'label'      => esc_html__( 'Margin', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => '',
					'bottom' => '',
					'left'   => '',
					'right'  => '',
					'unit'   => 'px',
				),
				'condition'  => array(
					'style' => array( 'style-5' ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'blog_content_background',
				'label'    => esc_html__( 'Background', 'tpebl' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tpae-blog-content',
			)
		);
		$this->add_control(
			'blog_border_post',
			array(
				'label'        => __( 'Border', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'style' => array( 'style-5' ),
				),
			)
		);
		$this->start_popover();
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'blog_content_border',
				'label'    => esc_html__( 'Border', 'tpebl' ),
				'selector' => '{{WRAPPER}} .tpae-blog-content',
			)
		);
		$this->add_control(
			'blog_content_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'meta_style_options',
			array(
				'label'     => esc_html__( 'Meta', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control(
			'blog_meta_layout_post',
			array(
				'label'        => __( 'Meta Layout', 'tpebl' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'style' => array( 'style-5' ),
				),
			)
		);
		$this->start_popover();
		$this->add_control(
			'meta_flex_direction',
			array(
				'label'     => esc_html__( 'Meta Direction', 'tpebl' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'row'            => array(
						'title' => esc_html__( 'Row', 'tpebl' ),
						'icon'  => 'eicon-h-align-left',
					),
					'row-reverse'    => array(
						'title' => esc_html__( 'Row Reverse', 'tpebl' ),
						'icon'  => 'eicon-h-align-right',
					),
					'column'         => array(
						'title' => esc_html__( 'Column', 'tpebl' ),
						'icon'  => 'eicon-v-align-top',
					),
					'column-reverse' => array(
						'title' => esc_html__( 'Column Reverse', 'tpebl' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'row',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .tpae-blog-meta,{{WRAPPER}} .tpae-blog-meta-overflow' => 'display: flex; flex-direction: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'meta_align_items',
			array(
				'label'     => esc_html__( 'Meta Align', 'tpebl' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Top', 'tpebl' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => esc_html__( 'Middle', 'tpebl' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Bottom', 'tpebl' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'center',
				'condition' => array(
					'content_direction' => array( 'row', 'row-reverse' ),
				),
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .tpae-blog-meta' => 'align-items: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'meta_gap',
			array(
				'label'      => esc_html__( 'Item Gap', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta,{{WRAPPER}} .tpae-blog-meta-overflow' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_responsive_control(
			'tp_meta_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow,{{WRAPPER}} .tpae-blog-meta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'tp_meta_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta',
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'tp_meta_border',
				'label'    => esc_html__( 'Border', 'tpebl' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta',
			)
		);
		$this->add_responsive_control(
			'tp_meta_border_r',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'date_overflow',
			array(
				'label'     => esc_html__( 'Date Position', 'tpebl' ),
				'type'      => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => esc_html__( 'Default', 'tpebl' ),
				'label_on'  => esc_html__( 'Custom', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'style'          => array( 'style-5' ),
					'show_post_date' => 'yes',
				),

			)
		);
		$this->start_popover();
		$this->add_responsive_control(
			'category_date_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .meta-date' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);
		$this->add_responsive_control(
			'date_style_margin_tb',
			array(
				'label'      => esc_html__( 'Top/Bottom Spacing', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .meta-date' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'date_style_margin_lr',
			array(
				'label'      => esc_html__( 'Left/Right Spacing', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .meta-date' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'wl_btn_border',
				'label'    => esc_html__( 'Border', 'tpebl' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta-overflow .meta-date',
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overflow_date_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta-overflow .meta-date',
			)
		);
		$this->add_responsive_control(
			'overflow_date_br',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .meta-date' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'meta_overflow',
			array(
				'label'     => esc_html__( 'Author Position', 'tpebl' ),
				'type'      => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => esc_html__( 'Default', 'tpebl' ),
				'label_on'  => esc_html__( 'Custom', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'style'            => array( 'style-5' ),
					'show_post_author' => 'yes',
				),
			)
		);
		$this->start_popover();
		$this->add_responsive_control(
			'category_meta_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-author' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);
		$this->add_responsive_control(
			'meta_style_margin_tb',
			array(
				'label'      => esc_html__( 'Top/Bottom Spacing', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-author' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'meta_style_margin_lr',
			array(
				'label'      => esc_html__( 'Left/Right Spacing', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-author' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'overflow_meta_border',
				'label'    => esc_html__( 'Border', 'tpebl' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-author',
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overflow_date__meta_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-author',
			)
		);
		$this->add_responsive_control(
			'overflow_meta_br',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-author' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'post_tima_overflow',
			array(
				'label'     => esc_html__( 'Post Time Position', 'tpebl' ),
				'type'      => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => esc_html__( 'Default', 'tpebl' ),
				'label_on'  => esc_html__( 'Custom', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'style'          => array( 'style-5' ),
					'show_read_time' => 'yes',
				),

			)
		);
		$this->start_popover();
		$this->add_responsive_control(
			'category_post_time_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-read-time' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);
		$this->add_responsive_control(
			'post_style_margin_tb',
			array(
				'label'      => esc_html__( 'Top/Bottom Spacing', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-read-time' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'post_style_margin_lr',
			array(
				'label'      => esc_html__( 'Left/Right Spacing', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-read-time' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'overflow_post_time_border',
				'label'    => esc_html__( 'Border', 'tpebl' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-read-time',
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'overflow_post_time_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-read-time',
			)
		);
		$this->add_responsive_control(
			'overflow_post_time_br',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .tpae-blog-meta-overflow .tpae-blog-read-time' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_control(
			'button_overflow',
			array(
				'label'     => esc_html__( 'Button Absolute', 'tpebl' ),
				'type'      => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => esc_html__( 'Default', 'tpebl' ),
				'label_on'  => esc_html__( 'Custom', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'style'          => array( 'style-5' ),
					'display_button' => 'yes',
				),

			)
		);
		$this->start_popover();
			$this->add_responsive_control(
				'button_style_margin_tb',
				array(
					'label'      => esc_html__( 'Top/Bottom Spacing', 'tpebl' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 500,
						),
						'%'  => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .pt-plus-button-wrapper.overflow' => 'top: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$this->add_responsive_control(
				'button_style_margin_lr',
				array(
					'label'      => esc_html__( 'Left/Right Spacing', 'tpebl' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 500,
						),
						'%'  => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .pt-plus-button-wrapper.overflow' => 'left: {{SIZE}}{{UNIT}};',
					),
				)
			);
		$this->end_popover();
		$this->end_controls_section();
		/*Post category*/
		$this->start_controls_section(
			'section_post_category_style',
			array(
				'label'     => esc_html__( 'Category Post', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'display_post_category' => 'yes',
					'style!'                => array( 'style-1', 'smart-loop-builder' ),
				),
			)
		);
		$this->add_control(
			'blog_category_post',
			array(
				'label'        => esc_html__( 'Category Post', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'style!'                => array( 'style-1' ),
					'display_post_category' => 'yes',
				),
			)
		);
		$this->start_popover();
		$this->add_responsive_control(
			'category_style_margin_tb',
			array(
				'label'      => esc_html__( 'Top/Bottom Spacing', 'tpebl' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tp-blog-cetegory-style-5, {{WRAPPER}} .tpae-blog-category-style-5' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'category_style_margin_lr',
			array(
				'label'      => esc_html__( 'Left/Right Spacing', 'tpebl' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .tp-blog-cetegory-style-5, {{WRAPPER}} .tpae-blog-category-style-5' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->end_popover();
		$this->add_responsive_control(
			'category_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .post-category-list.style-1 span a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
				'condition'  => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'category_typography',
				'label'     => esc_html__( 'Typography', 'tpebl' ),
				'selector'  => '{{WRAPPER}} .blog-list .post-category-list span a',
				'condition' => array(
					'display_post_category' => 'yes',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_category_style' );
		$this->start_controls_tab(
			'tab_category_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_control(
			'category_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-category-list span a' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_category_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_control(
			'category_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .post-category-list span:hover a' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'category_2_border_hover_color',
			array(
				'label'     => esc_html__( 'Hover Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ff214f',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a:before' => 'background: {{VALUE}};',
				),
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-2',
				),
			)
		);
		$this->add_control(
			'category_border',
			array(
				'label'     => esc_html__( 'Border', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'tpebl' ),
				'label_off' => esc_html__( 'Hide', 'tpebl' ),
				'default'   => 'no',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);

		$this->add_control(
			'category_border_style',
			array(
				'label'     => esc_html__( 'Border Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => l_theplus_get_border_style(),
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a' => 'border-style: {{VALUE}};',
				),
				'condition' => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_responsive_control(
			'box_category_border_width',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->start_controls_tabs(
			'tabs_category_border_style',
			array(
				'condition' => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->start_controls_tab(
			'tab_category_border_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_control(
			'category_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#252525',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_responsive_control(
			'category_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_category_border_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_control(
			'category_border_hover_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#252525',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span:hover a' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_responsive_control(
			'category_border_hover_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span:hover a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'category_border'       => 'yes',
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'category_bg_options',
			array(
				'label'     => esc_html__( 'Background Options', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->start_controls_tabs(
			'tabs_category_background_style',
			array(
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->start_controls_tab(
			'tab_category_background_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'category_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_category_background_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'category_hover_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span:hover a',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'category_shadow_options',
			array(
				'label'     => esc_html__( 'Box Shadow Options', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->start_controls_tabs(
			'tabs_category_shadow_style',
			array(
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->start_controls_tab(
			'tab_category_shadow_normal',
			array(
				'label'     => esc_html__( 'Normal', 'tpebl' ),
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'category_shadow',
				'selector'  => '{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span a',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_category_shadow_hover',
			array(
				'label'     => esc_html__( 'Hover', 'tpebl' ),
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'category_hover_shadow',
				'selector'  => '{{WRAPPER}} .blog-list .post-inner-loop .post-category-list span:hover a',
				'condition' => array(
					'display_post_category' => 'yes',
					'post_category_style'   => 'style-1',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
		/*Post category*/
		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => esc_html__( 'Title', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			's_title_pg',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} #pt-plus-blog-post-list.blog-list.blog-style-1 .post-title, 
					{{WRAPPER}} #pt-plus-blog-post-list.blog-list.blog-style-2 .post-title, 
					{{WRAPPER}} .blog-list.blog-style-3 h3.post-title, 
					{{WRAPPER}} #pt-plus-blog-post-list.blog-list.blog-style-4 .post-title,
					{{WRAPPER}} #pt-plus-blog-post-list.blog-list.blog-style-5 .post-title,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-title,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
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
				'selector' => '{{WRAPPER}} .blog-list .post-inner-loop .post-title,
				              {{WRAPPER}} .blog-list .post-inner-loop .post-title a,
							  {{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-title',
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
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .post-title,
					{{WRAPPER}} .blog-list .post-inner-loop .post-title a,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-title' => 'color: {{VALUE}}',
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
				'label'     => esc_html__( 'Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .post-title,
					{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .post-title a,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-compect-blog-wrap .post-title a:hover .post-title a,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-blog .tpae-preset-content .tpae-preset-title:hover' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_title_boxhover',
			array(
				'label'     => esc_html__( 'Box Hover', 'tpebl' ),
				'condition' => array(
					'style' => 'smart-loop-builder',
				),
			)
		);
		$this->add_control(
			'title_boxhover_color',
			array(
				'label'     => esc_html__( 'Title Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-blog:hover .tpae-preset-title' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_excerpt_style',
			array(
				'label'     => esc_html__( 'Content', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'display_excerpt' => 'yes',
				),
			)
		);
		/* Content Background Padding Start */
		$this->add_responsive_control(
			's_excerpt_content_pg',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .blog-list-content .entry-content p,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		/* Content Background Padding End */
		$this->add_responsive_control(
			'excerpt_margin',
			array(
				'label'      => esc_html__( 'Margin', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .entry-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'label'    => esc_html__( 'Typography', 'tpebl' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .blog-list .post-inner-loop .entry-content,
				{{WRAPPER}} .blog-list .post-inner-loop .entry-content p,
				{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-description',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .entry-content,
					{{WRAPPER}} .blog-list .post-inner-loop .entry-content p,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-description' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .entry-content,
					{{WRAPPER}} .blog-list .post-inner-loop .blog-list-content:hover .entry-content p,
					{{WRAPPER}} .blog-list .post-inner-loop .tpae-preset-description:hover' => 'color: {{VALUE}}',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'section_content_bg_style',
			array(
				'label'     => esc_html__( 'Content Background', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
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
				'selector' => '{{WRAPPER}} .blog-list.blog-style-1 .post-content-bottom,
				{{WRAPPER}} .blog-list .tpae-preset-content',
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
				'selector' => '{{WRAPPER}} .blog-list.blog-style-1 .blog-list-content:hover .post-content-bottom,
				{{WRAPPER}} .blog-list .tpae-preset-content:hover',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_post_image_style',
			array(
				'label'     => esc_html__( 'Featured Image', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'style!' => 'smart-loop-builder',
				),
			)
		);
		$this->add_responsive_control(
			'blog_featured_image_width',
			array(
				'label'      => esc_html__( 'Image width', 'tpebl' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
					'em' => array(
						'min' => 0,
						'max' => 50,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'vh' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .blog-featured-image .thumb-wrap img' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'style' => array( 'style-5' ),
				),
			)
		);
		$this->add_responsive_control(
			'blog_featured_image_height',
			array(
				'label'      => esc_html__( 'Image Height', 'tpebl' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
					'em' => array(
						'min' => 0,
						'max' => 50,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'vh' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .blog-featured-image .thumb-wrap img' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'style' => array( 'style-5' ),
				),
			)
		);
		$this->add_control(
			'hover_image_style',
			array(
				'label'   => esc_html__( 'Image Hover Effect', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => l_theplus_get_style_list( 1, 'yes' ),
			)
		);
		$this->add_responsive_control(
			'featured_image_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list.blog-style-3 .blog-list-content,
					{{WRAPPER}} .blog-list.blog-style-3 .blog-featured-image,
					{{WRAPPER}} .blog-list .tpae-compect-blog-wrap .blog-featured-image span.thumb-wrap img,
					{{WRAPPER}} .blog-list.blog-style-2 .blog-featured-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'tpae_enable_overlay',
			array(
				'label'        => esc_html__( 'Enable Overlay', 'tpebl' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'style' => 'style-5',
				),
			)
		);
		$this->add_control(
			'tpae_overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.6)',
				'selectors' => array(
					'{{WRAPPER}} .tpae-overlay-blog' => 'background: linear-gradient(to top, {{VALUE}}, transparent);',
				),
				'condition' => array(
					'tpae_enable_overlay' => 'yes',
					'style'               => 'style-5',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_filter_category_styling',
			array(
				'label'     => esc_html__( 'Filter Category', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'filter_category' => 'yes',
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
			'section_button_styling',
			array(
				'label'     => esc_html__( 'Button Style', 'tpebl' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'style'          => array( 'style-5' ),
					'display_button' => 'yes',
				),
			)
		);
		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => '15',
					'right'    => '30',
					'bottom'   => '15',
					'left'     => '30',
					'isLinked' => false,
				),
				'selectors'  => array(
					'{{WRAPPER}} .pt_plus_button .button-link-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'after',
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .pt_plus_button .button-link-wrap',
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);

		$this->add_control(
			'btn_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pt_plus_button .button-link-wrap' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pt_plus_button.button-style-7 .button-link-wrap:after' => 'border-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'icon_fill_color',
			array(
				'label'     => esc_html__( 'Fill', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .button-link-wrap .btn-icon svg path' => 'fill: {{VALUE}} !important;; ',
					'{{WRAPPER}} .button-link-wrap .btn-icon svg' => 'fill: {{VALUE}} !important;',

				),
				'condition' => array(
					'button_icon_style' => 'font_awesome_5',
				),
			)
		);
		$this->add_control(
			'icon_stroke_color',
			array(
				'label'     => esc_html__( 'Stroke', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .button-link-wrap .btn-icon svg path' => 'stroke: {{VALUE}} !important;; ',
					'{{WRAPPER}} .button-link-wrap .btn-icon svg' => 'stroke: {{VALUE}} !important;',

				),
				'condition' => array(
					'button_icon_style' => 'font_awesome_5',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'button_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap',
				'separator' => 'after',
				'condition' => array(
					'button_style!' => array( 'style-7', 'style-9' ),
				),
			)
		);
		$this->add_control(
			'button_border_style',
			array(
				'label'     => esc_html__( 'Border Style', 'tpebl' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'none'   => esc_html__( 'None', 'tpebl' ),
					'solid'  => esc_html__( 'Solid', 'tpebl' ),
					'dotted' => esc_html__( 'Dotted', 'tpebl' ),
					'dashed' => esc_html__( 'Dashed', 'tpebl' ),
					'groove' => esc_html__( 'Groove', 'tpebl' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap' => 'border-style: {{VALUE}};',
				),
				'condition' => array(
					'button_style' => array( 'style-8' ),
				),
			)
		);
		$this->add_responsive_control(
			'button_border_width',
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
					'{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'button_style'         => array( 'style-8' ),
					'button_border_style!' => 'none',
				),
			)
		);
		$this->add_control(
			'button_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#313131',
				'selectors' => array(
					'{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'button_style'         => array( 'style-8' ),
					'button_border_style!' => 'none',
				),
				'separator' => 'after',
			)
		);
		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'button_style' => array( 'style-8' ),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_shadow',
				'selector'  => '{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap',
				'condition' => array(
					'button_style' => array( 'style-8' ),
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_control(
			'btn_text_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pt_plus_button .button-link-wrap:hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'icon_fill_color_hover',
			array(
				'label'     => esc_html__( 'Fill', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .button-link-wrap .btn-icon:hover svg path' => 'fill: {{VALUE}} !important;; ',
					'{{WRAPPER}} .button-link-wrap .btn-icon:hover svg' => 'fill: {{VALUE}} !important;',

				),
				'condition' => array(
					'button_icon_style' => 'font_awesome_5',
				),
			)
		);
		$this->add_control(
			'icon_stroke_color_hover',
			array(
				'label'     => esc_html__( 'Stroke', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .button-link-wrap .btn-icon:hover svg path' => 'stroke: {{VALUE}} !important;; ',
					'{{WRAPPER}} .button-link-wrap .btn-icon:hover svg' => 'stroke: {{VALUE}} !important;',

				),
				'condition' => array(
					'button_icon_style' => 'font_awesome_5',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'button_hover_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap:hover',
				'separator' => 'after',
				'condition' => array(
					'button_style!' => array( 'style-7', 'style-9' ),
				),
			)
		);
		$this->add_control(
			'button_border_hover_color',
			array(
				'label'     => esc_html__( 'Hover Border Color', 'tpebl' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#313131',
				'selectors' => array(
					'{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'button_style'         => array( 'style-8' ),
					'button_border_style!' => 'none',
				),
				'separator' => 'after',
			)
		);
		$this->add_responsive_control(
			'button_hover_radius',
			array(
				'label'      => esc_html__( 'Hover Border Radius', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'button_style' => array( 'style-8' ),
				),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'button_hover_shadow',
				'selector'  => '{{WRAPPER}} .pt_plus_button.button-style-8 .button-link-wrap:hover',
				'condition' => array(
					'button_style' => array( 'style-8' ),
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'section_box_loop_styling',
			array(
				'label' => esc_html__( 'Box Loop Background Style', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'content_inner_padding',
			array(
				'label'      => esc_html__( 'Padding', 'tpebl' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'style!' => array( 'style-1', 'style-4' ),
				),
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
				'options'   => L_theplus_get_border_style(),
				'selectors' => array(
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog' => 'border-style: {{VALUE}};',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog' => 'border-color: {{VALUE}};',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content:hover,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap:hover,
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog:hover' => 'border-color: {{VALUE}};',
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
					'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content:hover
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap:hover
					{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'box_border' => 'yes',
				),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->start_controls_tabs( 'tabs_background_style' );
		$this->start_controls_tab(
			'tab_background_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'box_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' =>
				'{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content,
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap,
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_background_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'box_active_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .blog-list-content:hover,
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-compect-blog-wrap:hover,
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog:hover',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'content_box_shadow_options',
			array(
				'label'     => esc_html__( 'Box Shadow Options', 'tpebl' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->start_controls_tabs(
			'tabs_content_shadow_style',
			array()
		);
		$this->start_controls_tab(
			'tab_content_shadow_normal',
			array(
				'label' => esc_html__( 'Normal', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'content_shadow',
				'selector' =>
				'{{WRAPPER}} .blog-list.blog-style-1 .blog-list-content,
				{{WRAPPER}} .blog-list .tpae-compect-blog-wrap,
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_content_shadow_hover',
			array(
				'label' => esc_html__( 'Hover', 'tpebl' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'content_hover_shadow',
				'selector' =>
				'{{WRAPPER}} .blog-list.blog-style-1 .blog-list-content:hover,
				{{WRAPPER}} .blog-list .tpae-compect-blog-wrap:hover,
				{{WRAPPER}} .blog-list .post-inner-loop .grid-item .tpae-preset-blog:hover',
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
				'label' => esc_html__( 'Extra Option', 'tpebl' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'messy_column',
			array(
				'label'     => esc_html__( 'Messy Columns', 'tpebl' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'On', 'tpebl' ),
				'label_off' => esc_html__( 'Off', 'tpebl' ),
				'default'   => 'no',
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

		$Plus_Listing_block               = 'Plus_Listing_block';
		$tp_enable_global_scroll_animation = true;
		include L_THEPLUS_PATH . 'modules/widgets/theplus-widget-animation.php';

		include L_THEPLUS_PATH . 'modules/widgets/theplus-profeatures.php';
	}

	/**
	 * Render Written in PHP and HTML.
	 *
	 * @since 1.0.0
	 * @version 5.5.4
	 */
	public function render() {

		$settings   = $this->get_settings_for_display();
		$settings   = TP_Global_Scroll_Animation_Helper::resolve_widget_settings( $settings );
		$query_args = $this->get_query_args();
		$query      = new \WP_Query( $query_args );

		$style        = ! empty( $settings['style'] ) ? $settings['style'] : 'style-1';
		$layout       = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';
		$post_tags    = ! empty( $settings['post_tags'] ) ? $settings['post_tags'] : '';
		$content_html = ! empty( $settings['content_html'] ) ? $settings['content_html'] : '';

		$post_title_tag = ! empty( $settings['post_title_tag'] ) ? $settings['post_title_tag'] : 'h3';
		$post_category  = ! empty( $settings['post_category'] ) ? $settings['post_category'] : '';

		$display_post_meta   = ! empty( $settings['display_post_meta'] ) ? $settings['display_post_meta'] : '';
		$post_meta_tag_style = ! empty( $settings['post_meta_tag_style'] ) ? $settings['post_meta_tag_style'] : 'style-1';

		$post_excerpt_count = ! empty( $settings['post_excerpt_count'] ) ? $settings['post_excerpt_count'] : 30;
		$animation_stagger  = ! empty( $settings['animation_stagger']['size'] ) ? $settings['animation_stagger']['size'] : 150;
		$ani_out_duration   = ! empty( $settings['animation_out_duration']['size'] ) ? $settings['animation_out_duration']['size'] : 50;

		$display_excerpt = ! empty( $settings['display_excerpt'] ) ? $settings['display_excerpt'] : '';
		$out_duration    = ! empty( $settings['animation_out_duration_default'] ) ? $settings['animation_out_duration_default'] : '';
		$metro_col       = ! empty( $settings['metro_column'] ) ? $settings['metro_column'] : '3';

		$ani_effect = ! empty( $settings['animation_effects'] ) ? $settings['animation_effects'] : 'no-animation';
		$ani_delay  = ! empty( $settings['animation_delay']['size'] ) ? $settings['animation_delay']['size'] : 50;
		$out_delay  = ! empty( $settings['animation_out_delay']['size'] ) ? $settings['animation_out_delay']['size'] : 50;

		$ani_list     = ! empty( $settings['animated_column_list'] ) ? $settings['animated_column_list'] : '';
		$ani_duration = ! empty( $settings['animation_duration_default'] ) ? $settings['animation_duration_default'] : '';
		$ani_speed    = ! empty( $settings['animate_duration']['size'] ) ? $settings['animate_duration']['size'] : 50;
		$out_effect   = ! empty( $settings['animation_out_effects'] ) ? $settings['animation_out_effects'] : 'no-animation';

		$tp_absolute_layout = ! empty( $settings['tp_absolute_layout'] ) ? $settings['tp_absolute_layout'] : 'no';

		$show_date_time   = ! empty( $settings['show_post_date'] ) && 'yes' === $settings['show_post_date'] ? 'yes' : 'no';
		$show_author_name = ! empty( $settings['show_post_author'] ) && 'yes' === $settings['show_post_author'] ? 'yes' : 'no';
		$show_read_time   = ! empty( $settings['show_read_time'] ) && 'yes' === $settings['show_read_time'] ? 'yes' : 'no';
		$display_btn      = ! empty( $settings['display_button'] ) ? $settings['display_button'] : '';
		$author_prefix    = ! empty( $settings['author_prefix'] ) ? $settings['author_prefix'] : 'By';

		$before_after      = $settings['before_after'];
		$button_text       = $settings['button_text'];
		$button_icon_style = $settings['button_icon_style'];
		$button_icon       = $settings['button_icon'];
		// $button_icons_mind = $settings['button_icons_mind'];

		$tp_date_overflow      = $settings['date_overflow'];
		$tp_meta_overflow      = $settings['meta_overflow'];
		$tp_post_tima_overflow = $settings['post_tima_overflow'];

		$display_post_category = $settings['display_post_category'];
		$post_category_style   = $settings['post_category_style'];

		$display_title_limit  = $settings['display_title_limit'];
		$display_title_by     = $settings['display_title_by'];
		$display_title_input  = $settings['display_title_input'];
		$display_title_3_dots = $settings['display_title_3_dots'];

		$animated_columns = '';

		if ( 'no-animation' === $ani_effect ) {
			$animated_class = '';
			$animation_attr = '';
		} else {
			$animate_offset  = '85%';
			$animated_class  = 'animate-general';
			$animation_attr  = ' data-animate-type="' . esc_attr( $ani_effect ) . '" data-animate-delay="' . esc_attr( $ani_delay ) . '"';
			$animation_attr .= ' data-animate-offset="' . esc_attr( $animate_offset ) . '"';

			if ( 'stagger' === $ani_list ) {
				$animated_columns = 'animated-columns';
				$animation_attr  .= ' data-animate-columns="stagger"';
				$animation_attr  .= ' data-animate-stagger="' . esc_attr( $animation_stagger ) . '"';
			} elseif ( 'columns' === $ani_list ) {
				$animated_columns = 'animated-columns';
				$animation_attr  .= ' data-animate-columns="columns"';
			}

			if ( 'yes' === $ani_duration ) {
				$animate_duration = $ani_speed;
				$animation_attr  .= ' data-animate-duration="' . esc_attr( $animate_duration ) . '"';
			}

			if ( 'no-animation' !== $out_effect ) {
				$animation_attr .= ' data-animate-out-type="' . esc_attr( $out_effect ) . '" data-animate-out-delay="' . esc_attr( $out_delay ) . '"';

				if ( 'yes' === $out_duration ) {
					$animation_attr .= ' data-animate-out-duration="' . esc_attr( $ani_out_duration ) . '"';
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

		$layout_attr = '';
		$data_class  = '';

		if ( ! empty( $layout ) ) {
			$data_class .= l_theplus_get_layout_list_class( $layout );
			$layout_attr = l_theplus_get_layout_list_attr( $layout );
		} else {
			$data_class .= ' list-isotope';
		}

		$metro_columns = $metro_col;
		$metro_style   = ! empty( $settings[ 'metro_style_' . $metro_columns ] ) ? $settings[ 'metro_style_' . $metro_columns ] : 'style-1';

		if ( 'metro' === $layout ) {

			$layout_attr .= ' data-metro-columns="' . esc_attr( $metro_columns ) . '" ';
			if ( ! empty( $metro_style ) ) {

				$layout_attr .= ' data-metro-style="' . esc_attr( $metro_style ) . '" ';
			}
		}

		$data_class .= ' blog-' . esc_attr( $style );
		$data_class .= ' hover-image-' . esc_attr( $settings['hover_image_style'] );

		$output    = '';
		$data_attr = '';

		$ji  = 1;
		$ij  = '';
		$uid = uniqid( 'post' );

		$data_attr .= ' data-id="' . esc_attr( $uid ) . '"';
		$data_attr .= ' data-style="' . esc_attr( $style ) . '"';

		$tablet_ij = '';

		if ( ! $query->have_posts() ) {
			$output .= '<h3 class="theplus-posts-not-found">' . esc_html__( 'Posts not found', 'tpebl' ) . '</h3>';
		} elseif ( 'style-1' === $style || 'smart-loop-builder' === $style || 'style-5' === $style ) {

			if ( 'smart-loop-builder' === $style ) {
				$style_custom = ! empty( $settings['content_css'] ) ? $settings['content_css'] : '';
				$html_custom  = ! empty( $settings['content_html'] ) ? $settings['content_html'] : '';

				if ( ! empty( $style_custom ) ) {
					echo '<style>' . wp_strip_all_tags( $style_custom ) . '</style>';
				}

				if ( empty( $html_custom ) ) {
					$output .= '<h3 class="theplus-posts-not-found">' . esc_html__( 'Please enter values in both HTML & CSS to enable the Smart Loop Builder feature. If you don’t want to write your own, choose from Ready Presets.', 'tpebl' ) . '</h3>';
				}
			}

			$output .= '<div id="pt-plus-blog-post-list" class="blog-list ' . esc_attr( $uid ) . ' ' . $data_class . ' ' . esc_attr( $animated_class ) . '" ' . $layout_attr . ' ' . $data_attr . ' ' . $animation_attr . ' data-enable-isotope="1">';

			$output .= '<div id="' . esc_attr( $uid ) . '" class="tp-row post-inner-loop ' . esc_attr( $uid ) . ' ">';

			while ( $query->have_posts() ) {

				$query->the_post();
				$post = $query->post;

				// read more button
				$the_button = $button_attr = '';
				if ( 'yes' === $settings['display_button'] ) {
					$button_attr = 'button' . $ji;
					if ( ! empty( get_the_permalink() ) ) {
						$this->add_render_attribute( $button_attr, 'href', get_the_permalink() );
						$this->add_render_attribute( $button_attr, 'rel', 'nofollow' );
					}

					$this->add_render_attribute( $button_attr, 'class', 'button-link-wrap' );
					$this->add_render_attribute( $button_attr, 'role', 'button' );

					$button_style   = $settings['button_style'];
					$button_text    = $settings['button_text'];
					$btn_uid        = uniqid( 'btn' );
					$data_class     = $btn_uid;
					$data_class    .= ' button-' . $button_style . ' ';
					$overflow_class = ( ! empty( $settings['button_overflow'] ) && 'yes' === $settings['button_overflow'] ) ? ' overflow' : '';

					$the_button                      = '<div class="pt-plus-button-wrapper' . $overflow_class . '">';
						$the_button                 .= '<div class="button_parallax">';
							$the_button             .= '<div class="ts-button">';
								$the_button         .= '<div class="pt_plus_button ' . $data_class . '">';
									$the_button     .= '<div class="animted-content-inner">';
										$the_button .= '<a ' . $this->get_render_attribute_string( $button_attr ) . '>';
										$the_button .= include L_THEPLUS_WSTYLES . 'blog/post-button.php';
										$the_button .= '</a>';
									$the_button     .= '</div>';
								$the_button         .= '</div>';
							$the_button             .= '</div>';
						$the_button                 .= '</div>';
					$the_button                     .= '</div>';
				}

				if ( 'metro' === $layout ) {

					if ( ! empty( $metro_style ) ) {
						$ij = l_theplus_metro_style_layout( $ji, $metro_col, $metro_style );
					}
				}

				$output .= '<div class="grid-item metro-item' . esc_attr( $ij ) . ' ' . $desktop_class . ' ' . $tablet_class . ' ' . $mobile_class . ' ' . esc_attr( $animated_columns ) . '">';

				if ( ! empty( $style ) ) {
					ob_start();
					include L_THEPLUS_WSTYLES . 'blog/blog-' . sanitize_file_name( $style ) . '.php';
					$output .= ob_get_contents();
					ob_end_clean();
				}

				$output .= '</div>';

				++$ji;
			}

			$output .= '</div>';

			if ( ! empty( $post_category ) && is_array( $post_category ) ) {
				$post_category = implode( ',', $post_category );
			} else {
				$post_category = '';
			}

			if ( ! empty( $post_tags ) && is_array( $post_tags ) ) {
				$post_tags = implode( ',', $post_tags );
			} else {
				$post_tags = '';
			}

			if ( $query->found_posts != '' ) {
				$total_posts   = $query->found_posts;
				$post_offset   = ! empty( $settings['post_offset'] ) ? $settings['post_offset'] : 0;
				$display_posts = ! empty( $settings['display_posts'] ) ? $settings['display_posts'] : 0;
				$offset_posts  = intval( $display_posts + $post_offset );
				$total_posts   = intval( $total_posts - $offset_posts );

				if ( $total_posts != 0 && $settings['load_more_post'] != 0 ) {
					$load_page = ceil( intval( $total_posts ) / intval( $settings['load_more_post'] ) );
				} else {
					$load_page = 1;
				}

				$load_page = $load_page + 1;
			} else {
				$load_page = 1;
			}

			$loaded_posts_text = ! empty( $settings['loaded_posts_text'] ) ? $settings['loaded_posts_text'] : 'All done!';
			$tp_loading_text   = ! empty( $settings['tp_loading_text'] ) ? $settings['tp_loading_text'] : 'Loading...';

			$data_loadkey = '';
			if ( ( 'load_more' == $settings['post_extra_option'] || 'lazy_load' == $settings['post_extra_option'] ) && 'carousel' != $layout ) {
				$postattr = array(
					'load'                => 'blogs',
					'post_type'           => 'post',
					'texonomy_category'   => 'cat',
					'post_title_tag'      => esc_attr( $post_title_tag ),
					'layout'              => esc_attr( $layout ),
					'style'               => esc_attr( $style ),
					'desktop-column'      => esc_attr( $settings['desktop_column'] ),
					'tablet-column'       => esc_attr( $settings['tablet_column'] ),
					'mobile-column'       => esc_attr( $settings['mobile_column'] ),
					'metro_column'        => esc_attr( $settings['metro_column'] ),
					'metro_style'         => esc_attr( $metro_style ),
					'offset-posts'        => esc_attr( $settings['post_offset'] ),
					'category'            => esc_attr( $post_category ),
					'post_tags'           => esc_attr( $post_tags ),
					'order_by'            => esc_attr( $settings['post_order_by'] ),
					'post_order'          => esc_attr( $settings['post_order'] ),
					'filter_category'     => esc_attr( $settings['filter_category'] ),
					'display_post'        => esc_attr( $settings['display_posts'] ),
					'animated_columns'    => esc_attr( $animated_columns ),
					'post_load_more'      => esc_attr( $settings['load_more_post'] ),
					'content_html'        => $content_html,

					'display_post_meta'   => $display_post_meta,
					'post_meta_tag_style' => $post_meta_tag_style,
					'theplus_nonce'       => wp_create_nonce( 'theplus-addons' ),
				);

				$postattr     = array_merge( $postattr );
				$data_loadkey = L_tp_plus_simple_decrypt( wp_json_encode( $postattr ), 'ey' );
			}

			if ( $settings['post_extra_option'] == 'load_more' && $layout != 'carousel' ) {
				if ( ! empty( $total_posts ) && $total_posts > 0 ) {
					$load_more_btn_type_switch         = ! empty( $settings['load_more_btn_type_switch'] ) ? $settings['load_more_btn_type_switch'] : 'basic';
					$load_more_btn_global_style_preset = ! empty( $settings['load_more_btn_global_style_preset'] ) ? $settings['load_more_btn_global_style_preset'] : '';
					$load_more_anchor_attr            = 'class="post-load-more';

					if ( 'global' === $load_more_btn_type_switch ) {
						$load_more_btn_uid    = uniqid( 'load_more_btn' );
						$load_more_global_css = ! empty( $load_more_btn_global_style_preset ) ? $this->build_global_button_style_css( $load_more_btn_global_style_preset, '#' . $load_more_btn_uid ) : '';

						$output .= '<div id="' . esc_attr( $load_more_btn_uid ) . '" class="ajax_load_more">';

						if ( ! empty( $load_more_global_css ) ) {
							$output .= '<style>' . wp_strip_all_tags( $load_more_global_css ) . '</style>';
						}

						$output .= '<div class="pt_plus_button button-style-8">';
						$load_more_anchor_attr .= ' button-link-wrap';
					} else {
						$output .= '<div class="ajax_load_more">';
					}

					$load_more_anchor_attr .= '" data-layout="' . esc_attr( $layout ) . '" data-offset-posts="' . esc_attr( $settings['post_offset'] ) . '" data-load-class="' . esc_attr( $uid ) . '" data-display_post="' . esc_attr( $settings['display_posts'] ) . '" data-post_load_more="' . esc_attr( $settings['load_more_post'] ) . '" data-loaded_posts="' . esc_attr( $loaded_posts_text ) . '" data-tp_loading_text="' . esc_attr( $tp_loading_text ) . '" data-page="1" data-total_page="' . esc_attr( $load_page ) . '" data-loadattr= \'' . $data_loadkey . '\'';
					$output .= '<a ' . $load_more_anchor_attr . '>' . esc_html( $settings['load_more_btn_text'] ) . '</a>';

					if ( 'global' === $load_more_btn_type_switch ) {
						$output .= '</div>';
					}

					$output .= '</div>';
				}
			}

			$output .= '</div>';
		} else {
			$output .= '<h3 class="theplus-posts-not-found">' . esc_html__( 'This Style Premium Version', 'tpebl' ) . '</h3>';
		}

		echo $output;

		wp_reset_postdata();
	}

	/**
	 * Fetch post data Written in PHP and HTML.
	 *
	 * @since 1.0.0
	 * @version 5.4.2
	 */
	public function get_query_args() {
		$settings  = $this->get_settings_for_display();
		$post_tags = ! empty( $settings['post_tags'] ) ? $settings['post_tags'] : '';

		$post_category = ! empty( $settings['post_category'] ) ? $settings['post_category'] : '';

		$query_args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => intval( $settings['display_posts'] ),
			'orderby'             => $settings['post_order_by'],
			'order'               => $settings['post_order'],
		);

		$offset = $settings['post_offset'];
		$offset = ! empty( $offset ) ? absint( $offset ) : 0;

		if ( $offset ) {
			$query_args['offset'] = $offset;
		}

		if ( ! empty( $post_category ) ) {
			$query_args['category__in'] = $post_category;
		}

		if ( ! empty( $post_tags ) ) {
			$query_args['tag__in'] = $post_tags;
		}

		global $paged;

		$paged_custom = 1;
		$paged_custom = $paged;
		if ( get_query_var( 'paged' ) ) {
			$paged_custom = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged_custom = get_query_var( 'page' );
		} else {
			$paged_custom = 1;
		}

		$query_args['paged'] = $paged_custom;

		return $query_args;
	}

	/**
	 * Fetch post data Written in PHP and HTML.
	 *
	 * @version 6.1.1
	 */
	public function l_theplus_get_categories() {

		$categories = get_categories();

		if ( empty( $categories ) || ! is_array( $categories ) ) {
			return array();
		}

		return wp_list_pluck( $categories, 'name', 'term_id' );
	}
}
