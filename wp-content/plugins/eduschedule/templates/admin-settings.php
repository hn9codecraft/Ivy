<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="es-admin">
    <div class="es-page-head">
        <div>
            <h1>Settings</h1>
            <p class="es-page-sub">Configure timezone, platforms, and frontend pages.</p>
        </div>
    </div>

    <?php if ( ! empty( $_GET['saved'] ) ) : ?>
        <div class="es-notice es-notice-success">Settings saved.</div>
    <?php endif; ?>
    <?php if ( ! empty( $_GET['pages'] ) ) : ?>
        <div class="es-notice es-notice-success"><?php echo esc_html( wp_unslash( $_GET['pages'] ) ); ?></div>
    <?php endif; ?>
    <?php if ( ! empty( $_GET['reset_done'] ) ) : ?>
        <div class="es-notice es-notice-success">1:1 and Group operational data was cleared. WordPress users and package master data were kept.</div>
    <?php endif; ?>
    <?php if ( ! empty( $_GET['reset_error'] ) ) : ?>
        <div class="es-notice es-notice-error">Data reset was not completed. Please type RESET exactly before clicking the reset button.</div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'es_save_settings' ); ?>
        <input type="hidden" name="action" value="es_save_settings" />

        <div class="es-card">
            <h3>General</h3>
            <div class="es-field">
                <label class="es-label">Site / Brand Name</label>
                <input type="text" name="site_name" value="<?php echo esc_attr( $settings['site_name'] ); ?>" />
            </div>
            <div class="es-field">
                <label class="es-label">Work Country</label>
                <select name="work_country">
                    <?php foreach ( ES_Helpers::countries() as $code => $info ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $settings['work_country'], $code ); ?>>
                            <?php echo esc_html( $info['name'] ); ?> — <?php echo esc_html( $info['tz'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="es-helper">Slot times you create are stored in this timezone. Customers see them converted to their own timezone.</span>
            </div>
        </div>

        <div class="es-card">
            <h3>Meeting Platforms</h3>
            <div class="es-field">
                <label class="es-label">Available Platforms (one per line)</label>
                <textarea name="platforms" rows="4"><?php echo esc_textarea( implode( "\n", $settings['platforms'] ) ); ?></textarea>
            </div>
            <div class="es-field">
                <label class="es-label">Default Platform</label>
                <input type="text" name="default_platform" value="<?php echo esc_attr( $settings['default_platform'] ); ?>" />
            </div>
        </div>

        <div class="es-card">
            <h3>Registration</h3>
            <label class="es-checkbox-row"><input type="checkbox" name="register_open" value="1" <?php checked( ! empty( $settings['register_open'] ) ); ?> /> <span>Allow anyone to self-register</span></label>
        </div>

        <div class="es-card">
            <h3>Frontend Pages</h3>
            <p class="es-helper">Pages that contain the relevant shortcodes. The Reset Password page should host <code>[eduschedule_reset]</code> — emails sent by the "Forgot password" flow link here. Click "Auto-Create Pages" below if you haven't made them yet.</p>
            <?php
            $pages = get_pages( array( 'sort_column' => 'post_title', 'number' => 100 ) );
            $page_field = function( $key, $label ) use ( $settings, $pages ) {
                echo '<div class="es-field"><label class="es-label">' . esc_html( $label ) . '</label><select name="' . esc_attr( $key ) . '">';
                echo '<option value="0">— None —</option>';
                foreach ( $pages as $p ) {
                    echo '<option value="' . (int) $p->ID . '" ' . selected( (int) ( $settings[ $key ] ?? 0 ), (int) $p->ID, false ) . '>' . esc_html( $p->post_title ) . '</option>';
                }
                echo '</select></div>';
            };
            $page_field( 'login_page_id',    'Login Page' );
            $page_field( 'register_page_id', 'Register Page' );
            $page_field( 'dashboard_page_id','Dashboard Page' );
            $page_field( 'reset_page_id',    'Reset Password Page' );
            ?>
        </div>

        <div class="es-card">
            <h3>Currency &amp; Billing</h3>
            <div class="es-field">
                <label class="es-label">Default Currency</label>
                <select name="default_currency">
                    <?php foreach ( ES_Helpers::currencies() as $code => $info ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $settings['default_currency'], $code ); ?>>
                            <?php echo esc_html( $info['symbol'] . '  ' . $code . ' — ' . $info['name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="es-helper">Used as the default when creating a new package.</span>
            </div>
            <div class="es-field">
                <label class="es-label">Yearly Discount (%)</label>
                <input type="number" name="yearly_discount" min="0" max="100" step="0.1"
                       value="<?php echo esc_attr( $settings['yearly_discount'] ); ?>" />
                <span class="es-helper">Discount applied to the monthly price for the selected discount duration below.</span>
            </div>
            <div class="es-field">
                <label class="es-label">Discount Duration (Months)</label>
                <input type="number" name="yearly_discount_months" min="1" max="60" step="1"
                       value="<?php echo esc_attr( $settings['yearly_discount_months'] ?? 12 ); ?>" />
                <span class="es-helper">Example: enter <strong>12%</strong> and <strong>6 months</strong>. Frontend will calculate discounted monthly price × 6 months and sessions for 6 months.</span>
            </div>
            <label class="es-checkbox-row">
                <input type="checkbox" name="enable_yearly" value="1" <?php checked( ! empty( $settings['enable_yearly'] ) ); ?> />
                <span>Show Monthly / Yearly toggle on the public packages shortcode</span>
            </label>
            <p class="es-helper" style="margin-top:6px">
                You can override per-page with <code>[eduschedule_packages yearly_toggle="no"]</code> or
                <code>[eduschedule_packages default_cycle="yearly"]</code>.
            </p>
        </div>

        <div class="es-card">
            <h3>Stripe Payments</h3>
            <label class="es-checkbox-row">
                <input type="checkbox" name="stripe_enabled" value="1" <?php checked( ! empty( $settings['stripe_enabled'] ) ); ?> />
                <span>Enable Stripe Checkout</span>
            </label>

            <div class="es-field" style="margin-top:10px">
                <label class="es-label">Mode</label>
                <select name="stripe_mode">
                    <option value="test" <?php selected( $settings['stripe_mode'], 'test' ); ?>>Test</option>
                    <option value="live" <?php selected( $settings['stripe_mode'], 'live' ); ?>>Live</option>
                </select>
            </div>

            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Test Publishable Key</label>
                    <input type="text" name="stripe_test_pub_key" placeholder="pk_test_..."
                           value="<?php echo esc_attr( $settings['stripe_test_pub_key'] ); ?>" />
                </div>
                <div class="es-field">
                    <label class="es-label">Test Secret Key</label>
                    <input type="password" name="stripe_test_secret" placeholder="sk_test_..."
                           value="<?php echo esc_attr( $settings['stripe_test_secret'] ); ?>" autocomplete="new-password" />
                </div>
            </div>

            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Live Publishable Key</label>
                    <input type="text" name="stripe_live_pub_key" placeholder="pk_live_..."
                           value="<?php echo esc_attr( $settings['stripe_live_pub_key'] ); ?>" />
                </div>
                <div class="es-field">
                    <label class="es-label">Live Secret Key</label>
                    <input type="password" name="stripe_live_secret" placeholder="sk_live_..."
                           value="<?php echo esc_attr( $settings['stripe_live_secret'] ); ?>" autocomplete="new-password" />
                </div>
            </div>

            <div class="es-field">
                <label class="es-label">Webhook Signing Secret (optional)</label>
                <input type="password" name="stripe_webhook_secret" placeholder="whsec_..."
                       value="<?php echo esc_attr( $settings['stripe_webhook_secret'] ); ?>" autocomplete="new-password" />
                <span class="es-helper">
                    Endpoint URL: <code><?php echo esc_html( add_query_arg( 'es_stripe_webhook', '1', home_url( '/' ) ) ); ?></code><br>
                    Listen for <code>checkout.session.completed</code>.
                </span>
            </div>
        </div>

        <div class="es-settings-section">
            <h3>Email Notifications</h3>
            <p class="es-helper" style="margin-top:-6px">
                If emails aren't arriving, the most common cause is a missing or invalid <strong>From</strong> address (many hosts reject mail with no proper sender). Set a From address on your own domain. For reliable delivery, also install an SMTP plugin (e.g. WP Mail SMTP).
            </p>

            <div class="es-field">
                <label class="es-label">From Name</label>
                <input type="text" name="from_name" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                       value="<?php echo esc_attr( $settings['from_name'] ); ?>" />
            </div>

            <div class="es-field">
                <label class="es-label">From Email</label>
                <input type="email" name="from_email" placeholder="no-reply@yourdomain.com"
                       value="<?php echo esc_attr( $settings['from_email'] ); ?>" />
                <span class="es-helper">Leave blank to auto-use <code><?php echo esc_html( ES_Mailer::default_from_email() ); ?></code>.</span>
            </div>

            <div class="es-field">
                <label class="es-label">Reply-To (optional)</label>
                <input type="email" name="reply_to" placeholder="hello@yourdomain.com"
                       value="<?php echo esc_attr( $settings['reply_to'] ); ?>" />
            </div>

            <div class="es-field">
                <label class="es-checkbox-row">
                    <input type="checkbox" name="notify_admin" value="1" <?php checked( ! empty( $settings['notify_admin'] ) ); ?> />
                    <span>Send the admin a copy when a booking is made</span>
                </label>
            </div>

            <div class="es-field">
                <label class="es-label">Admin Notification Email (optional)</label>
                <input type="email" name="admin_notify_email" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
                       value="<?php echo esc_attr( $settings['admin_notify_email'] ); ?>" />
                <span class="es-helper">Leave blank to use the site admin email.</span>
            </div>
        </div>

        <div class="es-settings-section">
            <h3>SMTP (Outgoing Mail Server)</h3>
            <p class="es-helper" style="margin-top:-6px">
                Many hosts can't deliver mail through PHP's default <code>mail()</code>, so providers like Gmail silently drop it. Enabling SMTP authenticates every message through a real mail server so it actually arrives. For Gmail, use host <code>smtp.gmail.com</code>, port <code>587</code>, TLS, your full Gmail address as the username, and a Google <strong>App Password</strong> (not your normal password).
            </p>

            <label class="es-checkbox-row">
                <input type="checkbox" name="smtp_enabled" value="1" <?php checked( ! empty( $settings['smtp_enabled'] ) ); ?> />
                <span>Send all plugin email through this SMTP server</span>
            </label>

            <div class="es-modal-row" style="margin-top:10px">
                <div class="es-field">
                    <label class="es-label">SMTP Host</label>
                    <input type="text" name="smtp_host" placeholder="smtp.gmail.com"
                           value="<?php echo esc_attr( $settings['smtp_host'] ); ?>" />
                </div>
                <div class="es-field">
                    <label class="es-label">Port</label>
                    <input type="number" name="smtp_port" min="1" max="65535" placeholder="587"
                           value="<?php echo esc_attr( $settings['smtp_port'] ); ?>" />
                    <span class="es-helper">587 for TLS, 465 for SSL.</span>
                </div>
            </div>

            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Encryption</label>
                    <select name="smtp_encryption">
                        <option value="tls"  <?php selected( $settings['smtp_encryption'], 'tls' ); ?>>TLS (recommended)</option>
                        <option value="ssl"  <?php selected( $settings['smtp_encryption'], 'ssl' ); ?>>SSL</option>
                        <option value="none" <?php selected( $settings['smtp_encryption'], 'none' ); ?>>None</option>
                    </select>
                </div>
                <div class="es-field" style="display:flex;align-items:flex-end;">
                    <label class="es-checkbox-row" style="margin-bottom:8px">
                        <input type="checkbox" name="smtp_auth" value="1" <?php checked( ! empty( $settings['smtp_auth'] ) ); ?> />
                        <span>This server requires authentication</span>
                    </label>
                </div>
            </div>

            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">SMTP Username</label>
                    <input type="text" name="smtp_username" placeholder="you@gmail.com"
                           value="<?php echo esc_attr( $settings['smtp_username'] ); ?>" autocomplete="off" />
                </div>
                <div class="es-field">
                    <label class="es-label">SMTP Password / App Password</label>
                    <input type="password" name="smtp_password" placeholder="<?php echo ! empty( $settings['smtp_password'] ) ? '•••••••• (saved — leave blank to keep)' : 'App password'; ?>"
                           value="" autocomplete="new-password" />
                    <span class="es-helper">For Gmail, generate an App Password under your Google Account → Security. Leave blank to keep the saved password.</span>
                </div>
            </div>

            <div class="es-field" style="margin-top:6px">
                <button type="button" class="es-btn es-btn-ghost" id="es-send-test-email">
                    <span class="dashicons dashicons-email-alt"></span> Send Test Email
                </button>
                <span class="es-helper" id="es-test-email-msg" style="margin-left:10px"></span>
                <span class="es-helper" style="display:block;margin-top:6px">Save your settings first, then send a test email to confirm delivery.</span>
            </div>
        </div>

        <p style="margin-top:16px"><button type="submit" class="es-btn es-btn-primary"><span class="dashicons dashicons-saved"></span> Save Settings</button></p>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:14px">
        <?php wp_nonce_field( 'es_create_pages' ); ?>
        <input type="hidden" name="action" value="es_create_pages" />
        <button type="submit" class="es-btn es-btn-ghost"><span class="dashicons dashicons-plus"></span> Auto-Create Frontend Pages</button>
        <span class="es-helper">Creates Login, Register, and Dashboard pages with the right shortcodes — only if they don't already exist.</span>
    </form>



    <div class="es-card" style="margin-top:18px;border-color:#fecaca;background:#fff7f7">
        <h3 style="color:#991b1b">Fresh Start / Reset Student Data</h3>
        <p class="es-helper" style="color:#7f1d1d">
            This clears only EduSchedule operational data for 1:1 and Group workflows: bookings, schedule slots, payments, lead/package selections, groups, attendance, session files, and videos. It also removes EduSchedule student assignment meta from existing users.
            <strong>It does not delete WordPress users and it does not delete package master data.</strong>
        </p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('This will clear EduSchedule 1:1 and Group operational data. Users and package master data will remain. Continue?');">
            <?php wp_nonce_field( 'es_reset_student_data' ); ?>
            <input type="hidden" name="action" value="es_reset_student_data" />
            <div class="es-field">
                <label class="es-label">Type RESET to confirm</label>
                <input type="text" name="es_reset_confirm" placeholder="RESET" autocomplete="off" style="max-width:220px" />
            </div>
            <button type="submit" class="es-btn" style="background:#dc2626;color:#fff;border-color:#dc2626">
                <span class="dashicons dashicons-trash"></span> Reset 1:1 &amp; Group Data
            </button>
        </form>
    </div>

    <div class="es-card" style="margin-top:18px;background:#0f172a">
        <h3 style="color:#fff">Shortcodes Reference</h3>
        <table style="width:100%;font-size:13px;color:#cbd5e1">
            <tr><td style="padding:6px 0;width:240px"><code>[eduschedule_login]</code></td><td>Pink login form</td></tr>
            <tr><td style="padding:6px 0"><code>[eduschedule_register]</code></td><td>Pink registration form</td></tr>
            <tr><td style="padding:6px 0"><code>[eduschedule_dashboard]</code></td><td>User's bookings + slot booking calendar</td></tr>
            <tr><td style="padding:6px 0"><code>[eduschedule_reset]</code><br><code>[es_reset_password_form]</code><br><code>[ivy_reset_password]</code></td><td>Frontend forgot/reset password form</td></tr>
            <tr><td style="padding:6px 0;vertical-align:top"><code>[eduschedule_packages]</code></td>
                <td>
                    Public packages / pricing page. When Stripe is enabled, any visitor can buy a package directly via Stripe Checkout (hosted page). Attributes:<br>
                    <code>yearly_toggle="yes|no"</code> · <code>default_cycle="monthly|yearly"</code> · <code>recommended="1|2|3"</code>
                </td></tr>
        </table>
    </div>
</div>

<script>
(function ($) {
    $(document).on('click', '#es-send-test-email', function () {
        var $btn = $(this);
        var $msg = $('#es-test-email-msg');
        $btn.prop('disabled', true);
        $msg.css('color', '').text('Sending…');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_send_test_email',
            nonce: ES_ADMIN.nonce
        }).done(function (res) {
            if (res && res.success) {
                $msg.css('color', '#16a34a').text((res.data && res.data.message) || 'Sent.');
            } else {
                $msg.css('color', '#dc2626').text((res && res.data && res.data.message) || 'Send failed.');
            }
        }).fail(function () {
            $msg.css('color', '#dc2626').text('Request failed. Please try again.');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });
})(jQuery);
</script>
