<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ES_Stripe — Stripe Checkout integration
 *
 * Uses Stripe Checkout (hosted page) via REST API directly so we do not require
 * the Stripe PHP SDK. All requests are signed with the secret key over HTTPS.
 *
 * Flow:
 *   1) ES_Stripe::create_checkout_session( $user_id, $package_id, $billing_cycle )
 *      → returns a hosted checkout URL.
 *   2) Stripe redirects back to ?es_stripe=success&session_id=cs_xxx
 *   3) ES_Stripe::handle_return() verifies the session, marks payment paid,
 *      assigns package + valid_until, fires emails.
 *   4) Optional webhook at /?es_stripe_webhook=1 for async confirmation.
 */
class ES_Stripe {

    const API_BASE = 'https://api.stripe.com/v1';

    /** Are Stripe keys configured + enabled? */
    public static function is_enabled() {
        $s = ES_Helpers::settings();
        if ( empty( $s['stripe_enabled'] ) ) return false;
        $secret = self::secret_key();
        return ! empty( $secret );
    }

    public static function secret_key() {
        $s = ES_Helpers::settings();
        $mode = ! empty( $s['stripe_mode'] ) ? $s['stripe_mode'] : 'test';
        return $mode === 'live' ? trim( $s['stripe_live_secret'] ?? '' ) : trim( $s['stripe_test_secret'] ?? '' );
    }

    public static function publishable_key() {
        $s = ES_Helpers::settings();
        $mode = ! empty( $s['stripe_mode'] ) ? $s['stripe_mode'] : 'test';
        return $mode === 'live' ? trim( $s['stripe_live_pub_key'] ?? '' ) : trim( $s['stripe_test_pub_key'] ?? '' );
    }

    /**
     * Stripe expects amount in the smallest currency unit (cents, paise, etc.)
     * Zero-decimal currencies use the whole amount.
     */
    public static function to_minor_units( $amount, $currency ) {
        $zero_decimal = array( 'JPY', 'KRW', 'VND', 'CLP', 'ISK' ); // INR has paise → 2 decimals
        $amount = (float) $amount;
        if ( in_array( strtoupper( $currency ), $zero_decimal, true ) ) {
            return (int) round( $amount );
        }
        return (int) round( $amount * 100 );
    }

    /**
     * Create a Stripe Checkout Session and return the URL.
     *
     * @param int    $user_id
     * @param int    $package_id
     * @param string $billing_cycle 'monthly' | 'yearly'
     * @return array|WP_Error  ['url' => ..., 'session_id' => ...]
     */
    public static function create_checkout_session( $user_id, $package_id, $billing_cycle = 'monthly' ) {
        if ( ! self::is_enabled() ) {
            return new WP_Error( 'stripe_disabled', 'Stripe is not enabled. Please configure it in EduSchedule → Settings.' );
        }

        $pkg = ES_Packages::get( $package_id );
        if ( ! $pkg ) return new WP_Error( 'no_pkg', 'Package not found.' );

        $user = get_userdata( $user_id );
        if ( ! $user ) return new WP_Error( 'no_user', 'User not found.' );

        $currency = ! empty( $pkg->currency ) ? strtoupper( $pkg->currency ) : 'INR';
        $billing_cycle = in_array( $billing_cycle, array( 'monthly', 'yearly' ), true ) ? $billing_cycle : 'monthly';

        // Resolve final amount (yearly discount logic — ONE TIME payment)
        $amount = self::resolve_price( $pkg, $billing_cycle );

        if ( $amount <= 0 ) {
            return new WP_Error( 'bad_amount', 'Invalid package price.' );
        }

        $minor = self::to_minor_units( $amount, $currency );

        $description = $pkg->sub_heading ?: ( $pkg->tagline ?: '' );

        // Build return URLs
        $return_base = self::get_packages_page_url();
        $success_url = add_query_arg( array(
            'es_stripe' => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}', // Stripe substitutes this
        ), $return_base );
        $cancel_url = add_query_arg( array(
            'es_stripe' => 'cancel',
        ), $return_base );

        $body = array(
            'mode'                                 => 'payment',
            'payment_method_types[]'               => 'card',
            'line_items[0][quantity]'              => 1,
            'line_items[0][price_data][currency]'  => strtolower( $currency ),
            'line_items[0][price_data][unit_amount]' => $minor,
            'line_items[0][price_data][product_data][name]'        => $pkg->package_name . ' (' . ucfirst( $billing_cycle ) . ')',
            'line_items[0][price_data][product_data][description]' => $description,
            'customer_email'                       => $user->user_email,
            'client_reference_id'                  => 'u' . $user_id . '_p' . $package_id . '_' . $billing_cycle,
            'metadata[user_id]'                    => (string) $user_id,
            'metadata[package_id]'                 => (string) $package_id,
            'metadata[billing_cycle]'              => $billing_cycle,
            'metadata[amount]'                     => (string) $amount,
            'metadata[currency]'                   => $currency,
            'success_url'                          => $success_url,
            'cancel_url'                           => $cancel_url,
        );

        $resp = self::request( 'POST', '/checkout/sessions', $body );
        if ( is_wp_error( $resp ) ) return $resp;

        if ( empty( $resp['id'] ) || empty( $resp['url'] ) ) {
            return new WP_Error( 'stripe_no_session', 'Stripe did not return a checkout session URL.' );
        }

