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
// Package-discount toggle. Global discount settings are no longer used for
// package purchases; the switch is shown only when at least one rendered
// package has its own Discount % and Discount Months configured, unless the
// shortcode explicitly forces yearly_toggle="yes" or "no".
$show_toggle = ( $atts_yearly_toggle === 'yes' );
if ( ! in_array( $atts_default_cycle, array( 'monthly', 'yearly' ), true ) ) $atts_default_cycle = 'monthly';

$monthly_label_txt = ! empty( $sc_atts['monthly_label'] )  ? $sc_atts['monthly_label']  : 'Pay Monthly';
$semester_label_t  = ! empty( $sc_atts['semester_label'] ) ? $sc_atts['semester_label'] : 'Pay Yearly';
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
$login_mode   = '';          // 'guest' | 'wrong_account' | 'expired'
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
            <?php if ( $pkg && $row ) :
                $row_months    = max( 1, (int) ( $row->months ?? ( $pkg->months ?? 1 ) ) );
                $row_total     = (int) ( $row->total_sessions ?? ( $pkg->total_sessions ?? 0 ) );
                $row_monthly   = (int) ( $row->monthly_session_limit ?? ( $pkg->monthly_session_limit ?? 0 ) );
                $row_used      = (int) ( $row->used_sessions ?? 0 );
                $row_remaining = max( 0, $row_total - $row_used );
            ?>
                <div class="es-thank-pkg">
                    <div class="es-thank-pkg-name"><?php echo esc_html( $pkg->package_name ); ?></div>
                    <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                        <div class="es-thank-pkg-sub" style="font-size:13px;color:#6b7280;margin-bottom:8px;"><?php echo esc_html( $pkg->sub_heading ); ?></div>
                    <?php endif; ?>
                    <div class="es-thank-pkg-price">
                        <?php echo esc_html( ES_Helpers::format_price( $row->amount, $cur ) ); ?>
                        <span>· <?php echo (int) $row_months; ?> month<?php echo $row_months > 1 ? 's' : ''; ?> · paid in full</span>
                    </div>

                    <div class="es-thank-details" style="margin-top:18px;text-align:left;border-top:1px solid #e5e7eb;padding-top:16px;">
                        <div class="es-thank-detail-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;">
                            <span style="color:#6b7280;">Package Duration</span>
                            <strong style="color:#1e1e2e;"><?php echo (int) $row_months; ?> month<?php echo $row_months > 1 ? 's' : ''; ?></strong>
                        </div>
                        <div class="es-thank-detail-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;">
                            <span style="color:#6b7280;">Total Sessions</span>
                            <strong style="color:#1e1e2e;"><?php echo (int) $row_total; ?></strong>
                        </div>
                        <div class="es-thank-detail-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;">
                            <span style="color:#6b7280;">Monthly Sessions</span>
                            <strong style="color:#1e1e2e;"><?php echo (int) $row_monthly; ?> / month</strong>
                        </div>
                        <div class="es-thank-detail-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;">
                            <span style="color:#6b7280;">Remaining Sessions</span>
                            <strong style="color:#10b981;"><?php echo (int) $row_remaining; ?></strong>
                        </div>
                    </div>

                    <?php if ( $row->valid_until ) : ?>
                        <div class="es-thank-meta" style="margin-top:14px">
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
 *  PACKAGE DISPLAY MODE  (v3.9.6 — packages are PUBLIC)
 *
 *  New behaviour requested by the client:
 *    • The 3 packages are ALWAYS visible — even to logged-out visitors.
 *      We no longer hide them behind a "Login Required" wall.
 *    • Login is only enforced at BUY time: when a logged-out visitor
 *      clicks "Select This Plan", the JS opens a login popup. Once they
 *      log in (or sign up) they come back to this page and can pay.
 *
 *  Access rules:
 *    1. Logged-OUT visitor:
 *         → packages render normally. $valid_link = false (no payment
 *           panel rendered server-side; JS shows a login popup on Buy).
 *           $packages = all active packages.
 *    2. Personalised link (?user_id=X&token=Y) for the logged-in user:
 *         → staged packages + greeting + recommendation, same as before.
 *    3. Logged-IN self-serve (no token in URL):
 *         → any logged-in user can buy any active package.
 * ─────────────────────────────────────────────────────────────── */
