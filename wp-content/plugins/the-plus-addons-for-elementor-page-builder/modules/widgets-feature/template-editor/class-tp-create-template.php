<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://posimyth.com/
 * @since      6.4.2
 *
 * @package    ThePlus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tpae_Create_Template' ) ) {

	/**
	 * Tpae_Create_Template
	 *
	 * @since 6.4.2
	 */
	class Tpae_Create_Template {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Define the core functionality of the plugin.
		 *
		 * @since 6.4.2
		 */
		public function __construct() {
		    add_action( 'elementor/editor/before_enqueue_scripts', array($this, 'enqueue_editor_scripts') );

            add_action( 'wp_ajax_tpae_template_create', array( $this, 'tpae_template_create' ) );
			add_action( 'wp_ajax_nopriv_tpae_template_create', array( $this, 'tpae_template_create' ) );

			add_action( 'wp_ajax_change_current_template_title', array( $this, 'change_current_template_title' ) );
			add_action( 'wp_ajax_nopriv_change_current_template_title', array( $this, 'change_current_template_title' ) );            
		}

		/**
		 * Enqueues the custom CSS/Js editor JavaScript for The Plus Addons.
		 *
		 * @since 6.4.2
		 */
        public function enqueue_editor_scripts() {
            $white_label_options = get_option( 'theplus_white_label', array() );
            $wl_enabled          = false;
            $white_label_data    = array(
                'wl_enabled' => false,
                'wl_name'    => '',
                'wl_logo'    => '',
            );

            if ( is_array( $white_label_options ) ) {
                $wl_name = ! empty( $white_label_options['tp_plugin_name'] ) ? $white_label_options['tp_plugin_name'] : '';
                $wl_name = ! empty( $wl_name ) ? $wl_name : ( ! empty( $white_label_options['l_tp_plugin_name'] ) ? $white_label_options['l_tp_plugin_name'] : '' );
                $wl_logo = ! empty( $white_label_options['tp_plus_logo'] ) ? $white_label_options['tp_plus_logo'] : '';

                $wl_enabled = ! empty( $wl_name ) || ! empty( $wl_logo );

                $white_label_data['wl_enabled'] = $wl_enabled;
                $white_label_data['wl_name']    = $wl_name;
                $white_label_data['wl_logo']    = $wl_logo;
            }

            wp_enqueue_style( 'tp-create-temp-editor-css', L_THEPLUS_URL . 'modules/widgets-feature/template-editor/tp-create-temp-editor.min.css', array(), L_THEPLUS_VERSION);

            wp_enqueue_script( 'tp-create-temp-editor-js', L_THEPLUS_URL . 'modules/widgets-feature/template-editor/tp-create-temp-editor.min.js', array( 'jquery', 'wp-i18n' ), L_THEPLUS_VERSION, true );

            wp_localize_script( 
                'tp-create-temp-editor-js', 
                'tpae_createtemp_localize', 
                array(
                    'L_THEPLUS_ASSETS_URL' => L_THEPLUS_ASSETS_URL,

                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce('live_editor'),
                    'is_white_label' => $white_label_data,
                )
            );
        }

		/**
		 * Template Create
		 *
		 * @since 6.4.2
		 */
		public function tpae_template_create() {

			/** Security checked wp nonce*/
			$nonce = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
			if ( ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'live_editor' ) ) {
				die( 'Security checked!' );
			}

			/** Security checked user login*/
			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'content' => __( 'Insufficient permissions.', 'tpebl' ) ) );
			}

			$uniq       = uniqid();
			$rand_num   = wp_rand( 1, 1000 );
			$post_name  = 'tp-create-template-' . sanitize_text_field( wp_unslash( $uniq ) );
			$post_title = '';

			$args = array(
				'post_type'              => 'elementor_library',
				'post_status'            => 'publish',
				'name'                   => $post_name,
				'posts_per_page'         => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			);
			$post = get_posts( $args );

			if ( empty( $post ) ) {
				$post_title = 'TP Template ' . $rand_num;

				$params = array(
					'post_content' => '',
					'post_type'    => 'elementor_library',
					'post_title'   => $post_title,
					'post_name'    => $post_name,
					'post_status'  => 'publish',
					'meta_input'   => array(
						'_elementor_edit_mode'     => 'builder',
						'_elementor_template_type' => 'page',
						'_wp_page_template'        => 'elementor_canvas',
					),
				);

				$post_id = wp_insert_post( $params );

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					wp_delete_post( $post_id, true ); 
					wp_send_json_error( array( 'content' => __( 'Permission denied.', 'tpebl' ) ) );
				}

			}

			$temp_url = get_admin_url() . '/post.php?post=' . $post_id . '&action=elementor';

			$result = array(
				'url'   => $temp_url,
				'id'    => $post_id,
				'title' => $post_title,
			);

			wp_send_json_success( $result );
		}

		/**
		 * Change Template name
		 *
		 * @since 6.4.2
		 */
		public function change_current_template_title() {

			/** Security checked wp nonce*/
			$nonce = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
			if ( ! isset( $nonce ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'live_editor' ) ) {
				die( 'Security checked!' );
			}

			/** Security checked user login*/
			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'content' => __( 'Insufficient permissions.', 'tpebl' ) ) );
			}

			$id = ! empty( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

			$updated_title = ! empty( $_POST['updated_title'] ) ? sanitize_text_field( wp_unslash( $_POST['updated_title'] ) ) : '';

			$res = wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => $updated_title,
				)
			);

			$dev = array(
				'ID'         => $id,
				'post_title' => $updated_title,
			);

			wp_send_json_success( $dev );
		}
	}

	return Tpae_Create_Template::get_instance();
}