        // Record pending payment
        global $wpdb;
        $now = current_time( 'mysql' );
        $snap = self::payment_session_snapshot( $pkg, $billing_cycle );
        $flow_type = ES_Packages::get_staged_flow( $user_id );
        $latest    = ES_Packages::get_latest_lead_outcome( $user_id );
        $group_id  = ( $flow_type === 'group' && $latest && ! empty( $latest->group_id ) ) ? (int) $latest->group_id : 0;
        if ( $group_id ) {
            $course_ids  = ES_Packages::get_group_course_ids( $group_id );
            $course_id   = ES_Packages::first_course_id( $course_ids );
            $course_name = ES_Packages::course_names_str( $course_ids );
            if ( $course_name === '' && $latest && ! empty( $latest->course_name ) ) $course_name = $latest->course_name;
        } else {
            $flow_type   = '1to1';
            $course_ids  = ES_Packages::get_student_course_ids( $user_id );
            $course_id   = ES_Packages::first_course_id( $course_ids );
            $course_name = ES_Packages::course_name( $course_id );
        }
        $wpdb->insert( $wpdb->prefix . 'es_payments', array_merge( array(
            'user_id'            => $user_id,
            'course_id'          => $course_id ?: null,
            'course_name'        => $course_name,
            'flow_type'          => $flow_type,
            'group_id'           => $group_id ?: null,
            'package_id'         => $package_id,
            'amount'             => $amount,
            'currency'           => $currency,
            'billing_cycle'      => $billing_cycle,
            'gateway'            => 'stripe',
            'gateway_session_id' => $resp['id'],
            'status'             => 'pending',
            'meta'               => wp_json_encode( array( 'created_via' => 'checkout_session', 'flow_type' => $flow_type, 'group_id' => $group_id ) ),
            'created_at'         => $now,
            'updated_at'         => $now,
        ), $snap ) );

