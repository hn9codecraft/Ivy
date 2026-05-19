<?php
/**
 * Public Package Selection Page
 * Shortcode: [eduschedule_packages]
 *
 * Shortcode attributes ($sc_atts in scope):
 *   yearly_toggle  yes|no                     — show Monthly / Semester toggle
 *   default_cycle  monthly|yearly             — selected by default
 *   semester_label "Pay per Semester"         — overrides yearly toggle label
 *   monthly_label  "Pay Monthly"              — overrides monthly toggle label
 *   period_unit    month|semester             — unit shown next to the price
 *   brand_name     ""                         — header brand name (default: site name)
 *   brand_logo     ""                         — header logo URL (optional)
 *   recommended    "1|2|3"                    — package index (1-based) to mark Teacher's Choice
 *   recommendation_text ""                    — text shown in "My Recommendation" callout
 *
 * URL params:
 *   ?user_id=X&token=XXXXX                    — personalized link
 *   ?es_stripe=success&session_id=cs_xxx      — after Stripe Checkout return (fallback flow)
 *   ?es_stripe=cancel                         — soft notice
 *   ?selected=1&pkg=Y                         — non-Stripe selection thank-you
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$user_id      = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0;
$token        = isset( $_GET['token'] )   ? sanitize_text_field( $_GET['token'] ) : '';
$is_selected  = ! empty( $_GET['selected'] );
$selected_pkg = isset( $_GET['pkg'] ) ? (int) $_GET['pkg'] : 0;

// Stripe outcomes
$stripe_status   = isset( $_GET['es_stripe'] ) ? sanitize_text_field( $_GET['es_stripe'] ) : '';
$stripe_session  = isset( $_GET['session_id'] ) ? sanitize_text_field( $_GET['session_id'] ) : '';

$settings = ES_Helpers::settings();

// Resolve shortcode atts
$atts_default_cycle = isset( $sc_atts['default_cycle'] ) ? strtolower( $sc_atts['default_cycle'] ) : 'monthly';
$atts_yearly_toggle = isset( $sc_atts['yearly_toggle'] ) ? strtolower( $sc_atts['yearly_toggle'] ) : '';
// Decide whether to show the Monthly/Yearly switcher. Rule:
//   • If the shortcode explicitly sets yearly_toggle="yes" or "no", honour it.
//   • Otherwise show it automatically when a yearly discount is configured,
//     or when the admin has ticked "Enable Yearly Billing" in settings.
if ( $atts_yearly_toggle === 'yes' ) {
    $show_toggle = true;
} elseif ( $atts_yearly_toggle === 'no' ) {
    $show_toggle = false;
} else {
    $auto_yearly_discount = (float) ( $settings['yearly_discount'] ?? 0 );
    $show_toggle = ( ! empty( $settings['enable_yearly'] ) ) || ( $auto_yearly_discount > 0 );
}
if ( ! in_array( $atts_default_cycle, array( 'monthly', 'yearly' ), true ) ) $atts_default_cycle = 'monthly';

$monthly_label_txt = ! empty( $sc_atts['monthly_label'] )  ? $sc_atts['monthly_label']  : 'Pay Monthly';
$semester_label_t  = ! empty( $sc_atts['semester_label'] ) ? $sc_atts['semester_label'] : 'Pay per Semester';
$period_unit       = ! empty( $sc_atts['period_unit'] )    ? strtolower( $sc_atts['period_unit'] ) : 'month';

$brand_name = ! empty( $sc_atts['brand_name'] ) ? $sc_atts['brand_name'] : ( $settings['site_name'] ?? get_bloginfo( 'name' ) );
$brand_logo = ! empty( $sc_atts['brand_logo'] ) ? $sc_atts['brand_logo'] : '';
$recommended_idx = isset( $sc_atts['recommended'] ) ? (int) $sc_atts['recommended'] : 2; // default middle card
$recommendation_text = isset( $sc_atts['recommendation_text'] ) ? $sc_atts['recommendation_text'] : '';

$valid_link   = false;
$student_data = null;
$packages     = array();
$show_login   = false;       // when true, render a login prompt instead of cards
$login_reason = '';          // user-facing explanation
$current_user_id = get_current_user_id();
$is_personalised_link = false;  // true only for admin-issued ?user_id&token links with staged packages

/* ───────────────────────────────────────────────────────────────
 *  STRIPE HOSTED-CHECKOUT THANK-YOU (still supported as fallback)
 * ─────────────────────────────────────────────────────────────── */
