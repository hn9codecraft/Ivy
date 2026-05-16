<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      6.3.11
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\TPAEPInstallNotice;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Tpaepro_Notice' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.3.11
	 */
	class Tp_Tpaepro_Notice {

		/**
		 * Instance
		 *
		 * @since 6.3.11
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.3.11
		 *
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
		 * @since 6.3.11
		 */
		public function __construct() {
			add_action( 'wp_ajax_theplus_pro_dismiss_promo', array( $this, 'theplus_pro_promo_notice_dismiss' ) );

			$saved_time = get_option( 'tpae_install_time' );

			$saved_timestamp   = strtotime( $saved_time );
			$current_timestamp = current_time( 'timestamp' );

			$days_passed = floor( ( $current_timestamp - $saved_timestamp ) / DAY_IN_SECONDS );

			if ( $days_passed >= 2 ) {
				if ( ! get_option( 'tpae_pro_promo_notice' ) ) {
					add_action( 'admin_notices', array( $this, 'theplus_pro_promo_install_plugin' ) );
				}
			}
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_pro_promo_install_plugin() {

			$nonce  = wp_create_nonce( 'tpae-pro-notice' );
			$screen = get_current_screen();

			$et_plugin_status = apply_filters( 'tpae_get_plugin_status', 'template-kit-import/template-kit-import.php' );

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins' );

			if ( get_option( 'tpae_onbording_end' ) || 'not_installed' !== $et_plugin_status ) {
				$allowed_parents[] = 'theplus_welcome_page';
			}

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			if ( ! $parent_base ) {
				return;
			}

			if ( defined( 'L_THEPLUS_VERSION' ) && ! defined( 'THEPLUS_VERSION' ) ) {
				echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-pro-promo" style="border-left-color: #6660EF;">
					<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

						<div class="tp-tpae-logo" style="display: flex; padding-top: 14px;">
							<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor Promotion', 'tpebl' ) . '" />
						</div>
						<div style="margin: 0 10px; color: #000;">
							<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Unlock 120+ Elementor Widgets with The Plus Addons for Elementor Pro', 'tpebl' ) . '</h3>
							
							<p style="color: #1e1e1e;">' . esc_html__( 'The free version of The Plus Addons for Elementor gives you a great start, but Pro unlocks advanced widgets like Dynamic Listing Builder, Social Feed & Reviews, Advanced Mega Menu Menu, Login/Register Form, and many more.', 'tpebl' ) . '</p>
							
							<div class="tp-tpae-button" style="margin-top: 10px;">
								<a href="https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=banner&utm_campaign=links" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Upgrade Now', 'tpebl' ) . '</a>
								<a href="https://theplusaddons.com/free-vs-pro/?utm_source=wpbackend&utm_medium=banner&utm_campaign=links" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; color: #6660EF; background: #fff; border: #6660EF 1px solid">' . esc_html__( 'Compare Free vs Pro', 'tpebl' ) . '</a>
							</div>
						</div>
					</div>
				</div>';
			}

			?>
			<script>
				jQuery(document).on('click', '.tpae-pro-promo .notice-dismiss', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_pro_dismiss_promo',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_pro_notice',
						},
						success: function(response) {
							jQuery('.tpae-pro-promo').hide();
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * It's is use for Save key in database
		 * TAPG Notice and TAG Popup Dismisse
		 *
		 * @since 6.3.11
		 */
		public function theplus_pro_promo_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-pro-notice' ) ) {
				die( 'Security checked!' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			update_option( 'tpae_pro_promo_notice', true );

			wp_send_json_success();
		}
	}

	Tp_Tpaepro_Notice::instance();
}