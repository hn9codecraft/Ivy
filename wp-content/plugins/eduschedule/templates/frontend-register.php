<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="es-fe es-auth-page">
    <div class="es-auth-bg"></div>
    <div class="es-auth-card es-auth-card-wide">
        <h1 class="es-auth-title">Create Account</h1>
        <div class="es-auth-divider"></div>

        <form id="es-register-form" autocomplete="on" novalidate>
            <div class="es-fe-row">
                <div class="es-fe-field">
                    <label class="es-fe-label">FIRST NAME</label>
                    <input type="text" name="first_name" required placeholder="Elara" />
                </div>
                <div class="es-fe-field">
                    <label class="es-fe-label">LAST NAME</label>
                    <input type="text" name="last_name" required placeholder="Vance" />
                </div>
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">EMAIL ADDRESS</label>
                <input type="email" name="email" required placeholder="e.vance@monolith.systems" autocomplete="username" />
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
                        <?php foreach ( ES_Helpers::countries() as $code => $info ) : ?>
                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $info['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">PASSWORD</label>
                <div class="es-fe-pw-wrap">
                    <input type="password" name="password" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password" />
                    <button type="button" class="es-fe-eye" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                </div>
            </div>

            <div class="es-fe-field">
                <label class="es-fe-label">CONFIRM PASSWORD</label>
                <div class="es-fe-pw-wrap">
                    <input type="password" name="confirm_password" required minlength="8" placeholder="Re-enter your password" autocomplete="new-password" />
                    <button type="button" class="es-fe-eye" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                </div>
            </div>

            <button type="submit" class="es-fe-btn es-fe-btn-primary">Create Account →</button>

            <div class="es-fe-msg" id="es-register-msg" style="display:none"></div>
        </form>

        <div class="es-fe-foot">
            <?php
            $s = ES_Helpers::settings();
            $login = ! empty( $s['login_page_id'] ) ? get_permalink( $s['login_page_id'] ) : '';
            ?>
            Already have an account? <?php if ( $login ) : ?><a href="<?php echo esc_url( $login ); ?>"><strong>Login here</strong></a><?php endif; ?>
        </div>
    </div>
</div>
