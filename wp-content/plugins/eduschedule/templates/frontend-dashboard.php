<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$current = wp_get_current_user();
$tz = ES_Helpers::user_tz();
$upcoming = ES_DB::get_user_bookings( $current->ID, true );

// v3.9.6 — pull the student's paid purchases so they can see their active plan.
$my_payments = ES_Packages::get_user_payments( $current->ID, true );
$cat_label   = ES_Packages::category_label( $current->ID );

// Learning library: package-level/common uploads first, then individual/group
// session files and videos. This keeps the common file uploader content visible
// first on the student dashboard.
$library_files  = array();
$library_videos = array();
$seen_files     = array();
$seen_videos    = array();

foreach ( (array) $my_payments as $pay ) {
    foreach ( (array) ES_Packages::get_package_files( (int) $pay->package_id ) as $f ) {
        $key = 'file-' . (int) $f->id;
        if ( isset( $seen_files[ $key ] ) ) continue;
        $f->library_scope = 'Common';
        $f->library_package = $pay->package_name ?: 'Package';
        $library_files[] = $f;
        $seen_files[ $key ] = true;
    }
    foreach ( (array) ES_Packages::get_package_videos( (int) $pay->package_id ) as $v ) {
        $key = 'video-' . (int) $v->id;
        if ( isset( $seen_videos[ $key ] ) ) continue;
        $v->library_scope = 'Common';
        $v->library_package = $pay->package_name ?: 'Package';
        $library_videos[] = $v;
        $seen_videos[ $key ] = true;
    }
}

