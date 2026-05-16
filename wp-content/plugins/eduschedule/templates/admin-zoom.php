<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="es-admin">
    <div class="es-page-head">
        <div>
            <h1>Zoom Integration</h1>
            <p class="es-page-sub">Auto-create Zoom meetings when a customer books. Each booking gets its own unique meeting link.</p>
        </div>
    </div>

    <?php if ( ! empty( $_GET['saved'] ) ) : ?>
        <div class="es-notice es-notice-success">Zoom settings saved.</div>
    <?php endif; ?>
    <?php if ( isset( $_GET['tested'] ) ) :
        $ok = $_GET['tested'] === 'ok';
        $msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
    ?>
        <div class="es-notice <?php echo $ok ? 'es-notice-success' : 'es-notice-danger'; ?>"><strong><?php echo $ok ? '✓ Connection successful' : '✗ Connection failed'; ?></strong><?php if ( $msg ) echo ' — ' . esc_html( $msg ); ?></div>
    <?php endif; ?>

    <div class="es-status-bar">
        <?php if ( $is_configured ) : ?>
            <span class="es-pill es-pill-success">● Configured</span>
            <?php if ( ! empty( $zoom['last_test_ok'] ) ) : ?>
                <span class="es-status-text">Last successful test: <?php echo esc_html( human_time_diff( $zoom['last_test_ok'], current_time( 'timestamp' ) ) ); ?> ago</span>
            <?php endif; ?>
        <?php else : ?>
            <span class="es-pill es-pill-warning">● Not configured</span>
            <span class="es-status-text">Add credentials below to enable.</span>
        <?php endif; ?>
        <?php if ( ! empty( $zoom['last_error'] ) ) : ?>
            <div style="width:100%;margin-top:8px"><span class="es-pill es-pill-danger">Last error</span> <code><?php echo esc_html( $zoom['last_error'] ); ?></code></div>
        <?php endif; ?>
    </div>

    <div class="es-row-2-col">
        <div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'es_save_zoom' ); ?>
                <input type="hidden" name="action" value="es_save_zoom" />

                <div class="es-card">
                    <h3>Credentials</h3>
                    <div class="es-field">
                        <label class="es-checkbox-row"><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $zoom['enabled'] ) ); ?> /> <span>Auto-create Zoom meeting on each booking</span></label>
                    </div>
                    <div class="es-field">
                        <label class="es-label">Account ID *</label>
                        <input type="text" name="account_id" value="<?php echo esc_attr( $zoom['account_id'] ); ?>" placeholder="abc123XYZ..." autocomplete="off" />
                    </div>
                    <div class="es-field">
                        <label class="es-label">Client ID *</label>
                        <input type="text" name="client_id" value="<?php echo esc_attr( $zoom['client_id'] ); ?>" placeholder="Client ID..." autocomplete="off" />
                    </div>
                    <div class="es-field">
                        <label class="es-label">Client Secret *</label>
                        <input type="password" name="client_secret" value="" placeholder="<?php echo ! empty( $zoom['client_secret'] ) ? '•••••••• (leave blank to keep)' : 'Client Secret...'; ?>" autocomplete="new-password" />
                    </div>
                    <div class="es-field">
                        <label class="es-label">Host Email</label>
                        <input type="email" name="host_email" value="<?php echo esc_attr( $zoom['host_email'] ); ?>" placeholder="host@company.com" />
                        <span class="es-helper">Email of the Zoom user who will host meetings (must exist in your Zoom account).</span>
                    </div>
                </div>

                <div class="es-card">
                    <h3>Meeting Defaults</h3>
                    <label class="es-checkbox-row"><input type="checkbox" name="waiting_room" value="1" <?php checked( ! empty( $zoom['waiting_room'] ) ); ?> /> <span>Enable waiting room (recommended)</span></label>
                    <label class="es-checkbox-row"><input type="checkbox" name="auto_record" value="1" <?php checked( ! empty( $zoom['auto_record'] ) ); ?> /> <span>Auto-record to cloud (Zoom Pro+ required)</span></label>
                </div>

                <p style="margin-top:16px;display:flex;gap:10px">
                    <button type="submit" class="es-btn es-btn-primary"><span class="dashicons dashicons-saved"></span> Save Zoom Settings</button>
                </p>
            </form>

            <?php if ( $is_configured ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:14px">
                    <?php wp_nonce_field( 'es_test_zoom' ); ?>
                    <input type="hidden" name="action" value="es_test_zoom" />
                    <button type="submit" class="es-btn es-btn-ghost"><span class="dashicons dashicons-controls-play"></span> Test Connection</button>
                </form>
            <?php endif; ?>
        </div>

        <div>
            <div class="es-card es-help-card">
                <h3>Setup Guide</h3>
                <ol>
                    <li>Go to <a href="https://marketplace.zoom.us/develop/create" target="_blank" rel="noopener">marketplace.zoom.us → Develop → Build App</a></li>
                    <li>Choose <strong>Server-to-Server OAuth</strong></li>
                    <li>Give the app a name (e.g. "EduSchedule")</li>
                    <li>From <strong>App Credentials</strong>, copy: Account ID, Client ID, Client Secret</li>
                    <li>Fill <strong>Information</strong> page (required)</li>
                    <li>On <strong>Scopes</strong>, add:
                        <ul>
                            <li><code>meeting:write:meeting:admin</code></li>
                            <li><code>meeting:write:meeting</code></li>
                            <li><code>meeting:delete:meeting:admin</code></li>
                            <li><code>user:read:user:admin</code></li>
                        </ul>
                    </li>
                    <li>Click <strong>Activate your app</strong></li>
                    <li>Paste credentials → Save → Test Connection</li>
                </ol>
            </div>
        </div>
    </div>
</div>
