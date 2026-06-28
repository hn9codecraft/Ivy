<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.4.7' );
define( 'EHP_THEME_SLUG', 'hello-elementor' );

define( 'HELLO_THEME_PATH', get_template_directory() );
define( 'HELLO_THEME_URL', get_template_directory_uri() );
define( 'HELLO_THEME_ASSETS_PATH', HELLO_THEME_PATH . '/assets/' );
define( 'HELLO_THEME_ASSETS_URL', HELLO_THEME_URL . '/assets/' );
define( 'HELLO_THEME_SCRIPTS_PATH', HELLO_THEME_ASSETS_PATH . 'js/' );
define( 'HELLO_THEME_SCRIPTS_URL', HELLO_THEME_ASSETS_URL . 'js/' );
define( 'HELLO_THEME_STYLE_PATH', HELLO_THEME_ASSETS_PATH . 'css/' );
define( 'HELLO_THEME_STYLE_URL', HELLO_THEME_ASSETS_URL . 'css/' );
define( 'HELLO_THEME_IMAGES_PATH', HELLO_THEME_ASSETS_PATH . 'images/' );
define( 'HELLO_THEME_IMAGES_URL', HELLO_THEME_ASSETS_URL . 'images/' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );

			/*
			 * Editor Styles
			 */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'assets/css/editor-styles.css' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				HELLO_THEME_STYLE_URL . 'reset.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				HELLO_THEME_STYLE_URL . 'theme.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				HELLO_THEME_STYLE_URL . 'header-footer.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

function custom_theme_styles() {

    wp_enqueue_style(
        'custom-gravity-css',
        HELLO_THEME_STYLE_URL . 'custom-gravity.css',
        array(),
        HELLO_ELEMENTOR_VERSION
    );

    wp_enqueue_style(
        'custom-course-css',
        HELLO_THEME_STYLE_URL . 'course.css',
        array(),
        HELLO_ELEMENTOR_VERSION
    );

	 wp_enqueue_style(
        'package-css',
        HELLO_THEME_STYLE_URL . 'package.css',
        array(),
        HELLO_ELEMENTOR_VERSION
    );

    wp_enqueue_style(
        'profile-css',
        HELLO_THEME_STYLE_URL . 'profile.css',
        array(),
        HELLO_ELEMENTOR_VERSION
    );

    wp_enqueue_style(
        'sidebar-css',
        HELLO_THEME_STYLE_URL . 'sidebar.css',
        array(),
        HELLO_ELEMENTOR_VERSION
    );

    wp_enqueue_style(
        'course-list-css',
        HELLO_THEME_STYLE_URL . 'course-list.css',
        array(),
        HELLO_ELEMENTOR_VERSION
    );

    // Swiper library (required by custom.js slider)
    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        '11'
    );

    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        array(),
        '11',
        true
    );

    // Theme custom JS (depends on Swiper, loaded in footer)
    wp_enqueue_script(
        'custom-javascript',
        HELLO_THEME_SCRIPTS_URL . 'custom.js',
        array('swiper-js'),
        HELLO_ELEMENTOR_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'custom_theme_styles');

/**
 * Profile / account section navigation.
 *
 * Single source of truth for the four account pages (Profile, Payment method,
 * Invoice, Package detail). Used by both the profile tab bar
 * (template-parts/profile/profile-tabs.php) and the dashboard sidebar account
 * links so labels, slugs and icons only ever live in one place.
 *
 * @return array<string,array> Keyed by section id.
 */
function ivy_profile_nav_items() {
	return array(
		'profile' => array( 'label' => __( 'Profile', 'hello-elementor' ),        'slug' => 'user-profile',   'icon' => 'profile' ),
		'payment' => array( 'label' => __( 'Payment method', 'hello-elementor' ), 'slug' => 'payment-method', 'icon' => 'payment' ),
		'invoice' => array( 'label' => __( 'Invoice', 'hello-elementor' ),        'slug' => 'invoice',        'icon' => 'invoice' ),
		'package' => array( 'label' => __( 'Package detail', 'hello-elementor' ), 'slug' => 'package-detail', 'icon' => 'package' ),
	);
}

/**
 * Front-end URL for a profile section page slug.
 *
 * @param string $slug Page slug.
 * @return string
 */
function ivy_profile_url( $slug ) {
	return home_url( '/' . ltrim( $slug, '/' ) . '/' );
}

/**
 * Profile page templates now live in template-parts/profile/. WordPress only
 * auto-detects page templates one folder deep, so register them explicitly.
 */
add_filter( 'theme_page_templates', 'ivy_register_profile_templates' );
function ivy_register_profile_templates( $templates ) {
	$templates['template-parts/profile/user-profile.php']   = __( 'User Profile', 'hello-elementor' );
	$templates['template-parts/profile/payment-method.php'] = __( 'Payment Method', 'hello-elementor' );
	$templates['template-parts/profile/invoice.php']        = __( 'Invoice', 'hello-elementor' );
	$templates['template-parts/profile/package-detail.php'] = __( 'Package Detail', 'hello-elementor' );
	return $templates;
}