foreach ( (array) ES_Packages::get_session_files( '1to1', $current->ID ) as $f ) {
    $key = 'file-' . (int) $f->id;
    if ( isset( $seen_files[ $key ] ) ) continue;
    $f->library_scope = empty( $f->slot_id ) ? 'General' : 'Session';
    $f->library_package = '';
    $library_files[] = $f;
    $seen_files[ $key ] = true;
}
foreach ( (array) ES_Packages::get_videos( '1to1', $current->ID ) as $v ) {
    $key = 'video-' . (int) $v->id;
    if ( isset( $seen_videos[ $key ] ) ) continue;
    $v->library_scope = 'Video';
    $v->library_package = '';
    $library_videos[] = $v;
    $seen_videos[ $key ] = true;
}
foreach ( (array) ES_Packages::get_user_groups( $current->ID ) as $grp ) {
    foreach ( (array) ES_Packages::get_session_files( 'group', (int) $grp->id ) as $f ) {
        $key = 'file-' . (int) $f->id;
        if ( isset( $seen_files[ $key ] ) ) continue;
        $f->library_scope = empty( $f->slot_id ) ? 'Group General' : 'Group Session';
        $f->library_package = $grp->group_name;
        $library_files[] = $f;
        $seen_files[ $key ] = true;
    }
    foreach ( (array) ES_Packages::get_videos( 'group', (int) $grp->id ) as $v ) {
        $key = 'video-' . (int) $v->id;
        if ( isset( $seen_videos[ $key ] ) ) continue;
        $v->library_scope = 'Group Video';
        $v->library_package = $grp->group_name;
        $library_videos[] = $v;
        $seen_videos[ $key ] = true;
    }
}
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

        <!-- Your Plan / purchased packages -->
        <section class="es-dash-section">
            <h2>Your Plan</h2>
            <?php if ( empty( $my_payments ) ) : ?>
                <div class="es-dash-empty">
                    <p>You don't have an active plan yet.</p>
                    <p class="es-fe-helper">Choose a package to get started with your sessions.</p>
                </div>
            <?php else : ?>
                <div class="es-plan-grid">
                    <?php foreach ( $my_payments as $pay ) :
                        $amount_label = ES_Helpers::format_price( $pay->amount, $pay->currency );
                        // Use the duration snapshotted on the payment row (falls
                        // back to 1 for legacy rows created before this update).
                        $months       = max( 1, (int) ( $pay->months ?? 1 ) );
                        $total_sess   = (int) ( $pay->total_sessions ?? 0 );
                        $monthly_sess = (int) ( $pay->monthly_session_limit ?? 0 );
                        $used_sess    = (int) ( $pay->used_sessions ?? 0 );
                        $remain_sess  = max( 0, $total_sess - $used_sess );
                        $valid_until  = $pay->valid_until ? date_i18n( 'M j, Y', strtotime( $pay->valid_until ) ) : '';
                        $is_active    = $pay->valid_until ? ( strtotime( $pay->valid_until ) >= current_time( 'timestamp' ) ) : true;
                    ?>
                        <div class="es-plan-card">
                            <div class="es-plan-card-top">
                                <div class="es-plan-name"><?php echo esc_html( $pay->package_name ?: 'Package' ); ?></div>
                                <span class="es-plan-badge <?php echo $is_active ? 'is-active' : 'is-expired'; ?>">
                                    <?php echo $is_active ? 'Active' : 'Expired'; ?>
                                </span>
                            </div>
                            <?php if ( $pay->sub_heading ) : ?>
                                <div class="es-plan-sub"><?php echo esc_html( $pay->sub_heading ); ?></div>
                            <?php endif; ?>
                            <div class="es-plan-meta">
                                <div><span>Type</span><strong><?php echo esc_html( $cat_label ); ?></strong></div>
                                <div><span>Paid</span><strong><?php echo esc_html( $amount_label ); ?></strong></div>
                                <div><span>Duration</span><strong><?php echo esc_html( $months . ' month' . ( $months > 1 ? 's' : '' ) ); ?></strong></div>
                                <?php if ( $total_sess > 0 ) : ?>
                                    <div><span>Total Sessions</span><strong><?php echo (int) $total_sess; ?></strong></div>
                                <?php endif; ?>
                                <?php if ( $monthly_sess > 0 ) : ?>
                                    <div><span>Monthly Sessions</span><strong><?php echo (int) $monthly_sess; ?> / mo</strong></div>
                                <?php endif; ?>
                                <?php if ( $total_sess > 0 ) : ?>
                                    <div><span>Remaining</span><strong><?php echo (int) $remain_sess; ?></strong></div>
                                <?php endif; ?>
                                <?php if ( $valid_until ) : ?>
                                    <div><span>Valid until</span><strong><?php echo esc_html( $valid_until ); ?></strong></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Files & Videos -->
        <section class="es-dash-section">
            <h2>Files &amp; Videos</h2>
            <?php if ( empty( $library_files ) && empty( $library_videos ) ) : ?>
                <div class="es-dash-empty">
                    <p>No files or videos have been shared yet.</p>
                </div>
            <?php else : ?>
                <div class="es-plan-grid">
                    <?php foreach ( $library_files as $f ) : ?>
                        <div class="es-plan-card">
                            <div class="es-plan-card-top">
                                <div class="es-plan-name"><?php echo esc_html( $f->file_name ?: 'File' ); ?></div>
                                <span class="es-plan-badge is-active"><?php echo esc_html( $f->library_scope ); ?></span>
                            </div>
                            <?php if ( ! empty( $f->library_package ) ) : ?><div class="es-plan-sub"><?php echo esc_html( $f->library_package ); ?></div><?php endif; ?>
                            <div class="es-booking-actions" style="margin-top:12px;">
                                <a href="<?php echo esc_url( $f->file_url ); ?>" target="_blank" rel="noopener" class="es-fe-btn es-fe-btn-ghost-sm">Open File</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ( $library_videos as $v ) : ?>
                        <div class="es-plan-card">
                            <div class="es-plan-card-top">
                                <div class="es-plan-name"><?php echo esc_html( $v->title ?: 'Video' ); ?></div>
                                <span class="es-plan-badge is-active"><?php echo esc_html( $v->library_scope ); ?></span>
                            </div>
                            <?php if ( ! empty( $v->library_package ) ) : ?><div class="es-plan-sub"><?php echo esc_html( $v->library_package ); ?></div><?php endif; ?>
                            <div class="es-booking-actions" style="margin-top:12px;">
                                <a href="<?php echo esc_url( $v->video_url ); ?>" target="_blank" rel="noopener" class="es-fe-btn es-fe-btn-zoom">Watch Video</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

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
