<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Mailer {

    /**
     * Register mail hooks. Called once on plugins_loaded. When SMTP is enabled
     * in Settings, all wp_mail() calls (plugin and core) are routed through the
     * configured authenticated SMTP server — the reliable fix for Gmail and
     * other providers silently dropping unauthenticated PHP mail().
     */
    public static function init() {
        add_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ) );
    }

    /**
     * Configure PHPMailer to use the SMTP server set in Settings.
     *
     * @param PHPMailer $phpmailer  Passed by reference by the phpmailer_init hook.
     */
    public static function configure_smtp( $phpmailer ) {
        $s = ES_Helpers::settings();

        if ( empty( $s['smtp_enabled'] ) || empty( $s['smtp_host'] ) ) {
            return; // SMTP off or not configured — leave WP's default transport.
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $s['smtp_host'];
        $phpmailer->Port = (int) ( $s['smtp_port'] ?: 587 );

        $enc = isset( $s['smtp_encryption'] ) ? $s['smtp_encryption'] : 'tls';
        if ( $enc === 'ssl' ) {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ( $enc === 'tls' ) {
            $phpmailer->SMTPSecure = 'tls';
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        if ( ! empty( $s['smtp_auth'] ) ) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $s['smtp_username'];
            $phpmailer->Password = $s['smtp_password'];
        } else {
            $phpmailer->SMTPAuth = false;
        }

        // Keep the From aligned with the authenticated account when possible —
        // many providers (Gmail included) reject a From that doesn't match the
        // authenticated user. Fall back to the configured From otherwise.
        $from_email = ! empty( $s['from_email'] ) ? $s['from_email'] : self::default_from_email();
        if ( ! empty( $s['smtp_auth'] ) && is_email( $s['smtp_username'] ) ) {
            $from_email = $s['smtp_username'];
        }
        $from_name = ! empty( $s['from_name'] ) ? $s['from_name'] : get_bloginfo( 'name' );
        try {
            $phpmailer->setFrom( $from_email, $from_name, false );
        } catch ( Exception $e ) {
            // Ignore — PHPMailer will keep whatever From was already set.
        }
    }

    /**
     * Central send wrapper. Adds a proper From / Reply-To header (the #1 reason
     * wp_mail "silently fails" on most hosts is a missing/invalid From address),
     * forces HTML content type, and logs any delivery failure so issues are
     * visible instead of swallowed by an "@" suppressor.
     *
     * @param string|array $to
     * @param string $subject
     * @param string $body      HTML body
     * @param array  $extra_headers
     * @return bool
     */
    public static function send( $to, $subject, $body, $extra_headers = array() ) {
        $s = ES_Helpers::settings();

        $from_name  = ! empty( $s['from_name'] )  ? $s['from_name']  : get_bloginfo( 'name' );
        $from_email = ! empty( $s['from_email'] ) ? $s['from_email'] : self::default_from_email();
        $reply_to   = ! empty( $s['reply_to'] )   ? $s['reply_to']   : $from_email;

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'From: %s <%s>', $from_name, $from_email ),
            sprintf( 'Reply-To: %s', $reply_to ),
        );
        if ( ! empty( $extra_headers ) ) {
            $headers = array_merge( $headers, (array) $extra_headers );
        }

        // Capture a failure reason for this single send.
        $err = null;
        $catch = function ( $wp_error ) use ( &$err ) {
            if ( is_wp_error( $wp_error ) ) {
                $err = $wp_error->get_error_message();
            }
        };
        add_action( 'wp_mail_failed', $catch );

        $ok = wp_mail( $to, $subject, $body, $headers );

        remove_action( 'wp_mail_failed', $catch );

        if ( ! $ok ) {
            $reason = $err ? ' Reason: ' . $err : '';
            error_log( '[EduSchedule] Email failed to ' . ( is_array( $to ) ? implode( ',', $to ) : $to ) . ' — "' . $subject . '".' . $reason );
        }
        return $ok;
    }

    /**
     * Best-effort default From address on the site's own domain so the message
     * passes SPF/DMARC checks rather than being silently dropped.
     */
    public static function default_from_email() {
        $admin = get_option( 'admin_email' );
        $host  = parse_url( home_url(), PHP_URL_HOST );
        if ( $host ) {
            $host = preg_replace( '/^www\./i', '', $host );
            return 'no-reply@' . $host;
        }
        return $admin ?: 'no-reply@localhost';
    }

    /** Resolve the admin notification recipient. */
    public static function admin_recipient() {
        $s = ES_Helpers::settings();
        $email = ! empty( $s['admin_notify_email'] ) ? $s['admin_notify_email'] : get_option( 'admin_email' );
        return is_email( $email ) ? $email : '';
    }

    public static function send_booking_confirmation( $booking_id, $extra_comment = '' ) {
        $b = ES_DB::get_booking( $booking_id );
        if ( ! $b ) return false;
        $slot = ES_DB::get_slot( $b->slot_id );
        if ( ! $slot ) return false;
        $user = get_userdata( $b->user_id );
        if ( ! $user ) return false;

        $tz_user = ES_Helpers::user_tz( $user->ID );
        $start_dt = ES_Helpers::to_user_tz( $slot->slot_date . ' ' . $slot->start_time, $user->ID );
        $end_dt   = ES_Helpers::to_user_tz( $slot->slot_date . ' ' . $slot->end_time, $user->ID );

        if ( ! $start_dt ) return false;

        $when = $start_dt->format( 'l, F j, Y' ) . ' at ' . $start_dt->format( 'g:i A' )
              . ' – ' . $end_dt->format( 'g:i A' ) . ' (' . $tz_user->getName() . ')';

        $course_name = '';
        if ( ! empty( $slot->course_name ) ) {
            $course_name = (string) $slot->course_name;
        } elseif ( ! empty( $slot->course_id ) ) {
            $course_name = ES_Packages::course_name( (int) $slot->course_id );
        }

        $package_name = ! empty( $slot->package_name ) ? (string) $slot->package_name : '';
        if ( ( $course_name === '' || $package_name === '' ) && ! empty( $b->payment_id ) ) {
            global $wpdb;
            $pay_row = $wpdb->get_row( $wpdb->prepare(
                "SELECT course_name, package_name FROM {$wpdb->prefix}es_payments WHERE id = %d",
                (int) $b->payment_id
            ) );
            if ( $pay_row ) {
                if ( $course_name === '' && ! empty( $pay_row->course_name ) ) $course_name = (string) $pay_row->course_name;
                if ( $package_name === '' && ! empty( $pay_row->package_name ) ) $package_name = (string) $pay_row->package_name;
            }
        }

        $subject = $course_name !== ''
            ? sprintf( '[%s] %s Booking Confirmed — %s', get_bloginfo( 'name' ), $course_name, $start_dt->format( 'M j, g:i A' ) )
            : sprintf( '[%s] Booking Confirmed — %s', get_bloginfo( 'name' ), $start_dt->format( 'M j, g:i A' ) );

        ob_start(); ?>
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
          <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:#e91e63;color:#fff;padding:24px 28px">
              <div style="font-size:13px;letter-spacing:.6px;opacity:.85;text-transform:uppercase">Booking Confirmed</div>
              <h1 style="margin:6px 0 0;font-size:22px;font-weight:600">Hi <?php echo esc_html( $user->display_name ); ?>,</h1>
            </div>
            <div style="padding:24px 28px;color:#222;line-height:1.5;font-size:15px">
              <p style="margin:0 0 18px">Your booking has been confirmed. Details below.</p>

              <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;width:120px">Type</td><td style="padding:10px 0;font-weight:500"><?php echo esc_html( ES_Helpers::slot_type_label( $slot->slot_type ) ); ?></td></tr>
                <?php if ( $package_name !== '' ) : ?>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid #f0f0f3">Package</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $package_name ); ?></td></tr>
                <?php endif; ?>
                <?php if ( $course_name !== '' ) : ?>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid #f0f0f3">Course</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $course_name ); ?></td></tr>
                <?php endif; ?>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid #f0f0f3">When</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $when ); ?></td></tr>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid #f0f0f3">Duration</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo (int) $slot->duration_min; ?> minutes</td></tr>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid #f0f0f3">Platform</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $slot->platform ); ?></td></tr>
                <?php if ( $slot->title ) : ?>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid #f0f0f3;vertical-align:top">Topic</td><td style="padding:10px 0;border-top:1px solid #f0f0f3"><?php echo esc_html( $slot->title ); ?></td></tr>
                <?php endif; ?>
              </table>

              <?php if ( ! empty( $b->zoom_join_url ) ) : ?>
                <div style="background:#f3f4f6;border-radius:8px;padding:18px 20px;margin:18px 0">
                  <div style="font-size:13px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">Join the Zoom meeting</div>
                  <a href="<?php echo esc_url( $b->zoom_join_url ); ?>" style="display:inline-block;background:#2D8CFF;color:#fff;padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px">Join Meeting</a>
                  <div style="margin-top:14px;font-size:13px;color:#374151">
                    <div><strong>Meeting ID:</strong> <?php echo esc_html( $b->zoom_meeting_id ); ?></div>
                    <?php if ( ! empty( $b->zoom_password ) ) : ?>
                      <div><strong>Passcode:</strong> <?php echo esc_html( $b->zoom_password ); ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ( $extra_comment !== '' ) : ?>
                <div style="background:#f9fafb;border:1px solid #eef0f3;border-left:3px solid #e91e63;border-radius:8px;padding:14px 16px;margin:18px 0">
                  <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Additional notes</div>
                  <div style="font-size:14px;color:#374151;line-height:1.6;white-space:pre-wrap"><?php echo nl2br( esc_html( $extra_comment ) ); ?></div>
                </div>
              <?php endif; ?>
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
        return self::send( $user->user_email, $subject, $html );
    }

    public static function send_admin_notification( $booking_id, $extra_comment = '' ) {
        $s = ES_Helpers::settings();
        if ( empty( $s['notify_admin'] ) ) return false;

        $b = ES_DB::get_booking( $booking_id );
        if ( ! $b ) return false;
        $slot = ES_DB::get_slot( $b->slot_id );
        $user = get_userdata( $b->user_id );
        if ( ! $slot || ! $user ) return false;

        $admin_email = self::admin_recipient();
        if ( ! is_email( $admin_email ) ) return false;

        $subject = sprintf( '[%s] New booking: %s — %s',
            get_bloginfo( 'name' ),
            $slot->slot_date . ' ' . substr( $slot->start_time, 0, 5 ),
            $user->display_name
        );

        $rows  = '<tr><td style="padding:6px 0;color:#6b7280;width:120px">User</td><td style="padding:6px 0;font-weight:500">' . esc_html( $user->display_name ) . ' &lt;' . esc_html( $user->user_email ) . '&gt;</td></tr>';
        $rows .= '<tr><td style="padding:6px 0;color:#6b7280;border-top:1px solid #f0f0f3">When</td><td style="padding:6px 0;font-weight:500;border-top:1px solid #f0f0f3">' . esc_html( $slot->slot_date . ' ' . substr( $slot->start_time, 0, 5 ) . ' – ' . substr( $slot->end_time, 0, 5 ) ) . '</td></tr>';
        $rows .= '<tr><td style="padding:6px 0;color:#6b7280;border-top:1px solid #f0f0f3">Type</td><td style="padding:6px 0;font-weight:500;border-top:1px solid #f0f0f3">' . esc_html( ES_Helpers::slot_type_label( $slot->slot_type ) ) . '</td></tr>';
        $rows .= '<tr><td style="padding:6px 0;color:#6b7280;border-top:1px solid #f0f0f3">Platform</td><td style="padding:6px 0;font-weight:500;border-top:1px solid #f0f0f3">' . esc_html( $slot->platform ) . '</td></tr>';
        if ( $slot->title ) {
            $rows .= '<tr><td style="padding:6px 0;color:#6b7280;border-top:1px solid #f0f0f3">Topic</td><td style="padding:6px 0;border-top:1px solid #f0f0f3">' . esc_html( $slot->title ) . '</td></tr>';
        }
        if ( ! empty( $b->zoom_join_url ) ) {
            $rows .= '<tr><td style="padding:6px 0;color:#6b7280;border-top:1px solid #f0f0f3">Zoom</td><td style="padding:6px 0;border-top:1px solid #f0f0f3"><a href="' . esc_url( $b->zoom_join_url ) . '">' . esc_html( $b->zoom_join_url ) . '</a></td></tr>';
        }

        $comment_block = '';
        if ( $extra_comment !== '' ) {
            $comment_block = '<div style="background:#f9fafb;border:1px solid #eef0f3;border-left:3px solid #1e293b;border-radius:8px;padding:14px 16px;margin:16px 0"><div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Additional Comments</div><div style="font-size:14px;color:#374151;line-height:1.6;white-space:pre-wrap">' . nl2br( esc_html( $extra_comment ) ) . '</div></div>';
        }

        $manage = admin_url( 'admin.php?page=eduschedule-bookings' );
        $html = '<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">'
              . '<div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">'
              . '<div style="background:#1e293b;color:#fff;padding:20px 28px;font-size:18px;font-weight:600">New booking received</div>'
              . '<div style="padding:22px 28px;color:#222;font-size:15px"><table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">' . $rows . '</table>'
              . $comment_block
              . '<div style="text-align:center;margin:20px 0 4px"><a href="' . esc_url( $manage ) . '" style="display:inline-block;background:#1e293b;color:#fff;padding:11px 24px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px">Manage bookings</a></div>'
              . '</div></div></div>';

        return self::send( $admin_email, $subject, $html );
    }

    /**
     * After-Call email to the STUDENT (HTML).
     *
     * @param WP_User $user
     * @param string  $outcome          e.g. "1:1 Student", "Group Student", "Follow-up Needed", "Not Interested"
     * @param array   $packages_list    array of package row objects
     * @param string  $share_link       personalised package-selection link (may be empty)
     * @param string  $comments         additional comments typed by the admin
     */
    public static function send_after_call_student( $user, $outcome, $packages_list, $share_link, $comments, $course_name = '' ) {
        if ( ! $user || ! is_email( $user->user_email ) ) return false;

        $needs_package = in_array( $outcome, array( '1:1 Student', 'Group Student', 'Renewed' ), true );
        $brand         = get_bloginfo( 'name' );
        $accent        = '#e91e63';
        // Course name (when set) is surfaced in the subject so the student sees
        // which course this is about at a glance.
        $subject       = $course_name !== ''
            ? sprintf( '%s — %s — %s', $outcome === 'Renewed' ? 'Package Renewal' : 'Next Steps', $course_name, $brand )
            : ( $outcome === 'Renewed' ? 'Package Renewal — ' . $brand : 'Next Steps — ' . $brand );

        ob_start(); ?>
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
          <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:<?php echo esc_attr( $accent ); ?>;color:#fff;padding:24px 28px">
              <div style="font-size:13px;letter-spacing:.6px;opacity:.85;text-transform:uppercase">Thanks for speaking with us</div>
              <h1 style="margin:6px 0 0;font-size:22px;font-weight:600">Hi <?php echo esc_html( $user->display_name ); ?>,</h1>
            </div>
            <div style="padding:24px 28px;color:#222;line-height:1.6;font-size:15px">

              <?php if ( $course_name !== '' ) : ?>
                <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:8px;padding:12px 16px;margin:0 0 18px">
                  <div style="font-size:11px;color:#9d174d;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Course</div>
                  <div style="font-size:15px;font-weight:600;color:#831843"><?php echo esc_html( $course_name ); ?></div>
                </div>
              <?php endif; ?>

              <?php if ( $needs_package ) : ?>
                <p style="margin:0 0 16px"><?php echo $outcome === 'Renewed' ? 'Your renewed package details are below:' : 'Based on our conversation, we have prepared the following package' . ( count( $packages_list ) > 1 ? 's' : '' ) . ' for you:'; ?></p>
                <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin-bottom:18px">
                  <?php foreach ( $packages_list as $p ) :
                      $pcur = ! empty( $p->currency ) ? $p->currency : 'INR'; ?>
                    <tr>
                      <td style="padding:10px 0;border-top:1px solid #f0f0f3;font-weight:500"><?php echo esc_html( $p->package_name ); ?></td>
                      <td style="padding:10px 0;border-top:1px solid #f0f0f3;text-align:right;color:#374151">
                        <?php echo $p->price > 0 ? esc_html( ES_Helpers::format_price( $p->price, $pcur ) ) : ''; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </table>

                <?php if ( $share_link ) : ?>
                  <div style="text-align:center;margin:22px 0">
                    <a href="<?php echo esc_url( $share_link ); ?>" style="display:inline-block;background:<?php echo esc_attr( $accent ); ?>;color:#fff;padding:13px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px">Select Your Package</a>
                  </div>
                  <p style="margin:0 0 16px;font-size:13px;color:#6b7280;text-align:center">This link expires in 14 days.</p>
                <?php endif; ?>

              <?php elseif ( $outcome === 'Follow-up Needed' ) : ?>
                <p style="margin:0 0 16px">Thank you for your time today — we'll follow up with you again soon.</p>
              <?php else : ?>
                <p style="margin:0 0 16px">Thank you for your time. If you change your mind, we're always here to help.</p>
              <?php endif; ?>

              <?php if ( $comments ) : ?>
                <div style="background:#f9fafb;border:1px solid #eef0f3;border-left:3px solid <?php echo esc_attr( $accent ); ?>;border-radius:8px;padding:14px 16px;margin:18px 0">
                  <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Notes from our call</div>
                  <div style="font-size:14px;color:#374151;line-height:1.6;white-space:pre-wrap"><?php echo nl2br( esc_html( $comments ) ); ?></div>
                </div>
              <?php endif; ?>

              <p style="margin:18px 0 0">Best regards,<br><strong><?php echo esc_html( $brand ); ?></strong></p>
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( $brand ); ?></div>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
        return self::send( $user->user_email, $subject, $html );
    }

    /**
     * After-Call email to the ADMIN (HTML) — includes a direct link to the
     * student's detail page and any additional comments.
     */
    public static function send_after_call_admin( $user, $outcome, $packages_list, $share_link, $comments, $group = null, $course_name = '' ) {
        $admin_email = self::admin_recipient();
        if ( ! is_email( $admin_email ) || ! $user ) return false;

        $brand       = get_bloginfo( 'name' );
        $detail_link = admin_url( 'admin.php?page=eduschedule-students&view=detail&user_id=' . (int) $user->ID );
        $subject     = $course_name !== ''
            ? sprintf( '[%s] After Call: %s — %s (%s)', $brand, $user->display_name, $outcome, $course_name )
            : sprintf( '[%s] After Call: %s — %s', $brand, $user->display_name, $outcome );

        ob_start(); ?>
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
          <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:#1e293b;color:#fff;padding:22px 28px">
              <div style="font-size:13px;letter-spacing:.6px;opacity:.8;text-transform:uppercase">After-Call Summary</div>
              <h1 style="margin:6px 0 0;font-size:20px;font-weight:600"><?php echo esc_html( $user->display_name ); ?> — <?php echo esc_html( $outcome ); ?></h1>
            </div>
            <div style="padding:22px 28px;color:#222;line-height:1.6;font-size:15px">
              <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin-bottom:8px">
                <tr><td style="padding:8px 0;color:#6b7280;font-size:13px;width:120px">Lead</td><td style="padding:8px 0;font-weight:500"><?php echo esc_html( $user->display_name ); ?> &lt;<?php echo esc_html( $user->user_email ); ?>&gt;</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Outcome</td><td style="padding:8px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $outcome ); ?></td></tr>
                <?php if ( $course_name !== '' ) : ?>
                  <tr><td style="padding:8px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Course</td><td style="padding:8px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $course_name ); ?></td></tr>
                <?php endif; ?>
                <?php if ( $group ) : ?>
                  <tr><td style="padding:8px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Group</td><td style="padding:8px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $group->group_name ); ?></td></tr>
                <?php endif; ?>
              </table>

              <?php if ( ! empty( $packages_list ) ) : ?>
                <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin:14px 0 6px">Packages staged</div>
                <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">
                  <?php foreach ( $packages_list as $p ) :
                      $pcur = ! empty( $p->currency ) ? $p->currency : 'INR'; ?>
                    <tr>
                      <td style="padding:7px 0;border-top:1px solid #f0f0f3"><?php echo esc_html( $p->package_name ); ?></td>
                      <td style="padding:7px 0;border-top:1px solid #f0f0f3;text-align:right;color:#374151"><?php echo esc_html( ES_Helpers::format_price( $p->price, $pcur ) ); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </table>
              <?php endif; ?>

              <?php if ( $comments ) : ?>
                <div style="background:#f9fafb;border:1px solid #eef0f3;border-left:3px solid #1e293b;border-radius:8px;padding:14px 16px;margin:16px 0">
                  <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">Additional Comments</div>
                  <div style="font-size:14px;color:#374151;line-height:1.6;white-space:pre-wrap"><?php echo nl2br( esc_html( $comments ) ); ?></div>
                </div>
              <?php endif; ?>

              <?php if ( $share_link ) : ?>
                <div style="font-size:13px;color:#6b7280;margin:14px 0 4px">Student package link</div>
                <div style="font-size:13px;word-break:break-all"><a href="<?php echo esc_url( $share_link ); ?>" style="color:#2563eb"><?php echo esc_html( $share_link ); ?></a></div>
              <?php endif; ?>

              <div style="text-align:center;margin:22px 0 4px">
                <a href="<?php echo esc_url( $detail_link ); ?>" style="display:inline-block;background:#1e293b;color:#fff;padding:12px 26px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px">View Student Profile</a>
              </div>
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( $brand ); ?></div>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
        return self::send( $admin_email, $subject, $html );
    }

    /**
     * Send a custom password-reset email with a branded button linking to the
     * plugin's own reset page (never wp-login.php). Routed through self::send()
     * so it respects the SMTP settings.
     */
    public static function send_password_reset( $user, $reset_link ) {
        if ( ! $user || ! is_email( $user->user_email ) ) return false;

        $brand   = get_bloginfo( 'name' );
        $accent  = '#e91e63';
        $subject = sprintf( 'Reset your password — %s', $brand );

        ob_start(); ?>
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
          <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:<?php echo esc_attr( $accent ); ?>;color:#fff;padding:24px 28px">
              <div style="font-size:13px;letter-spacing:.6px;opacity:.85;text-transform:uppercase">Password Reset</div>
              <h1 style="margin:6px 0 0;font-size:22px;font-weight:600">Hi <?php echo esc_html( $user->display_name ); ?>,</h1>
            </div>
            <div style="padding:24px 28px;color:#222;line-height:1.6;font-size:15px">
              <p style="margin:0 0 16px">We received a request to reset the password for your account. Click the button below to choose a new password.</p>
              <div style="text-align:center;margin:22px 0">
                <a href="<?php echo esc_url( $reset_link ); ?>" style="display:inline-block;background:<?php echo esc_attr( $accent ); ?>;color:#fff;padding:13px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px">Reset My Password</a>
              </div>
              <p style="margin:0 0 8px;font-size:13px;color:#6b7280">If the button doesn't work, copy and paste this link into your browser:</p>
              <p style="margin:0 0 16px;font-size:12px;word-break:break-all"><a href="<?php echo esc_url( $reset_link ); ?>" style="color:#2563eb"><?php echo esc_html( $reset_link ); ?></a></p>
              <p style="margin:18px 0 0;font-size:13px;color:#6b7280">If you didn't request this, you can safely ignore this email — your password will stay the same.</p>
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( $brand ); ?></div>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
        return self::send( $user->user_email, $subject, $html );
    }

    /**
     * Send registration notifications to the new student and to the admin.
     */
    public static function send_registration_notifications( $user_id ) {
        $user = get_userdata( (int) $user_id );
        if ( ! $user ) return false;

        $brand = get_bloginfo( 'name' );
        $student_subject = sprintf( 'Welcome to %s', $brand );
        ob_start(); ?>
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
          <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:#6366f1;color:#fff;padding:22px 28px">
              <div style="font-size:13px;letter-spacing:.6px;opacity:.85;text-transform:uppercase">Registration Complete</div>
              <h1 style="margin:6px 0 0;font-size:22px;font-weight:600">Hi <?php echo esc_html( $user->display_name ); ?>,</h1>
            </div>
            <div style="padding:24px 28px;color:#222;line-height:1.6;font-size:15px">
              <p style="margin:0 0 14px">Your account has been created successfully.</p>
              <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;width:120px">Name</td><td style="padding:10px 0;font-weight:500"><?php echo esc_html( $user->display_name ); ?></td></tr>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Email</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $user->user_email ); ?></td></tr>
              </table>
              <p style="margin:18px 0 0">You can now log in and continue with your course or package selection.</p>
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( $brand ); ?></div>
          </div>
        </div>
        <?php
        $student_html = ob_get_clean();
        $student_sent = self::send( $user->user_email, $student_subject, $student_html );

        $admin_sent = true;
        $admin_email = self::admin_recipient();
        if ( $admin_email ) {
            $phone = get_user_meta( $user->ID, 'es_phone', true );
            $country = get_user_meta( $user->ID, 'es_country', true );
            $admin_subject = sprintf( '[%s] New student registration: %s', $brand, $user->display_name );
            ob_start(); ?>
            <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
              <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
                <div style="background:#1e293b;color:#fff;padding:22px 28px">
                  <div style="font-size:13px;letter-spacing:.6px;opacity:.85;text-transform:uppercase">New Registration</div>
                  <h1 style="margin:6px 0 0;font-size:20px;font-weight:600"><?php echo esc_html( $user->display_name ); ?></h1>
                </div>
                <div style="padding:22px 28px;color:#222;line-height:1.6;font-size:15px">
                  <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">
                    <tr><td style="padding:8px 0;color:#6b7280;font-size:13px;width:120px">Email</td><td style="padding:8px 0;font-weight:500"><?php echo esc_html( $user->user_email ); ?></td></tr>
                    <?php if ( $phone ) : ?><tr><td style="padding:8px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Phone</td><td style="padding:8px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $phone ); ?></td></tr><?php endif; ?>
                    <?php if ( $country ) : ?><tr><td style="padding:8px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Country</td><td style="padding:8px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $country ); ?></td></tr><?php endif; ?>
                  </table>
                  <div style="text-align:center;margin:22px 0 4px"><a href="<?php echo esc_url( admin_url( 'admin.php?page=eduschedule-students&view=detail&user_id=' . (int) $user->ID ) ); ?>" style="display:inline-block;background:#1e293b;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px">View Student</a></div>
                </div>
              </div>
            </div>
            <?php
            $admin_html = ob_get_clean();
            $admin_sent = self::send( $admin_email, $admin_subject, $admin_html );
        }

        return (bool) ( $student_sent && $admin_sent );
    }

}
