<?php
/**
 * Public Package Selection Page
 * Shortcode: [eduschedule_packages]
 *
 * URL params (for personalized link sent from admin):
 *   ?user_id=X&token=XXXXX
 * URL params after selection (thank you page):
 *   ?selected=1&pkg=Y
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$user_id      = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;
$token        = isset( $_GET['token'] )   ? sanitize_text_field( $_GET['token'] ) : '';
$is_selected  = ! empty( $_GET['selected'] );
$selected_pkg = isset( $_GET['pkg'] ) ? (int) $_GET['pkg'] : 0;

$valid_link   = false;
$student_data = null;
$packages     = array();

// ── THANK YOU MODE ──
if ( $is_selected && $selected_pkg ) {
    $pkg = ES_Packages::get( $selected_pkg );
    ?>
    <div class="es-public-packages es-thank-you">
        <div class="es-thank-you-card">
            <div class="es-thank-icon">✓</div>
            <h1>Thank You!</h1>
            <p class="es-thank-sub">Your package selection has been confirmed.</p>

            <?php if ( $pkg ) : ?>
                <div class="es-thank-pkg">
                    <div class="es-thank-pkg-name"><?php echo esc_html( $pkg->package_name ); ?></div>
                    <?php if ( $pkg->sub_heading ) : ?>
                        <div class="es-thank-pkg-sub"><?php echo esc_html( $pkg->sub_heading ); ?></div>
                    <?php endif; ?>
                    <div class="es-thank-pkg-price">
                        ₹<?php echo number_format( $pkg->price, 0 ); ?>
                        <?php if ( $pkg->tagline ) : ?>
                            <span> / <?php echo esc_html( $pkg->tagline ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <p class="es-thank-msg">
                We've sent a confirmation email to you. Our team will contact you shortly to schedule your first session.
            </p>
            <p class="es-thank-meta">
                If you have any questions, please email us at
                <a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"><?php echo esc_html( get_option( 'admin_email' ) ); ?></a>
            </p>
        </div>
    </div>

    <style>
    .es-thank-you{max-width:600px;margin:60px auto;padding:0 20px}
    .es-thank-you-card{background:#fff;border:2px solid #e5e7eb;border-radius:16px;padding:48px 32px;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,0.06)}
    .es-thank-icon{width:72px;height:72px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;margin:0 auto 20px}
    .es-thank-you-card h1{font-size:32px;font-weight:700;color:#1e1e2e;margin:0 0 8px}
    .es-thank-sub{font-size:16px;color:#6b7280;margin:0 0 24px}
    .es-thank-pkg{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin:24px 0}
    .es-thank-pkg-name{font-size:20px;font-weight:600;color:#1e1e2e;margin-bottom:4px}
    .es-thank-pkg-sub{font-size:13px;color:#6b7280;margin-bottom:8px}
    .es-thank-pkg-price{font-size:28px;font-weight:700;color:#6366f1}
    .es-thank-pkg-price span{font-size:14px;font-weight:400;color:#9ca3af}
    .es-thank-msg{font-size:15px;color:#4b5563;line-height:1.6;margin:20px 0 8px}
    .es-thank-meta{font-size:13px;color:#9ca3af;margin:0}
    .es-thank-meta a{color:#6366f1;text-decoration:none}
    </style>
    <?php
    return;
}

// ── PACKAGE SELECTION MODE ──
if ( $user_id && $token && ES_Packages::validate_token( $user_id, $token ) ) {
    $valid_link = true;
    $user = get_userdata( $user_id );
    if ( $user ) {
        $student_data = array(
            'id'    => $user->ID,
            'name'  => $user->display_name,
            'email' => $user->user_email,
        );
        // Only show staged packages
        $staged_ids = ES_Packages::get_staged_packages( $user_id );
        if ( ! empty( $staged_ids ) ) {
            foreach ( $staged_ids as $sid ) {
                $p = ES_Packages::get( $sid );
                if ( $p && $p->is_active ) {
                    $packages[] = $p;
                }
            }
        } else {
            // Fallback: show all active
            $packages = ES_Packages::get_all( true );
        }
    }
} else {
    // Public mode (no valid link) — show all active packages
    $packages = ES_Packages::get_all( true );
}
?>

<div class="es-public-packages">
    <?php if ( $valid_link && $student_data ) : ?>
        <div class="es-pkg-header">
            <h1>Select Your Package</h1>
            <p class="es-pkg-subtitle">
                Hello <strong><?php echo esc_html( $student_data['name'] ); ?></strong>,
                please select the package that works best for you.
            </p>
        </div>
    <?php else : ?>
        <div class="es-pkg-header">
            <h1>Our Packages</h1>
            <p class="es-pkg-subtitle">Choose the perfect coaching package for your goals</p>
        </div>
    <?php endif; ?>

    <?php if ( empty( $packages ) ) : ?>
        <div class="es-pkg-empty">
            <p>No packages available at the moment. Please check back later.</p>
        </div>
    <?php else : ?>
        <div class="es-pkg-grid">
            <?php foreach ( $packages as $pkg ) : ?>
                <div class="es-pkg-item" data-package-id="<?php echo (int) $pkg->id; ?>">
                    <div class="es-pkg-item-header">
                        <h2 class="es-pkg-item-name"><?php echo esc_html( $pkg->package_name ); ?></h2>
                        <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                            <p class="es-pkg-item-sub"><?php echo esc_html( $pkg->sub_heading ); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="es-pkg-item-price">
                        <span class="es-pkg-currency">₹</span><?php echo number_format( $pkg->price, 0 ); ?>
                        <?php if ( ! empty( $pkg->tagline ) ) : ?>
                            <span class="es-pkg-period">/ <?php echo esc_html( $pkg->tagline ); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ( ! empty( $pkg->hours ) ) : ?>
                        <div class="es-pkg-item-hours">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <path d="M12 6v6l4 2" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php echo (int) $pkg->hours; ?> Hours
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $pkg->description ) ) : ?>
                        <div class="es-pkg-item-desc">
                            <?php
                            $lines = explode( "\n", $pkg->description );
                            foreach ( $lines as $line ) {
                                $line = trim( $line );
                                if ( empty( $line ) ) continue;
                                echo '<div class="es-pkg-feature">';
                                if ( strpos( $line, '•' ) === 0 || strpos( $line, '-' ) === 0 ) {
                                    echo '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                                    echo '<span>' . esc_html( ltrim( $line, '•- ' ) ) . '</span>';
                                } else {
                                    echo '<span>' . esc_html( $line ) . '</span>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $valid_link && $student_data ) : ?>
                        <button type="button" class="es-pkg-select-btn"
                                data-package-id="<?php echo (int) $pkg->id; ?>"
                                data-user-id="<?php echo (int) $student_data['id']; ?>"
                                data-token="<?php echo esc_attr( $token ); ?>">
                            Select This Package
                        </button>
                    <?php else : ?>
                        <button type="button" class="es-pkg-contact-btn">
                            Contact Us
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.es-public-packages{max-width:1200px;margin:40px auto;padding:0 20px}
.es-pkg-header{text-align:center;margin-bottom:48px}
.es-pkg-header h1{font-size:36px;font-weight:700;color:#1e1e2e;margin:0 0 12px}
.es-pkg-subtitle{font-size:18px;color:#666;margin:0}
.es-pkg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:32px}
.es-pkg-item{background:#fff;border:2px solid #e5e7eb;border-radius:16px;padding:32px 24px;transition:all 0.3s;display:flex;flex-direction:column}
.es-pkg-item:hover{border-color:#6366f1;box-shadow:0 12px 32px rgba(99,102,241,0.15);transform:translateY(-4px)}
.es-pkg-item-header{margin-bottom:20px}
.es-pkg-item-name{font-size:24px;font-weight:700;color:#1e1e2e;margin:0 0 8px}
.es-pkg-item-sub{font-size:14px;color:#6b7280;margin:0}
.es-pkg-item-price{font-size:42px;font-weight:700;color:#6366f1;margin-bottom:16px;line-height:1}
.es-pkg-currency{font-size:28px}
.es-pkg-period{font-size:16px;font-weight:400;color:#9ca3af}
.es-pkg-item-hours{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:#eff6ff;color:#2563eb;border-radius:8px;font-size:14px;font-weight:600;margin-bottom:20px;width:fit-content}
.es-pkg-item-desc{flex:1;margin-bottom:24px}
.es-pkg-feature{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;font-size:15px;color:#4b5563;line-height:1.6}
.es-pkg-feature svg{flex-shrink:0;margin-top:2px;color:#6366f1}
.es-pkg-select-btn,.es-pkg-contact-btn{width:100%;padding:14px 24px;background:#6366f1;color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.2s}
.es-pkg-select-btn:hover,.es-pkg-contact-btn:hover{background:#4f46e5;transform:scale(1.02)}
.es-pkg-contact-btn{background:#64748b}
.es-pkg-contact-btn:hover{background:#475569}
.es-pkg-empty{text-align:center;padding:60px 20px;color:#6b7280}
@media (max-width:768px){
    .es-pkg-header h1{font-size:28px}
    .es-pkg-subtitle{font-size:16px}
    .es-pkg-grid{grid-template-columns:1fr;gap:24px}
}
</style>
