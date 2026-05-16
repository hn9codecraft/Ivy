<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      6.4.6
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\TPAEWinterSaleBanner;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Tpae_PluginFeatures_Banner' ) ) {

	/**
	 * This class used for only elementor widget load
	 *
	 * @since 6.4.6
	 */
	class Tpae_PluginFeatures_Banner {

		/**
		 * Instance
		 *
		 * @since 6.4.6
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.4.6
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
		 * @since 6.4.6
		 * @access public
		 */
		public function __construct() {

			/** TPAE Plugin Features Banner*/
			if ( ! get_option( 'tpae_pluginfeatures_notice_dismissed' ) ) {
				add_action( 'admin_notices', array( $this, 'tpae_plugin_features_banner' ) );
			}

			/** TPAE Plugin Features Banner Close*/
			add_action( 'wp_ajax_tpae_pluginfeatures_notice_dismiss', array( $this, 'tpae_pluginfeatures_notice_dismiss' ) );
		}

		/**
		 * Plugin Features Banner (Dynamic Tag & GSAP Animation)
		 *
		 * @since 6.4.6
		 */
		public function tpae_plugin_features_banner() {
			$nonce  = wp_create_nonce( 'tpae-pluginfeatures-banner' );
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins', 'theplus_welcome_page' );

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			if ( ! $parent_base ) {
				return;
			}

			$desc_text = __( 'The Plus Addons for Elementor now gives Dynamic Fields for Elementor (without Elementor Pro). In the Free version, you can use Post Dynamic Fields to build dynamic blog layouts. To unlock all Dynamic Fields and create advanced dynamic designs, upgrade to Pro.', 'tpebl' );

			$get_pro_btn = '<a href="' . esc_url( 'https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=banner&utm_campaign=dynamicfields' ) . '"  class="button tpae-offer-btn" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:4px;width:max-content;color:#006ADF;border:1px solid #006ADF;background:#ffffff00;padding:3px 22px;border-radius:5px;font-weight:500;"><i class="theplus-i-crown path1 path2" style="font-size:16px;"></i>' . esc_html__( 'Get Pro', 'tpebl' ) . '</a>';

			if ( defined( 'THEPLUS_VERSION' ) ) {

				$desc_text = __( 'You can now use Dynamic Fields (without Elementor Pro) in The Plus Addons for Elementor to build fully dynamic layouts.', 'tpebl' );

				$get_pro_btn = '';
			}

			echo '<div class="notice tpae-notice-show tpae-plugin-features-banner is-dismissible" style="border-left: 4px solid #006ADF;">
				<div class="inline" style="display: flex;column-gap: 12px;align-items: center;padding: 15px 10px;position: relative;    margin-left: 0px;">
					<img style="max-width:136px;max-height:136px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/dynamictag-banner.png' ) . '" />
					<div style="margin: 0 10px; color:#000;display:flex;flex-direction:column;gap:10px;">  
						<div style="font-size:16px;font-weight:600;letter-spacing:0.1px;">' . esc_html__( 'Big Update: Dynamic Fields Are Now Available in The Plus Addons for Elementor', 'tpebl' ) . '</div>
						<div style="font-size:12px;color:#5D5D5D;width:96%;"> ' . esc_html( $desc_text ) . ' </div>
						<div class="tpae-winter-sale-btn" style="display:flex;column-gap:10px;flex-wrap:wrap;margin-top:3px;">
							<a href="' . esc_url( 'https://theplusaddons.com/blog/dynamic-content-without-elementor-pro/?utm_source=wpbackend&utm_medium=banner&utm_campaign=dynamicfields' ) . '" class="button tpae-deal-btn" target="_blank" rel="noopener noreferrer" style=" width:max-content;color:#fff;border-color:#006ADF;background:#006ADF;padding:3px 22px;border-radius:5px;font-weight:500;">' . esc_html__( 'Read More', 'tpebl' ) . '</a>
							
							<a href="https://youtu.be/W2s-ZAvwuYc" class="button tpae-video-btn" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:4px;width:max-content;color:#006ADF;border:1px solid #006ADF;background:#ffffff00;padding:3px 22px;border-radius:5px;font-weight:500;"><i class="theplus-i-play" style="font-size:16px;"></i>' . esc_html__( 'See in Action', 'tpebl' ) . '</a>'

							. $get_pro_btn . '

						</div>
					</div>
				</div>
			</div>';

			echo '<style> .notice.tpae-notice-show.tpae-plugin-features-banner a.button.tpae-deal-btn:hover{background:#005FCC!important;}.notice.tpae-notice-show.tpae-plugin-features-banner a.button.tpae-offer-btn:hover,.notice.tpae-notice-show.tpae-plugin-features-banner a.button.tpae-video-btn:hover{background:#f3f3f3 !important;}</style>';
			?>
			<script>
				jQuery(document).on('click', '.tpae-plugin-features-banner.tpae-notice-show .notice-dismiss', function(e) {
					e.preventDefault();
					
					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'tpae_pluginfeatures_notice_dismiss',
							security: "<?php echo esc_attr( $nonce ); ?>",
						},
						success: function(response) {
							jQuery('.tpae-plugin-features-banner').hide();
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * It's is use for Save key in database for the TPAE Winter Sale Banner
		 *
		 * @since 6.4.6
		 */
		public function tpae_pluginfeatures_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! $get_security || ! wp_verify_nonce( $get_security, 'tpae-pluginfeatures-banner' ) ) {
				wp_send_json_error( 'Security check failed!' );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'tpebl' ) );
			}

			$get_type = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			update_option( 'tpae_pluginfeatures_notice_dismissed', true );

			wp_send_json_success();
		}
	}

	Tpae_PluginFeatures_Banner::instance();
}
