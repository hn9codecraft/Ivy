<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Auth {

    public function __construct() {
        // Redirect wp-login.php to frontend custom login (only for non-admins)
        add_action( 'login_init', array( $this, 'maybe_redirect_login' ) );
        // After successful frontend login, send users to dashboard (admins to wp-admin)
        add_filter( 'login_redirect', array( $this, 'after_login_redirect' ), 10, 3 );
        // Replace WP's "lost password" link with our flow
        add_filter( 'lostpassword_url', array( $this, 'lost_password_url' ), 10, 2 );
    }

    public function maybe_redirect_login() {
        $s = ES_Helpers::settings();
        $login_id = (int) $s['login_page_id'];
        if ( ! $login_id ) return;

        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';
        // Only redirect default 'login' / 'register' actions; let logout, lostpassword, rp, etc. work normally
        if ( ! in_array( $action, array( 'login', 'register' ), true ) ) return;

        // Skip if interrupt=1 (e.g. an admin bypass) or already POSTed (form submission)
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) return;

        $reg_id = (int) $s['register_page_id'];
        $url = ( $action === 'register' && $reg_id ) ? get_permalink( $reg_id ) : get_permalink( $login_id );
        if ( $url ) {
            wp_safe_redirect( $url );
            exit;
        }
    }

    public function after_login_redirect( $redirect_to, $requested, $user ) {
        if ( ! is_wp_error( $user ) && $user instanceof WP_User ) {
            if ( user_can( $user, 'manage_options' ) ) {
                return admin_url();
            }
            $s = ES_Helpers::settings();
            if ( ! empty( $s['dashboard_page_id'] ) ) {
                return get_permalink( $s['dashboard_page_id'] );
            }
        }
        return $redirect_to;
    }

    public function lost_password_url( $url, $redirect ) {
        // Keep default WP recovery for now; can be customized later
        return $url;
    }
}
