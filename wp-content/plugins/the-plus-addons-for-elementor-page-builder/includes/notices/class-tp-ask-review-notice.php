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

if ( ! class_exists( 'Tp_Ask_Review_Notice' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.3.11
	 */
	class Tp_Ask_Review_Notice {

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
			add_action( 'wp_ajax_theplus_askreview_notice_dismiss', array( $this, 'theplus_ask_review_notice_dismiss' ) );

			$saved_time = get_option( 'tpae_install_time' );

			$saved_timestamp   = strtotime( $saved_time );
			$current_timestamp = current_time( 'timestamp' );

			$days_passed = floor( ( $current_timestamp - $saved_timestamp ) / DAY_IN_SECONDS );

			if ( ! get_option( 'tpae_ask_review_notice' ) ) {
				if ( get_option( 'tpae_review_show_later' ) ) {
					$show_later_time = get_option( 'tpae_review_show_later' );

					$showlater_timestamp = strtotime( $show_later_time );
					$days_passed         = floor( ( $current_timestamp - $showlater_timestamp ) / DAY_IN_SECONDS );

					if ( $days_passed >= 30 ) {
						add_action( 'admin_notices', array( $this, 'theplus_ask_review_notice' ) );
					}
				} elseif ( $days_passed >= 14 ) {
					add_action( 'admin_notices', array( $this, 'theplus_ask_review_notice' ) );
				}
			}
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_ask_review_notice() {

			$nonce  = wp_create_nonce( 'tpae-ask-review' );
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

			echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-ask-review" style="border-left-color: #6660EF;">
					<div class="tp-nexter-werp" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

						<div class="tp-notice-wrap" style="display: flex; padding-top: 14px;">
							<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor Promotion', 'tpebl' ) . '" />
						</div>
						<div style="margin: 0 10px; color: #000;">
							<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Has The Plus Addons for Elementor Helped You Build Faster?', 'tpebl' ) . '</h3>

							<p style="color: #1e1e1e;">' . esc_html__( 'If The Plus Addons for Elementor saved you time or made your workflow smoother, please leave a review. It helps more Elementor creators discover us and keeps our team motivated.', 'tpebl' ) . '</p>

							<div class="tp-tpae-button" style="margin-top: 10px;">
								<a href="https://wordpress.org/support/plugin/the-plus-addons-for-elementor-page-builder/reviews/#new-post" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Leave a Review', 'tpebl' ) . '</a>
								<button type="button" class="button maybe-later" style="margin-right: 10px; color: #6660EF; background: #fff; border: #6660EF 1px solid">' . esc_html__( 'Maybe Later', 'tpebl' ) . '</button>
								<button type="button" class="button already-done" style="margin-right: 10px; color: #6660EF; background: #fff; border: #6660EF 1px solid">' . esc_html__( 'Already Done', 'tpebl' ) . '</button>
							</div>
						</div>
					</div>
				</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-ask-review .notice-dismiss,.tpae-ask-review .button.already-done', function(e) {
					e.preventDefault();

					var $notice = jQuery(this).closest('.tpae-ask-review');

					$notice.fadeTo(100, 0, function () {
						$notice.slideUp(100, function () {
							$notice.remove();
						});
					});

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_askreview_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_ask_review_notice',
						},
						success: function(response) {
							jQuery('.tpae-ask-review').hide();
						}
					});
				});

				jQuery(document).on('click', '.tpae-ask-review .button.maybe-later', function(e) {
					e.preventDefault();

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_askreview_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
							type: 'tpae_review_notice_later',
						},
						success: function(response) {
							jQuery('.tpae-ask-review').hide();
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
		public function theplus_ask_review_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-ask-review' ) ) {
				die( esc_html__( 'Security checked!', 'tpebl' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			if ( 'tpae_review_notice_later' === $get_type ) {
				$saved_time = current_time( 'mysql' );
				update_option( 'tpae_review_show_later', $saved_time );
			} elseif ( 'tpae_ask_review_notice' === $get_type ) {
				update_option( 'tpae_ask_review_notice', true );
			}

			return;

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

	Tp_Ask_Review_Notice::instance();
}