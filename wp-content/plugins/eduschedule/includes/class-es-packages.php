<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ES_Packages - Packages, Groups & Lead Management
 *
 * User Categories (stored in user meta '_es_user_category'):
 *   'demo'   - Demo Lead (not yet converted)
 *   '1to1'   - 1:1 Student (converted from demo)
 *   'group'  - Group Student (converted from demo)
 *   'lost'   - Not Interested
 */
class ES_Packages {

    const META_CATEGORY    = '_es_user_category';
    const META_PACKAGE_ID  = '_es_assigned_package_id';
    const META_GROUP_ID    = '_es_assigned_group_id';
    const META_TOKEN       = '_es_package_token';
    const META_TOKEN_EXP   = '_es_package_token_expiry';
    const META_STAGED      = '_es_staged_package_ids';
    const META_STAGED_FLOW = '_es_staged_flow_type';
    const META_HAS_1TO1   = '_es_has_1to1';
    const META_HAS_GROUP  = '_es_has_group';

    // Student profile fields surfaced in the tabbed detail UI.
    const META_PHONE   = 'es_phone';
    const META_PARENT  = 'es_parent';
    const META_SOURCE  = 'es_source';
    const META_GOAL    = 'es_goal';
    const META_BAND    = 'es_band';   // e.g. "IELTS Band 7.5"
    const META_NOTES   = 'es_notes';

