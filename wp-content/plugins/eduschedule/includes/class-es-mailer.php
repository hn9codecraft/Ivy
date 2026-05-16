<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Mailer {

    public static function send_booking_confirmation( $booking_id ) {
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

        $subject = sprintf( '[%s] Booking Confirmed — %s', get_bloginfo( 'name' ), $start_dt->format( 'M j, g:i A' ) );

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
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
        return wp_mail( $user->user_email, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
    }

    public static function send_admin_notification( $booking_id ) {
        $b = ES_DB::get_booking( $booking_id );
        if ( ! $b ) return false;
        $slot = ES_DB::get_slot( $b->slot_id );
        $user = get_userdata( $b->user_id );
        if ( ! $slot || ! $user ) return false;

        $admin_email = get_option( 'admin_email' );
        if ( ! is_email( $admin_email ) ) return false;

        $subject = sprintf( '[%s] New booking: %s — %s',
            get_bloginfo( 'name' ),
            $slot->slot_date . ' ' . substr( $slot->start_time, 0, 5 ),
            $user->display_name
        );

        $body  = "New booking received.\n\n";
        $body .= "User: " . $user->display_name . " <" . $user->user_email . ">\n";
        $body .= "Slot: " . $slot->slot_date . ' ' . substr( $slot->start_time, 0, 5 ) . " – " . substr( $slot->end_time, 0, 5 ) . "\n";
        $body .= "Type: " . ES_Helpers::slot_type_label( $slot->slot_type ) . "\n";
        $body .= "Platform: " . $slot->platform . "\n";
        if ( $slot->title ) $body .= "Topic: " . $slot->title . "\n";
        if ( ! empty( $b->zoom_join_url ) ) $body .= "\nZoom: " . $b->zoom_join_url . "\n";
        $body .= "\nManage: " . admin_url( 'admin.php?page=eduschedule-bookings' );

        return wp_mail( $admin_email, $subject, $body );
    }
}
