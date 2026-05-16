<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Shortcodes {

    public function __construct() {
        add_shortcode( 'eduschedule_login',        array( $this, 'login' ) );
        add_shortcode( 'eduschedule_register',     array( $this, 'register' ) );
        add_shortcode( 'eduschedule_auth',         array( $this, 'auth' ) ); // NEW: combined login + register toggle
        add_shortcode( 'eduschedule_dashboard',    array( $this, 'dashboard' ) );
        add_shortcode( 'eduschedule_packages',     array( $this, 'packages' ) );
        add_shortcode( 'course_booking_calendar',  array( $this, 'public_calendar' ) );
        add_shortcode( 'eduschedule_calendar',     array( $this, 'public_calendar' ) ); // alias
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
    }

    public function enqueue() {
        //if ( ! $this->page_uses_shortcode() ) return;
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'es-frontend', ES_URL . 'public/css/frontend.css', array( 'dashicons' ), ES_VERSION );
        wp_enqueue_script( 'es-frontend', ES_URL . 'public/js/frontend.js', array( 'jquery' ), ES_VERSION, true );
        // Packages JS for public package selection (also handles admin pages safely via class checks)
        wp_enqueue_script( 'es-packages', ES_URL . 'public/js/packages.js', array( 'jquery', 'es-frontend' ), ES_VERSION, true );
        wp_localize_script( 'es-frontend', 'ES_FE', array(
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'es_fe_nonce' ),         // generic nonce for booking, etc.
            'login_nonce'   => wp_create_nonce( 'es_login_nonce' ),      // dedicated login nonce
            'register_nonce'=> wp_create_nonce( 'es_register_nonce' ),   // dedicated register nonce
            'is_logged'     => is_user_logged_in(),
            'user_id'       => get_current_user_id(),
            'login_url'     => $this->page_url( 'login_page_id' ),
            'register_url'  => $this->page_url( 'register_page_id' ),
            'dashboard_url' => $this->page_url( 'dashboard_page_id' ),
            'current_url'   => $this->get_current_url(),
            'countries'     => ES_Helpers::countries(),
            'slot_types'    => ES_Helpers::slot_types(),
            'today'         => current_time( 'Y-m-d' ),
        ) );
    }

    /**
     * Get the current page URL (with query string) so we can stay on the same page after login/register.
     */
    private function get_current_url() {
        if ( ! isset( $_SERVER['REQUEST_URI'] ) ) return home_url();
        $scheme = ( is_ssl() ? 'https' : 'http' );
        $host   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : parse_url( home_url(), PHP_URL_HOST );
        return esc_url_raw( $scheme . '://' . $host . $_SERVER['REQUEST_URI'] );
    }

    private function page_uses_shortcode() {
        global $post;
        if ( ! $post ) return false;
        return has_shortcode( $post->post_content, 'eduschedule_login' )
            || has_shortcode( $post->post_content, 'eduschedule_register' )
            || has_shortcode( $post->post_content, 'eduschedule_auth' )
            || has_shortcode( $post->post_content, 'eduschedule_dashboard' )
            || has_shortcode( $post->post_content, 'course_booking_calendar' )
            || has_shortcode( $post->post_content, 'eduschedule_calendar' );
    }

    private function page_url( $key ) {
        $s = ES_Helpers::settings();
        $id = (int) ( $s[ $key ] ?? 0 );
        return $id ? get_permalink( $id ) : '';
    }

    public function login() {
        if ( is_user_logged_in() ) {
            $s = ES_Helpers::settings();
            $url = ! empty( $s['dashboard_page_id'] ) ? get_permalink( $s['dashboard_page_id'] ) : home_url();
            return '<div class="es-fe es-redirect"><p>You are already logged in. <a href="' . esc_url( $url ) . '">Go to dashboard →</a></p></div>';
        }
        ob_start();
        include ES_DIR . 'templates/frontend-login.php';
        return ob_get_clean();
    }

    public function register() {
        if ( is_user_logged_in() ) {
            $s = ES_Helpers::settings();
            $url = ! empty( $s['dashboard_page_id'] ) ? get_permalink( $s['dashboard_page_id'] ) : home_url();
            return '<div class="es-fe es-redirect"><p>You are already logged in. <a href="' . esc_url( $url ) . '">Go to dashboard →</a></p></div>';
        }
        $s = ES_Helpers::settings();
        if ( empty( $s['register_open'] ) ) {
            return '<div class="es-fe es-redirect"><p>Registration is currently closed. Please contact the admin.</p></div>';
        }
        ob_start();
        include ES_DIR . 'templates/frontend-register.php';
        return ob_get_clean();
    }

    /**
     * Combined Login + Register shortcode (toggle on same page).
     * Usage:
     *   [eduschedule_auth]
     *   [eduschedule_auth default="register"]
     *   [eduschedule_auth logged_in_message="You are logged in."]
     *
     * After successful login/register, the user STAYS on the SAME page (with all query params preserved).
     */
    public function auth( $atts = array() ) {
        $atts = shortcode_atts( array(
            'default'            => 'login', // 'login' or 'register'
            'logged_in_message'  => '',
            'show_logged_in_box' => 'yes',
        ), $atts, 'eduschedule_auth' );

        if ( is_user_logged_in() ) {
            if ( $atts['show_logged_in_box'] !== 'yes' ) return '';
            $user = wp_get_current_user();
            $msg = $atts['logged_in_message']
                ? $atts['logged_in_message']
                : sprintf( 'You are logged in as %s.', $user->display_name );
            return '<div class="es-fe es-auth-loggedin"><p>' . esc_html( $msg ) . '</p></div>';
        }

        $s = ES_Helpers::settings();
        $register_open = ! empty( $s['register_open'] );
        $countries = ES_Helpers::countries();

        ob_start();
        include ES_DIR . 'templates/frontend-auth.php';
        return ob_get_clean();
    }

    public function dashboard() {
        if ( ! is_user_logged_in() ) {
            $s = ES_Helpers::settings();
            $url = ! empty( $s['login_page_id'] ) ? get_permalink( $s['login_page_id'] ) : wp_login_url( get_permalink() );
            return '<div class="es-fe es-redirect"><p>Please <a href="' . esc_url( $url ) . '">log in</a> to view your dashboard.</p></div>';
        }
        ob_start();
        include ES_DIR . 'templates/frontend-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Public packages shortcode
     * Usage: [eduschedule_packages]
     */
    public function packages() {
        ob_start();
        include ES_DIR . 'templates/public-packages.php';
        return ob_get_clean();
    }

    /**
     * Public booking calendar — anyone can browse, must log in to book.
     * Usage: [course_booking_calendar title="Book a Session" types="1to1,group,open" months_ahead="3"]
     */
    public function public_calendar( $atts = array() ) {
        $atts = shortcode_atts( array(
            'title'        => 'Course Dates and Enrollment Times',
            'subtitle'     => '',
            'types'        => '1to1,group,open',  // comma-separated, 'personal' is always blocked
            'months_ahead' => 12,
            'show_legend'  => 'yes',
            'allow_multi'  => 'yes',  // yes|no - allow multi-date selection
        ), $atts, 'course_booking_calendar' );

        // Sanitize types
        $allowed = array_intersect(
            array_map( 'trim', explode( ',', $atts['types'] ) ),
            array( '1to1', 'group', 'open' )  // never include 'personal' here
        );
        if ( empty( $allowed ) ) $allowed = array( '1to1', 'group', 'open' );

        ob_start();
        include ES_DIR . 'templates/frontend-public-calendar.php';
        $html = ob_get_clean();

        return $html;
    }
}
