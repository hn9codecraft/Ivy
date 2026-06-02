<?php
/**
 * Custom forgot-password / reset-password views (front-end, no wp-login).
 *
 * Vars in scope (set by ES_Shortcodes::login):
 *   $rp_mode   'lostpassword' | 'rp'
 *   $rp_key    reset key (rp mode)
 *   $rp_login  user_login (rp mode)
 *   $rp_valid  bool — whether the key/login validated (rp mode)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$s        = ES_Helpers::settings();
$login_id = (int) ( $s['login_page_id'] ?? 0 );
// Prefer returning to the SAME page the reset flow was opened from (passed in
// as $rp_back_url); fall back to the configured login page, then the home page.
$login_url = isset( $rp_back_url ) && $rp_back_url
    ? $rp_back_url
    : ( $login_id ? get_permalink( $login_id ) : home_url( '/' ) );

$rp_notice = isset( $rp_notice ) ? (string) $rp_notice : '';
$rp_error  = isset( $rp_error )  ? (string) $rp_error  : '';
?>
<div class="es-fe es-auth-page">
    <div class="es-auth-bg"></div>
    <div class="es-auth-card">

        <?php if ( $rp_notice ) : ?>
            <div class="es-fe-msg es-msg-success" style="display:block;margin-bottom:16px;"><?php echo esc_html( $rp_notice ); ?></div>
        <?php endif; ?>
        <?php if ( $rp_error ) : ?>
            <div class="es-fe-msg es-msg-error" style="display:block;margin-bottom:16px;"><?php echo esc_html( $rp_error ); ?></div>
        <?php endif; ?>

        <?php if ( $rp_mode === 'lostpassword' ) : ?>
            <!-- ===== STEP 1: Request a reset link ===== -->
            <div class="es-auth-eyebrow">ACCESS PORTAL</div>
            <h1 class="es-auth-title">reset password</h1>
            <div class="es-auth-divider"></div>

            <form id="es-lostpw-form" method="post" autocomplete="on" novalidate>
                <?php wp_nonce_field( 'es_frontend_reset', 'es_reset_nonce' ); ?>
                <p class="es-fe-help" style="font-size:13px;color:#64748b;margin:0 0 16px;">
                    Enter the email address for your account and we'll send you a link to reset your password.
                </p>
                <div class="es-fe-field">
                    <label class="es-fe-label">EMAIL ADDRESS</label>
                    <input type="text" name="email" required placeholder="you@example.com" autocomplete="username" />
                </div>

                <button type="submit" name="es_lost_password_submit" class="es-fe-btn es-fe-btn-primary">Send reset link →</button>

                <div class="es-fe-msg" id="es-lostpw-msg" style="display:none"></div>
            </form>

            <div class="es-fe-foot">
                <a href="<?php echo esc_url( $login_url ); ?>">← Back to login</a>
            </div>

        <?php elseif ( $rp_mode === 'rp' && ! $rp_valid ) : ?>
            <!-- ===== Invalid / expired link ===== -->
            <div class="es-auth-eyebrow">ACCESS PORTAL</div>
            <h1 class="es-auth-title">link expired</h1>
            <div class="es-auth-divider"></div>
            <p class="es-fe-help" style="font-size:14px;color:#64748b;margin:0 0 18px;">
                This password reset link has expired or is invalid. Please request a new one.
            </p>
            <div class="es-fe-foot">
                <a href="<?php echo esc_url( add_query_arg( 'es_action', 'lostpassword', $login_url ) ); ?>">Request a new link</a>
                &nbsp;·&nbsp;
                <a href="<?php echo esc_url( $login_url ); ?>">Back to login</a>
            </div>

        <?php else : ?>
            <!-- ===== STEP 2: Set a new password ===== -->
            <div class="es-auth-eyebrow">ACCESS PORTAL</div>
            <h1 class="es-auth-title">new password</h1>
            <div class="es-auth-divider"></div>

            <form id="es-resetpw-form" method="post" autocomplete="off" novalidate>
                <?php wp_nonce_field( 'es_frontend_reset', 'es_reset_nonce' ); ?>
                <input type="hidden" name="key"   value="<?php echo esc_attr( $rp_key ); ?>" />
                <input type="hidden" name="login" value="<?php echo esc_attr( $rp_login ); ?>" />

                <div class="es-fe-field">
                    <label class="es-fe-label">NEW PASSWORD</label>
                    <div class="es-fe-pw-wrap">
                        <input type="password" name="password" required placeholder="••••••••" autocomplete="new-password" />
                        <button type="button" class="es-fe-eye es-fe-btn-link btn-link" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                    </div>
                </div>
                <div class="es-fe-field">
                    <label class="es-fe-label">CONFIRM PASSWORD</label>
                    <div class="es-fe-pw-wrap">
                        <input type="password" name="password_confirm" required placeholder="••••••••" autocomplete="new-password" />
                        <button type="button" class="es-fe-eye es-fe-btn-link btn-link" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                    </div>
                </div>

                <button type="submit" name="es_reset_password_submit" class="es-fe-btn es-fe-btn-primary">Set new password →</button>

                <div class="es-fe-msg" id="es-resetpw-msg" style="display:none"></div>
            </form>

            <div class="es-fe-foot">
                <a href="<?php echo esc_url( $login_url ); ?>">← Back to login</a>
            </div>
        <?php endif; ?>

    </div>
</div>
