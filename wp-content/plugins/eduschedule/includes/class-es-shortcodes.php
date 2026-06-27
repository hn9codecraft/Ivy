<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ES_Shortcodes {

    public function __construct() {
        $this->register_shortcodes();
        // Re-register late as well. Some themes/forms plugins use very generic
        // reset-password shortcode names and can overwrite our alias after
        // plugins_loaded. This makes [es_reset_password_form] reliably point to
        // the EduSchedule reset flow.
        add_action( 'init', array( $this, 'register_shortcodes' ), 9999 );
        add_action( 'wp_loaded', array( $this, 'register_shortcodes' ), 9999 );
        add_filter( 'pre_do_shortcode_tag', array( $this, 'force_edu_reset_shortcode' ), 0, 4 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

        // When a reset-link lands on a Reset Password page that has a theme/builder
        // lost-password form instead of our shortcode, replace the page body with
        // the EduSchedule reset view so ?es_action=rp always shows New Password.
        add_filter( 'the_content', array( $this, 'maybe_force_reset_page_content' ), 1 );

        // Hard route capture. This bypasses page builders or third-party forms
        // that can still render a generic forgot-password form on reset links.
        add_action( 'template_redirect', array( $this, 'maybe_render_direct_reset_page' ), 0 );
    }

    public function register_shortcodes() {
        // These two names are used for the password reset page. Remove first so
        // another form plugin/theme cannot keep ownership of them.
        remove_shortcode( 'eduschedule_reset' );
        remove_shortcode( 'es_reset_password_form' );
        remove_shortcode( 'ivy_reset_password' );

        add_shortcode( 'eduschedule_login',        array( $this, 'login' ) );
        add_shortcode( 'eduschedule_register',     array( $this, 'register' ) );
        add_shortcode( 'eduschedule_auth',         array( $this, 'auth' ) );
        add_shortcode( 'eduschedule_reset',        array( $this, 'reset' ) );
        add_shortcode( 'es_reset_password_form',   array( $this, 'reset' ) );
        add_shortcode( 'ivy_reset_password',       array( $this, 'reset' ) );
        add_shortcode( 'eduschedule_dashboard',    array( $this, 'dashboard' ) );
        add_shortcode( 'eduschedule_packages',     array( $this, 'packages' ) );
        add_shortcode( 'course_booking_calendar',  array( $this, 'public_calendar' ) );
        add_shortcode( 'eduschedule_calendar',     array( $this, 'public_calendar' ) );
        // [es_course_listing] now lives in the theme (functions.php +
        // template-parts/course-listing.php). The course_listing() method below
        // is kept as a reference but is no longer registered here.
    }

    public function enqueue() {
        //if ( ! $this->page_uses_shortcode() ) return;
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'es-frontend', ES_URL . 'public/css/frontend.css', array( 'dashicons' ), ES_VERSION );
        wp_enqueue_script( 'es-frontend', ES_URL . 'public/js/frontend.js', array( 'jquery' ), ES_VERSION, true );

        // Stripe Elements (loaded from Stripe CDN — required by Stripe's TOS).
        // Always enqueue so the packages shortcode JS can use it; tiny script, harmless when unused.
        $stripe_ready = class_exists( 'ES_Stripe' ) && ES_Stripe::is_enabled();
        if ( $stripe_ready ) {
            wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), null, true );
        }

        // Packages JS for public package selection (also handles admin pages safely via class checks)
        $pkg_deps = array( 'jquery', 'es-frontend' );
        if ( $stripe_ready ) $pkg_deps[] = 'stripe-js';
        wp_enqueue_script( 'es-packages', ES_URL . 'public/js/packages.js', $pkg_deps, ES_VERSION, true );

        $s = ES_Helpers::settings();

        wp_localize_script( 'es-frontend', 'ES_FE', array(
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'es_fe_nonce' ),         // generic nonce for booking, etc.
            'login_nonce'   => wp_create_nonce( 'es_login_nonce' ),      // dedicated login nonce
            'register_nonce'=> wp_create_nonce( 'es_register_nonce' ),   // dedicated register nonce
            'reset_nonce'   => wp_create_nonce( 'es_frontend_reset' ),   // dedicated reset nonce
            'is_logged'     => is_user_logged_in(),
            'user_id'       => get_current_user_id(),
            'login_url'     => $this->page_url( 'login_page_id' ),
            'register_url'  => $this->page_url( 'register_page_id' ),
            'dashboard_url' => $this->page_url( 'dashboard_page_id' ),
            'current_url'   => $this->get_current_url(),
            'countries'     => ES_Helpers::countries(),
            'slot_types'    => ES_Helpers::slot_types(),
            'today'         => current_time( 'Y-m-d' ),
            // Stripe configuration for the inline Elements form
            'stripe' => array(
                'enabled'         => $stripe_ready ? 1 : 0,
                'publishable_key' => $stripe_ready ? ES_Stripe::publishable_key() : '',
                'yearly_discount' => (float) ( $s['yearly_discount'] ?? 0 ),
            ),
        ) );
    }

    /**
     * Get the current page URL (with query string) so we can stay on the same page after login/register.
     */
    private function get_current_url() {
        if ( ! isset( $_SERVER['REQUEST_URI'] ) ) return home_url();
        $scheme = ( is_ssl() ? 'https' : 'http' );
        $host   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : parse_url( home_url(), PHP_URL_HOST );
        return esc_url_raw( $scheme . '://' . $host . $_SERVER['REQUEST_URI'] );
    }

    private function page_uses_shortcode() {
        global $post;
        if ( ! $post ) return false;
        return has_shortcode( $post->post_content, 'eduschedule_login' )
            || has_shortcode( $post->post_content, 'eduschedule_register' )
            || has_shortcode( $post->post_content, 'eduschedule_auth' )
            || has_shortcode( $post->post_content, 'eduschedule_reset' )
            || has_shortcode( $post->post_content, 'es_reset_password_form' )
            || has_shortcode( $post->post_content, 'ivy_reset_password' )
            || has_shortcode( $post->post_content, 'eduschedule_dashboard' )
            || has_shortcode( $post->post_content, 'course_booking_calendar' )
            || has_shortcode( $post->post_content, 'eduschedule_calendar' );
    }

    private function page_url( $key ) {
        $s = ES_Helpers::settings();
        $id = (int) ( $s[ $key ] ?? 0 );
        return $id ? get_permalink( $id ) : '';
    }

    public function login() {
        // Custom password-reset views, shown on the same login page via a flag.
        $reset_view = $this->maybe_render_reset_view();
        if ( $reset_view !== null ) return $reset_view;

        if ( is_user_logged_in() ) {
            $s = ES_Helpers::settings();
            $url = ! empty( $s['dashboard_page_id'] ) ? get_permalink( $s['dashboard_page_id'] ) : home_url();
            return '<div class="es-fe es-redirect"><p>You are already logged in. <a href="' . esc_url( $url ) . '">Go to dashboard →</a></p></div>';
        }
        ob_start();
        include ES_DIR . 'templates/frontend-login.php';
        return ob_get_clean();
    }

    /**
     * If the current request carries the forgot/reset flag (?es_action=
     * lostpassword|rp), render the custom reset view and return its HTML.
     * Returns null when there's no reset flag, so callers fall through to their
     * normal output. Shared by [eduschedule_login], [eduschedule_auth] and the
     * dedicated [eduschedule_reset] page so the forgot-password flow works on
     * any of them.
     *
     * @param bool $force  When true, always render (defaulting to the "request
     *                     a link" step) even without an es_action flag. Used by
     *                     the standalone [eduschedule_reset] shortcode.
     */
    public function maybe_render_reset_view( $force = false ) {
        $es_action = $this->current_reset_action();

        if ( $es_action !== 'lostpassword' && $es_action !== 'rp' ) {
            if ( ! $force ) {
                return null;
            }
            // Standalone reset page with no flag → show the request-link step.
            $es_action = 'lostpassword';
        }

        // 'rp' (reset password) carries key + login from the email link.
        $rp_key   = isset( $_GET['key'] )   ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['key'] ) ) )   : '';
        $rp_login = isset( $_GET['login'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['login'] ) ) ) : '';
        $rp_mode  = $es_action; // 'lostpassword' | 'rp'

        $rp_notice = '';
        $rp_error  = '';

        // Non-JS fallback. The AJAX flow still works, but this keeps the form
        // functional even if the theme delays or blocks the frontend script.
        $this->process_reset_form_post( $rp_mode, $rp_key, $rp_login, $rp_notice, $rp_error );

        // Validate the reset key up front so an expired/invalid link shows a
        // clear message instead of a dead form. Skip validation after a
        // successful password change because WordPress invalidates the key.
        $rp_valid = false;
        if ( $rp_mode === 'rp' && $rp_key && $rp_login && empty( $rp_notice ) ) {
            $check = check_password_reset_key( $rp_key, $rp_login );
            $rp_valid = ! is_wp_error( $check );
        } elseif ( $rp_mode === 'rp' && ! empty( $rp_notice ) ) {
            $rp_valid = true;
        }

        // The page to return to after success (back to login on the same page).
        $rp_back_url = remove_query_arg( array( 'es_action', 'action', 'key', 'login' ) );

        ob_start();
        include ES_DIR . 'templates/frontend-reset.php';
        return ob_get_clean();
    }


    /**
     * Hard override for reset shortcodes.
     *
     * This is stronger than add_shortcode(). If another plugin/theme registers
     * [es_reset_password_form], WordPress still fires pre_do_shortcode_tag before
     * that callback. Returning our HTML here guarantees the reset link opens the
     * New Password form when ?es_action=rp&key=...&login=... is present.
     */
    public function force_edu_reset_shortcode( $return, $tag, $attr, $m ) {
        if ( in_array( $tag, array( 'eduschedule_reset', 'es_reset_password_form', 'ivy_reset_password' ), true ) ) {
            return $this->maybe_render_reset_view( true );
        }
        return $return;
    }

    /**
     * Normalize all reset URL variants used by WordPress and EduSchedule.
     * Supports:
     *   ?es_action=lostpassword
     *   ?es_action=rp&key=...&login=...
     *   ?action=lostpassword / ?action=rp / ?action=resetpass
     *   ?key=...&login=... (some mail clients/plugins strip the action arg)
     */
    private function current_reset_action() {
        $action = '';
        if ( isset( $_GET['es_action'] ) ) {
            $action = sanitize_key( wp_unslash( $_GET['es_action'] ) );
        } elseif ( isset( $_GET['action'] ) ) {
            $action = sanitize_key( wp_unslash( $_GET['action'] ) );
        }

        if ( in_array( $action, array( 'retrievepassword', 'lost-password', 'lost_password' ), true ) ) {
            $action = 'lostpassword';
        }
        if ( in_array( $action, array( 'resetpass', 'reset-password', 'reset_password' ), true ) ) {
            $action = 'rp';
        }

        if ( ! $action && ! empty( $_GET['key'] ) && ! empty( $_GET['login'] ) ) {
            $action = 'rp';
        }

        return $action;
    }

    /**
     * Replace wrong/builder lost-password forms on the Reset Password page when
     * a reset link is opened. This directly fixes cases where /reset-password/
     * displays a generic Forgot Password form even though the URL contains
     * ?es_action=rp&key=...&login=....
     */
    /**
     * Directly render the EduSchedule reset view for real reset-link requests.
     * This is intentionally stronger than the_content replacement because some
     * Elementor/theme/form pages output their own reset form outside normal
     * shortcode rendering. It only runs on reset-style pages with reset query
     * args, so normal site pages are not affected.
     */
    public function maybe_render_direct_reset_page() {
        if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }

        $action = $this->current_reset_action();
        $has_reset_signal = in_array( $action, array( 'lostpassword', 'rp' ), true )
            || ( ! empty( $_GET['key'] ) && ! empty( $_GET['login'] ) );

        if ( ! $has_reset_signal || ! is_singular() ) {
            return;
        }

        global $post;
        if ( ! $post ) {
            return;
        }

        $content = (string) $post->post_content;
        $s = ES_Helpers::settings();
        $configured_reset_id = (int) ( $s['reset_page_id'] ?? 0 );
        $looks_like_reset_page = ( $configured_reset_id && (int) $post->ID === $configured_reset_id )
            || in_array( $post->post_name, array( 'reset-password', 'forgot-password', 'lost-password' ), true )
            || has_shortcode( $content, 'eduschedule_reset' )
            || has_shortcode( $content, 'es_reset_password_form' )
            || has_shortcode( $content, 'ivy_reset_password' )
            || has_shortcode( $content, 'eduschedule_login' )
            || has_shortcode( $content, 'eduschedule_auth' );

        if ( ! $looks_like_reset_page ) {
            return;
        }

        status_header( 200 );
        nocache_headers();
        ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'es-reset-direct-page' ); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>
