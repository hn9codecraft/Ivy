<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="es-admin">
    <div class="es-page-head">
        <div>
            <h1>All Bookings</h1>
            <p class="es-page-sub">All bookings made by users.</p>
        </div>
        <!-- <div class="es-page-actions">
            <button type="button" class="es-btn es-btn-primary" id="es-manual-book-btn">
                <span class="dashicons dashicons-plus"></span> Manual Book
            </button>
        </div> -->
    </div>

    <?php if ( ! empty( $_GET['deleted'] ) ) : ?>
        <div class="es-notice es-notice-success">Booking deleted.</div>
    <?php endif; ?>

    <form method="get" class="es-filter-bar">
        <input type="hidden" name="page" value="eduschedule-bookings" />
        <label>From <input type="date" name="date_from" value="<?php echo esc_attr( $args['date_from'] ); ?>" /></label>
        <label>To <input type="date" name="date_to" value="<?php echo esc_attr( $args['date_to'] ); ?>" /></label>
        <input type="search" name="s" placeholder="Search name or email" value="<?php echo esc_attr( $args['search'] ); ?>" />
        <button class="es-btn es-btn-primary" type="submit">Filter</button>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=eduschedule-bookings' ) ); ?>" class="es-btn es-btn-ghost">Reset</a>
        <span class="es-pill es-pill-info" style="margin-left:auto">Total: <?php echo count( $bookings ); ?></span>
    </form>

    <div class="es-card es-card-flush">
        <table class="es-table">
            <thead>
                <tr>
                    <th>#</th><th>Date / Time</th><th>Type</th><th>User</th><th>Email</th>
                    <th>Package / Sessions</th><th>Payment</th><th>Attendance</th><th>Platform</th><th>Meeting</th><th>Booked</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $bookings ) ) : ?>
                    <tr><td colspan="12" class="es-empty-cell">No bookings found.</td></tr>
                <?php else : foreach ( $bookings as $b ) :
                    $type_color = ES_Helpers::slot_type_color( $b->slot_type );
                    $plan = ! empty( $b->user_id ) ? ES_Packages::get_active_plan( $b->user_id ) : null;
                    $pkg  = $plan ? ES_Packages::get( $plan->package_id ) : null;
                    if ( ! $pkg && ! empty( $b->user_id ) ) {
                        $assigned_pkg_id = (int) get_user_meta( $b->user_id, ES_Packages::META_PACKAGE_ID, true );
                        $pkg = $assigned_pkg_id ? ES_Packages::get( $assigned_pkg_id ) : null;
                    }
                    $att_map = ! empty( $b->user_id ) ? ES_Packages::get_attendance_map( $b->user_id ) : array();
                    $att = ( ! empty( $b->slot_id ) && isset( $att_map[ (int) $b->slot_id ] ) ) ? $att_map[ (int) $b->slot_id ] : array( 'status' => 'none', 'comment' => '' );
                    $att_label = ES_Packages::att_status_label( $att['status'] );
                ?>
                    <tr>
                        <td>#<?php echo (int) $b->id; ?></td>
                        <td>
                            <strong><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $b->slot_date ) ) ); ?></strong>
                            <div class="es-cell-sub"><?php echo esc_html( substr( $b->start_time, 0, 5 ) . ' – ' . substr( $b->end_time, 0, 5 ) ); ?> (<?php echo (int) $b->duration_min; ?>m)</div>
                        </td>
                        <td><span class="es-tag" style="background:<?php echo esc_attr( $type_color ); ?>20;color:<?php echo esc_attr( $type_color ); ?>"><?php echo esc_html( ES_Helpers::slot_type_label( $b->slot_type ) ); ?></span></td>
                        <td><?php echo esc_html( $b->display_name ); ?></td>
                        <td><a href="mailto:<?php echo esc_attr( $b->user_email ); ?>"><?php echo esc_html( $b->user_email ); ?></a></td>
                        <td>
                            <strong><?php echo $pkg ? esc_html( $pkg->package_name ) : '—'; ?></strong>
                            <?php if ( $plan ) : ?>
                                <div class="es-cell-sub">Total: <?php echo (int) $plan->total_sessions; ?> · Used: <?php echo (int) $plan->used_sessions; ?> · Left: <?php echo (int) ES_Packages::remaining_sessions( $plan ); ?></div>
                            <?php elseif ( $pkg ) : ?>
                                <div class="es-cell-sub">Total sessions: <?php echo (int) ( $pkg->total_sessions ?? 0 ); ?></div>
                            <?php else : ?>
                                <div class="es-cell-sub">No active package</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $plan ) : ?>
                                <span class="es-pill es-pill-success"><?php echo esc_html( ucfirst( $plan->status ) ); ?></span>
                                <?php if ( ! empty( $plan->valid_until ) ) : ?><div class="es-cell-sub">Valid until <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $plan->valid_until ) ) ); ?></div><?php endif; ?>
                            <?php else : ?>
                                <span class="es-cell-sub">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $att_label ); ?></strong>
                            <?php if ( ! empty( $att['comment'] ) ) : ?><div class="es-cell-sub"><?php echo esc_html( $att['comment'] ); ?></div><?php endif; ?>
                        </td>

                        <td>
                            <?php
                            $platform = strtolower(trim($b->platform));
                            $join_link = '';

                            // Detect platform and assign link
                            if ($platform === 'zoom') {
                                $join_link = !empty($b->zoom_join_url) ? $b->zoom_join_url : 'https://zoom.us/join';
                            } elseif ($platform === 'google meet' || $platform === 'google_meet') {
                                $join_link = !empty($b->meet_link) ? $b->meet_link : 'https://meet.google.com/';
                            } elseif ($platform === 'teams' || $platform === 'microsoft teams') {
                                $join_link = !empty($b->teams_link) ? $b->teams_link : 'https://teams.microsoft.com/';
                            } else {
                                // fallback for unknown platform
                                $join_link = home_url('/');
                            }
                            ?>

                            <a href="<?php echo esc_url($join_link); ?>" target="_blank" class="es-btn es-btn-primary">
                                Join <?php echo esc_html( ucfirst($b->platform) ); ?>
                            </a>
                        </td>
                       
                        <td>
                            <?php
                            $join_url  = ! empty( $b->zoom_join_url )  ? $b->zoom_join_url  : '';
                            $start_url = ! empty( $b->zoom_start_url ) ? $b->zoom_start_url : '';
                            // Admin click-to-join: prefer host start_url (signs admin in as host) when present, else join_url
                            $admin_url = $start_url ?: $join_url;
                            ?>
                            <?php if ( $admin_url ) : ?>
                                <a href="<?php echo esc_url( $admin_url ); ?>" target="_blank" rel="noopener" class="es-zoom-btn" title="<?php echo $start_url ? esc_attr__( 'Open as host (Zoom start link)', 'eduschedule' ) : esc_attr__( 'Open Zoom join link', 'eduschedule' ); ?>">
                                    <span class="dashicons dashicons-video-alt2"></span>
                                    <?php echo $start_url ? esc_html__( 'Join as Host', 'eduschedule' ) : esc_html__( 'Join Zoom', 'eduschedule' ); ?>
                                </a>
                                <?php if ( ! empty( $b->zoom_meeting_id ) ) : ?>
                                    <div class="es-cell-sub" style="margin-top:4px">ID: <?php echo esc_html( $b->zoom_meeting_id ); ?></div>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="es-cell-sub">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="es-cell-sub"><?php echo esc_html( human_time_diff( strtotime( $b->created_at ), current_time( 'timestamp' ) ) ); ?> ago</td>
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Delete this booking?<?php echo ! empty( $b->zoom_meeting_id ) ? '\nZoom meeting will also be deleted.' : ''; ?>');" style="display:inline">
                                <?php wp_nonce_field( 'es_delete_booking' ); ?>
                                <input type="hidden" name="action" value="es_delete_booking" />
                                <input type="hidden" name="booking_id" value="<?php echo (int) $b->id; ?>" />
                                <button class="es-btn-link-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Manual Booking Modal -->
