<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Helpers {

    public static function settings() {
        return wp_parse_args( get_option( 'es_settings', array() ), array(
            'site_name'        => get_bloginfo( 'name' ),
            'work_country'     => 'IN',
            'work_timezone'    => 'Asia/Kolkata',
            'platforms'        => array( 'Zoom', 'Google Meet', 'Microsoft Teams' ),
            'default_platform' => 'Zoom',
            'register_open'    => 1,
            'login_page_id'    => 0,
            'register_page_id' => 0,
            'dashboard_page_id'=> 0,
            'reset_page_id'    => 0,   // dedicated [eduschedule_reset] page

            // Currency / Billing
            'default_currency' => 'INR',
            'yearly_discount'        => 0,   // percent off for the discounted multi-month cycle
            'yearly_discount_months' => 12,  // how many months the discounted cycle covers
            'enable_yearly'          => 1,   // enable discounted billing toggle on package shortcode

            // Stripe
            'stripe_enabled'        => 0,
            'stripe_mode'           => 'test', // test | live
            'stripe_test_pub_key'   => '',
            'stripe_test_secret'    => '',
            'stripe_live_pub_key'   => '',
            'stripe_live_secret'    => '',
            'stripe_webhook_secret' => '',

            // Email / Notifications
            'from_name'    => get_bloginfo( 'name' ),
            'from_email'   => '',   // blank → falls back to WP default (admin_email-derived)
            'reply_to'     => '',   // blank → uses from_email
            'notify_admin' => 1,    // send admin a copy on bookings / after-call
            'admin_notify_email' => '', // blank → site admin_email

            // SMTP — route all plugin mail through an authenticated SMTP server
            // so providers like Gmail actually accept the message instead of
            // silently dropping unauthenticated PHP mail().
            'smtp_enabled'    => 0,
            'smtp_host'       => '',
            'smtp_port'       => 587,
            'smtp_encryption' => 'tls',  // tls | ssl | none
            'smtp_auth'       => 1,      // SMTP requires authentication
            'smtp_username'   => '',
            'smtp_password'   => '',     // app password for Gmail, etc.
        ) );
    }

    /** Currencies supported */
    public static function currencies() {
        return array(
            'INR' => array( 'name' => 'Indian Rupee',     'symbol' => '₹' ),
            'USD' => array( 'name' => 'US Dollar',        'symbol' => '$' ),
            'EUR' => array( 'name' => 'Euro',             'symbol' => '€' ),
            'GBP' => array( 'name' => 'British Pound',    'symbol' => '£' ),
            'AUD' => array( 'name' => 'Australian Dollar','symbol' => 'A$' ),
            'CAD' => array( 'name' => 'Canadian Dollar',  'symbol' => 'C$' ),
            'AED' => array( 'name' => 'UAE Dirham',       'symbol' => 'AED ' ),
            'SGD' => array( 'name' => 'Singapore Dollar', 'symbol' => 'S$' ),
            'JPY' => array( 'name' => 'Japanese Yen',     'symbol' => '¥' ),
            'NZD' => array( 'name' => 'New Zealand Dollar','symbol' => 'NZ$' ),
        );
    }

    /** Get currency symbol */
    public static function currency_symbol( $code ) {
        $list = self::currencies();
        $code = strtoupper( $code );
        return isset( $list[ $code ] ) ? $list[ $code ]['symbol'] : ( $code . ' ' );
    }

    /** Format a price with the currency symbol */
    public static function format_price( $amount, $currency = 'INR' ) {
        $sym = self::currency_symbol( $currency );
        // Currencies with no decimals
        $no_decimals = array( 'JPY', 'INR' );
        $decimals = in_array( strtoupper( $currency ), $no_decimals, true ) ? 0 : 2;
        return $sym . number_format( (float) $amount, $decimals );
    }

    /** Country -> default timezone map (most common defaults) */
    public static function countries() {
        return array(
            'IN' => array( 'name' => 'India',          'tz' => 'Asia/Kolkata' ),
            'US' => array( 'name' => 'United States',  'tz' => 'America/New_York' ),
            'GB' => array( 'name' => 'United Kingdom', 'tz' => 'Europe/London' ),
            'CA' => array( 'name' => 'Canada',         'tz' => 'America/Toronto' ),
            'AU' => array( 'name' => 'Australia',      'tz' => 'Australia/Sydney' ),
            'DE' => array( 'name' => 'Germany',        'tz' => 'Europe/Berlin' ),
            'FR' => array( 'name' => 'France',         'tz' => 'Europe/Paris' ),
            'AE' => array( 'name' => 'United Arab Emirates', 'tz' => 'Asia/Dubai' ),
            'SG' => array( 'name' => 'Singapore',      'tz' => 'Asia/Singapore' ),
            'JP' => array( 'name' => 'Japan',          'tz' => 'Asia/Tokyo' ),
            'PK' => array( 'name' => 'Pakistan',       'tz' => 'Asia/Karachi' ),
            'BD' => array( 'name' => 'Bangladesh',     'tz' => 'Asia/Dhaka' ),
            'NP' => array( 'name' => 'Nepal',          'tz' => 'Asia/Kathmandu' ),
            'LK' => array( 'name' => 'Sri Lanka',      'tz' => 'Asia/Colombo' ),
            'NZ' => array( 'name' => 'New Zealand',    'tz' => 'Pacific/Auckland' ),
            'ZA' => array( 'name' => 'South Africa',   'tz' => 'Africa/Johannesburg' ),
            'SA' => array( 'name' => 'Saudi Arabia',   'tz' => 'Asia/Riyadh' ),
            'BR' => array( 'name' => 'Brazil',         'tz' => 'America/Sao_Paulo' ),
            'MX' => array( 'name' => 'Mexico',         'tz' => 'America/Mexico_City' ),
            'PH' => array( 'name' => 'Philippines',    'tz' => 'Asia/Manila' ),
        );
    }

    public static function tz_for_country( $code ) {
        $list = self::countries();
        return isset( $list[ $code ] ) ? $list[ $code ]['tz'] : 'UTC';
    }

    /** Returns the work timezone (admin's timezone) as DateTimeZone */
    public static function work_tz() {
        $s = self::settings();
        try { return new DateTimeZone( $s['work_timezone'] ); } catch ( Exception $e ) { return new DateTimeZone( 'UTC' ); }
    }

    /** Returns user's display timezone (from user_meta or work tz fallback) */
    public static function user_tz( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        if ( ! $user_id ) return self::work_tz();
        $tz = get_user_meta( $user_id, 'es_timezone', true );
        if ( $tz ) {
            try { return new DateTimeZone( $tz ); } catch ( Exception $e ) {}
        }
        $country = get_user_meta( $user_id, 'es_country', true );
        if ( $country ) {
            return new DateTimeZone( self::tz_for_country( $country ) );
        }
        return self::work_tz();
    }

    /** Slot type config */
    public static function slot_types() {
        return array(
            '1to1'     => array( 'label' => '1:1 Call',     'color' => '#3b82f6', 'description' => 'One-on-one. Capacity 1.' ),
            'group'    => array( 'label' => 'Group Call',   'color' => '#10b981', 'description' => 'Group session. Multiple users can book.' ),
            'open'     => array( 'label' => 'Open Slot',    'color' => '#8b5cf6', 'description' => 'Drop-in slot.' ),
            'personal' => array( 'label' => 'Personal',     'color' => '#ec4899', 'description' => 'Personal time. Not bookable by users.' ),
        );
    }

    public static function slot_type_color( $type ) {
        $types = self::slot_types();
        return isset( $types[ $type ] ) ? $types[ $type ]['color'] : '#6b7280';
    }

    public static function slot_type_label( $type ) {
        $types = self::slot_types();
        return isset( $types[ $type ] ) ? $types[ $type ]['label'] : $type;
    }

    public static function platforms() {
        $s = self::settings();
        return ! empty( $s['platforms'] ) ? $s['platforms'] : array( 'Zoom' );
    }

    /** Calculate end time (HH:MM) given start (HH:MM) + duration in minutes */
    public static function calc_end_time( $start, $duration ) {
        if ( ! $start ) return '';
        $parts = explode( ':', $start );
        if ( count( $parts ) < 2 ) return '';
        $total = intval( $parts[0] ) * 60 + intval( $parts[1] ) + intval( $duration );
        $total = ( ( $total % 1440 ) + 1440 ) % 1440;
        return sprintf( '%02d:%02d', floor( $total / 60 ), $total % 60 );
    }

    public static function valid_date( $d ) {
        return is_string( $d ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d ) && strtotime( $d ) !== false;
    }

    /** Convert work-tz time (Y-m-d H:i:s) to user's tz, return DateTime in user tz */
    public static function to_user_tz( $datetime_str_work_tz, $user_id = null ) {
        $work = self::work_tz();
        $user = self::user_tz( $user_id );
        try {
            $dt = new DateTime( $datetime_str_work_tz, $work );
            $dt->setTimezone( $user );
            return $dt;
        } catch ( Exception $e ) { return null; }
    }

    /** Format user-facing time for a slot (in user's timezone) */
    public static function slot_user_time( $slot_date, $start_time, $user_id = null ) {
        $dt = self::to_user_tz( $slot_date . ' ' . $start_time, $user_id );
        return $dt ? $dt->format( 'g:i A' ) : $start_time;
    }

    /** Pretty TZ label "Asia/Kolkata (IST, +05:30)" */
    public static function tz_label( DateTimeZone $tz ) {
        $now = new DateTime( 'now', $tz );
        return $tz->getName() . ' (' . $now->format( 'T, P' ) . ')';
    }

    public static function admin_capability() {
        return apply_filters( 'eduschedule_admin_capability', 'manage_options' );
    }

    /**
     * Resolve the best base URL for the self-service password-reset flow.
     * Preference order, so the flow stays self-contained inside the plugin:
     *   1) a published page containing the [eduschedule_reset] shortcode
     *   2) a published page containing [eduschedule_login] / [eduschedule_auth]
     *   3) the configured login page (Settings)
     *   4) the site home
     * Cached in a transient to avoid scanning pages on every request.
     */
    public static function reset_page_url() {
        $cached = get_transient( 'es_reset_page_url' );
        if ( $cached !== false ) return $cached;

        $url = '';

        // Pass 0 (v4.4.2): the admin explicitly chose a Reset Password page in
        // Settings → Frontend Pages. Always honour that first, regardless of
        // what shortcodes the page contains, so an admin can override the
        // auto-detect when their page uses a builder block or a non-standard
        // shortcode wrapper.
        $s        = self::settings();
        $reset_id = (int) ( $s['reset_page_id'] ?? 0 );
        if ( $reset_id && ( $p = get_post( $reset_id ) ) && $p->post_status === 'publish' ) {
            $url = get_permalink( $reset_id );
        }

        if ( ! $url ) {
            $pages = get_posts( array(
                'post_type'   => 'page',
                'post_status' => 'publish',
                'numberposts' => 100,
                'fields'      => 'ids',
            ) );

            // Pass 1: dedicated reset page.
            foreach ( $pages as $pid ) {
                $content = get_post_field( 'post_content', $pid );
                if ( has_shortcode( $content, 'eduschedule_reset' ) || has_shortcode( $content, 'es_reset_password_form' ) || has_shortcode( $content, 'ivy_reset_password' ) ) {
                    $url = get_permalink( $pid );
                    break;
                }
            }
            // Pass 2: login/auth page (the reset view also renders there).
            if ( ! $url ) {
                foreach ( $pages as $pid ) {
                    $content = get_post_field( 'post_content', $pid );
                    if ( has_shortcode( $content, 'eduschedule_login' ) || has_shortcode( $content, 'eduschedule_auth' ) ) {
                        $url = get_permalink( $pid );
                        break;
                    }
                }
            }
            // Pass 3: configured login page.
            if ( ! $url ) {
                $login_id = (int) ( $s['login_page_id'] ?? 0 );
                if ( $login_id ) $url = get_permalink( $login_id );
            }
            // Pass 4: home.
            if ( ! $url ) $url = home_url( '/' );
        }

        set_transient( 'es_reset_page_url', $url, HOUR_IN_SECONDS );
        return $url;
    }
}
