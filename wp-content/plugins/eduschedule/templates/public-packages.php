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
$atts_yearly_toggle = isset( $sc_atts['yearly_toggle'] ) ? strtolower( $sc_atts['yearly_toggle'] ) : ( ! empty( $settings['enable_yearly'] ) ? 'yes' : 'no' );
if ( ! in_array( $atts_default_cycle, array( 'monthly', 'yearly' ), true ) ) $atts_default_cycle = 'monthly';
$show_toggle = ( $atts_yearly_toggle === 'yes' );

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
 * ─────────────────────────────────────────────────────────────── */
if ( $user_id && $token && ES_Packages::validate_token( $user_id, $token ) ) {
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
            foreach ( $staged_ids as $sid ) {
                $p = ES_Packages::get( $sid );
                if ( $p && $p->is_active ) $packages[] = $p;
            }
        } else {
            $packages = ES_Packages::get_all( true );
        }
    }
} else {
    $packages = ES_Packages::get_all( true );
}

$stripe_ready    = class_exists( 'ES_Stripe' ) && ES_Stripe::is_enabled();
$yearly_discount = (float) ( $settings['yearly_discount'] ?? 0 );

$discount_label  = $yearly_discount > 0
    ? ' (' . rtrim( rtrim( number_format( $yearly_discount, 1 ), '0' ), '.' ) . '% Discount)'
    : '';
?>

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
            <?php if ( $valid_link && $student_data ) : ?>
                <span class="es-pp-brand-sep">|</span>
                <span class="es-pp-brand-sub">Decision Hub</span>
            <?php endif; ?>
        </div>

        <?php if ( $stripe_status === 'cancel' ) : ?>
            <div class="es-pkg-notice es-pkg-notice-warn">
                Payment was cancelled. You can try again whenever you're ready.
            </div>
        <?php endif; ?>

        <!-- Personalised heading -->
        <?php if ( $valid_link && $student_data ) : ?>
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

        <!-- Recommendation callout -->
        <?php if ( $valid_link && $recommendation_text ) : ?>
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
            <div class="es-pp-grid">
                <?php $i = 0; foreach ( $packages as $pkg ) : $i++;
                    $cur            = ! empty( $pkg->currency ) ? $pkg->currency : ( $settings['default_currency'] ?? 'INR' );
                    $monthly_price  = (float) $pkg->price;

                    // Yearly = monthly × 12 − global discount %
                    $yearly_price   = round( ( $monthly_price * 12 ) * ( 1 - ( $yearly_discount / 100 ) ), 2 );

                    $monthly_money_label = ES_Helpers::format_price( $monthly_price, $cur );
                    $yearly_money_label  = ES_Helpers::format_price( $yearly_price,  $cur );

                    $is_recommended = ( $valid_link && $recommended_idx > 0 && $i === $recommended_idx );
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
                                <?php echo esc_html( $yearly_money_label ); ?><span class="es-pp-period"> / <?php echo esc_html( ! empty( $sc_atts['period_unit_yearly'] ) ? $sc_atts['period_unit_yearly'] : 'semester' ); ?></span>
                            </span>
                        </div>

                        <?php if ( $valid_link && $student_data ) : ?>
                            <button type="button" class="es-pp-select-btn"
                                    data-package-id="<?php echo (int) $pkg->id; ?>"
                                    data-user-id="<?php echo (int) $student_data['id']; ?>"
                                    data-token="<?php echo esc_attr( $token ); ?>"
                                    data-name="<?php echo esc_attr( $student_data['name'] ); ?>"
                                    data-email="<?php echo esc_attr( $student_data['email'] ); ?>">
                                Select This Plan
                            </button>
                        <?php elseif ( $stripe_ready ) : ?>
                            <button type="button" class="es-pp-buy-btn"
                                    data-package-id="<?php echo (int) $pkg->id; ?>"
                                    data-package-name="<?php echo esc_attr( $pkg->package_name ); ?>">
                                Select This Plan
                            </button>
                        <?php else : ?>
                            <button type="button" class="es-pkg-contact-btn">Contact Us</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ( $show_toggle ) : ?>
                <div class="es-pp-toggle-wrap">
                    <span class="es-pp-toggle-label <?php echo $atts_default_cycle === 'monthly' ? 'is-active' : ''; ?>" data-side="monthly"><?php echo esc_html( $monthly_label_txt ); ?></span>
                    <button type="button" class="es-pp-switch <?php echo $atts_default_cycle === 'yearly' ? 'is-yearly' : ''; ?>" id="es-pp-cycle-switch" aria-label="Toggle billing cycle">
                        <span class="es-pp-switch-thumb"></span>
                    </button>
                    <span class="es-pp-toggle-label <?php echo $atts_default_cycle === 'yearly' ? 'is-active' : ''; ?>" data-side="yearly">
                        <?php echo esc_html( $semester_label_t . $discount_label ); ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- ============ PUBLIC BUYER MODAL (email collection → Stripe Checkout) ============ -->
    <?php if ( ! $valid_link && $stripe_ready ) : ?>
        <div class="es-pp-buy-modal" id="es-pp-buy-modal" aria-hidden="true">
            <div class="es-pp-buy-backdrop" id="es-pp-buy-backdrop"></div>
            <div class="es-pp-buy-card" role="dialog" aria-labelledby="es-pp-buy-title">
                <button type="button" class="es-pp-buy-close" id="es-pp-buy-close" aria-label="Close">×</button>
                <div class="es-pp-buy-head">
                    <div class="es-pp-buy-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <h2 id="es-pp-buy-title">Checkout</h2>
                    <p class="es-pp-buy-sub" id="es-pp-buy-plan-line">—</p>
                </div>
                <form id="es-pp-buy-form" novalidate>
                    <div class="es-pp-buy-field">
                        <label for="es-pp-buy-name">Your Name</label>
                        <input type="text" id="es-pp-buy-name" placeholder="Full name" required />
                    </div>
                    <div class="es-pp-buy-field">
                        <label for="es-pp-buy-email">Email Address</label>
                        <input type="email" id="es-pp-buy-email" placeholder="you@example.com" required />
                        <small class="es-pp-buy-hint">We'll send your receipt and account details here.</small>
                    </div>
                    <div class="es-pp-buy-error" id="es-pp-buy-error" role="alert"></div>
                    <button type="submit" class="es-pp-buy-submit" id="es-pp-buy-submit">
                        <span class="dashicons dashicons-lock"></span>
                        <span class="es-pp-buy-submit-text">Continue to secure payment</span>
                    </button>
                    <p class="es-pp-buy-fine">
                        You'll be redirected to Stripe to complete your purchase securely.
                    </p>
                </form>
            </div>
        </div>
    <?php endif; ?>

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

