<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$current = wp_get_current_user();
$tz = ES_Helpers::user_tz();
$upcoming = ES_DB::get_user_bookings( $current->ID, true );
?>
<div class="es-fe es-dashboard">
    <header class="es-fe-topbar">
        <div class="es-fe-brand"><?php echo esc_html( ES_Helpers::settings()['site_name'] ); ?></div>
        <div class="es-fe-user">
            <span class="es-fe-user-name">Hi, <?php echo esc_html( $current->first_name ?: $current->display_name ); ?></span>
            <button type="button" class="es-fe-btn-link" id="es-logout-btn">Log out</button>
        </div>
    </header>

    <div class="es-dash-shell">
        <!-- Upcoming bookings -->
        <section class="es-dash-section">
            <h2>Your Upcoming Bookings</h2>
            <?php if ( empty( $upcoming ) ) : ?>
                <div class="es-dash-empty">
                    <p>You have no upcoming bookings yet.</p>
                    <p class="es-fe-helper">Browse available slots below and book one!</p>
                </div>
            <?php else : ?>
                <div class="es-booking-grid">
                    <?php foreach ( $upcoming as $b ) :
                        $type_color = ES_Helpers::slot_type_color( $b->slot_type );
                        $type_label = ES_Helpers::slot_type_label( $b->slot_type );
                        $start_user = ES_Helpers::to_user_tz( $b->slot_date . ' ' . $b->start_time );
                        $end_user   = ES_Helpers::to_user_tz( $b->slot_date . ' ' . $b->end_time );
                    ?>
                        <div class="es-booking-card" style="--type-color:<?php echo esc_attr( $type_color ); ?>">
                            <div class="es-booking-head">
                                <span class="es-tag" style="background:<?php echo esc_attr( $type_color ); ?>20;color:<?php echo esc_attr( $type_color ); ?>"><?php echo esc_html( $type_label ); ?></span>
                                <span class="es-booking-platform"><?php echo esc_html( $b->platform ); ?></span>
                            </div>
                            <div class="es-booking-when"><?php echo esc_html( $start_user ? $start_user->format( 'D, M j' ) : $b->slot_date ); ?></div>
                            <div class="es-booking-time"><?php echo esc_html( $start_user ? $start_user->format( 'g:i A' ) : substr( $b->start_time, 0, 5 ) ); ?> – <?php echo esc_html( $end_user ? $end_user->format( 'g:i A' ) : substr( $b->end_time, 0, 5 ) ); ?></div>
                            <?php if ( $b->title ) : ?><div class="es-booking-title"><?php echo esc_html( $b->title ); ?></div><?php endif; ?>
                            <div class="es-booking-actions">
                                <?php if ( ! empty( $b->zoom_join_url ) ) : ?>
                                    <a href="<?php echo esc_url( $b->zoom_join_url ); ?>" target="_blank" rel="noopener" class="es-fe-btn es-fe-btn-zoom"><span class="dashicons dashicons-video-alt2"></span> Join Zoom</a>
                                <?php endif; ?>
                                <button type="button" class="es-fe-btn-link-danger es-cancel-booking" data-id="<?php echo (int) $b->id; ?>">Cancel</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Book a slot -->
        <section class="es-dash-section">
            <div class="es-dash-section-head">
                <h2>Book a Session</h2>
                <span class="es-fe-helper">Times shown in <strong><?php echo esc_html( $tz->getName() ); ?></strong></span>
            </div>

            <div class="es-fe-cal-shell">
                <div class="es-card">
                    <div class="es-cal-toolbar">
                        <h3 id="es-fe-cal-title">—</h3>
                        <div class="es-cal-nav">
                            <button type="button" class="es-fe-btn es-fe-btn-ghost-sm" id="es-fe-cal-prev">←</button>
                            <button type="button" class="es-fe-btn es-fe-btn-ghost-sm" id="es-fe-cal-today">Today</button>
                            <button type="button" class="es-fe-btn es-fe-btn-ghost-sm" id="es-fe-cal-next">→</button>
                        </div>
                    </div>
                    <div id="es-fe-calview" data-year="<?php echo (int) current_time( 'Y' ); ?>" data-month="<?php echo (int) current_time( 'n' ); ?>">
                        <div class="es-loading">Loading…</div>
                    </div>
                </div>

                <aside class="es-card es-fe-side" id="es-fe-side">
                    <div class="es-fe-side-empty">
                        <p>Pick a date with open slots to start booking.</p>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</div>

<!-- Confirm-booking modal -->
<div class="es-modal" id="es-fe-book-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card">
        <div class="es-modal-head">
            <h2>Confirm Booking</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body" id="es-fe-book-body"></div>
        <div class="es-modal-foot">
            <button type="button" class="es-fe-btn es-fe-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-fe-btn es-fe-btn-primary" id="es-fe-book-confirm">Confirm Booking</button>
        </div>
    </div>
</div>
