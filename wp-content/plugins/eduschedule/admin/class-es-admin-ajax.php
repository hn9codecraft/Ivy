<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Admin_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_es_admin_calendar_month',  array( $this, 'calendar_month' ) );
        add_action( 'wp_ajax_es_admin_day_slots',       array( $this, 'day_slots' ) );
        add_action( 'wp_ajax_es_admin_create_slot',     array( $this, 'create_slot' ) );
        add_action( 'wp_ajax_es_admin_update_slot',     array( $this, 'update_slot' ) );
        add_action( 'wp_ajax_es_admin_delete_slot',     array( $this, 'delete_slot' ) );
        add_action( 'wp_ajax_es_admin_all_slots',       array( $this, 'all_slots' ) );
        add_action( 'wp_ajax_es_admin_search_users',    array( $this, 'search_users' ) );
        add_action( 'wp_ajax_es_admin_bookable_slots',  array( $this, 'bookable_slots' ) );
        add_action( 'wp_ajax_es_admin_create_booking',  array( $this, 'create_booking' ) );

        // v3.5.0 — Calendar direct meeting + Students tab
        add_action( 'wp_ajax_es_admin_create_meeting',  array( $this, 'create_meeting' ) );
        add_action( 'wp_ajax_es_admin_add_student',     array( $this, 'add_student' ) );
        add_action( 'wp_ajax_es_admin_student_details', array( $this, 'student_details' ) );
        
        // Packages Module
        add_action( 'wp_ajax_es_admin_save_package',    array( $this, 'save_package' ) );
        add_action( 'wp_ajax_es_admin_get_package',     array( $this, 'get_package' ) );
        add_action( 'wp_ajax_es_admin_delete_package',  array( $this, 'delete_package' ) );
        add_action( 'wp_ajax_es_after_call_convert',    array( $this, 'after_call_convert' ) );

        // Email diagnostics
        add_action( 'wp_ajax_es_admin_send_test_email', array( $this, 'send_test_email' ) );

        // Groups Module
        add_action( 'wp_ajax_es_admin_save_group',      array( $this, 'save_group' ) );
        add_action( 'wp_ajax_es_admin_get_group',       array( $this, 'get_group' ) );
        add_action( 'wp_ajax_es_admin_delete_group',    array( $this, 'delete_group' ) );
        add_action( 'wp_ajax_es_admin_remove_group_member', array( $this, 'remove_group_member' ) );
        add_action( 'wp_ajax_es_admin_add_group_member',    array( $this, 'add_group_member' ) );

        // Session files (uploads) + meeting scheduling for 1:1 / Group sessions
        add_action( 'wp_ajax_es_admin_upload_session_file', array( $this, 'upload_session_file' ) );
        add_action( 'wp_ajax_es_admin_list_session_files',  array( $this, 'list_session_files' ) );
        add_action( 'wp_ajax_es_admin_delete_session_file', array( $this, 'delete_session_file' ) );
        add_action( 'wp_ajax_es_admin_schedule_session',    array( $this, 'schedule_session' ) );
        // v4.5 — Edit / delete an already-scheduled session slot
        add_action( 'wp_ajax_es_admin_edit_schedule_session',   array( $this, 'edit_schedule_session' ) );
        add_action( 'wp_ajax_es_admin_delete_schedule_session', array( $this, 'delete_schedule_session' ) );
        // v4.5 — Global file/video upload (not tied to a specific slot)
        add_action( 'wp_ajax_es_admin_global_upload',       array( $this, 'global_upload' ) );
        // v4.5 — Renew / extend a student's package
        add_action( 'wp_ajax_es_admin_renew_package',       array( $this, 'renew_package' ) );

        // Tabbed detail UI: attendance, videos, student profile
        add_action( 'wp_ajax_es_admin_save_attendance', array( $this, 'save_attendance' ) );
        add_action( 'wp_ajax_es_admin_save_group_attendance_bulk', array( $this, 'save_group_attendance_bulk' ) );
        add_action( 'wp_ajax_es_admin_add_video',       array( $this, 'add_video' ) );
        add_action( 'wp_ajax_es_admin_delete_video',    array( $this, 'delete_video' ) );
        add_action( 'wp_ajax_es_admin_add_package_video',  array( $this, 'add_package_video' ) );
        add_action( 'wp_ajax_es_admin_list_package_videos', array( $this, 'list_package_videos' ) );
        add_action( 'wp_ajax_es_admin_upload_package_file', array( $this, 'upload_package_file' ) );
        add_action( 'wp_ajax_es_admin_save_student_profile', array( $this, 'save_student_profile' ) );
        add_action( 'wp_ajax_es_admin_save_student_courses', array( $this, 'save_student_courses' ) );
        add_action( 'wp_ajax_es_admin_save_group_courses',   array( $this, 'save_group_courses' ) );

        // Public package selection
        add_action( 'wp_ajax_es_student_select_package', array( $this, 'student_select_package' ) );
        add_action( 'wp_ajax_nopriv_es_student_select_package', array( $this, 'student_select_package' ) );

        // Stripe Checkout (hosted page — kept as fallback)
        add_action( 'wp_ajax_es_stripe_create_checkout',        array( $this, 'stripe_create_checkout' ) );
        add_action( 'wp_ajax_nopriv_es_stripe_create_checkout', array( $this, 'stripe_create_checkout' ) );

        // Stripe Elements (inline payment form)
        add_action( 'wp_ajax_es_stripe_create_intent',        array( $this, 'stripe_create_intent' ) );
        add_action( 'wp_ajax_nopriv_es_stripe_create_intent', array( $this, 'stripe_create_intent' ) );
        add_action( 'wp_ajax_es_stripe_finalize_intent',        array( $this, 'stripe_finalize_intent' ) );
        add_action( 'wp_ajax_nopriv_es_stripe_finalize_intent', array( $this, 'stripe_finalize_intent' ) );
    }

    private function check() {
        if ( ! current_user_can( ES_Helpers::admin_capability() ) ) wp_send_json_error( array( 'message' => 'Forbidden' ) );
        check_ajax_referer( 'es_admin_nonce', 'nonce' );
    }

    /** Get calendar month with slots grouped */
    public function calendar_month() {
        $this->check();
        $year  = isset( $_POST['year'] )  ? (int) $_POST['year']  : (int) current_time( 'Y' );
        $month = isset( $_POST['month'] ) ? (int) $_POST['month'] : (int) current_time( 'n' );
        if ( $month < 1 || $month > 12 ) wp_send_json_error( array( 'message' => 'Invalid month' ) );

        $days_in = (int) date( 't', strtotime( "$year-$month-01" ) );
        $from = sprintf( '%04d-%02d-01', $year, $month );
        $to   = sprintf( '%04d-%02d-%02d', $year, $month, $days_in );

        // Calendar shows ONLY booked schedules (slots that have at least one
        // confirmed booking). Empty availability slots are hidden here.
        $slots = ES_DB::get_slots_in_range_calendar( $from, $to );

        $by_date = array();
        foreach ( $slots as $s ) {
            $iso = $s->slot_date;
            if ( ! isset( $by_date[ $iso ] ) ) $by_date[ $iso ] = array();
            $by_date[ $iso ][] = array(
                'id'         => (int) $s->id,
                'start'      => substr( $s->start_time, 0, 5 ),
                'end'        => substr( $s->end_time, 0, 5 ),
                'duration'   => (int) $s->duration_min,
                'type'       => $s->slot_type,
                'type_label' => ES_Helpers::slot_type_label( $s->slot_type ),
                'type_color' => ES_Helpers::slot_type_color( $s->slot_type ),
                'capacity'   => (int) $s->capacity,
                'booked'     => (int) $s->booked_count,
                'platform'   => $s->platform,
                'title'      => $s->title,
                'user_name'  => isset( $s->user_name ) ? $s->user_name : '',
                'user_email' => isset( $s->user_email ) ? $s->user_email : '',
            );
        }

        wp_send_json_success( array(
            'year' => $year, 'month' => $month,
            'month_name' => date_i18n( 'F Y', strtotime( $from ) ),
            'first_weekday' => (int) date( 'w', strtotime( $from ) ),
            'days_in' => $days_in,
            'today' => current_time( 'Y-m-d' ),
            'days' => $by_date,
        ) );
    }

    /** Get all slots for a single day with full detail */
    public function day_slots() {

        //$this->check();
        $date = isset( $_POST['date'] )? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
        if ( ! ES_Helpers::valid_date( $date ) ) {
            wp_send_json_error( array(
                'message' => 'Invalid date'
            ) );
        }
        $slots = ES_DB::get_slots_for_date( $date );
        $out = array();
        foreach ( $slots as $s ) {
            $out[] = array(
                'id'         => (int) $s->id,
                'date'       => $s->slot_date,
                'start'      => substr( $s->start_time, 0, 5 ),
                'end'        => substr( $s->end_time, 0, 5 ),
                'duration'   => (int) $s->duration_min,
                'type'       => $s->slot_type,
                'type_label' => ES_Helpers::slot_type_label( $s->slot_type ),
                'type_color' => ES_Helpers::slot_type_color( $s->slot_type ),
                'capacity'   => (int) $s->capacity,
                'booked'     => (int) $s->booked_count,
                'platform'   => $s->platform,
                'title'      => $s->title,
                'notes'      => $s->notes,
                'user_name'  => isset( $s->user_name ) ? $s->user_name : '',
                'user_email' => isset( $s->user_email ) ? $s->user_email : '',
            );
        }
        wp_send_json_success( array(
            'date'  => $date,
            'slots' => $out
        ) );
    }

    /** Get all defined slots (for "My Slots" right-side list) */
    public function all_slots() {
        $this->check();
        $today = current_time( 'Y-m-d' );
        $upcoming_only = ! empty( $_POST['upcoming_only'] );
        global $wpdb;
        $where = $upcoming_only ? $wpdb->prepare( "WHERE s.slot_date >= %s", $today ) : '';
        $sql = "SELECT s.*, COUNT(b.id) AS booked_count
                FROM " . ES_DB::table( 'slots' ) . " s
                LEFT JOIN " . ES_DB::table( 'bookings' ) . " b ON b.slot_id = s.id AND b.status='confirmed'
                $where
                GROUP BY s.id
                ORDER BY s.slot_date ASC, s.start_time ASC
                LIMIT 200";
        $rows = $wpdb->get_results( $sql );
        $out = array();
        foreach ( $rows as $s ) {
            $out[] = array(
                'id'         => (int) $s->id,
                'date'       => $s->slot_date,
                'start'      => substr( $s->start_time, 0, 5 ),
                'end'        => substr( $s->end_time, 0, 5 ),
                'duration'   => (int) $s->duration_min,
                'type'       => $s->slot_type,
                'type_label' => ES_Helpers::slot_type_label( $s->slot_type ),
                'type_color' => ES_Helpers::slot_type_color( $s->slot_type ),
                'capacity'   => (int) $s->capacity,
                'booked'     => (int) $s->booked_count,
                'platform'   => $s->platform,
                'title'      => $s->title,
            );
        }
        wp_send_json_success( array( 'slots' => $out ) );
    }

    public function create_slot() {
        $this->check();
        $data = $this->parse_slot_input();
        if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ) );

        // Block duplicate exact start time on the same date
        if ( $this->slot_exists_at( $data['slot_date'], $data['start_time'] ) ) {
            wp_send_json_error( array( 'message' => 'A slot already exists at this date and start time. Pick a different time.' ) );
        }

        $id = ES_DB::insert_slot( $data );
        if ( ! $id ) wp_send_json_error( array( 'message' => 'Could not save slot.' ) );
        wp_send_json_success( array( 'id' => $id, 'message' => 'Slot added.' ) );
    }

    public function update_slot() {
        $this->check();
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $id || ! ES_DB::get_slot( $id ) ) wp_send_json_error( array( 'message' => 'Slot not found.' ) );
        $data = $this->parse_slot_input();
        if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ) );

        // Block duplicate exact start time, but ignore self when editing
        if ( $this->slot_exists_at( $data['slot_date'], $data['start_time'], $id ) ) {
            wp_send_json_error( array( 'message' => 'Another slot already exists at this date and start time.' ) );
        }

        unset( $data['created_by'] );
        ES_DB::update_slot( $id, $data );
        wp_send_json_success( array( 'id' => $id, 'message' => 'Slot updated.' ) );
    }

    /** Check whether a slot already exists at the given date and start_time. Optional $exclude_id ignores a row. */
    private function slot_exists_at( $date, $start_time, $exclude_id = 0 ) {
        global $wpdb;
        $sql = "SELECT id FROM " . ES_DB::table( 'slots' ) . " WHERE slot_date = %s AND start_time = %s";
        $params = array( $date, $start_time );
        if ( $exclude_id ) {
            $sql .= " AND id != %d";
            $params[] = (int) $exclude_id;
        }
        $sql .= " LIMIT 1";
        return (bool) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
    }

    public function delete_slot() {
        $this->check();
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $id ) wp_send_json_error( array( 'message' => 'Invalid slot.' ) );
        $slot = ES_DB::get_slot( $id );
        if ( ! $slot ) wp_send_json_error( array( 'message' => 'Slot not found.' ) );

        // If slot has bookings with Zoom meetings, delete those Zoom meetings
        global $wpdb;
        $bookings = $wpdb->get_results( $wpdb->prepare(
            "SELECT zoom_meeting_id FROM " . ES_DB::table( 'bookings' ) . " WHERE slot_id = %d AND zoom_meeting_id IS NOT NULL",
            $id
        ) );
        foreach ( $bookings as $b ) {
            if ( ! empty( $b->zoom_meeting_id ) ) @ES_Zoom::delete_meeting( $b->zoom_meeting_id );
        }

        // Capture who was booked on this slot BEFORE deleting, so we can refund
        // their consumed sessions afterwards (deleting a scheduled meeting frees
        // the session it had consumed under the v4.3 schedule-consumes model).
        $affected_user_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT user_id FROM " . ES_DB::table( 'bookings' ) . " WHERE slot_id = %d",
            $id
        ) );
        $affected_group_ids = array();
        if ( $slot->slot_type === 'group' ) {
            if ( ! empty( $slot->group_id ) ) {
                $affected_group_ids = array( (int) $slot->group_id );
            } elseif ( ! empty( $affected_user_ids ) ) {
                $gm = $wpdb->prefix . 'es_group_members';
                $in = implode( ',', array_map( 'intval', $affected_user_ids ) );
                $affected_group_ids = $wpdb->get_col(
                    "SELECT DISTINCT group_id FROM {$gm} WHERE user_id IN ({$in})"
                );
            }
        }

        ES_DB::delete_slot( $id );

        // Recompute session usage now the meeting (and its bookings) are gone.
        if ( $slot->slot_type === '1to1' ) {
            foreach ( (array) $affected_user_ids as $uid ) {
                ES_Packages::recount_used_sessions( (int) $uid );
            }
        } elseif ( $slot->slot_type === 'group' ) {
            foreach ( (array) $affected_group_ids as $gid ) {
                ES_Packages::recount_group_used_sessions( (int) $gid );
            }
        }

        wp_send_json_success( array( 'message' => 'Slot deleted.' ) );
    }

    /** Validate + sanitize slot data from POST */
    private function parse_slot_input() {
        $date     = isset( $_POST['slot_date'] )  ? sanitize_text_field( wp_unslash( $_POST['slot_date'] ) )  : '';
        $start    = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
        $duration = isset( $_POST['duration_min'] ) ? (int) $_POST['duration_min'] : 60;
        $type     = isset( $_POST['slot_type'] ) ? sanitize_text_field( wp_unslash( $_POST['slot_type'] ) ) : '1to1';
        $capacity = isset( $_POST['capacity'] ) ? max( 1, (int) $_POST['capacity'] ) : 1;
        $platform = isset( $_POST['platform'] ) ? sanitize_text_field( wp_unslash( $_POST['platform'] ) ) : 'Zoom';
        $title    = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $notes    = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
        $course_id = isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : 0;

        if ( ! ES_Helpers::valid_date( $date ) ) return new WP_Error( 'es_bad_date', 'Invalid date.' );
        if ( ! preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $start ) ) return new WP_Error( 'es_bad_time', 'Invalid start time.' );
        if ( $duration < 5 || $duration > 1440 ) return new WP_Error( 'es_bad_dur', 'Duration must be between 5 and 1440 min.' );

        $start_hhmm = substr( $start, 0, 5 );
        $end_hhmm = ES_Helpers::calc_end_time( $start_hhmm, $duration );

        $valid_types = array_keys( ES_Helpers::slot_types() );
        if ( ! in_array( $type, $valid_types, true ) ) $type = '1to1';

        // Force capacity 1 for 1to1
        if ( $type === '1to1' ) $capacity = 1;
        if ( $type === 'personal' ) $capacity = 1;

        return array(
            'slot_date'    => $date,
            'start_time'   => $start_hhmm . ':00',
            'end_time'     => $end_hhmm . ':00',
            'duration_min' => $duration,
            'slot_type'    => $type,
            'capacity'     => $capacity,
            'platform'     => $platform,
            'title'        => $title,
            'notes'        => $notes,
            'course_id'    => $course_id ?: null,
            'created_by'   => get_current_user_id(),
        );
    }

    /* ============== ADMIN MANUAL BOOKING ============== */

    /** Search WP users for autocomplete */
    public function search_users() {
        $this->check();
        $q = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
        if ( strlen( $q ) < 2 ) wp_send_json_success( array( 'users' => array() ) );

        // Search by display name, login, or email
        $users = get_users( array(
            'search'         => '*' . esc_attr( $q ) . '*',
            'search_columns' => array( 'user_login', 'user_email', 'display_name', 'user_nicename' ),
            'number'         => 20,
            'orderby'        => 'display_name',
            'order'          => 'ASC',
        ) );

        $out = array();
        foreach ( $users as $u ) {
            $out[] = array(
                'id'      => (int) $u->ID,
                'name'    => $u->display_name ?: $u->user_login,
                'email'   => $u->user_email,
                'is_admin' => user_can( $u, 'manage_options' ),
            );
        }
        wp_send_json_success( array( 'users' => $out ) );
    }

    /** Returns slots that can be booked for an admin (any non-personal slot, with capacity info). */
    public function bookable_slots() {
        $this->check();
        $date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : current_time( 'Y-m-d' );
        if ( ! ES_Helpers::valid_date( $date_from ) ) $date_from = current_time( 'Y-m-d' );

        // Look ahead 90 days
        $date_to = date( 'Y-m-d', strtotime( $date_from . ' +90 days' ) );

        $slots = ES_DB::get_slots_in_range( $date_from, $date_to );
        $out = array();
        foreach ( $slots as $s ) {
            $remaining = (int) $s->capacity - (int) $s->booked_count;
            $out[] = array(
                'id'         => (int) $s->id,
                'date'       => $s->slot_date,
                'start'      => substr( $s->start_time, 0, 5 ),
                'end'        => substr( $s->end_time, 0, 5 ),
                'duration'   => (int) $s->duration_min,
                'type'       => $s->slot_type,
                'type_label' => ES_Helpers::slot_type_label( $s->slot_type ),
                'type_color' => ES_Helpers::slot_type_color( $s->slot_type ),
                'capacity'   => (int) $s->capacity,
                'booked'     => (int) $s->booked_count,
                'remaining'  => max( 0, $remaining ),
                'platform'   => $s->platform,
                'title'      => $s->title,
                'is_full'    => $remaining <= 0,
            );
        }
        wp_send_json_success( array( 'slots' => $out ) );
    }

    /** Create a booking on behalf of a user. */
    public function create_booking() {
        $this->check();
        $slot_id = isset( $_POST['slot_id'] ) ? (int) $_POST['slot_id'] : 0;
        $user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $note    = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
        $send_email = ! empty( $_POST['send_email'] );

        $slot = ES_DB::get_slot( $slot_id );
        if ( ! $slot )    wp_send_json_error( array( 'message' => 'Slot not found.' ) );
        if ( ! $user_id ) wp_send_json_error( array( 'message' => 'Please select a user.' ) );

        $user = get_userdata( $user_id );
        if ( ! $user ) wp_send_json_error( array( 'message' => 'User not found.' ) );

        if ( ES_DB::user_has_booked_slot( $slot_id, $user_id ) ) {
            wp_send_json_error( array( 'message' => 'This user has already booked this slot.' ) );
        }

        if ( ES_DB::count_bookings( $slot_id ) >= (int) $slot->capacity ) {
            wp_send_json_error( array( 'message' => 'This slot is fully booked.' ) );
        }

        $data = array(
            'slot_id'   => $slot_id,
            'user_id'   => $user_id,
            'status'    => 'confirmed',
            'user_note' => $note,
        );

        // Create Zoom meeting if applicable
        if ( ES_Zoom::is_configured() && stripos( $slot->platform, 'zoom' ) !== false ) {
            $z = ES_Zoom::create_meeting( array(
                'date'       => $slot->slot_date,
                'start_time' => substr( $slot->start_time, 0, 5 ),
                'duration'   => (int) $slot->duration_min,
                'topic'      => $slot->title ?: ( $user->display_name . ' — ' . ES_Helpers::slot_type_label( $slot->slot_type ) ),
                'agenda'     => $note,
            ) );
            if ( ! is_wp_error( $z ) ) {
                $data['zoom_meeting_id'] = $z['meeting_id'];
                $data['zoom_join_url']   = $z['join_url'];
                $data['zoom_start_url']  = $z['start_url'];
                $data['zoom_password']   = $z['password'];
            }
        }

        $bid = ES_DB::insert_booking( $data );
        if ( ! $bid ) {
            if ( ! empty( $data['zoom_meeting_id'] ) ) ES_Zoom::delete_meeting( $data['zoom_meeting_id'] );
            wp_send_json_error( array( 'message' => 'Could not create booking.' ) );
        }

        if ( $send_email ) {
            @ES_Mailer::send_booking_confirmation( $bid );
        }

        wp_send_json_success( array(
            'booking_id' => $bid,
            'message'    => 'Booking created for ' . $user->display_name . '.',
        ) );
    }

    /* ============== v3.5.0 — CALENDAR: CREATE MEETING WITH ASSIGNED USER(S) ============== */

    /**
     * Calendar "Add Slot" → creates a slot AND immediately books one or more users on it,
     * auto-creating Zoom meetings (one per booking) like the manual-book flow does.
     *
     * Required POST:
     *  slot_date, start_time, duration_min, slot_type ('1to1' or 'group'),
     *  capacity, platform, title, notes, user_ids (JSON array), send_email
     */
    public function create_meeting() {
        $this->check();

        $type     = isset( $_POST['slot_type'] ) ? sanitize_text_field( wp_unslash( $_POST['slot_type'] ) ) : '1to1';
        if ( ! in_array( $type, array( '1to1', 'group' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Direct meetings only support 1:1 or Group types.' ) );
        }

        $user_ids_raw = isset( $_POST['user_ids'] ) ? wp_unslash( $_POST['user_ids'] ) : '[]';
        $user_ids = json_decode( $user_ids_raw, true );
        if ( ! is_array( $user_ids ) ) $user_ids = array();
        $user_ids = array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );

        if ( empty( $user_ids ) ) {
            wp_send_json_error( array( 'message' => 'Please assign at least one user.' ) );
        }
        if ( $type === '1to1' && count( $user_ids ) > 1 ) {
            wp_send_json_error( array( 'message' => '1:1 meetings can only have one user.' ) );
        }

        // Reuse the existing slot validator
        $data = $this->parse_slot_input();
        if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ) );

        // Force capacity to fit user count for group meetings
        if ( $type === 'group' ) {
            $data['capacity'] = max( count( $user_ids ), (int) $data['capacity'] );
        } else {
            $data['capacity'] = 1;
        }

        // Block duplicate exact start time
        if ( $this->slot_exists_at( $data['slot_date'], $data['start_time'] ) ) {
            wp_send_json_error( array( 'message' => 'A slot already exists at this date and start time. Pick a different time.' ) );
        }

        // Validate every user exists
        $users = array();
        foreach ( $user_ids as $uid ) {
            $u = get_userdata( $uid );
            if ( ! $u ) wp_send_json_error( array( 'message' => 'User #' . $uid . ' not found.' ) );
            $users[] = $u;
        }

        // 1. Create slot
        $slot_id = ES_DB::insert_slot( $data );
        if ( ! $slot_id ) wp_send_json_error( array( 'message' => 'Could not create slot.' ) );

        $send_email = ! empty( $_POST['send_email'] );
        $note       = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

        // 2. For each user, create one Zoom meeting + one booking
        $created = array();
        $errors  = array();
        $zoom_active = ES_Zoom::is_configured() && stripos( $data['platform'], 'zoom' ) !== false;

        foreach ( $users as $u ) {
            $bdata = array(
                'slot_id'   => (int) $slot_id,
                'user_id'   => (int) $u->ID,
                'status'    => 'confirmed',
                'user_note' => $note,
            );

            if ( $zoom_active ) {
                $z = ES_Zoom::create_meeting( array(
                    'date'       => $data['slot_date'],
                    'start_time' => substr( $data['start_time'], 0, 5 ),
                    'duration'   => (int) $data['duration_min'],
                    'topic'      => $data['title'] ?: ( $u->display_name . ' — ' . ES_Helpers::slot_type_label( $type ) ),
                    'agenda'     => $note,
                ) );
                if ( ! is_wp_error( $z ) ) {
                    $bdata['zoom_meeting_id'] = $z['meeting_id'];
                    $bdata['zoom_join_url']   = $z['join_url'];
                    $bdata['zoom_start_url']  = $z['start_url'];
                    $bdata['zoom_password']   = $z['password'];
                } else {
                    $errors[] = 'Zoom create failed for ' . $u->display_name . ': ' . $z->get_error_message();
                }
            }

            $bid = ES_DB::insert_booking( $bdata );
            if ( $bid ) {
                $created[] = array( 'booking_id' => $bid, 'user' => $u->display_name );
                if ( $send_email ) {
                    @ES_Mailer::send_booking_confirmation( $bid );
                }
            } else {
                if ( ! empty( $bdata['zoom_meeting_id'] ) ) @ES_Zoom::delete_meeting( $bdata['zoom_meeting_id'] );
                $errors[] = 'Could not book ' . $u->display_name . '.';
            }
        }

        if ( empty( $created ) ) {
            // Rollback: delete the slot we just created since no booking succeeded
            ES_DB::delete_slot( (int) $slot_id );
            wp_send_json_error( array( 'message' => 'Could not create any bookings. ' . implode( ' / ', $errors ) ) );
        }

        $msg = sprintf( 'Meeting created with %d user(s).', count( $created ) );
        if ( ! empty( $errors ) ) $msg .= ' Some issues: ' . implode( ' / ', $errors );

        wp_send_json_success( array(
            'slot_id'  => (int) $slot_id,
            'bookings' => $created,
            'message'  => $msg,
        ) );
    }

    /* ============== v3.5.0 — STUDENTS TAB ============== */

    /**
     * Create a new WordPress user + EduSchedule meta (called from "+ Add Student" button).
     */
    public function add_student() {
        $this->check();

        $first  = isset( $_POST['first_name'] )  ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) )  : '';
        $last   = isset( $_POST['last_name'] )   ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )   : '';
        $email  = isset( $_POST['email'] )       ? sanitize_email( wp_unslash( $_POST['email'] ) )            : '';
        $phone  = isset( $_POST['phone'] )       ? sanitize_text_field( wp_unslash( $_POST['phone'] ) )       : '';
        $parent = isset( $_POST['parent_name'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_name'] ) ) : '';
        $ref    = isset( $_POST['reference'] )   ? sanitize_text_field( wp_unslash( $_POST['reference'] ) )   : '';
        $cmt    = isset( $_POST['comment'] )     ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '';
        $send   = ! empty( $_POST['send_email'] );

        if ( ! $first ) wp_send_json_error( array( 'message' => 'First name is required.' ) );
        if ( ! $email || ! is_email( $email ) ) wp_send_json_error( array( 'message' => 'A valid email is required.' ) );
        if ( email_exists( $email ) ) wp_send_json_error( array( 'message' => 'A user with this email already exists.' ) );

        // Build a unique username from email
        $base = sanitize_user( current( explode( '@', $email ) ), true );
        if ( ! $base ) $base = 'student';
        $login = $base; $i = 1;
        while ( username_exists( $login ) ) { $login = $base . $i; $i++; }

        $password = wp_generate_password( 14, true );
        $uid = wp_create_user( $login, $password, $email );
        if ( is_wp_error( $uid ) ) wp_send_json_error( array( 'message' => $uid->get_error_message() ) );

        $display = trim( $first . ' ' . $last ) ?: $login;
        wp_update_user( array(
            'ID'           => $uid,
            'first_name'   => $first,
            'last_name'    => $last,
            'display_name' => $display,
            'role'         => 'subscriber',
        ) );

        if ( $phone )  update_user_meta( $uid, 'es_phone',       $phone );
        if ( $parent ) update_user_meta( $uid, 'es_parent_name', $parent );
        if ( $ref )    update_user_meta( $uid, 'es_reference',   $ref );
        if ( $cmt )    update_user_meta( $uid, 'es_comment',     $cmt );

        if ( $send ) {
            // Send WP "set your password" notification so the student can log in.
            @wp_new_user_notification( $uid, null, 'user' );
        }

        wp_send_json_success( array(
            'user_id' => (int) $uid,
            'name'    => $display,
            'email'   => $email,
            'message' => 'Student added: ' . $display,
        ) );
    }

    /**
     * Return one student's profile + every booking they've made.
     */
    public function student_details() {
        $this->check();
        $uid = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        if ( ! $uid ) wp_send_json_error( array( 'message' => 'Missing user ID.' ) );

        $u = get_userdata( $uid );
        if ( ! $u ) wp_send_json_error( array( 'message' => 'User not found.' ) );

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

        wp_send_json_success( array(
            'user' => array(
                'id'           => (int) $u->ID,
                'first_name'   => $u->first_name,
                'last_name'    => $u->last_name,
                'display_name' => $u->display_name,
                'email'        => $u->user_email,
                'phone'        => get_user_meta( $u->ID, 'es_phone', true ),
                'parent_name'  => get_user_meta( $u->ID, 'es_parent_name', true ),
                'reference'    => get_user_meta( $u->ID, 'es_reference', true ),
                'comment'      => get_user_meta( $u->ID, 'es_comment', true ),
                'country'      => get_user_meta( $u->ID, 'es_country', true ),
                'timezone'     => get_user_meta( $u->ID, 'es_timezone', true ),
                'registered'   => $u->user_registered,
                'registered_label' => date_i18n( 'M j, Y', strtotime( $u->user_registered ) ),
            ),
            'stats' => array(
                'total'    => count( $bookings ),
                'upcoming' => $upcoming,
                'past'     => $past,
            ),
            'bookings' => $bookings,
        ) );
    }

    /* ============== PACKAGES MODULE ============== */

    public function save_package() {
        $this->check();
        
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

        $allowed_currencies = array_keys( ES_Helpers::currencies() );
        $currency = isset( $_POST['currency'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ) ) ) : 'INR';
        if ( ! in_array( $currency, $allowed_currencies, true ) ) $currency = 'INR';

        // is_active defaults to 1; we only honour an explicit "0" from the edit form.
        $is_active = 1;
        if ( isset( $_POST['is_active'] ) && (int) $_POST['is_active'] === 0 ) $is_active = 0;

        $data = array(
            'package_name'          => isset( $_POST['package_name'] ) ? sanitize_text_field( wp_unslash( $_POST['package_name'] ) ) : '',
            'sub_heading'           => isset( $_POST['sub_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['sub_heading'] ) ) : '',
            'monthly_price'         => isset( $_POST['monthly_price'] ) ? floatval( $_POST['monthly_price'] ) : 0,
            'months'                => isset( $_POST['months'] ) ? max( 1, (int) $_POST['months'] ) : 1,
            'monthly_session_limit' => isset( $_POST['monthly_session_limit'] ) ? max( 0, (int) $_POST['monthly_session_limit'] ) : 0,
            'currency'              => $currency,
            // billing_cycle + yearly_price columns still exist for backward-compat;
            // we just no longer expose them in the admin UI.
            'billing_cycle'         => 'monthly',
            'yearly_price'          => 0,
            'discount_percent'      => isset( $_POST['discount_percent'] ) ? max( 0, min( 100, floatval( $_POST['discount_percent'] ) ) ) : 0,
            'discount_months'       => isset( $_POST['discount_months'] ) ? max( 0, min( 60, (int) $_POST['discount_months'] ) ) : 0,
            'tagline'               => isset( $_POST['tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline'] ) ) : '',
            'description'           => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
            'display_order'         => isset( $_POST['display_order'] ) ? (int) $_POST['display_order'] : 0,
            'package_type'          => isset( $_POST['package_type'] ) && in_array( $_POST['package_type'], array( '1to1', 'group', 'consultancy' ), true ) ? sanitize_text_field( wp_unslash( $_POST['package_type'] ) ) : '1to1',
            'is_active'             => $is_active,
        );
        // price (total) and total_sessions are auto-computed by ES_Packages
        // from monthly_price × months and monthly_session_limit × months.

        if ( empty( $data['package_name'] ) ) {
            wp_send_json_error( array( 'message' => 'Package name is required' ) );
        }

        if ( $id > 0 ) {
            $result = ES_Packages::update( $id, $data );
            if ( $result !== false ) {
                wp_send_json_success( array(
                    'message' => 'Package updated successfully',
                    'id' => $id
                ) );
            }
        } else {
            $result = ES_Packages::insert( $data );
            if ( $result ) {
                wp_send_json_success( array(
                    'message' => 'Package created successfully',
                    'id' => $result
                ) );
            }
        }

        wp_send_json_error( array( 'message' => 'Failed to save package' ) );
    }

    public function get_package() {
        $this->check();
        
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        $package = ES_Packages::get( $id );

        if ( $package ) {
            wp_send_json_success( array( 'package' => $package ) );
        }

        wp_send_json_error( array( 'message' => 'Package not found' ) );
    }

    public function delete_package() {
        $this->check();
        
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        $result = ES_Packages::delete( $id );

        if ( $result !== false ) {
            wp_send_json_success( array( 'message' => 'Package deleted successfully' ) );
        }

        wp_send_json_error( array( 'message' => 'Failed to delete package' ) );
    }

    /**
     * Send a test email to the admin to verify mail (incl. SMTP) is working.
     */
    public function send_test_email() {
        $this->check();

        $to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
        if ( ! is_email( $to ) ) {
            $to = ES_Mailer::admin_recipient();
        }
        if ( ! is_email( $to ) ) {
            wp_send_json_error( array( 'message' => 'No valid recipient email. Set an admin notification email first.' ) );
        }

        $brand   = get_bloginfo( 'name' );
        $s       = ES_Helpers::settings();
        $transport = ! empty( $s['smtp_enabled'] ) && ! empty( $s['smtp_host'] )
            ? ( 'SMTP via ' . $s['smtp_host'] . ':' . (int) $s['smtp_port'] )
            : 'WordPress default mailer';

        $subject = sprintf( '[%s] Test Email', $brand );
        $body    = '<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;color:#222;line-height:1.6">'
                 . '<p>This is a test email from <strong>' . esc_html( $brand ) . '</strong>.</p>'
                 . '<p>If you received it, your email configuration is working.</p>'
                 . '<p style="color:#6b7280;font-size:13px">Transport: ' . esc_html( $transport ) . '</p>'
                 . '</div>';

        $ok = ES_Mailer::send( $to, $subject, $body );

        if ( $ok ) {
            wp_send_json_success( array( 'message' => 'Test email sent to ' . $to . ' (' . $transport . '). Check the inbox / spam folder.' ) );
        }
        wp_send_json_error( array( 'message' => 'Send failed. Check your SMTP settings and the PHP error log for "[EduSchedule] Email failed".' ) );
    }

    public function after_call_convert() {
        $this->check();

        $user_id  = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $outcome  = isset( $_POST['outcome'] ) ? sanitize_text_field( wp_unslash( $_POST['outcome'] ) ) : '';
        $comments = isset( $_POST['comments'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comments'] ) ) : '';
        $group_id = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;

        if ( ! $user_id || ! $outcome ) wp_send_json_error( array( 'message' => 'Missing required fields' ) );
        $user = get_userdata( $user_id );
        if ( ! $user ) wp_send_json_error( array( 'message' => 'Student not found.' ) );

        $course_ids_in = array();
        if ( isset( $_POST['course_ids'] ) && is_array( $_POST['course_ids'] ) ) {
            foreach ( $_POST['course_ids'] as $cid ) {
                $cid = (int) $cid;
                if ( $cid > 0 ) $course_ids_in[] = $cid;
            }
        }
        $course_ids_in = array_slice( array_values( array_unique( $course_ids_in ) ), 0, 1 );

        $package_ids = array();
        $flow_type   = '1to1';
        $group       = null;

        if ( $outcome === 'Group Student' ) {
            $flow_type = 'group';
            if ( ! $group_id ) wp_send_json_error( array( 'message' => 'Please select a group.' ) );

            $group = ES_Packages::get_group( $group_id );
            if ( ! $group ) wp_send_json_error( array( 'message' => 'Selected group was not found.' ) );

            $group_pkg_id = (int) ( $group->package_id ?? 0 );
            if ( ! $group_pkg_id ) {
                wp_send_json_error( array( 'message' => 'This group has no package assigned yet. Open the group Renew tab, assign a package/course, then add this student to the group flow.' ) );
            }

            $package_ids = array( $group_pkg_id );
            // Group course is the group's course. Do not use any stale student course.
            $course_ids_in = ES_Packages::get_group_course_ids( $group_id );
            $course_ids_in = array_slice( array_values( array_unique( array_map( 'intval', $course_ids_in ) ) ), 0, 1 );
        } else {
            if ( isset( $_POST['package_ids'] ) && is_array( $_POST['package_ids'] ) ) {
                foreach ( $_POST['package_ids'] as $pid ) {
                    $pid = (int) $pid;
                    if ( $pid > 0 ) $package_ids[] = $pid;
                }
            }
            $package_ids = array_slice( array_unique( $package_ids ), 0, 3 );

            $owned_active = ES_Packages::get_active_package_ids( $user_id );
            if ( ! empty( $owned_active ) ) {
                $package_ids = array_values( array_diff( $package_ids, $owned_active ) );
            }
        }

        $needs_package = in_array( $outcome, array( '1:1 Student', 'Group Student' ), true );
        if ( $outcome === '1:1 Student' && empty( $package_ids ) ) {
            wp_send_json_error( array( 'message' => 'Please select at least one package.' ) );
        }

        $primary_pkg_id = ! empty( $package_ids ) ? (int) $package_ids[0] : 0;
        ES_Packages::link_lead_to_package( $user_id, $primary_pkg_id, $outcome, $comments, $group_id ? $group_id : null );

        if ( $outcome === '1:1 Student' && ! empty( $course_ids_in ) ) {
            ES_Packages::set_student_course_ids( $user_id, $course_ids_in );
        }

        if ( ! empty( $course_ids_in ) && $primary_pkg_id ) {
            global $wpdb;
            $course_id   = ES_Packages::first_course_id( $course_ids_in );
            $course_name = ES_Packages::course_names_str( $course_ids_in );
            if ( $course_id ) {
                $wpdb->query( $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}es_lead_packages
                        SET course_id = %d, course_name = %s, flow_type = %s
                      WHERE user_id = %d AND package_id = %d
                      ORDER BY id DESC LIMIT 1",
                    $course_id, $course_name, $flow_type, $user_id, $primary_pkg_id
                ) );
            }
        }

        if ( ! empty( $package_ids ) ) {
            ES_Packages::set_staged_packages( $user_id, $package_ids );
            ES_Packages::set_staged_flow( $user_id, $flow_type );
        } else {
            ES_Packages::clear_staged_packages( $user_id );
        }

        $share_link = '';
        if ( $needs_package ) {
            $token = ES_Packages::generate_selection_token( $user_id, 14 );
            $pkg_page_id = 0;
            foreach ( get_pages() as $pg ) {
                if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) { $pkg_page_id = $pg->ID; break; }
            }
            $base_url = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/' );
            $share_link = add_query_arg( array( 'user_id' => $user_id, 'token' => $token ), $base_url );
        }

        $packages_list = array();
        foreach ( $package_ids as $pid ) {
            $p = ES_Packages::get( $pid );
            if ( $p ) $packages_list[] = $p;
        }

        $course_name = '';
        if ( $outcome === 'Group Student' && $group_id ) {
            $course_name = ES_Packages::course_names_str( ES_Packages::get_group_course_ids( $group_id ) );
            if ( $course_name === '' && $group && ! empty( $group->course_name ) ) $course_name = $group->course_name;
        } elseif ( ! empty( $course_ids_in ) ) {
            $course_name = ES_Packages::course_names_str( $course_ids_in );
        } else {
            $course_name = ES_Packages::course_names_str( ES_Packages::get_student_course_ids( $user_id ) );
        }

        ES_Mailer::send_after_call_student( $user, $outcome, $packages_list, $share_link, $comments, $course_name );
        ES_Mailer::send_after_call_admin( $user, $outcome, $packages_list, $share_link, $comments, $group, $course_name );

        wp_send_json_success( array(
            'message'    => $outcome === 'Group Student' ? 'Group package link created. Student can complete the group package without changing their 1:1 package.' : 'Lead converted successfully. Package selection email sent.',
            'share_link' => $share_link,
            'reload'     => true,
        ) );
    }

    public function student_select_package() {
        check_ajax_referer( 'es_fe_nonce', 'nonce' );

        $user_id    = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $token      = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
        $package_id = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;

        if ( ! $user_id || ! $token || ! $package_id ) wp_send_json_error( array( 'message' => 'Invalid request' ) );
        if ( ! ES_Packages::validate_token( $user_id, $token ) ) wp_send_json_error( array( 'message' => 'Invalid or expired link' ) );

        $package = ES_Packages::get( $package_id );
        if ( ! $package ) wp_send_json_error( array( 'message' => 'Package not found' ) );

        $staged = ES_Packages::get_staged_packages( $user_id );
        if ( ! empty( $staged ) && ! in_array( $package_id, $staged, true ) ) {
            wp_send_json_error( array( 'message' => 'This package is not available for selection' ) );
        }

        $existing_outcome = ES_Packages::get_latest_lead_outcome( $user_id );
        $outcome_label = $existing_outcome ? $existing_outcome->outcome : 'Student Selected';
        $group_id      = $existing_outcome && ! empty( $existing_outcome->group_id ) ? (int) $existing_outcome->group_id : 0;
        $flow_type     = ES_Packages::get_staged_flow( $user_id );
        if ( $group_id || ES_Packages::outcome_to_category( $outcome_label ) === 'group' ) $flow_type = 'group';
        else $flow_type = '1to1';

        $active_same = ES_Packages::get_active_package_payment( $user_id, $package_id, $flow_type, $group_id );
        if ( $active_same ) {
            $not_expired = empty( $active_same->valid_until ) || strtotime( $active_same->valid_until ) >= current_time( 'timestamp' );
            if ( $not_expired && ES_Packages::remaining_sessions( $active_same ) > 0 ) {
                wp_send_json_error( array( 'message' => 'This package is already active for this student in this flow.' ) );
            }
        }

        $lead_link_id = ES_Packages::link_lead_to_package( $user_id, $package_id, $outcome_label, '', $group_id ?: null );

        $course_id   = 0;
        $course_name = '';
        if ( $existing_outcome ) {
            if ( ! empty( $existing_outcome->course_id ) ) $course_id = (int) $existing_outcome->course_id;
            if ( ! empty( $existing_outcome->course_name ) ) $course_name = $existing_outcome->course_name;
        }

        if ( $flow_type === 'group' && $group_id ) {
            $group_course_ids = ES_Packages::get_group_course_ids( $group_id );
            if ( ! $course_id ) $course_id = ES_Packages::first_course_id( $group_course_ids );
            if ( $course_name === '' ) $course_name = ES_Packages::course_names_str( $group_course_ids );
            if ( $course_name === '' ) { $group = ES_Packages::get_group( $group_id ); if ( $group && ! empty( $group->course_name ) ) $course_name = $group->course_name; }
        } else {
            $course_ids = ES_Packages::get_student_course_ids( $user_id );
            if ( ! $course_id ) $course_id = ES_Packages::first_course_id( $course_ids );
            if ( $course_name === '' ) $course_name = ES_Packages::course_names_str( $course_ids );
            if ( $course_id ) ES_Packages::set_student_course_ids( $user_id, array( $course_id ) );
        }
        if ( $course_id && $course_name === '' ) $course_name = ES_Packages::course_name( $course_id );

        if ( $lead_link_id ) {
            global $wpdb;
            $wpdb->update( $wpdb->prefix . 'es_lead_packages', array(
                'course_id'   => $course_id ?: null,
                'course_name' => $course_name,
                'flow_type'   => $flow_type,
                'group_id'    => $flow_type === 'group' && $group_id ? $group_id : null,
            ), array( 'id' => (int) $lead_link_id ) );
        }

        $sel_months       = max( 1, (int) ( $package->months ?? 1 ) );
        $monthly_sessions = max( 0, (int) ( $package->monthly_session_limit ?? 0 ) );
        $total_sessions   = (int) ( $package->total_sessions ?? 0 );
        if ( $total_sessions <= 0 && $monthly_sessions > 0 ) $total_sessions = $monthly_sessions * $sel_months;

        $valid_from_ts = current_time( 'timestamp' );
        $valid_from    = date( 'Y-m-d H:i:s', $valid_from_ts );
        $valid_until   = date( 'Y-m-d H:i:s', strtotime( '+' . $sel_months . ' months', $valid_from_ts ) );

        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'es_payments', array(
            'user_id'               => $user_id,
            'package_id'            => $package_id,
            'package_name'          => $package->package_name,
            'course_id'             => $course_id ?: null,
            'course_name'           => $course_name,
            'flow_type'             => $flow_type,
            'group_id'              => $flow_type === 'group' && $group_id ? $group_id : null,
            'amount'                => (float) ( $package->price ?? 0 ),
            'monthly_price'         => (float) ( $package->monthly_price ?? $package->price ?? 0 ),
            'months'                => $sel_months,
            'monthly_session_limit' => $monthly_sessions,
            'total_sessions'        => $total_sessions,
            'used_sessions'         => 0,
            'currency'              => ! empty( $package->currency ) ? $package->currency : 'INR',
            'billing_cycle'         => 'after_call',
            'gateway'               => 'manual',
            'payment_method'        => 'student_selection',
            'gateway_session_id'    => $flow_type . '_after_call_' . $user_id . '_' . $package_id . '_' . time(),
            'status'                => 'paid',
            'valid_from'            => $valid_from,
            'valid_until'           => $valid_until,
            'meta'                  => wp_json_encode( array( 'source' => 'student_select_package', 'outcome' => $outcome_label, 'flow_type' => $flow_type, 'group_id' => $group_id ) ),
        ) );
        $new_payment_id = (int) $wpdb->insert_id;

        if ( $flow_type === 'group' && $group_id ) {
            ES_Packages::add_user_to_group( $group_id, $user_id );
            update_user_meta( $user_id, ES_Packages::META_HAS_GROUP, 1 );
            update_user_meta( $user_id, ES_Packages::META_GROUP_ID, $group_id );
            $group = ES_Packages::get_group( $group_id );
            $group_update = array();
            if ( $group && empty( $group->package_id ) ) $group_update['package_id'] = $package_id;
            if ( $group && empty( $group->total_sessions ) ) $group_update['total_sessions'] = $total_sessions;
            if ( $group && empty( $group->course_id ) && $course_id ) $group_update['course_ids'] = implode( ',', array( $course_id ) );
            if ( ! empty( $group_update ) ) ES_Packages::update_group( $group_id, $group_update );
        } else {
            update_user_meta( $user_id, ES_Packages::META_HAS_1TO1, 1 );
            update_user_meta( $user_id, ES_Packages::META_PACKAGE_ID, $package_id );
        }

        ES_Packages::clear_token( $user_id );
        ES_Packages::clear_staged_packages( $user_id );

        $pkg_page_id = 0;
        foreach ( get_pages() as $pg ) { if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) { $pkg_page_id = $pg->ID; break; } }
        $base_url = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/' );
        $redirect_url = add_query_arg( array( 'selected' => 1, 'pkg' => $package_id ), $base_url );

        $user = get_userdata( $user_id );
        if ( $user ) {
            $pkg_currency = ! empty( $package->currency ) ? $package->currency : 'INR';
            $price_label  = ES_Helpers::format_price( $package->price, $pkg_currency );
            $subject      = 'Package Selection Confirmed' . ( $course_name ? ': ' . $course_name : '' );
            $message      = "Hello " . $user->display_name . ",\n\nThank you for selecting your package:\n\n";
            $message     .= "Package: " . $package->package_name . "\n";
            if ( $course_name ) $message .= "Course: " . $course_name . "\n";
            $message     .= "Type: " . ( $flow_type === 'group' ? 'Group' : '1:1' ) . "\n";
            $message     .= "Amount: " . $price_label . "\nSessions: " . $total_sessions . "\nValid Until: " . date_i18n( 'M j, Y', strtotime( $valid_until ) ) . "\n\nRegards,\n" . get_bloginfo( 'name' );
            ES_Mailer::send( $user->user_email, $subject, nl2br( esc_html( $message ) ) );

            $admin_email = get_option( 'admin_email' );
            if ( $admin_email ) {
                ES_Mailer::send( $admin_email, 'Student Package Selected' . ( $course_name ? ': ' . $course_name : '' ),
                    '<p><strong>Student:</strong> ' . esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')</p>' .
                    '<p><strong>Package:</strong> ' . esc_html( $package->package_name ) . '</p>' .
                    '<p><strong>Type:</strong> ' . esc_html( $flow_type === 'group' ? 'Group' : '1:1' ) . '</p>' .
                    ( $course_name ? '<p><strong>Course:</strong> ' . esc_html( $course_name ) . '</p>' : '' ) .
                    '<p><strong>Payment ID:</strong> #' . (int) $new_payment_id . '</p>' );
            }
        }

        wp_send_json_success( array( 'message' => 'Package selected successfully', 'redirect' => $redirect_url ) );
    }

    /* =================== STRIPE CHECKOUT =================== */

    /**
     * Create a Stripe Checkout Session and return its hosted URL.
     * Frontend POSTs: package_id, user_id, token, billing_cycle, nonce.
     */
    public function stripe_create_checkout() {
        check_ajax_referer( 'es_fe_nonce', 'nonce' );

        $user_id       = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $token         = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
        $package_id    = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;
        $billing_cycle = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : 'monthly';

        if ( ! $user_id || ! $token || ! $package_id ) {
            wp_send_json_error( array( 'message' => 'Invalid request.' ) );
        }
        if ( ! in_array( $billing_cycle, array( 'monthly', 'yearly' ), true ) ) {
            $billing_cycle = 'monthly';
        }

        if ( ! class_exists( 'ES_Stripe' ) || ! ES_Stripe::is_enabled() ) {
            wp_send_json_error( array( 'message' => 'Stripe is not available. Please contact the admin.' ) );
        }

        if ( ! ES_Packages::validate_token( $user_id, $token ) ) {
            wp_send_json_error( array( 'message' => 'Invalid or expired link.' ) );
        }

        // If the admin staged a subset, enforce the package belongs to it.
        $staged = ES_Packages::get_staged_packages( $user_id );
        if ( ! empty( $staged ) && ! in_array( $package_id, $staged, true ) ) {
            wp_send_json_error( array( 'message' => 'This package is not available for selection.' ) );
        }

        $result = ES_Stripe::create_checkout_session( $user_id, $package_id, $billing_cycle );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'url'        => $result['url'],
            'session_id' => $result['session_id'],
        ) );
    }

    /**
     * Create a Stripe PaymentIntent for the inline Elements form.
     * POSTs: package_id, user_id, token, billing_cycle, name, email.
     * Returns: client_secret, amount label, etc.
     */
    public function stripe_create_intent() {
        check_ajax_referer( 'es_fe_nonce', 'nonce' );

        // Defence in depth: the public packages template already requires login,
        // but the AJAX action is also registered with nopriv_ so anyone could
        // hit it directly. Reject anonymous requests here too.
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Please log in to continue.' ) );
        }

        $user_id       = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $token         = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
        $package_id    = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;
        $billing_cycle = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : 'monthly';
        $name          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        // Billing address (required by RBI for INR / India card payments)
        $addr = array(
            'line1'   => isset( $_POST['addr_line1'] )  ? sanitize_text_field( wp_unslash( $_POST['addr_line1'] ) )  : '',
            'city'    => isset( $_POST['addr_city'] )   ? sanitize_text_field( wp_unslash( $_POST['addr_city'] ) )   : '',
            'state'   => isset( $_POST['addr_state'] )  ? sanitize_text_field( wp_unslash( $_POST['addr_state'] ) )  : '',
            'postal'  => isset( $_POST['addr_postal'] ) ? sanitize_text_field( wp_unslash( $_POST['addr_postal'] ) ) : '',
            'country' => isset( $_POST['addr_country'] ) ? strtoupper( substr( sanitize_text_field( wp_unslash( $_POST['addr_country'] ) ), 0, 2 ) ) : '',
        );

        // The user_id from the form MUST match the currently logged-in user.
        // This stops a logged-in user from buying packages for someone else
        // by tampering with the hidden field.
        if ( $user_id !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => 'Account mismatch. Please refresh and try again.' ) );
        }

        if ( ! $user_id || ! $token || ! $package_id ) {
            wp_send_json_error( array( 'message' => 'Invalid request.' ) );
        }
        if ( ! in_array( $billing_cycle, array( 'monthly', 'yearly' ), true ) ) {
            $billing_cycle = 'monthly';
        }
        if ( $email && ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        }

        if ( ! class_exists( 'ES_Stripe' ) || ! ES_Stripe::is_enabled() ) {
            wp_send_json_error( array( 'message' => 'Stripe is not available. Please contact the admin.' ) );
        }

        if ( ! ES_Packages::validate_token( $user_id, $token ) ) {
            wp_send_json_error( array( 'message' => 'Invalid or expired link.' ) );
        }

        $staged = ES_Packages::get_staged_packages( $user_id );
        if ( ! empty( $staged ) && ! in_array( $package_id, $staged, true ) ) {
            wp_send_json_error( array( 'message' => 'This package is not available for selection.' ) );
        }

        // Block re-buying a package only inside the current flow.
        // A student may own the same package separately for 1:1 and Group.
        $selection_flow = ES_Packages::get_staged_flow( $user_id );
        $selection_group_id = 0;
        if ( $selection_flow === 'group' ) {
            $latest_selection = ES_Packages::get_latest_lead_outcome( $user_id );
            if ( $latest_selection && ! empty( $latest_selection->group_id ) ) {
                $selection_group_id = (int) $latest_selection->group_id;
            }
        }
        $active_pay = ES_Packages::get_active_package_payment( $user_id, $package_id, $selection_flow, $selection_group_id );
        if ( $active_pay ) {
            $until_msg = ! empty( $active_pay->valid_until )
                ? ' It is active until ' . date_i18n( 'F j, Y', strtotime( $active_pay->valid_until ) ) . '.'
                : '';
            wp_send_json_error( array(
                'message'       => 'You already have this plan active for this flow.' . $until_msg . ' You can renew once it expires.',
                'already_owned' => true,
            ) );
        }

        // Persist the billing address on the user's profile so it pre-fills
        // next time and is available to the admin Payments view.
        if ( $addr['line1'] )   update_user_meta( $user_id, 'es_addr_line1',  $addr['line1'] );
        if ( $addr['city'] )    update_user_meta( $user_id, 'es_addr_city',   $addr['city'] );
        if ( $addr['state'] )   update_user_meta( $user_id, 'es_addr_state',  $addr['state'] );
        if ( $addr['postal'] )  update_user_meta( $user_id, 'es_addr_postal', $addr['postal'] );
        if ( $addr['country'] ) {
            update_user_meta( $user_id, 'es_addr_country', $addr['country'] );
            // Keep the legacy es_country meta in sync if it was empty
            if ( ! get_user_meta( $user_id, 'es_country', true ) ) {
                update_user_meta( $user_id, 'es_country', $addr['country'] );
            }
        }

        $result = ES_Stripe::create_payment_intent( $user_id, $package_id, $billing_cycle, $name, $email, $addr );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'client_secret'     => $result['client_secret'],
            'payment_intent_id' => $result['payment_intent_id'],
            'amount'            => $result['amount'],
            'currency'          => $result['currency'],
            'amount_label'      => $result['amount_label'],
            'billing_cycle'     => $result['billing_cycle'],
            'publishable_key'   => ES_Stripe::publishable_key(),
        ) );
    }

    /**
     * Finalize a Stripe PaymentIntent after JS has confirmed it client-side.
     * Server re-verifies status with Stripe, then marks paid + fires emails.
     */
    public function stripe_finalize_intent() {
        check_ajax_referer( 'es_fe_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Please log in to continue.' ) );
        }

        $pi_id = isset( $_POST['payment_intent_id'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_intent_id'] ) ) : '';
        if ( ! $pi_id ) {
            wp_send_json_error( array( 'message' => 'Missing payment intent id.' ) );
        }

        if ( ! class_exists( 'ES_Stripe' ) || ! ES_Stripe::is_enabled() ) {
            wp_send_json_error( array( 'message' => 'Stripe is not available.' ) );
        }

        // Verify the pending row's user_id matches the logged-in user before
        // finalizing — stops a logged-in user from finalizing someone else's
        // PaymentIntent (which would assign that package to the wrong user).
        global $wpdb;
        $row_user_id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}es_payments WHERE gateway_session_id = %s LIMIT 1",
            $pi_id
        ) );
        if ( $row_user_id && $row_user_id !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => 'This payment belongs to a different account.' ) );
        }

        $result = ES_Stripe::finalize_payment_intent( $pi_id );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /* =================== GROUPS =================== */

    public function save_group() {
        $this->check();

        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        $course_ids = array();
        if ( isset( $_POST['course_ids'] ) && is_array( $_POST['course_ids'] ) ) {
            foreach ( $_POST['course_ids'] as $cid ) {
                $cid = (int) $cid;
                if ( $cid > 0 ) $course_ids[] = $cid;
            }
        }
        $course_ids = array_slice( array_values( array_unique( $course_ids ) ), 0, 1 );

        // Renew flow for groups. Same behavior as After Call: create
        // package-selection/payment links instead of directly adding sessions.
        $is_renew = ! empty( $_POST['renew'] ) && $id > 0;
        if ( $is_renew ) {
            $pkg_id = isset( $_POST['package_id'] ) && $_POST['package_id'] ? (int) $_POST['package_id'] : 0;
            if ( ! $pkg_id ) wp_send_json_error( array( 'message' => 'Package required for renewal.' ) );
            $pkg = ES_Packages::get( $pkg_id );
            if ( ! $pkg ) wp_send_json_error( array( 'message' => 'Package not found.' ) );

            $g = ES_Packages::get_group( $id );
            if ( ! $g ) wp_send_json_error( array( 'message' => 'Group not found.' ) );

            $members = ES_Packages::get_group_members( $id );
            if ( empty( $members ) ) {
                wp_send_json_error( array( 'message' => 'Add at least one student to the group before sending a renew link.' ) );
            }

            $now_ts = current_time( 'timestamp' );
            foreach ( (array) ES_Packages::get_group_payments( $id, true ) as $pay ) {
                $left  = max( 0, (int) ( $pay->total_sessions ?? 0 ) - (int) ( $pay->used_sessions ?? 0 ) );
                $valid = empty( $pay->valid_until ) || strtotime( $pay->valid_until ) >= $now_ts;
                if ( (int) $pay->package_id === $pkg_id && $valid && $left > 0 ) {
                    wp_send_json_error( array( 'message' => 'This group already has this package active with sessions left. Select a different package, or renew after it expires / has no sessions left.' ) );
                }
            }

            $renew_note = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
            $send_email = ! empty( $_POST['send_email'] );

            $course_id = ! empty( $course_ids ) ? (int) $course_ids[0] : ES_Packages::first_course_id( ES_Packages::get_group_course_ids( $id ) );
            $course_name = $course_id ? ES_Packages::course_name( $course_id ) : '';
            if ( $course_name === '' && ! empty( $g->course_name ) ) $course_name = $g->course_name;

            // Store the selected package/course on the group for display and for
            // Group Student after-call flow. Do not touch total/used sessions here.
            $group_update = array(
                'package_id'   => $pkg_id,
                'package_name' => $pkg->package_name,
                'course_id'    => $course_id ?: null,
                'course_name'  => $course_name,
            );
            if ( $course_id ) $group_update['course_ids'] = (string) $course_id;
            if ( $renew_note !== '' ) {
                $existing_notes = $g->notes ?: '';
                $group_update['notes'] = trim( $existing_notes . ( $existing_notes ? "\n" : '' ) . '[Renew link] ' . $renew_note );
            }
            ES_Packages::update_group( $id, $group_update );

            $pkg_page_id = 0;
            foreach ( get_pages() as $pg ) {
                if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) { $pkg_page_id = $pg->ID; break; }
            }
            $base_url = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/' );

            $links = array();
            global $wpdb;
            foreach ( $members as $member ) {
                $uid = (int) $member->ID;
                ES_Packages::link_lead_to_package( $uid, $pkg_id, 'Group Student', $renew_note, $id );
                ES_Packages::set_staged_packages( $uid, array( $pkg_id ) );
                ES_Packages::set_staged_flow( $uid, 'group' );

                $token = ES_Packages::generate_selection_token( $uid, 14 );
                $share_link = add_query_arg( array( 'user_id' => $uid, 'token' => $token ), $base_url );
                $links[] = array(
                    'user_id' => $uid,
                    'name'    => $member->display_name,
                    'email'   => $member->user_email,
                    'link'    => $share_link,
                );

                // Make sure the latest lead row carries the course and flow details.
                $latest_lead_id = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}es_lead_packages WHERE user_id = %d AND package_id = %d ORDER BY id DESC LIMIT 1",
                    $uid, $pkg_id
                ) );
                if ( $latest_lead_id ) {
                    $wpdb->update(
                        $wpdb->prefix . 'es_lead_packages',
                        array(
                            'course_id'   => $course_id ?: null,
                            'course_name' => $course_name,
                            'flow_type'   => 'group',
                            'group_id'    => (int) $id,
                        ),
                        array( 'id' => $latest_lead_id )
                    );
                }

                if ( $send_email ) {
                    ES_Mailer::send_after_call_student( $member, 'Group Student', array( $pkg ), $share_link, $renew_note, $course_name );
                }
            }

            $admin_to = get_option( 'admin_email' );
            if ( $admin_to ) {
                $first_link = ! empty( $links[0]['link'] ) ? $links[0]['link'] : '';
                ES_Mailer::send(
                    $admin_to,
                    'Group renew package link created: ' . $g->group_name . ( $course_name ? ' - ' . $course_name : '' ),
                    '<p>Group renewal package-selection links were created.</p>' .
                    '<p><strong>Group:</strong> ' . esc_html( $g->group_name ) . '</p>' .
                    '<p><strong>Package:</strong> ' . esc_html( $pkg->package_name ) . '</p>' .
                    ( $course_name ? '<p><strong>Course:</strong> ' . esc_html( $course_name ) . '</p>' : '' ) .
                    '<p><strong>Members:</strong> ' . count( $links ) . '</p>' .
                    ( $first_link ? '<p><strong>First link:</strong> <a href="' . esc_url( $first_link ) . '">' . esc_html( $first_link ) . '</a></p>' : '' )
                );
            }

            wp_send_json_success( array(
                'message'    => 'Group renewal link created. Package becomes active only after the student completes the selection/payment.',
                'id'         => $id,
                'share_link' => ! empty( $links[0]['link'] ) ? $links[0]['link'] : '',
                'links'      => $links,
                'link_count' => count( $links ),
            ) );
        }

        // Normal group create/edit is intentionally simple: only name + description.
        // Package/course/session allowance are linked through the student purchase
        // flow or the Renew tab, so editing a group will not accidentally wipe
        // an existing linked package.
        $group_name = isset( $_POST['group_name'] ) ? sanitize_text_field( wp_unslash( $_POST['group_name'] ) ) : '';
        if ( empty( $group_name ) ) {
            wp_send_json_error( array( 'message' => 'Group name is required' ) );
        }

        $data = array(
            'group_name' => $group_name,
            'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
            'is_active'  => 1,
        );

        // Backward compatibility: older admin screens may still POST these fields.
        // Include them only when actually present.
        if ( isset( $_POST['package_id'] ) ) {
            $data['package_id'] = $_POST['package_id'] ? (int) $_POST['package_id'] : null;
        }
        if ( isset( $_POST['course_ids'] ) ) {
            $data['course_ids'] = implode( ',', $course_ids );
        }
        if ( isset( $_POST['duration'] ) ) {
            $data['duration'] = sanitize_text_field( wp_unslash( $_POST['duration'] ) );
        }
        if ( isset( $_POST['total_sessions'] ) ) {
            $data['total_sessions'] = (int) $_POST['total_sessions'];
        }
        if ( isset( $_POST['color'] ) ) {
            $color = sanitize_hex_color( wp_unslash( $_POST['color'] ) );
            if ( $color ) $data['color'] = $color;
        }

        if ( $id > 0 ) {
            $r = ES_Packages::update_group( $id, $data );
            if ( $r !== false ) {
                wp_send_json_success( array( 'message' => 'Group updated', 'id' => $id ) );
            }
        } else {
            $new_id = ES_Packages::insert_group( $data );
            if ( $new_id ) {
                wp_send_json_success( array( 'message' => 'Group created', 'id' => $new_id ) );
            }
        }

        wp_send_json_error( array( 'message' => 'Failed to save group' ) );
    }

    public function get_group() {
        $this->check();
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        $g  = ES_Packages::get_group( $id );
        if ( $g ) wp_send_json_success( array( 'group' => $g ) );
        wp_send_json_error( array( 'message' => 'Group not found' ) );
    }

    public function delete_group() {
        $this->check();
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        $r  = ES_Packages::delete_group( $id );
        if ( $r !== false ) wp_send_json_success( array( 'message' => 'Group deleted' ) );
        wp_send_json_error( array( 'message' => 'Failed to delete group' ) );
    }

    public function remove_group_member() {
        $this->check();
        $gid = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;
        $uid = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        if ( ! $gid || ! $uid ) wp_send_json_error( array( 'message' => 'Missing fields' ) );

        ES_Packages::remove_user_from_group( $gid, $uid );

        // Also clear the user's group meta if it matches
        $assigned = (int) get_user_meta( $uid, ES_Packages::META_GROUP_ID, true );
        if ( $assigned === $gid ) {
            delete_user_meta( $uid, ES_Packages::META_GROUP_ID );
        }
        if ( empty( ES_Packages::get_user_groups( $uid ) ) ) {
            delete_user_meta( $uid, ES_Packages::META_HAS_GROUP );
            if ( ES_Packages::get_user_category( $uid ) === 'group' && ! ES_Packages::user_has_flow( $uid, '1to1' ) ) {
                update_user_meta( $uid, ES_Packages::META_CATEGORY, 'demo' );
            }
        }

        wp_send_json_success( array( 'message' => 'Member removed' ) );
    }


    public function add_group_member() {
        $this->check();

        $gid = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;
        $uid = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        if ( ! $gid || ! $uid ) wp_send_json_error( array( 'message' => 'Please select a student.' ) );

        $group = ES_Packages::get_group( $gid );
        $user  = get_userdata( $uid );
        if ( ! $group ) wp_send_json_error( array( 'message' => 'Group not found.' ) );
        if ( ! $user )  wp_send_json_error( array( 'message' => 'Student not found.' ) );

        ES_Packages::add_user_to_group( $gid, $uid );
        update_user_meta( $uid, ES_Packages::META_HAS_GROUP, 1 );
        update_user_meta( $uid, ES_Packages::META_GROUP_ID, $gid );

        if ( ! ES_Packages::user_has_flow( $uid, '1to1' ) ) {
            update_user_meta( $uid, ES_Packages::META_CATEGORY, 'group' );
        }

        wp_send_json_success( array( 'message' => 'Student added to group.' ) );
    }

    /* ============== SESSION FILES (UPLOADS) ============== */

    /**
     * Upload a PDF / DOCX / PPT(X) / video and attach it to a 1:1 student or
     * a group. Expects multipart form-data:
     *   target_type = '1to1' | 'group'
     *   target_id   = user_id (1to1) or group_id (group)
     *   slot_id     = optional slot association
     *   file        = the uploaded file ($_FILES['file'])
     */
    public function upload_session_file() {
        $this->check();

        $target_type = isset( $_POST['target_type'] ) ? sanitize_text_field( wp_unslash( $_POST['target_type'] ) ) : '1to1';
        if ( ! in_array( $target_type, array( '1to1', 'group' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid target type.' ) );
        }
        $target_id = isset( $_POST['target_id'] ) ? (int) $_POST['target_id'] : 0;
        $slot_id   = isset( $_POST['slot_id'] ) ? (int) $_POST['slot_id'] : 0;
        if ( ! $target_id ) {
            wp_send_json_error( array( 'message' => 'Missing target.' ) );
        }

        if ( empty( $_FILES['file'] ) || ! isset( $_FILES['file']['tmp_name'] ) ) {
            wp_send_json_error( array( 'message' => 'No file received.' ) );
        }

        $file = $_FILES['file'];
        if ( ! empty( $file['error'] ) ) {
            wp_send_json_error( array( 'message' => 'Upload error (code ' . (int) $file['error'] . ').' ) );
        }

        // Validate extension against our allowed buckets.
        $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        $type = ES_Packages::ext_to_type( $ext );
        if ( ! $type ) {
            wp_send_json_error( array( 'message' => 'Unsupported file type. Allowed: PDF, DOC/DOCX, PPT/PPTX, images (JPG/PNG/GIF/WebP), video.' ) );
        }

        // Let WordPress handle the move + MIME sniffing.
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Permit our extensions for this single upload.
        $mimes_filter = function ( $mimes ) {
            $mimes['pdf']  = 'application/pdf';
            $mimes['doc']  = 'application/msword';
            $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            $mimes['ppt']  = 'application/vnd.ms-powerpoint';
            $mimes['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            $mimes['jpg']  = 'image/jpeg';
            $mimes['jpeg'] = 'image/jpeg';
            $mimes['png']  = 'image/png';
            $mimes['gif']  = 'image/gif';
            $mimes['webp'] = 'image/webp';
            $mimes['mp4']  = 'video/mp4';
            $mimes['mov']  = 'video/quicktime';
            $mimes['webm'] = 'video/webm';
            $mimes['mkv']  = 'video/x-matroska';
            $mimes['avi']  = 'video/x-msvideo';
            return $mimes;
        };
        add_filter( 'upload_mimes', $mimes_filter );

        $overrides = array( 'test_form' => false );
        $moved = wp_handle_upload( $file, $overrides );

        remove_filter( 'upload_mimes', $mimes_filter );

        if ( isset( $moved['error'] ) ) {
            wp_send_json_error( array( 'message' => $moved['error'] ) );
        }

        $file_id = ES_Packages::insert_session_file( array(
            'slot_id'     => $slot_id ?: null,
            'target_type' => $target_type,
            'user_id'     => $target_type === '1to1' ? $target_id : null,
            'group_id'    => $target_type === 'group' ? $target_id : null,
            'file_name'   => sanitize_file_name( $file['name'] ),
            'file_url'    => esc_url_raw( $moved['url'] ),
            'file_path'   => $moved['file'],
            'file_type'   => $type,
            'file_size'   => (int) ( $file['size'] ?? 0 ),
            'uploaded_by' => get_current_user_id(),
        ) );

        if ( ! $file_id ) {
            wp_send_json_error( array( 'message' => 'Could not save the file record.' ) );
        }

        wp_send_json_success( array(
            'message' => 'File uploaded',
            'file'    => ES_Packages::get_session_file( $file_id ),
        ) );
    }

    public function list_session_files() {
        $this->check();
        $target_type = isset( $_POST['target_type'] ) ? sanitize_text_field( wp_unslash( $_POST['target_type'] ) ) : '1to1';
        $target_id   = isset( $_POST['target_id'] ) ? (int) $_POST['target_id'] : 0;
        if ( ! $target_id ) wp_send_json_error( array( 'message' => 'Missing target.' ) );

        $files = ES_Packages::get_session_files( $target_type, $target_id );
        wp_send_json_success( array( 'files' => $files ) );
    }

    public function delete_session_file() {
        $this->check();
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $id ) wp_send_json_error( array( 'message' => 'Missing file id.' ) );

        $r = ES_Packages::delete_session_file( $id );
        if ( $r !== false ) wp_send_json_success( array( 'message' => 'File deleted' ) );
        wp_send_json_error( array( 'message' => 'Could not delete the file.' ) );
    }

    /* ============== SCHEDULE A SESSION (MEETING) ============== */

    /**
     * Convenience wrapper around create_meeting() so a session can be scheduled
     * straight from a student or group detail page. For 1:1, target_id is the
     * user; for group, target_id is the group (its members are booked).
     * Reuses the same slot/Zoom/booking pipeline.
     */
    public function schedule_session() {
        $this->check();
        global $wpdb;

        $target_type = isset( $_POST['target_type'] ) ? sanitize_text_field( wp_unslash( $_POST['target_type'] ) ) : '1to1';
        if ( ! in_array( $target_type, array( '1to1', 'group' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid target type.' ) );
        }
        $target_id = isset( $_POST['target_id'] ) ? (int) $_POST['target_id'] : 0;
        if ( ! $target_id ) wp_send_json_error( array( 'message' => 'Missing target.' ) );

        // Resolve the user list.
        $user_ids = array();
        if ( $target_type === 'group' ) {
            $members = ES_Packages::get_group_members( $target_id );
            foreach ( $members as $m ) $user_ids[] = (int) $m->ID;
            if ( empty( $user_ids ) ) {
                wp_send_json_error( array( 'message' => 'This group has no members to schedule.' ) );
            }
        } else {
            $user_ids = array( $target_id );
        }

        $chosen_payment_id = isset( $_POST['payment_id'] ) ? (int) $_POST['payment_id'] : 0;
        $chosen_payment    = null;
        $schedule_package_id   = 0;
        $schedule_package_name = '';
        $schedule_course_id    = isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : 0;
        $schedule_course_name  = $schedule_course_id ? ES_Packages::course_name( $schedule_course_id ) : '';

        if ( $target_type === '1to1' ) {
            if ( $chosen_payment_id > 0 ) {
                $chosen_payment = $wpdb->get_row( $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}es_payments WHERE id = %d AND user_id = %d AND status = 'paid'",
                    $chosen_payment_id, (int) $user_ids[0]
                ) );
                if ( ! $chosen_payment ) {
                    wp_send_json_error( array( 'message' => 'The selected package is not valid for this student.' ) );
                }
                $left = max( 0, (int) $chosen_payment->total_sessions - (int) $chosen_payment->used_sessions );
                if ( $left <= 0 ) {
                    wp_send_json_error( array( 'message' => 'No sessions left on the selected package. Pick a different package or renew it first.' ) );
                }
                if ( ! empty( $chosen_payment->valid_until ) && strtotime( $chosen_payment->valid_until ) < current_time( 'timestamp' ) ) {
                    wp_send_json_error( array( 'message' => 'The selected package expired on ' . date_i18n( 'M j, Y', strtotime( $chosen_payment->valid_until ) ) . '.' ) );
                }
            } else {
                $plan = ES_Packages::get_active_plan( (int) $user_ids[0] );
                if ( ! $plan ) {
                    wp_send_json_error( array( 'message' => 'This student has no active package. Convert or renew them via After Call before scheduling.' ) );
                }
                $left = ES_Packages::remaining_sessions( $plan );
                if ( $left <= 0 ) {
                    wp_send_json_error( array( 'message' => 'No sessions left on the active package. Renew before scheduling more.' ) );
                }
                if ( ! empty( $plan->valid_until ) && strtotime( $plan->valid_until ) < current_time( 'timestamp' ) ) {
                    wp_send_json_error( array( 'message' => 'The active package has expired (valid until ' . date_i18n( 'M j, Y', strtotime( $plan->valid_until ) ) . '). Renew before scheduling.' ) );
                }
                $chosen_payment_id = (int) $plan->id;
                $chosen_payment    = $plan;
            }

            $schedule_package_id   = (int) ( $chosen_payment->package_id ?? 0 );
            $schedule_package_name = ! empty( $chosen_payment->package_name ) ? $chosen_payment->package_name : '';
            if ( ! $schedule_package_name && $schedule_package_id ) {
                $pkg = ES_Packages::get( $schedule_package_id );
                $schedule_package_name = $pkg ? $pkg->package_name : '';
            }

            if ( ! $schedule_course_id ) {
                $schedule_course_id = (int) ( $chosen_payment->course_id ?? 0 );
            }
            if ( ! $schedule_course_id ) {
                $schedule_course_id = ES_Packages::first_course_id( ES_Packages::get_student_course_ids( (int) $user_ids[0] ) );
            }
            if ( $schedule_course_id ) {
                $schedule_course_name = ES_Packages::course_name( $schedule_course_id );
                ES_Packages::set_student_course_ids( (int) $user_ids[0], array( $schedule_course_id ) );
                if ( $chosen_payment_id && $schedule_course_name && empty( $chosen_payment->course_name ) ) {
                    $wpdb->update(
                        $wpdb->prefix . 'es_payments',
                        array( 'course_id' => $schedule_course_id, 'course_name' => $schedule_course_name ),
                        array( 'id' => $chosen_payment_id )
                    );
                }
            } elseif ( ! empty( $chosen_payment->course_name ) ) {
                $schedule_course_name = $chosen_payment->course_name;
            }
        } else {
            $g = ES_Packages::get_group( $target_id );
            if ( ! $g ) {
                wp_send_json_error( array( 'message' => 'Group not found.' ) );
            }

            if ( $chosen_payment_id > 0 ) {
                $chosen_payment = $wpdb->get_row( $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}es_payments WHERE id = %d AND group_id = %d AND status = 'paid'",
                    $chosen_payment_id, (int) $target_id
                ) );
                if ( ! $chosen_payment ) {
                    wp_send_json_error( array( 'message' => 'The selected group package is not valid.' ) );
                }
                $left = max( 0, (int) $chosen_payment->total_sessions - (int) $chosen_payment->used_sessions );
                if ( $left <= 0 ) {
                    wp_send_json_error( array( 'message' => 'No sessions left on the selected group package. Renew it first.' ) );
                }
                if ( ! empty( $chosen_payment->valid_until ) && strtotime( $chosen_payment->valid_until ) < current_time( 'timestamp' ) ) {
                    wp_send_json_error( array( 'message' => 'The selected group package expired on ' . date_i18n( 'M j, Y', strtotime( $chosen_payment->valid_until ) ) . '.' ) );
                }
                $schedule_package_id   = (int) ( $chosen_payment->package_id ?? 0 );
                $schedule_package_name = ! empty( $chosen_payment->package_name ) ? $chosen_payment->package_name : '';
                if ( ! $schedule_package_name && $schedule_package_id ) {
                    $pkg = ES_Packages::get( $schedule_package_id );
                    $schedule_package_name = $pkg ? $pkg->package_name : '';
                }
                if ( ! $schedule_course_id ) {
                    $schedule_course_id = (int) ( $chosen_payment->course_id ?? 0 );
                }
                if ( $schedule_course_id ) {
                    $schedule_course_name = ES_Packages::course_name( $schedule_course_id );
                    ES_Packages::set_group_course_ids( $target_id, array( $schedule_course_id ) );
                    if ( $schedule_course_name && empty( $chosen_payment->course_name ) ) {
                        $wpdb->update( $wpdb->prefix . 'es_payments', array( 'course_id' => $schedule_course_id, 'course_name' => $schedule_course_name ), array( 'id' => $chosen_payment_id ) );
                    }
                } elseif ( ! empty( $chosen_payment->course_name ) ) {
                    $schedule_course_name = $chosen_payment->course_name;
                }

                ES_Packages::update_group( $target_id, array(
                    'package_id'     => $schedule_package_id,
                    'package_name'   => $schedule_package_name,
                    'course_id'      => $schedule_course_id ?: null,
                    'course_name'    => $schedule_course_name,
                    'total_sessions' => (int) ( $chosen_payment->total_sessions ?? 0 ),
                    'used_sessions'  => (int) ( $chosen_payment->used_sessions ?? 0 ),
                ) );
            } else {
                if ( empty( $g->package_id ) ) {
                    wp_send_json_error( array( 'message' => 'Link a package to this group before scheduling.' ) );
                }

                $g_total = (int) ( $g->total_sessions ?? 0 );
                $g_used  = (int) ( $g->used_sessions ?? 0 );
                if ( $g_total <= 0 ) {
                    $g_pkg_for_total = ES_Packages::get( (int) $g->package_id );
                    if ( $g_pkg_for_total && (int) ( $g_pkg_for_total->total_sessions ?? 0 ) > 0 ) {
                        $g_total = (int) $g_pkg_for_total->total_sessions;
                        ES_Packages::update_group( $target_id, array( 'package_id' => (int) $g->package_id, 'total_sessions' => $g_total ) );
                    }
                }
                if ( $g_total <= 0 ) {
                    wp_send_json_error( array( 'message' => 'This group package has no session allowance. Add sessions or renew the package before scheduling.' ) );
                }
                if ( $g_used >= $g_total ) {
                    wp_send_json_error( array( 'message' => 'No sessions left on this group package. Renew before scheduling more.' ) );
                }

                $schedule_package_id   = (int) $g->package_id;
                $schedule_package_name = ! empty( $g->package_name ) ? $g->package_name : '';
                if ( ! $schedule_package_name ) {
                    $pkg = ES_Packages::get( $schedule_package_id );
                    $schedule_package_name = $pkg ? $pkg->package_name : '';
                }
                if ( ! $schedule_course_id ) {
                    $schedule_course_id = ! empty( $g->course_id ) ? (int) $g->course_id : ES_Packages::first_course_id( ES_Packages::get_group_course_ids( $target_id ) );
                }
                if ( $schedule_course_id ) {
                    $schedule_course_name = ES_Packages::course_name( $schedule_course_id );
                    ES_Packages::set_group_course_ids( $target_id, array( $schedule_course_id ) );
                } elseif ( ! empty( $g->course_name ) ) {
                    $schedule_course_name = $g->course_name;
                }
            }
        }

        // Reuse slot validation.
        $data = $this->parse_slot_input();
        if ( is_wp_error( $data ) ) wp_send_json_error( array( 'message' => $data->get_error_message() ) );

        $data['slot_type'] = $target_type;
        $data['capacity']  = $target_type === 'group' ? max( count( $user_ids ), (int) $data['capacity'] ) : 1;
        $data['group_id']  = $target_type === 'group' ? (int) $target_id : null;
        $data['package_id']   = $schedule_package_id ?: null;
        $data['package_name'] = $schedule_package_name;
        $data['course_id']    = $schedule_course_id ?: null;
        $data['course_name']  = $schedule_course_name;

        if ( $this->slot_exists_at( $data['slot_date'], $data['start_time'] ) ) {
            wp_send_json_error( array( 'message' => 'A slot already exists at this date and start time.' ) );
        }

        // Create the slot.
        $slot_id = ES_DB::insert_slot( $data );
        if ( ! $slot_id ) wp_send_json_error( array( 'message' => 'Could not create the session slot.' ) );

        $note        = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
        $send_email  = ! empty( $_POST['send_email'] );
        $zoom_active = ES_Zoom::is_configured() && stripos( $data['platform'], 'zoom' ) !== false;

        $created = array();
        $errors  = array();

        foreach ( $user_ids as $uid ) {
            $u = get_userdata( $uid );
            if ( ! $u ) { $errors[] = 'User #' . $uid . ' not found.'; continue; }

            $bdata = array(
                'slot_id'   => (int) $slot_id,
                'user_id'   => (int) $uid,
                'status'    => 'confirmed',
                'user_note' => $note,
            );
            if ( $chosen_payment_id > 0 ) {
                $bdata['payment_id'] = $chosen_payment_id;
            }

            if ( $zoom_active ) {
                $z = ES_Zoom::create_meeting( array(
                    'date'       => $data['slot_date'],
                    'start_time' => substr( $data['start_time'], 0, 5 ),
                    'duration'   => (int) $data['duration_min'],
                    'topic'      => $data['title'] ?: ( $u->display_name . ' — ' . ES_Helpers::slot_type_label( $target_type ) ),
                    'agenda'     => $note,
                ) );
                if ( ! is_wp_error( $z ) ) {
                    $bdata['zoom_meeting_id'] = $z['meeting_id'];
                    $bdata['zoom_join_url']   = $z['join_url'];
                    $bdata['zoom_start_url']  = $z['start_url'];
                    $bdata['zoom_password']   = $z['password'];
                } else {
                    $errors[] = 'Zoom: ' . $z->get_error_message();
                }
            }

            $bid = ES_DB::insert_booking( $bdata );
            if ( $bid ) {
                $created[] = $bid;
                if ( $send_email ) {
                    $sched_note = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
                    // Send to student
                    ES_Mailer::send_booking_confirmation( $bid, $sched_note );
                    // Always send admin notification on admin-scheduled sessions
                    ES_Mailer::send_admin_booking_notification( $bid, $sched_note );
                }
            } else {
                if ( ! empty( $bdata['zoom_meeting_id'] ) ) @ES_Zoom::delete_meeting( $bdata['zoom_meeting_id'] );
                $errors[] = 'Could not book ' . $u->display_name . '.';
            }
        }

        if ( empty( $created ) ) {
            ES_DB::delete_slot( (int) $slot_id );
            wp_send_json_error( array( 'message' => 'Could not schedule the session. ' . implode( ' / ', $errors ) ) );
        }

        $counts = array();
        if ( $target_type === '1to1' ) {
            foreach ( $user_ids as $uid ) {
                ES_Packages::recount_used_sessions( (int) $uid );
            }
            $fresh = ES_Packages::get_active_plan( (int) $user_ids[0] );
            $counts = array(
                'sessions_total' => $fresh ? (int) ( $fresh->total_sessions ?? 0 ) : 0,
                'sessions_used'  => $fresh ? (int) ( $fresh->used_sessions ?? 0 ) : 0,
                'sessions_left'  => $fresh ? ES_Packages::remaining_sessions( $fresh ) : 0,
            );
        } else {
            ES_Packages::recount_group_used_sessions( $target_id );
            $g_after = ES_Packages::get_group( $target_id );
            $counts = array(
                'group_total' => $g_after ? (int) ( $g_after->total_sessions ?? 0 ) : 0,
                'group_used'  => $g_after ? (int) ( $g_after->used_sessions ?? 0 ) : 0,
                'group_left'  => $g_after ? max( 0, (int) ( $g_after->total_sessions ?? 0 ) - (int) ( $g_after->used_sessions ?? 0 ) ) : 0,
            );
        }

        wp_send_json_success( array_merge( array(
            'message' => 'Session scheduled (' . count( $created ) . ' booking' . ( count( $created ) > 1 ? 's' : '' ) . ')',
            'slot_id' => (int) $slot_id,
            'errors'  => $errors,
        ), $counts ) );
    }

    /* ============== EDIT / DELETE SCHEDULED SESSION ============== */

    /**
     * v4.5 — Edit a scheduled session's date, time, duration, platform, title.
     * Does NOT change bookings or Zoom meeting topic (cosmetic fields only).
     */
    public function edit_schedule_session() {
        $this->check();
        $slot_id  = isset( $_POST['slot_id'] ) ? (int) $_POST['slot_id'] : 0;
        if ( ! $slot_id ) wp_send_json_error( array( 'message' => 'Missing slot.' ) );

        global $wpdb;
        $slot = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}es_slots WHERE id = %d", $slot_id ) );
        if ( ! $slot ) wp_send_json_error( array( 'message' => 'Session not found.' ) );

        $date     = isset( $_POST['slot_date'] )    ? sanitize_text_field( wp_unslash( $_POST['slot_date'] ) )    : $slot->slot_date;
        $start    = isset( $_POST['start_time'] )   ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) )   : $slot->start_time;
        $duration = isset( $_POST['duration_min'] ) ? (int) $_POST['duration_min']                                 : $slot->duration_min;
        $platform = isset( $_POST['platform'] )     ? sanitize_text_field( wp_unslash( $_POST['platform'] ) )     : $slot->platform;
        $title    = isset( $_POST['title'] )        ? sanitize_text_field( wp_unslash( $_POST['title'] ) )        : $slot->title;
        $notes    = isset( $_POST['notes'] )        ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) )    : $slot->notes;

        if ( ! ES_Helpers::valid_date( $date ) )         wp_send_json_error( array( 'message' => 'Invalid date.' ) );
        if ( ! preg_match( '/^\d{2}:\d{2}/', $start ) ) wp_send_json_error( array( 'message' => 'Invalid start time.' ) );
        if ( $duration < 5 )                             wp_send_json_error( array( 'message' => 'Duration must be at least 5 min.' ) );

        $start_hhmm = substr( $start, 0, 5 );
        $end_hhmm   = ES_Helpers::calc_end_time( $start_hhmm, $duration );

        $wpdb->update(
            $wpdb->prefix . 'es_slots',
            array(
                'slot_date'    => $date,
                'start_time'   => $start_hhmm . ':00',
                'end_time'     => $end_hhmm . ':00',
                'duration_min' => $duration,
                'platform'     => $platform,
                'title'        => $title,
                'notes'        => $notes,
            ),
            array( 'id' => $slot_id ),
            array( '%s','%s','%s','%d','%s','%s','%s' ),
            array( '%d' )
        );

        wp_send_json_success( array( 'message' => 'Session updated.' ) );
    }

    /**
     * v4.5 — Delete a scheduled session slot (and its bookings, Zoom meeting,
     * and refund the consumed session). Thin wrapper around delete_slot().
     */
    public function delete_schedule_session() {
        $this->check();
        $slot_id = isset( $_POST['slot_id'] ) ? (int) $_POST['slot_id'] : 0;
        if ( ! $slot_id ) wp_send_json_error( array( 'message' => 'Missing slot.' ) );

        // Delegate to the existing delete_slot logic (handles Zoom, refund, etc.)
        $_POST['id'] = $slot_id; // delete_slot reads from POST['id']
        $this->delete_slot();
        // delete_slot calls wp_send_json_success / error so we never reach here.
    }

    /* ============== GLOBAL FILE / VIDEO UPLOAD ============== */

    /**
     * v4.5 — Upload a file/video that is NOT tied to a specific session slot.
     * It is stored as a global file against the 1to1-user or group so it shows
     * up in the Schedule tab's global section.
     */
    public function global_upload() {
        $this->check();

        $target_type = isset( $_POST['target_type'] ) ? sanitize_text_field( wp_unslash( $_POST['target_type'] ) ) : '1to1';
        if ( ! in_array( $target_type, array( '1to1', 'group' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid target type.' ) );
        }
        $target_id = isset( $_POST['target_id'] ) ? (int) $_POST['target_id'] : 0;
        if ( ! $target_id ) wp_send_json_error( array( 'message' => 'Missing target.' ) );

        if ( empty( $_FILES['file'] ) || ! isset( $_FILES['file']['tmp_name'] ) ) {
            wp_send_json_error( array( 'message' => 'No file received.' ) );
        }
        $file = $_FILES['file'];
        if ( ! empty( $file['error'] ) ) {
            wp_send_json_error( array( 'message' => 'Upload error (code ' . (int) $file['error'] . ').' ) );
        }

        $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        $type = ES_Packages::ext_to_type( $ext );
        if ( ! $type ) {
            wp_send_json_error( array( 'message' => 'Unsupported file type. Allowed: PDF, DOC/DOCX, PPT/PPTX, images (JPG/PNG/GIF/WebP), video.' ) );
        }

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        $mimes_filter = function ( $mimes ) {
            $mimes['pdf']  = 'application/pdf';
            $mimes['doc']  = 'application/msword';
            $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            $mimes['ppt']  = 'application/vnd.ms-powerpoint';
            $mimes['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            $mimes['jpg']  = 'image/jpeg';
            $mimes['jpeg'] = 'image/jpeg';
            $mimes['png']  = 'image/png';
            $mimes['gif']  = 'image/gif';
            $mimes['webp'] = 'image/webp';
            $mimes['mp4']  = 'video/mp4';
            $mimes['mov']  = 'video/quicktime';
            $mimes['webm'] = 'video/webm';
            $mimes['mkv']  = 'video/x-matroska';
            $mimes['avi']  = 'video/x-msvideo';
            return $mimes;
        };
        add_filter( 'upload_mimes', $mimes_filter );
        $moved = wp_handle_upload( $file, array( 'test_form' => false ) );
        remove_filter( 'upload_mimes', $mimes_filter );

        if ( isset( $moved['error'] ) ) {
            wp_send_json_error( array( 'message' => $moved['error'] ) );
        }

        $file_id = ES_Packages::insert_session_file( array(
            'slot_id'     => null,
            'target_type' => $target_type,
            'user_id'     => $target_type === '1to1' ? $target_id : null,
            'group_id'    => $target_type === 'group' ? $target_id : null,
            'package_id'  => isset( $_POST['package_id'] ) && (int) $_POST['package_id'] > 0 ? (int) $_POST['package_id'] : null,
            'file_name'   => sanitize_file_name( $file['name'] ),
            'file_url'    => esc_url_raw( $moved['url'] ),
            'file_path'   => $moved['file'],
            'file_type'   => $type,
            'file_size'   => (int) ( $file['size'] ?? 0 ),
            'uploaded_by' => get_current_user_id(),
        ) );

        if ( ! $file_id ) wp_send_json_error( array( 'message' => 'Could not save the file record.' ) );

        wp_send_json_success( array(
            'message' => 'File uploaded',
            'file'    => ES_Packages::get_session_file( $file_id ),
        ) );
    }

    /* ============== RENEW PACKAGE ============== */

    /**
     * v4.5.8 — Renew / extend a student's 1:1 package by staging a package
     * selection link, same as After Call. The paid plan is created only after
     * the student completes the selection/payment step.
     */
    public function renew_package() {
        $this->check();

        $user_id    = isset( $_POST['user_id'] )    ? (int) $_POST['user_id']    : 0;
        $package_id = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;
        $comments   = isset( $_POST['comments'] )   ? sanitize_textarea_field( wp_unslash( $_POST['comments'] ) ) : '';
        $send_email = ! empty( $_POST['send_email'] );

        $course_ids_in = array();
        if ( isset( $_POST['course_ids'] ) && is_array( $_POST['course_ids'] ) ) {
            foreach ( $_POST['course_ids'] as $cid ) { $cid = (int) $cid; if ( $cid > 0 ) $course_ids_in[] = $cid; }
        }
        $course_ids_in = array_slice( array_values( array_unique( $course_ids_in ) ), 0, 1 );

        if ( ! $user_id )    wp_send_json_error( array( 'message' => 'Missing student.' ) );
        if ( ! $package_id ) wp_send_json_error( array( 'message' => 'Please select a package to renew with.' ) );

        $pkg  = ES_Packages::get( $package_id );
        $user = get_userdata( $user_id );
        if ( ! $pkg )  wp_send_json_error( array( 'message' => 'Package not found.' ) );
        if ( ! $user ) wp_send_json_error( array( 'message' => 'Student not found.' ) );

        $active_same = ES_Packages::get_active_package_payment( $user_id, $package_id, '1to1' );
        if ( $active_same ) {
            $not_expired = empty( $active_same->valid_until ) || strtotime( $active_same->valid_until ) >= current_time( 'timestamp' );
            if ( $not_expired && ES_Packages::remaining_sessions( $active_same ) > 0 ) {
                wp_send_json_error( array( 'message' => 'This 1:1 package is still active. Renew it after expiry or after all sessions are used.' ) );
            }
        }

        if ( ! empty( $course_ids_in ) ) { ES_Packages::set_student_course_ids( $user_id, $course_ids_in ); $course_ids = $course_ids_in; }
        else { $course_ids = ES_Packages::get_student_course_ids( $user_id ); }
        $course_id   = ES_Packages::first_course_id( $course_ids );
        $course_name = ES_Packages::course_names_str( $course_ids );

        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'es_lead_packages', array(
            'user_id'             => $user_id,
            'package_id'          => $package_id,
            'course_id'           => $course_id ?: null,
            'course_name'         => $course_name,
            'flow_type'           => '1to1',
            'outcome'             => 'Renewed',
            'additional_comments' => $comments,
            'group_id'            => null,
            'selected_at'         => current_time( 'mysql' ),
        ) );

        ES_Packages::set_staged_packages( $user_id, array( $package_id ) );
        ES_Packages::set_staged_flow( $user_id, '1to1' );
        $token = ES_Packages::generate_selection_token( $user_id, 14 );

        $pkg_page_id = 0;
        foreach ( get_pages() as $pg ) { if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) { $pkg_page_id = $pg->ID; break; } }
        $base_url   = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/' );
        $share_link = add_query_arg( array( 'user_id' => $user_id, 'token' => $token ), $base_url );

        if ( $send_email ) {
            ES_Mailer::send_after_call_student( $user, 'Renewed', array( $pkg ), $share_link, $comments, $course_name );
            ES_Mailer::send_after_call_admin( $user, 'Renewed', array( $pkg ), $share_link, $comments, null, $course_name );
        }

        wp_send_json_success( array(
            'message'    => 'Renewal package link created. The package becomes active after the student completes selection/payment.',
            'share_link' => $share_link,
            'reload'     => true,
        ) );
    }

    /* ============== ATTENDANCE ============== */

    public function save_attendance() {
        $this->check();
        $user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        if ( ! $user_id ) wp_send_json_error( array( 'message' => 'Missing student.' ) );

        $ok = ES_Packages::save_attendance( array(
            'user_id'  => $user_id,
            'slot_id'  => isset( $_POST['slot_id'] ) ? (int) $_POST['slot_id'] : 0,
            'group_id' => isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0,
            'status'   => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'none',
            'comment'  => isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '',
            'att_date' => isset( $_POST['att_date'] ) ? sanitize_text_field( wp_unslash( $_POST['att_date'] ) ) : current_time( 'Y-m-d' ),
        ) );

        if ( $ok === false ) wp_send_json_error( array( 'message' => 'Could not save attendance.' ) );

        $group_id = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;
        if ( $group_id ) {
            ES_Packages::recount_group_used_sessions( $group_id );
            $g = ES_Packages::get_group( $group_id );
            wp_send_json_success( array(
                'message'       => 'Attendance saved',
                'group_total'   => $g ? (int) ( $g->total_sessions ?? 0 ) : 0,
                'group_used'    => $g ? (int) ( $g->used_sessions ?? 0 ) : 0,
                'group_left'    => $g ? max( 0, (int) ( $g->total_sessions ?? 0 ) - (int) ( $g->used_sessions ?? 0 ) ) : 0,
            ) );
        }

        // Recompute 1:1 used_sessions from actual bookings (schedule-time model):
        // scheduling consumes a session immediately; absent_excused refunds it.
        ES_Packages::recount_used_sessions( $user_id );

        // Return the student's refreshed session counts so the UI can update.
        $plan = ES_Packages::get_active_plan( $user_id );
        wp_send_json_success( array(
            'message'        => 'Attendance saved',
            'sessions_total' => $plan ? (int) ( $plan->total_sessions ?? 0 ) : 0,
            'sessions_used'  => $plan ? (int) ( $plan->used_sessions ?? 0 ) : 0,
            'sessions_left'  => $plan ? ES_Packages::remaining_sessions( $plan ) : 0,
        ) );
    }

    /**
     * Bulk-mark a whole group's attendance for ONE session in a single request.
     * Used by the group "Mark all Present / Absent / Clear" toolbar so admins
     * don't have to click every member individually. Marks every current member
     * of the group for the given slot, then recomputes the group's used-session
     * count once and returns the per-session marked summary so the UI can update
     * the header counter and per-row buttons without a reload.
     *
     * POST: group_id, slot_id, status ('present'|'absent_unexcused'|
     *       'absent_excused'|'none'), att_date (optional).
     */
    public function save_group_attendance_bulk() {
        $this->check();

        $group_id = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;
        $slot_id  = isset( $_POST['slot_id'] )  ? (int) $_POST['slot_id'] : 0;
        $status   = isset( $_POST['status'] )   ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'none';
        $att_date = isset( $_POST['att_date'] ) ? sanitize_text_field( wp_unslash( $_POST['att_date'] ) ) : current_time( 'Y-m-d' );

        if ( ! $group_id || ! $slot_id ) {
            wp_send_json_error( array( 'message' => 'Missing group or session.' ) );
        }

        $status  = ES_Packages::normalize_att_status( $status );
        $members = ES_Packages::get_group_members( $group_id );
        if ( empty( $members ) ) {
            wp_send_json_error( array( 'message' => 'This group has no members.' ) );
        }

        $saved   = array(); // user_id => status (only the non-"none" stay marked)
        $marked  = 0;
        foreach ( $members as $m ) {
            $uid = (int) $m->ID;
            $ok  = ES_Packages::save_attendance( array(
                'user_id'  => $uid,
                'slot_id'  => $slot_id,
                'group_id' => $group_id,
                'status'   => $status,
                // Preserve any existing per-row comment by leaving it untouched
                // is not possible in a single bulk write, so bulk actions clear
                // the comment only when explicitly clearing the status.
                'comment'  => '',
                'att_date' => $att_date,
            ) );
            if ( $ok !== false ) {
                $saved[ $uid ] = $status;
                if ( $status !== 'none' ) $marked++;
            }
        }

        // Recompute the group's used-session count once after the batch.
        ES_Packages::recount_group_used_sessions( $group_id );

        $g = ES_Packages::get_group( $group_id );

        wp_send_json_success( array(
            'message'        => 'Attendance updated for ' . count( $saved ) . ' member' . ( count( $saved ) === 1 ? '' : 's' ),
            'slot_id'        => $slot_id,
            'status'         => $status,
            'saved'          => $saved,
            'marked'         => $marked,
            'total_members'  => count( $members ),
            'group_total'    => $g ? (int) ( $g->total_sessions ?? 0 ) : 0,
            'group_used'     => $g ? (int) ( $g->used_sessions ?? 0 ) : 0,
            'group_left'     => $g ? max( 0, (int) ( $g->total_sessions ?? 0 ) - (int) ( $g->used_sessions ?? 0 ) ) : 0,
        ) );
    }

    /* ============== VIDEOS ============== */

    public function add_video() {
        $this->check();
        $target_type   = isset( $_POST['target_type'] ) ? sanitize_text_field( wp_unslash( $_POST['target_type'] ) ) : '1to1';
        $target_id     = isset( $_POST['target_id'] ) ? (int) $_POST['target_id'] : 0;
        $title         = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $url           = isset( $_POST['video_url'] ) ? esc_url_raw( wp_unslash( $_POST['video_url'] ) ) : '';
        $duration      = isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '';
        $attachment_id = isset( $_POST['attachment_id'] ) ? (int) $_POST['attachment_id'] : 0;

        // If an attachment was chosen from the media library, prefer its URL and
        // title so we always store a valid, hosted video reference (#4).
        if ( $attachment_id ) {
            $att_url = wp_get_attachment_url( $attachment_id );
            if ( $att_url ) $url = esc_url_raw( $att_url );
            if ( ! $title ) {
                $att_title = get_the_title( $attachment_id );
                if ( $att_title ) $title = sanitize_text_field( $att_title );
            }
        }

        if ( ! $target_id || ! $title || ! $url ) {
            wp_send_json_error( array( 'message' => 'Please choose a video to upload.' ) );
        }

        $id = ES_Packages::insert_video( array(
            'target_type'   => $target_type === 'group' ? 'group' : '1to1',
            'user_id'       => $target_type === 'group' ? null : $target_id,
            'group_id'      => $target_type === 'group' ? $target_id : null,
            'title'         => $title,
            'video_url'     => $url,
            'duration'      => $duration,
            'attachment_id' => $attachment_id ?: null,
        ) );

        if ( ! $id ) wp_send_json_error( array( 'message' => 'Could not add the video.' ) );

        $video = null;
        global $wpdb;
        $video = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}es_videos WHERE id = %d", $id ) );
        wp_send_json_success( array( 'message' => 'Video added', 'video' => $video ) );
    }

    public function delete_video() {
        $this->check();
        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $id ) wp_send_json_error( array( 'message' => 'Missing video id.' ) );
        $r = ES_Packages::delete_video( $id );
        if ( $r !== false ) wp_send_json_success( array( 'message' => 'Video deleted' ) );
        wp_send_json_error( array( 'message' => 'Could not delete the video.' ) );
    }

    /**
     * v4.4 — Per-package "global" videos. The admin attaches a video to a
     * package on the Packages admin page; it then shows up for every student
     * who owns that package, independent of per-session uploads.
     *
     * Accepts either a media-library attachment_id OR a direct video_url, plus
     * the package_id to associate.
     */
    public function add_package_video() {
        $this->check();
        $package_id    = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;
        $title         = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $url           = isset( $_POST['video_url'] ) ? esc_url_raw( wp_unslash( $_POST['video_url'] ) ) : '';
        $duration      = isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '';
        $attachment_id = isset( $_POST['attachment_id'] ) ? (int) $_POST['attachment_id'] : 0;

        if ( ! $package_id ) wp_send_json_error( array( 'message' => 'Missing package.' ) );
        if ( ! ES_Packages::get( $package_id ) ) {
            wp_send_json_error( array( 'message' => 'Package not found.' ) );
        }

        // Prefer media-library attachment when provided.
        if ( $attachment_id ) {
            $att_url = wp_get_attachment_url( $attachment_id );
            if ( $att_url ) $url = esc_url_raw( $att_url );
            if ( ! $title ) {
                $att_title = get_the_title( $attachment_id );
                if ( $att_title ) $title = sanitize_text_field( $att_title );
            }
        }

        if ( ! $title || ! $url ) {
            wp_send_json_error( array( 'message' => 'Please choose a video and give it a title.' ) );
        }

        $id = ES_Packages::insert_video( array(
            'target_type'   => 'package',
            'user_id'       => null,
            'group_id'      => null,
            'package_id'    => $package_id,
            'title'         => $title,
            'video_url'     => $url,
            'duration'      => $duration,
            'attachment_id' => $attachment_id ?: null,
        ) );

        if ( ! $id ) wp_send_json_error( array( 'message' => 'Could not add the video.' ) );

        global $wpdb;
        $video = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}es_videos WHERE id = %d", $id ) );
        wp_send_json_success( array( 'message' => 'Video added to package', 'video' => $video ) );
    }

    /**
     * v4.4 — List all videos attached to a package. Used to refresh the videos
     * grid on the Packages admin page after an add/delete.
     */
    public function list_package_videos() {
        $this->check();
        $package_id = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;
        if ( ! $package_id ) wp_send_json_error( array( 'message' => 'Missing package.' ) );
        $videos = ES_Packages::get_package_videos( $package_id );
        wp_send_json_success( array( 'videos' => $videos ) );
    }

    /**
     * v4.4.2 — Upload a "global" file attached to a package (PDF/DOC/PPT or
     * video). Mirrors upload_session_file but stores against package_id with
     * target_type='package'. Visible to every student of the package.
     */
    public function upload_package_file() {
        $this->check();

        $package_id = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;
        if ( ! $package_id || ! ES_Packages::get( $package_id ) ) {
            wp_send_json_error( array( 'message' => 'Package not found.' ) );
        }

        if ( empty( $_FILES['file'] ) || ! isset( $_FILES['file']['tmp_name'] ) ) {
            wp_send_json_error( array( 'message' => 'No file received.' ) );
        }
        $file = $_FILES['file'];
        if ( ! empty( $file['error'] ) ) {
            wp_send_json_error( array( 'message' => 'Upload error (code ' . (int) $file['error'] . ').' ) );
        }

        $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        $type = ES_Packages::ext_to_type( $ext );
        if ( ! $type ) {
            wp_send_json_error( array( 'message' => 'Unsupported file type. Allowed: PDF, DOC/DOCX, PPT/PPTX, images (JPG/PNG/GIF/WebP), video.' ) );
        }

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $mimes_filter = function ( $mimes ) {
            $mimes['pdf']  = 'application/pdf';
            $mimes['doc']  = 'application/msword';
            $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            $mimes['ppt']  = 'application/vnd.ms-powerpoint';
            $mimes['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            $mimes['jpg']  = 'image/jpeg';
            $mimes['jpeg'] = 'image/jpeg';
            $mimes['png']  = 'image/png';
            $mimes['gif']  = 'image/gif';
            $mimes['webp'] = 'image/webp';
            $mimes['mp4']  = 'video/mp4';
            $mimes['mov']  = 'video/quicktime';
            $mimes['webm'] = 'video/webm';
            $mimes['mkv']  = 'video/x-matroska';
            $mimes['avi']  = 'video/x-msvideo';
            return $mimes;
        };
        add_filter( 'upload_mimes', $mimes_filter );
        $moved = wp_handle_upload( $file, array( 'test_form' => false ) );
        remove_filter( 'upload_mimes', $mimes_filter );

        if ( isset( $moved['error'] ) ) {
            wp_send_json_error( array( 'message' => $moved['error'] ) );
        }

        $file_id = ES_Packages::insert_session_file( array(
            'slot_id'     => null,
            'target_type' => 'package',
            'user_id'     => null,
            'group_id'    => null,
            'package_id'  => $package_id,
            'file_name'   => sanitize_file_name( $file['name'] ),
            'file_url'    => esc_url_raw( $moved['url'] ),
            'file_path'   => $moved['file'],
            'file_type'   => $type,
            'file_size'   => (int) ( $file['size'] ?? 0 ),
            'uploaded_by' => get_current_user_id(),
        ) );

        if ( ! $file_id ) {
            wp_send_json_error( array( 'message' => 'Could not save the file record.' ) );
        }

        wp_send_json_success( array(
            'message' => 'File uploaded to package',
            'file'    => ES_Packages::get_session_file( $file_id ),
        ) );
    }

    /* ============== STUDENT PROFILE ============== */

    public function save_student_courses() {
        $this->check();
        $user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        if ( ! $user_id ) wp_send_json_error( array( 'message' => 'Missing student.' ) );

        $course_ids = array();
        if ( isset( $_POST['course_ids'] ) ) {
            $raw = (array) wp_unslash( $_POST['course_ids'] );
            $course_ids = array_map( 'intval', $raw );
        }
        $saved = ES_Packages::set_student_course_ids( $user_id, $course_ids );
        wp_send_json_success( array(
            'message' => 'Courses saved',
            'course_ids' => $saved,
        ) );
    }

    public function save_group_courses() {
        $this->check();
        $group_id = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;
        if ( ! $group_id || ! ES_Packages::get_group( $group_id ) ) {
            wp_send_json_error( array( 'message' => 'Invalid group.' ) );
        }

        $course_ids = array();
        if ( isset( $_POST['course_ids'] ) ) {
            $raw = (array) wp_unslash( $_POST['course_ids'] );
            $course_ids = array_map( 'intval', $raw );
        }
        $saved = ES_Packages::set_group_course_ids( $group_id, $course_ids );
        wp_send_json_success( array(
            'message' => 'Courses saved',
            'course_ids' => $saved,
        ) );
    }

    public function save_student_profile() {
        $this->check();
        $user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        if ( ! $user_id || ! get_userdata( $user_id ) ) {
            wp_send_json_error( array( 'message' => 'Invalid student.' ) );
        }

        $fields = array(
            ES_Packages::META_PHONE  => 'phone',
            ES_Packages::META_PARENT => 'parent',
            ES_Packages::META_SOURCE => 'source',
            ES_Packages::META_GOAL   => 'goal',
            ES_Packages::META_BAND   => 'band',
            ES_Packages::META_NOTES  => 'notes',
        );
        foreach ( $fields as $meta_key => $post_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                $val = $post_key === 'notes'
                    ? sanitize_textarea_field( wp_unslash( $_POST[ $post_key ] ) )
                    : sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
                update_user_meta( $user_id, $meta_key, $val );
            }
        }
        wp_send_json_success( array( 'message' => 'Saved' ) );
    }
}