<?php
/**
 * Combined Login + Register template (toggle on same page).
 * Used by [eduschedule_auth] shortcode.
 *
 * Available variables:
 *   $atts          (array)  shortcode atts
 *   $register_open (bool)
 *   $countries     (array)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$default_view = ( isset( $atts['default'] ) && $atts['default'] === 'register' && $register_open ) ? 'register' : 'login';
?>
<div class="es-fe es-auth-page es-auth-combined" data-default="<?php echo esc_attr( $default_view ); ?>">
    <div class="es-auth-bg"></div>

    <!-- ============= LOGIN VIEW ============= -->
    <div class="es-auth-card es-auth-view es-auth-view-login" <?php echo $default_view === 'login' ? '' : 'style="display:none"'; ?>>
        <div class="es-auth-eyebrow">ACCESS PORTAL</div>
        <h1 class="es-auth-title">Login</h1>
        <div class="es-auth-divider"></div>

        <form id="es-login-form" autocomplete="on" novalidate>
            <?php wp_nonce_field( 'es_login_nonce', 'es_login_nonce_field' ); ?>

            <div class="es-fe-field">
                <label class="es-fe-label">EMAIL ADDRESS</label>
                <input type="email" name="email" required placeholder="you@example.com" autocomplete="username" />
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">PASSWORD</label>
                <div class="es-fe-pw-wrap">
                    <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password" />
                    <button type="button" class="es-fe-eye es-fe-btn-link btn-link" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                </div>
                <?php
                // Forgot password → reload THIS page with the reset flag. The
                // auth shortcode on this page renders the custom reset view.
                $es_self_url   = remove_query_arg( array( 'es_action', 'key', 'login' ) );
                $es_forgot_url = add_query_arg( 'es_action', 'lostpassword', $es_self_url );
                ?>
                <a href="<?php echo esc_url( $es_forgot_url ); ?>" class="es-fe-forgot">Forgot password?</a>
            </div>

            <label class="es-fe-remember">
                <input type="checkbox" name="remember" value="1" /> <span>Remember me</span>
            </label>

            <button type="submit" class="es-fe-btn es-fe-btn-primary">Log in →</button>

            <div class="es-fe-msg" id="es-login-msg" style="display:none"></div>
        </form>

        <?php if ( $register_open ) : ?>
            <div class="es-fe-foot">
                New here? <a href="#" class="es-auth-toggle" data-target="register"><strong>Create an account</strong></a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ============= REGISTER VIEW ============= -->
    <?php if ( $register_open ) : ?>
    <div class="es-auth-card es-auth-card-wide es-auth-view es-auth-view-register" <?php echo $default_view === 'register' ? '' : 'style="display:none"'; ?>>
        <div class="es-auth-eyebrow">JOIN US</div>
        <h1 class="es-auth-title">Create Account</h1>
        <div class="es-auth-divider"></div>

        <form id="es-register-form" autocomplete="on" novalidate>
            <?php wp_nonce_field( 'es_register_nonce', 'es_register_nonce_field' ); ?>

            <div class="es-fe-row">
                <div class="es-fe-field">
                    <label class="es-fe-label">FIRST NAME</label>
                    <input type="text" name="first_name" required placeholder="First name" />
                </div>
                <div class="es-fe-field">
                    <label class="es-fe-label">LAST NAME</label>
                    <input type="text" name="last_name" required placeholder="Last name" />
                </div>
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">EMAIL ADDRESS</label>
                <input type="email" name="email" required placeholder="you@example.com" autocomplete="username" />
            </div>

            <div class="es-fe-row">
                <div class="es-fe-field">
                    <label class="es-fe-label">PHONE NUMBER</label>
                    <input type="tel" name="phone" placeholder="+1 (555) 000-0000" />
                </div>
                <div class="es-fe-field">
                    <label class="es-fe-label">COUNTRY</label>
                    <select name="country" required>
                        <option value="">— Select —</option>
                        <?php foreach ( $countries as $code => $info ) : ?>
                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $info['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">PASSWORD</label>
                <div class="es-fe-pw-wrap">
                    <input type="password" name="password" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password" />
                    <button type="button" class="es-fe-eye es-fe-btn-link btn-link" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                </div>
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">CONFIRM PASSWORD</label>
                <div class="es-fe-pw-wrap">
                    <input type="password" name="confirm_password" required minlength="8" placeholder="Re-enter your password" autocomplete="new-password" />
                    <button type="button" class="es-fe-eye es-fe-btn-link btn-link" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                </div>
            </div>

            <button type="submit" class="es-fe-btn es-fe-btn-primary">Create Account →</button>

            <div class="es-fe-msg" id="es-register-msg" style="display:none"></div>
        </form>

        <div class="es-fe-foot">
            Already have an account? <a href="#" class="es-auth-toggle" data-target="login"><strong>Login here</strong></a>
        </div>
    </div>
    <?php endif; ?>
</div>