    /**
     * Create tables on activation
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Packages table
        //  - price          = the FINAL total package price (monthly_price × months)
        //  - monthly_price  = price charged per month
        //  - months         = package duration in months
        //  - monthly_session_limit = how many sessions a student may use each month
        //  - total_sessions = monthly_session_limit × months (cached for convenience)
        $sql = "CREATE TABLE {$wpdb->prefix}es_packages (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            package_name VARCHAR(255) NOT NULL,
            sub_heading VARCHAR(255) NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            monthly_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            months INT NOT NULL DEFAULT 1,
            monthly_session_limit INT NOT NULL DEFAULT 0,
            total_sessions INT NOT NULL DEFAULT 0,
            currency VARCHAR(10) NOT NULL DEFAULT 'INR',
            billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
            yearly_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
            discount_months INT NOT NULL DEFAULT 0,
            hours INT NOT NULL DEFAULT 0,
            tagline VARCHAR(255) NULL,
            description TEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY is_active (is_active),
            KEY display_order (display_order)
        ) $charset_collate;";
        dbDelta( $sql );

        // Payments table — for Stripe transactions
        //  Session columns snapshot the package terms at purchase time so that
        //  later edits to the package don't change what a student already bought.
        //   - months                = duration purchased
        //   - monthly_session_limit = sessions allowed per month
        //   - total_sessions        = monthly_session_limit × months
        //   - used_sessions         = sessions consumed so far
        $sql_payments = "CREATE TABLE {$wpdb->prefix}es_payments (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            package_id BIGINT(20) UNSIGNED NOT NULL,
            package_name VARCHAR(255) NULL,
            course_id BIGINT(20) UNSIGNED NULL,
            course_name VARCHAR(255) NULL,
            flow_type VARCHAR(20) NOT NULL DEFAULT '1to1',
            group_id BIGINT(20) UNSIGNED NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            monthly_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            months INT NOT NULL DEFAULT 1,
            monthly_session_limit INT NOT NULL DEFAULT 0,
            total_sessions INT NOT NULL DEFAULT 0,
            used_sessions INT NOT NULL DEFAULT 0,
            currency VARCHAR(10) NOT NULL DEFAULT 'INR',
            billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
            gateway VARCHAR(30) NOT NULL DEFAULT 'stripe',
            payment_method VARCHAR(50) NULL,
            gateway_session_id VARCHAR(190) NULL,
            gateway_payment_id VARCHAR(190) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            valid_from DATETIME NULL,
            valid_until DATETIME NULL,
            meta TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY package_id (package_id),
            KEY course_id (course_id),
            KEY flow_type (flow_type),
            KEY group_id (group_id),
            KEY status (status),
            KEY gateway_session_id (gateway_session_id)
        ) $charset_collate;";
        dbDelta( $sql_payments );

        // Lead → Package link table
        $sql_links = "CREATE TABLE {$wpdb->prefix}es_lead_packages (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            package_id BIGINT(20) UNSIGNED NOT NULL,
            course_id BIGINT(20) UNSIGNED NULL,
            course_name VARCHAR(255) NULL,
            flow_type VARCHAR(20) NOT NULL DEFAULT '1to1',
            outcome VARCHAR(50) NOT NULL,
            additional_comments TEXT NULL,
            group_id BIGINT(20) UNSIGNED NULL,
            selected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY package_id (package_id),
            KEY outcome (outcome),
            KEY flow_type (flow_type)
        ) $charset_collate;";
        dbDelta( $sql_links );

        // Groups table
        $sql_groups = "CREATE TABLE {$wpdb->prefix}es_groups (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            group_name VARCHAR(255) NOT NULL,
            package_id BIGINT(20) UNSIGNED NULL,
            package_name VARCHAR(255) NULL,
            course_id BIGINT(20) UNSIGNED NULL,
            course_name VARCHAR(255) NULL,
            duration VARCHAR(100) NULL,
            total_sessions INT NOT NULL DEFAULT 0,
            used_sessions INT NOT NULL DEFAULT 0,
            course_ids TEXT NULL,
            color VARCHAR(20) NULL,
            notes TEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY is_active (is_active),
            KEY package_id (package_id)
        ) $charset_collate;";
        dbDelta( $sql_groups );

        // Group members table
        $sql_group_members = "CREATE TABLE {$wpdb->prefix}es_group_members (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            group_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY group_user (group_id, user_id),
            KEY group_id (group_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta( $sql_group_members );

        // Session files table — uploads (PDF / DOCX / PPT / video) attached to a
        // 1:1 or Group session (slot). target_type tells whether the file belongs
        // to a single student ('1to1') or a whole group ('group').
        $sql_session_files = "CREATE TABLE {$wpdb->prefix}es_session_files (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slot_id BIGINT(20) UNSIGNED NULL,
            target_type VARCHAR(20) NOT NULL DEFAULT '1to1',
            user_id BIGINT(20) UNSIGNED NULL,
            group_id BIGINT(20) UNSIGNED NULL,
            file_name VARCHAR(255) NOT NULL,
            package_id BIGINT(20) UNSIGNED NULL,
            file_url TEXT NOT NULL,
            file_path TEXT NULL,
            file_type VARCHAR(20) NOT NULL DEFAULT 'pdf',
            file_size BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            uploaded_by BIGINT(20) UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY slot_id (slot_id),
            KEY user_id (user_id),
            KEY group_id (group_id),
            KEY target_type (target_type)
        ) $charset_collate;";
        dbDelta( $sql_session_files );

        // Attendance table — one row per (session/slot or date) per student.
        // For 1:1, group_id is NULL. For group sessions, both user_id and
        // group_id are set so we can render the group roster.
        $sql_attendance = "CREATE TABLE {$wpdb->prefix}es_attendance (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slot_id BIGINT(20) UNSIGNED NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            group_id BIGINT(20) UNSIGNED NULL,
            target_type VARCHAR(20) NOT NULL DEFAULT '1to1',
            att_date DATE NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'none',
            comment TEXT NULL,
            marked_by BIGINT(20) UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY slot_id (slot_id),
            KEY user_id (user_id),
            KEY group_id (group_id),
            KEY att_date (att_date)
        ) $charset_collate;";
        dbDelta( $sql_attendance );

        // Videos table — lesson recordings / links attached to a 1:1 student
        // or a group. video_url may be an uploaded file URL or an external link
        // (YouTube/Vimeo/etc).
        $sql_videos = "CREATE TABLE {$wpdb->prefix}es_videos (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            target_type VARCHAR(20) NOT NULL DEFAULT '1to1',
            user_id BIGINT(20) UNSIGNED NULL,
            group_id BIGINT(20) UNSIGNED NULL,
            title VARCHAR(255) NOT NULL,
            course_id BIGINT(20) UNSIGNED NULL,
            package_id BIGINT(20) UNSIGNED NULL,
            video_url TEXT NOT NULL,
            duration VARCHAR(20) NULL,
            thumb_url TEXT NULL,
            added_by BIGINT(20) UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY group_id (group_id),
            KEY target_type (target_type)
        ) $charset_collate;";
        dbDelta( $sql_videos );

        // ── Safety migrations for sites upgrading from older schema ──
        self::maybe_add_columns();
    }

    /**
     * Add any columns that dbDelta might not retro-fit on older installs,
     * and ensure the slots table can carry a scheduled meeting link.
     * Safe to run repeatedly.
     */
    public static function maybe_add_columns() {
        global $wpdb;

        $add = function ( $table, $column, $definition ) use ( $wpdb ) {
            $full = $wpdb->prefix . $table;
            // Only ALTER if the column is missing.
            $exists = $wpdb->get_results( $wpdb->prepare(
                "SHOW COLUMNS FROM `{$full}` LIKE %s", $column
            ) );
            if ( empty( $exists ) ) {
                $wpdb->query( "ALTER TABLE `{$full}` ADD COLUMN {$column} {$definition}" );
            }
        };

        // Packages — new pricing / session columns
        $add( 'es_packages', 'monthly_price',          "DECIMAL(10,2) NOT NULL DEFAULT 0" );
        $add( 'es_packages', 'months',                 "INT NOT NULL DEFAULT 1" );
        $add( 'es_packages', 'monthly_session_limit',  "INT NOT NULL DEFAULT 0" );
        $add( 'es_packages', 'total_sessions',         "INT NOT NULL DEFAULT 0" );
        $add( 'es_packages', 'discount_percent',      "DECIMAL(5,2) NOT NULL DEFAULT 0" );
        $add( 'es_packages', 'discount_months',       "INT NOT NULL DEFAULT 0" );

        // Payments — purchase-time snapshot of package/course/session terms
        $add( 'es_payments', 'package_name',           "VARCHAR(255) NULL" );
        $add( 'es_payments', 'course_id',              "BIGINT(20) UNSIGNED NULL" );
        $add( 'es_payments', 'course_name',            "VARCHAR(255) NULL" );
        $add( 'es_payments', 'flow_type',              "VARCHAR(20) NOT NULL DEFAULT '1to1'" );
        $add( 'es_payments', 'group_id',               "BIGINT(20) UNSIGNED NULL" );
        $add( 'es_payments', 'payment_method',         "VARCHAR(50) NULL" );
        $add( 'es_payments', 'monthly_price',          "DECIMAL(10,2) NOT NULL DEFAULT 0" );
        $add( 'es_payments', 'months',                 "INT NOT NULL DEFAULT 1" );
        $add( 'es_payments', 'monthly_session_limit',  "INT NOT NULL DEFAULT 0" );
        $add( 'es_payments', 'total_sessions',         "INT NOT NULL DEFAULT 0" );
        $add( 'es_payments', 'used_sessions',          "INT NOT NULL DEFAULT 0" );

        // Lead links — keep a course snapshot alongside the chosen package.
        $add( 'es_lead_packages', 'course_id',   "BIGINT(20) UNSIGNED NULL" );
        $add( 'es_lead_packages', 'course_name', "VARCHAR(255) NULL" );
        $add( 'es_lead_packages', 'flow_type',   "VARCHAR(20) NOT NULL DEFAULT '1to1'" );
        $add( 'es_lead_packages', 'group_id',    "BIGINT(20) UNSIGNED NULL" );

        // Slots — optional scheduled meeting link (Zoom / Meet / Teams URL)
        $add( 'es_slots', 'meeting_url',   "TEXT NULL" );
        $add( 'es_slots', 'meeting_label', "VARCHAR(190) NULL" );
        // Group sessions are linked directly to their group so they never leak
        // into another group or into a student's 1:1 schedule.
        $add( 'es_slots', 'group_id',      "BIGINT(20) UNSIGNED NULL" );

        // Slots / Videos — optional linked course (post ID from the 'course' CPT)
        $add( 'es_slots',  'course_id', "BIGINT(20) UNSIGNED NULL" );
        $add( 'es_slots',  'course_name', "VARCHAR(255) NULL" );
        $add( 'es_slots',  'package_id', "BIGINT(20) UNSIGNED NULL" );
        $add( 'es_slots',  'package_name', "VARCHAR(255) NULL" );
        $add( 'es_videos', 'course_id', "BIGINT(20) UNSIGNED NULL" );

        // Groups — selected course posts (comma-separated 'course' CPT IDs),
        // mirrors the per-student course selection on the 1:1 page.
        $add( 'es_groups', 'course_ids', "TEXT NULL" );
        $add( 'es_groups', 'package_name', "VARCHAR(255) NULL" );
        $add( 'es_groups', 'course_id', "BIGINT(20) UNSIGNED NULL" );
        $add( 'es_groups', 'course_name', "VARCHAR(255) NULL" );

        // Videos — file metadata for uploaded (vs linked) videos
        $add( 'es_videos', 'attachment_id', "BIGINT(20) UNSIGNED NULL" );

        // Videos — optional package link so admins can add "global" videos to
        // a specific purchased package (v4.4). NULL = legacy / general video.
        $add( 'es_videos', 'package_id', "BIGINT(20) UNSIGNED NULL" );

        // Session files — optional package link mirroring videos. Lets admins
        // attach a file to a particular package even when it isn't tied to one
        // specific scheduled slot (v4.4).
        $add( 'es_session_files', 'package_id', "BIGINT(20) UNSIGNED NULL" );

        // Bookings — which payment / purchased package this booking consumes
        // a session from. NULL = legacy bookings (recount logic falls back to
        // the user's currently active plan). v4.4.
        $add( 'es_bookings', 'payment_id', "BIGINT(20) UNSIGNED NULL" );

        // Attendance status values such as absent_unexcused are longer than 12 chars.
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}es_attendance` MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'none'" );

        // One-time retroactive fix: paid payments that ended up with 0 total
        // sessions (created before the snapshot logic was corrected) get their
        // sessions backfilled from the package now. Safe to run repeatedly.
        self::backfill_paid_session_totals();

        // One-time repair for the attendance double-counting bug: remove
        // duplicate attendance rows (same user/slot/group) keeping the earliest,
        // then recompute every paid plan's used_sessions strictly from
        // attendance. This corrects inflated counters like "3 sessions / 15 used".
        self::repair_attendance_counts();
    }

    /**
     * Repair / migrate session usage to the v4.3 model where a session is
     * consumed at SCHEDULE time (every confirmed 1:1 booking = 1 used session)
     * and only refunded by an "Absent - with permission" attendance mark.
     *  1) Collapse duplicate attendance rows (same user_id + slot_id + group_id)
     *     down to a single row (the earliest id wins).
     *  2) Recompute used_sessions for every paid plan and every group from the
     *     actual scheduled bookings, so counters always match reality.
     * Safe to run repeatedly.
     */
    public static function repair_attendance_counts() {
        global $wpdb;
        $att = $wpdb->prefix . 'es_attendance';
        $pay = $wpdb->prefix . 'es_payments';

        // 1) Delete duplicate attendance rows, keeping the lowest id per key.
        //    NULL-safe grouping via the <=> operator so 1:1 (NULL group) and
        //    group rows are de-duped on their real keys.
        $wpdb->query(
            "DELETE a FROM {$att} a
               JOIN {$att} b
                 ON a.user_id = b.user_id
                AND a.slot_id  <=> b.slot_id
                AND a.group_id <=> b.group_id
                AND a.id > b.id"
        );

        // 2) Recompute used_sessions for each user who has a paid plan.
        $user_ids = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$pay} WHERE status = 'paid'" );
        foreach ( (array) $user_ids as $uid ) {
            self::recount_used_sessions( (int) $uid );
        }

        // 3) Recompute used_sessions for every group from its scheduled meetings.
        $group_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}es_groups" );
        foreach ( (array) $group_ids as $gid ) {
            self::recount_group_used_sessions( (int) $gid );
        }
    }

    /**
     * Backfill total_sessions on any paid payment row that is currently 0 but
     * whose package implies a session allowance. Fixes the "bought a plan but
     * 0 sessions left" situation for purchases made before the fix.
     */
    public static function backfill_paid_session_totals() {
        global $wpdb;
        $pay = $wpdb->prefix . 'es_payments';

        $rows = $wpdb->get_results(
            "SELECT * FROM {$pay} WHERE status = 'paid' AND ( total_sessions IS NULL OR total_sessions = 0 )"
        );
        if ( empty( $rows ) ) return;

        if ( ! class_exists( 'ES_Stripe' ) ) return; // snapshot helper lives there

        foreach ( $rows as $row ) {
            ES_Stripe::backfill_sessions_if_missing( $row );
        }
    }

    /* =================== PACKAGES =================== */

    public static function get_all( $active_only = true ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_packages';
        $where = $active_only ? 'WHERE is_active = 1' : '';
        return $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY display_order ASC, id ASC" );
    }

    public static function get( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_packages';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) );
    }

    public static function insert( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_packages';

        $defaults = array(
            'package_name'          => '',
            'sub_heading'           => '',
            'price'                 => 0,
            'monthly_price'         => 0,
            'months'                => 1,
            'monthly_session_limit' => 0,
            'total_sessions'        => 0,
            'currency'              => 'INR',
            'billing_cycle'         => 'monthly',
            'yearly_price'          => 0,
            'discount_percent'      => 0,
            'discount_months'       => 0,
            'hours'                 => 0,
            'tagline'               => '',
            'description'           => '',
            'is_active'             => 1,
            'display_order'         => 0,
        );
        $data = wp_parse_args( $data, $defaults );
        $data = self::compute_derived( $data );

        if ( $wpdb->insert( $table, $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function update( $id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_packages';
        $data  = self::compute_derived( $data );
        return $wpdb->update( $table, $data, array( 'id' => (int) $id ) );
    }

    /**
     * Auto-calculate the final package price and total sessions from the
     * monthly figures + duration. Total price = monthly_price × months,
     * total sessions = monthly_session_limit × months.
     * Only computes when the source fields are present in $data.
     */
    public static function compute_derived( $data ) {
        $months = isset( $data['months'] ) ? max( 1, (int) $data['months'] ) : null;

        if ( $months !== null ) {
            $data['months'] = $months;

            if ( isset( $data['monthly_price'] ) ) {
                $data['price'] = round( (float) $data['monthly_price'] * $months, 2 );
            }
            if ( isset( $data['monthly_session_limit'] ) ) {
                $data['total_sessions'] = (int) $data['monthly_session_limit'] * $months;
            }
        }
        return $data;
    }

    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_packages';
        return $wpdb->delete( $table, array( 'id' => (int) $id ) );
    }

    /* =================== GROUPS =================== */

    public static function get_all_groups( $active_only = true ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_groups';
        $where = $active_only ? 'WHERE is_active = 1' : '';
        return $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY id DESC" );
    }

    public static function get_group( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_groups';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) );
    }

    /**
     * Prepare group package/course snapshots so group screens don't have to
     * guess from live package data later. Keeps group behaviour aligned with
     * the 1:1 payment snapshot flow.
     */
    public static function prepare_group_data( $data ) {
        if ( isset( $data['package_id'] ) && $data['package_id'] ) {
            $pkg = self::get( (int) $data['package_id'] );
            if ( $pkg ) {
                $data['package_name'] = $pkg->package_name;
                if ( empty( $data['total_sessions'] ) && ! empty( $pkg->total_sessions ) ) {
                    $data['total_sessions'] = (int) $pkg->total_sessions;
                }
                if ( empty( $data['duration'] ) && ! empty( $pkg->months ) ) {
                    $m = (int) $pkg->months;
                    $data['duration'] = $m . ' month' . ( $m === 1 ? '' : 's' );
                }
            }
        }

        $course_ids = array();
        if ( isset( $data['course_ids'] ) ) {
            if ( is_array( $data['course_ids'] ) ) {
                $course_ids = $data['course_ids'];
            } elseif ( is_string( $data['course_ids'] ) && $data['course_ids'] !== '' ) {
                $course_ids = explode( ',', $data['course_ids'] );
            }
        }
        $course_id = self::first_course_id( $course_ids );
        if ( $course_id ) {
            $data['course_id']   = $course_id;
            $data['course_name'] = self::course_name( $course_id );
            $data['course_ids']  = implode( ',', array( $course_id ) );
        } elseif ( isset( $data['course_ids'] ) && $data['course_ids'] === '' ) {
            $data['course_id']   = null;
            $data['course_name'] = '';
        }

        return $data;
    }

    public static function insert_group( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_groups';

        $defaults = array(
            'group_name'     => '',
            'package_id'     => null,
            'package_name'   => '',
            'course_id'      => null,
            'course_name'    => '',
            'duration'       => '',
            'total_sessions' => 0,
            'used_sessions'  => 0,
            'course_ids'     => '',
            'color'          => '#6366f1',
            'notes'          => '',
            'is_active'      => 1,
        );
        $data = wp_parse_args( $data, $defaults );
        $data = self::prepare_group_data( $data );

        if ( $wpdb->insert( $table, $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function update_group( $id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_groups';
        $data  = self::prepare_group_data( $data );
        return $wpdb->update( $table, $data, array( 'id' => (int) $id ) );
    }

    public static function delete_group( $id ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'es_group_members', array( 'group_id' => (int) $id ) );
        return $wpdb->delete( $wpdb->prefix . 'es_groups', array( 'id' => (int) $id ) );
    }

    public static function add_user_to_group( $group_id, $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_group_members';
        // Avoid duplicate
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE group_id = %d AND user_id = %d",
            (int) $group_id, (int) $user_id
        ) );
        if ( $exists ) return true;
        return $wpdb->insert( $table, array(
            'group_id' => (int) $group_id,
            'user_id'  => (int) $user_id,
        ) );
    }

    public static function remove_user_from_group( $group_id, $user_id ) {
        global $wpdb;
        return $wpdb->delete( $wpdb->prefix . 'es_group_members', array(
            'group_id' => (int) $group_id,
            'user_id'  => (int) $user_id,
        ) );
    }

    public static function get_group_members( $group_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_group_members';
        $ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT user_id FROM {$table} WHERE group_id = %d", (int) $group_id
        ) );
        if ( empty( $ids ) ) return array();
        return get_users( array( 'include' => $ids ) );
    }

    public static function count_group_members( $group_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}es_group_members WHERE group_id = %d",
            (int) $group_id
        ) );
    }

    public static function get_group_assignable_users( $group_id = 0, $limit = 500 ) {
        $exclude = array();
        if ( $group_id ) {
            foreach ( self::get_group_members( $group_id ) as $m ) {
                $exclude[] = (int) $m->ID;
            }
        }
        return get_users( array(
            'orderby'      => 'display_name',
            'order'        => 'ASC',
            'number'       => $limit,
            'exclude'      => $exclude,
            'role__not_in' => array( 'administrator' ),
        ) );
    }

    public static function get_user_groups( $user_id ) {
        global $wpdb;
        $gm = $wpdb->prefix . 'es_group_members';
        $g  = $wpdb->prefix . 'es_groups';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT g.* FROM {$g} g
             INNER JOIN {$gm} gm ON gm.group_id = g.id
             WHERE gm.user_id = %d",
            (int) $user_id
        ) );
    }

    /* =================== LEAD MANAGEMENT =================== */

    /**
     * Link a lead to a package + record outcome
     * Also updates the user's category meta
     */
    public static function link_lead_to_package( $user_id, $package_id, $outcome, $comments = '', $group_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_lead_packages';

        $category  = self::outcome_to_category( $outcome );
        $flow_type = ( $category === 'group' || $group_id ) ? 'group' : '1to1';

        $wpdb->insert( $table, array(
            'user_id'             => (int) $user_id,
            'package_id'          => (int) $package_id,
            'flow_type'           => $flow_type,
            'outcome'             => sanitize_text_field( $outcome ),
            'additional_comments' => sanitize_textarea_field( $comments ),
            'group_id'            => $group_id ? (int) $group_id : null,
        ) );

        $insert_id = $wpdb->insert_id;

        // v4.5.8: keep 1:1 and Group workflows independent. A student can
        // keep their 1:1 data while also joining a group, and vice versa.
        if ( $category === '1to1' ) {
            update_user_meta( $user_id, self::META_HAS_1TO1, 1 );
            $current = self::get_user_category( $user_id );
            if ( ! in_array( $current, array( 'group', 'lost' ), true ) ) {
                update_user_meta( $user_id, self::META_CATEGORY, '1to1' );
            }
            if ( $package_id > 0 ) {
                update_user_meta( $user_id, self::META_PACKAGE_ID, (int) $package_id );
            }
        } elseif ( $category === 'group' ) {
            update_user_meta( $user_id, self::META_HAS_GROUP, 1 );
            $current = self::get_user_category( $user_id );
            if ( ! self::user_has_flow( $user_id, '1to1' ) && ! in_array( $current, array( '1to1', 'lost' ), true ) ) {
                update_user_meta( $user_id, self::META_CATEGORY, 'group' );
            }
            if ( $group_id ) {
                update_user_meta( $user_id, self::META_GROUP_ID, (int) $group_id );
                self::add_user_to_group( $group_id, $user_id );
            }
        } else {
            update_user_meta( $user_id, self::META_CATEGORY, $category );
        }

        return $insert_id;
    }

    /**
     * Convert outcome label to category slug
     */
    public static function outcome_to_category( $outcome ) {
        $map = array(
            '1:1 Student'      => '1to1',
            'Group Student'    => 'group',
            'Follow-up Needed' => 'demo',
            'Not Interested'   => 'lost',
            'Renewed'          => '1to1',
        );
        return isset( $map[ $outcome ] ) ? $map[ $outcome ] : 'demo';
    }

    public static function get_user_category( $user_id ) {
        $cat = get_user_meta( $user_id, self::META_CATEGORY, true );
        return $cat ?: 'demo';
    }

    public static function user_has_flow( $user_id, $flow_type ) {
        $flow_type = $flow_type === 'group' ? 'group' : '1to1';
        $cat = self::get_user_category( $user_id );
        if ( $flow_type === '1to1' ) {
            return $cat === '1to1' || (bool) get_user_meta( $user_id, self::META_HAS_1TO1, true );
        }
        return $cat === 'group' || (bool) get_user_meta( $user_id, self::META_HAS_GROUP, true ) || ! empty( self::get_user_groups( $user_id ) );
    }

    public static function get_lead_packages( $user_id ) {
        global $wpdb;
        $link_table = $wpdb->prefix . 'es_lead_packages';
        $pkg_table  = $wpdb->prefix . 'es_packages';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT lp.*, p.package_name, p.sub_heading, p.price, p.hours, p.tagline, p.description
             FROM {$link_table} lp
             LEFT JOIN {$pkg_table} p ON p.id = lp.package_id
             WHERE lp.user_id = %d
             ORDER BY lp.selected_at DESC",
            (int) $user_id
        ) );
    }

    public static function get_latest_lead_outcome( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_lead_packages';
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY selected_at DESC LIMIT 1",
            (int) $user_id
        ) );
    }

    /**
     * Get users by category
     *
     * @param string $category 'demo', '1to1', 'group', 'lost', or 'all'
     */
    public static function get_users_by_category( $category = 'demo', $limit = 500 ) {
        if ( $category === 'all' ) {
            return get_users( array( 'orderby' => 'registered', 'order' => 'DESC', 'number' => $limit ) );
        }

        if ( $category === 'demo' ) {
            // demo = users explicitly tagged 'demo' OR users with NO category meta yet (and not admin)
            $args = array(
                'orderby'      => 'registered',
                'order'        => 'DESC',
                'number'       => $limit,
                'role__not_in' => array( 'administrator', 'editor' ),
                'meta_query'   => array(
                    'relation' => 'OR',
                    array(
                        'key'     => self::META_CATEGORY,
                        'value'   => 'demo',
                        'compare' => '=',
                    ),
                    array(
                        'key'     => self::META_CATEGORY,
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );
            return get_users( $args );
        }

        if ( $category === '1to1' ) {
            return get_users( array(
                'orderby'  => 'registered',
                'order'    => 'DESC',
                'number'   => $limit,
                'meta_query' => array(
                    'relation' => 'OR',
                    array( 'key' => self::META_CATEGORY,  'value' => '1to1', 'compare' => '=' ),
                    array( 'key' => self::META_HAS_1TO1, 'value' => '1',    'compare' => '=' ),
                ),
            ) );
        }

        if ( $category === 'group' ) {
            return get_users( array(
                'orderby'  => 'registered',
                'order'    => 'DESC',
                'number'   => $limit,
                'meta_query' => array(
                    'relation' => 'OR',
                    array( 'key' => self::META_CATEGORY,   'value' => 'group', 'compare' => '=' ),
                    array( 'key' => self::META_HAS_GROUP, 'value' => '1',     'compare' => '=' ),
                ),
            ) );
        }

        return get_users( array(
            'orderby'    => 'registered',
            'order'      => 'DESC',
            'number'     => $limit,
            'meta_key'   => self::META_CATEGORY,
            'meta_value' => $category,
        ) );
    }

    public static function count_users_by_category( $category ) {
        return count( self::get_users_by_category( $category ) );
    }

    /* =================== TOKEN (for shortcode link) =================== */

    public static function generate_selection_token( $user_id, $expiry_days = 7 ) {
        $token = wp_generate_password( 32, false );
        update_user_meta( $user_id, self::META_TOKEN, $token );
        update_user_meta( $user_id, self::META_TOKEN_EXP, time() + ( $expiry_days * DAY_IN_SECONDS ) );
        return $token;
    }

    public static function validate_token( $user_id, $token ) {
        $stored = get_user_meta( $user_id, self::META_TOKEN, true );
        $exp    = get_user_meta( $user_id, self::META_TOKEN_EXP, true );

        if ( empty( $stored ) || $stored !== $token ) return false;
        if ( empty( $exp ) || time() > (int) $exp )   return false;

        return true;
    }

    public static function clear_token( $user_id ) {
        delete_user_meta( $user_id, self::META_TOKEN );
        delete_user_meta( $user_id, self::META_TOKEN_EXP );
    }

    /**
     * Get the package_ids the admin staged for the public shortcode for this user.
     */
    public static function get_staged_packages( $user_id ) {
        $raw = get_user_meta( $user_id, self::META_STAGED, true );
        if ( empty( $raw ) ) return array();
        return array_filter( array_map( 'intval', explode( ',', $raw ) ) );
    }

    public static function set_staged_packages( $user_id, array $package_ids ) {
        $ids = array_filter( array_map( 'intval', $package_ids ) );
        update_user_meta( $user_id, self::META_STAGED, implode( ',', $ids ) );
    }

    public static function set_staged_flow( $user_id, $flow_type ) {
        $flow_type = $flow_type === 'group' ? 'group' : '1to1';
        update_user_meta( $user_id, self::META_STAGED_FLOW, $flow_type );
    }

    public static function get_staged_flow( $user_id ) {
        $flow = get_user_meta( $user_id, self::META_STAGED_FLOW, true );
        return $flow === 'group' ? 'group' : '1to1';
    }

    public static function clear_staged_packages( $user_id ) {
        delete_user_meta( $user_id, self::META_STAGED );
        delete_user_meta( $user_id, self::META_STAGED_FLOW );
    }

    /* =================== PAYMENTS =================== */

    /**
     * Get a single user's PAID payments, newest first, joined to package +
     * the latest lead outcome (1:1 / Group). Used by the student dashboard.
     */
    public static function get_user_payments( $user_id, $paid_only = true, $flow_type = 'all' ) {
        global $wpdb;
        $pay   = $wpdb->prefix . 'es_payments';
        $pkg   = $wpdb->prefix . 'es_packages';

        $where = 'p.user_id = %d';
        if ( $paid_only ) $where .= " AND p.status = 'paid'";
        $flow_type = in_array( $flow_type, array( '1to1', 'group', 'all' ), true ) ? $flow_type : 'all';
        if ( $flow_type !== 'all' ) {
            if ( $flow_type === 'group' ) {
                $where .= " AND (p.flow_type = 'group' OR (p.group_id IS NOT NULL AND p.group_id > 0))";
            } else {
                $where .= " AND (p.flow_type IS NULL OR p.flow_type = '' OR p.flow_type = '1to1') AND (p.group_id IS NULL OR p.group_id = 0)";
            }
        }

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT p.*, COALESCE(NULLIF(p.package_name, ''), k.package_name) AS package_name, k.sub_heading
               FROM {$pay} p
               LEFT JOIN {$pkg} k ON k.id = p.package_id
              WHERE {$where}
              ORDER BY p.created_at DESC",
            (int) $user_id
        ) );
    }

    /**
     * Get ALL payments for the admin Payments screen, joined to the WP user
     * and package. Supports an optional status filter and search term.
     *
     * @param array $args  ['status' => 'paid'|'pending'|'all', 'search' => '...']
     */
    public static function get_all_payments( $args = array() ) {
        global $wpdb;
        $pay   = $wpdb->prefix . 'es_payments';
        $pkg   = $wpdb->prefix . 'es_packages';
        $users = $wpdb->users;

        $status = isset( $args['status'] ) ? $args['status'] : 'all';
        $search = isset( $args['search'] ) ? trim( $args['search'] ) : '';

        $where  = '1=1';
        $params = array();

        if ( $status && $status !== 'all' ) {
            $where   .= ' AND p.status = %s';
            $params[] = $status;
        }
        if ( $search !== '' ) {
            $where   .= ' AND ( u.display_name LIKE %s OR u.user_email LIKE %s OR k.package_name LIKE %s )';
            $like     = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }

        $sql = "SELECT p.*, u.display_name, u.user_email, COALESCE(NULLIF(p.package_name, ''), k.package_name) AS package_name
                  FROM {$pay} p
                  LEFT JOIN {$users} u ON u.ID = p.user_id
                  LEFT JOIN {$pkg} k   ON k.id = p.package_id
                 WHERE {$where}
                 ORDER BY p.created_at DESC";

        if ( $params ) {
            $sql = $wpdb->prepare( $sql, $params );
        }
        return $wpdb->get_results( $sql );
    }

    /**
     * Number of months a billing cycle covers. After the v3.9.6 pricing
     * change, the "yearly"/discounted toggle is just a cheaper monthly rate,
     * so every cycle grants 1 month of access.
     */
    public static function cycle_months( $billing_cycle ) {
        return 1;
    }

    /**
     * The student's current category label (1:1 Student / Group Student / …).
     */
    public static function category_label( $user_id ) {
        $map = array(
            '1to1'  => '1:1',
            'group' => 'Group',
            'demo'  => 'Demo',
            'lost'  => 'Not Interested',
        );
        $cat = self::get_user_category( $user_id );
        return isset( $map[ $cat ] ) ? $map[ $cat ] : ucfirst( $cat );
    }

    /* =================== ACTIVE PLAN / SESSIONS =================== */

    /**
     * Return the student's current (most recent paid, still-valid) plan as a
     * payment row, or null. Falls back to the latest paid row if none are
     * within their validity window.
     */
    public static function get_active_plan( $user_id, $flow_type = '1to1' ) {
        $payments = self::get_user_payments( $user_id, true, $flow_type );
        if ( empty( $payments ) ) return null;

        $now = current_time( 'timestamp' );
        foreach ( $payments as $p ) {
            if ( empty( $p->valid_until ) || strtotime( $p->valid_until ) >= $now ) {
                return class_exists( 'ES_Stripe' ) ? ES_Stripe::backfill_sessions_if_missing( $p ) : $p;
            }
        }
        return class_exists( 'ES_Stripe' ) ? ES_Stripe::backfill_sessions_if_missing( $payments[0] ) : $payments[0];
    }

    /**
     * Every paid payment that still has sessions left AND has not expired.
     * Used by the per-session-package picker in the Schedule modal so admins
     * can choose which purchased package to consume the session from when a
     * student has multiple active packages stacked. v4.4.
     *
     * Returned payment rows are decorated with .remaining so the UI can render
     * "Package — X left" labels without recomputing.
     *
     * @return object[]
     */
    public static function get_schedulable_payments( $user_id ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) return array();

        $payments = self::get_user_payments( $user_id, true, '1to1' );
        if ( empty( $payments ) ) return array();

        $now = current_time( 'timestamp' );
        $out = array();
        foreach ( $payments as $p ) {
            if ( ! empty( $p->valid_until ) && strtotime( $p->valid_until ) < $now ) continue;
            $total = (int) ( $p->total_sessions ?? 0 );
            $used  = (int) ( $p->used_sessions ?? 0 );
            $left  = max( 0, $total - $used );
            if ( $total > 0 && $left <= 0 ) continue;   // no sessions left
            $p->remaining = $left;
            $out[] = $p;
        }
        return $out;
    }

    /**
     * Does the user currently hold a still-valid (not-yet-expired) paid plan
     * for THIS specific package? Used to stop a student buying the same
     * package twice while it's still active.
     *
     * A payment counts as "active" when its status is paid and its
     * valid_until is either empty (lifetime) or in the future.
     *
     * @return object|null  The active payment row for the package, or null.
     */
    public static function get_active_package_payment( $user_id, $package_id, $flow_type = 'all', $group_id = 0 ) {
        $user_id    = (int) $user_id;
        $package_id = (int) $package_id;
        $group_id   = (int) $group_id;
        if ( ! $user_id || ! $package_id ) return null;

        $payments = self::get_user_payments( $user_id, true, $flow_type );
        if ( empty( $payments ) ) return null;

        $now = current_time( 'timestamp' );
        foreach ( $payments as $p ) {
            if ( (int) $p->package_id !== $package_id ) continue;
            if ( $flow_type === 'group' && $group_id && (int) ( $p->group_id ?? 0 ) !== $group_id ) continue;
            if ( $flow_type === '1to1' && ! empty( $p->group_id ) ) continue;
            if ( empty( $p->valid_until ) || strtotime( $p->valid_until ) >= $now ) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Convenience boolean wrapper around get_active_package_payment().
     */
    public static function has_active_package( $user_id, $package_id ) {
        return (bool) self::get_active_package_payment( $user_id, $package_id );
    }

    /**
     * IDs of every package the user holds an active (still-valid) paid plan
     * for. Handy for the admin UI to disable already-owned options.
     *
     * @return int[]
     */
    public static function get_active_package_ids( $user_id, $flow_type = '1to1' ) {
        $user_id = (int) $user_id;
        if ( ! $user_id ) return array();

        $flow_type = in_array( $flow_type, array( '1to1', 'group', 'all' ), true ) ? $flow_type : '1to1';
        $payments = self::get_user_payments( $user_id, true, $flow_type );
        if ( empty( $payments ) ) return array();

        $now = current_time( 'timestamp' );
        $ids = array();
        foreach ( $payments as $p ) {
            $not_expired = empty( $p->valid_until ) || strtotime( $p->valid_until ) >= $now;
            if ( $not_expired && self::remaining_sessions( $p ) > 0 ) {
                $ids[] = (int) $p->package_id;
            }
        }
        return array_values( array_unique( array_filter( $ids ) ) );
    }

    /**
     * Remaining sessions on a payment row (never below zero).
     */
    public static function remaining_sessions( $payment ) {
        if ( ! $payment ) return 0;
        $total = (int) ( $payment->total_sessions ?? 0 );
        $used  = (int) ( $payment->used_sessions ?? 0 );
        return max( 0, $total - $used );
    }

    /**
     * Increment / decrement the used-sessions counter on a payment row.
     * Used when an admin marks attendance or schedules a session.
     */
    public static function adjust_used_sessions( $payment_id, $delta = 1 ) {
        global $wpdb;
        $pay = $wpdb->prefix . 'es_payments';
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT total_sessions, used_sessions FROM {$pay} WHERE id = %d", (int) $payment_id ) );
        if ( ! $row ) return false;

        $new_used = (int) $row->used_sessions + (int) $delta;
        $new_used = max( 0, min( (int) $row->total_sessions, $new_used ) );

        return $wpdb->update(
            $pay,
            array( 'used_sessions' => $new_used, 'updated_at' => current_time( 'mysql' ) ),
            array( 'id' => (int) $payment_id )
        );
    }

    /**
     * Recompute a GROUP's used_sessions strictly from its own scheduled
     * meetings (minus sessions fully cancelled). Each distinct group meeting
     * (slot) the group is booked into consumes one of the group's shared
     * sessions. Group sessions are independent of individual student plans.
     *
     * @return int  The corrected used-session count for the group.
     */
    public static function recount_group_used_sessions( $group_id ) {
        global $wpdb;
        $group = self::get_group( $group_id );
        if ( ! $group ) return 0;

        $b  = $wpdb->prefix . 'es_bookings';
        $s  = $wpdb->prefix . 'es_slots';
        $gm = $wpdb->prefix . 'es_group_members';

        // Distinct GROUP slots only. Do not count 1:1 bookings made for
        // members of this group. New slots carry sl.group_id; older group slots
        // are accepted through the NULL/0 fallback as long as slot_type=group.
        $used = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT sl.id)
               FROM {$s} sl
               INNER JOIN {$b} bk ON bk.slot_id = sl.id AND bk.status = 'confirmed'
               INNER JOIN {$gm} mm ON mm.user_id = bk.user_id AND mm.group_id = %d
              WHERE sl.slot_type = 'group'
                AND sl.group_id = %d",
            (int) $group_id, (int) $group_id
        ) );

        $total = (int) ( $group->total_sessions ?? 0 );
        if ( $total > 0 && $used > $total ) $used = $total;

        $wpdb->update(
            $wpdb->prefix . 'es_groups',
            array( 'used_sessions' => $used, 'updated_at' => current_time( 'mysql' ) ),
            array( 'id' => (int) $group_id )
        );

        self::recount_group_payment_used_sessions( (int) $group_id );

        return $used;
    }

    /**
     * Recompute used_sessions for each paid payment row attached to a group.
     * A group meeting creates one booking per member, so we count DISTINCT slots
     * per payment_id to avoid multiplying one class by the number of students.
     */
    public static function recount_group_payment_used_sessions( $group_id ) {
        global $wpdb;
        $group_id = (int) $group_id;
        if ( ! $group_id ) return 0;

        $pay = $wpdb->prefix . 'es_payments';
        $b   = $wpdb->prefix . 'es_bookings';
        $s   = $wpdb->prefix . 'es_slots';

        $payments = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, total_sessions FROM {$pay} WHERE group_id = %d AND status = 'paid'",
            $group_id
        ) );
        if ( empty( $payments ) ) return 0;

        $latest_used = 0;
        foreach ( $payments as $row ) {
            $pid  = (int) $row->id;
            $used = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT sl.id)
                   FROM {$b} bk
                   INNER JOIN {$s} sl ON sl.id = bk.slot_id
                  WHERE bk.payment_id = %d
                    AND bk.status = 'confirmed'
                    AND sl.slot_type = 'group'
                    AND sl.group_id = %d",
                $pid, $group_id
            ) );
            $total = (int) $row->total_sessions;
            if ( $total > 0 && $used > $total ) $used = $total;
            $wpdb->update(
                $pay,
                array( 'used_sessions' => $used, 'updated_at' => current_time( 'mysql' ) ),
                array( 'id' => $pid )
            );
            $latest_used = $used;
        }
        return $latest_used;
    }

    /**
     * Convenience wrapper: nudge a group's used-session count and re-derive it
     * from scheduled meetings. The $delta is advisory only; the authoritative
     * value is always recomputed from the group's actual bookings so the count
     * can never drift.
     */
    public static function adjust_group_used_sessions( $group_id, $delta = 1 ) {
        return self::recount_group_used_sessions( $group_id );
    }

    /* =================== SESSION FILES (UPLOADS) =================== */

    /** Allowed upload types → extensions/mimes. */
    public static function allowed_upload_types() {
        return array(
            'pdf'   => array( 'ext' => array( 'pdf' ),                             'label' => 'PDF' ),
            'doc'   => array( 'ext' => array( 'doc', 'docx' ),                     'label' => 'Word' ),
            'ppt'   => array( 'ext' => array( 'ppt', 'pptx' ),                     'label' => 'PowerPoint' ),
            'image' => array( 'ext' => array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ), 'label' => 'Image' ),
            'video' => array( 'ext' => array( 'mp4', 'mov', 'webm', 'mkv', 'avi' ), 'label' => 'Video' ),
        );
    }

    /** Map a file extension to one of our type buckets, or false if unsupported. */
    public static function ext_to_type( $ext ) {
        $ext = strtolower( $ext );
        foreach ( self::allowed_upload_types() as $type => $info ) {
            if ( in_array( $ext, $info['ext'], true ) ) return $type;
        }
        return false;
    }

    public static function insert_session_file( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_session_files';

        $defaults = array(
            'slot_id'     => null,
            'target_type' => '1to1',
            'user_id'     => null,
            'group_id'    => null,
            'package_id'  => null,   // v4.4 — per-package "global" files
            'file_name'   => '',
            'file_url'    => '',
            'file_path'   => '',
            'file_type'   => 'pdf',
            'file_size'   => 0,
            'uploaded_by' => get_current_user_id(),
        );
        $data = wp_parse_args( $data, $defaults );

        if ( $wpdb->insert( $table, $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function get_session_file( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_session_files';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) );
    }

    /**
     * Files for a specific 1:1 student (by user_id) or a group (by group_id).
     *
     * @param string $target_type '1to1' | 'group'
     * @param int    $target_id   user_id when 1to1, group_id when group
     */
    public static function get_session_files( $target_type, $target_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_session_files';

        if ( $target_type === 'group' ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE target_type = 'group' AND group_id = %d ORDER BY created_at DESC",
                (int) $target_id
            ) );
        }
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE target_type = '1to1' AND user_id = %d ORDER BY created_at DESC",
            (int) $target_id
        ) );
    }

    /**
     * v4.4.2 — Per-package "global" files (PDF/DOC/PPT/video uploads attached
     * to a package, not to one student or session). These travel with the
     * package and are visible to every student who owns it. target_type =
     * 'package', user_id/group_id = NULL, package_id = the package.
     */
    public static function get_package_files( $package_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_session_files';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE package_id = %d AND target_type = 'package' ORDER BY created_at DESC",
            (int) $package_id
        ) );
    }

    /**
     * Group this target's uploaded files by their slot_id. Useful for the
     * Schedule tab to render "what's attached to each session". Files with
     * NULL slot_id (general / global) are keyed under 0. v4.4.
     *
     * @return array  [ slot_id|0 => [ ...files ] ]
     */
    public static function get_session_files_by_slot( $target_type, $target_id ) {
        $rows = self::get_session_files( $target_type, $target_id );
        $out  = array();
        foreach ( $rows as $r ) {
            $k = (int) ( $r->slot_id ?? 0 );
            if ( ! isset( $out[ $k ] ) ) $out[ $k ] = array();
            $out[ $k ][] = $r;
        }
        return $out;
    }

    public static function delete_session_file( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_session_files';

        // Remove the physical file if it lives in uploads.
        $row = self::get_session_file( $id );
        if ( $row && ! empty( $row->file_path ) && file_exists( $row->file_path ) ) {
            @unlink( $row->file_path );
        }
        return $wpdb->delete( $table, array( 'id' => (int) $id ) );
    }

    /* =================== ATTENDANCE =================== */

    /**
     * Upcoming/recent sessions (slot bookings) for a student, newest first.
     * Used to render the Attendance tab rows.
     */
    public static function get_student_sessions( $user_id, $limit = 12 ) {
        global $wpdb;
        $b = $wpdb->prefix . 'es_bookings';
        $s = $wpdb->prefix . 'es_slots';
        $p = $wpdb->prefix . 'es_payments';
        $k = $wpdb->prefix . 'es_packages';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT bk.id AS booking_id, bk.payment_id,
                    pay.package_id AS pkg_id,
                    COALESCE(NULLIF(sl.package_name, ''), NULLIF(pay.package_name, ''), pk.package_name) AS pkg_name,
                    pay.course_id AS payment_course_id,
                    COALESCE(NULLIF(pay.course_name, ''), '') AS payment_course_name,
                    sl.id AS slot_id, sl.slot_date, sl.start_time,
                    sl.duration_min, sl.title, sl.slot_type, sl.course_id,
                    sl.course_name AS slot_course_name,
                    sl.package_id AS slot_package_id,
                    sl.package_name AS slot_package_name
               FROM {$b} bk
               INNER JOIN {$s} sl ON sl.id = bk.slot_id
               LEFT JOIN {$p} pay ON pay.id = bk.payment_id
               LEFT JOIN {$k} pk  ON pk.id  = pay.package_id
              WHERE bk.user_id = %d
                AND bk.status != 'cancelled'
                AND sl.slot_type = '1to1'
              ORDER BY sl.slot_date DESC, sl.start_time DESC
              LIMIT %d",
            (int) $user_id, (int) $limit
        ) );
    }

    /**
     * Full schedule (all meetings) for a single 1:1 student, with platform,
     * Zoom links and status — used for the read-only "Schedule" tab.
     */
    public static function get_student_schedule( $user_id, $limit = 100 ) {
        global $wpdb;
        $b = $wpdb->prefix . 'es_bookings';
        $s = $wpdb->prefix . 'es_slots';
        $p = $wpdb->prefix . 'es_payments';
        $k = $wpdb->prefix . 'es_packages';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT bk.id AS booking_id, bk.status AS booking_status,
                    bk.zoom_join_url, bk.zoom_start_url, bk.zoom_meeting_id,
                    bk.payment_id,
                    sl.id AS slot_id, sl.slot_date, sl.start_time, sl.end_time,
                    sl.duration_min, sl.title, sl.slot_type, sl.platform, sl.notes, sl.course_id,
                    sl.course_name AS slot_course_name,
                    sl.package_id AS slot_package_id,
                    sl.package_name AS slot_package_name,
                    pay.package_id AS pkg_id,
                    COALESCE(NULLIF(sl.package_name, ''), NULLIF(pay.package_name, ''), pk.package_name) AS pkg_name,
                    pay.course_id AS payment_course_id, pay.course_name AS payment_course_name
               FROM {$b} bk
               INNER JOIN {$s} sl ON sl.id = bk.slot_id
               LEFT JOIN {$p} pay ON pay.id = bk.payment_id
               LEFT JOIN {$k} pk  ON pk.id  = pay.package_id
              WHERE bk.user_id = %d
                AND bk.status != 'cancelled'
                AND sl.slot_type = '1to1'
              ORDER BY sl.slot_date DESC, sl.start_time DESC
              LIMIT %d",
            (int) $user_id, (int) $limit
        ) );
    }

    /**
     * Full schedule (all meetings) for a whole group — distinct slots that the
     * group's members are booked into, with platform, Zoom link and status.
     */
    public static function get_group_schedule( $group_id, $limit = 100 ) {
        global $wpdb;
        $b  = $wpdb->prefix . 'es_bookings';
        $s  = $wpdb->prefix . 'es_slots';
        $gm = $wpdb->prefix . 'es_group_members';
        $g  = $wpdb->prefix . 'es_groups';
        $pk = $wpdb->prefix . 'es_packages';

        // Only show real GROUP slots here. Earlier versions joined through
        // group_members only, so a member's 1:1 sessions appeared inside the
        // group screen. group_id is used for new slots; the slot_type fallback
        // keeps older group slots visible without leaking 1:1 meetings.
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT MIN(bk.id) AS booking_id, MAX(bk.zoom_join_url) AS zoom_join_url,
                    MAX(bk.zoom_meeting_id) AS zoom_meeting_id,
                    sl.id AS slot_id, sl.slot_date, sl.start_time, sl.end_time,
                    sl.duration_min, sl.title, sl.slot_type, sl.platform, sl.notes, sl.course_id,
                    sl.course_name AS slot_course_name,
                    sl.package_id AS slot_package_id,
                    sl.package_name AS slot_package_name,
                    sl.group_id AS slot_group_id,
                    MAX(COALESCE(NULLIF(sl.package_name, ''), NULLIF(gr.package_name, ''), pkg.package_name)) AS package_name,
                    MAX(COALESCE(NULLIF(sl.course_name, ''), NULLIF(gr.course_name, ''), '')) AS schedule_course_name,
                    MAX(COALESCE(sl.package_id, gr.package_id)) AS schedule_package_id,
                    COUNT(DISTINCT bk.user_id) AS attendee_count
               FROM {$b} bk
               INNER JOIN {$s} sl ON sl.id = bk.slot_id
               INNER JOIN {$gm} gm ON gm.user_id = bk.user_id AND gm.group_id = %d
               LEFT JOIN {$g} gr ON gr.id = %d
               LEFT JOIN {$pk} pkg ON pkg.id = COALESCE(sl.package_id, gr.package_id)
              WHERE bk.status != 'cancelled'
                AND sl.slot_type = 'group'
                AND sl.group_id = %d
              GROUP BY sl.id, sl.slot_date, sl.start_time, sl.end_time,
                       sl.duration_min, sl.title, sl.slot_type, sl.platform, sl.notes, sl.course_id, sl.course_name, sl.package_id, sl.package_name, sl.group_id
              ORDER BY sl.slot_date DESC, sl.start_time DESC
              LIMIT %d",
            (int) $group_id, (int) $group_id, (int) $group_id, (int) $limit
        ) );
    }

    /**
     * Attendance for an entire group, keyed by slot_id then user_id →
     * {status, comment}. Lets the group attendance UI pre-fill each member's
     * saved status for whichever session the admin selects.
     */
    public static function get_group_attendance_map( $group_id ) {
        global $wpdb;
        $t = $wpdb->prefix . 'es_attendance';
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT at.slot_id, at.user_id, at.status, at.comment
               FROM {$t} at
               LEFT JOIN {$wpdb->prefix}es_slots sl ON sl.id = at.slot_id
              WHERE at.group_id = %d
                AND ( at.slot_id IS NULL OR sl.slot_type = 'group' )
                AND ( at.slot_id IS NULL OR sl.group_id = %d )",
            (int) $group_id, (int) $group_id
        ) );
        $map = array();
        foreach ( $rows as $r ) {
            $sid = (int) $r->slot_id;
            $uid = (int) $r->user_id;
            if ( ! isset( $map[ $sid ] ) ) $map[ $sid ] = array();
            $map[ $sid ][ $uid ] = array( 'status' => $r->status, 'comment' => $r->comment );
        }
        return $map;
    }

    /**
     * Map of attendance keyed by slot_id for a student → {status, comment}.
     */
    public static function get_attendance_map( $user_id, $group_id = 0 ) {        global $wpdb;
        $t = $wpdb->prefix . 'es_attendance';
        if ( $group_id ) {
            $rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT slot_id, status, comment FROM {$t} WHERE user_id = %d AND group_id = %d",
                (int) $user_id, (int) $group_id
            ) );
        } else {
            $rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT slot_id, status, comment FROM {$t} WHERE user_id = %d AND ( group_id IS NULL OR group_id = 0 )",
                (int) $user_id
            ) );
        }
        $map = array();
        foreach ( $rows as $r ) {
            $map[ (int) $r->slot_id ] = array( 'status' => $r->status, 'comment' => $r->comment );
        }
        return $map;
    }

    /**
     * Create or update a single attendance record (true upsert by slot+user+group).
     *
     * IMPORTANT: a student can change their status for the SAME session as many
     * times as needed — that must always UPDATE the one existing row, never
     * insert a new one. The previous version compared `slot_id = 0` against a
     * stored NULL (which never matches in SQL), so every save inserted a fresh
     * row AND re-counted the session — producing impossible totals like "3
     * sessions but 15 used". The lookup below is NULL-safe so the existing row
     * is always found, and the used-session counter is adjusted ONLY on a real
     * status transition for that one row.
     */
    public static function save_attendance( $args ) {
        global $wpdb;
        $t = $wpdb->prefix . 'es_attendance';

        $user_id = (int) ( $args['user_id'] ?? 0 );
        $slot_id = isset( $args['slot_id'] ) ? (int) $args['slot_id'] : 0;
        $group_id= isset( $args['group_id'] ) ? (int) $args['group_id'] : 0;
        if ( ! $user_id ) return false;

        // Find an existing row to update. Build NULL-safe conditions so a 0
        // slot/group (stored as NULL) still matches the same logical record.
        // WordPress's prepare() coerces a PHP null to '' for %s, so we inject
        // the IS NULL / = comparisons explicitly instead of binding null.
        $slot_cond  = $slot_id  ? $wpdb->prepare( 'slot_id = %d',  $slot_id )  : 'slot_id IS NULL';
        $group_cond = $group_id ? $wpdb->prepare( 'group_id = %d', $group_id ) : 'group_id IS NULL';
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$t}
              WHERE user_id = %d AND {$slot_cond} AND {$group_cond}
              ORDER BY id ASC
              LIMIT 1",
            $user_id
        ) );

        // Clean up any legacy duplicate rows for this exact key (created by the
        // earlier insert-every-time bug). Keep the canonical row found above and
        // remove the rest so counts and the attendance map stay correct.
        if ( $existing ) {
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$t}
                  WHERE user_id = %d AND {$slot_cond} AND {$group_cond} AND id <> %d",
                $user_id, (int) $existing
            ) );
        }

        $status = self::normalize_att_status( $args['status'] ?? 'none' );

        $data = array(
            'slot_id'     => $slot_id ?: null,
            'user_id'     => $user_id,
            'group_id'    => $group_id ?: null,
            'target_type' => $group_id ? 'group' : '1to1',
            'att_date'    => isset( $args['att_date'] ) ? $args['att_date'] : current_time( 'Y-m-d' ),
            'status'      => $status,
            'comment'     => isset( $args['comment'] ) ? sanitize_textarea_field( $args['comment'] ) : '',
            'marked_by'   => get_current_user_id(),
        );

        if ( $existing ) {
            $result = $wpdb->update( $t, $data, array( 'id' => (int) $existing ) );
        } else {
            $result = $wpdb->insert( $t, $data );
        }

        // Recompute used_sessions for the student. Under the v4.3 model a
        // session is consumed at SCHEDULE time, so this recount derives the
        // count from confirmed 1:1 bookings minus any excused absences — which
        // means marking "Absent - with permission" here refunds the session,
        // and changing it back re-consumes it. Single source of truth.
        if ( $result !== false ) {
            self::recount_used_sessions( $user_id );
        }

        return $result;
    }

    /**
     * Recompute a 1:1 student's used_sessions on their active plan.
     *
     * NEW MODEL (v4.3): a session is consumed the moment the admin SCHEDULES it
     * — i.e. every confirmed 1:1 booking counts as one used session. The only
     * way a scheduled session is given back is when the admin marks attendance
     * as "Absent - with permission" (absent_excused), which refunds it.
     *
     *   used = (confirmed 1:1 bookings) − (those bookings marked absent_excused)
     *
     * Present and Absent-without-permission therefore stay consumed (they are
     * just the default scheduled state), and only an explicit excused absence
     * returns the session. Capped at total_sessions, never below zero.
     *
     * @return int|null  The corrected used-session count, or null if no plan.
     */
    public static function recount_used_sessions( $user_id ) {
        global $wpdb;
        $user_id = (int) $user_id;

        $b = $wpdb->prefix . 'es_bookings';
        $s = $wpdb->prefix . 'es_slots';
        $a = $wpdb->prefix . 'es_attendance';
        $p = $wpdb->prefix . 'es_payments';

        // Every paid payment row for this user — we recompute used_sessions
        // for EACH one independently so a student with multiple stacked
        // packages (v4.4) gets its own session pool consumed correctly.
        $payments = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, total_sessions FROM {$p} WHERE user_id = %d AND status = 'paid' ORDER BY created_at DESC",
            $user_id
        ) );
        if ( empty( $payments ) ) return null;

        // The id of the user's currently-active plan — legacy bookings that
        // have NULL payment_id are counted against this one (so older data
        // keeps working after upgrade).
        $active    = self::get_active_plan( $user_id );
        $active_id = $active ? (int) $active->id : 0;

        $latest_used = null;
        foreach ( $payments as $row ) {
            $pid = (int) $row->id;

            // Bookings counted against THIS payment:
            //   - any booking with bk.payment_id = $pid, OR
            //   - if this row is the active plan, also legacy bookings (NULL).
            $cond = '( bk.payment_id = %d';
            $args = array( $pid );
            if ( $pid === $active_id ) {
                $cond .= ' OR bk.payment_id IS NULL';
            }
            $cond .= ' )';

            // Schedule-time consumption model (v4.3+):
            // Every confirmed 1:1 booking uses one session immediately.
            // "Absent – with permission" refunds it; all other statuses (or no
            // attendance row yet) keep the session consumed.
            // Demo sessions (slot_type = 'demo') are excluded.
            $scheduled = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT bk.slot_id)
                   FROM {$b} bk
                   INNER JOIN {$s} sl ON sl.id = bk.slot_id
                  WHERE bk.user_id = %d
                    AND bk.status  = 'confirmed'
                    AND sl.slot_type = '1to1'
                    AND {$cond}",
                array_merge( array( $user_id ), $args )
            ) );

            // Subtract sessions where attendance is "absent_excused" (refunded).
            $excused = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT bk.slot_id)
                   FROM {$b} bk
                   INNER JOIN {$s} sl ON sl.id = bk.slot_id
                   INNER JOIN {$a} at ON at.slot_id = bk.slot_id
                                     AND at.user_id  = bk.user_id
                                     AND ( at.group_id IS NULL OR at.group_id = 0 )
                  WHERE bk.user_id = %d
                    AND bk.status  = 'confirmed'
                    AND sl.slot_type = '1to1'
                    AND at.status = 'absent_excused'
                    AND {$cond}",
                array_merge( array( $user_id ), $args )
            ) );

            $used  = max( 0, $scheduled - $excused );
            $total = (int) $row->total_sessions;
            if ( $total > 0 && $used > $total ) $used = $total;

            $wpdb->update(
                $p,
                array( 'used_sessions' => $used, 'updated_at' => current_time( 'mysql' ) ),
                array( 'id' => $pid )
            );

            if ( $pid === $active_id ) $latest_used = $used;
        }

        return $latest_used;
    }

    /** Valid attendance statuses. */
    public static function att_statuses() {
        return array( 'present', 'absent_excused', 'absent_unexcused', 'none' );
    }

    /**
     * Normalise an incoming status string, mapping the legacy 'absent' to
     * 'absent_unexcused' (absent without permission = session used).
     */
    public static function normalize_att_status( $status ) {
        $status = (string) $status;
        if ( $status === 'absent' ) $status = 'absent_unexcused';
        return in_array( $status, self::att_statuses(), true ) ? $status : 'none';
    }

    /**
     * Does this attendance status consume one of the student's sessions?
     *   present            → yes (used)
     *   absent_unexcused   → yes (absent without permission = used)
     *   absent_excused     → no  (absent with permission = not used)
     *   none               → no
     */
    public static function att_status_consumes( $status ) {
        $status = self::normalize_att_status( $status );
        return in_array( $status, array( 'present', 'absent_unexcused' ), true );
    }

    /** Human label for an attendance status. */
    public static function att_status_label( $status ) {
        switch ( self::normalize_att_status( $status ) ) {
            case 'present':          return 'Present';
            case 'absent_excused':   return 'Absent - with permission';
            case 'absent_unexcused': return 'Absent - without permission';
            default:                 return 'Not marked';
        }
    }

    /* =================== VIDEOS =================== */

    public static function get_videos( $target_type, $target_id ) {
        global $wpdb;
        $t = $wpdb->prefix . 'es_videos';
        if ( $target_type === 'group' ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$t} WHERE target_type = 'group' AND group_id = %d ORDER BY created_at DESC",
                (int) $target_id
            ) );
        }
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$t} WHERE target_type = '1to1' AND user_id = %d ORDER BY created_at DESC",
            (int) $target_id
        ) );
    }

    /**
     * Per-package videos (v4.4) — global videos linked to a specific package.
     * These show up wherever a student of that package can view their session
     * library, independent of per-session uploads. target_type = 'package',
     * user_id/group_id = NULL, package_id = the package this belongs to.
     */
    public static function get_package_videos( $package_id ) {
        global $wpdb;
        $t = $wpdb->prefix . 'es_videos';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$t} WHERE package_id = %d ORDER BY created_at DESC",
            (int) $package_id
        ) );
    }

    public static function insert_video( $data ) {
        global $wpdb;
        $t = $wpdb->prefix . 'es_videos';
        $defaults = array(
            'target_type'   => '1to1',
            'user_id'       => null,
            'group_id'      => null,
            'package_id'    => null,   // v4.4 — for global per-package videos
            'title'         => '',
            'video_url'     => '',
            'duration'      => '',
            'thumb_url'     => '',
            'attachment_id' => null,
            'added_by'      => get_current_user_id(),
        );
        $data = wp_parse_args( $data, $defaults );
        if ( $wpdb->insert( $t, $data ) ) return $wpdb->insert_id;
        return false;
    }

    public static function delete_video( $id ) {
        global $wpdb;
        $t = $wpdb->prefix . 'es_videos';
        return $wpdb->delete( $t, array( 'id' => (int) $id ) );
    }

    /* =================== STUDENT PROFILE =================== */

    /**
     * Convenience: assemble the profile fields shown in the detail header/Package tab.
     */
    public static function get_student_profile( $user_id ) {
        $u = get_userdata( $user_id );
        return array(
            'id'      => (int) $user_id,
            'name'    => $u ? $u->display_name : '',
            'email'   => $u ? $u->user_email : '',
            'phone'   => get_user_meta( $user_id, self::META_PHONE,  true ),
            'parent'  => get_user_meta( $user_id, self::META_PARENT, true ),
            'source'  => get_user_meta( $user_id, self::META_SOURCE, true ),
            'goal'    => get_user_meta( $user_id, self::META_GOAL,   true ),
            'band'    => get_user_meta( $user_id, self::META_BAND,   true ),
            'notes'   => get_user_meta( $user_id, self::META_NOTES,  true ),
        );
    }

    /* =================== STUDENT COURSES =================== */

    const META_COURSE_IDS = '_es_selected_course_ids';

    public static function get_course_posts() {
        if ( ! post_type_exists( 'course' ) ) return array();
        return get_posts( array(
            'post_type'      => 'course',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'numberposts'    => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
    }

    /**
     * Resolve an array of course-post IDs to their titles. Skips IDs that no
     * longer resolve to a post. Returns a numeric array of strings.
     */
    public static function course_names( $course_ids ) {
        $names = array();
        foreach ( (array) $course_ids as $cid ) {
            $cid = (int) $cid;
            if ( $cid <= 0 ) continue;
            $title = get_the_title( $cid );
            if ( $title !== '' ) $names[] = $title;
        }
        return $names;
    }

    /**
     * Comma-joined course names for an array of IDs (empty string when none).
     */
    public static function course_names_str( $course_ids ) {
        return implode( ', ', self::course_names( $course_ids ) );
    }

    public static function get_student_course_ids( $user_id ) {
        $raw = get_user_meta( $user_id, self::META_COURSE_IDS, true );
        if ( is_array( $raw ) ) return array_values( array_filter( array_map( 'intval', $raw ) ) );
        if ( is_string( $raw ) && $raw !== '' ) return array_values( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
        return array();
    }

    public static function set_student_course_ids( $user_id, array $course_ids ) {
        $ids = array_values( array_unique( array_filter( array_map( 'intval', $course_ids ) ) ) );
        update_user_meta( $user_id, self::META_COURSE_IDS, $ids );
        return $ids;
    }

    /* =================== GROUP COURSES =================== */
    // Groups aren't users, so their selected course posts are stored in the
    // es_groups.course_ids column (comma-separated 'course' CPT IDs).

    public static function get_group_course_ids( $group_id ) {
        $group = self::get_group( $group_id );
        if ( ! $group || ! isset( $group->course_ids ) || $group->course_ids === '' || $group->course_ids === null ) {
            return array();
        }
        return array_values( array_filter( array_map( 'intval', explode( ',', (string) $group->course_ids ) ) ) );
    }

    public static function set_group_course_ids( $group_id, array $course_ids ) {
        global $wpdb;
        $ids = array_values( array_unique( array_filter( array_map( 'intval', $course_ids ) ) ) );
        $wpdb->update(
            $wpdb->prefix . 'es_groups',
            array(
                'course_ids'  => implode( ',', $ids ),
                'course_id'   => self::first_course_id( $ids ) ?: null,
                'course_name' => self::course_names_str( array_slice( $ids, 0, 1 ) ),
            ),
            array( 'id' => (int) $group_id )
        );
        return $ids;
    }


    /**
     * Return one sanitized course id from any course-id list.
     */
    public static function first_course_id( $course_ids ) {
        foreach ( (array) $course_ids as $cid ) {
            $cid = (int) $cid;
            if ( $cid > 0 ) return $cid;
        }
        return 0;
    }

    public static function course_name( $course_id ) {
        $course_id = (int) $course_id;
        if ( $course_id <= 0 ) return '';
        $title = get_the_title( $course_id );
        return $title !== '' ? $title : '';
    }

    /**
     * Packages the admin may select in the Renew tab.
     * A package is hidden while the student still has a non-expired paid
     * purchase of that package with sessions remaining. Expired/fully-used
     * packages become renewable again.
     */
    public static function get_renewable_packages( $user_id, $active_only = false ) {
        $user_id  = (int) $user_id;
        $packages = self::get_all( $active_only );
        $out      = array();

        foreach ( (array) $packages as $pkg ) {
            $payment = self::get_active_package_payment( $user_id, (int) $pkg->id, '1to1' );
            if ( $payment ) {
                $remaining   = self::remaining_sessions( $payment );
                $valid_until = ! empty( $payment->valid_until ) ? strtotime( $payment->valid_until ) : 0;
                $not_expired = empty( $payment->valid_until ) || ( $valid_until && $valid_until >= current_time( 'timestamp' ) );
                if ( $not_expired && $remaining > 0 ) {
                    continue;
                }
            }
            $out[] = $pkg;
        }
        return $out;
    }

    /**
     * Packages the admin may select in a Group Renew tab. The current linked
     * package is hidden until the group shared session pool is exhausted.
     */
    /**
     * Paid packages connected to a group, newest first. This gives the group
     * screen a package-wise view when the group has been renewed more than once.
     */
    public static function get_group_payments( $group_id, $paid_only = true ) {
        global $wpdb;
        $pay = $wpdb->prefix . 'es_payments';
        $pkg = $wpdb->prefix . 'es_packages';
        $where = 'p.group_id = %d AND ( p.flow_type = \'group\' OR p.group_id IS NOT NULL )';
        if ( $paid_only ) $where .= " AND p.status = 'paid'";
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT p.*, COALESCE(NULLIF(p.package_name, ''), k.package_name) AS package_name, k.sub_heading
               FROM {$pay} p
               LEFT JOIN {$pkg} k ON k.id = p.package_id
              WHERE {$where}
              ORDER BY p.created_at DESC",
            (int) $group_id
        ) );
    }

    public static function get_group_renewable_packages( $group_id, $active_only = false ) {
        $group_id = (int) $group_id;
        $packages = self::get_all( $active_only );
        if ( ! $group_id || ! self::get_group( $group_id ) ) return $packages;

        // Hide only packages that are still active for this group payment flow.
        // This allows multiple group packages while preventing duplicate renewals
        // until a same-package payment is expired or fully consumed.
        $now_ts = current_time( 'timestamp' );
        $active_pkg_ids = array();
        foreach ( (array) self::get_group_payments( $group_id, true ) as $pay ) {
            $total = (int) ( $pay->total_sessions ?? 0 );
            $used  = (int) ( $pay->used_sessions ?? 0 );
            $left  = max( 0, $total - $used );
            $valid = empty( $pay->valid_until ) || strtotime( $pay->valid_until ) >= $now_ts;
            if ( $valid && $left > 0 ) {
                $active_pkg_ids[] = (int) $pay->package_id;
            }
        }
        $active_pkg_ids = array_values( array_unique( array_filter( $active_pkg_ids ) ) );

        $out = array();
        foreach ( (array) $packages as $pkg ) {
            if ( in_array( (int) $pkg->id, $active_pkg_ids, true ) ) continue;
            $out[] = $pkg;
        }
        return $out;
    }

}
