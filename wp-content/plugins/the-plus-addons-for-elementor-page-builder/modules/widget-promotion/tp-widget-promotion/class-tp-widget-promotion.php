<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      6.4.1
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\Tp_Widget_Promotion;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Widget_Promotion' ) ) {

	/**
	 * This class used for only load widget notice
	 *
	 * @since 6.4.1
	 */
	class Tp_Widget_Promotion {

		/**
		 * Instance
		 *
		 * @since 6.4.1
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
		 * @since 6.4.1
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
		 * @since 5.2.3
		 * @version 6.4.1
		 */
		public function __construct() {

			add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'wdkit_widgets_elementor_editor_sripts' ) );
		}

		public function wdkit_widgets_elementor_editor_sripts() {

			$tpae_promo_widgets = array(
				array(
					'name'       => 'tp-audio-player-adv',
					'title'      => __( 'Audio Player', 'tpebl' ),
					'icon'       => 'theplus-i-audio-player',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/audio-player/',
				),
				array(
					'name'       => 'tp-advanced-typography-adv',
					'title'      => __( 'Advanced Typography', 'tpebl' ),
					'icon'       => 'theplus-i-advanced-tpography',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/advanced-typography/',
				),
				array(
					'name'       => 'tp-advanced-buttons-adv',
					'title'      => __( 'Advanced Buttons', 'tpebl' ),
					'icon'       => 'theplus-i-advanced-buttons',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/advanced-buttons/',
				),
				array(
					'name'       => 'tp-advertisement-banner-adv',
					'title'      => __( 'Advertisement Banner', 'tpebl' ),
					'icon'       => 'theplus-i-advertisement-banner',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/banner-widget/',
				),
				array(
					'name'       => 'tp-animated-service-boxes-adv',
					'title'      => __( 'Animated Service Boxes', 'tpebl' ),
					'icon'       => 'theplus-i-animated-service-boxes',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/animated-service-boxes/',
				),
				array(
					'name'       => 'tp-before-after-adv',
					'title'      => __( 'Before After', 'tpebl' ),
					'icon'       => 'theplus-i-before-after',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/before-after-slider/',
				),
				array(
					'name'       => 'tp-dynamic-smart-showcase-adv',
					'title'      => __( 'Dynamic Smart Showcase', 'tpebl' ),
					'icon'       => 'theplus-i-dynamic-smart-showcase',
					'demo_url'   => 'https://theplusaddons.com/elementor-listing/#plus-magazine-post-styles/',
				),
				array(
					'name'       => 'tp-wp-bodymovin-adv',
					'title'      => __( 'LottieFiles Animation', 'tpebl' ),
					'icon'       => 'theplus-i-lottiefiles-animation',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/lottiefiles-animation/',
				),
				array(
					'name'       => 'tp-carousel-remote-adv',
					'title'      => __( 'Carousel Remote', 'tpebl' ),
					'icon'       => 'theplus-i-carousel-remote',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/carousel-remote-sync/',
				),
				array(
					'name'       => 'tp-cascading-image-adv',
					'title'      => __( 'Image Cascading', 'tpebl' ),
					'icon'       => 'theplus-i-cascading-image',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/image-cascading/',
				),
				array(
					'name'       => 'tp-chart-adv',
					'title'      => __( 'Chart', 'tpebl' ),
					'icon'       => 'theplus-i-chart',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/advanced-charts/',
				),
				array(
					'name'       => 'tp-circle-menu-adv',
					'title'      => __( 'Circle Menu', 'tpebl' ),
					'icon'       => 'theplus-i-circle-menu',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/circle-menu/',
				),
				array(
					'name'       => 'tp-coupon-code-adv',
					'title'      => __( 'Coupon Code', 'tpebl' ),
					'icon'       => 'theplus-i-coupon-code',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/coupon-code/',
				),
				array(
					'name'       => 'tp-design-tool-adv',
					'title'      => __( 'Design Tool', 'tpebl' ),
					'icon'       => 'theplus-i-design-tool',
					'demo_url'   => 'https://theplusaddons.com/elementor-extras/grid-design-tool/',
				),
				array(
					'name'       => 'tp-draw-svg-adv',
					'title'      => __( 'Draw SVG', 'tpebl' ),
					'icon'       => 'theplus-i-draw-svg',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/draw-animated-svg-icon/',
				),
				array(
					'name'       => 'tp-dynamic-listing-adv',
					'title'      => __( 'Dynamic Listing', 'tpebl' ),
					'icon'       => 'theplus-i-dynamic-listing',
					'demo_url'   => 'https://theplusaddons.com/elementor-listing/dynamic-listing/',
				),
				array(
					'name'       => 'tp-custom-field-adv',
					'title'      => __( 'Custom Field', 'tpebl' ),
					'icon'       => 'theplus-i-tp-custom-field',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/grid-builder/acf-repeater-field/',
				),
				array(
					'name'       => 'tp-dynamic-device-adv',
					'title'      => __( 'Dynamic Device', 'tpebl' ),
					'icon'       => 'theplus-i-dynamic-device',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/dynamic-device-mockups/',
				),
				array(
					'name'       => 'tp-google-map-adv',
					'title'      => __( 'Google Map', 'tpebl' ),
					'icon'       => 'theplus-i-google-map',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/google-map/',
				),
				array(
					'name'       => 'tp-hotspot-adv',
					'title'      => __( 'Hotspot', 'tpebl' ),
					'icon'       => 'theplus-i-hotspot',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/hotspot/',
				),
				array(
					'name'       => 'tp-horizontal-scroll-advance-adv',
					'title'      => __( 'Horizontal Scroll', 'tpebl' ),
					'icon'       => 'theplus-i-horizontal-scroll',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/horizontal-scroll/',
				),
				array(
					'name'       => 'tp-image-factory-adv',
					'title'      => __( 'Creative Image', 'tpebl' ),
					'icon'       => 'theplus-i-creative-image',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/creative-images',
				),
				array(
					'name'       => 'tp-mailchimp-adv',
					'title'      => __( 'MailChimp', 'tpebl' ),
					'icon'       => 'theplus-i-mailchimp',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/mailchimp-form/',
				),
				array(
					'name'       => 'tp-mobile-menu-adv',
					'title'      => __( 'Mobile Menu', 'tpebl' ),
					'icon'       => 'theplus-i-mobile-menu',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/header-builder/mobile-menu/',
				),
				array(
					'name'       => 'tp-morphing-layouts-adv',
					'title'      => __( 'Morphing Layouts', 'tpebl' ),
					'icon'       => 'theplus-i-morphing-layouts',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/morphing-sections',
				),
				array(
					'name'       => 'tp-mouse-cursor-adv',
					'title'      => __( 'Mouse Cursor', 'tpebl' ),
					'icon'       => 'theplus-i-mouse-cursor',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/mouse-cursor/',
				),
				array(
					'name'       => 'tp-navigation-menu-adv',
					'title'      => __( 'Navigation Menu', 'tpebl' ),
					'icon'       => 'theplus-i-navigation-menu',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/header-builder/navigation-menu/',
				),
				array(
					'name'       => 'tp-off-canvas-adv',
					'title'      => __( 'Popup Builder / Off Canvas', 'tpebl' ),
					'icon'       => 'theplus-i-popup-builder-offcanvas',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/popup-builder/',
				),
				array(
					'name'       => 'tp-pre-loader-adv',
					'title'      => __( 'Pre Loader', 'tpebl' ),
					'icon'       => 'theplus-i-pre-loader',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/preloader-and-page-transition/',
				),
				array(
					'name'       => 'tp-pricing-list-adv',
					'title'      => __( 'Pricing List', 'tpebl' ),
					'icon'       => 'theplus-i-pricing-list',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/price-list/',
				),
				array(
					'name'       => 'tp-product-listout-adv',
					'title'      => __( 'Product Listing', 'tpebl' ),
					'icon'       => 'theplus-i-product-listing',
					'demo_url'   => 'https://theplusaddons.com/elementor-listing/woocommerce-product/',
				),
				array(
					'name'       => 'tp-protected-content-adv',
					'title'      => __( 'Protected Content', 'tpebl' ),
					'icon'       => 'theplus-i-protected-content',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/protected-content/',
				),
				array(
					'name'       => 'tp-row-background-adv',
					'title'      => __( 'Row Background', 'tpebl' ),
					'icon'       => 'theplus-i-row-background',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/section-background/',
				),
				array(
					'name'       => 'tp-scroll-sequence-adv',
					'title'      => __( 'Scroll Sequence', 'tpebl' ),
					'icon'       => 'theplus-i-scroll-sequence',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/image-scroll-sequence/',
				),
				array(
					'name'       => 'tp-search-filter-adv',
					'title'      => __( 'Search Filter', 'tpebl' ),
					'icon'       => 'theplus-i-search-filters',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/ajax-filters',
				),
				array(
					'name'       => 'tp-search-bar-adv',
					'title'      => __( 'Search Bar', 'tpebl' ),
					'icon'       => 'theplus-i-search-bar',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/ajax-search-bar',
				),
				array(
					'name'       => 'tp-site-logo-adv',
					'title'      => __( 'Site Logo', 'tpebl' ),
					'icon'       => 'theplus-i-site-logo',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/header-builder/site-logo-for-elementor/',
				),
				array(
					'name'       => 'tp-shape-divider-adv',
					'title'      => __( 'Advanced Separators', 'tpebl' ),
					'icon'       => 'theplus-i-advanced-separators',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/advanced-separators',
				),
				array(
					'name'       => 'tp-social-feed-adv',
					'title'      => __( 'Social Feed', 'tpebl' ),
					'icon'       => 'theplus-i-social-feed',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/combined-filterable-social-feed/',
				),
				array(
					'name'       => 'tp-social-reviews-adv',
					'title'      => __( 'Social Reviews', 'tpebl' ),
					'icon'       => 'theplus-i-social-reviews',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/social-reviews-badges/',
				),
				array(
					'name'       => 'tp-social-sharing-adv',
					'title'      => __( 'Social Sharing', 'tpebl' ),
					'icon'       => 'theplus-i-social-sharing',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/social-sharing-buttons/',
				),
				array(
					'name'       => 'tp-table-content-adv',
					'title'      => __( 'Table of Content', 'tpebl' ),
					'icon'       => 'theplus-i-table-of-content',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/table-of-contents/',
				),
				array(
					'name'       => 'tp-timeline-adv',
					'title'      => __( 'Timeline', 'tpebl' ),
					'icon'       => 'theplus-i-timeline',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/timeline/',
				),
				array(
					'name'       => 'tp-unfold-adv',
					'title'      => __( 'Unfold', 'tpebl' ),
					'icon'       => 'theplus-i-unfold',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/unfold/',
				),
				array(
					'name'       => 'tp-woo-cart-adv',
					'title'      => __( 'Woo Cart', 'tpebl' ),
					'icon'       => 'theplus-i-woo-cart',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/cart-page/',
				),
				array(
					'name'       => 'tp-woo-checkout-adv',
					'title'      => __( 'Woo Checkout', 'tpebl' ),
					'icon'       => 'theplus-i-woo-checkout',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/checkout-page/',
				),
				array(
					'name'       => 'tp-woo-compare-adv',
					'title'      => __( 'Woo Compare', 'tpebl' ),
					'icon'       => 'theplus-i-woo-compare',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/product-compare/',
				),
				array(
					'name'       => 'tp-wp-quickview-adv',
					'title'      => __( 'Woo Quickview', 'tpebl' ),
					'icon'       => 'theplus-i-quick-view',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/quick-view/',
				),
				array(
					'name'       => 'tp-woo-multi-step-adv',
					'title'      => __( 'Woo Multi Step', 'tpebl' ),
					'icon'       => 'theplus-i-woo-multi-step',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/multi-step-checkout/',
				),
				array(
					'name'       => 'tp-woo-myaccount-adv',
					'title'      => __( 'Woo My Account', 'tpebl' ),
					'icon'       => 'theplus-i-woo-my-account',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/my-account-page/',
				),
				array(
					'name'       => 'tp-woo-order-track-adv',
					'title'      => __( 'Woo Order Track', 'tpebl' ),
					'icon'       => 'theplus-i-woo-order-track',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/order-track-page/',
				),
				array(
					'name'       => 'tp-woo-single-basic-adv',
					'title'      => __( 'Woo Single Basic', 'tpebl' ),
					'icon'       => 'theplus-i-woo-single-basic',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'       => 'tp-woo-single-image-adv',
					'title'      => __( 'Woo Single Image', 'tpebl' ),
					'icon'       => 'theplus-i-woo-product-images',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'       => 'tp-woo-single-pricing-adv',
					'title'      => __( 'Woo Single Pricing', 'tpebl' ),
					'icon'       => 'theplus-i-woo-single-pricing',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'       => 'tp-woo-single-tabs-adv',
					'title'      => __( 'Woo Single Tabs', 'tpebl' ),
					'icon'       => 'theplus-i-woo-single-tabs',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'       => 'tp-woo-thank-you-adv',
					'title'      => __( 'Woo Thank You', 'tpebl' ),
					'icon'       => 'theplus-i-woo-thank-you',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/thank-you-page/',
				),
				array(
					'name'       => 'tp-woo-wishlist-adv',
					'title'      => __( 'Woo Wishlist', 'tpebl' ),
					'icon'       => 'theplus-i-woo-wishlist',
					'demo_url'   => 'https://theplusaddons.com/elementor-builder/woocommerce-builder/',
				),
				array(
					'name'       => 'tp-wp-login-register-adv',
					'title'      => __( 'WP Login Register', 'tpebl' ),
					'icon'       => 'theplus-i-wp-login-register',
					'demo_url'   => 'https://theplusaddons.com/elementor-widget/login-form/',
				),
			);

			wp_enqueue_style( 'tp-permotions', L_THEPLUS_URL . 'modules/widget-promotion/tp-widget-promotion/tp-promotion.css', array(), L_THEPLUS_VERSION );

				wp_enqueue_script(
					'tp-widgets-panel-js',
					L_THEPLUS_URL . 'modules/widget-promotion/tp-widget-promotion/tp-widgets-load.js',
					array( 'jquery', 'wp-i18n' ),
					L_THEPLUS_VERSION,
					true
				);
				wp_set_script_translations( 'tp-widgets-panel-js', 'tpebl' );

				wp_localize_script(
					'tp-widgets-panel-js',
					'tpPanelSettings',
					array(
						'tp_pro_widgets' => $tpae_promo_widgets,
						'nonce'          => wp_create_nonce( 'tp_wdkit_preview_popup' ),
						'ajax_url'       => admin_url( 'admin-ajax.php' ),
					)
				);
		}
	}

	Tp_Widget_Promotion::instance();
}