/* ─── Public Buyer Modal (email collection → Stripe Checkout) ─── */
.es-pp-buy-modal{
    position:fixed;inset:0;z-index:99999;display:none;
    align-items:center;justify-content:center;padding:20px;
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
}
.es-pp-buy-modal.is-open{display:flex}
.es-pp-buy-backdrop{
    position:absolute;inset:0;background:rgba(15,23,42,0.55);
    backdrop-filter:blur(2px);
}
.es-pp-buy-card{
    position:relative;background:#fff;border-radius:14px;
    max-width:440px;width:100%;padding:32px 28px 24px;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
    animation:esBuyIn .25s cubic-bezier(.4,0,.2,1);
}
@keyframes esBuyIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.es-pp-buy-close{
    position:absolute;top:10px;right:12px;width:32px;height:32px;
    border:0;background:transparent;font-size:26px;line-height:1;cursor:pointer;
    color:#94a3b8;border-radius:50%;transition:background .15s;
}
.es-pp-buy-close:hover{background:#f1f5f9;color:#1e293b}
.es-pp-buy-head{text-align:center;margin-bottom:20px}
.es-pp-buy-icon{
    width:52px;height:52px;border-radius:50%;background:#1e293b;color:#caa657;
    display:inline-flex;align-items:center;justify-content:center;
    margin-bottom:12px;
}
.es-pp-buy-icon .dashicons{font-size:24px;width:24px;height:24px}
.es-pp-buy-head h2{margin:0 0 6px;font-size:20px;font-weight:600;color:#1e293b}
.es-pp-buy-sub{margin:0;font-size:13px;color:#64748b}
.es-pp-buy-field{margin-bottom:14px}
.es-pp-buy-field label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;letter-spacing:.3px;text-transform:uppercase}
.es-pp-buy-field input{
    width:100%;padding:11px 13px;background:#fff;
    border:1px solid #cbd5e1;border-radius:8px;font-size:14px;color:#1e293b;
    box-sizing:border-box;transition:border-color .15s,box-shadow .15s;
}
.es-pp-buy-field input:focus{outline:none;border-color:#caa657;box-shadow:0 0 0 3px rgba(202,166,87,.18)}
.es-pp-buy-hint{display:block;font-size:11px;color:#94a3b8;margin-top:5px}
.es-pp-buy-error{
    color:#b91c1c;font-size:13px;min-height:18px;margin:4px 0 10px;
    background:transparent;
}
.es-pp-buy-submit{
    appearance:none;width:100%;padding:13px;background:#1e293b;color:#fff;
    border:0;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    transition:background .2s,transform .1s;
}
.es-pp-buy-submit:hover:not(:disabled){background:#0f172a}
.es-pp-buy-submit:active:not(:disabled){transform:translateY(1px)}
.es-pp-buy-submit:disabled{opacity:.7;cursor:wait}
.es-pp-buy-submit .dashicons{font-size:16px;width:16px;height:16px}
.es-pp-buy-fine{
    margin:12px 0 0;font-size:11px;color:#94a3b8;text-align:center;line-height:1.5;
}

/* Buy button (public mode) — matches "Select This Plan" styling */
.es-pp-buy-btn{
    appearance:none;background:#1e293b;color:#fff;border:0;width:100%;
    padding:11px 14px;border-radius:6px;font-size:13px;font-weight:600;
    cursor:pointer;transition:all .2s;letter-spacing:.2px;
}
.es-pp-buy-btn:hover{background:#0f172a}
.es-pp-card.is-featured .es-pp-buy-btn{background:#caa657;color:#1e293b}
.es-pp-card.is-featured .es-pp-buy-btn:hover{background:#b58e3e}
.es-pp-buy-btn:disabled{opacity:.7;cursor:wait}

/* Mobile */
@media (max-width:640px){
    .es-pp-main{padding:22px 18px 28px}
    .es-pp-pay-panel{width:100%;max-width:none}
    .es-pp-greeting h1{font-size:22px}
}
</style>