<main id="primary" class="site-main es-reset-direct-wrap" style="max-width:640px;margin:60px auto;padding:0 20px;">
    <?php echo $this->maybe_render_reset_view( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</main>
<?php wp_footer(); ?>
</body>
</html>
        <?php
        exit;
    }

    public function maybe_force_reset_page_content( $content ) {
        if ( is_admin() || ! is_singular() ) {
            return $content;
        }

        $action = $this->current_reset_action();
        $has_reset_signal = in_array( $action, array( 'lostpassword', 'rp' ), true )
            || ( ! empty( $_GET['key'] ) && ! empty( $_GET['login'] ) );

        if ( ! $has_reset_signal ) {
            return $content;
        }

        global $post;
        if ( ! $post ) {
            return $content;
        }

        // When a real reset link is opened, render the EduSchedule reset
        // form directly at the_content priority 1. This avoids shortcode-name
        // conflicts and page-builder/forms plugins that keep showing their own
        // generic Forgot Password form even though ?es_action=rp is present.
        if ( has_shortcode( $content, 'eduschedule_reset' ) || has_shortcode( $content, 'es_reset_password_form' ) || has_shortcode( $content, 'ivy_reset_password' ) || has_shortcode( $content, 'eduschedule_login' ) || has_shortcode( $content, 'eduschedule_auth' ) ) {
            return $this->maybe_render_reset_view( true );
        }

        $s = ES_Helpers::settings();
        $configured_reset_id = (int) ( $s['reset_page_id'] ?? 0 );
        $looks_like_reset_page = ( $configured_reset_id && (int) $post->ID === $configured_reset_id )
            || in_array( $post->post_name, array( 'reset-password', 'forgot-password', 'lost-password' ), true );

        if ( ! $looks_like_reset_page ) {
            return $content;
        }

        return $this->maybe_render_reset_view( true );
    }

    /**
     * Server-side fallback for reset forms. AJAX remains the default, but this
     * protects the flow when frontend JavaScript is not running.
     */
    private function process_reset_form_post( &$rp_mode, &$rp_key, &$rp_login, &$rp_notice, &$rp_error ) {
        if ( empty( $_POST ) ) return;

        if ( isset( $_POST['es_lost_password_submit'] ) ) {
            if ( empty( $_POST['es_reset_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['es_reset_nonce'] ) ), 'es_frontend_reset' ) ) {
                $rp_error = 'Security check failed. Please refresh and try again.';
                return;
            }

            $login = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
            if ( $login === '' ) {
                $rp_error = 'Please enter your email address or username.';
                return;
            }

            $user = is_email( $login ) ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );
            $generic = 'If that account exists, a password reset link has been sent. Please check your inbox and spam folder.';

            if ( $user ) {
                $key = get_password_reset_key( $user );
                if ( ! is_wp_error( $key ) ) {
                    $reset_link = add_query_arg( array(
                        'es_action' => 'rp',
                        'key'       => rawurlencode( $key ),
                        'login'     => rawurlencode( $user->user_login ),
                    ), ES_Helpers::reset_page_url() );
                    if ( class_exists( 'ES_Mailer' ) ) {
                        ES_Mailer::send_password_reset( $user, $reset_link );
                    } else {
                        wp_mail( $user->user_email, 'Reset your password', $reset_link );
                    }
                }
            }

            $rp_mode   = 'lostpassword';
            $rp_notice = $generic;
            return;
        }

        if ( isset( $_POST['es_reset_password_submit'] ) ) {
            if ( empty( $_POST['es_reset_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['es_reset_nonce'] ) ), 'es_frontend_reset' ) ) {
                $rp_error = 'Security check failed. Please refresh and try again.';
                return;
            }

            $rp_key   = isset( $_POST['key'] )   ? sanitize_text_field( wp_unslash( $_POST['key'] ) )   : $rp_key;
            $rp_login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : $rp_login;
            $pass1    = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
            $pass2    = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : '';

            $rp_mode = 'rp';

            if ( $rp_key === '' || $rp_login === '' ) {
                $rp_error = 'This reset link is invalid. Please request a new one.';
                return;
            }
            if ( strlen( $pass1 ) < 6 ) {
                $rp_error = 'Password must be at least 6 characters.';
                return;
            }
            if ( $pass1 !== $pass2 ) {
                $rp_error = 'The two passwords do not match.';
                return;
            }

            $user = check_password_reset_key( $rp_key, $rp_login );
            if ( is_wp_error( $user ) ) {
                $rp_error = 'This reset link has expired or is invalid. Please request a new one.';
                return;
            }

            reset_password( $user, $pass1 );
            $rp_notice = 'Your password has been reset successfully. You can now log in.';
        }
    }

    /**
     * Standalone forgot/reset-password page. Place [eduschedule_reset] on a
     * dedicated page to host the whole self-service reset flow (request link →
     * branded email → set new password) entirely within the plugin, without
     * depending on the login page. Logged-in users are gently bounced to their
     * dashboard unless they're actively following a reset link.
     */
    public function reset() {
        $es_action = $this->current_reset_action();

        if ( is_user_logged_in() && $es_action !== 'rp' && $es_action !== 'lostpassword' ) {
            $s   = ES_Helpers::settings();
            $url = ! empty( $s['dashboard_page_id'] ) ? get_permalink( $s['dashboard_page_id'] ) : home_url();
            return '<div class="es-fe es-redirect"><p>You are already logged in. <a href="' . esc_url( $url ) . '">Go to dashboard →</a></p></div>';
        }

        return $this->maybe_render_reset_view( true );
    }

    public function register() {
        if ( is_user_logged_in() ) {
            $s = ES_Helpers::settings();
            $url = ! empty( $s['dashboard_page_id'] ) ? get_permalink( $s['dashboard_page_id'] ) : home_url();
            return '<div class="es-fe es-redirect"><p>You are already logged in. <a href="' . esc_url( $url ) . '">Go to dashboard →</a></p></div>';
        }
        $s = ES_Helpers::settings();
        if ( empty( $s['register_open'] ) ) {
            return '<div class="es-fe es-redirect"><p>Registration is currently closed. Please contact the admin.</p></div>';
        }
        ob_start();
        include ES_DIR . 'templates/frontend-register.php';
        return ob_get_clean();
    }

    /**
     * Combined Login + Register shortcode (toggle on same page).
     * Usage:
     *   [eduschedule_auth]
     *   [eduschedule_auth default="register"]
     *   [eduschedule_auth logged_in_message="You are logged in."]
     *
     * After successful login/register, the user STAYS on the SAME page (with all query params preserved).
     */
    public function auth( $atts = array() ) {
        $atts = shortcode_atts( array(
            'default'            => 'login', // 'login' or 'register'
            'logged_in_message'  => '',
            'show_logged_in_box' => 'yes',
        ), $atts, 'eduschedule_auth' );

        // Custom forgot/reset-password views work on the auth shortcode too.
        $reset_view = $this->maybe_render_reset_view();
        if ( $reset_view !== null ) return $reset_view;

        if ( is_user_logged_in() ) {
            if ( $atts['show_logged_in_box'] !== 'yes' ) return '';
            $user = wp_get_current_user();
            $msg = $atts['logged_in_message']
                ? $atts['logged_in_message']
                : sprintf( 'You are logged in as %s.', $user->display_name );
            return '<div class="es-fe es-auth-loggedin"><p>' . esc_html( $msg ) . '</p></div>';
        }

        $s = ES_Helpers::settings();
        $register_open = ! empty( $s['register_open'] );
        $countries = ES_Helpers::countries();

        ob_start();
        include ES_DIR . 'templates/frontend-auth.php';
        return ob_get_clean();
    }

    public function dashboard() {
        if ( ! is_user_logged_in() ) {
            $s = ES_Helpers::settings();
            $url = ! empty( $s['login_page_id'] ) ? get_permalink( $s['login_page_id'] ) : wp_login_url( get_permalink() );
            return '<div class="es-fe es-redirect"><p>Please <a href="' . esc_url( $url ) . '">log in</a> to view your dashboard.</p></div>';
        }
        ob_start();
        include ES_DIR . 'templates/frontend-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Public packages shortcode
     * Usage:
     *   [eduschedule_packages]
     *   [eduschedule_packages yearly_toggle="no"]
     *   [eduschedule_packages default_cycle="yearly"]
     *   [eduschedule_packages monthly_label="Pay Monthly" semester_label="Pay per Semester"]
     *   [eduschedule_packages brand_name="Ivy Quest Academy" brand_logo="https://.../logo.png"]
     *   [eduschedule_packages recommended="2" recommendation_text="Based on our consultation, I recommend ..."]
     *   [eduschedule_packages period_unit="month" period_unit_yearly="semester"]
     */
    public function packages( $atts = array() ) {
        $sc_atts = shortcode_atts( array(
            'yearly_toggle'       => '',
            'default_cycle'       => 'monthly',
            'monthly_label'       => 'Pay Monthly',
            'semester_label'      => 'Pay Yearly',
            'period_unit'         => 'month',
            'period_unit_yearly'  => 'semester',
            'brand_name'          => '',
            'brand_logo'          => '',
            'recommended'         => '2',
            'recommendation_text' => '',
        ), $atts, 'eduschedule_packages' );

        ob_start();
        include ES_DIR . 'templates/public-packages.php';
        return ob_get_clean();
    }

    /**
     * Course listing shortcode — displays all published 'course' CPT posts in
     * the card-list layout matching the site design (thumbnail, title, short
     * description, rating stars, creator, badge pills, Demo button).
     *
     * ACF fields used (from scf-export):
     *   course_short_description  – textarea
     *   rating                    – number (0–5, step 0.1)
     *   course_created_by         – text
     *
     * Badges are driven by the post's category terms (or custom taxonomy
     * 'course_badge'). If neither exists the pill row is hidden.
     *
     * Usage:
     *   [es_course_listing]
     *   [es_course_listing posts_per_page="12" orderby="title" order="ASC" category="ielts"]
     *   [es_course_listing demo_label="Book Demo" demo_url="/book-demo"]
     *
     * @param  array  $atts  Shortcode attributes.
     * @return string        HTML output.
     */
    public function course_listing( $atts = array() ) {
        $atts = shortcode_atts( array(
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'category'       => '',   // slug of a category/term to filter by
            'demo_label'     => 'Demo',
            'demo_url'       => '',   // override link; empty = single post permalink
            'show_filter'    => 'yes', // yes|no — show left sidebar category filter
        ), $atts, 'es_course_listing' );

        // Determine which taxonomy to use for filtering
        $filter_tax = taxonomy_exists( 'course_category' ) ? 'course_category' : 'category';

        $query_args = array(
            'post_type'      => 'course',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $atts['posts_per_page'],
            'orderby'        => sanitize_key( $atts['orderby'] ),
            'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
            'no_found_rows'  => true,
        );

        // Optional taxonomy filter (supports both 'category' and 'course_category')
        if ( ! empty( $atts['category'] ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => $filter_tax,
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $atts['category'] ),
                ),
            );
        }

        $courses = get_posts( $query_args );

        // Build category list for filter sidebar (only cats that have courses)
        $show_filter = ( strtolower( $atts['show_filter'] ) !== 'no' );
        $filter_cats = array();
        if ( $show_filter ) {
            $all_cats = get_terms( array(
                'taxonomy'   => $filter_tax,
                'hide_empty' => true,
                'object_ids' => wp_list_pluck( $courses, 'ID' ),
                'orderby'    => 'name',
                'order'      => 'ASC',
            ) );
            if ( ! is_wp_error( $all_cats ) ) $filter_cats = $all_cats;
        }

        // Map course ID → its term slugs for JS filtering
        $course_cats = array(); // [ post_id => [slug, slug, ...] ]
        if ( $show_filter && ! empty( $filter_cats ) ) {
            foreach ( $courses as $c ) {
                $terms = wp_get_post_terms( $c->ID, $filter_tax, array( 'fields' => 'slugs' ) );
                $course_cats[ $c->ID ] = is_array( $terms ) ? $terms : array();
            }
        }

        $uid = 'escl_' . substr( md5( serialize( $atts ) ), 0, 8 ); // unique id per shortcode instance

        ob_start();
        ?>
        <div class="course-list-heading">
            <div class="container">
                <div class="breadcrumb course-breadcrumb">
                    <a href="/">Home</a> <span class="text-primary"> | </span>
                    <span>Courses</span>
                </div>

                <h1>All Certification Preparation Courses</h1>
            </div>
        </div>
        <div class="es-course-listing-wrap container" id="<?php echo esc_attr( $uid ); ?>">
        <?php if ( $show_filter && ! empty( $filter_cats ) ) : ?>
            <aside class="es-cl-sidebar">
                <div class="es-cl-filter-head">Filter</div>
                <ul class="es-cl-cat-list">
                    <li>
                        <label class="es-cl-cat-item is-active" data-slug="">
                            <input type="radio" name="<?php echo esc_attr( $uid ); ?>_cat" value="" checked /> All Courses
                        </label>
                    </li>
                    <?php foreach ( $filter_cats as $term ) : ?>
                    <li>
                        <label class="es-cl-cat-item" data-slug="<?php echo esc_attr( $term->slug ); ?>">
                            <input type="radio" name="<?php echo esc_attr( $uid ); ?>_cat" value="<?php echo esc_attr( $term->slug ); ?>" />
                            <?php echo esc_html( $term->name ); ?>
                            <span class="es-cl-cat-count"><?php echo (int) $term->count; ?></span>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        <?php endif; ?>

        <div class="es-course-listing">
            <?php if ( empty( $courses ) ) : ?>
                <p class="es-cl-empty">No courses found.</p>
            <?php else : foreach ( $courses as $course ) :
                $cid         = (int) $course->ID;
                $title       = get_the_title( $course );
                $permalink   = ! empty( $atts['demo_url'] ) ? esc_url( $atts['demo_url'] ) : get_permalink( $course );

                // Course image: first ACF image_gallery image, then featured image as fallback.
                $thumb_url = '';
                if ( function_exists( 'get_field' ) ) {
                    $gallery = get_field( 'image_gallery', $cid );
                    if ( ! empty( $gallery[0]['url'] ) ) {
                        $thumb_url = $gallery[0]['url'];
                    }
                }
                if ( ! $thumb_url ) {
                    $thumb_url = get_the_post_thumbnail_url( $course, 'medium' );
                }

                // ACF fields — safe fallbacks if ACF not active
                $short_desc  = function_exists( 'get_field' ) ? get_field( 'course_short_description', $cid ) : '';
                $rating      = function_exists( 'get_field' ) ? (float) get_field( 'rating', $cid ) : 0;
                $creator     = function_exists( 'get_field' ) ? get_field( 'course_created_by', $cid ) : '';
                if ( ! $short_desc ) $short_desc = wp_trim_words( get_the_excerpt( $course ) ?: strip_tags( $course->post_content ), 18, '…' );
                if ( ! $creator )    $creator    = get_the_author_meta( 'display_name', (int) $course->post_author );

                // Badges: try custom taxonomy 'course_badge', fallback to 'category'
                $badge_terms = array();
                if ( taxonomy_exists( 'course_badge' ) ) {
                    $badge_terms = wp_get_post_terms( $cid, 'course_badge', array( 'fields' => 'names' ) );
                }
                if ( empty( $badge_terms ) ) {
                    $cat_terms = wp_get_post_terms( $cid, 'category', array( 'fields' => 'names' ) );
                    $badge_terms = is_array( $cat_terms ) ? $cat_terms : array();
                }
                $badge_terms = is_wp_error( $badge_terms ) ? array() : $badge_terms;

                // Rating stars: filled / half / empty
                $rating_clamped = min( 5, max( 0, $rating ) );
                $stars_html = '';
                for ( $s = 1; $s <= 5; $s++ ) {
                    $diff = $rating_clamped - ( $s - 1 );
                    if ( $diff >= 1 )        $stars_html .= '<span class="es-cl-star es-cl-star-full">★</span>';
                    elseif ( $diff >= 0.4 )  $stars_html .= '<span class="es-cl-star es-cl-star-half">★</span>';
                    else                     $stars_html .= '<span class="es-cl-star es-cl-star-empty">★</span>';
                }

                // Data attribute for JS category filter
                $cat_slugs_attr = ! empty( $course_cats[ $cid ] ) ? implode( ' ', $course_cats[ $cid ] ) : '';
            ?>
                <div class="es-cl-row" data-cats="<?php echo esc_attr( $cat_slugs_attr ); ?>">
                    <!-- Thumbnail -->
                    <div class="es-cl-thumb">
                        <?php if ( $thumb_url ) : ?>
                            <a href="<?php echo esc_url( $permalink ); ?>">
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
                            </a>
                        <?php else : ?>
                            <div class="es-cl-thumb-placeholder"><span class="dashicons dashicons-welcome-learn-more"></span></div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="es-cl-info">
                        <h3 class="es-cl-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                        <?php if ( $short_desc ) : ?>
                            <p class="es-cl-desc"><?php echo esc_html( $short_desc ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $badge_terms ) ) : ?>
                            <div class="es-cl-badges">
                                <?php foreach ( $badge_terms as $badge ) :
                                    $cls  = 'es-cl-badge';
                                    if ( stripos( $badge, 'premium' ) !== false )      $cls .= ' es-cl-badge-premium';
                                    elseif ( stripos( $badge, 'bestsell' ) !== false ) $cls .= ' es-cl-badge-bestseller';
                                    elseif ( stripos( $badge, 'new' ) !== false )      $cls .= ' es-cl-badge-new';
                                ?>
                                    <span class="<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $badge ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $creator ) : ?>
                            <div class="es-cl-creator"><?php echo esc_html( $creator ); ?></div>
                        <?php endif; ?>

                        <?php if ( $rating_clamped > 0 ) : ?>
                            <div class="es-cl-rating">
                                <span class="es-cl-rating-num"><?php echo number_format( $rating_clamped, 1 ); ?></span>
                                <span class="es-cl-stars"><?php echo $stars_html; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Demo button -->
                    <div class="es-cl-action">
                        <a href="<?php echo esc_url( $permalink ); ?>" class="es-cl-demo-btn"><?php echo esc_html( $atts['demo_label'] ); ?></a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div><!-- /.es-course-listing -->
        </div><!-- /.es-course-listing-wrap -->

        <script>
        (function(){
            var wrap = document.getElementById('<?php echo esc_js( $uid ); ?>');
            if (!wrap) return;
            wrap.addEventListener('click', function(e) {
                var label = e.target.closest('.es-cl-cat-item');
                if (!label) return;
                var slug = label.dataset.slug || '';
                // Update active state
                wrap.querySelectorAll('.es-cl-cat-item').forEach(function(l){ l.classList.remove('is-active'); });
                label.classList.add('is-active');
                // Filter rows
                wrap.querySelectorAll('.es-cl-row').forEach(function(row){
                    if (!slug) { row.style.display = ''; return; }
                    var cats = (row.dataset.cats || '').split(' ');
                    row.style.display = cats.indexOf(slug) !== -1 ? '' : 'none';
                });
            });
        })();
        </script>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Public booking calendar — anyone can browse, must log in to book.
     * Usage: [course_booking_calendar title="Book a Session" types="1to1,group,open" months_ahead="3"]
     */
    public function public_calendar( $atts = array() ) {
        $atts = shortcode_atts( array(
            'title'        => 'Course Dates and Enrollment Times',
            'subtitle'     => '',
            'types'        => '1to1,group,open',  // comma-separated, 'personal' is always blocked
            'months_ahead' => 12,
            'show_legend'  => 'yes',
            'allow_multi'  => 'yes',  // yes|no - allow multi-date selection
        ), $atts, 'course_booking_calendar' );

        // Sanitize types
        $allowed = array_intersect(
            array_map( 'trim', explode( ',', $atts['types'] ) ),
            array( '1to1', 'group', 'open' )  // never include 'personal' here
        );
        if ( empty( $allowed ) ) $allowed = array( '1to1', 'group', 'open' );

        ob_start();
        include ES_DIR . 'templates/frontend-public-calendar.php';
        $html = ob_get_clean();

        return $html;
    }
}
