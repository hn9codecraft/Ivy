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

namespace Tp\Notices\TPAEDataTracking;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Data_Tracking_Notice' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.3.11
	 */
	class Tp_Data_Tracking_Notice {

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
			add_action( 'admin_notices', array( $this, 'theplus_data_tracking_notice' ) );
			add_action( 'wp_ajax_theplus_askreview_notice_dismiss', array( $this, 'theplus_ask_review_notice_dismiss' ) );
		}

		/**
		 * Plugin Active Theplus Addons for Block Editor Notice Installing Notice show
		 *
		 * @since 6.3.11
		 */
		public function theplus_data_tracking_notice() {

			$nonce  = wp_create_nonce( 'tpae-data-tracking' );
			$screen = get_current_screen();

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins' );

			if ( get_option( 'tpae_onbording_end' ) ) {
				$allowed_parents[] = 'theplus_welcome_page';
			}

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			if ( ! $parent_base ) {
				return;
			}

			if ( ! get_option( 'tpae_ask_review_notice' ) ) {
				echo '<div class="notice notice-error is-dismissible tpae-notice-show tpae-data-tracking" style="border-left-color: #6660EF;">
                        <div class="tp-nexter-werp" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

                            <div class="tp-notice-wrap" style="display: flex; padding-top: 14px;">
                                <img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor Promotion', 'tpebl' ) . '" />
                            </div>
                            <div style="margin: 0 10px; color: #000;">
                                <h3 style="margin: 10px 0 7px;">' . esc_html__( 'Help Us Improve The Plus Addons for Elementor', 'tpebl' ) . '</h3>

                                <p style="color: #1e1e1e;">' . esc_html__( 'We’d love to understand how users build with The Plus Addons for Elementor so we can keep improving the right widgets. Data is anonymous, and no personal information is collected.', 'tpebl' ) . '</p>

                                <div class="tp-tpae-button" style="margin-top: 10px;">
                                    <a href="https://wordpress.org/support/plugin/the-plus-addons-for-elementor-page-builder/reviews/#new-post" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; background: #6660EF; color: #fff;">' . esc_html__( 'Allow', 'tpebl' ) . '</a>
                                    <a href="https://theplusaddons.com/free-vs-pro/" class="button" target="_blank" rel="noopener noreferrer" style="margin-right: 10px; color: #6660EF; background: #fff; border: #6660EF 1px solid">' . esc_html__( 'No Thanks', 'tpebl' ) . '</a>
                                </div>
                            </div>
                        </div>
                    </div>';
			}

			?>
			<script>
				jQuery(document).on('click', '.tpae-ask-review .notice-dismiss,.tpae-ask-review .button.already-done', function(e) {
					e.preventDefault();

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
				die( 'Security checked!' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			update_option( 'tpae_ask_review_notice', true );

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

	Tp_Data_Tracking_Notice::instance();
}