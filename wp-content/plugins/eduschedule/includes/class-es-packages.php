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

    /**
     * Create tables on activation
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Packages table
        $sql = "CREATE TABLE {$wpdb->prefix}es_packages (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            package_name VARCHAR(255) NOT NULL,
            sub_heading VARCHAR(255) NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
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

        // Lead → Package link table
        $sql_links = "CREATE TABLE {$wpdb->prefix}es_lead_packages (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            package_id BIGINT(20) UNSIGNED NOT NULL,
            outcome VARCHAR(50) NOT NULL,
            additional_comments TEXT NULL,
            group_id BIGINT(20) UNSIGNED NULL,
            selected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY package_id (package_id),
            KEY outcome (outcome)
        ) $charset_collate;";
        dbDelta( $sql_links );

        // Groups table
        $sql_groups = "CREATE TABLE {$wpdb->prefix}es_groups (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            group_name VARCHAR(255) NOT NULL,
            package_id BIGINT(20) UNSIGNED NULL,
            duration VARCHAR(100) NULL,
            total_sessions INT NOT NULL DEFAULT 0,
            used_sessions INT NOT NULL DEFAULT 0,
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
            'package_name'  => '',
            'sub_heading'   => '',
            'price'         => 0,
            'hours'         => 0,
            'tagline'       => '',
            'description'   => '',
            'is_active'     => 1,
            'display_order' => 0,
        );
        $data = wp_parse_args( $data, $defaults );

        if ( $wpdb->insert( $table, $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function update( $id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_packages';
        return $wpdb->update( $table, $data, array( 'id' => (int) $id ) );
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

    public static function insert_group( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_groups';

        $defaults = array(
            'group_name'     => '',
            'package_id'     => null,
            'duration'       => '',
            'total_sessions' => 0,
            'used_sessions'  => 0,
            'color'          => '#6366f1',
            'notes'          => '',
            'is_active'      => 1,
        );
        $data = wp_parse_args( $data, $defaults );

        if ( $wpdb->insert( $table, $data ) ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    public static function update_group( $id, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'es_groups';
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

        $wpdb->insert( $table, array(
            'user_id'             => (int) $user_id,
            'package_id'          => (int) $package_id,
            'outcome'             => sanitize_text_field( $outcome ),
            'additional_comments' => sanitize_textarea_field( $comments ),
            'group_id'            => $group_id ? (int) $group_id : null,
        ) );

        $insert_id = $wpdb->insert_id;

        // Update user category meta based on outcome
        $category = self::outcome_to_category( $outcome );
        update_user_meta( $user_id, self::META_CATEGORY, $category );

        if ( $package_id > 0 ) {
            update_user_meta( $user_id, self::META_PACKAGE_ID, (int) $package_id );
        }

        if ( $group_id && $category === 'group' ) {
            update_user_meta( $user_id, self::META_GROUP_ID, (int) $group_id );
            self::add_user_to_group( $group_id, $user_id );
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
        );
        return isset( $map[ $outcome ] ) ? $map[ $outcome ] : 'demo';
    }

    public static function get_user_category( $user_id ) {
        $cat = get_user_meta( $user_id, self::META_CATEGORY, true );
        return $cat ?: 'demo';
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

    public static function clear_staged_packages( $user_id ) {
        delete_user_meta( $user_id, self::META_STAGED );
    }
}
