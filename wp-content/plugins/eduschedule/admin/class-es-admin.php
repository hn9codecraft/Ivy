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
        wp_enqueue_style( 'es-admin', ES_URL . 'public/css/admin.css', array( 'dashicons' ), ES_VERSION );
        wp_enqueue_script( 'es-admin', ES_URL . 'public/js/admin.js', array( 'jquery' ), ES_VERSION, true );
        wp_enqueue_script( 'es-packages', ES_URL . 'public/js/packages.js', array( 'jquery', 'es-admin' ), ES_VERSION, true );

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

        if ( $selected && ES_Packages::get_user_category( $uid ) !== '1to1' ) {
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

        $current = ES_Helpers::settings();
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
        ) ) );

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
        update_option( 'es_settings', $settings );
        $msg = $created ? 'Created: ' . implode( ', ', $created ) : 'Pages already exist';
        wp_safe_redirect( admin_url( 'admin.php?page=eduschedule-settings&pages=' . urlencode( $msg ) ) );
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