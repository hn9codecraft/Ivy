<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since   1.0.0
 *
 * @package Theplus
 */

namespace TheplusAddons;

use Elementor\Utils;
use Elementor\Core\Settings\Manager as SettingsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * It Is load all widget and dashbord
 *
 * @since 1.0.0
 */
final class L_Theplus_Element_Load {

	/**
	 * Core singleton class
	 *
	 * @var _instance pattern realization
	 */
	private static $instance;

	/**
	 * Get Elementor Plugin Instance
	 *
	 * @return \Elementor\Theplus_Element_Loader
	 */
	public static function elementor() {
		return \Elementor\Plugin::$instance;
	}

	/**
	 * Get Singleton Instance
	 *
	 * This static method ensures that only one instance of the class is created
	 * and provides a way to access that instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Theplus_Element_Loader The single instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * ThePlus_Load Class
	 *
	 * This class is responsible for handling the loading of ThePlus Addons.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'in_plugin_update_message-' . L_THEPLUS_PBNAME, array( $this, 'tp_f_in_plugin_update_message' ), 10, 2 );

		register_activation_hook( L_THEPLUS_FILE, array( __CLASS__, 'tp_f_activation' ) );
		register_deactivation_hook( L_THEPLUS_FILE, array( __CLASS__, 'tp_f_deactivation' ) );

		add_action( 'init', array( $this, 'tp_i18n' ) );
		add_action( 'plugins_loaded', array( $this, 'tp_f_plugin_loaded' ) );
	}

	/**
	 * When Show Update Notice that time this function is used
	 *
	 * @since 5.6.6
	 *
	 * @param array  $data     Array of plugin update data.
	 * @param object $response Object containing response data from the update check.
	 */
	public function tp_f_in_plugin_update_message( $data, $response ) {

		if ( isset( $data['upgrade_notice'] ) && ! empty( $data['upgrade_notice'] ) ) {
			printf( '<div class="update-message">%s</div>', wpautop( $data['upgrade_notice'] ) );
		}
	}

	/**
	 * Elementor Plugin Not install than show this Notice
	 *
	 * @since 5.6.6
	 */
	public function tp_f_elementor_load_notice() {
		$plugin = 'elementor/elementor.php';

		$installed_plugins = get_plugins();

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$tp_ele_btn_txt = esc_html__( 'Install Now', 'tpebl' );

		if ( isset( $installed_plugins[ $plugin ] ) ) {
			$tp_ele_btn_txt = esc_html__( 'Activate Now', 'tpebl' );
		}else{
			$tp_ele_btn_txt = esc_html__( 'Install Now', 'tpebl' );
		}

		echo '<div class="notice notice-error tpae-notice-show tpae-install-elementor" style="border-left-color: #6660EF;">
			<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

				<div style="margin: 0; color: #000;">
					<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Elementor Plugin Required', 'tpebl' ) . '</h3>
					<p>' . esc_html__( 'The Plus Addons for Elementor works as an extension of Elementor. Please install and activate Elementor to unlock all 120+ widgets and extensions. Without Elementor, the addon cannot function.', 'tpebl' ) . '</p>';
						echo '<div class="tp-tpae-button" style="margin-top: 10px;">
								<div style="background: #6660EF; color: #fff; position: relative;" class="button tpae-ele-btn" data-slug="elementor/elementor.php" data-name="elementor">
									'. esc_html( $tp_ele_btn_txt ) .'
								</div>
							</div>';
				echo '</div>
			</div>
		</div>';
	}

	/**
	 * Plugin Activation.
	 *
	 * @return void
	 */
	public static function tp_f_activation() {}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function tp_f_deactivation() {}