if ( $stripe_status === 'success' && $stripe_session ) {
    global $wpdb;
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}es_payments WHERE gateway_session_id = %s",
        $stripe_session
    ) );
    $pkg = $row ? ES_Packages::get( $row->package_id ) : null;
    $cur = $row ? $row->currency : 'INR';
    ?>
    <div class="es-public-packages es-thank-you">
        <div class="es-thank-you-card">
            <div class="es-thank-icon">✓</div>
            <h1>Payment Successful</h1>
            <p class="es-thank-sub">Thank you! Your enrollment is now active.</p>
            <?php if ( $pkg && $row ) : ?>
                <div class="es-thank-pkg">
                    <div class="es-thank-pkg-name"><?php echo esc_html( $pkg->package_name ); ?></div>
                    <div class="es-thank-pkg-price">
                        <?php echo esc_html( ES_Helpers::format_price( $row->amount, $cur ) ); ?>
                        <span>· <?php echo esc_html( ucfirst( $row->billing_cycle ) ); ?> (one-time)</span>
                    </div>
                    <?php if ( $row->valid_until ) : ?>
                        <div class="es-thank-meta" style="margin-top:10px">
                            Active until <strong><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $row->valid_until ) ) ); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <p class="es-thank-msg">A receipt has been emailed to you. Our team will reach out shortly.</p>
        </div>
    </div>
    <style>
    .es-thank-you{max-width:600px;margin:60px auto;padding:0 20px}
    .es-thank-you-card{background:#fff;border:2px solid #e5e7eb;border-radius:16px;padding:48px 32px;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,0.06)}
    .es-thank-icon{width:72px;height:72px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;margin:0 auto 20px}
    .es-thank-you-card h1{font-size:32px;font-weight:700;color:#1e1e2e;margin:0 0 8px}
    .es-thank-sub{font-size:16px;color:#6b7280;margin:0 0 24px}
    .es-thank-pkg{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin:24px 0;text-align:center}
    .es-thank-pkg-name{font-size:20px;font-weight:600;color:#1e1e2e;margin-bottom:8px}
    .es-thank-pkg-price{font-size:28px;font-weight:700;color:#10b981}
    .es-thank-pkg-price span{font-size:14px;font-weight:400;color:#9ca3af;display:block;margin-top:4px}
    .es-thank-msg{font-size:15px;color:#4b5563;line-height:1.6;margin:20px 0 8px}
    .es-thank-meta{font-size:13px;color:#9ca3af}
    </style>
    <?php
    return;
}

/* ───────────────────────────────────────────────────────────────
 *  NON-STRIPE THANK-YOU (?selected=1&pkg=Y)
 * ─────────────────────────────────────────────────────────────── */
if ( $is_selected && $selected_pkg ) {
    $pkg = ES_Packages::get( $selected_pkg );
    $cur = $pkg && ! empty( $pkg->currency ) ? $pkg->currency : ( $settings['default_currency'] ?? 'INR' );
    ?>
    <div class="es-public-packages es-thank-you">
        <div class="es-thank-you-card">
            <div class="es-thank-icon">✓</div>
            <h1>Thank You!</h1>
            <p class="es-thank-sub">Your package selection has been confirmed.</p>
            <?php if ( $pkg ) : ?>
                <div class="es-thank-pkg">
                    <div class="es-thank-pkg-name"><?php echo esc_html( $pkg->package_name ); ?></div>
                    <div class="es-thank-pkg-price"><?php echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) ); ?></div>
                </div>
            <?php endif; ?>
            <p class="es-thank-msg">We've sent a confirmation email and will contact you shortly.</p>
        </div>
    </div>
    <style>
    .es-thank-you{max-width:600px;margin:60px auto;padding:0 20px}
    .es-thank-you-card{background:#fff;border:2px solid #e5e7eb;border-radius:16px;padding:48px 32px;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,0.06)}
    .es-thank-icon{width:72px;height:72px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;margin:0 auto 20px}
    .es-thank-you-card h1{font-size:32px;font-weight:700;color:#1e1e2e;margin:0 0 8px}
    .es-thank-sub{font-size:16px;color:#6b7280;margin:0 0 24px}
    .es-thank-pkg{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin:24px 0;text-align:center}
    .es-thank-pkg-name{font-size:20px;font-weight:600;color:#1e1e2e;margin-bottom:6px}
    .es-thank-pkg-price{font-size:28px;font-weight:700;color:#6366f1}
    .es-thank-msg{font-size:15px;color:#4b5563;line-height:1.6;margin:20px 0 0}
    </style>
    <?php
    return;
}

/* ───────────────────────────────────────────────────────────────
 *  PACKAGE SELECTION MODE
 *
 *  Access rules (all enforced server-side):
 *    1. Visitor must be logged in. If not → render login prompt.
 *    2. If a personalised link is present (?user_id=X&token=Y):
 *         a. user_id MUST match the currently logged-in user.
 *         b. token MUST be valid for that user.
 *       → On success: $valid_link = true, $packages = staged-or-all.
 *       → On failure: render an error explaining the mismatch.
 *    3. If no token in URL but user is logged in:
 *         → treat as "self-serve": $valid_link = true with the logged-in
 *           user as $student_data, $packages = all active packages.
 *           (Any logged-in user can buy any active package.)
 * ─────────────────────────────────────────────────────────────── */
if ( ! is_user_logged_in() ) {
    $show_login   = true;
    $login_reason = 'Please log in to view and purchase packages.';
} elseif ( $user_id && $token ) {
    // Personalised link path
    if ( $user_id !== $current_user_id ) {
        $show_login   = true;
        $login_reason = 'This personalised link belongs to a different account. Please log in with that account.';
    } elseif ( ! ES_Packages::validate_token( $user_id, $token ) ) {
        $show_login   = true;
        $login_reason = 'This link has expired or is invalid. Please contact us for a new one.';
    } else {
        $valid_link = true;
        $user = get_userdata( $user_id );
        if ( $user ) {
            $student_data = array(
                'id'    => $user->ID,
                'name'  => $user->display_name,
                'email' => $user->user_email,
            );
            $staged_ids = ES_Packages::get_staged_packages( $user_id );
            if ( ! empty( $staged_ids ) ) {
                $is_personalised_link = true;
                foreach ( $staged_ids as $sid ) {
                    $p = ES_Packages::get( $sid );
                    if ( $p && $p->is_active ) $packages[] = $p;
                }
            } else {
                $packages = ES_Packages::get_all( true );
            }
        }
    }
} else {
    // Logged-in self-serve — no token in URL, use current user
    $current_user = wp_get_current_user();
    $valid_link   = true;
    $student_data = array(
        'id'    => $current_user->ID,
        'name'  => $current_user->display_name,
        'email' => $current_user->user_email,
    );
    // Mint a token for this session so the inline payment AJAX (which requires
    // user_id + token) authenticates. Reuse the user's existing valid token
    // if one is present (e.g. from a recent after-call), otherwise create one.
    $existing_token = get_user_meta( $current_user->ID, ES_Packages::META_TOKEN, true );
    $existing_exp   = (int) get_user_meta( $current_user->ID, ES_Packages::META_TOKEN_EXP, true );
    if ( $existing_token && $existing_exp > time() ) {
        $token = $existing_token;
    } else {
        $token = ES_Packages::generate_selection_token( $current_user->ID, 1 ); // 1-day TTL
    }
    $user_id = $current_user->ID;
    $packages = ES_Packages::get_all( true );
}

$stripe_ready    = class_exists( 'ES_Stripe' ) && ES_Stripe::is_enabled();
$yearly_discount = (float) ( $settings['yearly_discount'] ?? 0 );

// Admin-only diagnostic — only visible to people who can manage the plugin,
// so end users never see it. Helps the operator understand why "Buy Now"
// isn't showing.
$stripe_admin_hint = '';
if ( ! $stripe_ready && current_user_can( 'manage_options' ) ) {
    $reasons = array();
    if ( ! class_exists( 'ES_Stripe' ) ) {
        $reasons[] = 'Stripe integration class missing — please reinstall the plugin.';
    } else {
        if ( empty( $settings['stripe_enabled'] ) ) {
            $reasons[] = 'The "Enable Stripe Checkout" checkbox is OFF.';
        }
        $mode_label = ! empty( $settings['stripe_mode'] ) ? $settings['stripe_mode'] : 'test';
        $secret_key_name = $mode_label === 'live' ? 'stripe_live_secret' : 'stripe_test_secret';
        if ( empty( $settings[ $secret_key_name ] ) ) {
            $reasons[] = 'No <strong>' . esc_html( ucfirst( $mode_label ) ) . ' Secret Key</strong> is set for the current mode.';
        }
    }
    if ( $reasons ) {
        $stripe_admin_hint = 'Stripe is not active. ' . implode( ' ', $reasons )
            . ' <a href="' . esc_url( admin_url( 'admin.php?page=eduschedule-settings' ) ) . '">Open Settings →</a>';
    }
}

$discount_label  = $yearly_discount > 0
    ? ' (' . rtrim( rtrim( number_format( $yearly_discount, 1 ), '0' ), '.' ) . '% Discount)'
    : '';

$yearly_discount_int = (int) round( $yearly_discount );
?>

<?php if ( $show_login ) :
    // The visitor needs to log in (or log in as the right user) — render a
    // prompt and stop rendering the rest of the page. We preserve the current
    // URL (with user_id/token if present) as the post-login redirect_to so
    // they end up back here after authenticating.
    $current_url = ( is_ssl() ? 'https' : 'http' ) . '://'
        . ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '' )
        . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
    $login_url = wp_login_url( $current_url );
    $registration_open = (bool) get_option( 'users_can_register' );
    ?>
    <div class="es-pp-shell es-pp-login-shell">
        <div class="es-pp-login-card">
            <div class="es-pp-brand" style="justify-content:center;border:0;margin-bottom:18px">
                <?php if ( $brand_logo ) : ?>
                    <img src="<?php echo esc_url( $brand_logo ); ?>" alt="" class="es-pp-brand-logo" />
                <?php else : ?>
                    <span class="es-pp-brand-mark">◆</span>
                <?php endif; ?>
                <span class="es-pp-brand-name"><?php echo esc_html( $brand_name ); ?></span>
            </div>
            <div class="es-pp-login-icon">
                <span class="dashicons dashicons-lock"></span>
            </div>
            <h1 class="es-pp-login-title">Login Required</h1>
            <p class="es-pp-login-sub"><?php echo esc_html( $login_reason ); ?></p>
            <a href="<?php echo esc_url( $login_url ); ?>" class="es-pp-login-btn">
                Log in to continue
            </a>
            <?php if ( $registration_open ) : ?>
                <p class="es-pp-login-meta">
                    Don't have an account?
                    <a href="<?php echo esc_url( wp_registration_url() ); ?>">Sign up</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <style>
    .es-pp-login-shell{max-width:480px;margin:60px auto;padding:0 20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
    .es-pp-login-card{background:#fff;border-radius:16px;padding:36px 32px;text-align:center;box-shadow:0 10px 40px rgba(15,23,42,.08);border:1px solid #e5e7eb}
    .es-pp-login-icon{width:60px;height:60px;border-radius:50%;background:#1e293b;color:#caa657;display:inline-flex;align-items:center;justify-content:center;margin-bottom:18px}
    .es-pp-login-icon .dashicons{font-size:28px;width:28px;height:28px}
    .es-pp-login-title{margin:0 0 10px;font-size:22px;font-weight:600;color:#1e293b}
    .es-pp-login-sub{margin:0 0 22px;font-size:14px;color:#64748b;line-height:1.5}
    .es-pp-login-btn{display:inline-block;background:#caa657;color:#1e293b;padding:12px 26px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;transition:background .15s}
    .es-pp-login-btn:hover{background:#b58e3e;color:#1e293b}
    .es-pp-login-meta{margin:18px 0 0;font-size:13px;color:#94a3b8}
    .es-pp-login-meta a{color:#caa657;text-decoration:none;font-weight:500}
    .es-pp-login-meta a:hover{text-decoration:underline}
    </style>
    <?php return; // stop rendering the rest of the template
endif; ?>

<div class="es-pp-shell" data-default-cycle="<?php echo esc_attr( $atts_default_cycle ); ?>">

    <!-- ============ MAIN PANEL (LEFT) ============ -->
    <div class="es-pp-main">

        <!-- Brand bar -->
        <div class="es-pp-brand">
            <?php if ( $brand_logo ) : ?>
                <img src="<?php echo esc_url( $brand_logo ); ?>" alt="" class="es-pp-brand-logo" />
            <?php else : ?>
                <span class="es-pp-brand-mark">◆</span>
            <?php endif; ?>
            <span class="es-pp-brand-name"><?php echo esc_html( $brand_name ); ?></span>
            <?php if ( $is_personalised_link && $student_data ) : ?>
                <span class="es-pp-brand-sep">|</span>
                <span class="es-pp-brand-sub">Decision Hub</span>
            <?php endif; ?>
        </div>

        <?php if ( $stripe_status === 'cancel' ) : ?>
            <div class="es-pkg-notice es-pkg-notice-warn">
                Payment was cancelled. You can try again whenever you're ready.
            </div>
        <?php endif; ?>

        <?php if ( $stripe_admin_hint ) : ?>
            <div class="es-pkg-notice es-pkg-notice-warn" style="display:flex;gap:10px;align-items:flex-start">
                <span class="dashicons dashicons-warning" style="margin-top:2px"></span>
                <span><strong>Admin notice (only you can see this):</strong> <?php echo wp_kses_post( $stripe_admin_hint ); ?></span>
            </div>
        <?php endif; ?>

        <!-- Heading: personal greeting only for admin-issued personalised links.
             Self-serve logged-in users see the generic "Our Packages" heading
             so the page doesn't awkwardly show their own name as the title. -->
        <?php if ( $is_personalised_link && $student_data ) : ?>
            <div class="es-pp-greeting">
                <h1><?php echo esc_html( $student_data['name'] ); ?></h1>
                <p>Personalized Plan Selection</p>
            </div>
        <?php else : ?>
            <div class="es-pp-greeting">
                <h1>Our Packages</h1>
                <p>Choose the perfect plan for your goals</p>
            </div>
        <?php endif; ?>

        <!-- Recommendation callout — admin-issued personalised links only -->
        <?php if ( $is_personalised_link && $recommendation_text ) : ?>
            <div class="es-pp-reco">
                <div class="es-pp-reco-head">
                    <span class="es-pp-reco-spark">✨</span> My Recommendation
                </div>
                <div class="es-pp-reco-body"><?php echo esc_html( $recommendation_text ); ?></div>
            </div>
        <?php endif; ?>

        <?php if ( empty( $packages ) ) : ?>
            <div class="es-pkg-empty">
                <p>No packages available at the moment. Please check back later.</p>
            </div>
        <?php else : ?>
            <?php if ( $show_toggle ) : ?>
                <div class="es-pp-toggle-wrap es-pp-toggle-wrap-top">
                    <span class="es-pp-toggle-label <?php echo $atts_default_cycle === 'monthly' ? 'is-active' : ''; ?>" data-side="monthly"><?php echo esc_html( $monthly_label_txt ); ?></span>
                    <button type="button" class="es-pp-switch <?php echo $atts_default_cycle === 'yearly' ? 'is-yearly' : ''; ?>" id="es-pp-cycle-switch" aria-label="Toggle billing cycle">
                        <span class="es-pp-switch-thumb"></span>
                    </button>
                    <span class="es-pp-toggle-label <?php echo $atts_default_cycle === 'yearly' ? 'is-active' : ''; ?>" data-side="yearly">
                        <?php echo esc_html( $semester_label_t . $discount_label ); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="es-pp-grid">
                <?php $i = 0; foreach ( $packages as $pkg ) : $i++;
                    $cur            = ! empty( $pkg->currency ) ? $pkg->currency : ( $settings['default_currency'] ?? 'INR' );
                    $monthly_price  = (float) $pkg->price;

                    // Yearly = monthly × 12 − global discount %
                    $yearly_price   = round( ( $monthly_price * 12 ) * ( 1 - ( $yearly_discount / 100 ) ), 2 );

                    $monthly_money_label = ES_Helpers::format_price( $monthly_price, $cur );
                    $yearly_money_label  = ES_Helpers::format_price( $yearly_price,  $cur );

                    $is_recommended = ( $is_personalised_link && $recommended_idx > 0 && $i === $recommended_idx );
                ?>
                    <div class="es-pp-card <?php echo $is_recommended ? 'is-featured' : ''; ?>"
                         data-package-id="<?php echo (int) $pkg->id; ?>"
                         data-currency="<?php echo esc_attr( $cur ); ?>"
                         data-monthly="<?php echo esc_attr( $monthly_price ); ?>"
                         data-yearly="<?php echo esc_attr( $yearly_price ); ?>"
                         data-monthly-label="<?php echo esc_attr( $monthly_money_label ); ?>"
                         data-yearly-label="<?php echo esc_attr( $yearly_money_label ); ?>"
                         data-package-name="<?php echo esc_attr( $pkg->package_name ); ?>">

                        <?php if ( $is_recommended ) : ?>
                            <div class="es-pp-ribbon">Teacher's Choice</div>
                        <?php endif; ?>

                        <div class="es-pp-card-icon">
                            <?php if ( $i === 1 ) : ?>
                                <!-- Cap icon -->
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 8 L4 18 L24 28 L44 18 Z"/><path d="M12 22 L12 32 C12 32 17 36 24 36 C31 36 36 32 36 32 L36 22"/><path d="M44 18 L44 30"/></svg>
                            <?php elseif ( $i === 2 ) : ?>
                                <!-- Flask icon -->
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 4 L20 16 L10 38 C8 42 11 44 14 44 L34 44 C37 44 40 42 38 38 L28 16 L28 4 Z"/><path d="M20 4 L28 4"/></svg>
                            <?php else : ?>
                                <!-- Smaller flask -->
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 6 L21 18 L13 38 C12 41 14 43 17 43 L31 43 C34 43 36 41 35 38 L27 18 L27 6 Z"/><path d="M21 6 L27 6"/></svg>
                            <?php endif; ?>
                        </div>

                        <h3 class="es-pp-card-name"><?php echo esc_html( $pkg->package_name ); ?></h3>
                        <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                            <p class="es-pp-card-sub"><?php echo esc_html( $pkg->sub_heading ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $pkg->description ) ) : ?>
                            <ul class="es-pp-card-features">
                                <?php
                                $lines = explode( "\n", $pkg->description );
                                foreach ( $lines as $line ) {
                                    $line = trim( $line );
                                    if ( $line === '' ) continue;
                                    $line = ltrim( $line, '•- ' );
                                    echo '<li>' . esc_html( $line ) . '</li>';
                                }
                                ?>
                            </ul>
                        <?php endif; ?>

                        <div class="es-pp-card-price">
                            <span class="es-pp-amount-monthly" <?php echo $atts_default_cycle === 'yearly' ? 'style="display:none"' : ''; ?>>
                                <?php echo esc_html( $monthly_money_label ); ?><span class="es-pp-period"> / <?php echo esc_html( $period_unit ); ?></span>
                            </span>
                            <span class="es-pp-amount-yearly" <?php echo $atts_default_cycle === 'monthly' ? 'style="display:none"' : ''; ?>>
                                <?php echo esc_html( $yearly_money_label ); ?><span class="es-pp-period"> / <?php echo esc_html( ! empty( $sc_atts['period_unit_yearly'] ) ? $sc_atts['period_unit_yearly'] : 'year' ); ?></span>
                                <?php if ( $yearly_discount_int > 0 ) : ?>
                                    <span class="es-pp-save-badge">Save <?php echo (int) $yearly_discount_int; ?>%</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ( $stripe_ready ) : ?>
                            <button type="button" class="es-pp-select-btn"
                                    data-package-id="<?php echo (int) $pkg->id; ?>"
                                    data-user-id="<?php echo (int) $student_data['id']; ?>"
                                    data-token="<?php echo esc_attr( $token ); ?>"
                                    data-name="<?php echo esc_attr( $student_data['name'] ); ?>"
                                    data-email="<?php echo esc_attr( $student_data['email'] ); ?>">
                                Select This Plan
                            </button>
                        <?php else : ?>
                            <button type="button" class="es-pkg-contact-btn">Contact Us</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

    <!-- ============ PAYMENT PANEL (RIGHT — slides in) ============ -->
    <?php if ( $valid_link && $student_data ) : ?>
        <aside class="es-pp-pay-panel" id="es-pp-pay-panel" aria-hidden="true">
            <button type="button" class="es-pp-pay-close" id="es-pp-pay-close" aria-label="Close payment panel">×</button>

            <div class="es-pp-pay-brand">
                <?php if ( $brand_logo ) : ?>
                    <img src="<?php echo esc_url( $brand_logo ); ?>" alt="" />
                <?php else : ?>
                    <div class="es-pp-pay-brand-mark">◆</div>
                <?php endif; ?>
                <div class="es-pp-pay-brand-name"><?php echo esc_html( $brand_name ); ?></div>
                <div class="es-pp-pay-brand-sub">Personalized Plan</div>
            </div>

            <div class="es-pp-pay-secure">
                <span class="dashicons dashicons-lock"></span> Secure payment form
            </div>

            <form id="es-pp-pay-form" novalidate>
                <input type="hidden" id="es-pp-package-id" value="" />
                <input type="hidden" id="es-pp-user-id" value="<?php echo (int) $student_data['id']; ?>" />
                <input type="hidden" id="es-pp-token" value="<?php echo esc_attr( $token ); ?>" />
                <input type="hidden" id="es-pp-cycle" value="<?php echo esc_attr( $atts_default_cycle ); ?>" />

                <div class="es-pp-pay-field">
                    <label for="es-pp-name">Name</label>
                    <input type="text" id="es-pp-name" placeholder="Name" value="<?php echo esc_attr( $student_data['name'] ); ?>" required />
                </div>

                <div class="es-pp-pay-field">
                    <label for="es-pp-email">Email</label>
                    <input type="email" id="es-pp-email" placeholder="Email" value="<?php echo esc_attr( $student_data['email'] ); ?>" required />
                </div>

                <div class="es-pp-pay-field">
                    <label>Payment Method</label>
                    <div class="es-pp-pay-method">
                        <span class="dashicons dashicons-money-alt"></span>
                        <span>Credit Card</span>
                        <span class="es-pp-pay-method-caret">▾</span>
                    </div>
                </div>

                <div class="es-pp-pay-field">
                    <label for="es-pp-card-element">Card Details</label>
                    <!-- Stripe Card Element mounts here -->
                    <div id="es-pp-card-element" class="es-pp-card-element"></div>
                    <div id="es-pp-card-errors" class="es-pp-card-errors" role="alert"></div>
                </div>

                <div class="es-pp-pay-summary">
                    <div class="es-pp-pay-summary-label">Plan</div>
                    <div class="es-pp-pay-summary-value" id="es-pp-summary-plan">—</div>
                    <div class="es-pp-pay-summary-label">Total</div>
                    <div class="es-pp-pay-summary-amount" id="es-pp-summary-amount">—</div>
                </div>

                <button type="submit" class="es-pp-pay-submit" id="es-pp-pay-submit">
                    <span class="es-pp-pay-submit-text">Activate Plan</span>
                </button>

                <div class="es-pp-pay-marks">
                    <span class="es-pp-mark">VISA</span>
                    <span class="es-pp-mark">Mastercard</span>
                    <span class="es-pp-mark">Amex</span>
                    <span class="es-pp-mark">UPI</span>
                </div>
            </form>
        </aside>
        <div class="es-pp-overlay" id="es-pp-overlay" aria-hidden="true"></div>
    <?php endif; ?>
</div>

<style>
/* ─── Shell ─── */
.es-pp-shell{position:relative;max-width:1180px;margin:24px auto;padding:0 20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.es-pp-main{background:#fff;border-radius:14px;padding:32px 36px 40px;border:1px solid #eef0f3;box-shadow:0 1px 2px rgba(0,0,0,0.02)}

/* Brand bar */
.es-pp-brand{display:flex;align-items:center;gap:10px;padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid #eef0f3;color:#1e293b;font-size:15px}
.es-pp-brand-logo{height:28px;width:auto}
.es-pp-brand-mark{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;color:#1e3a8a}
.es-pp-brand-name{font-weight:600}
.es-pp-brand-sep{color:#cbd5e1}
.es-pp-brand-sub{color:#64748b}

/* Greeting */
.es-pp-greeting{text-align:center;margin:6px 0 18px}
.es-pp-greeting h1{font-size:26px;font-weight:700;color:#1e293b;margin:0 0 4px;letter-spacing:.2px}
.es-pp-greeting p{font-size:14px;color:#64748b;margin:0}

/* Recommendation */
.es-pp-reco{background:#fff8e6;border:1px solid #f5d56b;border-radius:10px;padding:12px 16px;margin:14px 0 22px}
.es-pp-reco-head{font-size:14px;font-weight:600;color:#92651e;margin-bottom:4px}
.es-pp-reco-spark{margin-right:6px}
.es-pp-reco-body{font-size:13px;color:#5c4514;line-height:1.5}

/* Notice */
.es-pkg-notice{padding:10px 14px;border-radius:8px;margin:0 0 16px;font-size:13px}
.es-pkg-notice-warn{background:#fef3c7;border:1px solid #fde68a;color:#92400e}

/* Grid */
.es-pp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-top:8px}
.es-pp-card{position:relative;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px 18px 20px;display:flex;flex-direction:column;align-items:center;text-align:center;transition:all .2s}
.es-pp-card:hover{border-color:#cbd5e1;box-shadow:0 4px 10px rgba(0,0,0,.06)}
.es-pp-card.is-featured{background:#1e293b;color:#fff;border-color:#1e293b;transform:translateY(-4px)}
.es-pp-card.is-featured .es-pp-card-name,
.es-pp-card.is-featured .es-pp-card-price,
.es-pp-card.is-featured .es-pp-card-icon{color:#fff}
.es-pp-card.is-featured .es-pp-card-sub,
.es-pp-card.is-featured .es-pp-card-features li{color:#cbd5e1}

.es-pp-ribbon{position:absolute;top:14px;right:-6px;background:#caa657;color:#fff;font-size:10px;font-weight:700;padding:5px 14px;border-radius:3px;letter-spacing:.5px;transform:rotate(0deg);box-shadow:0 1px 4px rgba(0,0,0,0.2)}
.es-pp-ribbon::after{content:'';position:absolute;top:100%;right:0;border:3px solid transparent;border-top-color:#8a6d36;border-right-color:#8a6d36}

.es-pp-card-icon{color:#94a3b8;margin-bottom:10px}
.es-pp-card.is-featured .es-pp-card-icon{color:#caa657}
.es-pp-card-name{font-size:15px;font-weight:600;color:#1e293b;margin:6px 0 4px;line-height:1.35}
.es-pp-card-sub{font-size:12px;color:#64748b;margin:0 0 12px}
.es-pp-card-features{list-style:none;padding:0;margin:8px 0 14px;font-size:13px;color:#475569;line-height:1.8}
.es-pp-card-features li{padding:0}
.es-pp-card-price{font-size:22px;font-weight:700;color:#1e293b;margin:6px 0 14px}
.es-pp-period{font-size:13px;font-weight:400;color:#94a3b8}
.es-pp-card.is-featured .es-pp-period{color:#cbd5e1}

.es-pp-select-btn,.es-pkg-contact-btn{appearance:none;background:#1e293b;color:#fff;border:0;width:100%;padding:11px 14px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;letter-spacing:.2px}
.es-pp-select-btn:hover,.es-pkg-contact-btn:hover{background:#0f172a}
.es-pp-card.is-featured .es-pp-select-btn{background:#caa657;color:#1e293b}
.es-pp-card.is-featured .es-pp-select-btn:hover{background:#b58e3e}
.es-pp-select-btn:disabled{opacity:.7;cursor:wait}

/* Cycle toggle */
.es-pp-toggle-wrap{display:flex;align-items:center;justify-content:center;gap:14px;margin:28px auto 6px;font-size:13px;color:#475569}
.es-pp-toggle-wrap-top{
    margin:8px auto 22px;
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:999px;
    padding:8px 18px;
    width:fit-content;
}
.es-pp-toggle-label{cursor:pointer;user-select:none;transition:color .15s}
.es-pp-toggle-label.is-active{color:#1e293b;font-weight:600}
.es-pp-switch{appearance:none;background:#caa657;border:0;width:44px;height:24px;border-radius:999px;position:relative;cursor:pointer;padding:0;transition:background .2s}
.es-pp-switch-thumb{position:absolute;top:2px;left:2px;width:20px;height:20px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.es-pp-switch.is-yearly .es-pp-switch-thumb{transform:translateX(20px)}

.es-pkg-empty{text-align:center;padding:60px 20px;color:#64748b}

/* ─── Payment Panel (right slide-in) ─── */
.es-pp-pay-panel{
    position:fixed;top:0;right:0;width:380px;max-width:100%;height:100vh;
    background:#162439;color:#e2e8f0;padding:28px 26px;
    overflow-y:auto;z-index:99998;
    transform:translateX(105%);transition:transform .35s cubic-bezier(.4,0,.2,1);
    box-shadow:-8px 0 32px rgba(0,0,0,.25);
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
}
.es-pp-pay-panel.is-open{transform:translateX(0)}
.es-pp-overlay{
    position:fixed;inset:0;background:rgba(15,23,42,0.55);
    opacity:0;pointer-events:none;transition:opacity .35s;z-index:99997;
}
.es-pp-overlay.is-open{opacity:1;pointer-events:auto}

.es-pp-pay-close{
    position:absolute;top:14px;right:14px;width:32px;height:32px;border:0;background:transparent;
    color:#cbd5e1;font-size:28px;line-height:1;cursor:pointer;border-radius:50%;
}
.es-pp-pay-close:hover{background:rgba(255,255,255,.08);color:#fff}

.es-pp-pay-brand{text-align:center;padding:14px 0 18px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:18px}
.es-pp-pay-brand img{max-height:48px}
.es-pp-pay-brand-mark{font-size:32px;color:#caa657}
.es-pp-pay-brand-name{font-size:15px;font-weight:600;color:#fff;margin-top:6px}
.es-pp-pay-brand-sub{font-size:12px;color:#94a3b8;margin-top:2px}

.es-pp-pay-secure{
    display:flex;align-items:center;justify-content:center;gap:8px;
    background:rgba(255,255,255,.06);border-radius:8px;padding:10px;
    font-size:13px;color:#cbd5e1;margin-bottom:18px;
}
.es-pp-pay-secure .dashicons{font-size:16px;width:16px;height:16px}

.es-pp-pay-field{margin-bottom:14px}
.es-pp-pay-field label{display:block;font-size:12px;color:#94a3b8;margin-bottom:6px;font-weight:500}
.es-pp-pay-field input[type=text],
.es-pp-pay-field input[type=email]{
    width:100%;padding:10px 12px;background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:14px;
    box-sizing:border-box;
}
.es-pp-pay-field input:focus{outline:none;border-color:#caa657;background:rgba(255,255,255,.09)}
.es-pp-pay-field input::placeholder{color:#64748b}

.es-pp-pay-method{
    display:flex;align-items:center;gap:8px;
    padding:10px 12px;background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);border-radius:8px;font-size:14px;color:#fff;
}
.es-pp-pay-method .dashicons{font-size:18px;width:18px;height:18px;color:#cbd5e1}
.es-pp-pay-method-caret{margin-left:auto;color:#94a3b8}

.es-pp-card-element{
    padding:12px;background:rgba(255,255,255,.92);border:1px solid rgba(255,255,255,.1);
    border-radius:8px;min-height:42px;
}
.es-pp-card-errors{color:#fca5a5;font-size:12px;margin-top:6px;min-height:16px}

.es-pp-pay-summary{
    background:rgba(255,255,255,.04);border-radius:8px;padding:12px 14px;margin:8px 0 14px;
    display:grid;grid-template-columns:1fr auto;row-gap:6px;font-size:13px;
}

.es-pp-pay-summary-label{color:#94a3b8}
.es-pp-pay-summary-value{color:#fff;text-align:right;font-weight:500}
.es-pp-pay-summary-amount{color:#caa657;font-weight:700;font-size:15px;text-align:right}

.es-pp-pay-submit{
    width:100%;padding:13px;background:#caa657;color:#1e293b;
    border:0;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;letter-spacing:.3px;
    transition:all .2s;
}
.es-pp-pay-submit:hover:not(:disabled){background:#b58e3e}
.es-pp-pay-submit:disabled{opacity:.65;cursor:wait}

.es-pp-pay-marks{display:flex;gap:6px;justify-content:center;margin-top:14px;flex-wrap:wrap}
.es-pp-mark{
    background:rgba(255,255,255,.08);color:#cbd5e1;
    font-size:10px;font-weight:600;padding:4px 8px;border-radius:4px;letter-spacing:.5px;
}

/* ─── "Save X%" discount badge on yearly price ─── */
.es-pp-save-badge{
    display:inline-block;
    background:#10b981;
    color:#fff;
    font-size:11px;
    font-weight:700;
    padding:3px 8px;
    border-radius:10px;
    margin-left:8px;
    vertical-align:middle;
    letter-spacing:.3px;
    text-transform:uppercase;
}
.es-pp-card.is-featured .es-pp-save-badge{
    background:#caa657;
    color:#1e293b;
}

/* Mobile */
@media (max-width:640px){
    .es-pp-main{padding:22px 18px 28px}
    .es-pp-pay-panel{width:100%;max-width:none}
    .es-pp-greeting h1{font-size:22px}
}
</style>