<div class="es-modal" id="es-manual-book-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card es-modal-lg">
        <div class="es-modal-head">
            <h2 id="es-mb-title">Manual Booking</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">

            <div class="es-field">
                <label class="es-label">User</label>
                <div class="es-user-picker">
                    <input type="text" id="es-mb-user-search" placeholder="Search by name or email…" autocomplete="off" />
                    <input type="hidden" id="es-mb-user-id" value="" />
                    <div class="es-user-results" id="es-mb-user-results"></div>
                    <div class="es-user-selected" id="es-mb-user-selected" style="display:none">
                        <span class="es-user-selected-info"></span>
                        <button type="button" class="es-user-selected-clear">Change</button>
                    </div>
                </div>
            </div>

            <div class="es-field" id="es-mb-slot-field">
                <label class="es-label">Slot</label>
                <select id="es-mb-slot">
                    <option value="">— Loading slots… —</option>
                </select>
                <span class="es-helper">Only future slots from the next 90 days are shown.</span>
            </div>

            <div class="es-field" id="es-mb-slot-fixed" style="display:none">
                <label class="es-label">Slot</label>
                <div class="es-mb-slot-fixed-info"></div>
            </div>

            <div class="es-field">
                <label class="es-label">Note (optional)</label>
                <textarea id="es-mb-note" rows="3" placeholder="Any context the user should see in the confirmation"></textarea>
            </div>

            <label class="es-checkbox-row">
                <input type="checkbox" id="es-mb-send-email" checked />
                <span>Send confirmation email to the user</span>
            </label>
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-mb-confirm">Create Booking</button>
        </div>
    </div>
</div>
