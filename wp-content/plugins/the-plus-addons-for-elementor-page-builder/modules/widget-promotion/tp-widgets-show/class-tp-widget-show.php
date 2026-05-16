<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      6.4.8
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */


/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Widget_Show' ) ) {

	/**
	 * This class used for only load widget notice
	 *
	 * @since 6.4.8
	 */
	class Tp_Widget_Show {

		/**
		 * Instance
		 *
		 * @since 6.4.8
		 * @access private
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.4.8
		 * @access public
		 * @static
		 * @return instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Perform some compatibility checks to make sure basic requirements are meet.
		 *
		 * @since 6.4.8
		 */
		public function __construct() {

			add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'tp_inactive_widgets_sripts' ) );

			add_action('wp_ajax_tpae_handle_enable_widget', array( $this, 'tpae_handle_enable_widget' ) );
			
		}

		public function tpae_wl_help_link_enabled() {
			$whitelabel = get_option( 'theplus_white_label' );

			if ( ! empty( $whitelabel['help_link'] ) && 'on' === $whitelabel['help_link'] ) {
				return true;
			}
			return false;
		}

		public function tp_inactive_widgets_sripts() {

			$tpae_free_widgets = array(
				array(
					'name'    => 'tp_accordion',
					'title'   => __( 'Accordion', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-accordion',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/accordion/',
					'tag'     => 'freemium',
				),
				array(
					'name'     => 'tp_age_gate',
					'title'    => __( 'Age Gate', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-age-gate',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/age-gate-verification/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_blockquote',
					'title'    => __( 'Blockquote', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-blockquote',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/blockquote/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_button',
					'title'    => __( 'Button', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-button',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/free-buttons/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_breadcrumbs_bar',
					'title'    => __( 'Breadcrumbs Bar', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-breadcrumbs-bar',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/breadcrumbs-bar/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_countdown',
					'title'    => __( 'Count Down', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-count-down',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/countdown-timer/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_carousel_anything',
					'title'    => __( 'Carousel Anything', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-carousel-anything',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/carousel-slider/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_dynamic_categories',
					'title'    => __( 'Dynamic Categories', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-dynamic-categories',
					'demo_url'  => 'https://theplusaddons.com/elementor-listing/dynamic-category/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_dark_mode',
					'title'    => __( 'Dark Mode', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-dark-mode',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/dark-mode-switcher/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_heading_title',
					'title'    => __( 'Heading Title', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-heading-title',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/heading-titles/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_info_box',
					'title'    => __( 'Info Box', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-info-box',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/info-box/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_icon',
					'title'    => __( 'Icon', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-icon',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/custom-icons/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_messagebox',
					'title'    => __( 'Message Box', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-message-box',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/message-box/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_number_counter',
					'title'    => __( 'Number Counter', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-number-counter',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/number-counter/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_progress_bar',
					'title'    => __( 'Progress Bar', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-progress-bar',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/progress-bar/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_title',
					'title'    => __( 'Post Title', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-title',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-title/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_pricing_table',
					'title'    => __( 'Pricing Table', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-pricing-table',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/pricing-table/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_post_content',
					'title'    => __( 'Post Content', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-content',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-content/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_featured_image',
					'title'    => __( 'Post Featured Image', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-featured-image',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-featured-image/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_navigation',
					'title'    => __( 'Post Prev/Next', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-prev-next',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-navigation/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_author',
					'title'    => __( 'Post Author', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-author',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-author/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_comment',
					'title'    => __( 'Post Comment', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-comment',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-comment/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_meta',
					'title'    => __( 'Post Meta', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-meta',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/blog-builder/post-meta/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_style_list',
					'title'    => __( 'Stylish List', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-stylish-list',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/stylish-list/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_syntax_highlighter',
					'title'    => __( 'Syntax Highlighter', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-syntax-highlighter',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/syntax-code-highlighter/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_table',
					'title'    => __( 'Table', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-table',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/data-tables/',
					'tag'      => __( 'freemium', 'tpebl' ),
				),
				array(
					'name'     => 'tp_tabs_tours',
					'title'    => __( 'Tabs/Tours', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-tabs-tours',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/tabs/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_adv_text_block',
					'title'    => __( 'Text Block', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-text-block',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/advanced-text-block/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_video_player',
					'title'    => __( 'Video Player', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-video-player',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/video-player/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_post_search',
					'title'    => __( 'Post Search', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-post-search',
					'demo_url'  => 'https://theplusaddons.com/widgets/search-bar/',
					'tag'      => 'DEPRECATED',
				),
				array(
					'name'     => 'tp_header_extras',
					'title'    => __( 'Header Meta Content', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-header-meta-content',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/header-builder/header-extras/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_navigation_menu_lite',
					'title'    => __( 'Navigation Menu Lite', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-navigation-menu-lite',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/header-builder/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_page_scroll',
					'title'    => __( 'Page Scroll', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-page-scroll',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/full-page-scroll/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_heading_animation',
					'title'    => __( 'Heading Animation', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-heading-animation',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/heading-animations/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_flip_box',
					'title'    => __( 'Flip Box', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-flip-box',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/flipbox/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_hovercard',
					'title'    => __( 'Hover Card', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-hover-card',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/hover-card/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_smooth_scroll',
					'title'    => __( 'Smooth Scroll', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-smooth-scroll',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/smooth-scroll/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_process_steps',
					'title'    => __( 'Process Steps', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-process-steps',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/process-steps/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_scroll_navigation',
					'title'    => __( 'Scroll Navigation', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-scroll-navigation',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/header-builder/one-page-scroll-navigation/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_switcher',
					'title'    => __( 'Switcher', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-switcher',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/switcher/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_blog_listout',
					'title'    => __( 'Blog Listing', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-blog-listing',
					'demo_url'  => 'https://theplusaddons.com/elementor-listing/blog-post/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_clients_listout',
					'title'    => __( 'Clients Listing', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-clients-listing',
					'demo_url'  => 'https://theplusaddons.com/elementor-listing/client-logos/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_gallery_listout',
					'title'    => __( 'Gallery Listing', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-gallery-listing',
					'demo_url'  => 'https://theplusaddons.com/elementor-listing/image-gallery/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_team_member_listout',
					'title'    => __( 'Team Member Listing', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-team-member-listing',
					'demo_url'  => 'https://theplusaddons.com/elementor-listing/team-members/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_testimonial_listout',
					'title'    => __( 'Testimonial', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-testimonial',
					'demo_url'  => 'https://theplusaddons.com/elementor-listing/testimonials/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_social_embed',
					'title'    => __( 'Social Embed', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-social-embed',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/social-embed/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_social_icon',
					'title'    => __( 'Social Icon', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-social-icon',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/social-icons/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_contact_form_7',
					'title'    => __( 'Contact Form 7', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-contact-form-7',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/contact-form-7-styler/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_everest_form',
					'title'    => __( 'Everest Form', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-everest-form',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/everest-forms-styler/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_plus_form',
					'title'    => __( 'Form', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-wp-forms',
					'demo_url'  => 'https://theplusaddons.com/elementor-builder/form-builder/',
					'tag'      => 'freemium',
				),
				array(
					'name'     => 'tp_gravity_form',
					'title'    => __( 'Gravity Form', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-gravity-form',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/gravity-forms-styler/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_meeting_scheduler',
					'title'    => __( 'Meeting Scheduler', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-meeting-scheduler',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/meeting-schedulers/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_ninja_form',
					'title'    => __( 'Ninja Form', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-ninja-form',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/ninja-forms-styler/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_wp_forms',
					'title'    => __( 'WP Forms', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-wp-forms',
					'demo_url'  => 'https://theplusaddons.com/elementor-widget/wpforms-styler/',
					'tag'      => 'free',
				),
				array(
					'name'     => 'tp_caldera_forms',
					'title'    => __( 'Caldera Forms', 'tpebl' ),
					'icon'    => 'tpae-editor-logo theplus-i-caldera-form',
					'demo_url'  => 'https://theplusaddons.com/widgets/caldera-forms/',
					'tag'      => 'DEPRECATED',
				),
			);
			$tpae_pro_widgets = array(
				array(
					'name'     => 'tp_audio_player',
					'title'    => __( 'Audio Player', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-audio-player',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/audio-player/',
				),
				array(
					'name'     => 'tp_advanced_typography',
					'title'    => __( 'Advanced Typography', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-advanced-tpography',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/advanced-typography/',
				),
				array(
					'name'     => 'tp_advanced_buttons',
					'title'    => __( 'Advanced Buttons', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-advanced-buttons',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/advanced-buttons/',
				),
				array(
					'name'     => 'tp_advertisement_banner',
					'title'    => __( 'Advertisement Banner', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-advertisement-banner',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/banner-widget/',
				),
				array(
					'name'     => 'tp_animated_service_boxes',
					'title'    => __( 'Animated Service Boxes', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-animated-service-boxes',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/animated-service-boxes/',
				),
				array(
					'name'     => 'tp_before_after',
					'title'    => __( 'Before After', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-before-after',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/before-after-slider/',
				),
				array(
					'name'     => 'tp_dynamic_smart_showcase',
					'title'    => __( 'Dynamic Smart Showcase', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-dynamic-smart-showcase',
					'demo_url' => 'https://theplusaddons.com/elementor-listing/#plus-magazine-post-styles/',
				),
				array(
					'name'     => 'tp_wp_bodymovin',
					'title'    => __( 'LottieFiles Animation', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-lottiefiles-animation',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/lottiefiles-animation/',
				),
				array(
					'name'     => 'tp_carousel_remote',
					'title'    => __( 'Carousel Remote', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-carousel-remote',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/carousel-remote-sync/',
				),
				array(
					'name'     => 'tp_cascading_image',
					'title'    => __( 'Image Cascading', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-cascading-image',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/image-cascading/',
				),
				array(
					'name'     => 'tp_chart',
					'title'    => __( 'Chart', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-chart',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/advanced-charts/',
				),
				array(
					'name'     => 'tp_circle_menu',
					'title'    => __( 'Circle Menu', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-circle-menu',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/circle-menu/',
				),
				array(
					'name'     => 'tp_coupon_code',
					'title'    => __( 'Coupon Code', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-coupon-code',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/coupon-code/',
				),
				array(
					'name'     => 'tp_design_tool',
					'title'    => __( 'Design Tool', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-design-tool',
					'demo_url' => 'https://theplusaddons.com/elementor-extras/grid-design-tool/',
				),
				array(
					'name'     => 'tp_draw_svg',
					'title'    => __( 'Draw SVG', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-draw-svg',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/draw-animated-svg-icon/',
				),
				array(
					'name'     => 'tp_dynamic_listing',
					'title'    => __( 'Dynamic Listing', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-dynamic-listing',
					'demo_url' => 'https://theplusaddons.com/elementor-listing/dynamic-listing/',
				),
				// array(
				// 	'name'     => 'tp-custom-field-adv',
				// 	'title'    => 'Custom Field',
				// 	'icon'     => 'tpae-editor-logo theplus-i-tp-custom-field',
				// 	'demo_url' => 'https://theplusaddons.com/elementor-builder/grid-builder/acf-repeater-field/',
				// ),
				array(
					'name'     => 'tp_dynamic_device',
					'title'    => __( 'Dynamic Device', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-dynamic-device',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/dynamic-device-mockups/',
				),
				array(
					'name'     => 'tp_google_map',
					'title'    => __( 'Google Map', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-google-map',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/google-map/',
				),
				array(
					'name'     => 'tp_hotspot',
					'title'    => __( 'Hotspot', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-hotspot',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/hotspot/',
				),
				array(
					'name'     => 'tp_horizontal_scroll_advance',
					'title'    => __( 'Horizontal Scroll', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-horizontal-scroll',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/horizontal-scroll/',
				),
				array(
					'name'     => 'tp_image_factory',
					'title'    => __( 'Creative Image', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-creative-image',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/creative-images',
				),
				array(
					'name'     => 'tp_mailchimp',
					'title'    => __( 'MailChimp', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-mailchimp',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/mailchimp-form/',
				),
				array(
					'name'     => 'tp_mobile_menu',
					'title'    => __( 'Mobile Menu', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-mobile-menu',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/header-builder/mobile-menu/',
				),
				array(
					'name'     => 'tp_morphing_layouts',
					'title'    => __( 'Morphing Layouts', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-morphing-layouts',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/morphing-sections',
				),
				array(
					'name'     => 'tp_mouse_cursor',
					'title'    => __( 'Mouse Cursor', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-mouse-cursor',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/mouse-cursor/',
				),
				array(
					'name'     => 'tp_navigation_menu',
					'title'    => __( 'Navigation Menu', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-navigation-menu',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/header-builder/navigation-menu/',
				),
				array(
					'name'     => 'tp_off_canvas',
					'title'    => __( 'Popup Builder / Off Canvas', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-popup-builder-offcanvas',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/popup-builder/',
				),
				array(
					'name'     => 'tp_pre_loader',
					'title'    => __( 'Pre Loader', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-pre-loader',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/preloader-and-page-transition/',
				),
				array(
					'name'     => 'tp_pricing_list',
					'title'    => __( 'Pricing List', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-pricing-list',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/price-list/',
				),
				array(
					'name'     => 'tp_product_listout',
					'title'    => __( 'Product Listing', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-product-listing',
					'demo_url' => 'https://theplusaddons.com/elementor-listing/woocommerce-product/',
				),
				array(
					'name'     => 'tp_protected_content',
					'title'    => __( 'Protected Content', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-protected-content',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/protected-content/',
				),
				array(
					'name'     => 'tp_row_background',
					'title'    => __( 'Row Background', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-row-background',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/section-background/',
				),
				array(
					'name'     => 'tp_scroll_sequence',
					'title'    => __( 'Scroll Sequence', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-scroll-sequence',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/image-scroll-sequence/',
				),
				array(
					'name'     => 'tp_search_filter',
					'title'    => __( 'Search Filter', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-search-filters',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/ajax-filters',
				),
				array(
					'name'     => 'tp_search_bar',
					'title'    => __( 'Search Bar', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-search-bar',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/ajax-search-bar',
				),
				array(
					'name'     => 'tp_site_logo',
					'title'    => __( 'Site Logo', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-site-logo',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/header-builder/site-logo-for-elementor/',
				),
				array(
					'name'     => 'tp_shape_divider',
					'title'    => __( 'Advanced Separators', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-advanced-separators',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/advanced-separators',
				),
				array(
					'name'     => 'tp_social_feed',
					'title'    => __( 'Social Feed', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-social-feed',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/combined-filterable-social-feed/',
				),
				array(
					'name'     => 'tp_social_reviews',
					'title'    => __( 'Social Reviews', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-social-reviews',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/social-reviews-badges/',
				),
				array(
					'name'     => 'tp_social_sharing',
					'title'    => __( 'Social Sharing', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-social-sharing',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/social-sharing-buttons/',
				),
				array(
					'name'     => 'tp_table_content',
					'title'    => __( 'Table of Content', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-table-of-content',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/table-of-contents/',
				),
				array(
					'name'     => 'tp_timeline',
					'title'    => __( 'Timeline', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-timeline',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/timeline/',
				),
				array(
					'name'     => 'tp_unfold',
					'title'    => __( 'Unfold', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-unfold',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/unfold/',
				),
				array(
					'name'     => 'tp_woo_cart',
					'title'    => __( 'Woo Cart', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-cart',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/cart-page/',
				),
				array(
					'name'     => 'tp_woo_checkout',
					'title'    => __( 'Woo Checkout', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-checkout',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/checkout-page/',
				),
				array(
					'name'     => 'tp_woo_compare',
					'title'    => __( 'Woo Compare', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-compare',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/product-compare/',
				),
				array(
					'name'     => 'tp_wp_quickview',
					'title'    => __( 'Woo Quickview', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-quick-view',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/quick-view/',
				),
				array(
					'name'     => 'tp_woo_multi_step',
					'title'    => __( 'Woo Multi Step', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-multi-step',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/multi-step-checkout/',
				),
				array(
					'name'     => 'tp_woo_myaccount',
					'title'    => __( 'Woo My Account', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-my-account',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/my-account-page/',
				),
				array(
					'name'     => 'tp_woo_order_track',
					'title'    => __( 'Woo Order Track', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-order-track',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/order-track-page/',
				),
				array(
					'name'     => 'tp_woo_single_basic',
					'title'    => __( 'Woo Single Basic', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-single-basic',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'     => 'tp_woo_single_image',
					'title'    => __( 'Woo Single Image', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-product-images',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'     => 'tp_woo_single_pricing',
					'title'    => __( 'Woo Single Pricing', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-single-pricing',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'     => 'tp_woo_single_tabs',
					'title'    => __( 'Woo Single Tabs', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-single-tabs',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'     => 'tp_woo_thank_you',
					'title'    => __( 'Woo Thank You', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-thank-you',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/thank-you-page/',
				),
				array(
					'name'     => 'tp_woo_wishlist',
					'title'    => __( 'Woo Wishlist', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-woo-wishlist',
					'demo_url' => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'     => 'tp_wp_login_register',
					'title'    => __( 'WP Login Register', 'tpebl' ),
					'icon'     => 'tpae-editor-logo theplus-i-wp-login-register',
					'demo_url' => 'https://theplusaddons.com/elementor-widget/login-form/',
				),
			);

			$get_widgets_list = get_option( 'theplus_options' );
			$check_elements   = ! empty( $get_widgets_list['check_elements'] ) ? $get_widgets_list['check_elements'] : array();

			if(defined( 'THEPLUS_VERSION' )){

				$tpae_all_w_list =array_merge( $tpae_free_widgets, $tpae_pro_widgets );

				$tpae_widgets_final_list = array_filter( $tpae_all_w_list, function( $widget ) use ( $check_elements ) {
					return ! in_array( $widget['name'], $check_elements );
				});

				$tpae_widgets_list = array_values( $tpae_widgets_final_list );

			}else{
                $tpae_all_w_list = $tpae_free_widgets;

				$tpae_widgets_final_list = array_filter( $tpae_all_w_list, function( $widget ) use ( $check_elements ) {
					return ! in_array( $widget['name'], $check_elements );
				});
				$tpae_widgets_list = array_values( $tpae_widgets_final_list );
			}

			$tp_whitelabel = get_option( 'theplus_white_label' );
			$get_plugin_name = 'The Plus Addons Widgets';
			
			if ( ! empty( $tp_whitelabel['tp_plugin_name'] ) ) {
				$get_plugin_name = !empty($tp_whitelabel['tp_plugin_name']) ? $tp_whitelabel['tp_plugin_name'] : 'The Plus Addons Widgets';
			}

			wp_enqueue_style( 'tp-widgets-show', L_THEPLUS_URL . 'modules/widget-promotion/tp-widgets-show/tp-widgets-show.css', array(), L_THEPLUS_VERSION );

				wp_enqueue_script(
					'tp-widgets-show-js',
					L_THEPLUS_URL . 'modules/widget-promotion/tp-widgets-show/tp-widgets-show.js',
					array( 'jquery', 'wp-i18n' ),
					L_THEPLUS_VERSION,
					true
				);
				wp_set_script_translations( 'tp-widgets-show-js', 'tpebl' );

				wp_localize_script(
					'tp-widgets-show-js',
					'tp_widgets_list',
					array(
						'tp_inactive_widgets_list' => $tpae_widgets_list,
						'nonce'                    => wp_create_nonce( 'tpae_widgets_enable' ),
						'ajax_url'                 => admin_url( 'admin-ajax.php' ),
						'is_help_enabled'          => $this->tpae_wl_help_link_enabled(),
						'tpae_category'            => $get_plugin_name,
					)
				);
		}

		public function tpae_handle_enable_widget() {

			check_ajax_referer('tpae_widgets_enable', 'security');

			if ( ! current_user_can('manage_options') ) {
				wp_send_json(
					$this->tpae_set_response(false, 'Invalid Permission.', 'Something went wrong.')
				);
			}
			
			if ( empty($_POST['widget_id']) ) {
				wp_send_json(
					$this->tpae_set_response(false, 'Invalid Widget ID', 'Something went wrong.')
				);
			}

			$widget_id = sanitize_key($_POST['widget_id']);

			$plus_options = get_option('theplus_options', array());

			if ( empty($plus_options['check_elements']) || ! is_array($plus_options['check_elements']) ) {
				$plus_options['check_elements'] = array();
			}

			if ( ! in_array($widget_id, $plus_options['check_elements'], true) ) {

				$plus_options['check_elements'][] = $widget_id;
				update_option('theplus_options', $plus_options);

				wp_send_json(
					$this->tpae_set_response(true, 'Widget activated successfully', '')
				);
			}

			wp_send_json(
				$this->tpae_set_response(false, 'Widget already active', '')
			);
		}

		/**
		 * Set the response data.
		 *
		 * @since 6.4.8
		 *
		 * @param bool   $success     Indicates whether the operation was successful. Default is false.
		 * @param string $message     The main message to include in the response. Default is an empty string.
		 * @param string $description A more detailed description of the message or error. Default is an empty string.
		 */
		public function tpae_set_response( $success = false, $message = '', $description = '') {

			$response = array(
				'success'     => $success,
				'message'     => esc_html( $message ),
				'description' => esc_html( $description ),
			);

			return $response;
		}
	}

	Tp_Widget_Show::instance();
}