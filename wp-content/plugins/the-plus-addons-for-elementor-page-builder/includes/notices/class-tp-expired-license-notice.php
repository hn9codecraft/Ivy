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

namespace Tp\Notices\TPAEExpiredLicense;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Expired_License_Notice' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.3.11
	 */
	class Tp_Expired_License_Notice {

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
		 * @since 6.5.6
		 *
		 * @var license_data
		 */
		// public $license_data = get_option( 'tpaep_licence_data' );
		private $license_data = null;

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
			add_action( 'wp_ajax_theplus_explicense_notice_dismiss', array( $this, 'theplus_expired_license_notice_dismiss' ) );

			$this->license_data = get_option( 'tpaep_licence_data' );

			if ( empty( $this->license_data['expires'] ) ) {
				return;
			}

			$expiry_date  = strtotime( $this->license_data['expires'] );
			$current_date = time();

			$days_left = floor( ( $expiry_date - $current_date ) / DAY_IN_SECONDS );

			if ( $days_left <= 0 ) {
				if ( ! get_option( 'tpae_expired_license_notice' ) ) {
					add_action( 'admin_notices', array( $this, 'theplus_expired_license_notice' ) );
				}
			}

			if ( $days_left <= 7 && $days_left > 0 ) {
				if ( ! get_option( 'tpae_expired_license_week_notice' ) ) {
					add_action( 'admin_notices', array( $this, 'theplus_expired_license_week_notice' ) );
				}
			}

			if ( $days_left <= 30 && $days_left > 7 ) {
				if ( ! get_option( 'tpae_expired_license_month_notice' ) ) {
					add_action( 'admin_notices', array( $this, 'theplus_expired_license_month_notice' ) );
				}
			}
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_expired_license_notice() {

			$nonce  = wp_create_nonce( 'tpae-expired-license' );
			$screen = get_current_screen();

			$et_plugin_status = apply_filters( 'tpae_get_plugin_status', 'template-kit-import/template-kit-import.php' );

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins' );

			if ( get_option( 'tpae_onbording_end' ) || 'not_installed' !== $et_plugin_status ) {
				$allowed_parents[] = 'theplus_welcome_page';
			}

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			$license_data = $this->license_data;
			$license_key  = $license_data ? $license_data['license_key'] : '';

			if ( ! $parent_base ) {
				return;
			}

			echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-expired-license-notice" style="border-left-color: #6660EF;">
				<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

					<div class="tp-tpae-logo" style="display: flex; padding-top: 14px;">
						<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="The Plus Addons for Elementor Promotion" />
					</div>
					<div style="margin: 0 10px; color: #000;">
						<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Your The Plus Addons for Elementor License Has Expired', 'tpebl' ) . '</h3>
						
						<p style="color: #1e1e1e;">' . esc_html__( 'Please renew your license to continue using Pro features and receive updates, security patches, and access to new features.', 'tpebl' ) . '</p>
						
						<div class="tp-tpae-button" style="margin-top: 10px;">
							<a href="https://store.posimyth.com/checkout/?edd_license_key=' . esc_html( $license_key ) . '&download_id=141297" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Renew Now', 'tpebl' ) . '</a>
						</div>
					</div>
				</div>
			</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-expired-license-notice .notice-dismiss', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_explicense_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_expired_license_notice',
						},
						success: function(response) {
							jQuery('.tpae-expired-license-notice').hide();
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_expired_license_week_notice() {

			$nonce  = wp_create_nonce( 'tpae-expired-license' );
			$screen = get_current_screen();

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, array( 'index', 'elementor', 'themes', 'edit', 'plugins', 'theplus_welcome_page' ), true );

			$license_data = $this->license_data;
			$license_key  = $license_data ? $license_data['license_key'] : '';

			if ( ! $parent_base ) {
				return;
			}

			echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-expired-license-week" style="border-left-color: #6660EF;">
				<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

					<div class="tp-tpae-logo" style="display: flex; padding-top: 14px;">
						<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="The Plus Addons for Elementor Promotion" />
					</div>
					<div style="margin: 0 10px; color: #000;">
						<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Final Reminder – The Plus Addons for Elementor License Expires in 7 Days', 'tpebl' ) . '</h3>
						
						<p style="color: #1e1e1e;">' . esc_html__( 'Your Pro license will expire in 7 days. Renewing ensures continued access to updates and Pro features without interruption.', 'tpebl' ) . '</p>
						
						<div class="tp-tpae-button" style="margin-top: 10px;">
							<a href="https://store.posimyth.com/checkout/?edd_license_key=' . esc_html( $license_key ) . '&download_id=141297" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Renew Now', 'tpebl' ) . '</a>
						</div>
					</div>
				</div>
			</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-expired-license-week .notice-dismiss', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_explicense_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_expired_license_week_notice',
						},
						success: function(response) {
							jQuery('.tpae-expired-license-week').hide();
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_expired_license_month_notice() {

			$nonce  = wp_create_nonce( 'tpae-expired-license' );
			$screen = get_current_screen();

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, array( 'index', 'elementor', 'themes', 'edit', 'plugins', 'theplus_welcome_page' ), true );

			$license_data = $this->license_data;
			$license_key  = $license_data ? $license_data['license_key'] : '';

			if ( ! $parent_base ) {
				return;
			}

			echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-expired-license-month" style="border-left-color: #6660EF;">
				<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

					<div class="tp-tpae-logo" style="display: flex; padding-top: 14px;">
						<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor Promotion', 'tpebl' ) . '" />
					</div>
					<div style="margin: 0 10px; color: #000;">
						<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Heads-Up: Your The Plus Addons for Elementor License Will Expire in 30 Days', 'tpebl' ) . '</h3>
						
						<p style="color: #1e1e1e;">' . esc_html__( 'Your Pro license will expire in 30 days renew in advance to avoid any break in functionality or receiving active updates.', 'tpebl' ) . '</p>
						
						<div class="tp-tpae-button" style="margin-top: 10px;">
							<a href="https://store.posimyth.com/checkout/?edd_license_key=' . esc_html( $license_key ) . '&download_id=141297" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Renew Early', 'tpebl' ) . '</a>
						</div>
					</div>
				</div>
			</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-expired-license-month .notice-dismiss', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_explicense_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_expired_license_month_notice',
						},
						success: function(response) {
							jQuery('.tpae-expired-license-month').hide();
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
		public function theplus_expired_license_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-expired-license' ) ) {
				die( 'Security checked!' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			if ( 'tpae_expired_license_notice' === $get_type ) {
				update_option( 'tpae_expired_license_notice', true );
			} elseif ( 'tpae_expired_license_week_notice' === $get_type ) {
				update_option( 'tpae_expired_license_week_notice', true );
			} elseif ( 'tpae_expired_license_month_notice' === $get_type ) {
				update_option( 'tpae_expired_license_month_notice', true );
			}

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

	Tp_Expired_License_Notice::instance();
}