<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Ajax {

    public function __construct() {
        $public = array( 'es_login', 'es_register', 'es_get_calendar_month', 'es_get_slot', 'es_lost_password', 'es_reset_password' );
        $private = array( 'es_book_slot', 'es_cancel_booking', 'es_logout', 'es_update_profile' );
        foreach ( $public as $h ) {
            add_action( "wp_ajax_$h",        array( $this, str_replace( 'es_', 'handle_', $h ) ) );
            add_action( "wp_ajax_nopriv_$h", array( $this, str_replace( 'es_', 'handle_', $h ) ) );
        }
        foreach ( $private as $h ) {
            add_action( "wp_ajax_$h", array( $this, str_replace( 'es_', 'handle_', $h ) ) );
        }
    }

    private function check() {
        check_ajax_referer( 'es_fe_nonce', 'nonce' );
    }

    /** Parse optional types filter from POST. Always blocks 'personal' for non-admins. */
    private function parse_types_filter() {
        if ( ! isset( $_POST['types'] ) ) return null;
        $raw = sanitize_text_field( wp_unslash( $_POST['types'] ) );
        if ( $raw === '' ) return null;
        $types = array_map( 'trim', explode( ',', $raw ) );
        $valid = array_intersect( $types, array_keys( ES_Helpers::slot_types() ) );
        // Strip 'personal' from public callers
        if ( ! current_user_can( 'manage_options' ) ) {
            $valid = array_values( array_diff( $valid, array( 'personal' ) ) );
        }
        return $valid;
    }

    /* ============== AUTH ============== */

    public function handle_register() {
        // Dedicated register nonce — accepts either the localized JS nonce or a wp_nonce_field hidden input
        $valid = false;
        if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'es_register_nonce' ) ) {
            $valid = true;
        } elseif ( isset( $_POST['es_register_nonce_field'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['es_register_nonce_field'] ) ), 'es_register_nonce' ) ) {
            $valid = true;
        } elseif ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'es_fe_nonce' ) ) {
            // Fallback for older clients
            $valid = true;
        }
        if ( ! $valid ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page and try again.' ), 403 );
        }

        $s = ES_Helpers::settings();
        if ( empty( $s['register_open'] ) ) wp_send_json_error( array( 'message' => 'Registration is currently closed.' ) );

        $first   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last    = isset( $_POST['last_name'] )  ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )  : '';
        $email   = isset( $_POST['email'] )      ? sanitize_email( wp_unslash( $_POST['email'] ) )           : '';
        $phone   = isset( $_POST['phone'] )      ? sanitize_text_field( wp_unslash( $_POST['phone'] ) )      : '';
        $country = isset( $_POST['country'] )    ? strtoupper( substr( sanitize_text_field( wp_unslash( $_POST['country'] ) ), 0, 2 ) ) : '';
        $password= isset( $_POST['password'] )   ? wp_unslash( $_POST['password'] )                          : '';
        $confirm = isset( $_POST['confirm_password'] ) ? wp_unslash( $_POST['confirm_password'] )             : '';
        $track   = isset( $_POST['track'] )      ? sanitize_text_field( wp_unslash( $_POST['track'] ) )      : '';
        $stay_url= isset( $_POST['stay_url'] )   ? esc_url_raw( wp_unslash( $_POST['stay_url'] ) )           : '';

        if ( $first === '' || $last === '' )           wp_send_json_error( array( 'message' => 'Please enter your full name.' ) );
        if ( ! is_email( $email ) )                    wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        if ( strlen( $password ) < 8 )                 wp_send_json_error( array( 'message' => 'Password must be at least 8 characters.' ) );
        if ( $confirm !== '' && $password !== $confirm ) wp_send_json_error( array( 'message' => 'Passwords do not match.' ) );
        if ( email_exists( $email ) )                  wp_send_json_error( array( 'message' => 'An account with this email already exists.' ) );

        $username = $this->generate_unique_username( $email );
        $user_id = wp_create_user( $username, $password, $email );
        if ( is_wp_error( $user_id ) ) wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );

        wp_update_user( array(
            'ID'           => $user_id,
            'first_name'   => $first,
            'last_name'    => $last,
            'display_name' => trim( $first . ' ' . $last ),
            'role'         => 'subscriber',
        ) );

        update_user_meta( $user_id, 'es_phone', $phone );
        update_user_meta( $user_id, 'es_country', $country );
        update_user_meta( $user_id, 'es_timezone', ES_Helpers::tz_for_country( $country ) );
        if ( $track )  update_user_meta( $user_id, 'es_track', $track );

        // Send registration emails to the student and admin. Uses the plugin's
        // mail wrapper so From/Reply-To headers are valid on common hosts.
        if ( class_exists( 'ES_Mailer' ) ) {
            ES_Mailer::send_registration_notifications( $user_id );
        }

        // Auto-login
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        // Determine redirect: stay on same page if a safe stay_url was provided, else dashboard
        if ( $stay_url && $this->is_safe_internal_url( $stay_url ) ) {
            $redirect = $stay_url;
        } else {
            $dash_id = (int) ( ES_Helpers::settings()['dashboard_page_id'] ?? 0 );
            $redirect = $dash_id ? get_permalink( $dash_id ) : home_url();
        }

        wp_send_json_success( array(
            'message'  => 'Account created!',
            'redirect' => $redirect,
        ) );
    }

    private function generate_unique_username( $email ) {
        $base = sanitize_user( current( explode( '@', $email ) ), true );
        $base = $base ?: 'user';
        $candidate = $base;
        $i = 1;
        while ( username_exists( $candidate ) ) {
            $candidate = $base . $i;
            $i++;
            if ( $i > 999 ) { $candidate = $base . wp_rand( 1000, 9999 ); break; }
        }
        return $candidate;
    }

    public function handle_login() {
        // Dedicated login nonce — accepts either the localized JS nonce or a wp_nonce_field hidden input
        $valid = false;
        if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'es_login_nonce' ) ) {
            $valid = true;
        } elseif ( isset( $_POST['es_login_nonce_field'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['es_login_nonce_field'] ) ), 'es_login_nonce' ) ) {
            $valid = true;
        } elseif ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'es_fe_nonce' ) ) {
            // Fallback for older clients still using the generic nonce
            $valid = true;
        }
        if ( ! $valid ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page and try again.' ), 403 );
        }

        $email    = isset( $_POST['email'] )    ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';
        $remember = ! empty( $_POST['remember'] );
        $stay_url = isset( $_POST['stay_url'] ) ? esc_url_raw( wp_unslash( $_POST['stay_url'] ) ) : '';

        if ( ! is_email( $email ) || $password === '' ) {
            wp_send_json_error( array( 'message' => 'Please enter your email and password.' ) );
        }

        $user = wp_authenticate( $email, $password );
        if ( is_wp_error( $user ) ) {
            // Try authenticating by username (in case email maps differently)
            $u = get_user_by( 'email', $email );
            if ( $u ) {
                $user = wp_authenticate( $u->user_login, $password );
            }
        }
        if ( is_wp_error( $user ) ) wp_send_json_error( array( 'message' => 'Incorrect email or password.' ) );

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, $remember );

        // Determine where to send the user after login.
        // Priority: 1) stay_url (same page) when explicitly requested  2) admin for admins  3) dashboard
        $redirect = '';
        if ( $stay_url && $this->is_safe_internal_url( $stay_url ) ) {
            $redirect = $stay_url;
        } elseif ( user_can( $user, 'manage_options' ) ) {
            $redirect = admin_url();
        } else {
            $dash_id = (int) ( ES_Helpers::settings()['dashboard_page_id'] ?? 0 );
            $redirect = $dash_id ? get_permalink( $dash_id ) : home_url();
        }

        wp_send_json_success( array(
            'message'  => 'Logged in.',
            'redirect' => $redirect,
        ) );
    }

    /**
     * Make sure a "stay on same page" URL belongs to this site.
     * Prevents open-redirect via the stay_url parameter.
     */
    private function is_safe_internal_url( $url ) {
        if ( empty( $url ) ) return false;
        $home_host = parse_url( home_url(), PHP_URL_HOST );
        $url_host  = parse_url( $url, PHP_URL_HOST );
        if ( ! $home_host || ! $url_host ) return false;
        return ( strtolower( $home_host ) === strtolower( $url_host ) );
    }

    /**
     * Step 1 of the custom password reset: the user submits their email, we
     * generate a WordPress reset key and email them a link back to our own
     * login page (?es_action=rp&key=...&login=...) — never to wp-login.php.
     *
     * To avoid leaking which emails are registered, the success message is the
     * same whether or not the account exists.
     */
    public function handle_lost_password() {
        $valid = false;
        // Accept login nonce, generic fe nonce, or the dedicated reset-page nonce
        if ( isset( $_POST['nonce'] ) ) {
            $n = sanitize_key( wp_unslash( $_POST['nonce'] ) );
            if ( wp_verify_nonce( $n, 'es_login_nonce' ) ||
                 wp_verify_nonce( $n, 'es_fe_nonce' ) ||
                 wp_verify_nonce( $n, 'es_frontend_reset' ) ) {
                $valid = true;
            }
        }
        // Also accept the hidden nonce field sent by the non-JS form fallback
        if ( ! $valid && isset( $_POST['es_reset_nonce'] ) ) {
            if ( wp_verify_nonce( sanitize_key( wp_unslash( $_POST['es_reset_nonce'] ) ), 'es_frontend_reset' ) ) {
                $valid = true;
            }
        }
        if ( ! $valid ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ), 403 );
        }

        $login = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
        $generic = 'If that email is registered, a password reset link has been sent. Please check your inbox (and spam).';

        if ( $login === '' ) {
            wp_send_json_error( array( 'message' => 'Please enter your email address.' ) );
        }

        // Resolve the user by email or login.
        $user = is_email( $login ) ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );
        if ( ! $user ) {
            // Don't reveal non-existence.
            wp_send_json_success( array( 'message' => $generic ) );
        }

        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            wp_send_json_success( array( 'message' => $generic ) );
        }

        // Build a reset link back to a page that hosts the reset form. Prefer a
        // dedicated [eduschedule_reset] page, then the login page (Settings).
        $base_url = ES_Helpers::reset_page_url();
        $reset_link = add_query_arg( array(
            'es_action' => 'rp',
            'key'       => rawurlencode( $key ),
            'login'     => rawurlencode( $user->user_login ),
        ), $base_url );

        // Send via the plugin mailer (respects SMTP settings).
        if ( class_exists( 'ES_Mailer' ) ) {
            ES_Mailer::send_password_reset( $user, $reset_link );
        }

        wp_send_json_success( array( 'message' => $generic ) );
    }

    /**
     * Step 2 of the custom password reset: validate the key + login and set the
     * new password. Uses WordPress's own key check so links expire correctly.
     */
    public function handle_reset_password() {
        $valid = false;
        if ( isset( $_POST['nonce'] ) ) {
            $n = sanitize_key( wp_unslash( $_POST['nonce'] ) );
            if ( wp_verify_nonce( $n, 'es_login_nonce' ) ||
                 wp_verify_nonce( $n, 'es_fe_nonce' ) ||
                 wp_verify_nonce( $n, 'es_frontend_reset' ) ) {
                $valid = true;
            }
        }
        if ( ! $valid && isset( $_POST['es_reset_nonce'] ) ) {
            if ( wp_verify_nonce( sanitize_key( wp_unslash( $_POST['es_reset_nonce'] ) ), 'es_frontend_reset' ) ) {
                $valid = true;
            }
        }
        if ( ! $valid ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ), 403 );
        }

        $key   = isset( $_POST['key'] )   ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
        $login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : '';
        $pass1 = isset( $_POST['password'] )        ? (string) wp_unslash( $_POST['password'] ) : '';
        $pass2 = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : '';

        if ( $key === '' || $login === '' ) {
            wp_send_json_error( array( 'message' => 'This reset link is invalid. Please request a new one.' ) );
        }
        if ( strlen( $pass1 ) < 6 ) {
            wp_send_json_error( array( 'message' => 'Password must be at least 6 characters.' ) );
        }
        if ( $pass1 !== $pass2 ) {
            wp_send_json_error( array( 'message' => 'The two passwords do not match.' ) );
        }

        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => 'This reset link has expired or is invalid. Please request a new one.' ) );
        }

        reset_password( $user, $pass1 );

        // Where to send them next (their login page).
        $s        = ES_Helpers::settings();
        $login_id = (int) ( $s['login_page_id'] ?? 0 );
        $login_url = $login_id ? get_permalink( $login_id ) : home_url( '/' );

        wp_send_json_success( array(
            'message'   => 'Your password has been reset. You can now log in.',
            'login_url' => $login_url,
        ) );
    }

    public function handle_logout() {
        $this->check();
        wp_logout();
        $s = ES_Helpers::settings();
        $login = ! empty( $s['login_page_id'] ) ? get_permalink( $s['login_page_id'] ) : home_url();
        wp_send_json_success( array( 'redirect' => $login ) );
    }

    public function handle_update_profile() {
        $this->check();
        if ( ! is_user_logged_in() ) wp_send_json_error( array( 'message' => 'Not logged in.' ) );
        $uid = get_current_user_id();
        $first = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last  = isset( $_POST['last_name'] )  ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )  : '';
        $phone = isset( $_POST['phone'] )      ? sanitize_text_field( wp_unslash( $_POST['phone'] ) )      : '';
        $country = isset( $_POST['country'] )  ? strtoupper( substr( sanitize_text_field( wp_unslash( $_POST['country'] ) ), 0, 2 ) ) : '';

        wp_update_user( array(
            'ID' => $uid,
            'first_name' => $first,
            'last_name' => $last,
            'display_name' => trim( $first . ' ' . $last ),
        ) );
        update_user_meta( $uid, 'es_phone', $phone );
        if ( $country ) {
            update_user_meta( $uid, 'es_country', $country );
            update_user_meta( $uid, 'es_timezone', ES_Helpers::tz_for_country( $country ) );
        }
        wp_send_json_success( array( 'message' => 'Profile updated.' ) );
    }

    /* ============== SLOTS / BOOKINGS ============== */

    /** Returns slots grouped by date for a month, with availability info, in user's tz */
    public function handle_get_calendar_month() {
        $this->check();
        $year  = isset( $_POST['year'] )  ? (int) $_POST['year']  : (int) current_time( 'Y' );
        $month = isset( $_POST['month'] ) ? (int) $_POST['month'] : (int) current_time( 'n' );
        if ( $month < 1 || $month > 12 ) wp_send_json_error( array( 'message' => 'Invalid month' ) );

        // Optional type filter (comma-separated). 'personal' is always blocked for non-admins.
        $allowed_types = $this->parse_types_filter();

        $days_in = (int) date( 't', strtotime( "$year-$month-01" ) );
        $from = sprintf( '%04d-%02d-01', $year, $month );
        $to   = sprintf( '%04d-%02d-%02d', $year, $month, $days_in );

        $slots = ES_DB::get_slots_in_range( $from, $to );
        $today = current_time( 'Y-m-d' );
        $current_user_id = get_current_user_id();

        $by_date = array();
        foreach ( $slots as $s ) {
            // hide 'personal' from public users
            if ( $s->slot_type === 'personal' && ! current_user_can( 'manage_options' ) ) continue;
            // apply types filter
            if ( $allowed_types && ! in_array( $s->slot_type, $allowed_types, true ) ) continue;
            $remaining = (int) $s->capacity - (int) $s->booked_count;

            // For logged-in users: a slot they've already booked is not "open" to them
            $bookable_for_user = ( $remaining > 0 && $s->slot_date >= $today );
            if ( $bookable_for_user && $current_user_id ) {
                if ( ES_DB::user_has_booked_slot( $s->id, $current_user_id ) ) {
                    $bookable_for_user = false;
                }
            }

            $iso = $s->slot_date;
            if ( ! isset( $by_date[ $iso ] ) ) $by_date[ $iso ] = array(
                'count' => 0,
                'open' => 0,
                'types' => array(),
            );
            $by_date[ $iso ]['count']++;
            if ( $bookable_for_user ) $by_date[ $iso ]['open']++;
            if ( ! in_array( $s->slot_type, $by_date[ $iso ]['types'], true ) ) {
                $by_date[ $iso ]['types'][] = $s->slot_type;
            }
        }

        wp_send_json_success( array(
            'year' => $year, 'month' => $month,
            'month_name' => date_i18n( 'F Y', strtotime( $from ) ),
            'first_weekday' => (int) date( 'w', strtotime( $from ) ),
            'days_in' => $days_in,
            'today' => $today,
            'days' => $by_date,
            'tz' => ES_Helpers::user_tz()->getName(),
        ) );
    }

    /** Get full slot list for one date (in user's tz) */
    public function handle_get_slot() {
        $this->check();
        $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
        if ( ! ES_Helpers::valid_date( $date ) ) wp_send_json_error( array( 'message' => 'Invalid date' ) );

        $allowed_types = $this->parse_types_filter();

        $slots = ES_DB::get_slots_for_date( $date );
        $current_user = get_current_user_id();
        $today = current_time( 'Y-m-d' );

        $out = array();
        foreach ( $slots as $s ) {
            if ( $s->slot_type === 'personal' && ! current_user_can( 'manage_options' ) ) continue;
            if ( $allowed_types && ! in_array( $s->slot_type, $allowed_types, true ) ) continue;
            $remaining = (int) $s->capacity - (int) $s->booked_count;
            $start_user = ES_Helpers::to_user_tz( $s->slot_date . ' ' . $s->start_time );
            $end_user   = ES_Helpers::to_user_tz( $s->slot_date . ' ' . $s->end_time );
            $is_past = ( $s->slot_date < $today );
            $already_booked = $current_user ? ES_DB::user_has_booked_slot( $s->id, $current_user ) : false;
            $out[] = array(
                'id'         => (int) $s->id,
                'date'       => $s->slot_date,
                'start'      => substr( $s->start_time, 0, 5 ),
                'end'        => substr( $s->end_time, 0, 5 ),
                'start_user' => $start_user ? $start_user->format( 'g:i A' ) : substr( $s->start_time, 0, 5 ),
                'end_user'   => $end_user ? $end_user->format( 'g:i A' ) : substr( $s->end_time, 0, 5 ),
                'duration'   => (int) $s->duration_min,
                'type'       => $s->slot_type,
                'type_label' => ES_Helpers::slot_type_label( $s->slot_type ),
                'type_color' => ES_Helpers::slot_type_color( $s->slot_type ),
                'capacity'   => (int) $s->capacity,
                'booked'     => (int) $s->booked_count,
                'remaining'  => $remaining,
                'platform'   => $s->platform,
                'title'      => $s->title,
                'notes'      => $s->notes,
                'is_past'    => $is_past,
                'is_full'    => $remaining <= 0,
                'already_booked' => $already_booked,
            );
        }
        wp_send_json_success( array( 'date' => $date, 'slots' => $out, 'tz' => ES_Helpers::user_tz()->getName() ) );
    }

    public function handle_book_slot() {
        // Verify nonce with a clear, recoverable error message (stale nonces on
        // cached pages are the most common cause of "booking silently fails").
        if ( ! check_ajax_referer( 'es_fe_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Your session expired. Please refresh the page and try booking again.' ), 403 );
        }

        $slot_id = isset( $_POST['slot_id'] ) ? (int) $_POST['slot_id'] : 0;
        $note    = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
        $parent  = isset( $_POST['parent_name'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_name'] ) ) : '';
        $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $uid = get_current_user_id();
        if ( ! $uid ) wp_send_json_error( array( 'message' => 'Please log in first.' ) );
        if ( ! $slot_id ) wp_send_json_error( array( 'message' => 'No slot was selected.' ) );

        if ( $parent !== '' ) {
            update_user_meta( $uid, 'es_parent_name', $parent );
        }
        if ( $phone !== '' ) {
            update_user_meta( $uid, 'es_phone', $phone );
        }
        if ( $email !== '' ) {
            $current_user = get_userdata( $uid );
            if ( $current_user && strtolower( (string) $current_user->user_email ) !== strtolower( $email ) ) {
                wp_update_user( array(
                    'ID'         => $uid,
                    'user_email' => $email,
                ) );
            }
        }

        $slot = ES_DB::get_slot( $slot_id );
        if ( ! $slot ) wp_send_json_error( array( 'message' => 'Slot not found.' ) );
        if ( $slot->slot_type === 'personal' ) wp_send_json_error( array( 'message' => 'This slot is not available.' ) );
        if ( $slot->slot_date < current_time( 'Y-m-d' ) ) wp_send_json_error( array( 'message' => 'Cannot book past dates.' ) );

        if ( ES_DB::user_has_booked_slot( $slot_id, $uid ) ) {
            wp_send_json_error( array( 'message' => 'You have already booked this slot.' ) );
        }

        $booked = ES_DB::count_bookings( $slot_id );
        if ( $booked >= (int) $slot->capacity ) wp_send_json_error( array( 'message' => 'This slot is fully booked.' ) );

        // (#11) If the user holds a session-limited plan and has used all of
        // their sessions, block further self-scheduling. Users with no plan
        // (e.g. open public bookings) are unaffected. Note: the session is NOT
        // consumed here — consumption happens when attendance is marked (Present
        // or Absent-without-permission), keeping a single source of truth.
        $plan = ES_Packages::get_active_plan( $uid );
        if ( $plan && (int) ( $plan->total_sessions ?? 0 ) > 0 ) {
            if ( ES_Packages::remaining_sessions( $plan ) <= 0 ) {
                wp_send_json_error( array( 'message' => 'You have used all the sessions in your current package. Please renew or upgrade to book more.' ) );
            }
        }

        $data = array(
            'slot_id'   => $slot_id,
            'user_id'   => $uid,
            'status'    => 'confirmed',
            'user_note' => $note,
        );
        if ( $slot->slot_type === '1to1' && $plan ) {
            $data['payment_id'] = (int) $plan->id;
        }

        // Create Zoom meeting if applicable
        if ( ES_Zoom::is_configured() && stripos( $slot->platform, 'zoom' ) !== false ) {
            $user = wp_get_current_user();
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
            // Cleanup zoom if needed
            if ( ! empty( $data['zoom_meeting_id'] ) ) ES_Zoom::delete_meeting( $data['zoom_meeting_id'] );
            wp_send_json_error( array( 'message' => 'Could not save booking. Please try again.' ) );
        }

        // Pass the user's note through as an additional comment in the emails (#14).
        ES_Mailer::send_booking_confirmation( $bid, $note );
        ES_Mailer::send_admin_notification( $bid, $note );

        wp_send_json_success( array(
            'booking_id' => $bid,
            'message' => 'Booking confirmed!',
            'zoom_join_url' => $data['zoom_join_url'] ?? '',
        ) );
    }

    public function handle_cancel_booking() {
        $this->check();
        $bid = isset( $_POST['booking_id'] ) ? (int) $_POST['booking_id'] : 0;
        $uid = get_current_user_id();
        if ( ! $uid ) wp_send_json_error( array( 'message' => 'Not logged in.' ) );

        $b = ES_DB::get_booking( $bid );
        if ( ! $b || (int) $b->user_id !== $uid ) wp_send_json_error( array( 'message' => 'Booking not found.' ) );

        if ( ! empty( $b->zoom_meeting_id ) ) @ES_Zoom::delete_meeting( $b->zoom_meeting_id );
        ES_DB::delete_booking( $bid );

        wp_send_json_success( array( 'message' => 'Booking cancelled.' ) );
    }
}