        return array( 'url' => $resp['url'], 'session_id' => $resp['id'] );
    }

    /**
     * GUEST checkout — no WP user required up front. Used by the public
     * pricing page so any visitor can buy a package.
     *
     * Behaviour:
     *   - If a WP user already exists with the given email, that user is used.
     *   - Otherwise the user is created LATER, after Stripe confirms payment
     *     (in finalize_session). We just stash the email + name in metadata.
     *
     * @param int    $package_id
     * @param string $billing_cycle  'monthly' | 'yearly'
     * @param string $email          buyer email (required)
     * @param string $name           buyer name (optional, used for new accounts)
     * @return array|WP_Error  ['url' => ..., 'session_id' => ...]
     */
    public static function create_checkout_session_guest( $package_id, $billing_cycle = 'monthly', $email = '', $name = '' ) {
        if ( ! self::is_enabled() ) {
            return new WP_Error( 'stripe_disabled', 'Stripe is not enabled. Please configure it in EduSchedule → Settings.' );
        }

        $email = sanitize_email( $email );
        if ( ! $email || ! is_email( $email ) ) {
            return new WP_Error( 'bad_email', 'A valid email is required to continue.' );
        }

        $pkg = ES_Packages::get( $package_id );
        if ( ! $pkg ) return new WP_Error( 'no_pkg', 'Package not found.' );
        if ( empty( $pkg->is_active ) ) {
            return new WP_Error( 'inactive', 'This package is not available.' );
        }

        $currency = ! empty( $pkg->currency ) ? strtoupper( $pkg->currency ) : 'INR';
        $billing_cycle = in_array( $billing_cycle, array( 'monthly', 'yearly' ), true ) ? $billing_cycle : 'monthly';

        $amount = self::resolve_price( $pkg, $billing_cycle );
        if ( $amount <= 0 ) return new WP_Error( 'bad_amount', 'Invalid package price.' );

        $minor = self::to_minor_units( $amount, $currency );

        $description = $pkg->sub_heading ?: ( $pkg->tagline ?: '' );

        // If a user already exists with this email, use their ID; otherwise 0 (created on success).
        $existing = get_user_by( 'email', $email );
        $user_id  = $existing ? (int) $existing->ID : 0;

        $return_base = self::get_packages_page_url();
        $success_url = add_query_arg( array(
            'es_stripe'  => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ), $return_base );
        $cancel_url  = add_query_arg( array( 'es_stripe' => 'cancel' ), $return_base );

        $body = array(
            'mode'                                                  => 'payment',
            'payment_method_types[]'                                => 'card',
            'line_items[0][quantity]'                               => 1,
            'line_items[0][price_data][currency]'                   => strtolower( $currency ),
            'line_items[0][price_data][unit_amount]'                => $minor,
            'line_items[0][price_data][product_data][name]'         => $pkg->package_name . ' (' . ucfirst( $billing_cycle ) . ')',
            'line_items[0][price_data][product_data][description]'  => $description,
            'customer_email'                                        => $email,
            'client_reference_id'                                   => 'guest_p' . $package_id . '_' . $billing_cycle,
            'metadata[user_id]'                                     => (string) $user_id,
            'metadata[package_id]'                                  => (string) $package_id,
            'metadata[billing_cycle]'                               => $billing_cycle,
            'metadata[amount]'                                      => (string) $amount,
            'metadata[currency]'                                    => $currency,
            'metadata[flow]'                                        => 'public_pricing',
            'metadata[buyer_email]'                                 => $email,
            'metadata[buyer_name]'                                  => sanitize_text_field( $name ),
            'success_url'                                           => $success_url,
            'cancel_url'                                            => $cancel_url,
        );

        $resp = self::request( 'POST', '/checkout/sessions', $body );
        if ( is_wp_error( $resp ) ) return $resp;

        if ( empty( $resp['id'] ) || empty( $resp['url'] ) ) {
            return new WP_Error( 'stripe_no_session', 'Stripe did not return a checkout session URL.' );
        }

        // Record pending payment (user_id may be 0 — filled in on finalize).
        global $wpdb;
        $now = current_time( 'mysql' );
        $snap = self::payment_session_snapshot( $pkg, $billing_cycle );
        $wpdb->insert( $wpdb->prefix . 'es_payments', array_merge( array(
            'user_id'            => $user_id,
            'package_id'         => $package_id,
            'amount'             => $amount,
            'currency'           => $currency,
            'billing_cycle'      => $billing_cycle,
            'gateway'            => 'stripe',
            'gateway_session_id' => $resp['id'],
            'status'             => 'pending',
            'meta'               => wp_json_encode( array(
                'created_via' => 'checkout_session_public',
                'email'       => $email,
                'name'        => $name,
            ) ),
            'created_at'         => $now,
            'updated_at'         => $now,
        ), $snap ) );

        return array( 'url' => $resp['url'], 'session_id' => $resp['id'] );
    }

    /**
     * Effective term (in months) that a billing cycle bills + grants.
     *
     * - monthly cycle  → the package's own duration (`months`).
     * - yearly/discounted cycle (v4.3.3) → the package's configured
     *   `discount_months`. This is the change requested: when the year/discount
     *   toggle is ON the plan is billed for the discount months (e.g. 5 months),
     *   NOT the package's default duration. If no discount_months is configured
     *   we fall back to the package's own months so nothing breaks.
     *
     * Everything that needs "how many months does this purchase cover" — the
     * charged amount, the granted access window (valid_until) and the session
     * snapshot — funnels through here so the three can never drift apart.
     */
    public static function effective_term_months( $pkg, $billing_cycle = 'monthly' ) {
        $pkg_months = max( 1, (int) ( $pkg->months ?? 1 ) );
        if ( $billing_cycle !== 'yearly' ) {
            return $pkg_months;
        }
        $disc_months = (int) ( $pkg->discount_months ?? 0 );
        // For the yearly/discounted toggle, use the configured discount months
        // exactly as entered. When unset or invalid, fall back to package months.
        $disc_months = max( 0, $disc_months );
        return $disc_months > 0 ? $disc_months : $pkg_months;
    }

    /**
     * Resolve the final price for the package given a billing cycle.
     * - monthly: price as stored
     * - yearly:  if yearly_price > 0, use it; otherwise price * 12 with optional global yearly_discount %
     */
    public static function resolve_price( $pkg, $billing_cycle = 'monthly' ) {
        // ── New monthly model ──
        // The package's `price` column already holds the FULL total
        // (monthly_price × months). We charge that full amount up front.
        $months        = max( 1, (int) ( $pkg->months ?? 1 ) );
        $monthly_price = (float) ( $pkg->monthly_price ?? 0 );

        if ( $monthly_price > 0 ) {
            $total = (float) $pkg->price;
            if ( $total <= 0 ) {
                $total = round( $monthly_price * $months, 2 );
            }

            // Discounted ("yearly") cycle (v4.3.3):
            //   Bill ONLY for the configured discount_months at the monthly rate,
            //   then subtract the discount on those same months:
            //     term  = discount_months (falls back to package months if unset)
            //     total = (term × monthly) − (monthly × discount_months × discount% / 100)
            //   e.g. 12-month package @ 20000/mo, "5 discount months", 20% off:
            //     5 × 20000 − (20000 × 5 × 20)/100 = 100000 − 20000 = 80000
            //   The student is billed for — and receives access/sessions for —
            //   the discount months, not the package's full default duration.
            if ( $billing_cycle === 'yearly' ) {
                $year_months = self::effective_term_months( $pkg, 'yearly' ); // = discount_months (or pkg months fallback)
                $discount    = max( 0, min( 100, (float) ( $pkg->discount_percent ?? 0 ) ) );
                $disc_months = $year_months;

                $gross    = $monthly_price * $year_months;
                $discount_amount = ( $discount > 0 && $disc_months > 0 )
                    ? ( $monthly_price * $disc_months * $discount / 100 )
                    : 0;
                $total = round( max( 0, $gross - $discount_amount ), 2 );
            }
            return $total;
        }

        // ── Legacy fallback (packages created before the monthly update) ──
        $price = (float) $pkg->price;

        if ( $billing_cycle !== 'yearly' ) {
            return $price;
        }

        $yearly = isset( $pkg->yearly_price ) ? (float) $pkg->yearly_price : 0;
        if ( $yearly > 0 ) {
            return $yearly;
        }

        // Legacy discounted purchase: bill ONLY the discount_months at the
        // monthly rate, discount applied to those months (same model as above).
        $year_months = self::effective_term_months( $pkg, 'yearly' );
        $discount    = max( 0, min( 100, (float) ( $pkg->discount_percent ?? 0 ) ) );
        $disc_months = $year_months;
        if ( $discount > 0 && $disc_months > 0 ) {
            $gross           = $price * $year_months;
            $discount_amount = $price * $disc_months * $discount / 100;
            return round( max( 0, $gross - $discount_amount ), 2 );
        }
        // No discount configured → bill the effective term at the monthly rate.
        return round( $price * $year_months, 2 );
    }

    /**
     * Number of months the "yearly" / discounted cycle bills for. As of v4.3.3
     * the discounted toggle bills the package's DISCOUNT months (so a 12-month
     * package with "5 discount months" is billed + granted for 5 months). The
     * legacy no-arg call returns 12 for safety.
     */
    public static function yearly_billed_months( $pkg = null ) {
        if ( $pkg ) {
            return self::effective_term_months( $pkg, 'yearly' );
        }
        return 12;
    }

    /**
     * Build the session-term snapshot to store on a payment row at creation
     * time, so later package edits don't change what a student already bought.
     *
     * @param object $pkg            The package row.
     * @param string $billing_cycle  'monthly' grants the package's own months;
     *                               'yearly' grants the discount months (v4.3.3).
     */
    public static function payment_session_snapshot( $pkg, $billing_cycle = 'monthly' ) {
        $monthly_price = (float) ( $pkg->monthly_price ?? 0 );
        if ( $monthly_price <= 0 ) {
            // Legacy package: stored price was the monthly figure.
            $monthly_price = (float) $pkg->price;
        }
        $monthly_limit  = (int) ( $pkg->monthly_session_limit ?? 0 );

        // v4.3.3: a monthly purchase grants the package's OWN duration; a
        // discounted ("yearly") purchase grants only the DISCOUNT months — the
        // same term it is billed for. effective_term_months() is the single
        // source of truth so price, access window and sessions stay in lockstep.
        $months = self::effective_term_months( $pkg, $billing_cycle );

        // Work out the total sessions granted. Prefer monthly_limit × months.
        // If no monthly limit is set, fall back to the package's stored
        // total_sessions.
        $pkg_total = (int) ( $pkg->total_sessions ?? 0 );
        if ( $monthly_limit > 0 ) {
            $total_sessions = $monthly_limit * $months;
        } elseif ( $pkg_total > 0 ) {
            $total_sessions = $pkg_total;
        } else {
            $total_sessions = 0;
        }

        return array(
            'package_name'          => $pkg->package_name ?? '',
            'monthly_price'         => $monthly_price,
            'months'                => $months,
            'monthly_session_limit' => $monthly_limit,
            'total_sessions'        => $total_sessions,
            'used_sessions'         => 0,
        );
    }

    /**
     * Safety net: if a payment row somehow ended up with total_sessions = 0
     * (e.g. it was created before the snapshot logic was fixed, or the package
     * had no session limit at the time), backfill the session terms from the
     * package now, at the moment the payment is confirmed. This guarantees a
     * paid student actually receives their sessions. Returns the (possibly
     * updated) row.
     */
    public static function backfill_sessions_if_missing( $row ) {
        if ( ! $row ) return $row;
        if ( (int) ( $row->total_sessions ?? 0 ) > 0 ) return $row; // already fine

        $pkg = ES_Packages::get( $row->package_id );
        if ( ! $pkg ) return $row;

        $snap = self::payment_session_snapshot( $pkg, $row->billing_cycle ?: 'monthly' );
        if ( (int) $snap['total_sessions'] <= 0 ) return $row; // nothing to grant

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'es_payments',
            array(
                'months'                => (int) $snap['months'],
                'monthly_session_limit' => (int) $snap['monthly_session_limit'],
                'total_sessions'        => (int) $snap['total_sessions'],
                'updated_at'            => current_time( 'mysql' ),
            ),
            array( 'id' => (int) $row->id )
        );

        // Reflect on the in-memory row too.
        $row->months                = (int) $snap['months'];
        $row->monthly_session_limit = (int) $snap['monthly_session_limit'];
        $row->total_sessions        = (int) $snap['total_sessions'];
        return $row;
    }

    /* ============================================================
     *  INLINE STRIPE ELEMENTS — PaymentIntent flow
     *  Used by the on-page payment form (no redirect to Stripe).
     * ============================================================ */

    /**
     * Create a PaymentIntent and a pending payment row.
     *
     * @return array|WP_Error  ['client_secret' => ..., 'amount' => ..., 'currency' => ..., 'payment_intent_id' => ...]
     */
    public static function create_payment_intent( $user_id, $package_id, $billing_cycle = 'monthly', $name = '', $email = '', $addr = array() ) {
        if ( ! self::is_enabled() ) {
            return new WP_Error( 'stripe_disabled', 'Stripe is not enabled. Please configure it in EduSchedule → Settings.' );
        }

        $pkg = ES_Packages::get( $package_id );
        if ( ! $pkg ) return new WP_Error( 'no_pkg', 'Package not found.' );

        if ( ! in_array( $billing_cycle, array( 'monthly', 'yearly' ), true ) ) {
            $billing_cycle = 'monthly';
        }

        $currency = ! empty( $pkg->currency ) ? strtoupper( $pkg->currency ) : 'INR';
        $amount   = self::resolve_price( $pkg, $billing_cycle );
        if ( $amount <= 0 ) return new WP_Error( 'bad_amount', 'Invalid package price.' );

        $minor = self::to_minor_units( $amount, $currency );

        // Normalise address
        $addr = wp_parse_args( (array) $addr, array(
            'line1' => '', 'city' => '', 'state' => '', 'postal' => '', 'country' => '',
        ) );

        $body = array(
            'amount'                 => $minor,
            'currency'               => strtolower( $currency ),
            'description'            => $pkg->package_name . ' (' . ucfirst( $billing_cycle ) . ')',
            'payment_method_types[]' => 'card',
            'metadata[user_id]'      => (string) $user_id,
            'metadata[package_id]'   => (string) $package_id,
            'metadata[billing_cycle]'=> $billing_cycle,
            'metadata[amount]'       => (string) $amount,
            'metadata[currency]'     => $currency,
            'metadata[flow]'         => 'inline_elements',
        );
        if ( $email ) $body['receipt_email'] = $email;

        // Attach billing details (incl. address) to the PaymentIntent. This is
        // required for Indian (INR) card payments under RBI/Stripe rules and
        // also improves acceptance rates elsewhere.
        if ( $name )            $body['shipping[name]']               = $name;
        if ( $addr['line1'] )   $body['shipping[address][line1]']     = $addr['line1'];
        if ( $addr['city'] )    $body['shipping[address][city]']      = $addr['city'];
        if ( $addr['state'] )   $body['shipping[address][state]']     = $addr['state'];
        if ( $addr['postal'] )  $body['shipping[address][postal_code]'] = $addr['postal'];
        if ( $addr['country'] ) $body['shipping[address][country]']   = $addr['country'];

        $resp = self::request( 'POST', '/payment_intents', $body );
        if ( is_wp_error( $resp ) ) return $resp;

        if ( empty( $resp['id'] ) || empty( $resp['client_secret'] ) ) {
            return new WP_Error( 'stripe_no_pi', 'Stripe did not return a PaymentIntent.' );
        }

        // Record pending payment
        global $wpdb;
        $now = current_time( 'mysql' );
        $snap = self::payment_session_snapshot( $pkg, $billing_cycle );
        $flow_type = ES_Packages::get_staged_flow( $user_id );
        $latest    = ES_Packages::get_latest_lead_outcome( $user_id );
        $group_id  = ( $flow_type === 'group' && $latest && ! empty( $latest->group_id ) ) ? (int) $latest->group_id : 0;
        if ( $group_id ) {
            $course_ids  = ES_Packages::get_group_course_ids( $group_id );
            $course_id   = ES_Packages::first_course_id( $course_ids );
            $course_name = ES_Packages::course_names_str( $course_ids );
            if ( $course_name === '' && $latest && ! empty( $latest->course_name ) ) $course_name = $latest->course_name;
        } else {
            $flow_type   = '1to1';
            $course_ids  = ES_Packages::get_student_course_ids( $user_id );
            $course_id   = ES_Packages::first_course_id( $course_ids );
            $course_name = ES_Packages::course_name( $course_id );
        }
        $wpdb->insert( $wpdb->prefix . 'es_payments', array_merge( array(
            'user_id'            => (int) $user_id,
            'course_id'          => $course_id ?: null,
            'course_name'        => $course_name,
            'flow_type'          => $flow_type,
            'group_id'           => $group_id ?: null,
            'package_id'         => (int) $package_id,
            'amount'             => $amount,
            'currency'           => $currency,
            'billing_cycle'      => $billing_cycle,
            'gateway'            => 'stripe',
            'gateway_session_id' => $resp['id'],          // we re-use this column for PaymentIntent id
            'status'             => 'pending',
            'meta'               => wp_json_encode( array(
                'created_via' => 'payment_intent',
                'flow_type'   => $flow_type,
                'group_id'    => $group_id,
                'name'        => $name,
                'email'       => $email,
                'address'     => $addr,
            ) ),
            'created_at'         => $now,
            'updated_at'         => $now,
        ), $snap ) );

        return array(
            'client_secret'     => $resp['client_secret'],
            'payment_intent_id' => $resp['id'],
            'amount'            => $amount,
            'currency'          => $currency,
            'amount_label'      => ES_Helpers::format_price( $amount, $currency ),
            'billing_cycle'     => $billing_cycle,
        );
    }

    /**
     * Finalize an inline PaymentIntent after the JS confirms it.
     * Verifies status with Stripe, marks our payment paid, assigns package,
     * fires confirmation emails.
     *
     * @return array|WP_Error  ['amount_label' => ..., 'package_name' => ..., 'valid_until' => ...]
     */
    public static function finalize_payment_intent( $payment_intent_id ) {
        global $wpdb;

        if ( empty( $payment_intent_id ) ) {
            return new WP_Error( 'bad_request', 'Missing payment_intent_id.' );
        }

        $pi = self::request( 'GET', '/payment_intents/' . urlencode( $payment_intent_id ) );
        if ( is_wp_error( $pi ) ) return $pi;

        if ( empty( $pi['status'] ) || $pi['status'] !== 'succeeded' ) {
            return new WP_Error( 'not_paid', 'Payment is not complete yet. Status: ' . ( $pi['status'] ?? 'unknown' ) );
        }

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}es_payments WHERE gateway_session_id = %s",
            $payment_intent_id
        ) );
        if ( ! $row ) return new WP_Error( 'no_row', 'Payment record not found.' );

        // Idempotent — if already paid, return success info
        if ( $row->status === 'paid' ) {
            $pkg_existing = ES_Packages::get( $row->package_id );
            return array(
                'amount_label' => ES_Helpers::format_price( $row->amount, $row->currency ),
                'package_name' => $pkg_existing ? $pkg_existing->package_name : '',
                'valid_until'  => $row->valid_until ? date_i18n( 'F j, Y', strtotime( $row->valid_until ) ) : '',
                'already'      => true,
            );
        }

        $valid_from  = current_time( 'mysql' );
        $row_months  = max( 1, (int) ( $row->months ?? 1 ) );
        $valid_until = self::compute_valid_until( $row->billing_cycle, $valid_from, $row_months );

        $wpdb->update(
            $wpdb->prefix . 'es_payments',
            array(
                'status'             => 'paid',
                'gateway_payment_id' => $pi['id'],
                'valid_from'         => $valid_from,
                'valid_until'        => $valid_until,
                'updated_at'         => current_time( 'mysql' ),
            ),
            array( 'id' => $row->id )
        );

        // Refresh row so emails get the latest values
        $row->status      = 'paid';
        $row->valid_from  = $valid_from;
        $row->valid_until = $valid_until;

        // Safety net: ensure the student actually receives their sessions even
        // if the snapshot stored at creation was empty (#1 — "after buy, no
        // sessions in 1:1 section").
        $row = self::backfill_sessions_if_missing( $row );

        // 1:1 package ownership is derived from payments, not user meta.
        delete_user_meta( $row->user_id, ES_Packages::META_PACKAGE_ID );
        if ( ! empty( $row->group_id ) ) {
            update_user_meta( $row->user_id, ES_Packages::META_HAS_GROUP, 1 );
            update_user_meta( $row->user_id, ES_Packages::META_GROUP_ID, (int) $row->group_id );
            ES_Packages::add_user_to_group( (int) $row->group_id, (int) $row->user_id );
        }

        // Record as a lead-package link with current outcome
        $existing_outcome = ES_Packages::get_latest_lead_outcome( $row->user_id );
        $group_id      = ! empty( $row->group_id ) ? (int) $row->group_id : ( $existing_outcome && $existing_outcome->group_id ? (int) $existing_outcome->group_id : null );
        $outcome_label = ( ! empty( $row->flow_type ) && $row->flow_type === 'group' ) || $group_id ? 'Group Student' : ( $existing_outcome ? $existing_outcome->outcome : '1:1 Student' );

        ES_Packages::link_lead_to_package(
            (int) $row->user_id,
            (int) $row->package_id,
            $outcome_label,
            'Payment received via Stripe (' . $row->billing_cycle . ', inline)',
            $group_id
        );
        if ( $group_id && $outcome_label === 'Group Student' ) {
            $g_update = array( 'package_id' => (int) $row->package_id );
            if ( ! empty( $row->course_id ) ) $g_update['course_ids'] = (string) (int) $row->course_id;
            ES_Packages::update_group( (int) $group_id, $g_update );
        }

        // Clear token + staged so the link can't be re-used
        ES_Packages::clear_token( $row->user_id );
        ES_Packages::clear_staged_packages( $row->user_id );

        // Fire confirmation emails
        self::send_payment_emails( $row );

        $pkg_now = ES_Packages::get( $row->package_id );
        return array(
            'amount_label' => ES_Helpers::format_price( $row->amount, $row->currency ),
            'package_name' => $pkg_now ? $pkg_now->package_name : '',
            'valid_until'  => $valid_until ? date_i18n( 'F j, Y', strtotime( $valid_until ) ) : '',
            'already'      => false,
        );
    }


    /**
     * Handle return URL from Stripe Checkout (?es_stripe=success&session_id=...)
     * Called on template_redirect.
     */
    public static function handle_return() {
        if ( empty( $_GET['es_stripe'] ) ) return;
        $action = sanitize_text_field( wp_unslash( $_GET['es_stripe'] ) );

        if ( $action === 'cancel' ) {
            // Just let the package page show with a notice; the page reads $_GET.
            return;
        }

        if ( $action !== 'success' ) return;
        $session_id = isset( $_GET['session_id'] ) ? sanitize_text_field( wp_unslash( $_GET['session_id'] ) ) : '';
        if ( ! $session_id ) return;

        self::finalize_session( $session_id );
    }

    /**
     * Verify a checkout session, mark our payment paid, and apply package.
     *
     * @param string $session_id
     * @return bool
     */
    public static function finalize_session( $session_id ) {
        global $wpdb;

        $session = self::request( 'GET', '/checkout/sessions/' . urlencode( $session_id ) );
        if ( is_wp_error( $session ) ) return false;

        $paid = ! empty( $session['payment_status'] ) && $session['payment_status'] === 'paid';

        // Find our payment row
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}es_payments WHERE gateway_session_id = %s",
            $session_id
        ) );

        if ( ! $row ) return false;

        // Avoid re-processing
        if ( $row->status === 'paid' ) return true;

        if ( ! $paid ) {
            $wpdb->update(
                $wpdb->prefix . 'es_payments',
                array( 'status' => 'failed', 'updated_at' => current_time( 'mysql' ) ),
                array( 'id' => $row->id )
            );
            return false;
        }

        // Mark paid + set validity
        $valid_from  = current_time( 'mysql' );
        $row_months  = max( 1, (int) ( $row->months ?? 1 ) );
        $valid_until = self::compute_valid_until( $row->billing_cycle, $valid_from, $row_months );

        /* ── GUEST CHECKOUT: row may have user_id=0. Create/find a WP user
         *    from the email captured at checkout time so package can be assigned. */
        if ( empty( $row->user_id ) ) {
            $meta_in = ! empty( $row->meta ) ? json_decode( $row->meta, true ) : array();
            $email   = '';
            $name    = '';
            if ( is_array( $meta_in ) ) {
                $email = isset( $meta_in['email'] ) ? sanitize_email( $meta_in['email'] ) : '';
                $name  = isset( $meta_in['name'] )  ? sanitize_text_field( $meta_in['name'] )  : '';
            }
            // Stripe also echoes customer_email on the session — prefer that if present.
            if ( ! $email && ! empty( $session['customer_details']['email'] ) ) {
                $email = sanitize_email( $session['customer_details']['email'] );
            }
            if ( ! $email && ! empty( $session['customer_email'] ) ) {
                $email = sanitize_email( $session['customer_email'] );
            }

            if ( $email && is_email( $email ) ) {
                $user = get_user_by( 'email', $email );
                if ( ! $user ) {
                    $username = self::generate_unique_username( $email );
                    $password = wp_generate_password( 16, true, false );
                    $new_id   = wp_create_user( $username, $password, $email );
                    if ( ! is_wp_error( $new_id ) ) {
                        if ( $name ) {
                            wp_update_user( array(
                                'ID'           => $new_id,
                                'display_name' => $name,
                                'first_name'   => $name,
                            ) );
                        }
                        // Send the standard WP "set your password" notification.
                        wp_new_user_notification( $new_id, null, 'user' );
                        $row->user_id = (int) $new_id;
                    }
                } else {
                    $row->user_id = (int) $user->ID;
                }
            }
        }

        if ( empty( $row->user_id ) ) {
            // Couldn't determine a user — record the payment but bail before assignment.
            $wpdb->update(
                $wpdb->prefix . 'es_payments',
                array(
                    'status'             => 'paid',
                    'gateway_payment_id' => ! empty( $session['payment_intent'] ) ? $session['payment_intent'] : '',
                    'valid_from'         => $valid_from,
                    'valid_until'        => $valid_until,
                    'updated_at'         => current_time( 'mysql' ),
                ),
                array( 'id' => $row->id )
            );
            return true;
        }

        $wpdb->update(
            $wpdb->prefix . 'es_payments',
            array(
                'status'             => 'paid',
                'user_id'            => $row->user_id,
                'gateway_payment_id' => ! empty( $session['payment_intent'] ) ? $session['payment_intent'] : '',
                'valid_from'         => $valid_from,
                'valid_until'        => $valid_until,
                'updated_at'         => current_time( 'mysql' ),
            ),
            array( 'id' => $row->id )
        );

        // 1:1 package ownership is derived from payments, not user meta.
        delete_user_meta( $row->user_id, ES_Packages::META_PACKAGE_ID );
        if ( ! empty( $row->group_id ) ) {
            update_user_meta( $row->user_id, ES_Packages::META_HAS_GROUP, 1 );
            update_user_meta( $row->user_id, ES_Packages::META_GROUP_ID, (int) $row->group_id );
            ES_Packages::add_user_to_group( (int) $row->group_id, (int) $row->user_id );
        }

        // Safety net: grant sessions even if the snapshot was empty (#1).
        $row = self::backfill_sessions_if_missing( $row );

        // Record as a lead-package link with "Paid" outcome
        $existing_outcome = ES_Packages::get_latest_lead_outcome( $row->user_id );
        $group_id      = ! empty( $row->group_id ) ? (int) $row->group_id : ( $existing_outcome && $existing_outcome->group_id ? (int) $existing_outcome->group_id : null );
        $outcome_label = ( ! empty( $row->flow_type ) && $row->flow_type === 'group' ) || $group_id ? 'Group Student' : ( $existing_outcome ? $existing_outcome->outcome : '1:1 Student' );

        ES_Packages::link_lead_to_package(
            $row->user_id,
            (int) $row->package_id,
            $outcome_label,
            'Payment received via Stripe (' . $row->billing_cycle . ')',
            $group_id
        );
        if ( $group_id && $outcome_label === 'Group Student' ) {
            $g_update = array( 'package_id' => (int) $row->package_id );
            if ( ! empty( $row->course_id ) ) $g_update['course_ids'] = (string) (int) $row->course_id;
            ES_Packages::update_group( (int) $group_id, $g_update );
        }

        // Clear token + staged
        ES_Packages::clear_token( $row->user_id );
        ES_Packages::clear_staged_packages( $row->user_id );

        // Send emails
        self::send_payment_emails( $row );

        return true;
    }

    public static function compute_valid_until( $billing_cycle, $from_mysql, $months = 1 ) {
        try {
            $dt = new DateTime( $from_mysql, new DateTimeZone( 'UTC' ) );
            // Grant access for the full purchased duration. `$months` comes from
            // the package's `months` column (snapshotted on the payment row).
            $months = max( 1, (int) $months );
            $dt->modify( '+' . $months . ' months' );
            return $dt->format( 'Y-m-d H:i:s' );
        } catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * Send confirmation emails to student + admin after successful payment.
     */
    public static function send_payment_emails( $payment_row ) {
        $user = get_userdata( $payment_row->user_id );
        $pkg  = ES_Packages::get( $payment_row->package_id );
        if ( ! $user || ! $pkg ) return;

        $amount_label = ES_Helpers::format_price( $payment_row->amount, $payment_row->currency );
        $cycle_label  = ucfirst( $payment_row->billing_cycle );
        $valid_to     = $payment_row->valid_until ? date_i18n( 'F j, Y', strtotime( $payment_row->valid_until ) ) : '';

        // Student email (HTML)
        $subject = 'Payment Confirmed — ' . $pkg->package_name;
        ob_start(); ?>
        <div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f7f7f9;padding:24px">
          <div style="background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
            <div style="background:#10b981;color:#fff;padding:24px 28px">
              <div style="font-size:13px;letter-spacing:.6px;opacity:.85;text-transform:uppercase">Payment Successful</div>
              <h1 style="margin:6px 0 0;font-size:22px;font-weight:600">Hi <?php echo esc_html( $user->display_name ); ?>,</h1>
            </div>
            <div style="padding:24px 28px;color:#222;line-height:1.55;font-size:15px">
              <p style="margin:0 0 18px">Thank you for your payment. Your enrollment is now active.</p>
              <table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;width:140px">Package</td><td style="padding:10px 0;font-weight:500"><?php echo esc_html( $pkg->package_name ); ?></td></tr>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Billing</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $cycle_label ); ?> (one-time payment)</td></tr>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Amount</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $amount_label ); ?></td></tr>
                <?php if ( $valid_to ) : ?>
                <tr><td style="padding:10px 0;color:#6b7280;font-size:13px;border-top:1px solid #f0f0f3">Valid Until</td><td style="padding:10px 0;font-weight:500;border-top:1px solid #f0f0f3"><?php echo esc_html( $valid_to ); ?></td></tr>
                <?php endif; ?>
              </table>
              <p style="margin-top:18px">We'll contact you shortly to schedule your first session.</p>
            </div>
            <div style="padding:14px 28px;background:#fafafa;color:#9ca3af;font-size:12px;border-top:1px solid #f0f0f3">Sent from <?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
          </div>
        </div>
        <?php
        $html = ob_get_clean();
        if ( class_exists( 'ES_Mailer' ) ) { ES_Mailer::send( $user->user_email, $subject, $html ); } else { wp_mail( $user->user_email, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) ); }

        // Admin email (plain text — simple)
        $admin_email = get_option( 'admin_email' );
        if ( $admin_email ) {
            $admin_subject = 'New Payment: ' . $user->display_name . ' — ' . $pkg->package_name;
            $admin_body  = "A new payment has been received.\n\n";
            $admin_body .= "Student: {$user->display_name} ({$user->user_email})\n";
            $admin_body .= "Package: {$pkg->package_name}\n";
            $admin_body .= "Cycle: {$cycle_label}\n";
            $admin_body .= "Amount: {$amount_label}\n";
            if ( $valid_to ) $admin_body .= "Valid Until: {$valid_to}\n";
            $admin_body .= "\nView all payments: " . admin_url( 'admin.php?page=eduschedule-bookings' );
            if ( class_exists( 'ES_Mailer' ) ) { ES_Mailer::send( $admin_email, $admin_subject, nl2br( esc_html( $admin_body ) ) ); } else { wp_mail( $admin_email, $admin_subject, $admin_body ); }
        }
    }

    /**
     * Webhook handler — POST to ?es_stripe_webhook=1
     */
    public static function handle_webhook() {
        if ( empty( $_GET['es_stripe_webhook'] ) ) return;
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) return;

        $payload = file_get_contents( 'php://input' );
        $sig     = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';

        $s = ES_Helpers::settings();
        $whsec = trim( $s['stripe_webhook_secret'] ?? '' );

        if ( $whsec && ! self::verify_webhook_signature( $payload, $sig, $whsec ) ) {
            status_header( 400 );
            exit( 'Bad signature' );
        }

        $event = json_decode( $payload, true );
        if ( ! $event || empty( $event['type'] ) ) {
            status_header( 400 );
            exit( 'Bad event' );
        }

        if ( $event['type'] === 'checkout.session.completed' ) {
            $session = $event['data']['object'] ?? array();
            if ( ! empty( $session['id'] ) ) {
                self::finalize_session( $session['id'] );
            }
        }

        // Inline Elements flow — payment_intent.succeeded
        if ( $event['type'] === 'payment_intent.succeeded' ) {
            $pi = $event['data']['object'] ?? array();
            if ( ! empty( $pi['id'] ) ) {
                self::finalize_payment_intent( $pi['id'] );
            }
        }

        status_header( 200 );
        echo 'ok';
        exit;
    }

    /** Stripe signature verification (HMAC SHA256) */
    private static function verify_webhook_signature( $payload, $header, $secret ) {
        if ( empty( $header ) || empty( $secret ) ) return false;
        $parts = array();
        foreach ( explode( ',', $header ) as $kv ) {
            $p = explode( '=', $kv, 2 );
            if ( count( $p ) === 2 ) $parts[ trim( $p[0] ) ] = trim( $p[1] );
        }
        if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) return false;
        $signed = $parts['t'] . '.' . $payload;
        $expected = hash_hmac( 'sha256', $signed, $secret );
        return hash_equals( $expected, $parts['v1'] );
    }

    /**
     * Low-level Stripe REST API request.
     */
    private static function request( $method, $path, $body = array() ) {
        $key = self::secret_key();
        if ( empty( $key ) ) return new WP_Error( 'no_key', 'Stripe secret key not configured.' );

        $url = self::API_BASE . $path;
        $args = array(
            'method'  => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $key,
            ),
        );

        if ( $method === 'POST' && ! empty( $body ) ) {
            $args['body'] = $body; // Stripe accepts application/x-www-form-urlencoded
        }

        $resp = wp_remote_request( $url, $args );
        if ( is_wp_error( $resp ) ) return $resp;

        $code = wp_remote_retrieve_response_code( $resp );
        $data = json_decode( wp_remote_retrieve_body( $resp ), true );

        if ( $code >= 400 ) {
            $msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Stripe API error';
            return new WP_Error( 'stripe_api', $msg );
        }
        return is_array( $data ) ? $data : array();
    }

    /** Find the page hosting the [eduschedule_packages] shortcode (for return URLs) */
    private static function get_packages_page_url() {
        $pages = get_pages( array( 'number' => 200 ) );
        foreach ( $pages as $pg ) {
            if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) {
                return get_permalink( $pg->ID );
            }
        }
        return home_url( '/' );
    }

    /**
     * Build a unique username derived from an email's local part.
     * Falls back to numbered suffixes if taken.
     */
    private static function generate_unique_username( $email ) {
        $base = strtolower( preg_replace( '/[^a-z0-9_.\-]/i', '', strstr( $email, '@', true ) ?: 'user' ) );
        $base = $base !== '' ? $base : 'user';
        $candidate = $base;
        $i = 1;
        while ( username_exists( $candidate ) ) {
            $candidate = $base . $i;
            $i++;
            if ( $i > 9999 ) {
                $candidate = $base . '_' . wp_generate_password( 6, false );
                break;
            }
        }
        return $candidate;
    }
}
