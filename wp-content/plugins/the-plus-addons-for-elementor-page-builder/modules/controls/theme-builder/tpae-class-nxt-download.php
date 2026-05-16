<?php
/**
 * It is Main File to load all Notice, Upgrade Menu and all
 *
 * @link       https://posimyth.com/
 * @since      6.5.6
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

if ( ! class_exists( 'Tp_Nxt_Download' ) ) {

	/**
	 * This class used for Wdesign-kit releted
	 *
	 * @since 6.5.6
	 */
	class Tp_Nxt_Download {

		/**
		 * Instance
		 *
		 * @since 6.5.6
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.5.6
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
		 * @since 6.5.6
		 */
		public function __construct() {

			if ( class_exists( '\Elementor\Plugin' ) ) {
				add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'tpae_asets_editor_sripts' ] );
				add_action( 'wp_ajax_tp_install_promotions_plugin', array( $this, 'tp_install_promotions_plugin' ) );
				add_action( 'wp_ajax_tpae_create_page', array( $this, 'tpae_create_page' ) );
			}
		}

		public function tpae_asets_editor_sripts() {

			wp_enqueue_script( 'theplus-editor-theme-builder', L_THEPLUS_URL . 'modules/controls/theme-builder/theplus-editor-theme-builder.js', array(), L_THEPLUS_VERSION, false );
			wp_localize_script(
				'theplus-editor-theme-builder',
				'theplus_editor_theme_builder',
				array(
					'nonce'    => wp_create_nonce( 'tp_nxt_install' ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}

		public function tp_install_promotions_plugin(){

			check_ajax_referer( 'tp_nxt_install', 'security' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				$response = $this->tpae_response('Invalid Permission.', 'Something went wrong.',false );

				wp_send_json( $response );
				wp_die();
			}

			$plugin_type = isset( $_POST['plugin_type'] ) ? sanitize_text_field( $_POST['plugin_type'] ) : 'elementor_library';

			if('nexter_ext' === $plugin_type){
				$type = array(
					'tp_slug' => 'nexter-extension',
					'tp_plugin_basename' => 'nexter-extension/nexter-extension.php'
				);
			}elseif ('tp_woo' === $plugin_type){
				$type = array(
					'tp_slug' => 'woocommerce',
					'tp_plugin_basename' => 'woocommerce/woocommerce.php'
				);
			}

			$tp_response = apply_filters( 'tpae_plugin_install', $type );

			$response = $this->tpae_response( 'Success', '', true, $tp_response );

			wp_send_json( $response );
			
		}


		/**
		 * Create Page for Header
		 * 
		 * @since 6.4.1
		 */
		public function tpae_create_page() {

			check_ajax_referer( 'tp_nxt_install', 'security' );

			$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : 'elementor_library';
			$page_type = isset( $_POST['page_type'] ) ? sanitize_text_field( $_POST['page_type'] ) : 'tp_header';
			$page_name = isset( $_POST['page_name'] ) ? sanitize_text_field( $_POST['page_name'] ) : 'theplus-addon';

			$allowed_post_types = array( 'elementor_library', 'nxt_builder' );
			if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
				$response = $this->tpae_response( 'Invalid Post Type', 'The selected post type is not allowed.', false );

				wp_send_json( $response );
				wp_die();
			}

			$post_type_object = get_post_type_object( $post_type );
			if ( ! $post_type_object ) {
				$response = $this->tpae_response( 'Post Type Not Found', 'The requested post type does not exist.', false );

				wp_send_json( $response );
				wp_die();
			}

			if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
				$response = $this->tpae_response( 'Permission Denied', 'You do not have permission to create this content.', false );

				wp_send_json( $response );
				wp_die();
			}

			$post_id = wp_insert_post( 
				array(
					'post_type'   => $post_type,
					'post_title'  => $page_name,
					'post_status' => 'draft',
				) 
			);

			if ( is_wp_error( $post_id ) ) {
				$response = $this->tpae_response( 'Creation Failed', 'Failed to create the post. Please try again.', false );

				wp_send_json( $response );
				wp_die();
			}

			if ( $post_type === 'nxt_builder' ) {
				update_post_meta( $post_id, 'template_type', $page_type );
				update_post_meta( $post_id, 'nxt-hooks-layout-sections', $page_type );
			} elseif ( $post_type === 'elementor_library' ) {
				update_post_meta( $post_id, '_elementor_template_type', $page_type );
			}

			$elementor_edit_url = admin_url( 'post.php?post=' . $post_id . '&action=elementor' );

			$response = $this->tpae_response(
				'Page Created Successfully', 'Your template has been created successfully.',
				true,
				array(
					'post_id'  => $post_id,
					'edit_url' => $elementor_edit_url,
				)
			);

			wp_send_json( $response );
			wp_die();
		}


		/**
		 * Response
		 *
		 * @param string  $message pass message.
		 * @param string  $description pass message.
		 * @param boolean $success pass message.
		 * @param string  $data pass message.
		 *
		 * @since 6.5.6
		 */
		public function tpae_response( $message = '', $description = '', $success = false, $data = '' ) {
			return array(
				'message'     => $message,
				'description' => $description,
				'success'     => $success,
				'data'        => $data,
			);
		}
	}

	Tp_Nxt_Download::instance();
}