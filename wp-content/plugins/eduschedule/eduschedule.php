<?php
/**
 * Plugin Name: EduSchedule
 * Description: Modern booking platform with frontend register/login, user dashboard, slot-based booking (1:1 / Group / Open / Personal), Zoom auto-create, country-aware timezones.
 * Version: 4.7.1
 * Author: Your Name
 * Text Domain: eduschedule
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ES_VERSION', '4.7.9' );
define( 'ES_DB_VERSION', '2.0.6' ); // bump when DB schema changes
define( 'ES_FILE', __FILE__ );
define( 'ES_DIR', plugin_dir_path( __FILE__ ) );
define( 'ES_URL', plugin_dir_url( __FILE__ ) );

require_once ES_DIR . 'includes/class-es-activator.php';
require_once ES_DIR . 'includes/class-es-helpers.php';
require_once ES_DIR . 'includes/class-es-db.php';
require_once ES_DIR . 'includes/class-es-packages.php';
require_once ES_DIR . 'includes/class-es-zoom.php';
require_once ES_DIR . 'includes/class-es-mailer.php';
require_once ES_DIR . 'includes/class-es-stripe.php';
require_once ES_DIR . 'includes/class-es-shortcodes.php';
require_once ES_DIR . 'includes/class-es-ajax.php';
require_once ES_DIR . 'includes/class-es-auth.php';
require_once ES_DIR . 'admin/class-es-admin.php';
require_once ES_DIR . 'admin/class-es-admin-ajax.php';

register_activation_hook( __FILE__, array( 'ES_Activator', 'activate' ) );

add_action( 'plugins_loaded', function () {
    // Auto-upgrade DB if version changed (without needing re-activation)
    $installed_db_version = get_option( 'es_db_version', '0' );
    if ( version_compare( $installed_db_version, ES_DB_VERSION, '<' ) ) {
        ES_Packages::create_table();
        update_option( 'es_db_version', ES_DB_VERSION );
    }

    new ES_Auth();
    new ES_Shortcodes();
    new ES_Ajax();
    if ( is_admin() ) {
        new ES_Admin();
        new ES_Admin_Ajax();
    }

    // Email — route through SMTP when configured (Settings → Email).
    ES_Mailer::init();

    // Stripe — return URL + webhook handlers
    add_action( 'template_redirect', array( 'ES_Stripe', 'handle_return' ) );
    add_action( 'init', array( 'ES_Stripe', 'handle_webhook' ) );
} );
