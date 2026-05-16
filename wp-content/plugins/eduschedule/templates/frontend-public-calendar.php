<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var array $atts */
/** @var array $allowed */

$tz = ES_Helpers::user_tz();
$is_logged = is_user_logged_in();
$current = $is_logged ? wp_get_current_user() : null;

$s = ES_Helpers::settings();
$login_url = ! empty( $s['login_page_id'] ) ? get_permalink( $s['login_page_id'] ) : wp_login_url( get_permalink() );
$reg_url   = ! empty( $s['register_page_id'] ) ? get_permalink( $s['register_page_id'] ) : '';

$default_platform = $s['default_platform'] ?? 'Zoom';
$platforms = $s['platforms'] ?? array( 'Zoom' );

$allow_multi = isset( $atts['allow_multi'] ) ? ( $atts['allow_multi'] === 'yes' ) : true;
?>
<div class="es-fe es-pcal-app"
     data-types="<?php echo esc_attr( implode( ',', $allowed ) ); ?>"
     data-months-ahead="<?php echo (int) $atts['months_ahead']; ?>"
     data-allow-multi="<?php echo $allow_multi ? '1' : '0'; ?>"
     data-login-url="<?php echo esc_url( $login_url ); ?>"
     data-register-url="<?php echo esc_url( $reg_url ); ?>"
     data-is-logged="<?php echo $is_logged ? '1' : '0'; ?>"
     data-default-platform="<?php echo esc_attr( $default_platform ); ?>"
     data-platforms='<?php echo esc_attr( wp_json_encode( $platforms ) ); ?>'
     data-tz="<?php echo esc_attr( $tz->getName() ); ?>"
     data-today="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"
     <?php if ( $is_logged ) : ?>
       data-user-first="<?php echo esc_attr( $current->first_name ?: '' ); ?>"
       data-user-last="<?php echo esc_attr( $current->last_name ?: '' ); ?>"
       data-user-email="<?php echo esc_attr( $current->user_email ); ?>"
     <?php endif; ?>>

    <h2 class="es-pcal-title"><?php echo esc_html( $atts['title'] ); ?></h2>
    <?php if ( ! empty( $atts['subtitle'] ) ) : ?>
        <p class="es-pcal-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
    <?php endif; ?>

    <?php if ( $atts['show_legend'] === 'yes' ) : ?>
        <div class="es-pcal-legend">
            <?php foreach ( ES_Helpers::slot_types() as $key => $info ) :
                if ( ! in_array( $key, $allowed, true ) ) continue; ?>
                <span class="es-pcal-legend-item">
                    <i class="es-pcal-legend-dot" style="background:<?php echo esc_attr( $info['color'] ); ?>"></i>
                    <?php echo esc_html( $info['label'] ); ?>
                </span>
            <?php endforeach; ?>
            <span class="es-pcal-legend-tz">Times in <strong><?php echo esc_html( $tz->getName() ); ?></strong></span>
        </div>
    <?php endif; ?>

    <div class="es-pcal-step-wrap" data-step="1">
        <div class="es-pcal-loading">Loading…</div>
    </div>
</div>

<!-- Login required modal (shown when guest tries to book) -->
<div class="es-modal" id="es-pcal-login-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card">
        <div class="es-modal-head">
            <h2>Log in to continue</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">
            <p>You need an account to complete your booking. It only takes 30 seconds.</p>
            <div class="es-pcal-login-actions">
                <a href="<?php echo esc_url( $login_url ); ?>" class="es-fe-btn es-fe-btn-primary" id="es-pcal-go-login">Log in</a>
                <?php if ( $reg_url ) : ?>
                    <a href="<?php echo esc_url( $reg_url ); ?>" class="es-fe-btn es-fe-btn-ghost" id="es-pcal-go-register">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
