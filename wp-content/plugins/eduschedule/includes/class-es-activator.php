<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Activator {

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql_slots = "CREATE TABLE {$wpdb->prefix}es_slots (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slot_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            duration_min INT NOT NULL,
            slot_type VARCHAR(20) NOT NULL DEFAULT '1to1',
            capacity INT NOT NULL DEFAULT 1,
            platform VARCHAR(40) NOT NULL DEFAULT 'Zoom',
            title VARCHAR(190) NULL,
            notes TEXT NULL,
            course_id BIGINT(20) UNSIGNED NULL,
            course_name VARCHAR(255) NULL,
            package_id BIGINT(20) UNSIGNED NULL,
            package_name VARCHAR(255) NULL,
            created_by BIGINT(20) UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY slot_date (slot_date),
            KEY slot_type (slot_type)
        ) $charset_collate;";

        $sql_bookings = "CREATE TABLE {$wpdb->prefix}es_bookings (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slot_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            status VARCHAR(20) DEFAULT 'confirmed',
            zoom_meeting_id VARCHAR(64) NULL,
            zoom_join_url TEXT NULL,
            zoom_start_url TEXT NULL,
            zoom_password VARCHAR(64) NULL,
            user_note TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slot_user (slot_id, user_id),
            KEY user_id (user_id),
            KEY slot_id (slot_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_slots );
        dbDelta( $sql_bookings );

        // Create packages tables
        ES_Packages::create_table();

        // Default settings
        if ( ! get_option( 'es_settings' ) ) {
            update_option( 'es_settings', array(
                'site_name'        => get_bloginfo( 'name' ),
                'work_country'     => 'IN',
                'work_timezone'    => 'Asia/Kolkata',
                'platforms'        => array( 'Zoom', 'Google Meet', 'Microsoft Teams' ),
                'default_platform' => 'Zoom',
                'register_open'    => 1,
                'login_page_id'    => 0,
                'register_page_id' => 0,
                'dashboard_page_id'=> 0,
            ) );
        }
    }
}
