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

namespace Tp\Notices\TPAEActivateLicense;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Activate_License_Notice' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.3.11
	 */
	class Tp_Activate_License_Notice {

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

			if ( ! get_option( 'tpae_activate_license_notice' ) ) {
				add_action( 'admin_notices', array( $this, 'theplus_activate_license_notice' ) );
			}
			add_action( 'wp_ajax_theplus_actlicense_notice_dismiss', array( $this, 'theplus_act_license_notice_dismiss' ) );
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_activate_license_notice() {

			$nonce  = wp_create_nonce( 'tpae-activate-license' );
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

			if ( ! get_option( 'tpae_onbording_end' ) && 'not_installed' === $et_plugin_status ) {
				$activate_url = $this->l_theplus_dashboard_url( 'theplus_welcome_page#/activate_pro?tp_skip_onbording' );
			} else {
				$activate_url = $this->l_theplus_dashboard_url( 'theplus_welcome_page#/activate_pro' );
			}

			echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-activate-license-promo" style="border-left-color: #6660EF;">
				<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

					<div class="tp-tpae-logo" style="display: flex; padding-top: 14px;">
						<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor Promotion', 'tpebl' ) . '" />
					</div>
					<div style="margin: 0 10px; color: #000;">
						<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Activate The Plus Addons for Elementor Pro License for Updates & Support', 'tpebl' ) . '</h3>
						
						<p style="color: #1e1e1e;">' . esc_html__( 'Your Pro version is installed, now activate your license for active updates and support access.', 'tpebl' ) . '</p>
						
						<div class="tp-tpae-button" style="margin-top: 10px;">
							<a href="' . esc_url( $activate_url ) . '" class="button" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Activate License', 'tpebl' ) . '</a>
						</div>
					</div>
				</div>
			</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-activate-license-promo .notice-dismiss', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_actlicense_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_activate_license_notice',
						},
						success: function(response) {
							jQuery('.tpae-tpag-blocks-promo').hide();
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
		public function theplus_act_license_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-activate-license' ) ) {
				die( esc_html__( 'Security checked!', 'tpebl' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			update_option( 'tpae_activate_license_notice', true );

			wp_send_json_success();
		}


		/**
		 * Redirect Dashboard Page
		 *
		 * @since 5.5.6
		 */
		public function l_theplus_dashboard_url( $slug ) {
			$plugin_page_url = add_query_arg(
				array(
					'page' => $slug,
				),
				admin_url( 'admin.php' )
			);

			return $plugin_page_url;
		}
	}

	Tp_Activate_License_Notice::instance();
}