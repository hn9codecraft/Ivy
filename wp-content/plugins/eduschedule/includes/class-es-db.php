<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_DB {

    public static function table( $name ) {
        global $wpdb;
        return $wpdb->prefix . 'es_' . $name;
    }

    /* ============== SLOTS ============== */

    public static function insert_slot( $data ) {
        global $wpdb;
        $defaults = array(
            'slot_date' => '',
            'start_time' => '',
            'end_time' => '',
            'duration_min' => 60,
            'slot_type' => '1to1',
            'capacity' => 1,
            'platform' => 'Zoom',
            'title' => '',
            'notes' => '',
            'created_by' => get_current_user_id(),
        );
        $data = wp_parse_args( $data, $defaults );
        if ( $wpdb->insert( self::table( 'slots' ), $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function update_slot( $id, $data ) {
        global $wpdb;
        return $wpdb->update( self::table( 'slots' ), $data, array( 'id' => (int) $id ) );
    }

    public static function delete_slot( $id ) {
        global $wpdb;
        // Cascade: delete bookings on this slot
        $wpdb->delete( self::table( 'bookings' ), array( 'slot_id' => (int) $id ) );
        return $wpdb->delete( self::table( 'slots' ), array( 'id' => (int) $id ) );
    }

    public static function get_slot( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table( 'slots' ) . " WHERE id = %d", (int) $id
        ) );
    }

    public static function get_slots_in_range_calendar( $from, $to ) {

        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT 
                s.*,
                b.*,
                u.display_name AS user_name,
                u.user_email,
                COUNT(b.id) AS booked_count

            FROM " . self::table('slots') . " s

            INNER JOIN " . self::table('bookings') . " b 
                ON b.slot_id = s.id

            LEFT JOIN {$wpdb->users} u
                ON u.ID = b.user_id

            WHERE b.status = %s
            AND s.slot_date BETWEEN %s AND %s

            GROUP BY s.id

            ORDER BY s.slot_date ASC, s.start_time ASC",

            'confirmed',
            $from,
            $to
        );

        $rows = $wpdb->get_results($query);
        return ! empty($rows) ? $rows : array();
    }

    /** Get slots in a date range, optionally with booking counts */
    public static function get_slots_in_range( $from, $to ) {
        global $wpdb;
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT s.*, COUNT(b.id) AS booked_count
             FROM " . self::table( 'slots' ) . " s
             LEFT JOIN " . self::table( 'bookings' ) . " b ON b.slot_id = s.id AND b.status='confirmed'
             WHERE s.slot_date BETWEEN %s AND %s
             GROUP BY s.id
             ORDER BY s.slot_date ASC, s.start_time ASC", $from, $to
        ) );
        return $rows ?: array();
    }

    public static function get_slots_for_date( $date ) {
        return self::get_slots_in_range_calendar( $date, $date );
    }

    public static function count_bookings( $slot_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::table( 'bookings' ) . " WHERE slot_id = %d AND status='confirmed'", (int) $slot_id
        ) );
    }

    /* ============== BOOKINGS ============== */

    public static function insert_booking( $data ) {
        global $wpdb;
        $defaults = array(
            'slot_id' => 0,
            'user_id' => 0,
            'status' => 'confirmed',
            'zoom_meeting_id' => null,
            'zoom_join_url' => null,
            'zoom_start_url' => null,
            'zoom_password' => null,
            'user_note' => '',
        );
        $data = wp_parse_args( $data, $defaults );
        if ( $wpdb->insert( self::table( 'bookings' ), $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function get_booking( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table( 'bookings' ) . " WHERE id = %d", (int) $id
        ) );
    }

    public static function delete_booking( $id ) {
        global $wpdb;
        return $wpdb->delete( self::table( 'bookings' ), array( 'id' => (int) $id ) );
    }

    public static function user_has_booked_slot( $slot_id, $user_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::table( 'bookings' ) . "
             WHERE slot_id = %d AND user_id = %d AND status='confirmed'",
            (int) $slot_id, (int) $user_id
        ) ) > 0;
    }

    /** Get all bookings (for admin list) — joins slot info and user info */
    public static function get_bookings_for_admin( $args = array() ) {
        global $wpdb;
        $defaults = array( 'limit' => 200, 'offset' => 0, 'date_from' => '', 'date_to' => '', 'search' => '' );
        $args = wp_parse_args( $args, $defaults );
        $where = '1=1';
        $params = array();
        if ( ! empty( $args['date_from'] ) ) { $where .= ' AND s.slot_date >= %s'; $params[] = $args['date_from']; }
        if ( ! empty( $args['date_to'] ) )   { $where .= ' AND s.slot_date <= %s'; $params[] = $args['date_to']; }
        if ( ! empty( $args['search'] ) )    {
            $where .= ' AND (u.user_email LIKE %s OR u.display_name LIKE %s)';
            $like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $params[] = $like; $params[] = $like;
        }
        $sql = "SELECT b.*, s.slot_date, s.start_time, s.end_time, s.duration_min, s.slot_type, s.platform, s.title,
                       u.user_email, u.display_name
                FROM " . self::table( 'bookings' ) . " b
                LEFT JOIN " . self::table( 'slots' ) . " s ON s.id = b.slot_id
                LEFT JOIN {$wpdb->users} u ON u.ID = b.user_id
                WHERE $where
                ORDER BY s.slot_date DESC, s.start_time ASC
                LIMIT %d OFFSET %d";
        $params[] = (int) $args['limit'];
        $params[] = (int) $args['offset'];
        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    /** Get current user's bookings (frontend dashboard) */
    public static function get_user_bookings( $user_id, $upcoming_only = false ) {
        global $wpdb;
        $where = 'b.user_id = %d AND b.status = "confirmed"';
        $params = array( (int) $user_id );
        if ( $upcoming_only ) {
            $where .= ' AND s.slot_date >= %s';
            $params[] = current_time( 'Y-m-d' );
        }
        $sql = "SELECT b.*, s.slot_date, s.start_time, s.end_time, s.duration_min, s.slot_type, s.platform, s.title, s.notes
                FROM " . self::table( 'bookings' ) . " b
                LEFT JOIN " . self::table( 'slots' ) . " s ON s.id = b.slot_id
                WHERE $where
                ORDER BY s.slot_date ASC, s.start_time ASC";
        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    /** Stats for admin dashboard */
    public static function admin_stats() {
        global $wpdb;
        $today = current_time( 'Y-m-d' );
        return array(
            'slots_total'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table( 'slots' ) ),
            'slots_upcoming'  => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . self::table( 'slots' ) . " WHERE slot_date >= %s", $today ) ),
            'bookings_total'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table( 'bookings' ) . " WHERE status='confirmed'" ),
            'bookings_today'  => (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM " . self::table( 'bookings' ) . " b
                 LEFT JOIN " . self::table( 'slots' ) . " s ON s.id = b.slot_id
                 WHERE s.slot_date = %s AND b.status='confirmed'", $today ) ),
        );
    }
}
