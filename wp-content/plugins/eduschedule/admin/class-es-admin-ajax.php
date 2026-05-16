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

        // Groups Module
        add_action( 'wp_ajax_es_admin_save_group',      array( $this, 'save_group' ) );
        add_action( 'wp_ajax_es_admin_get_group',       array( $this, 'get_group' ) );
        add_action( 'wp_ajax_es_admin_delete_group',    array( $this, 'delete_group' ) );
        add_action( 'wp_ajax_es_admin_remove_group_member', array( $this, 'remove_group_member' ) );

        // Public package selection
        add_action( 'wp_ajax_es_student_select_package', array( $this, 'student_select_package' ) );
        add_action( 'wp_ajax_nopriv_es_student_select_package', array( $this, 'student_select_package' ) );
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

        $slots = ES_DB::get_slots_in_range( $from, $to );
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
        $this->check();
        $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
        if ( ! ES_Helpers::valid_date( $date ) ) wp_send_json_error( array( 'message' => 'Invalid date' ) );
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
            );
        }
        wp_send_json_success( array( 'date' => $date, 'slots' => $out ) );
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

        ES_DB::delete_slot( $id );
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
        $data = array(
            'package_name'  => isset( $_POST['package_name'] ) ? sanitize_text_field( wp_unslash( $_POST['package_name'] ) ) : '',
            'sub_heading'   => isset( $_POST['sub_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['sub_heading'] ) ) : '',
            'price'         => isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : 0,
            'hours'         => isset( $_POST['hours'] ) ? (int) $_POST['hours'] : 0,
            'tagline'       => isset( $_POST['tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline'] ) ) : '',
            'description'   => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
            'display_order' => isset( $_POST['display_order'] ) ? (int) $_POST['display_order'] : 0,
            'is_active'     => isset( $_POST['is_active'] ) ? 1 : 0,
        );

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

    public function after_call_convert() {
        $this->check();

        $user_id    = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $outcome    = isset( $_POST['outcome'] ) ? sanitize_text_field( wp_unslash( $_POST['outcome'] ) ) : '';
        $comments   = isset( $_POST['comments'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comments'] ) ) : '';
        $group_id   = isset( $_POST['group_id'] ) ? (int) $_POST['group_id'] : 0;

        // Multi-package selection: an array of package IDs the admin staged
        $package_ids = array();
        if ( isset( $_POST['package_ids'] ) && is_array( $_POST['package_ids'] ) ) {
            foreach ( $_POST['package_ids'] as $pid ) {
                $pid = (int) $pid;
                if ( $pid > 0 ) $package_ids[] = $pid;
            }
        }
        // Cap at 3 packages
        $package_ids = array_slice( array_unique( $package_ids ), 0, 3 );

        if ( ! $user_id || ! $outcome ) {
            wp_send_json_error( array( 'message' => 'Missing required fields' ) );
        }

        // For 1:1 / Group student, at least one package required
        $needs_package = in_array( $outcome, array( '1:1 Student', 'Group Student' ), true );
        if ( $needs_package && empty( $package_ids ) ) {
            wp_send_json_error( array( 'message' => 'Please select at least one package' ) );
        }

        if ( $outcome === 'Group Student' && ! $group_id ) {
            wp_send_json_error( array( 'message' => 'Please select a group' ) );
        }

        // Save outcome record + update user category
        $primary_pkg_id = ! empty( $package_ids ) ? (int) $package_ids[0] : 0;
        ES_Packages::link_lead_to_package(
            $user_id,
            $primary_pkg_id,
            $outcome,
            $comments,
            $group_id ? $group_id : null
        );

        // Stage packages for the public shortcode
        if ( ! empty( $package_ids ) ) {
            ES_Packages::set_staged_packages( $user_id, $package_ids );
        } else {
            ES_Packages::clear_staged_packages( $user_id );
        }

        // Generate a token for the public link (only for converting outcomes)
        $share_link = '';
        if ( $needs_package ) {
            $token = ES_Packages::generate_selection_token( $user_id, 14 );

            // Find a page with the [eduschedule_packages] shortcode
            $pkg_page_id = 0;
            $pages_q = get_pages();
            foreach ( $pages_q as $pg ) {
                if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) {
                    $pkg_page_id = $pg->ID;
                    break;
                }
            }
            $base_url = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/' );
            $share_link = add_query_arg( array(
                'user_id' => $user_id,
                'token'   => $token,
            ), $base_url );
        }

        // Send emails
        $user = get_userdata( $user_id );
        if ( $user ) {
            $packages_list = array();
            foreach ( $package_ids as $pid ) {
                $p = ES_Packages::get( $pid );
                if ( $p ) $packages_list[] = $p;
            }

            // Email to student
            $subject = 'Next Steps - ' . get_bloginfo( 'name' );
            $message  = "Hello " . $user->display_name . ",\n\n";
            $message .= "Thank you for speaking with us today!\n\n";

            if ( $needs_package ) {
                $message .= "Based on our conversation, we've prepared the following package(s) for you:\n\n";
                foreach ( $packages_list as $p ) {
                    $message .= "• " . $p->package_name;
                    if ( $p->price > 0 ) $message .= " — ₹" . number_format( $p->price, 0 );
                    $message .= "\n";
                }
                $message .= "\nPlease click the link below to select your preferred package:\n";
                $message .= $share_link . "\n\n";
                $message .= "(This link expires in 14 days.)\n\n";
            } elseif ( $outcome === 'Follow-up Needed' ) {
                $message .= "We'll follow up with you again soon.\n\n";
            } elseif ( $outcome === 'Not Interested' ) {
                $message .= "Thank you for your time. If you change your mind, please don't hesitate to reach out.\n\n";
            }

            if ( $comments ) {
                $message .= "Notes from our call:\n" . $comments . "\n\n";
            }

            $message .= "Best regards,\n" . get_bloginfo( 'name' );

            wp_mail( $user->user_email, $subject, $message );

            // Email to admin
            $admin_email  = get_option( 'admin_email' );
            $admin_subj   = "After Call: {$user->display_name} — {$outcome}";
            $admin_body   = "Lead: {$user->display_name} ({$user->user_email})\n";
            $admin_body  .= "Outcome: {$outcome}\n";
            if ( ! empty( $packages_list ) ) {
                $admin_body .= "Packages staged:\n";
                foreach ( $packages_list as $p ) {
                    $admin_body .= "  - {$p->package_name} (₹" . number_format( $p->price, 0 ) . ")\n";
                }
            }
            if ( $group_id ) {
                $grp = ES_Packages::get_group( $group_id );
                if ( $grp ) $admin_body .= "Group: {$grp->group_name}\n";
            }
            if ( $share_link ) {
                $admin_body .= "\nShare link: {$share_link}\n";
            }
            if ( $comments ) {
                $admin_body .= "\nComments:\n{$comments}\n";
            }
            wp_mail( $admin_email, $admin_subj, $admin_body );
        }

        wp_send_json_success( array(
            'message'    => 'Lead converted successfully. Emails sent.',
            'share_link' => $share_link,
            'reload'     => true,
        ) );
    }

    public function student_select_package() {
        check_ajax_referer( 'es_fe_nonce', 'nonce' );

        $user_id    = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
        $token      = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
        $package_id = isset( $_POST['package_id'] ) ? (int) $_POST['package_id'] : 0;

        if ( ! $user_id || ! $token || ! $package_id ) {
            wp_send_json_error( array( 'message' => 'Invalid request' ) );
        }

        if ( ! ES_Packages::validate_token( $user_id, $token ) ) {
            wp_send_json_error( array( 'message' => 'Invalid or expired link' ) );
        }

        $package = ES_Packages::get( $package_id );
        if ( ! $package ) {
            wp_send_json_error( array( 'message' => 'Package not found' ) );
        }

        // Verify this package was actually staged for this user
        $staged = ES_Packages::get_staged_packages( $user_id );
        if ( ! empty( $staged ) && ! in_array( $package_id, $staged, true ) ) {
            wp_send_json_error( array( 'message' => 'This package is not available for selection' ) );
        }

        // Determine outcome: keep existing category (1:1 or group) from the previous after-call
        $existing_outcome = ES_Packages::get_latest_lead_outcome( $user_id );
        $outcome_label = 'Student Selected';
        $group_id = null;
        if ( $existing_outcome ) {
            $outcome_label = $existing_outcome->outcome;
            $group_id = $existing_outcome->group_id;
        }

        // Record final selection
        ES_Packages::link_lead_to_package( $user_id, $package_id, $outcome_label, '', $group_id );

        // Update assigned package meta
        update_user_meta( $user_id, ES_Packages::META_PACKAGE_ID, $package_id );

        // Clear token (one-time use)
        ES_Packages::clear_token( $user_id );

        // Clear staged
        ES_Packages::clear_staged_packages( $user_id );

        // Build redirect URL (thank you page on same shortcode page)
        $pkg_page_id = 0;
        $pages_q = get_pages();
        foreach ( $pages_q as $pg ) {
            if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) {
                $pkg_page_id = $pg->ID;
                break;
            }
        }
        $base_url = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/' );
        $redirect_url = add_query_arg( array(
            'selected' => 1,
            'pkg'      => $package_id,
        ), $base_url );

        // Send notification emails
        $user = get_userdata( $user_id );
        if ( $user ) {
            $subject  = 'Package Selection Confirmed';
            $message  = "Hello " . $user->display_name . ",\n\n";
            $message .= "Thank you for selecting your package:\n\n";
            $message .= "Package: " . $package->package_name . "\n";
            $message .= "Price: ₹" . number_format( $package->price, 0 ) . "\n";
            if ( $package->hours ) {
                $message .= "Duration: " . $package->hours . " hours\n";
            }
            $message .= "\nWe'll contact you shortly to schedule your first session.\n\n";
            $message .= "Best regards,\n" . get_bloginfo( 'name' );

            wp_mail( $user->user_email, $subject, $message );

            $admin_email   = get_option( 'admin_email' );
            $admin_subject = "Student Package Selection: " . $user->display_name;
            $admin_message = "A student has selected their package:\n\n";
            $admin_message .= "Student: " . $user->display_name . " (" . $user->user_email . ")\n";
            $admin_message .= "Package: " . $package->package_name . "\n";
            $admin_message .= "Price: ₹" . number_format( $package->price, 0 ) . "\n";

            wp_mail( $admin_email, $admin_subject, $admin_message );
        }

        wp_send_json_success( array(
            'message'      => 'Package selected successfully',
            'package_name' => $package->package_name,
            'redirect'     => $redirect_url,
        ) );
    }

    /* =================== GROUPS =================== */

    public function save_group() {
        $this->check();

        $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        $data = array(
            'group_name'     => isset( $_POST['group_name'] ) ? sanitize_text_field( wp_unslash( $_POST['group_name'] ) ) : '',
            'package_id'     => isset( $_POST['package_id'] ) && $_POST['package_id'] ? (int) $_POST['package_id'] : null,
            'duration'       => isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '',
            'total_sessions' => isset( $_POST['total_sessions'] ) ? (int) $_POST['total_sessions'] : 0,
            'color'          => isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : '#6366f1',
            'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
            'is_active'      => 1,
        );

        if ( empty( $data['group_name'] ) ) {
            wp_send_json_error( array( 'message' => 'Group name is required' ) );
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

        wp_send_json_success( array( 'message' => 'Member removed' ) );
    }
}
