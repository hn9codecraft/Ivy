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

        // ── Make the custom reset page authoritative ──
        // Whenever WordPress generates a password-reset email (from ANY source —
        // our AJAX, the wp-login form, or another plugin), rewrite the reset
        // link so it points at our own front-end set-password page instead of
        // wp-login.php. This guarantees users never land on the built-in screen.
        add_filter( 'retrieve_password_message', array( $this, 'reset_email_message' ), 20, 4 );
        add_filter( 'retrieve_password_title',   array( $this, 'reset_email_title' ), 20, 3 );

        // v3.9.6 — Lock students out of /wp-admin. Subscribers (and any role
        // that can't manage_options) should never reach the WordPress
        // dashboard; they belong on the front-end student dashboard instead.
        add_action( 'admin_init', array( $this, 'block_admin_for_students' ) );
        add_action( 'after_setup_theme', array( $this, 'hide_admin_bar_for_students' ) );
    }

    /**
     * Build the front-end set-password URL (our custom page) for a reset key.
     */
    private function custom_reset_url( $key, $user_login ) {
        $base = ES_Helpers::reset_page_url();
        return add_query_arg( array(
            'es_action' => 'rp',
            'key'       => rawurlencode( $key ),
            'login'     => rawurlencode( $user_login ),
        ), $base );
    }

    /**
     * Rewrite WordPress's native password-reset email so its link goes to our
     * custom set-password page. Falls back to the default message if we can't
     * resolve a login page.
     *
     * @param string $message    Default email body.
     * @param string $key        The reset key.
     * @param string $user_login The user login.
     * @param WP_User $user_data  The user object.
     */
    public function reset_email_message( $message, $key, $user_login, $user_data = null ) {
        // Only rewrite the message if we can resolve a front-end page that hosts
        // the reset form (dedicated reset page, login/auth page, or configured
        // login page). Otherwise leave WordPress's default message intact.
        $base = ES_Helpers::reset_page_url();
        if ( ! $base || $base === home_url( '/' ) ) {
            // home_url fallback means no plugin reset page exists — don't hijack.
            $s        = ES_Helpers::settings();
            $login_id = (int) ( $s['login_page_id'] ?? 0 );
            if ( ! $login_id ) return $message;
        }

        $reset_url = $this->custom_reset_url( $key, $user_login );
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $lines   = array();
        $lines[] = sprintf( 'Someone requested a password reset for your %s account.', $site_name );
        $lines[] = '';
        $lines[] = 'If this was you, click the link below to set a new password:';
        $lines[] = $reset_url;
        $lines[] = '';
        $lines[] = 'If you did not request this, you can safely ignore this email and your password will remain unchanged.';

        return implode( "\r\n", $lines );
    }

    /**
     * Match the email subject to the custom flow.
     */
    public function reset_email_title( $title, $user_login = '', $user_data = null ) {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
        return sprintf( '[%s] Reset your password', $site_name );
    }

    /**
     * Redirect non-admin users away from /wp-admin to the front-end dashboard
     * (or the site home). AJAX requests are always allowed through so the
     * booking/payment endpoints keep working.
     */
    public function block_admin_for_students() {
        if ( wp_doing_ajax() ) return;                 // allow admin-ajax.php
        if ( ! is_user_logged_in() ) return;           // login flow handles guests
        if ( current_user_can( 'manage_options' ) ) return; // admins are fine

        // Allow staff who genuinely manage the schedule (e.g. an editor role
        // you've granted the plugin capability to) to stay in.
        $cap = ES_Helpers::admin_capability();
        if ( $cap !== 'manage_options' && current_user_can( $cap ) ) return;

        $s = ES_Helpers::settings();
        $dash_id = (int) ( $s['dashboard_page_id'] ?? 0 );
        $target  = $dash_id ? get_permalink( $dash_id ) : home_url( '/' );

        wp_safe_redirect( $target );
        exit;
    }

    /**
     * Hide the WordPress admin toolbar for non-admin users on the front end.
     */
    public function hide_admin_bar_for_students() {
        if ( ! is_user_logged_in() ) return;
        if ( current_user_can( 'manage_options' ) ) return;
        $cap = ES_Helpers::admin_capability();
        if ( $cap !== 'manage_options' && current_user_can( $cap ) ) return;
        show_admin_bar( false );
    }

    public function maybe_redirect_login() {
        $s = ES_Helpers::settings();
        $login_id = (int) $s['login_page_id'];

        // Resolve a usable login URL. Prefer the configured login page; if none
        // is set, fall back to any page containing the login shortcode, so the
        // custom forgot/reset flow still works out of the box.
        $login_url = $login_id ? get_permalink( $login_id ) : '';
        if ( ! $login_url ) {
            $login_url = $this->find_login_page_url();
        }

        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';

        // ── Custom forgot-password flow ──
        // Send WordPress's built-in "lost password" and "reset password" screens
        // on wp-login.php to our own front-end views instead, so users never see
        // the WP admin login chrome. We carry over the key/login params on the
        // reset step so the link from the email keeps working. This runs even if
        // no login page is explicitly configured (we resolve one above).
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            $reset_url = ES_Helpers::reset_page_url();
            $reset_base = ( $reset_url && $reset_url !== home_url( '/' ) ) ? $reset_url : $login_url;
            if ( $reset_base && ( $action === 'lostpassword' || $action === 'retrievepassword' ) ) {
                wp_safe_redirect( add_query_arg( 'es_action', 'lostpassword', $reset_base ) );
                exit;
            }
            if ( $reset_base && ( $action === 'rp' || $action === 'resetpass' ) ) {
                $args = array( 'es_action' => 'rp' );
                if ( isset( $_REQUEST['key'] ) ) {
                    $args['key'] = sanitize_text_field( wp_unslash( $_REQUEST['key'] ) );
                }
                if ( isset( $_REQUEST['login'] ) ) {
                    $args['login'] = sanitize_text_field( wp_unslash( $_REQUEST['login'] ) );
                }
                wp_safe_redirect( add_query_arg( $args, $reset_base ) );
                exit;
            }
        }

        // Beyond this point we only redirect plain login/register, which needs a
        // configured login page.
        if ( ! $login_id || ! $login_url ) return;

        // Only redirect default 'login' / 'register' actions; let logout, etc. work normally
        if ( ! in_array( $action, array( 'login', 'register' ), true ) ) return;

        // Skip if interrupt=1 (e.g. an admin bypass) or already POSTed (form submission)
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) return;

        $reg_id = (int) $s['register_page_id'];
        $url = ( $action === 'register' && $reg_id ) ? get_permalink( $reg_id ) : $login_url;
        if ( $url ) {
            wp_safe_redirect( $url );
            exit;
        }
    }

    /**
     * Find a published page that contains the login shortcode, so the custom
     * forgot/reset flow works even before an admin sets the login page in
     * Settings. Cached in a transient to avoid scanning on every request.
     */
    private function find_login_page_url() {
        $cached = get_transient( 'es_login_page_url' );
        if ( $cached !== false ) return $cached;

        $url = '';
        $pages = get_posts( array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'numberposts'    => 50,
            's'              => 'eduschedule_login',
            'fields'         => 'ids',
        ) );
        foreach ( $pages as $pid ) {
            $content = get_post_field( 'post_content', $pid );
            if ( has_shortcode( $content, 'eduschedule_login' ) || has_shortcode( $content, 'eduschedule_auth' ) ) {
                $url = get_permalink( $pid );
                break;
            }
        }
        // Cache for an hour (empty string is a valid "none found" result).
        set_transient( 'es_login_page_url', $url, HOUR_IN_SECONDS );
        return $url;
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
        // Route "lost password" to our own front-end login page (with a flag the
        // login shortcode detects) instead of wp-login.php. Falls back to a page
        // containing the login shortcode, then to the WP default.
        $s = ES_Helpers::settings();
        $login_id = (int) ( $s['login_page_id'] ?? 0 );
        $base = $login_id ? get_permalink( $login_id ) : '';
        if ( ! $base ) {
            $base = $this->find_login_page_url();
        }
        if ( ! $base ) return $url;
        return add_query_arg( 'es_action', 'lostpassword', $base );
    }
}
