<?php
/**
 * It is Main File to load all Notice, Upgrade Menu and all
 *
 * @link       https://posimyth.com/
 * @since      5.3.3
 * @version    5.6.3
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Theplus\Notices;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Notices_Main' ) ) {

	/**
	 * This class used for only load All Notice Files
	 *
	 * @since 5.3.3
	 * @version 5.6.3
	 */
	class Tp_Notices_Main {

		/**
		 * Instance
		 *
		 * @since 5.3.3
		 * @access private
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * White label Option
		 *
		 * @var string
		 */
		public $whitelabel = '';

		/**
		 * White label Option
		 *
		 * @var string
		 */
		public $hidden_label = '';

		/**
		 * Instance
		 *
		 * @since 6.5.6
		 *
		 * @var w_d_s_i_g_n_k_i_t_slug
		 */
		public $w_d_s_i_g_n_k_i_t_slug = 'wdesignkit/wdesignkit.php';

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 5.3.3
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
		 * @since 5.3.3
		 * @access public
		 */
		public function __construct() {
			$this->tp_white_label();
			$this->tp_notices_manage();
		}

		/**
		 * Here add globel class varible for white label
		 *
		 * @since 5.3.3
		 */
		public function tp_white_label() {
			$this->whitelabel   = get_option( 'theplus_white_label' );
			$this->hidden_label = ! empty( $this->whitelabel['tp_hidden_label'] ) ? $this->whitelabel['tp_hidden_label'] : '';
		}

		/**
		 * Initiate our hooks
		 *
		 * @since 5.3.3
		 * @version 5.6.5
		 */
		public function tp_notices_manage() {

			if ( ! get_option( 'tpae_install_time' ) ) {
				add_option( 'tpae_install_time', current_time( 'mysql' ) );
			}

			// $envato_plugins = array(
			// 'name'        => 'envato-elements',
			// 'status'      => '',
			// 'plugin_slug' => 'envato-elements/envato-elements.php',
			// );

			$tpae_pro_plugin    = array(
				'name'        => 'theplus_elementor_addon',
				'status'      => '',
				'plugin_slug' => 'theplus_elementor_addon/theplus_elementor_addon.php',
			);
			$nxt_plugin         = array(
				'name'        => 'the-plus-addons-for-block-editor',
				'status'      => '',
				'plugin_slug' => 'the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php',
			);
			$nxt_pro_plugin     = array(
				'name'        => 'the-plus-addons-for-block-editor-pro',
				'status'      => '',
				'plugin_slug' => 'the-plus-addons-for-block-editor-pro/the-plus-addons-for-block-editor-pro.php',
			);
			$nxt_ext_plugin     = array(
				'name'        => 'nexter-extension',
				'status'      => '',
				'plugin_slug' => 'nexter-extension/nexter-extension.php',
			);
			$nxt_ext_pro_plugin = array(
				'name'        => 'nexter-pro-extensions',
				'status'      => '',
				'plugin_slug' => 'nexter-pro-extensions/nexter-pro-extensions.php',
			);
			$ele_plugin         = array(
				'name'        => 'elementor',
				'status'      => '',
				'plugin_slug' => 'elementor/elementor.php',
			);
			$ele_pro_plugin     = array(
				'name'        => 'elementor-pro',
				'status'      => '',
				'plugin_slug' => 'elementor-pro/elementor-pro.php',
			);
			$temp_kit_plugin    = array(
				'name'        => 'template-kit-import',
				'status'      => '',
				'plugin_slug' => 'template-kit-import/template-kit-import.php',
			);

			$ele_details     = $this->tpae_check_plugins_depends( $ele_plugin );
			$ele_pro_details = $this->tpae_check_plugins_depends( $ele_pro_plugin );

			$tpae_pro_details = $this->tpae_check_plugins_depends( $tpae_pro_plugin );

			$nxt_details     = $this->tpae_check_plugins_depends( $nxt_plugin );
			$nxt_pro_details = $this->tpae_check_plugins_depends( $nxt_pro_plugin );

			$nxt_ext_details     = $this->tpae_check_plugins_depends( $nxt_ext_plugin );
			$nxt_ext_pro_details = $this->tpae_check_plugins_depends( $nxt_ext_pro_plugin );

			$temp_kit_details = $this->tpae_check_plugins_depends( $temp_kit_plugin );

			if ( is_admin() && current_user_can( 'manage_options' ) ) {

				include L_THEPLUS_PATH . 'includes/notices/class-tp-plugin-page.php';

				if ( empty( $this->whitelabel ) || 'on' !== $this->hidden_label ) {
					include L_THEPLUS_PATH . 'includes/notices/class-tp-widget-notice.php';
				}

				include L_THEPLUS_PATH . 'includes/notices/class-tp-dashboard-overview.php';

				$current_version = str_replace( '.', '', L_THEPLUS_VERSION );

				$current_cleanup_key  = 'theplus_cleanup_sale_notices_' . $current_version;
				$previous_cleanup_key = 'theplus_cleanup_sale_notices_' . ( (int) $current_version - 1 );

				/**Remove Key In Databash*/
				if ( ! get_option( $current_cleanup_key ) ) {
					if ( get_option( $previous_cleanup_key ) ) {
						delete_option( $previous_cleanup_key );
					}

					include L_THEPLUS_PATH . 'includes/notices/class-tp-notices-remove.php';

					update_option( $current_cleanup_key, true );
				}
			}

			$license_data = get_option( 'tpaep_licence_data' );

			if ( empty( $this->whitelabel['plugin_news'] ) || 'on' !== $this->whitelabel['plugin_news'] ) {

				$check_license = $license_data ? $license_data['expires'] : '';

				/** Plugin Features Banner*/
				// if ( ! get_option( 'tpae_pluginfeatures_notice_dismissed' ) ) {
				// 	include L_THEPLUS_PATH . 'includes/notices/class-tp-plugin-features-banner.php';
				// }

				if ( ! empty( $ele_pro_details[0]['status'] ) && 'unavailable' == $ele_pro_details[0]['status'] ) {
					if ( ! empty( $nxt_ext_details[0]['status'] ) && 'unavailable' == $nxt_ext_details[0]['status'] ) {
						if ( ! empty( $nxt_ext_pro_details[0]['status'] ) && 'unavailable' == $nxt_ext_pro_details[0]['status'] ) {
							include L_THEPLUS_PATH . 'includes/notices/class-tp-nexter-extension-promo.php';
						}
					}
				}

				if ( ! get_option( 'tpae_nexter_block_notice' ) ) {
					if ( ! empty( $nxt_details[0]['status'] ) && 'unavailable' == $nxt_details[0]['status'] ) {
						if ( ! empty( $nxt_pro_details[0]['status'] ) && 'unavailable' == $nxt_pro_details[0]['status'] ) {
							include L_THEPLUS_PATH . 'includes/notices/class-tp-nexter-notice.php';
						}
					}
				}

				/** Install TPAE Pro notice*/
				if ( ! get_option( 'tpae_pro_promo_notice' ) ) {
					if ( ! empty( $tpae_pro_details[0]['status'] ) && 'unavailable' === $tpae_pro_details[0]['status'] ) {
						include L_THEPLUS_PATH . 'includes/notices/class-tp-tpaepro-notice.php';
					}
				}

				/** Activate License*/
				if ( ! empty( $tpae_pro_details[0]['status'] ) && 'active' === $tpae_pro_details[0]['status'] ) {
					
					if ( ! get_option( 'tpae_activate_license_notice' ) ) {
						if ( empty( $license_data ) && empty( $license_data['license_key'] ) ) {
							include L_THEPLUS_PATH . 'includes/notices/class-tp-activate-license-notice.php';
						}
					}

					/** License Expired*/
					/** License will Expire in 1 Month*/
					/** License will Expire in 1 Week*/
					if ( ! empty( $license_data ) && 'lifetime' !== $license_data['expires'] ) {
						include L_THEPLUS_PATH . 'includes/notices/class-tp-expired-license-notice.php';
					}
				}

				/** Ask for Review*/
				if ( ! get_option( 'tpae_ask_review_notice' ) ) {
					include L_THEPLUS_PATH . 'includes/notices/class-tp-ask-review-notice.php';
				}

				/** Join Community Discord*/
				if ( ! get_option( 'tpae_join_community_notice' ) ) {
					include L_THEPLUS_PATH . 'includes/notices/class-tp-join-community-notice.php';
				}

				/** Allow Data Tracking*/
				// include L_THEPLUS_PATH . 'includes/notices/class-tp-data-tracking-notice.php';

			}

			// $envato_details = $this->tpae_check_plugins_depends( $envato_plugins );
			// if ( current_user_can( 'manage_options' ) ) {
			// $current_user_id = get_current_user_id();
			// $meta_value = get_user_meta( $current_user_id, 'elementor_introduction', true );

			// $ai_get_started_announcement = ( ! empty( $meta_value ) && ! empty( $meta_value['ai-get-started-announcement'] ) ) ? $meta_value['ai-get-started-announcement'] : 0;

			// if( '0' != $ai_get_started_announcement ){
			// if( !empty( $envato_details[0]['status'] ) && 'unavailable' == $envato_details[0]['status'] ){
			// $option_eop = get_option( 'tp_editor_onbording_popup' );
			// if ( empty( $option_eop ) || 'yes' !== $option_eop ) {
			// }
			// }
			// }
			// }

			if ( current_user_can( 'install_plugins' ) && current_user_can( 'manage_options' ) && $this->tp_check_plugin_status() ) {
				if ( empty( $this->whitelabel ) || 'on' !== $this->hidden_label ) {
					$option_value = get_option( 'tp_wdkit_preview_popup' );

					if ( empty( $option_value ) || 'yes' !== $option_value ) {
						// if( !empty( $envato_details[0]['status'] ) && 'unavailable' == $envato_details[0]['status'] ){
						if ( ! empty( $temp_kit_details[0]['status'] ) && 'unavailable' == $temp_kit_details[0]['status'] ) {
							include L_THEPLUS_PATH . 'includes/notices/class-tp-wdkit-preview-popup.php';
						}
						// }
					}
				}
			}
		}

		/**
		 * Check Plugin Status
		 *
		 * @since 5.6.3
		 */
		public function tp_check_plugin_status() {

			if ( ! defined( 'WDKIT_VERSION' ) ) {
				return true;
			}

			$installed_plugins = $this->get_plugins();

			if ( empty( $installed_plugins ) ) {
				return false;
			}

			if ( is_plugin_active( $this->w_d_s_i_g_n_k_i_t_slug ) || ( ! empty( $installed_plugins ) && isset( $installed_plugins[ $this->w_d_s_i_g_n_k_i_t_slug ] ) ) ) {
				return false;
			} else {
				return true;
			}

			return false;
		}

		/**
		 *
		 * It is Use for Check Plugin Dependency of template.
		 *
		 * @since 6.0.0
		 */
		public function tpae_check_plugins_depends( $plugin ) {
			$update_plugin = array();

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();

			$pluginslug = ! empty( $plugin['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $plugin['plugin_slug'] ) ) : '';

			if ( ! is_plugin_active( $pluginslug ) ) {
				if ( ! isset( $all_plugins[ $pluginslug ] ) ) {
						$plugin['status'] = 'unavailable';
				} else {
					$plugin['status'] = 'inactive';
				}

				$update_plugin[] = $plugin;
			} elseif ( is_plugin_active( $pluginslug ) ) {
				$plugin['status'] = 'active';
				$update_plugin[]  = $plugin;
			}

			return $update_plugin;
		}

		/**
		 *
		 * It is Use for get plugin list.
		 *
		 * @since 5.6.3
		 */
		private function get_plugins() {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once \ABSPATH . 'wp-admin/includes/plugin.php';

				return get_plugins();
			}
		}
	}

	Tp_Notices_Main::instance();
}
