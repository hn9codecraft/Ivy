<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

        add_action( 'admin_post_es_save_zoom',     array( $this, 'save_zoom' ) );
        add_action( 'admin_post_es_test_zoom',     array( $this, 'test_zoom' ) );
        add_action( 'admin_post_es_save_settings', array( $this, 'save_settings' ) );
        add_action( 'admin_post_es_create_pages',  array( $this, 'create_pages' ) );
        add_action( 'admin_post_es_delete_booking', array( $this, 'delete_booking' ) );
        add_action( 'admin_post_es_reset_student_data', array( $this, 'reset_student_data' ) );
    }

    public function menu() {
        $cap = ES_Helpers::admin_capability();

        add_menu_page(
            'EduSchedule', 'EduSchedule', $cap, 'eduschedule',
            array( $this, 'page_calendar' ),
            'dashicons-calendar-alt', 26
        );
        add_submenu_page( 'eduschedule', 'Calendar',         'Calendar',         $cap, 'eduschedule',           array( $this, 'page_calendar' ) );
        add_submenu_page( 'eduschedule', 'My Slots',         'My Slots',         $cap, 'eduschedule-slots',     array( $this, 'page_slots' ) );
        add_submenu_page( 'eduschedule', 'All Bookings',     'All Bookings',     $cap, 'eduschedule-bookings',  array( $this, 'page_bookings' ) );
        add_submenu_page( 'eduschedule', 'Payments',         'Payments',         $cap, 'eduschedule-payments',  array( $this, 'page_payments' ) );
        add_submenu_page( 'eduschedule', 'Students',         'Students',         $cap, 'eduschedule-students',  array( $this, 'page_students' ) );
        //add_submenu_page( 'eduschedule', 'Demo Leads',       'Demo Leads',       $cap, 'eduschedule-demo-leads', array( $this, 'page_demo_leads' ) );
        add_submenu_page( 'eduschedule', '1:1 Students',     '1:1 Students',     $cap, 'eduschedule-1to1',      array( $this, 'page_one_to_one' ) );
        add_submenu_page( 'eduschedule', 'Groups',           'Groups',           $cap, 'eduschedule-groups',    array( $this, 'page_groups' ) );
        add_submenu_page( 'eduschedule', 'Packages',         'Packages',         $cap, 'eduschedule-packages',  array( $this, 'page_packages' ) );
        add_submenu_page( 'eduschedule', 'Zoom Integration', 'Zoom Integration', $cap, 'eduschedule-zoom',      array( $this, 'page_zoom' ) );
        add_submenu_page( 'eduschedule', 'Settings',         'Settings',         $cap, 'eduschedule-settings',  array( $this, 'page_settings' ) );
    }

    public function enqueue( $hook ) {
        if ( strpos( $hook, 'eduschedule' ) === false ) return;
        wp_enqueue_style( 'dashicons' );

        // Select2 — used for the Course multi-select on the 1:1 & Group pages.
        // Loaded from a CDN (consistent with how Stripe.js is loaded on the
        // front end). If the CDN is blocked the page falls back to a plain
        // multi-select, which still works.
        wp_enqueue_style( 'es-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13' );
        wp_enqueue_script( 'es-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array( 'jquery' ), '4.0.13', true );

        wp_enqueue_style( 'es-admin', ES_URL . 'public/css/admin.css', array( 'dashicons' ), ES_VERSION );

        // Load the WordPress media uploader so admins can upload / pick videos
        // (and other media) straight from the media library (#4 — Videos tab).
        wp_enqueue_media();

        wp_enqueue_script( 'es-admin', ES_URL . 'public/js/admin.js', array( 'jquery' ), ES_VERSION, true );
        wp_enqueue_script( 'es-packages', ES_URL . 'public/js/packages.js', array( 'jquery', 'es-admin', 'es-select2' ), ES_VERSION, true );

        wp_localize_script( 'es-admin', 'ES_ADMIN', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'es_admin_nonce' ),
            'today'      => current_time( 'Y-m-d' ),
            'slot_types' => ES_Helpers::slot_types(),
            'platforms'  => ES_Helpers::platforms(),
            'work_tz'    => ES_Helpers::work_tz()->getName(),
        ) );
    }

    public function page_calendar() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        include ES_DIR . 'templates/admin-calendar.php';
    }

    public function page_slots() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        include ES_DIR . 'templates/admin-slots.php';
    }

    public function page_bookings() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        $args = array(
            'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
            'date_to'   => isset( $_GET['date_to'] )   ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) )   : '',
            'search'    => isset( $_GET['s'] )         ? sanitize_text_field( wp_unslash( $_GET['s'] ) )         : '',
        );
        $bookings = ES_DB::get_bookings_for_admin( $args );
        include ES_DIR . 'templates/admin-bookings.php';
    }

    public function page_payments() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        $args = array(
            'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'paid',
            'search' => isset( $_GET['s'] )      ? sanitize_text_field( wp_unslash( $_GET['s'] ) )      : '',
        );
        $payments = ES_Packages::get_all_payments( $args );
        include ES_DIR . 'templates/admin-payments.php';
    }

    public function page_zoom() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        $zoom = ES_Zoom::settings();
        $is_configured = ES_Zoom::is_configured();
        include ES_DIR . 'templates/admin-zoom.php';
    }

    public function page_settings() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        $settings = ES_Helpers::settings();
        include ES_DIR . 'templates/admin-settings.php';
    }

    public function page_packages() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;
        include ES_DIR . 'templates/admin-packages.php';
    }

    public function page_demo_leads() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;

        $uid = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;

        $users    = ES_Packages::get_users_by_category( 'demo' );
        $selected = $uid ? get_userdata( $uid ) : null;

        // Verify user is in demo category before selecting
        if ( $selected && ES_Packages::get_user_category( $uid ) !== 'demo' ) {
            $selected = null;
            $uid = 0;
        }

        include ES_DIR . 'templates/admin-demo-leads.php';
    }

    public function page_one_to_one() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;

        $uid = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;

        $users    = ES_Packages::get_users_by_category( '1to1' );
        $selected = $uid ? get_userdata( $uid ) : null;

        if ( $selected && ! ES_Packages::user_has_flow( $uid, '1to1' ) ) {
            $selected = null;
            $uid = 0;
        }

        include ES_DIR . 'templates/admin-1to1-students.php';
    }

    public function page_groups() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;

        $gid = isset( $_GET['group_id'] ) ? (int) $_GET['group_id'] : 0;

        $groups   = ES_Packages::get_all_groups( false );
        $selected = $gid ? ES_Packages::get_group( $gid ) : null;

        include ES_DIR . 'templates/admin-groups.php';
    }

    public function page_students() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) return;

        // v3.5.1 — Detail sub-page: ?page=eduschedule-students&view=detail&user_id=X
        $view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : '';
        if ( $view === 'detail' ) {
            $this->page_student_detail();
            return;
        }

        $filter = isset( $_GET['filter'] ) ? sanitize_key( $_GET['filter'] ) : 'all';
        global $wpdb;
        $tb = ES_DB::table( 'bookings' );

        if ( $filter === 'with_bookings' ) {
            $ids = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$tb} WHERE user_id > 0" );
            $users = $ids ? get_users( array( 'include' => $ids, 'orderby' => 'registered', 'order' => 'DESC', 'number' => 500 ) ) : array();
        } else {
            $users = get_users( array(
                'orderby' => 'registered',
                'order'   => 'DESC',
                'number'  => 500,
            ) );
        }

        $students = array();
        foreach ( $users as $u ) {
            // Skip pure admin/editor accounts on the default view (still allow if filter=all)
            $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tb} WHERE user_id = %d", $u->ID ) );
            $students[] = array(
                'ID'           => (int) $u->ID,
                'display_name' => $u->display_name,
                'email'        => $u->user_email,
                'phone'        => get_user_meta( $u->ID, 'es_phone', true ),
                'parent_name'  => get_user_meta( $u->ID, 'es_parent_name', true ),
                'reference'    => get_user_meta( $u->ID, 'es_reference', true ),
                'registered'   => $u->user_registered,
                'count'        => $count,
                'is_admin'     => user_can( $u, 'manage_options' ),
            );
        }

        include ES_DIR . 'templates/admin-students.php';
    }

    /**
     * v3.5.1 — Student Detail page (replaces the modal popup)
     * URL: admin.php?page=eduschedule-students&view=detail&user_id=X
     */
    public function page_student_detail() {
        $uid = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;
        $u   = $uid ? get_userdata( $uid ) : null;

        if ( ! $u ) {
            echo '<div class="es-admin"><div class="es-card" style="padding:24px">';
            echo '<h1>Student not found</h1>';
            echo '<p><a class="es-btn es-btn-ghost" href="' . esc_url( admin_url( 'admin.php?page=eduschedule-students' ) ) . '">&larr; Back to Students</a></p>';
            echo '</div></div>';
            return;
        }

        global $wpdb;
        $sql = "SELECT b.*, s.slot_date, s.start_time, s.end_time, s.duration_min, s.slot_type, s.platform, s.title
                FROM " . ES_DB::table( 'bookings' ) . " b
                LEFT JOIN " . ES_DB::table( 'slots' ) . " s ON s.id = b.slot_id
                WHERE b.user_id = %d
                ORDER BY s.slot_date DESC, s.start_time DESC";
        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $uid ) );

        $bookings = array();
        foreach ( (array) $rows as $b ) {
            $bookings[] = array(
                'id'             => (int) $b->id,
                'slot_id'        => (int) $b->slot_id,
                'date'           => $b->slot_date,
                'date_label'     => $b->slot_date ? date_i18n( 'M j, Y', strtotime( $b->slot_date ) ) : '—',
                'start'          => $b->start_time ? substr( $b->start_time, 0, 5 ) : '',
                'end'            => $b->end_time   ? substr( $b->end_time,   0, 5 ) : '',
                'duration'       => (int) $b->duration_min,
                'type'           => $b->slot_type,
                'type_label'     => $b->slot_type ? ES_Helpers::slot_type_label( $b->slot_type ) : '',
                'type_color'     => $b->slot_type ? ES_Helpers::slot_type_color( $b->slot_type ) : '#6b7280',
                'platform'       => $b->platform,
                'title'          => $b->title,
                'status'         => $b->status,
                'zoom_join_url'  => $b->zoom_join_url,
                'zoom_start_url' => $b->zoom_start_url,
                'created_at'     => $b->created_at,
            );
        }

        $upcoming = 0; $past = 0; $today = current_time( 'Y-m-d' );
        foreach ( $bookings as $b ) {
            if ( $b['date'] && $b['date'] >= $today ) $upcoming++; else $past++;
        }

        $student = array(
            'id'               => (int) $u->ID,
            'first_name'       => $u->first_name,
            'last_name'        => $u->last_name,
            'display_name'     => $u->display_name,
            'email'            => $u->user_email,
            'phone'            => get_user_meta( $u->ID, 'es_phone', true ),
            'parent_name'      => get_user_meta( $u->ID, 'es_parent_name', true ),
            'reference'        => get_user_meta( $u->ID, 'es_reference', true ),
            'comment'          => get_user_meta( $u->ID, 'es_comment', true ),
            'country'          => get_user_meta( $u->ID, 'es_country', true ),
            'timezone'         => get_user_meta( $u->ID, 'es_timezone', true ),
            'registered'       => $u->user_registered,
            'registered_label' => date_i18n( 'M j, Y', strtotime( $u->user_registered ) ),
            'is_admin'         => user_can( $u, 'manage_options' ),
            'category'         => ES_Packages::get_user_category( $u->ID ),
        );

        // Latest after-call outcome (if any)
        $latest_outcome = ES_Packages::get_latest_lead_outcome( $u->ID );

        $stats = array(
            'total'    => count( $bookings ),
            'upcoming' => $upcoming,
            'past'     => $past,
        );

        include ES_DIR . 'templates/admin-student-detail.php';
    }

    /* ============== HANDLERS ============== */

    public function save_zoom() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'es_save_zoom' );

        $existing = ES_Zoom::settings();
        $client_secret = isset( $_POST['client_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) : '';
        if ( $client_secret === '' && ! empty( $existing['client_secret'] ) ) {
            $client_secret = $existing['client_secret'];
        }

        update_option( 'es_zoom_settings', array(
            'enabled'       => ! empty( $_POST['enabled'] ) ? 1 : 0,
            'account_id'    => isset( $_POST['account_id'] ) ? sanitize_text_field( wp_unslash( $_POST['account_id'] ) ) : '',
            'client_id'     => isset( $_POST['client_id'] )  ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) )  : '',
            'client_secret' => $client_secret,
            'host_email'    => isset( $_POST['host_email'] ) ? sanitize_email( wp_unslash( $_POST['host_email'] ) )      : '',
            'auto_record'   => ! empty( $_POST['auto_record'] ) ? 1 : 0,
            'waiting_room'  => ! empty( $_POST['waiting_room'] ) ? 1 : 0,
            'last_error'    => $existing['last_error'] ?? '',
            'last_test_ok'  => $existing['last_test_ok'] ?? 0,
        ) );
        delete_transient( ES_Zoom::TOKEN_OPT );
        wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-zoom&saved=1' ) );
        exit;
    }

    public function test_zoom() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'es_test_zoom' );
        $result = ES_Zoom::test_credentials();
        if ( is_wp_error( $result ) ) {
            $url = add_query_arg( array( 'page' => 'eduschedule-zoom', 'tested' => 'fail', 'msg' => urlencode( $result->get_error_message() ) ), admin_url( 'admin.php' ) );
        } else {
            $email = isset( $result['email'] ) ? $result['email'] : 'OK';
            $url = add_query_arg( array( 'page' => 'eduschedule-zoom', 'tested' => 'ok', 'msg' => urlencode( $email ) ), admin_url( 'admin.php' ) );
        }
        wp_safe_redirect( $url );
        exit;
    }

    public function save_settings() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'es_save_settings' );

        $work_country = isset( $_POST['work_country'] ) ? strtoupper( substr( sanitize_text_field( wp_unslash( $_POST['work_country'] ) ), 0, 2 ) ) : 'IN';
        $work_tz = ES_Helpers::tz_for_country( $work_country );

        $platforms_raw = isset( $_POST['platforms'] ) ? sanitize_textarea_field( wp_unslash( $_POST['platforms'] ) ) : 'Zoom';
        $platforms = array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', $platforms_raw ) ) ) );
        if ( empty( $platforms ) ) $platforms = array( 'Zoom' );

        // Currency
        $allowed_currencies = array_keys( ES_Helpers::currencies() );
        $default_currency = isset( $_POST['default_currency'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['default_currency'] ) ) ) : 'INR';
        if ( ! in_array( $default_currency, $allowed_currencies, true ) ) $default_currency = 'INR';

        $yearly_discount = isset( $_POST['yearly_discount'] ) ? floatval( $_POST['yearly_discount'] ) : 0;
        $yearly_discount = max( 0, min( 100, $yearly_discount ) );

        $yearly_discount_months = isset( $_POST['yearly_discount_months'] ) ? (int) $_POST['yearly_discount_months'] : 12;
        $yearly_discount_months = max( 1, min( 60, $yearly_discount_months ) );

        // Stripe
        $stripe_mode = isset( $_POST['stripe_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_mode'] ) ) : 'test';
        if ( ! in_array( $stripe_mode, array( 'test', 'live' ), true ) ) $stripe_mode = 'test';

        $current = ES_Helpers::settings();

        // Secrets: if posted field is empty AND we already have one stored, keep the existing value
        $kept_test_secret    = ( isset( $_POST['stripe_test_secret'] )    && $_POST['stripe_test_secret']    !== '' ) ? sanitize_text_field( wp_unslash( $_POST['stripe_test_secret'] ) )    : $current['stripe_test_secret'];
        $kept_live_secret    = ( isset( $_POST['stripe_live_secret'] )    && $_POST['stripe_live_secret']    !== '' ) ? sanitize_text_field( wp_unslash( $_POST['stripe_live_secret'] ) )    : $current['stripe_live_secret'];
        $kept_webhook_secret = ( isset( $_POST['stripe_webhook_secret'] ) && $_POST['stripe_webhook_secret'] !== '' ) ? sanitize_text_field( wp_unslash( $_POST['stripe_webhook_secret'] ) ) : $current['stripe_webhook_secret'];

        // SMTP password: keep the stored value if the field was left blank.
        $kept_smtp_password = ( isset( $_POST['smtp_password'] ) && $_POST['smtp_password'] !== '' )
            ? trim( (string) wp_unslash( $_POST['smtp_password'] ) )
            : ( isset( $current['smtp_password'] ) ? $current['smtp_password'] : '' );

        $smtp_encryption = isset( $_POST['smtp_encryption'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_encryption'] ) ) : 'tls';
        if ( ! in_array( $smtp_encryption, array( 'tls', 'ssl', 'none' ), true ) ) $smtp_encryption = 'tls';

        $smtp_port = isset( $_POST['smtp_port'] ) ? (int) $_POST['smtp_port'] : 587;
        if ( $smtp_port < 1 || $smtp_port > 65535 ) $smtp_port = 587;

        update_option( 'es_settings', array_merge( $current, array(
            'site_name'        => isset( $_POST['site_name'] ) ? sanitize_text_field( wp_unslash( $_POST['site_name'] ) ) : get_bloginfo( 'name' ),
            'work_country'     => $work_country,
            'work_timezone'    => $work_tz,
            'platforms'        => $platforms,
            'default_platform' => isset( $_POST['default_platform'] ) ? sanitize_text_field( wp_unslash( $_POST['default_platform'] ) ) : $platforms[0],
            'register_open'    => ! empty( $_POST['register_open'] ) ? 1 : 0,
            'login_page_id'    => isset( $_POST['login_page_id'] ) ? (int) $_POST['login_page_id'] : 0,
            'register_page_id' => isset( $_POST['register_page_id'] ) ? (int) $_POST['register_page_id'] : 0,
            'dashboard_page_id'=> isset( $_POST['dashboard_page_id'] ) ? (int) $_POST['dashboard_page_id'] : 0,
            'reset_page_id'    => isset( $_POST['reset_page_id'] ) ? (int) $_POST['reset_page_id'] : 0,

            // Currency / Billing
            'default_currency' => $default_currency,
            'yearly_discount'        => $yearly_discount,
            'yearly_discount_months' => $yearly_discount_months,
            'enable_yearly'          => ! empty( $_POST['enable_yearly'] ) ? 1 : 0,

            // Stripe
            'stripe_enabled'        => ! empty( $_POST['stripe_enabled'] ) ? 1 : 0,
            'stripe_mode'           => $stripe_mode,
            'stripe_test_pub_key'   => isset( $_POST['stripe_test_pub_key'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_test_pub_key'] ) ) : '',
            'stripe_test_secret'    => $kept_test_secret,
            'stripe_live_pub_key'   => isset( $_POST['stripe_live_pub_key'] ) ? sanitize_text_field( wp_unslash( $_POST['stripe_live_pub_key'] ) ) : '',
            'stripe_live_secret'    => $kept_live_secret,
            'stripe_webhook_secret' => $kept_webhook_secret,

            // Email / Notifications
            'from_name'          => isset( $_POST['from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['from_name'] ) ) : get_bloginfo( 'name' ),
            'from_email'         => isset( $_POST['from_email'] ) ? sanitize_email( wp_unslash( $_POST['from_email'] ) ) : '',
            'reply_to'           => isset( $_POST['reply_to'] ) ? sanitize_email( wp_unslash( $_POST['reply_to'] ) ) : '',
            'notify_admin'       => ! empty( $_POST['notify_admin'] ) ? 1 : 0,
            'admin_notify_email' => isset( $_POST['admin_notify_email'] ) ? sanitize_email( wp_unslash( $_POST['admin_notify_email'] ) ) : '',

            // SMTP
            'smtp_enabled'    => ! empty( $_POST['smtp_enabled'] ) ? 1 : 0,
            'smtp_host'       => isset( $_POST['smtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_host'] ) ) : '',
            'smtp_port'       => $smtp_port,
            'smtp_encryption' => $smtp_encryption,
            'smtp_auth'       => ! empty( $_POST['smtp_auth'] ) ? 1 : 0,
            'smtp_username'   => isset( $_POST['smtp_username'] ) ? sanitize_text_field( wp_unslash( $_POST['smtp_username'] ) ) : '',
            'smtp_password'   => $kept_smtp_password,
        ) ) );

        // Login/register/dashboard page IDs may have changed — bust the cached
        // page-URL lookups so the reset/login flow resolves immediately.
        delete_transient( 'es_login_page_url' );
        delete_transient( 'es_reset_page_url' );

        wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-settings&saved=1' ) );
        exit;
    }

    /** Auto-create the 3 frontend pages with shortcodes */
    public function create_pages() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'es_create_pages' );
        $created = array();
        $pages = array(
            'login_page_id'     => array( 'title' => 'Login',     'shortcode' => '[eduschedule_login]' ),
            'register_page_id'  => array( 'title' => 'Register',  'shortcode' => '[eduschedule_register]' ),
            'dashboard_page_id' => array( 'title' => 'Dashboard', 'shortcode' => '[eduschedule_dashboard]' ),
        );
        $settings = ES_Helpers::settings();
        foreach ( $pages as $key => $info ) {
            if ( ! empty( $settings[ $key ] ) && get_post( $settings[ $key ] ) ) continue;
            $id = wp_insert_post( array(
                'post_title'   => $info['title'],
                'post_content' => $info['shortcode'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ) );
            if ( $id && ! is_wp_error( $id ) ) {
                $settings[ $key ] = $id;
                $created[] = $info['title'];
            }
        }

        // Dedicated self-service password-reset page. We try to track its ID
        // in the reset_page_id setting (v4.4.2). If none exists yet, create it.
        $has_reset_page = false;
        if ( ! empty( $settings['reset_page_id'] ) && ( $rp = get_post( (int) $settings['reset_page_id'] ) ) && $rp->post_status === 'publish' ) {
            $has_reset_page = true;
        } else {
            foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 100, 'fields' => 'ids' ) ) as $pid ) {
                if ( has_shortcode( get_post_field( 'post_content', $pid ), 'eduschedule_reset' ) || has_shortcode( get_post_field( 'post_content', $pid ), 'es_reset_password_form' ) ) {
                    $settings['reset_page_id'] = $pid;
                    $has_reset_page = true;
                    break;
                }
            }
        }
        if ( ! $has_reset_page ) {
            $rid = wp_insert_post( array(
                'post_title'   => 'Reset Password',
                'post_content' => '[eduschedule_reset]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ) );
            if ( $rid && ! is_wp_error( $rid ) ) {
                $settings['reset_page_id'] = $rid;
                $created[] = 'Reset Password';
            }
        }

        update_option( 'es_settings', $settings );
        // New auth pages may have been created — bust the cached lookups so the
        // reset/login flow resolves the new pages immediately.
        delete_transient( 'es_login_page_url' );
        delete_transient( 'es_reset_page_url' );
        $msg = $created ? 'Created: ' . implode( ', ', $created ) : 'Pages already exist';
        wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-settings&pages=' . urlencode( $msg ) ) );
        exit;
    }


    /**
     * Danger-zone reset: remove EduSchedule operational student/group data while
     * preserving WordPress users and package master records.
     */
    public function reset_student_data() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'es_reset_student_data' );

        $confirm = isset( $_POST['es_reset_confirm'] ) ? strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['es_reset_confirm'] ) ) ) ) : '';
        if ( $confirm !== 'RESET' ) {
            wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-settings&reset_error=confirm' ) );
            exit;
        }

        global $wpdb;

        $tables_to_clear = array(
            'es_bookings',
            'es_slots',
            'es_payments',
            'es_lead_packages',
            'es_groups',
            'es_group_members',
            'es_session_files',
            'es_attendance',
            'es_videos',
        );

        foreach ( $tables_to_clear as $table ) {
            $full = $wpdb->prefix . $table;
            $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full ) );
            if ( $exists === $full ) {
                $wpdb->query( "DELETE FROM `{$full}`" );
                $wpdb->query( "ALTER TABLE `{$full}` AUTO_INCREMENT = 1" );
            }
        }

        // Keep WP users, but remove EduSchedule student/group assignment/meta so
        // existing users can be converted again from a clean state.
        $meta_keys = array(
            ES_Packages::META_CATEGORY,
            ES_Packages::META_PACKAGE_ID,
            ES_Packages::META_GROUP_ID,
            ES_Packages::META_TOKEN,
            ES_Packages::META_TOKEN_EXP,
            ES_Packages::META_STAGED,
            'es_phone',
            'es_parent',
            'es_parent_name',
            'es_reference',
            'es_source',
            'es_goal',
            'es_band',
            'es_notes',
        );
        foreach ( $meta_keys as $meta_key ) {
            delete_metadata( 'user', 0, $meta_key, '', true );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-settings&reset_done=1' ) );
        exit;
    }

    public function delete_booking() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'es_delete_booking' );
        $id = isset( $_POST['booking_id'] ) ? (int) $_POST['booking_id'] : 0;
        if ( $id ) {
            $b = ES_DB::get_booking( $id );
            if ( $b && ! empty( $b->zoom_meeting_id ) ) {
                @ES_Zoom::delete_meeting( $b->zoom_meeting_id );
            }
            ES_DB::delete_booking( $id );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-bookings&deleted=1' ) );
        exit;
    }
}