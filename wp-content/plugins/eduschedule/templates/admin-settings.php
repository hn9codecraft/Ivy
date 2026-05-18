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
            <p class="es-helper">Pages that contain shortcodes for login / register / dashboard. Click "Auto-Create Pages" below if you haven't made them yet.</p>
            <?php
            $pages = get_pages( array( 'sort_column' => 'post_title', 'number' => 100 ) );
            $page_field = function( $key, $label ) use ( $settings, $pages ) {
                echo '<div class="es-field"><label class="es-label">' . esc_html( $label ) . '</label><select name="' . esc_attr( $key ) . '">';
                echo '<option value="0">— None —</option>';
                foreach ( $pages as $p ) {
                    echo '<option value="' . (int) $p->ID . '" ' . selected( (int) $settings[ $key ], (int) $p->ID, false ) . '>' . esc_html( $p->post_title ) . '</option>';
                }
                echo '</select></div>';
            };
            $page_field( 'login_page_id',    'Login Page' );
            $page_field( 'register_page_id', 'Register Page' );
            $page_field( 'dashboard_page_id','Dashboard Page' );
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
                <span class="es-helper">
                    Single global setting. Admins only enter the <strong>monthly price</strong> per package — the frontend toggle automatically shows yearly = <code>monthly × 12 − this discount %</code>. Yearly is charged as a <strong>one-time payment</strong> covering 12 months.
                </span>
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

        <p style="margin-top:16px"><button type="submit" class="es-btn es-btn-primary"><span class="dashicons dashicons-saved"></span> Save Settings</button></p>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:14px">
        <?php wp_nonce_field( 'es_create_pages' ); ?>
        <input type="hidden" name="action" value="es_create_pages" />
        <button type="submit" class="es-btn es-btn-ghost"><span class="dashicons dashicons-plus"></span> Auto-Create Frontend Pages</button>
        <span class="es-helper">Creates Login, Register, and Dashboard pages with the right shortcodes — only if they don't already exist.</span>
    </form>

    <div class="es-card" style="margin-top:18px;background:#0f172a">
        <h3 style="color:#fff">Shortcodes Reference</h3>
        <table style="width:100%;font-size:13px;color:#cbd5e1">
            <tr><td style="padding:6px 0;width:240px"><code>[eduschedule_login]</code></td><td>Pink login form</td></tr>
            <tr><td style="padding:6px 0"><code>[eduschedule_register]</code></td><td>Pink registration form</td></tr>
            <tr><td style="padding:6px 0"><code>[eduschedule_dashboard]</code></td><td>User's bookings + slot booking calendar</td></tr>
            <tr><td style="padding:6px 0;vertical-align:top"><code>[eduschedule_packages]</code></td>
                <td>
                    Public packages / pricing page. When Stripe is enabled, any visitor can buy a package directly via Stripe Checkout (hosted page). Attributes:<br>
                    <code>yearly_toggle="yes|no"</code> · <code>default_cycle="monthly|yearly"</code> · <code>recommended="1|2|3"</code>
                </td></tr>
        </table>
    </div>
</div>