/**
 * Safety net: load the moved profile templates by basename, so a page keeps
 * working whether its stored template meta points at the old (template-parts/)
 * or new (template-parts/profile/) path.
 */
add_filter( 'template_include', 'ivy_include_profile_templates' );
function ivy_include_profile_templates( $template ) {
	if ( ! is_page() ) {
		return $template;
	}

	$assigned = get_page_template_slug( get_queried_object_id() );
	if ( ! $assigned ) {
		return $template;
	}

	$map = array(
		'user-profile.php'   => 'template-parts/profile/user-profile.php',
		'payment-method.php' => 'template-parts/profile/payment-method.php',
		'invoice.php'        => 'template-parts/profile/invoice.php',
		'package-detail.php' => 'template-parts/profile/package-detail.php',
	);

	$base = basename( $assigned );
	if ( isset( $map[ $base ] ) ) {
		$located = get_theme_file_path( $map[ $base ] );
		if ( file_exists( $located ) ) {
			return $located;
		}
	}

	return $template;
}

/**
 * One-time migration: repoint pages that still reference the old (root)
 * template paths to the new template-parts/profile/ paths, so the wp-admin
 * Page Attributes dropdown also shows the correct template.
 */
add_action( 'admin_init', 'ivy_migrate_profile_templates' );
function ivy_migrate_profile_templates() {
	if ( get_option( 'ivy_profile_templates_migrated' ) ) {
		return;
	}

	$map = array(
		'template-parts/user-profile.php'   => 'template-parts/profile/user-profile.php',
		'template-parts/payment-method.php' => 'template-parts/profile/payment-method.php',
		'template-parts/invoice.php'        => 'template-parts/profile/invoice.php',
		'template-parts/package-detail.php' => 'template-parts/profile/package-detail.php',
	);

	foreach ( $map as $old => $new ) {
		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $old,
			'fields'         => 'ids',
		) );
		foreach ( $pages as $pid ) {
			update_post_meta( $pid, '_wp_page_template', $new );
		}
	}

	update_option( 'ivy_profile_templates_migrated', 1 );
}

/**
 * [es_course_listing] — course list page.
 * Markup lives in template-parts/course-listing.php, styles in
 * assets/css/course-list.css. Moved here from the eduschedule plugin.
 */
function ivy_course_listing_shortcode( $atts = array() ) {
    $atts = shortcode_atts( array(
        'title'          => 'All Certification Preparation Courses',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'category'       => '',
        'demo_label'     => 'Demo',
        'demo_url'       => '',
    ), $atts, 'es_course_listing' );

    ob_start();
    include get_template_directory() . '/template-parts/course-listing.php';
    return ob_get_clean();
}
add_shortcode( 'es_course_listing', 'ivy_course_listing_shortcode' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

require HELLO_THEME_PATH . '/theme.php';

HelloTheme\Theme::instance();

function create_course_post_type() {
    register_post_type('course', [
        'labels' => [
            'name' => 'Courses',
            'singular_name' => 'Course',
            'add_new' => 'Add New Course',
            'add_new_item' => 'Add New Course',
            'edit_item' => 'Edit Course',
            'new_item' => 'New Course',
            'view_item' => 'View Course',
            'search_items' => 'Search Courses',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'courses'],
        'show_in_rest' => true, // Gutenberg support
    ]);
}
add_action('init', 'create_course_post_type');

function create_course_taxonomy() {
    register_taxonomy('course_category', 'course', [
        'labels' => [
            'name' => 'Course Categories',
            'singular_name' => 'Course Category',
            'search_items' => 'Search Categories',
            'all_items' => 'All Categories',
            'edit_item' => 'Edit Category',
            'add_new_item' => 'Add New Category',
        ],
        'hierarchical' => true, // like categories
        'public' => true,
        'rewrite' => ['slug' => 'course-category'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'create_course_taxonomy');


/* Disable comments for all post types */
function vworks_disable_comments_all_post_types() {
    $post_types = get_post_types();

    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}
add_action('admin_init', 'vworks_disable_comments_all_post_types');


/* Close comments on frontend */
function vworks_close_comments_status() {
    return false;
}
add_filter('comments_open', 'vworks_close_comments_status', 20, 2);
add_filter('pings_open', 'vworks_close_comments_status', 20, 2);


/* Hide existing comments */
function vworks_hide_existing_comments($comments) {
    return array();
}
add_filter('comments_array', 'vworks_hide_existing_comments', 10, 2);


/* Remove Comments menu from admin */
function vworks_remove_comments_admin_menu() {
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'vworks_remove_comments_admin_menu');


/* Remove Comments from admin bar */
function vworks_remove_comments_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}
add_action('wp_before_admin_bar_render', 'vworks_remove_comments_admin_bar');


/* Redirect comments admin page */
function vworks_redirect_comments_page() {
    global $pagenow;

    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'vworks_redirect_comments_page');
