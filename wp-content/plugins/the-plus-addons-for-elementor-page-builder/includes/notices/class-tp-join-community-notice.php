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

namespace Tp\Notices\TPAEAskReview;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Join_Community_Notice' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.3.11
	 */
	class Tp_Join_Community_Notice {

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
			add_action( 'wp_ajax_theplus_joincom_notice_dismiss', array( $this, 'theplus_join_community_notice_dismiss' ) );

			$saved_time = get_option( 'tpae_install_time' );

			$saved_timestamp   = strtotime( $saved_time );
			$current_timestamp = current_time( 'timestamp' );

			$days_passed = floor( ( $current_timestamp - $saved_timestamp ) / DAY_IN_SECONDS );

			if ( $days_passed >= 30 ) {
				if ( ! get_option( 'tpae_join_community_notice' ) ) {
					add_action( 'admin_notices', array( $this, 'theplus_join_community_notice' ) );
				}
			}
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_join_community_notice() {

			$nonce  = wp_create_nonce( 'tpae-join-community' );
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

			echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-join-community" style="border-left-color: #6660EF;">
				<div class="tp-nexter-werp" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

					<div class="tp-notice-wrap" style="display: flex; padding-top: 14px;">
						<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor Promotion', 'tpebl' ) . '" />
					</div>
					<div style="margin: 0 10px; color: #000;">
						<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Join The Plus Addons for Elementor Community – Learn, Share & Grow', 'tpebl' ) . '</h3>

						<p style="color: #1e1e1e;">' . esc_html__( 'Get early access to features, share ideas, and connect with other Elementor creators.', 'tpebl' ) . '</p>

						<div class="tp-tpae-button" style="margin-top: 10px;">
							<a href="https://go.posimyth.com/plus-elementor-discord" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Join Discord', 'tpebl' ) . '</a>
							<a href="https://www.facebook.com/groups/theplus4elementor" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Join Facebook Group', 'tpebl' ) . '</a>
						</div>
					</div>
				</div>
			</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-join-community .notice-dismiss', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_joincom_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_join_community_notice',
						},
						success: function(response) {
							jQuery('.tpae-join-community').hide();
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
		public function theplus_join_community_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-join-community' ) ) {
				die( 'Security checked!' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			update_option( 'tpae_join_community_notice', true );

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

	Tp_Join_Community_Notice::instance();
}