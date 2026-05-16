<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="es-fe es-auth-page">
    <div class="es-auth-bg"></div>
    <div class="es-auth-card">
        <div class="es-auth-eyebrow">ACCESS PORTAL</div>
        <h1 class="es-auth-title">login</h1>
        <div class="es-auth-divider"></div>

        <form id="es-login-form" autocomplete="on" novalidate>
            <div class="es-fe-field">
                <label class="es-fe-label">EMAIL ADDRESS</label>
                <input type="email" name="email" required placeholder="you@example.com" autocomplete="username" />
            </div>
            <div class="es-fe-field">
                <label class="es-fe-label">PASSWORD</label>
                <div class="es-fe-pw-wrap">
                    <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password" />
                    <button type="button" class="es-fe-eye" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                </div>
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="es-fe-forgot">Forgot password?</a>
            </div>

            <button type="submit" class="es-fe-btn es-fe-btn-primary">Log in →</button>

            <div class="es-fe-msg" id="es-login-msg" style="display:none"></div>
        </form>

        <div class="es-fe-foot">
            <?php
            $s = ES_Helpers::settings();
            $reg = ! empty( $s['register_page_id'] ) ? get_permalink( $s['register_page_id'] ) : '';
            ?>
            New here? <?php if ( $reg ) : ?><a href="<?php echo esc_url( $reg ); ?>">Create an account</a><?php endif; ?>
        </div>
    </div>
</div>