	/**
	 * After Load Plugin All set than call this function
	 *
	 * @since 5.6.6
	 */
	public function tp_f_plugin_loaded() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'tp_f_elementor_load_notice' ) );
			add_action('wp_ajax_tpae_elementor_ajax_call', array($this, 'tpae_elementor_ajax_call'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_css_js'));
			return;
		}

		// Register class automatically.
		$this->tp_manage_files();

		$this->includes();

		// Finally hooked up all things.
		$this->hooks();

		if ( ! defined( 'THEPLUS_VERSION' ) ) {
			L_Theplus_Elements_Integration()->init();
		}

		$this->include_widgets();
	}

	public function tpae_elementor_ajax_call() {

		check_ajax_referer("tpae-addons", "nonce");

		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error([
				'message' => __('Invalid permission. Only administrators can perform this action.', 'tpebl')
			], 403);
		}

		$tp_slug             = 'elementor';
		$tp_plugin_basename  = 'elementor/elementor.php';

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		$installed_plugins = get_plugins();

		$response = wp_remote_post(
			'http://api.wordpress.org/plugins/info/1.0/',
			[
				'body' => [
					'action'  => 'plugin_information',
					'request' => serialize((object) [
						'slug'   => $tp_slug,
						'fields' => ['version' => false],
					]),
				],
			]
		);

		$plugin_info = unserialize(wp_remote_retrieve_body($response));

		if (!$plugin_info) {
			wp_send_json_error([
				'message' => __('Failed to retrieve plugin information.', 'tpebl')
			]);
		}

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader($skin);


		if (!isset($installed_plugins[$tp_plugin_basename])) {

			$installed = $upgrader->install($plugin_info->download_link);

			if (!$installed) {
				wp_send_json_error([
					'message' => __('Failed to install Elementor plugin.', 'tpebl')
				]);
			}

			$activation = activate_plugin($tp_plugin_basename);

			if (is_wp_error($activation)) {
				wp_send_json_error([
					'message' => __('Plugin installed but activation failed.', 'tpebl')
				]);
			}

			wp_send_json_success([
				'message' => __('Elementor installed & activated successfully!', 'tpebl'),
				'installed' => true,
				'activated' => true,
			]);
		}

		$activation = activate_plugin($tp_plugin_basename);

		if (is_wp_error($activation)) {
			wp_send_json_error([
				'message' => __('Elementor activation failed.', 'tpebl')
			]);
		}

		wp_send_json_success([
			'message' => __('Elementor activated successfully!', 'tpebl'),
			'installed' => true,
			'activated' => true,
		]);
	}


	/*
	* Admin Enqueue Scripts
	* @sinc 6.4.3
	**/
	public function admin_enqueue_css_js( $hook ){
		
		wp_enqueue_script( 'tpae-admins-js', L_THEPLUS_ASSETS_URL . 'js/admin/tp-elementor-install.js',array() , L_THEPLUS_VERSION, true );
		wp_localize_script(
			'tpae-admins-js',
			'tpae_admins_js',
			array(
				'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
				'tpae_nonce' => wp_create_nonce("tpae-addons"),
			)
		);

	}

	/**
	 * Load Text Domain.
	 * Text Domain : tpebl
	 *
	 * @since 5.6.6
	 */
	public function tp_i18n() {
		load_plugin_textdomain( 'tpebl', false, L_THEPLUS_PNAME . '/languages' );
	}

	/**
	 * Include and manage files related to notices.
	 *
	 * This function includes the class responsible for managing notices in ThePlus plugin.
	 * It includes the file class-tp-notices-main.php from the specified path.
	 *
	 * @since 5.1.18
	 */
	public function tp_manage_files() {

		require_once L_THEPLUS_PATH . 'includes/admin/tpae_hooks/class-tpae-main-hooks.php';

		include L_THEPLUS_PATH . 'includes/notices/class-tp-notices-main.php';
		include L_THEPLUS_PATH . 'includes/user-experience/class-tp-user-experience-main.php';
		include L_THEPLUS_PATH . 'includes/admin/dashboard/class-tpae-dashboard-main.php';

		include L_THEPLUS_PATH . 'includes/smart-loop-builder/class-tpae-loop-builder.php';
		include L_THEPLUS_PATH . 'includes/preset/class-wdkit-preset.php';
		include L_THEPLUS_PATH . 'modules/controls/theme-builder/tpae-class-nxt-download.php';

		// Front or Elementor Editor
		require_once L_THEPLUS_PATH . 'includes/tp-lazy-function.php';
	}

	/**
	 * Hooks Setup for ThePlus Load Class
	 *
	 * This private method sets up hooks and actions needed for the functionality of the ThePlus Load class.
	 *
	 * @since 5.1.18
	 * @version 6.4.1
	 */
	private function hooks() {
		$theplus_options = get_option( 'theplus_options' );

		$plus_extras = l_theplus_get_option( 'general', 'extras_elements' );
		$elements    = l_theplus_get_option( 'general', 'check_elements' );

		if ( ( isset( $plus_extras ) && empty( $plus_extras ) && empty( $theplus_options ) ) || ( ! empty( $plus_extras ) && in_array( 'plus_display_rules', $plus_extras ) ) ) {
			add_action( 'wp_head', array( $this, 'print_style' ) );
		}

		// add_action( 'elementor/init', array( $this, 'add_elementor_category' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_category' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'theplus_editor_styles' ) );
		
		if ( defined( 'THEPLUS_VERSION' ) && ! empty( $elements ) && is_array( $elements ) && in_array( 'tp_social_feed', $elements ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'theplus_frontend_styles' ) );
		}

		if(defined('THEPLUS_VERSION') && !empty($plus_extras) && is_array($plus_extras) && in_array('plus_adv_scroll_interactions', $plus_extras)) {
			add_action( 'wp_enqueue_scripts', array( $this, 'theplus_frontend_styles' ) );
		}

		add_filter( 'upload_mimes', array( $this, 'theplus_mime_types' ) );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'theplus_sanitize_svg_upload' ) );

		// Include some backend files.
		add_action( 'admin_enqueue_scripts', array( $this, 'theplus_elementor_admin_css' ) );

		$get_notification = get_option( 'tpae_menu_notification' );

		if ( $get_notification !== TPAE_MENU_NOTIFICETIONS ) {
			add_action( 'admin_footer', array( $this, 'tpae_add_notificetion' ) );
		}

		add_option( 'tpae_menu_notification', '3' );
		add_option( 'tpae_whats_new_notification', '3' );
	}

	/**
	 * Include Module Manager and Admin PHP Files
	 *
	 * This private method is called during the class instantiation and loads
	 * the required module manager and admin PHP files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		require_once L_THEPLUS_INCLUDES_URL . 'plus_addon.php';
		require_once L_THEPLUS_PATH . 'modules/widgets-feature/class-tp-widgets-feature-main.php';

		add_action( 'elementor/init', function() {
			require L_THEPLUS_PATH . 'modules/extensions/class-tpae-extensions-main.php';
		});
		// require L_THEPLUS_PATH . 'modules/theplus-core-cp.php';

		if ( ! defined( 'THEPLUS_VERSION' ) ) {
			require L_THEPLUS_PATH . 'modules/theplus-integration.php';
		}
		include L_THEPLUS_PATH . 'modules/widget-promotion/tp-widget-promotion-main.php';

		require L_THEPLUS_PATH . 'modules/query-control/module.php';

		require_once L_THEPLUS_PATH . 'modules/helper-function.php';
	}

	/**
	 * Include Widget Files
	 *
	 * This method is responsible for including the required files related to widgets.
	 * It ensures that the necessary files for widgets are loaded.
	 *
	 * @since 1.0.0
	 */
	public function include_widgets() {
		require_once L_THEPLUS_PATH . 'modules/theplus-include-widgets.php';

		if ( defined( 'THEPLUS_VERSION' ) ) {
			require L_THEPLUS_PATH . 'includes/admin/white_label/class-tpae-white-label.php';
		}
	}

	/**
	 * Theplus_Element_Loader Class
	 *
	 * This class manages the inclusion of styles for Theplus Elementor Editor.
	 *
	 * @since 1.0.0
	 */
	public function theplus_editor_styles() {

		wp_enqueue_style( 'theplus-ele-admin', L_THEPLUS_ASSETS_URL . 'css/admin/theplus-ele-admin.css', array(), L_THEPLUS_VERSION, false );
		wp_enqueue_style( 'theplus-icons-library', L_THEPLUS_ASSETS_URL . 'fonts/style.css', array(), L_THEPLUS_VERSION, false );

		$white_label_options = get_option( 'theplus_white_label', array() );

		if ( is_array( $white_label_options ) ) {
			$wl_logo = ! empty( $white_label_options['tp_plus_logo'] ) ? $white_label_options['tp_plus_logo'] : '';
			$wl_name = ! empty( $white_label_options['tp_plugin_name'] ) ? $white_label_options['tp_plugin_name'] : '';
			$wl_name = ! empty( $wl_name ) ? $wl_name : ( ! empty( $white_label_options['l_tp_plugin_name'] ) ? $white_label_options['l_tp_plugin_name'] : '' );

			if ( ! empty( $wl_logo ) || ! empty( $wl_name ) ) {
				$inline_css = '';

				if ( ! empty( $wl_logo ) ) {
					$wl_logo_escaped = esc_url( $wl_logo );
					$inline_css .= '.elementor-element .icon i.tpae-editor-logo:after { background-image: url("' . $wl_logo_escaped . '"); background-size: contain; background-repeat: no-repeat; }';
				} else {
					$inline_css .= '.elementor-element .icon i.tpae-editor-logo:after { background-image: none; }';
				}

				wp_add_inline_style( 'theplus-ele-admin', $inline_css );
			}
		}

		$ui_theme = SettingsManager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );

		if ( ! empty( $ui_theme ) && 'dark' === $ui_theme ) {
			wp_enqueue_style( 'theplus-ele-admin-dark', L_THEPLUS_ASSETS_URL . 'css/admin/theplus-ele-admin-dark.css', array(), L_THEPLUS_VERSION, false );
		}
	}

	/**
	 * Load Icon library on the frontend side
	 *
	 * @since 6.4.2
	 */
	public function theplus_frontend_styles() {
		wp_enqueue_style( 'theplus-icons-library', L_THEPLUS_ASSETS_URL . 'fonts/style.css', array(), L_THEPLUS_VERSION, false );
	}

	/**
	 * Enqueue Theplus Elementor Admin CSS and JavaScript
	 *
	 * This method enqueues the necessary scripts and styles for Theplus Elementor Admin.
	 * It includes jQuery UI Dialog, Theplus Elementor Admin CSS, and a custom admin JavaScript file.
	 * Additionally, it sets up inline JavaScript variables for AJAX functionality.
	 *
	 * @since 6.1.0
	 */
	public function theplus_elementor_admin_css( $hook ) {

		wp_enqueue_style( 'theplus-ele-admin', L_THEPLUS_ASSETS_URL . 'css/admin/theplus-ele-admin.css', array(), L_THEPLUS_VERSION, false );
		wp_enqueue_script( 'theplus-admin-js', L_THEPLUS_ASSETS_URL . 'js/admin/theplus-admin.js', array(), L_THEPLUS_VERSION, false );

		$script_handle = 'theplus-admin-js';

		$js_inline = 'var theplus_ajax_url = "' . esc_url(admin_url("admin-ajax.php")) . '";
        var theplus_ajax_post_url = "' . esc_url(admin_url("admin-post.php")) . '";
        var theplus_nonce = "' . esc_js(wp_create_nonce("theplus-addons")) . '";';

		wp_add_inline_script( $script_handle, $js_inline );
	}

	/**
	 * Modify Allowed MIME Types for File Uploads
	 *
	 * This function is a WordPress filter used to extend the list of allowed MIME types for file uploads.
	 * It adds support for SVG (Scalable Vector Graphics) and SVGZ (compressed SVG) file types.
	 *
	 * @param array $mimes Associative array of allowed MIME types.
	 * @return array Modified array of allowed MIME types.
	 *
	 * @since 1.0.0
	 */
	public function theplus_mime_types( $mimes ) {
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';

		return $mimes;

	}

	/**
	 * Sanitize uploaded SVGs
	 * 
	 * @since 6.3.16
	 */
	public function theplus_sanitize_svg_upload( $file ) {

		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( $ext === 'svg' ) {
			$contents = file_get_contents( $file['tmp_name'] );

			$bad_patterns = [
				'/<\s*script/i',
				'/\son[a-z]+\s*=/i',
				'/<\s*foreignObject/i',
				'/<\s*(iframe|embed|object)/i',
				'/javascript:/i',
				'/data:/i',
			];

			foreach ( $bad_patterns as $re ) {
				if ( preg_match( $re, $contents ) ) {
					$file['error'] = __( 'SVG contains unsafe content', 'tpebl' );

					return $file;
				}
			}
		}

		return $file;
	}

	/**
	 * Print style.
	 *
	 * Adds custom CSS to the HEAD html tag. The CSS that emphasise the maintenance
	 * mode with red colors.
	 *
	 * Fired by `admin_head` and `wp_head` filters.
	 *
	 * @since 2.1.0
	 */
	public function print_style() {
		?>
		<style>*:not(.elementor-editor-active) .plus-conditions--hidden {display: none;}</style> 
		<?php
	}

	/**
	 * Add Elementor Category for PlusEssential Elements
	 *
	 * This method is responsible for adding a custom category to the Elementor Page Builder
	 * for PlusEssential elements.
	 *
	 * @since 6.0.5
	 */
	public function add_elementor_category() {

		$elementor = \Elementor\Plugin::$instance;

		$post_id = get_the_ID();
		$template_type = '';

		if ( $post_id ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$template_type = $document->get_name();
				$source_type   = get_post_meta( $post_id, '_elementor_source', true );
			} else {
				$template_type = get_post_meta( $post_id, '_elementor_template_type', true );
			}
		}

		$plus_categories = array(
			'plus-essential'   => array( 'title' => esc_html__( 'Plus Essential', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-advanced'    => array( 'title' => esc_html__( 'Plus Advanced', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-creative'    => array( 'title' => esc_html__( 'Plus Creative', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-listing'     => array( 'title' => esc_html__( 'Plus Listing', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-social'      => array( 'title' => esc_html__( 'Plus Social', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-forms'       => array( 'title' => esc_html__( 'Plus Forms', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-woo-builder' => array( 'title' => esc_html__( 'Plus WooCommerce', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-depreciated' => array( 'title' => esc_html__( 'Plus Depreciated', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
		);

		if ( $post_id ) {
			$post_type = get_post_type( $post_id );

			/** For check header of the nexter extension */
			if ( in_array( $post_type, [ 'nxt_builder', 'nxt_template' ], true ) ) {
				$template_type = get_post_meta( $post_id, 'template_type', true );
			}
		}

		// if ( 'loop-item' === $template_type ) {
		// 	$template_type = $source_type;
		// }

		if ( in_array( $template_type, [ 'header' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();
        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-header'] = [
						'title' => esc_html__( 'Plus Header', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'archive', 'archives' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();
        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-archive'] = [
						'title' => esc_html__( 'Plus Archive', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'product-archive' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();

        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-product-archive'] = [
						'title' => esc_html__( 'Plus Product Archive', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'product', 'singular' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();

        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-product'] = [
						'title' => esc_html__( 'Plus Product', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'single-page', 'single-post', 'singular' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();

        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-single'] = [
						'title' => esc_html__( 'Plus Single', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

        foreach ( $plus_categories as $index => $plus_widgets ) {
            $elementor->elements_manager->add_category(
                $index,
                array(
                    'title' => esc_html__( $plus_widgets['title'], 'tpebl' ),
                    'icon'  => $plus_widgets['icon'],
                ),
                1
            );
        }

	}

	/**
	 * The Plus Addon Menu Notificetions icon
	 *
	 * @since 6.4.1
	 */
	public function tpae_add_notificetion() {

		?>
			<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', function() {
					var menuItem = document.querySelector('#toplevel_page_theplus_welcome_page');
					if (menuItem) {
						menuItem.classList.add('tpae-admin-notice-active');
					}
				});
			</script>
		<?php
	}
}

L_Theplus_Element_Load::instance();