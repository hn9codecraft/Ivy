<?php
/**
 * Plugin Name:       Cloudways Partner Hub
 * Plugin URI:        https://www.cloudways.com/
 * Description:       Overrides specific WordPress plugin upgrade links with the Cloudways affiliate URL and manages partner referral integrations.
 * Version:           4.0.0
 * Requires at least: 5.0
 * Tested up to:      6.3
 * Author:            Cloudways
 * Author URI:        https://www.cloudways.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cw-partner-hub
 *
 * @package CloudwaysPartnerHub
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define the affiliate URL constant.
 * Replace with the actual affiliate link if updated.
 */
define( 'CWPH_OMNISEND_AFFILIATE_URL', 'https://www.omnisend.com/cloudways-partner-offer' );

/**
 * Main class for the Cloudways Partner Hub plugin.
 */
class CWPH_Partner_Hub {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup all necessary WordPress hooks.
	 */
	private function setup_hooks() {
		// Omnisend plugin: https://wordpress.org/plugins/omnisend/
		add_filter( 'omnisend_signup_wp_link', array( $this, 'override_omnisend_link' ) );

		// Omnisend WooCommerce plugin: https://wordpress.org/plugins/omnisend-connect/
		add_filter( 'omnisend_woo_partner_link', array( $this, 'override_omnisend_link' ) );
	}

	/**
	 * Filters the Omnisend partner links to use the defined affiliate URL.
	 *
	 * @param string $url The original URL provided by the filter.
	 * @return string The affiliate URL constant.
	 */
	public function override_omnisend_link( $url ) {
		return CWPH_OMNISEND_AFFILIATE_URL;
	}
}

/**
 * Initialize the plugin.
 */
function cwph_run_partner_hub() {
	new CWPH_Partner_Hub();
}
add_action( 'plugins_loaded', 'cwph_run_partner_hub' );

/**
 * Add Cloudways redirect data for the CookieYes integration.
 *
 * @param array $data Existing redirect data.
 * @return array Modified redirect data with Cloudways partner info.
 */

 // CookieYes
add_filter(
	'cookieyes_cloudways_redirect',
	function( $data ) {
		return array(
			'tpRef'  => 'mji1zdh',
			'coupon' => 'CW8K9N2X4M7P',
		);
	}
);

/**
 * Set a default referral username for the Equalize Digital's Accessibility Checker integration.
 *
 * This filter ensures that a default referral username ('cloudways')
 * is applied if no referral (`$ref`) is provided by the system.
 *
 * @param string|null $ref The existing referral value.
 * @return string The modified or default referral username.
 */

// Equalize Digital
add_filter(
	'edac_filter_generate_link_type_ref',
	function( $ref ) {
		if ( ! $ref ) {
			$ref = 'cloudways';
		}
		return $ref;
	}
);

// Rank Math
add_filter( 'rank_math/link', function( $args ) {
    $args['affiliate'] = 'cloudways';
    return $args;
} );

// Omnisend
add_filter( 'omnisend_signup_wp_link', function ( $url ) {
	return 'https://www.omnisend.com/cloudways-partner-offer'; // your affiliate url
} );

// Omnisend WooCommerce
add_filter( 'omnisend_woo_partner_link', function ( $url ) {
	return 'https://www.omnisend.com/cloudways-partner-offer'; // your affiliate url
} );