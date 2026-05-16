<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Zoom Server-to-Server OAuth integration.
 * Docs: https://developers.zoom.us/docs/internal-apps/s2s-oauth/
 *
 * Required credentials (entered by admin in Settings):
 *  - Account ID
 *  - Client ID
 *  - Client Secret
 */
class ES_Zoom {

    const TOKEN_URL = 'https://zoom.us/oauth/token';
    const API_BASE  = 'https://api.zoom.us/v2';
    const TOKEN_OPT = 'es_zoom_token';

    /** Are credentials present and integration enabled? */
    public static function is_configured() {
        $s = self::settings();
        return ! empty( $s['enabled'] )
            && ! empty( $s['account_id'] )
            && ! empty( $s['client_id'] )
            && ! empty( $s['client_secret'] );
    }

    public static function settings() {
        return wp_parse_args( get_option( 'es_zoom_settings', array() ), array(
            'enabled'       => 0,
            'account_id'    => '',
            'client_id'     => '',
            'client_secret' => '',
            'host_email'    => '', // Zoom user (host) - email address
            'auto_record'   => 0,
            'waiting_room'  => 1,
            'last_error'    => '',
            'last_test_ok'  => 0,
        ) );
    }

    public static function update_settings( $arr ) {
        $current = self::settings();
        $merged = array_merge( $current, $arr );
        update_option( 'es_zoom_settings', $merged );
    }

    /** Get a valid OAuth access token (cached). Returns string|WP_Error */
    public static function get_token() {
        $cached = get_transient( self::TOKEN_OPT );
        if ( $cached ) return $cached;

        $s = self::settings();
        if ( empty( $s['account_id'] ) || empty( $s['client_id'] ) || empty( $s['client_secret'] ) ) {
            return new WP_Error( 'cbc_zoom_no_creds', 'Zoom credentials are not configured.' );
        }

        $resp = wp_remote_post( self::TOKEN_URL, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $s['client_id'] . ':' . $s['client_secret'] ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'account_credentials',
                'account_id' => $s['account_id'],
            ),
        ) );

        if ( is_wp_error( $resp ) ) {
            self::log_error( 'token: ' . $resp->get_error_message() );
            return $resp;
        }

        $code = wp_remote_retrieve_response_code( $resp );
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );

        if ( $code !== 200 || empty( $body['access_token'] ) ) {
            $msg = isset( $body['reason'] ) ? $body['reason'] : ( isset( $body['errorMessage'] ) ? $body['errorMessage'] : 'Unknown auth error' );
            self::log_error( 'token (' . $code . '): ' . $msg );
            return new WP_Error( 'cbc_zoom_auth_failed', $msg );
        }

        $token = $body['access_token'];
        $ttl   = isset( $body['expires_in'] ) ? max( 60, (int) $body['expires_in'] - 60 ) : 3300;
        set_transient( self::TOKEN_OPT, $token, $ttl );
        return $token;
    }

    /** Test the credentials by fetching the user record. */
    public static function test_credentials() {
        $token = self::get_token();
        if ( is_wp_error( $token ) ) {
            self::update_settings( array( 'last_test_ok' => 0 ) );
            return $token;
        }

        $s = self::settings();
        $user = ! empty( $s['host_email'] ) ? $s['host_email'] : 'me';
        $resp = wp_remote_get( self::API_BASE . '/users/' . rawurlencode( $user ), array(
            'timeout' => 12,
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        ) );
        if ( is_wp_error( $resp ) ) {
            self::log_error( 'test: ' . $resp->get_error_message() );
            self::update_settings( array( 'last_test_ok' => 0 ) );
            return $resp;
        }
        $code = wp_remote_retrieve_response_code( $resp );
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( $code !== 200 ) {
            $msg = isset( $body['message'] ) ? $body['message'] : 'HTTP ' . $code;
            self::log_error( 'test (' . $code . '): ' . $msg );
            self::update_settings( array( 'last_test_ok' => 0 ) );
            return new WP_Error( 'cbc_zoom_test_failed', $msg );
        }
        self::update_settings( array( 'last_test_ok' => time(), 'last_error' => '' ) );
        return $body;
    }

    /**
     * Create a Zoom meeting. Returns array on success, WP_Error on failure.
     *
     * @param array $args { date(Y-m-d), start_time(H:i), duration(int min), topic, agenda, timezone }
     */
    public static function create_meeting( $args ) {
        $token = self::get_token();
        if ( is_wp_error( $token ) ) return $token;

        $s = self::settings();
        $host = ! empty( $s['host_email'] ) ? $s['host_email'] : 'me';

        // Build start_time: ISO8601 in the work timezone (admin's configured TZ)
        $tz   = ES_Helpers::work_tz();
        $when = new DateTime( $args['date'] . ' ' . $args['start_time'], $tz );
        $iso  = $when->format( 'Y-m-d\TH:i:s' );

        $body = array(
            'topic'      => mb_substr( $args['topic'] ?: 'Booking', 0, 200 ),
            'type'       => 2, // scheduled
            'start_time' => $iso,
            'duration'   => max( 15, (int) $args['duration'] ),
            'timezone'   => $tz->getName(),
            'agenda'     => mb_substr( $args['agenda'] ?: '', 0, 2000 ),
            'settings'   => array(
                'host_video'        => true,
                'participant_video' => true,
                'join_before_host'  => false,
                'mute_upon_entry'   => true,
                'waiting_room'      => ! empty( $s['waiting_room'] ),
                'auto_recording'    => ! empty( $s['auto_record'] ) ? 'cloud' : 'none',
                'approval_type'     => 2,
            ),
        );

        $resp = wp_remote_post( self::API_BASE . '/users/' . rawurlencode( $host ) . '/meetings', array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode( $body ),
        ) );

        if ( is_wp_error( $resp ) ) {
            self::log_error( 'create: ' . $resp->get_error_message() );
            return $resp;
        }
        $code = wp_remote_retrieve_response_code( $resp );
        $data = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( $code !== 201 && $code !== 200 ) {
            $msg = isset( $data['message'] ) ? $data['message'] : 'HTTP ' . $code;
            self::log_error( 'create (' . $code . '): ' . $msg );
            return new WP_Error( 'cbc_zoom_create_failed', $msg );
        }
        return array(
            'meeting_id' => isset( $data['id'] ) ? (string) $data['id'] : '',
            'join_url'   => isset( $data['join_url'] ) ? $data['join_url'] : '',
            'start_url'  => isset( $data['start_url'] ) ? $data['start_url'] : '',
            'password'   => isset( $data['password'] ) ? $data['password'] : '',
            'topic'      => isset( $data['topic'] ) ? $data['topic'] : '',
        );
    }

    public static function delete_meeting( $meeting_id ) {
        if ( ! $meeting_id ) return false;
        $token = self::get_token();
        if ( is_wp_error( $token ) ) return $token;
        $resp = wp_remote_request( self::API_BASE . '/meetings/' . rawurlencode( $meeting_id ), array(
            'method' => 'DELETE',
            'timeout' => 12,
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        ) );
        if ( is_wp_error( $resp ) ) return $resp;
        return wp_remote_retrieve_response_code( $resp ) === 204;
    }

    private static function log_error( $msg ) {
        self::update_settings( array( 'last_error' => date( 'Y-m-d H:i:s' ) . ' — ' . $msg ) );
    }
}