$login_popup_url = '';   // used by JS to send logged-out users to login
$is_guest_view   = false; // true when a logged-out visitor is browsing
$register_url    = '';
$lostpw_url      = '';

if ( ! is_user_logged_in() ) {


    // PUBLIC view — per requirement, logged-out visitors must log in first and
    // are NOT shown the package list. Build a login URL that returns them to
    // THIS exact page after authenticating.
    $current_url = ( is_ssl() ? 'https' : 'http' ) . '://'
        . ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '' )
        . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
    $login_popup_url = wp_login_url( $current_url );
    $is_guest_view   = true;

    // Prefer the plugin's own register page if configured, else WP default.
    $reg_page_id  = (int) ( $settings['register_page_id'] ?? 0 );
    $register_url = $reg_page_id ? get_permalink( $reg_page_id ) : ( get_option( 'users_can_register' ) ? wp_registration_url() : '' );
    $lostpw_url   = wp_lostpassword_url( $current_url );

    // Show the login screen only — no packages for guests.
    $show_login   = true;
    $login_reason = 'Please log in to view your packages and continue.';
    $login_mode   = 'guest';
    $valid_link   = false;
    $student_data = null;
    $packages     = array();

} elseif ( $user_id && $token ) {
    // Personalised link path
    if ( $user_id !== $current_user_id ) {
        // The visitor IS logged in, just not as the link's owner. Don't ask them
        // to log in again — show a clear "this package isn't for you" message.
        $show_login   = true;
        $login_mode   = 'wrong_account';
        $login_reason = 'This package selection was prepared for a different account, so it isn’t available on your account. If you believe this is a mistake, please contact us and we’ll be happy to help.';
    } elseif ( ! ES_Packages::validate_token( $user_id, $token ) ) {
        $show_login   = true;
        $login_mode   = 'expired';
        $login_reason = 'This package link has expired. Please contact us for an up-to-date link.';
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

// Course label shown on personalized purchase links. It is read from the
// staged lead/payment flow so the purchase page clearly shows which course the
// package belongs to.
$selection_course_name = '';
$selection_flow_type   = '1to1';
if ( ! empty( $student_data['id'] ) ) {
    $selection_flow_type = ES_Packages::get_staged_flow( (int) $student_data['id'] );
    $latest_course_row = ES_Packages::get_latest_lead_outcome( (int) $student_data['id'] );
    if ( $latest_course_row && ! empty( $latest_course_row->course_name ) ) {
        $selection_course_name = $latest_course_row->course_name;
    } elseif ( $latest_course_row && ! empty( $latest_course_row->group_id ) ) {
        $selection_course_name = ES_Packages::course_names_str( ES_Packages::get_group_course_ids( (int) $latest_course_row->group_id ) );
    } else {
        $selection_course_name = ES_Packages::course_names_str( ES_Packages::get_student_course_ids( (int) $student_data['id'] ) );
    }
}

// Recompute the discounted toggle now that the exact packages to render are known.
$has_package_discount = false;
if ( ! empty( $packages ) ) {
    foreach ( $packages as $pkg_for_toggle ) {
        if ( ! empty( $pkg_for_toggle->discount_percent ) && ! empty( $pkg_for_toggle->discount_months ) ) {
            $has_package_discount = true;
            break;
        }
    }
}
if ( $atts_yearly_toggle === 'no' ) {
    $show_toggle = false;
} elseif ( $atts_yearly_toggle !== 'yes' ) {
    $show_toggle = $has_package_discount;
}
if ( ! $show_toggle && $atts_default_cycle === 'yearly' ) {
    $atts_default_cycle = 'monthly';
}

/* Pull the logged-in user's saved billing details (used to pre-fill the
 * Stripe address fields — required for INR/India card payments). */
$billing_prefill = array( 'country' => '', 'phone' => '', 'line1' => '', 'city' => '', 'state' => '', 'postal' => '' );
if ( is_user_logged_in() ) {
    $cu = wp_get_current_user();
    $billing_prefill['country'] = strtoupper( (string) get_user_meta( $cu->ID, 'es_country', true ) );
    $billing_prefill['phone']   = (string) get_user_meta( $cu->ID, 'es_phone', true );
    $billing_prefill['line1']   = (string) get_user_meta( $cu->ID, 'es_addr_line1', true );
    $billing_prefill['city']    = (string) get_user_meta( $cu->ID, 'es_addr_city', true );
    $billing_prefill['state']   = (string) get_user_meta( $cu->ID, 'es_addr_state', true );
    $billing_prefill['postal']  = (string) get_user_meta( $cu->ID, 'es_addr_postal', true );
}

$stripe_ready    = class_exists( 'ES_Stripe' ) && ES_Stripe::is_enabled();
$yearly_discount = 0; // Global discount intentionally ignored; package-level discounts are used per card.

// Packages this user already owns an active (still-valid) plan for — used to
// show a "Current Plan" state and disable the buy button so they can't
// re-purchase the same package while it's active.
$owned_active_ids = ( is_user_logged_in() && ! empty( $student_data['id'] ) )
    ? ES_Packages::get_active_package_ids( (int) $student_data['id'], $selection_flow_type )
    : array();

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

$discount_label  = $has_package_discount ? ' (discount available)' : '';
$yearly_discount_int = 0;
?>

<?php if ( $show_login ) : ?>

    <?php if ( $login_mode === 'guest' ) : ?>

        <div class="es-pp-login-shortcode-wrap">
            <?php echo do_shortcode( '[eduschedule_auth]' ); ?>
        </div>

  

    <?php else : ?>

        <?php
        $dash_id  = (int) ( $settings['dashboard_page_id'] ?? 0 );
        $dash_url = $dash_id ? get_permalink( $dash_id ) : home_url( '/' );
        ?>

        <div class="es-pp-shell es-pp-login-shell">
            <div class="es-pp-login-card">
                
                <div class="es-pp-login-icon">
                    <span class="dashicons <?php echo $login_mode === 'wrong_account' ? 'dashicons-info-outline' : 'dashicons-lock'; ?>"></span>
                </div>

                <?php if ( $login_mode === 'wrong_account' ) : ?>
                    <h1 class="es-pp-login-title">This package isn’t available on your account</h1>
                    <p class="es-pp-login-sub"><?php echo esc_html( $login_reason ); ?></p>
                    <a href="<?php echo esc_url( $dash_url ); ?>" class="btn btn-primary">Go to my dashboard</a>

                <?php elseif ( $login_mode === 'expired' ) : ?>
                    <h1 class="es-pp-login-title">This link has expired</h1>
                    <p class="es-pp-login-sub"><?php echo esc_html( $login_reason ); ?></p>
                    <a href="<?php echo esc_url( $dash_url ); ?>" class="btn btn-primary">Go to my dashboard</a>
                <?php endif; ?>
            </div>
        </div>

      
    <?php endif; ?>

    <?php return; // stop rendering the rest of the template ?>

<?php endif; ?>

<div class="es-pp-shell<?php echo ( $valid_link && $student_data ) ? ' has-pay-col' : ''; ?>" data-default-cycle="<?php echo esc_attr( $atts_default_cycle ); ?>" data-yearly-discount="<?php echo esc_attr( $yearly_discount_int ); ?>">

    <!-- ============ MAIN PANEL (LEFT) ============ -->
    <div class="es-pp-main">

        <!-- Brand bar -->
        <div class="es-pp-brand">
                <span class="es-pp-brand-mark">◆</span>
            <span class="es-pp-brand-name">Package Detail</span>
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
                <p>Personalized Plan Selection<?php echo $selection_course_name ? ' · Course: ' . esc_html( $selection_course_name ) : ''; ?></p>
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

                    // ── New monthly model ──
                    //   monthly_price = charged per month
                    //   months        = duration
                    //   total         = monthly_price × months (stored in `price`)
                    // Fall back gracefully for legacy packages where only `price`
                    // (the old monthly figure) exists.
                    $pkg_months    = max( 1, (int) ( $pkg->months ?? 1 ) );
                    $monthly_price = (float) ( $pkg->monthly_price ?? 0 );
                    if ( $monthly_price <= 0 ) {
                        // Legacy: treat stored price as monthly, total = price (1 month)
                        $monthly_price = (float) $pkg->price;
                        $total_price   = ( $pkg_months > 1 ) ? round( $monthly_price * $pkg_months, 2 ) : (float) $pkg->price;
                    } else {
                        $total_price   = (float) $pkg->price; // already monthly × months
                        if ( $total_price <= 0 ) {
                            $total_price = round( $monthly_price * $pkg_months, 2 );
                        }
                    }

                    $total_sessions = (int) ( $pkg->total_sessions ?? 0 );
                    $monthly_limit  = (int) ( $pkg->monthly_session_limit ?? 0 );

                    // Discounted ("yearly") cycle (v4.3). The discounted tab
                    // bills the package's OWN duration at the monthly rate, then
                    // subtracts a discount applied only to discount_months:
                    //   total = (months × monthly) − (monthly × discount_months × discount% / 100)
                    // The headline per-month shown on the discounted tab is the
                    // effective rate = total ÷ months (kept consistent with total).
                    $pkg_discount_percent = max( 0, min( 100, (float) ( $pkg->discount_percent ?? 0 ) ) );
                    $pkg_discount_months  = $pkg->discount_months ? $pkg->discount_months : $pkg_months;
                    $pkg_has_discount     = ( $pkg_discount_percent > 0 && $pkg_discount_months > 0 );

                    $yearly_billed_months = $pkg_discount_months;
                    $yearly_gross         = $monthly_price * $yearly_billed_months;
                    $yearly_discount_amt  = $pkg_has_discount
                        ? ( $monthly_price * $pkg_discount_months * $pkg_discount_percent / 100 )
                        : 0;
                    $yearly_total         = round( max( 0, $yearly_gross - $yearly_discount_amt ), 2 );
                    // Effective per-month rate for the discounted tab headline.
                    $yearly_price         = $yearly_billed_months > 0
                        ? round( $yearly_total / $yearly_billed_months, 2 )
                        : $monthly_price;

                    $monthly_money_label = ES_Helpers::format_price( $monthly_price, $cur );
                    $yearly_money_label  = ES_Helpers::format_price( $yearly_price,  $cur );
                    $total_money_label   = ES_Helpers::format_price( $total_price,   $cur );
                    $pkg_discount_int    = (int) round( $pkg_discount_percent );

                    $is_recommended = ( $is_personalised_link && $recommended_idx > 0 && $i === $recommended_idx );
                    $is_owned       = in_array( (int) $pkg->id, $owned_active_ids, true );
                ?>
                    <div class="es-pp-card <?php echo $is_recommended ? 'is-featured' : ''; ?><?php echo $is_owned ? ' is-owned' : ''; ?>"
                         data-package-id="<?php echo (int) $pkg->id; ?>"
                         data-currency="<?php echo esc_attr( $cur ); ?>"
                         data-monthly="<?php echo esc_attr( $monthly_price ); ?>"
                         data-yearly="<?php echo esc_attr( $yearly_price ); ?>"
                         data-months="<?php echo (int) $pkg_months; ?>"
                         data-total="<?php echo esc_attr( $total_price ); ?>"
                         data-total-label="<?php echo esc_attr( $total_money_label ); ?>"
                         data-total-sessions="<?php echo (int) $total_sessions; ?>"
                         data-monthly-limit="<?php echo (int) $monthly_limit; ?>"
                         data-discount-percent="<?php echo esc_attr( $pkg_discount_percent ); ?>"
                         data-discount-months="<?php echo (int) $pkg_discount_months; ?>"
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
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M24 8 L4 18 L24 28 L44 18 Z"/><path d="M12 22 L12 32 C12 32 17 36 24 36 C31 36 36 32 36 32 L36 22"/><path d="M44 18 L44 30"/></svg>
                            <?php endif; ?>
                        </div>

                        <h3 class="es-pp-card-name"><?php echo esc_html( $pkg->package_name ); ?></h3>
                        <?php if ( $selection_course_name ) : ?>
                            <div class="es-pp-course-chip" >Course: <?php echo esc_html( $selection_course_name ); ?></div>
                        <?php endif; ?>
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
                                <?php echo esc_html( $monthly_money_label ); ?><span class="es-pp-period"> / month</span>
                            </span>
                            <span class="es-pp-amount-yearly" <?php echo $atts_default_cycle === 'monthly' ? 'style="display:none"' : ''; ?>>
                                <?php if ( $pkg_has_discount ) : ?>
                                    <span class="es-pp-old-price"><?php echo esc_html( $monthly_money_label ); ?></span>
                                <?php endif; ?>
                                <?php echo esc_html( $yearly_money_label ); ?><span class="es-pp-period"> / month</span>
                                <?php if ( $pkg_has_discount ) : ?>
                                    <span class="es-pp-save-badge">Save <?php echo (int) $pkg_discount_int; ?>% / <?php echo (int) $pkg_discount_months; ?> mo</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <!-- Duration + total payable breakdown -->
                        <?php
                        // When the page defaults to the discounted cycle, show the
                        // discount-months duration up front (matches the JS toggle,
                        // which swaps Duration to discount_months when ON).
                        $bd_init_months = ( $atts_default_cycle === 'yearly' && $pkg_has_discount )
                            ? $pkg_discount_months
                            : $pkg_months;
                        ?>
                        <div class="es-pp-card-breakdown" style="font-size:13px;line-height:1.7;margin:-6px 0 12px;color:inherit;opacity:.85;">
                            <div class="es-pp-bd-row" style="display:flex;justify-content:space-between;gap:10px;">
                                <span>Duration</span>
                                <strong class="es-pp-bd-duration"><?php echo (int) $bd_init_months; ?> month<?php echo $bd_init_months > 1 ? 's' : ''; ?></strong>
                            </div>
                            <?php if ( $total_sessions > 0 ) : ?>
                                <div class="es-pp-bd-row" style="display:flex;justify-content:space-between;gap:10px;">
                                    <span>Sessions</span>
                                    <strong class="es-pp-bd-sessions"><?php echo (int) $total_sessions; ?> session<?php echo $total_sessions !== 1 ? 's' : ''; ?><?php if ( $monthly_limit > 0 ) : ?> <small style="opacity:.75">(<?php echo (int) $monthly_limit; ?>/mo)</small><?php endif; ?></strong>
                                </div>
                            <?php endif; ?>
                            <div class="es-pp-bd-row es-pp-bd-total" style="display:flex;justify-content:space-between;gap:10px;font-size:15px;margin-top:4px;padding-top:6px;border-top:1px solid rgba(128,128,128,.2);">
                                <span>Total payable</span>
                                <strong class="es-pp-bd-total-amount"><?php echo esc_html( $total_money_label ); ?></strong>
                            </div>
                        </div>

                        <?php
                        // v3.9.6 — The "Select This Plan" button now shows for
                        // EVERYONE. For logged-out visitors it carries a
                        // data-login-url so the JS can pop the login flow.
                        // For logged-in users it carries the real user/token so
                        // the inline Stripe payment panel opens.
                        $is_guest = ! is_user_logged_in();
                        ?>
                        <?php if ( $is_owned ) : ?>
                            <button type="button" class="es-pp-select-btn es-pp-owned-btn" disabled aria-disabled="true">
                                <span class="dashicons dashicons-yes" style="font-size:16px;width:16px;height:16px;vertical-align:-2px"></span>
                                Current Plan
                            </button>
                        <?php elseif ( $stripe_ready || $is_guest ) : ?>
                            <button type="button" class="es-pp-select-btn"
                                    data-package-id="<?php echo (int) $pkg->id; ?>"
                                    <?php if ( $is_guest ) : ?>
                                        data-guest="1"
                                        data-login-url="<?php echo esc_url( $login_popup_url ); ?>"
                                    <?php else : ?>
                                        data-user-id="<?php echo (int) $student_data['id']; ?>"
                                        data-token="<?php echo esc_attr( $token ); ?>"
                                        data-name="<?php echo esc_attr( $student_data['name'] ); ?>"
                                        data-email="<?php echo esc_attr( $student_data['email'] ); ?>"
                                    <?php endif; ?>>
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
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/iv-logo.svg" alt="IVy" />
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

                <!-- ─── CARD DETAILS — split layout ───
                     Row 1: full-width Card Number
                     Row 2: Expiry + CVC side by side
                     (Stripe individual Elements mount into these.) -->
                <div class="es-pp-pay-field">
                    <label for="es-pp-card-number">Card Number</label>
                    <div id="es-pp-card-number" class="es-pp-card-element es-pp-card-row-full"></div>
                </div>

                <div class="es-pp-card-row-split">
                    <div class="es-pp-pay-field">
                        <label for="es-pp-card-expiry">Expiry</label>
                        <div id="es-pp-card-expiry" class="es-pp-card-element"></div>
                    </div>
                    <div class="es-pp-pay-field">
                        <label for="es-pp-card-cvc">CVC</label>
                        <div id="es-pp-card-cvc" class="es-pp-card-element"></div>
                    </div>
                </div>
                <div id="es-pp-card-errors" class="es-pp-card-errors" role="alert"></div>

                <!-- ─── BILLING ADDRESS ───
                     Indian regulations (RBI) require a billing address +
                     country on every card payment. We pre-fill from the
                     logged-in user's saved profile where available. -->
                <div class="es-pp-pay-divider">Billing Address</div>

                <div class="es-pp-pay-field">
                    <label for="es-pp-addr-line1">Address</label>
                    <input type="text" id="es-pp-addr-line1" placeholder="Street address" value="<?php echo esc_attr( $billing_prefill['line1'] ); ?>" autocomplete="address-line1" required />
                </div>

                <div class="es-pp-card-row-split">
                    <div class="es-pp-pay-field">
                        <label for="es-pp-addr-city">City</label>
                        <input type="text" id="es-pp-addr-city" placeholder="City" value="<?php echo esc_attr( $billing_prefill['city'] ); ?>" autocomplete="address-level2" required />
                    </div>
                    <div class="es-pp-pay-field">
                        <label for="es-pp-addr-state">State</label>
                        <input type="text" id="es-pp-addr-state" placeholder="State" value="<?php echo esc_attr( $billing_prefill['state'] ); ?>" autocomplete="address-level1" />
                    </div>
                </div>

                <div class="es-pp-card-row-split">
                    <div class="es-pp-pay-field">
                        <label for="es-pp-addr-postal">Postal Code</label>
                        <input type="text" id="es-pp-addr-postal" placeholder="PIN / ZIP" value="<?php echo esc_attr( $billing_prefill['postal'] ); ?>" autocomplete="postal-code" required />
                    </div>
                    <div class="es-pp-pay-field">
                        <label for="es-pp-addr-country">Country</label>
                        <select id="es-pp-addr-country" autocomplete="country" required>
                            <?php
                            $country_list = ES_Helpers::countries();
                            $sel_country  = $billing_prefill['country'] ?: 'IN';
                            foreach ( $country_list as $code => $info ) {
                                printf(
                                    '<option value="%s"%s>%s</option>',
                                    esc_attr( $code ),
                                    selected( $sel_country, $code, false ),
                                    esc_html( $info['name'] )
                                );
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="es-pp-pay-summary">
                    <div class="es-pp-pay-summary-label">Plan</div>
                    <div class="es-pp-pay-summary-value" id="es-pp-summary-plan">—</div>

                    <div class="es-pp-pay-summary-label">Monthly Price</div>
                    <div class="es-pp-pay-summary-value" id="es-pp-summary-monthly">—</div>

                    <div class="es-pp-pay-summary-label">Duration</div>
                    <div class="es-pp-pay-summary-value" id="es-pp-summary-months">—</div>

                    <div class="es-pp-pay-summary-label">Sessions</div>
                    <div class="es-pp-pay-summary-value" id="es-pp-summary-sessions">—</div>

                    <div class="es-pp-pay-summary-label">Total Payable</div>
                    <div class="es-pp-pay-summary-amount" id="es-pp-summary-amount">—</div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="es-pp-pay-submit">
                    <span class="es-pp-pay-submit-text">Activate Plan</span>
                </button>

                <div class="es-pp-pay-marks">
                    <span class="es-pp-mark">VISA</span>
                    <span class="es-pp-mark">Mastercard</span>
                    <span class="es-pp-mark">Amex</span>
                    <span class="es-pp-mark">RuPay</span>
                </div>
            </form>
        </aside>
        <div class="es-pp-overlay" id="es-pp-overlay" aria-hidden="true"></div>
    <?php endif; ?>
</div>

<?php if ( $is_guest_view ) : ?>
    <!-- ============ LOGIN MODAL (in-page, same window) ============
         Shown when a logged-out visitor clicks "Select This Plan".
         Reuses #es-login-form so the existing AJAX handler in frontend.js
         logs them in without leaving the page. -->
    <div class="es-login-modal" id="es-login-modal" aria-hidden="true">
        <div class="es-login-modal-overlay" id="es-login-modal-overlay"></div>
        <div class="es-login-modal-card" role="dialog" aria-modal="true" aria-labelledby="es-login-modal-title">
            <button type="button" class="es-login-modal-close" id="es-login-modal-close" aria-label="Close">×</button>

            <div class="es-login-modal-head">
                <?php if ( $brand_logo ) : ?>
                    <img src="<?php echo esc_url( $brand_logo ); ?>" alt="" class="es-login-modal-logo" />
                <?php else : ?>
                    <span class="es-login-modal-mark">◆</span>
                <?php endif; ?>
                <h2 class="es-login-modal-title" id="es-login-modal-title">Log in to continue</h2>
                <p class="es-login-modal-sub">Sign in to select your plan and complete your enrollment.</p>
            </div>

            <form id="es-login-form" class="es-login-modal-form" autocomplete="on" novalidate>
                <input type="hidden" name="es_login_nonce_field" value="<?php echo esc_attr( wp_create_nonce( 'es_login_nonce' ) ); ?>" />
                <input type="hidden" name="es_login_reload" value="1" />

                <div class="es-login-modal-field">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="you@example.com" autocomplete="username" />
                </div>

                <div class="es-login-modal-field">
                    <label>Password</label>
                    <div class="es-login-modal-pw">
                        <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password" />
                        <button type="button" class="es-fe-eye" aria-label="Toggle password"><span class="dashicons dashicons-visibility"></span></button>
                    </div>
                </div>

                <div class="es-login-modal-row">
                    <label class="es-login-modal-remember">
                        <input type="checkbox" name="remember" value="1" /> Remember me
                    </label>
                    <?php if ( $lostpw_url ) : ?>
                        <a href="<?php echo esc_url( $lostpw_url ); ?>" class="es-login-modal-forgot">Forgot password?</a>
                    <?php endif; ?>
                </div>

                <button type="submit" class="es-login-modal-submit es-fe-btn es-fe-btn-primary">Log in</button>

                <div class="es-fe-msg es-login-modal-msg" id="es-login-msg" style="display:none"></div>
            </form>

            <?php if ( $register_url ) : ?>
                <div class="es-login-modal-foot">
                    Don't have an account?
                    <a href="<?php echo esc_url( $register_url ); ?>">Sign up</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

