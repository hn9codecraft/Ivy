<?php
/**
 * 1:1 Students admin page — tabbed detail UI (light theme, matches WP admin)
 * URL: admin.php?page=eduschedule-1to1&user_id=X
 * v4.5 — Schedule edit/delete, global file upload, Renew tab
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$base = admin_url( 'admin.php?page=eduschedule-1to1' );
$detail_mode = ! empty( $selected );

if ( ! function_exists( 'es_user_sessions_left' ) ) {
    function es_user_sessions_left( $user_id ) {
        $plan = ES_Packages::get_active_plan( $user_id );
        if ( ! $plan ) return null;
        return array(
            'total' => (int) ( $plan->total_sessions ?? 0 ),
            'used'  => (int) ( $plan->used_sessions ?? 0 ),
            'left'  => ES_Packages::remaining_sessions( $plan ),
            'plan'  => $plan,
        );
    }
}
?>
<div class="es-admin es-1to1-page">

    <div class="es-page-head">
        <div>
            <h1><?php echo $detail_mode ? esc_html( $selected->display_name . ' Details' ) : '1:1 Students'; ?></h1>
            <p class="es-page-sub"><?php echo $detail_mode ? esc_html( $selected->user_email ) : 'Converted 1-on-1 students — ' . count( $users ) . ' total'; ?></p>
        </div>
        <?php if ( $detail_mode ) : ?>
            <div class="es-page-actions"><a class="es-btn es-btn-ghost" href="<?php echo esc_url( $base ); ?>">← Back to 1:1 Students</a></div>
        <?php endif; ?>
    </div>

    <?php if ( $detail_mode ) : ?>
        <style>.es-1to1-page .es-tabui-shell{display:block}.es-1to1-page .es-tabui-shell>.es-card{display:none}.es-1to1-page .es-tabui-shell>div{width:100%}.es-1to1-page .es-tabui-detail{max-width:none}</style>
    <?php endif; ?>

    <?php if ( ! $detail_mode ) : ?>
        <!-- ===== LIST VIEW ===== -->
        <div class="es-card es-card-flush">
            <table class="es-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Package</th>
                        <th>Sessions Left</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $users ) ) : ?>
                        <tr><td colspan="7" class="es-empty-cell">No 1:1 students yet. Convert leads via After Call.</td></tr>
                    <?php else : foreach ( $users as $u ) :
                        $initial = strtoupper( substr( $u->display_name ?: 'U', 0, 1 ) );
                        $short   = strtolower( substr( $u->display_name ?: 'usr', 0, 3 ) );
                        $sess    = es_user_sessions_left( $u->ID );
                        $u_phone = get_user_meta( $u->ID, 'es_phone', true );
                        $u_pkg   = null;
                        if ( $sess && ! empty( $sess['plan'] ) ) {
                            $u_pkg = ES_Packages::get( $sess['plan']->package_id );
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="es-cell-user">
                                    <div class="es-avatar">
                                        <span class="es-avatar-letter"><?php echo esc_html( $initial ); ?></span>
                                        <span class="es-avatar-tag"><?php echo esc_html( $short ); ?></span>
                                    </div>
                                    <div>
                                        <div class="es-cell-user-name"><?php echo esc_html( $u->display_name ); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><a href="mailto:<?php echo esc_attr( $u->user_email ); ?>"><?php echo esc_html( $u->user_email ); ?></a></td>
                            <td><?php echo esc_html( $u_phone ?: '—' ); ?></td>
                            <td><?php echo $u_pkg ? esc_html( $u_pkg->package_name ) : '—'; ?></td>
                            <td>
                                <?php if ( $sess ) : ?>
                                    <span class="es-badge"><?php echo (int) $sess['left']; ?></span>
                                <?php else : ?>
                                    <span class="es-cell-sub">No active plan</span>
                                <?php endif; ?>
                            </td>
                            <td class="es-cell-sub"><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $u->user_registered ) ) ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( add_query_arg( 'user_id', (int) $u->ID, $base ) ); ?>" class="es-btn es-btn-primary es-btn-sm">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>

    <div class="es-tabui-shell">

        <!-- LEFT: Student list -->
        <div class="es-card" style="padding:12px;">
            <div class="es-tabui-listlabel">Students</div>
            <input type="text" id="es-1to1-search" class="es-tabui-search" placeholder="Search..." />

            <div id="es-1to1-list" class="es-tabui-list">
                <?php if ( empty( $users ) ) : ?>
                    <p class="es-empty-cell" style="font-size:12px;">No 1:1 students yet. Convert leads via After Call.</p>
                <?php else : foreach ( $users as $u ) :
                    $initial = strtoupper( substr( $u->display_name ?: 'U', 0, 2 ) );
                    $active  = ( (int) $u->ID === (int) $uid );
                    $sess    = es_user_sessions_left( $u->ID );
                    $sub     = $sess ? ( $sess['left'] . ' sessions left' ) : 'No active plan';
                ?>
                    <a href="<?php echo esc_url( add_query_arg( 'user_id', $u->ID, $base ) ); ?>"
                       class="es-tabui-item <?php echo $active ? 'is-active' : ''; ?>"
                       data-name="<?php echo esc_attr( strtolower( $u->display_name ) ); ?>">
                        <div class="es-tabui-avatar" style="background:rgba(56,189,248,0.15);color:#0284c7;"><?php echo esc_html( $initial ); ?></div>
                        <div style="flex:1;min-width:0;">
                            <div class="es-tabui-item-name"><?php echo esc_html( $u->display_name ); ?></div>
                            <div class="es-tabui-item-sub"><?php echo esc_html( $sub ); ?></div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- RIGHT: Tabbed detail -->
        <div>
            <?php if ( ! $selected ) : ?>
                <div class="es-card" style="padding:60px 20px;text-align:center;color:var(--es-text-muted);">
                    <div style="font-size:48px;margin-bottom:12px;">👤</div>
                    <div style="font-size:14px;">Select a student from the list</div>
                </div>
            <?php else :
                $profile  = ES_Packages::get_student_profile( $selected->ID );
                $pkg      = null;
                $plan     = ES_Packages::get_active_plan( $selected->ID );
                if ( ! $pkg && $plan ) $pkg = ES_Packages::get( $plan->package_id );

                $total = $plan ? (int) ( $plan->total_sessions ?? 0 ) : ( $pkg ? (int) ( $pkg->total_sessions ?? 0 ) : 0 );
                $used  = $plan ? (int) ( $plan->used_sessions ?? 0 ) : 0;

                // Under the attendance-time model, used_sessions only counts
                // sessions with attendance marked (Present/Absent-without-permission).
                // To prevent over-scheduling, we also count confirmed-but-unattended
                // bookings as "pending" sessions that still consume capacity.
                $pending = 0;
                if ( $plan ) {
                    global $wpdb;
                    $b_tbl = $wpdb->prefix . 'es_bookings';
                    $s_tbl = $wpdb->prefix . 'es_slots';
                    $a_tbl = $wpdb->prefix . 'es_attendance';
                    $pending = (int) $wpdb->get_var( $wpdb->prepare(
                        "SELECT COUNT(DISTINCT bk.slot_id)
                           FROM {$b_tbl} bk
                           INNER JOIN {$s_tbl} sl ON sl.id = bk.slot_id
                           LEFT JOIN  {$a_tbl} at ON at.slot_id  = bk.slot_id
                                                  AND at.user_id  = bk.user_id
                                                  AND (at.group_id IS NULL OR at.group_id = 0)
                          WHERE bk.user_id = %d
                            AND bk.status  = 'confirmed'
                            AND sl.slot_type = '1to1'
                            AND (at.id IS NULL OR at.status NOT IN ('present','absent_unexcused','absent_excused'))",
                        (int) $selected->ID
                    ) );
                }
                $effective_used = $used + $pending;
                $left  = max( 0, $total - $effective_used );
                $pct   = $total > 0 ? round( ( $effective_used / $total ) * 100 ) : 0;
                $dur   = $plan ? (int) ( $plan->months ?? 1 ) : ( $pkg ? (int) ( $pkg->months ?? 1 ) : 0 );

                $sched_blocked = false;
                $sched_reason  = '';
                if ( ! $plan ) {
                    $sched_blocked = true;
                    $sched_reason  = 'No active package — convert/renew via After Call or the Renew tab first.';
                } elseif ( $left <= 0 ) {
                    $sched_blocked = true;
                    $sched_reason  = 'No sessions left on the active package.';
                } elseif ( ! empty( $plan->valid_until ) && strtotime( $plan->valid_until ) < current_time( 'timestamp' ) ) {
                    $sched_blocked = true;
                    $sched_reason  = 'Package expired on ' . date_i18n( 'M j, Y', strtotime( $plan->valid_until ) ) . '.';
                }

                $initial   = strtoupper( substr( $selected->display_name, 0, 2 ) );
                $sessions  = ES_Packages::get_student_sessions( $selected->ID, 12 );
                $schedule  = ES_Packages::get_student_schedule( $selected->ID, 100 );
                $att_map   = ES_Packages::get_attendance_map( $selected->ID );
                $files     = ES_Packages::get_session_files( '1to1', $selected->ID );
                $files_by_slot = ES_Packages::get_session_files_by_slot( '1to1', $selected->ID );
                // Global files (slot_id IS NULL)
                $global_files  = array_values( array_filter( $files, function($f){ return empty($f->slot_id); } ) );
                $videos    = ES_Packages::get_videos( '1to1', $selected->ID );
                $platforms = ES_Helpers::platforms();
                $join_url  = ES_DB::latest_join_url_for_user( $selected->ID );
                $course_posts = ES_Packages::get_course_posts();
                $selected_course_ids = ES_Packages::get_student_course_ids( $selected->ID );
                $selected_course_id  = ES_Packages::first_course_id( $selected_course_ids );

                $all_groups     = ES_Packages::get_all_groups( true );
                $sd_schedulable = ES_Packages::get_schedulable_payments( $selected->ID );
                if ( ! empty( $sd_schedulable ) ) {
                    $sched_blocked = false;
                    $sched_reason  = '';
                } elseif ( $plan && $left <= 0 ) {
                    $sched_blocked = true;
                    $sched_reason  = 'No sessions left on any active package. Use the Renew tab first.';
                }
                $sd_all_paid    = ES_Packages::get_user_payments( $selected->ID, true, '1to1' );
                $all_packages   = ES_Packages::get_all( false );
                $renew_packages = ES_Packages::get_renewable_packages( $selected->ID, false );
                $att_packages   = array();
                foreach ( (array) $sessions as $att_sess ) {
                    $att_pid = isset( $att_sess->pkg_id ) ? (int) $att_sess->pkg_id : ( isset( $att_sess->slot_package_id ) ? (int) $att_sess->slot_package_id : 0 );
                    if ( $att_pid && ! isset( $att_packages[ $att_pid ] ) ) {
                        $att_packages[ $att_pid ] = ! empty( $att_sess->pkg_name ) ? $att_sess->pkg_name : ( ! empty( $att_sess->slot_package_name ) ? $att_sess->slot_package_name : ( 'Package #' . $att_pid ) );
                    }
                }
                $att_package_groups = array();
                foreach ( (array) $sessions as $att_sess ) {
                    $att_pid = isset( $att_sess->pkg_id ) ? (int) $att_sess->pkg_id : ( isset( $att_sess->slot_package_id ) ? (int) $att_sess->slot_package_id : 0 );
                    $key     = $att_pid ? ( 'pkg_' . $att_pid ) : 'no_package';
                    if ( ! isset( $att_package_groups[ $key ] ) ) {
                        $att_course = ! empty( $att_sess->slot_course_name ) ? $att_sess->slot_course_name : ( ! empty( $att_sess->course_id ) ? ES_Packages::course_name( (int) $att_sess->course_id ) : ( ! empty( $att_sess->payment_course_name ) ? $att_sess->payment_course_name : '' ) );
                        $att_package_groups[ $key ] = array(
                            'package_id'   => $att_pid,
                            'package_name' => ! empty( $att_sess->pkg_name ) ? $att_sess->pkg_name : ( ! empty( $att_sess->slot_package_name ) ? $att_sess->slot_package_name : 'No linked package' ),
                            'course_name'  => $att_course,
                            'rows'         => array(),
                        );
                    }
                    $att_package_groups[ $key ]['rows'][] = $att_sess;
                }
            ?>
                <div class="es-tabui-detail" data-target-type="1to1" data-target-id="<?php echo (int) $selected->ID; ?>">

                    <!-- Header -->
                    <div class="es-tabui-header">
                        <div class="es-tabui-header-avatar" style="background:rgba(56,189,248,0.15);color:#0284c7;"><?php echo esc_html( $initial ); ?></div>
                        <div class="es-tabui-header-info">
                            <h2><?php echo esc_html( $selected->display_name ); ?></h2>
                            <div class="es-tabui-header-meta">
                                <span><?php echo esc_html( $profile['email'] ); ?></span>
                                <?php if ( $profile['phone'] ) : ?><span><?php echo esc_html( $profile['phone'] ); ?></span><?php endif; ?>
                                <?php if ( $profile['band'] ) : ?><span><?php echo esc_html( $profile['band'] ); ?></span><?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ( $sched_blocked ) : ?>
                            <button type="button" class="es-btn es-btn-ghost" disabled title="<?php echo esc_attr( $sched_reason ); ?>">+ Schedule</button>
                        <?php else : ?>
                            <button type="button" class="es-btn es-btn-ghost es-open-schedule-modal">+ Schedule</button>
                        <?php endif; ?>
                    </div>

                    <!-- Tab bar -->
                    <div class="es-tabbar">
                        <button type="button" class="es-tab is-active" data-tab="pkg">Package</button>
                        <button type="button" class="es-tab" data-tab="att">Attendance</button>
                        <button type="button" class="es-tab" data-tab="schedule">Schedule</button>
                        <button type="button" class="es-tab" data-tab="renew">Purchase Package</button>
                    </div>

                    <div class="es-tab-body">

                        <!-- ATTENDANCE -->
                        <div class="es-tabpane" data-pane="att" style="display:none;">
                            <div class="es-section-label">Session Attendance</div>
                            <p class="es-att-legend" style="font-size:12px;opacity:.7;margin:0 0 12px;">
                                Scheduling a session uses one session.  · 
                                <strong>Present</strong> = stays used  · 
                                <strong>Absent - without permission</strong> = stays used  · 
                                <strong>Absent - with permission</strong> = session refunded
                            </p>
                            <?php if ( ! empty( $att_packages ) ) : ?>
                                <div class="es-field" style="max-width:360px;margin-bottom:12px;">
                                    <label class="es-label">Filter by Package</label>
                                    <select id="es-att-package-filter" style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;">
                                        <option value="">All packages</option>
                                        <?php foreach ( $att_packages as $att_pid => $att_pname ) : ?>
                                            <option value="<?php echo (int) $att_pid; ?>"><?php echo esc_html( $att_pname ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <?php if ( empty( $sessions ) ) : ?>
                                <p class="es-empty-cell">No sessions yet. Schedule one with the "+ Schedule" button above.</p>
                            <?php else : ?>
                                <style>
                                    .es-attpkg-accordion{display:flex;flex-direction:column;gap:10px}.es-attpkg{border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff}.es-attpkg-head{width:100%;border:0;background:#f8fafc;padding:12px 14px;display:flex;align-items:center;gap:10px;text-align:left;cursor:pointer}.es-attpkg.is-open .es-attpkg-caret{transform:rotate(90deg)}.es-attpkg-caret{transition:.2s}.es-attpkg-title{font-weight:700;color:#111827}.es-attpkg-sub{font-size:12px;color:#64748b}.es-attpkg-body{display:none;padding:10px}.es-attpkg.is-open .es-attpkg-body{display:block}.es-attpkg .es-att-row{margin:0 0 8px}.es-attpkg .es-att-row:last-child{margin-bottom:0}
                                </style>
                                <div class="es-attpkg-accordion">
                                <?php foreach ( $att_package_groups as $gidx => $grp ) : $open = $gidx === array_key_first( $att_package_groups ); ?>
                                    <div class="es-attpkg <?php echo $open ? 'is-open' : ''; ?>" data-package-id="<?php echo (int) $grp['package_id']; ?>">
                                        <button type="button" class="es-attpkg-head">
                                            <span class="es-attpkg-caret">▸</span>
                                            <span style="flex:1;min-width:0;">
                                                <span class="es-attpkg-title"><?php echo esc_html( $grp['package_name'] ); ?></span>
                                                <span class="es-attpkg-sub"> · <?php echo count( $grp['rows'] ); ?> session<?php echo count( $grp['rows'] ) === 1 ? '' : 's'; ?></span>
                                                <?php if ( ! empty( $grp['course_name'] ) ) : ?><span class="es-pill es-pill-info" style="font-size:10px;margin-left:8px;">Course: <?php echo esc_html( $grp['course_name'] ); ?></span><?php endif; ?>
                                            </span>
                                        </button>
                                        <div class="es-attpkg-body">
                                        <?php foreach ( $grp['rows'] as $s ) :
                                            $att = $att_map[ (int) $s->slot_id ] ?? array( 'status' => 'none', 'comment' => '' );
                                            $cur_status = ES_Packages::normalize_att_status( $att['status'] );
                                            $d   = strtotime( $s->slot_date );
                                            $row_pkg_id = isset( $s->pkg_id ) ? (int) $s->pkg_id : ( isset( $s->slot_package_id ) ? (int) $s->slot_package_id : 0 );
                                            $row_course_name = ! empty( $s->slot_course_name ) ? $s->slot_course_name : ( ! empty( $s->course_id ) ? ES_Packages::course_name( (int) $s->course_id ) : ( ! empty( $s->payment_course_name ) ? $s->payment_course_name : '' ) );
                                        ?>
                                            <div class="es-att-row" data-slot-id="<?php echo (int) $s->slot_id; ?>" data-package-id="<?php echo (int) $row_pkg_id; ?>">
                                                <div style="flex:1;min-width:160px;">
                                                    <div class="es-att-title"><?php echo esc_html( $s->title ?: 'Session' ); ?></div>
                                                    <div class="es-att-sub"><?php echo esc_html( date_i18n( 'M j', $d ) . ' · ' . substr( $s->start_time, 0, 5 ) ); ?></div>
                                                    <span class="es-pill es-pill-info" style="margin-top:5px;display:inline-block;font-size:10px;"><?php echo esc_html( $grp['package_name'] ); ?></span>
                                                    <?php if ( $row_course_name ) : ?><span class="es-pill" style="margin-top:5px;display:inline-block;font-size:10px;background:#eef2ff;color:#3730a3;">Course: <?php echo esc_html( $row_course_name ); ?></span><?php endif; ?>
                                                </div>
                                                <div class="es-att-options">
                                                    <button type="button" class="es-att-btn es-att-present <?php echo $cur_status === 'present' ? 'is-on' : ''; ?>" data-status="present" title="Counts as a used session">✓ Present</button>
                                                    <button type="button" class="es-att-btn es-att-absent-no <?php echo $cur_status === 'absent_unexcused' ? 'is-on' : ''; ?>" data-status="absent_unexcused" title="Absent without permission — counts as a used session">✗ Absent - without permission</button>
                                                    <button type="button" class="es-att-btn es-att-absent-yes <?php echo $cur_status === 'absent_excused' ? 'is-on' : ''; ?>" data-status="absent_excused" title="Absent with permission — does NOT count as a used session">○ Absent - with permission</button>
                                                </div>
                                                <input type="text" class="es-att-comment" value="<?php echo esc_attr( $att['comment'] ); ?>" placeholder="Add attendance note..." />
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- PACKAGE -->
                        <div class="es-tabpane" data-pane="pkg">
                            <?php
                            $sd_course_names = ES_Packages::course_names_str( $selected_course_ids );
                            $sd_current_course_name = $sd_course_names;
                            if ( $plan && ! empty( $plan->course_name ) ) {
                                $sd_current_course_name = $plan->course_name;
                            }
                            ?>
                            <?php if ( $sched_blocked ) : ?>
                                <div class="es-alert es-alert-warning" style="margin-bottom:14px;padding:10px 12px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;color:#92400e;font-size:13px;line-height:1.5;">
                                    <strong>Scheduling disabled.</strong> <?php echo esc_html( $sched_reason ); ?>
                                    <?php if ( ! $plan || $left <= 0 || ( ! empty( $plan->valid_until ) && strtotime( $plan->valid_until ) < current_time( 'timestamp' ) ) ) : ?>
                                         <a href="#" class="es-tab-link" data-goto="renew" style="color:#92400e;font-weight:600;text-decoration:underline;">Go to Renew tab →</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $sd_all_paid ) ) :
                                $now_ts = current_time( 'timestamp' );
                            ?>
                                <div class="es-pkgacc-wrap" style="margin-bottom:14px;">
                                    <div class="es-section-label" style="margin-bottom:8px;">All Packages (<?php echo count( $sd_all_paid ); ?>)</div>
                                    <div class="es-pkgacc">
                                        <?php foreach ( $sd_all_paid as $i => $payRow ) :
                                            $payPkg = ES_Packages::get( (int) $payRow->package_id );
                                            $p_total = (int) ( $payRow->total_sessions ?? 0 );
                                            $p_used  = (int) ( $payRow->used_sessions ?? 0 );
                                            $is_active_plan = $plan && (int) $plan->id === (int) $payRow->id;
                                            // For the active plan, subtract pending (scheduled but unattended) sessions
                                            $p_pending = $is_active_plan ? max( 0, (int) $pending ) : 0;
                                            $p_left  = max( 0, $p_total - $p_used - $p_pending );
                                            $p_pct   = $p_total > 0 ? round( ( ( $p_used + $p_pending ) / $p_total ) * 100 ) : 0;
                                            $is_expired = ! empty( $payRow->valid_until ) && strtotime( $payRow->valid_until ) < $now_ts;
                                            $is_empty   = ( $p_total > 0 && $p_left <= 0 );
                                            if ( $is_expired )      { $st_cls = 'es-pill-danger';  $st_txt = 'EXPIRED'; }
                                            elseif ( $is_empty )    { $st_cls = 'es-pill-warning'; $st_txt = 'NO SESSIONS LEFT'; }
                                            else                    { $st_cls = 'es-pill-success'; $st_txt = 'ACTIVE'; }
                                            $is_active_plan = $plan && (int) $plan->id === (int) $payRow->id;
                                        ?>
                                            <div class="es-pkgacc-row<?php echo $is_active_plan ? ' is-current' : ''; ?>">
                                                <button type="button" class="es-pkgacc-head" aria-expanded="<?php echo $is_active_plan ? 'true' : 'false'; ?>">
                                                    <span class="es-pkgacc-caret">›</span>
                                                    <span class="es-pkgacc-name">
                                                        <?php echo esc_html( $payRow->package_name ? $payRow->package_name : ( 'Package #' . (int) $payRow->package_id ) ); ?>
                                                        <?php if ( $is_active_plan ) : ?><span class="es-pill es-pill-info" style="margin-left:6px;font-size:10px;">CURRENT</span><?php endif; ?>
                                                    </span>
                                                    <span class="es-pkgacc-sub">
                                                        <?php echo (int) $p_used; ?> attended<?php if ( $p_pending > 0 ) : ?> · <?php echo (int) $p_pending; ?> scheduled<?php endif; ?> · <?php echo (int) $p_left; ?> left of <?php echo (int) $p_total; ?><?php if ( ! empty( $payRow->course_name ) ) : ?> · <?php echo esc_html( $payRow->course_name ); ?><?php endif; ?>
                                                    </span>
                                                    <span class="es-pill <?php echo esc_attr( $st_cls ); ?>"><?php echo esc_html( $st_txt ); ?></span>
                                                </button>
                                                <div class="es-pkgacc-body" <?php echo $is_active_plan ? '' : 'style="display:none;"'; ?>>
                                                    <div class="es-package-detail-grid">
                                                        <div><span>Package</span><strong><?php echo esc_html( $payRow->package_name ? $payRow->package_name : '—' ); ?></strong></div>
                                                        <?php $pay_course_name = ! empty( $payRow->course_name ) ? $payRow->course_name : ''; ?>
                                                        <?php if ( $pay_course_name !== '' ) : ?><div><span>Course</span><strong><?php echo esc_html( $pay_course_name ); ?></strong></div><?php endif; ?>
                                                        <?php if ( $payPkg && ! empty( $payPkg->sub_heading ) ) : ?><div><span>Sub Heading</span><strong><?php echo esc_html( $payPkg->sub_heading ); ?></strong></div><?php endif; ?>
                                                        <?php $p_dur = (int) ( $payRow->months ?? 0 ); ?>
                                                        <?php if ( $p_dur > 0 ) : ?><div><span>Duration</span><strong><?php echo (int) $p_dur; ?> month<?php echo $p_dur > 1 ? 's' : ''; ?></strong></div><?php endif; ?>
                                                        <div><span>Monthly Sessions</span><strong><?php echo (int) ( $payRow->monthly_session_limit ?? 0 ); ?></strong></div>
                                                        <div><span>Total Sessions</span><strong><?php echo (int) $p_total; ?></strong></div>
                                                        <div><span>Attended (Used)</span><strong><?php echo (int) $p_used; ?></strong></div>
                                                        <?php if ( $p_pending > 0 ) : ?><div><span>Scheduled (Pending)</span><strong><?php echo (int) $p_pending; ?></strong></div><?php endif; ?>
                                                        <div><span>Remaining</span><strong><?php echo (int) $p_left; ?></strong></div>
                                                        <?php if ( ! empty( $payRow->valid_until ) ) : ?><div><span>Valid Until</span><strong><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $payRow->valid_until ) ) ); ?></strong></div><?php endif; ?>
                                                        <?php if ( ! empty( $payRow->amount ) ) : ?><div><span>Paid</span><strong><?php echo esc_html( ES_Helpers::format_price( $payRow->amount, ! empty( $payRow->currency ) ? $payRow->currency : 'INR' ) ); ?></strong></div><?php endif; ?>
                                                    </div>
                                                    <div class="es-usage-bar" style="margin-top:10px;"><div class="es-usage-bar-fill" style="width:<?php echo (int) $p_pct; ?><?php echo '%'; ?>;"></div></div>
                                                    <?php if ( $payPkg && ! empty( $payPkg->description ) ) : ?>
                                                        <div class="es-package-desc"><?php echo nl2br( esc_html( $payPkg->description ) ); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="es-card" style="padding:18px 20px;">
                                <div class="es-section-label">Student Details</div>
                                <form id="es-profile-form" data-user-id="<?php echo (int) $selected->ID; ?>">
                                    <?php
                                    $rows = array(
                                        'parent' => array( 'Parent', $profile['parent'] ),
                                        'phone'  => array( 'Phone',  $profile['phone'] ),
                                        'source' => array( 'Source', $profile['source'] ),
                                        'goal'   => array( 'Goal',   $profile['goal'] ),
                                        'band'   => array( 'Level / Band', $profile['band'] ),
                                    );
                                    foreach ( $rows as $k => $info ) : ?>
                                        <div class="es-detail-row">
                                            <span><?php echo esc_html( $info[0] ); ?></span>
                                            <input type="text" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $info[1] ); ?>" placeholder="—" />
                                        </div>
                                    <?php endforeach; ?>
                                    <div style="margin-top:14px;">
                                        <div class="es-section-label" style="margin-bottom:6px;">Notes</div>
                                        <textarea name="notes" rows="2" placeholder="Notes..."><?php echo esc_textarea( $profile['notes'] ); ?></textarea>
                                    </div>
                                    <div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;margin-top:12px;">
                                        <span class="es-profile-msg" style="display:none;font-size:12px;"></span>
                                        <button type="button" class="es-btn es-btn-primary es-btn-sm es-profile-save">Save Details</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- SCHEDULE -->
                        <div class="es-tabpane" data-pane="schedule" style="display:none;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                                <div class="es-section-label" style="margin:0;">All Scheduled Meetings</div>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <?php if ( $sched_blocked ) : ?>
                                        <button type="button" class="es-btn es-btn-primary es-btn-sm" disabled title="<?php echo esc_attr( $sched_reason ); ?>">+ Schedule</button>
                                    <?php else : ?>
                                        <button type="button" class="es-btn es-btn-primary es-btn-sm es-open-schedule-modal">+ Schedule</button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Global files (not tied to a session) -->
                            <?php if ( ! empty( $global_files ) ) : ?>
                                <div class="es-card" style="padding:14px 16px;margin-bottom:14px;background:#f8fafc;border:1px solid #e2e8f0;">
                                    <div class="es-section-label" style="margin-bottom:8px;">Global Files / Videos</div>
                                    <div id="es-global-files-list" style="display:flex;flex-wrap:wrap;gap:8px;">
                                        <?php foreach ( $global_files as $gf ) : ?>
                                            <div class="es-file-chip" data-file-id="<?php echo (int) $gf->id; ?>" style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;">
                                                <span class="es-pill es-pill-info" style="font-size:10px;padding:2px 6px;"><?php echo esc_html( strtoupper( $gf->file_type ) ); ?></span>
                                                <a href="<?php echo esc_url( $gf->file_url ); ?>" target="_blank" rel="noopener" style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;"><?php echo esc_html( $gf->file_name ); ?></a>
                                                <button type="button" class="es-delete-file-btn" data-file-id="<?php echo (int) $gf->id; ?>" title="Delete file" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:0;">×</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div id="es-global-files-list" style="display:none;margin-bottom:14px;"></div>
                            <?php endif; ?>

                            <?php
                            // Build package-wise groups for Schedule accordion
                            $sched_pkg_groups = array();
                            foreach ( (array) $schedule as $srow ) {
                                $srow_pkg_id   = ! empty( $srow->pkg_id ) ? (int) $srow->pkg_id : ( ! empty( $srow->slot_package_id ) ? (int) $srow->slot_package_id : 0 );
                                $srow_pkg_name = ! empty( $srow->pkg_name ) ? $srow->pkg_name : ( ! empty( $srow->slot_package_name ) ? $srow->slot_package_name : '' );
                                $srow_key      = $srow_pkg_id ? 'pkg_' . $srow_pkg_id : 'no_pkg';
                                if ( ! isset( $sched_pkg_groups[ $srow_key ] ) ) {
                                    $sched_pkg_groups[ $srow_key ] = array(
                                        'pkg_id'   => $srow_pkg_id,
                                        'pkg_name' => $srow_pkg_name ?: 'No Package',
                                        'rows'     => array(),
                                    );
                                }
                                $sched_pkg_groups[ $srow_key ]['rows'][] = $srow;
                            }
                            ?>
                            <?php if ( empty( $schedule ) ) : ?>
                                <p class="es-empty-cell">No meetings scheduled yet.</p>
                            <?php else : ?>
                                <style>
                                .es-schedpkg{border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:10px}
                                .es-schedpkg-head{width:100%;border:0;background:#f8fafc;padding:12px 14px;display:flex;align-items:center;gap:10px;text-align:left;cursor:pointer;font:inherit}
                                .es-schedpkg-head:hover{background:#f1f5f9}
                                .es-schedpkg.is-open .es-schedpkg-caret{transform:rotate(90deg)}
                                .es-schedpkg-caret{display:inline-block;color:#6d28d9;font-size:18px;width:16px;transition:.18s}
                                .es-schedpkg-name{flex:1;font-size:14px;font-weight:700;color:#1e293b}
                                .es-schedpkg-sub{font-size:12px;color:#64748b}
                                .es-schedpkg-body{display:none;border-top:1px solid #eef0f3}
                                .es-schedpkg.is-open .es-schedpkg-body{display:block}
                                </style>
                                <div class="es-schedpkg-accordion">
                                <?php foreach ( $sched_pkg_groups as $sgidx => $sgrp ) :
                                    $sg_open = $sgidx === array_key_first( $sched_pkg_groups );
                                    $sg_payment = null;
                                    if ( $sgrp['pkg_id'] && ! empty( $sd_all_paid ) ) {
                                        foreach ( $sd_all_paid as $sdp ) {
                                            if ( (int) $sdp->package_id === $sgrp['pkg_id'] ) { $sg_payment = $sdp; break; }
                                        }
                                    }
                                    $sg_total = $sg_payment ? (int) ( $sg_payment->total_sessions ?? 0 ) : 0;
                                    $sg_used  = $sg_payment ? (int) ( $sg_payment->used_sessions ?? 0 ) : 0;
                                    $sg_left  = max( 0, $sg_total - $sg_used );
                                ?>
                                <div class="es-schedpkg <?php echo $sg_open ? 'is-open' : ''; ?>">
                                    <button type="button" class="es-schedpkg-head">
                                        <span class="es-schedpkg-caret">›</span>
                                        <span class="es-schedpkg-name"><?php echo esc_html( $sgrp['pkg_name'] ); ?></span>
                                        <span class="es-schedpkg-sub"><?php echo count( $sgrp['rows'] ); ?> session<?php echo count( $sgrp['rows'] ) !== 1 ? 's' : ''; ?></span>
                                        <?php if ( $sg_total > 0 ) : ?>
                                            <span class="es-pill es-pill-info" style="font-size:11px;"><?php echo (int) $sg_used; ?> used · <?php echo (int) $sg_left; ?> left</span>
                                        <?php endif; ?>
                                    </button>
                                    <div class="es-schedpkg-body">
                                    <div class="es-card es-card-flush" style="border:0;border-radius:0;">
                                    <table class="es-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Duration</th>
                                                <th>Title</th>
                                                <th>Platform</th>
                                                <th>Status</th>
                                                <th>Meeting</th>
                                                <th>Files / Videos</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $sgrp['rows'] as $row ) :
                                                $d = strtotime( $row->slot_date );
                                                $st_status = $row->booking_status ? ucfirst( $row->booking_status ) : 'Confirmed';
                                                $end_lbl = $row->end_time ? substr( $row->end_time, 0, 5 ) : '';
                                                $row_files = isset( $files_by_slot[ (int) $row->slot_id ] ) ? $files_by_slot[ (int) $row->slot_id ] : array();
                                                $row_course_name = ! empty( $row->slot_course_name ) ? $row->slot_course_name : ( ! empty( $row->course_id ) ? ES_Packages::course_name( (int) $row->course_id ) : ( ! empty( $row->payment_course_name ) ? $row->payment_course_name : '' ) );
                                            ?>
                                                <tr data-slot-id="<?php echo (int) $row->slot_id; ?>">
                                                    <td><strong><?php echo esc_html( date_i18n( 'M j, Y', $d ) ); ?></strong></td>
                                                    <td><?php echo esc_html( substr( $row->start_time, 0, 5 ) . ( $end_lbl ? ' – ' . $end_lbl : '' ) ); ?></td>
                                                    <td><?php echo (int) $row->duration_min; ?> min</td>
                                                    <td>
                                                        <?php echo esc_html( $row->title ?: 'Session' ); ?>
                                                        <?php if ( $row_course_name ) : ?><div class="es-cell-sub">Course: <?php echo esc_html( $row_course_name ); ?></div><?php endif; ?>
                                                    </td>
                                                    <td><?php echo esc_html( $row->platform ?: '—' ); ?></td>
                                                    <td><span class="es-pill es-pill-success"><?php echo esc_html( $st_status ); ?></span></td>
                                                    <td>
                                                        <?php if ( ! empty( $row->zoom_join_url ) ) : ?>
                                                            <a href="<?php echo esc_url( $row->zoom_join_url ); ?>" target="_blank" rel="noopener" class="es-btn es-btn-ghost es-btn-sm">Join</a>
                                                            <?php if ( ! empty( $row->zoom_meeting_id ) ) : ?>
                                                                <div class="es-cell-sub">ID: <?php echo esc_html( $row->zoom_meeting_id ); ?></div>
                                                            <?php endif; ?>
                                                        <?php else : ?>
                                                            <span class="es-cell-sub">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
                                                            <?php if ( ! empty( $row_files ) ) : ?>
                                                                <div class="es-slot-files-wrap" data-slot-id="<?php echo (int) $row->slot_id; ?>" style="display:flex;flex-direction:column;gap:4px;">
                                                                    <?php foreach ( $row_files as $f ) : ?>
                                                                        <div class="es-slot-file-item" data-file-id="<?php echo (int) $f->id; ?>" style="display:inline-flex;align-items:center;gap:6px;">
                                                                            <a href="<?php echo esc_url( $f->file_url ); ?>" target="_blank" rel="noopener" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;">
                                                                                <span class="es-pill es-pill-info" style="font-size:10px;padding:2px 6px;"><?php echo esc_html( strtoupper( $f->file_type ) ); ?></span>
                                                                                <span style="text-overflow:ellipsis;overflow:hidden;white-space:nowrap;max-width:140px;display:inline-block;"><?php echo esc_html( $f->file_name ); ?></span>
                                                                            </a>
                                                                            <button type="button" class="es-delete-file-btn" data-file-id="<?php echo (int) $f->id; ?>" title="Delete" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:0 2px;">×</button>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php else : ?>
                                                                <div class="es-slot-files-wrap" data-slot-id="<?php echo (int) $row->slot_id; ?>"></div>
                                                            <?php endif; ?>
                                                            <input type="file" class="es-slot-file-input" data-slot-id="<?php echo (int) $row->slot_id; ?>" data-target-type="1to1" data-target-id="<?php echo (int) $selected->ID; ?>" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.webm,.mkv,.avi" multiple style="display:none;" />
                                                            <button type="button" class="es-btn es-btn-ghost es-btn-sm es-slot-add-files" data-slot-id="<?php echo (int) $row->slot_id; ?>">
                                                                <span class="dashicons dashicons-plus-alt2" style="font-size:14px;width:14px;height:14px;vertical-align:text-bottom;"></span> Add
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td style="white-space:nowrap;">
                                                        <button type="button" class="es-btn es-btn-ghost es-btn-sm es-edit-session-btn"
                                                            data-slot-id="<?php echo (int) $row->slot_id; ?>"
                                                            data-slot-date="<?php echo esc_attr( $row->slot_date ); ?>"
                                                            data-start-time="<?php echo esc_attr( substr( $row->start_time, 0, 5 ) ); ?>"
                                                            data-duration="<?php echo (int) $row->duration_min; ?>"
                                                            data-platform="<?php echo esc_attr( $row->platform ); ?>"
                                                            data-title="<?php echo esc_attr( $row->title ); ?>"
                                                            data-notes="<?php echo esc_attr( $row->notes ?? '' ); ?>"
                                                            title="Edit session">✏️ Edit</button>
                                                        <button type="button" class="es-btn es-btn-danger es-btn-sm es-delete-session-btn"
                                                            data-slot-id="<?php echo (int) $row->slot_id; ?>"
                                                            data-title="<?php echo esc_attr( $row->title ?: 'Session' ); ?>"
                                                            title="Delete session">🗑 Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    </div>
                                    </div>
                                    <!-- Package-wise global files (slot_id IS NULL, matching package_id) -->
                                    <?php
                                    $pkg_global_files = array_values( array_filter( $global_files, function($gf) use ($sgrp) {
                                        return $sgrp['pkg_id'] > 0
                                            ? (int) ( $gf->package_id ?? 0 ) === (int) $sgrp['pkg_id']
                                            : empty( $gf->package_id );
                                    } ) );
                                    ?>
                                    <?php if ( ! empty( $pkg_global_files ) ) : ?>
                                        <div style="padding:10px 14px;border-top:1px solid #f1f5f9;">
                                            <div class="es-section-label" style="font-size:11px;margin-bottom:6px;">Package Files</div>
                                            <div class="es-slot-files-wrap" id="es-pkg-files-<?php echo (int) $sgrp['pkg_id']; ?>" style="display:flex;flex-wrap:wrap;gap:6px;">
                                                <?php foreach ( $pkg_global_files as $gf ) : ?>
                                                    <div class="es-file-chip" data-file-id="<?php echo (int) $gf->id; ?>" style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;">
                                                        <span class="es-pill es-pill-info" style="font-size:10px;padding:2px 6px;"><?php echo esc_html( strtoupper( $gf->file_type ) ); ?></span>
                                                        <a href="<?php echo esc_url( $gf->file_url ); ?>" target="_blank" rel="noopener" style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;"><?php echo esc_html( $gf->file_name ); ?></a>
                                                        <button type="button" class="es-delete-file-btn" data-file-id="<?php echo (int) $gf->id; ?>" title="Delete file" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:0;">×</button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div id="es-pkg-files-<?php echo (int) $sgrp['pkg_id']; ?>" style="display:none;padding:10px 14px;border-top:1px solid #f1f5f9;"></div>
                                    <?php endif; ?>
                                    <div style="padding:10px 14px;display:flex;justify-content:flex-end;border-top:1px solid #f1f5f9;">
                                        <button type="button" class="es-btn es-btn-ghost es-btn-sm es-open-pkg-upload"
                                            data-pkg-id="<?php echo (int) $sgrp['pkg_id']; ?>"
                                            data-pkg-name="<?php echo esc_attr( $sgrp['pkg_name'] ); ?>"
                                            title="Upload files for this package">
                                            <span class="dashicons dashicons-upload" style="font-size:14px;width:14px;height:14px;vertical-align:text-bottom;"></span> Upload Files
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ===================== RENEW TAB ===================== -->
                        <div class="es-tabpane" data-pane="renew" style="display:none;">
                            <div class="es-section-label" style="margin-bottom:10px;">Purchase Package</div>
                            <p style="font-size:13px;color:var(--es-text-muted);margin:0 0 16px;line-height:1.5;">
                                Use this tab when the student has completed their current package and needs a new one, or when their plan has expired.
                                This creates a package-selection link and sends it by email. The package becomes active only after the student completes selection/payment.
                            </p>

                            <div class="es-aftercall-linkbox" style="margin-bottom:16px;">
                                <div class="es-aftercall-linklabel">Package Selection Link <span>(created after submit)</span></div>
                                <div class="es-aftercall-linkrow">
                                    <input type="text" id="es-renew-link" readonly value="" placeholder="The renewal link will appear here after submit" />
                                    <button type="button" class="es-btn es-btn-primary es-copy-renew-link" data-target="#es-renew-link">Copy</button>
                                </div>
                            </div>

                            <?php if ( $plan && $left > 0 && ( empty( $plan->valid_until ) || strtotime( $plan->valid_until ) >= current_time( 'timestamp' ) ) ) : ?>
                                <div class="es-alert" style="padding:10px 12px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;color:#065f46;font-size:13px;margin-bottom:16px;">
                                    <strong>Note:</strong> This student still has an active package with <?php echo (int) $left; ?> session<?php echo $left === 1 ? '' : 's'; ?> left<?php if ( ! empty( $plan->valid_until ) ) : ?> (valid until <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $plan->valid_until ) ) ); ?>)<?php endif; ?>. The Renew dropdown hides this active package until it expires or has no sessions left.
                                </div>
                            <?php endif; ?>

                            <div class="es-card" style="padding:20px;">
                                <div id="es-renew-form">
                                    <?php
                                    // All active packages for JS-based filtering by type
                                    $all_renew_packages = ES_Packages::get_all( true );
                                    ?>
                                    <div class="es-field" style="margin-bottom:14px;">
                                        <label class="es-label">Package Type</label>
                                        <select id="es-renew-type" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                                            <option value="">— All Types —</option>
                                            <option value="1to1">1:1</option>
                                            <option value="group">Group</option>
                                            <option value="consultancy">Consultancy</option>
                                        </select>
                                    </div>

                                    <div class="es-field" style="margin-bottom:14px;">
                                        <label class="es-label">Select Package</label>
                                        <select id="es-renew-package" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                                            <option value="">— Select a package —</option>
                                            <?php foreach ( $all_renew_packages as $ap ) :
                                                $ap_type = ! empty( $ap->package_type ) ? $ap->package_type : '1to1';
                                            ?>
                                                <option value="<?php echo (int) $ap->id; ?>"
                                                    data-sessions="<?php echo (int) ( $ap->total_sessions ?? 0 ); ?>"
                                                    data-months="<?php echo (int) ( $ap->months ?? 1 ); ?>"
                                                    data-price="<?php echo esc_attr( $ap->price ?? 0 ); ?>"
                                                    data-type="<?php echo esc_attr( $ap_type ); ?>">
                                                    <?php echo esc_html( $ap->package_name ); ?>
                                                    (<?php echo (int) ( $ap->total_sessions ?? 0 ); ?> sessions · <?php echo (int) ( $ap->months ?? 1 ); ?> months)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ( empty( $all_renew_packages ) ) : ?>
                                            <small class="es-helper" style="display:block;margin-top:6px;color:#b45309;">No packages available. Add packages from the Packages page first.</small>
                                        <?php endif; ?>
                                    </div>

                                    <div id="es-renew-pkg-preview" style="display:none;padding:12px 14px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:10px;margin-bottom:14px;font-size:13px;">
                                        <strong id="es-renew-pkg-name"></strong>
                                        <span id="es-renew-pkg-meta" style="color:#6d28d9;margin-left:8px;"></span>
                                        <div style="margin-top:4px;color:#6b7280;" id="es-renew-valid-note"></div>
                                    </div>

                                    <?php if ( ! empty( $course_posts ) ) : ?>
                                    <div class="es-field" style="margin-bottom:14px;">
                                        <label class="es-label">Course <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label>
                                        <div class="es-course-select-wrap">
                                            <select id="es-renew-course" class="es-course-select" style="width:100%;">
                                                <option value="">Select course...</option>
                                                <?php foreach ( $course_posts as $cp ) : ?>
                                                    <option value="<?php echo (int) $cp->ID; ?>" <?php selected( (int) $cp->ID, $selected_course_id ); ?>><?php echo esc_html( $cp->post_title ); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="es-field" style="margin-bottom:14px;">
                                        <label class="es-label">Notes / Comments <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label>
                                        <textarea id="es-renew-comments" rows="2" placeholder="Renewal notes..." style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;"></textarea>
                                    </div>

                                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:16px;cursor:pointer;">
                                        <input type="checkbox" id="es-renew-email" checked /> Send renewal confirmation email to student
                                    </label>

                                    <div id="es-renew-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>

                                    <div style="display:flex;justify-content:flex-end;">
                                        <button type="button" class="es-btn es-btn-primary" id="es-renew-submit" data-user-id="<?php echo (int) $selected->ID; ?>" <?php disabled( empty( $renew_packages ) ); ?>>
                                            <span class="dashicons dashicons-update" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;margin-right:4px;"></span>
                                            Purchase Package
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SCHEDULE MODAL CONTENT -->
                        <div class="es-schedule-holder">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px;">
                                <div class="es-section-label" style="margin:0;">Sessions</div>
                                <button type="button" class="es-btn es-btn-primary es-btn-sm" id="es-sched-new-btn">+ New</button>
                            </div>

                            <div id="es-sched-list" style="margin-bottom:18px;">
                                <?php if ( empty( $sessions ) ) : ?>
                                    <p class="es-empty-cell">No sessions scheduled.</p>
                                <?php else : foreach ( $sessions as $s ) : $d = strtotime( $s->slot_date ); ?>
                                    <div class="es-sess-item">
                                        <div class="es-sess-date">
                                            <div class="es-sess-day"><?php echo esc_html( date_i18n( 'j', $d ) ); ?></div>
                                            <div class="es-sess-mon"><?php echo esc_html( date_i18n( 'M', $d ) ); ?></div>
                                        </div>
                                        <div style="flex:1;">
                                            <div class="es-sess-time"><?php echo esc_html( substr( $s->start_time, 0, 5 ) . ' · ' . (int) $s->duration_min . ' min' ); ?></div>
                                            <div class="es-sess-desc"><?php echo esc_html( $s->title ?: 'Session' ); ?></div>
                                        </div>
                                        <span class="es-status-pill">Confirmed</span>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>

                            <div id="es-schedule-modal" class="es-schedule-modal" aria-hidden="true"><div class="es-schedule-modal-overlay"></div><div id="es-schedule-session" class="es-newsession es-schedule-modal-card" data-target-type="1to1" data-target-id="<?php echo (int) $selected->ID; ?>"><button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
                                <div class="es-section-label">New Session</div>

                                <?php if ( ! empty( $sd_schedulable ) ) : ?>
                                    <div class="es-ss-pkg-pick" style="margin:0 0 16px;padding:14px 16px;background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%);border:1px solid #c4b5fd;border-radius:12px;">
                                        <label class="es-label" style="color:#6d28d9;font-weight:600;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                                            <span class="dashicons dashicons-archive" style="font-size:16px;width:16px;height:16px;"></span>
                                            Purchased Package
                                        </label>
                                        <select id="es-ss-payment" style="width:100%;padding:9px 12px;border:1px solid #c4b5fd;border-radius:8px;background:#fff;font-size:14px;font-weight:500;">
                                            <?php foreach ( $sd_schedulable as $sp ) :
                                                $sp_label = $sp->package_name ? $sp->package_name : ( 'Package #' . (int) $sp->package_id );
                                                $sp_course = ! empty( $sp->course_name ) ? $sp->course_name : '';
                                                $sp_until = ! empty( $sp->valid_until ) ? date_i18n( 'M j, Y', strtotime( $sp->valid_until ) ) : '';
                                            ?>
                                                <option value="<?php echo (int) $sp->id; ?>"
                                                    data-package-name="<?php echo esc_attr( $sp_label ); ?>"
                                                    data-course-name="<?php echo esc_attr( $sp_course ); ?>"
                                                    data-total="<?php echo (int) ( $sp->total_sessions ?? 0 ); ?>"
                                                    data-used="<?php echo (int) ( $sp->used_sessions ?? 0 ); ?>"
                                                    data-left="<?php echo (int) $sp->remaining; ?>"
                                                    data-valid-until="<?php echo esc_attr( $sp_until ); ?>">
                                                    <?php echo esc_html( $sp_label ); ?><?php if ( $sp_course !== '' ) : ?> · Course: <?php echo esc_html( $sp_course ); ?><?php endif; ?> · <?php echo (int) $sp->remaining; ?> session<?php echo $sp->remaining === 1 ? '' : 's'; ?> left<?php if ( $sp_until ) : ?> · until <?php echo esc_html( $sp_until ); ?><?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="es-ss-payment-preview" class="es-ss-payment-preview" style="display:none;margin-top:10px;"></div>
                                        <small style="margin-top:6px;display:block;font-size:12px;color:#6d28d9;">
                                            <?php if ( count( $sd_schedulable ) === 1 ) : ?>
                                                One active package. This session will be consumed from it.
                                            <?php else : ?>
                                                <?php echo count( $sd_schedulable ); ?> active packages — pick which one this session consumes from.
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <?php /* Course is taken from the selected purchased package. No manual course field in session popup. */ ?>
                                <div class="es-modal-row">
                                    <div class="es-field"><label class="es-label">Date</label><input type="date" id="es-ss-date" min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" /></div>
                                    <div class="es-field"><label class="es-label">Start Time</label><input type="time" id="es-ss-time" /></div>
                                </div>
                                <div class="es-modal-row">
                                    <div class="es-field"><label class="es-label">Duration (min)</label><input type="number" id="es-ss-duration" value="60" min="5" step="5" /></div>
                                    <div class="es-field"><label class="es-label">Platform</label><select id="es-ss-platform"><?php foreach ( $platforms as $p ) : ?><option value="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></option><?php endforeach; ?></select></div>
                                </div>
                                <div class="es-field"><label class="es-label">Session Title <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label><input type="text" id="es-ss-title" placeholder="e.g. IELTS Speaking Practice" /></div>
                                <div class="es-field"><label class="es-label">Notes <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label><textarea id="es-ss-notes" rows="2" placeholder="Agenda / notes"></textarea></div>

                                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:6px 0 12px;cursor:pointer;color:var(--es-text-soft);"><input type="checkbox" id="es-ss-email" checked /> Send confirmation email</label>
                                <div id="es-ss-msg" style="display:none;font-size:13px;margin-bottom:10px;"></div>
                                <div style="display:flex;justify-content:flex-end;"><button type="button" class="es-btn es-btn-primary" id="es-ss-submit"><span class="dashicons dashicons-video-alt2"></span> Schedule Session</button></div>
                            </div></div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- v4.6: Global Upload Modal — supports files, images, videos (package-wise) -->
<div id="es-global-upload-modal" class="es-schedule-modal" aria-hidden="true" style="display:none;">
    <div class="es-schedule-modal-overlay"></div>
    <div class="es-schedule-modal-card" style="max-width:520px;">
        <button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
        <div class="es-section-label" style="margin-bottom:4px;">Upload Files / Images / Videos</div>
        <p style="font-size:12.5px;color:var(--es-text-muted);margin:0 0 4px;line-height:1.6;">
            Attach files to this student. Visible in the Schedule tab under the package.
        </p>
        <div id="es-gu-pkg-label" style="display:none;font-size:12px;font-weight:600;color:#6d28d9;margin-bottom:12px;padding:6px 10px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:8px;"></div>
        <input type="hidden" id="es-gu-target-type" value="1to1" />
        <input type="hidden" id="es-gu-target-id" value="<?php echo $detail_mode && $selected ? (int) $selected->ID : 0; ?>" />
        <input type="hidden" id="es-gu-package-id" value="" />
        <div class="es-field">
            <label class="es-label" style="margin-bottom:8px;">Choose file(s)</label>
            <label class="es-upload-drop-zone" id="es-gu-drop-zone">
                <input type="file" id="es-gu-file" multiple
                    accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.webm,.mkv,.avi"
                    style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;" />
                <div class="es-upload-drop-inner">
                    <div class="es-upload-drop-icon">
                        <span class="dashicons dashicons-upload" style="font-size:32px;width:32px;height:32px;color:#6d28d9;"></span>
                    </div>
                    <div class="es-upload-drop-text">
                        <strong>Drop files here</strong> or <span class="es-upload-browse-link">browse</span>
                    </div>
                    <div class="es-upload-drop-hint">PDF, DOC/DOCX, PPT/PPTX, JPG/PNG/GIF/WebP, MP4/MOV/WebM</div>
                </div>
            </label>
            <div id="es-gu-preview" class="es-upload-preview" style="display:none;margin-top:10px;"></div>
        </div>
        <div id="es-gu-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
            <button type="button" class="es-btn es-btn-ghost es-schedule-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-gu-submit">
                <span class="dashicons dashicons-upload" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;"></span> Upload
            </button>
        </div>
    </div>
</div>

<!-- Join Group Modal (read-only — does not change 1:1 data) -->
<div id="es-join-group-modal" class="es-schedule-modal" aria-hidden="true" style="display:none;">
    <div class="es-schedule-modal-overlay"></div>
    <div class="es-schedule-modal-card" style="max-width:480px;">
        <button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
        <div class="es-section-label" style="margin-bottom:4px;">Add to Group</div>
        <p style="font-size:12.5px;color:var(--es-text-muted);margin:0 0 16px;line-height:1.6;">
            This will add the 1:1 student to a group so they can attend group sessions.
            <strong>No 1:1 data is changed</strong> — their package, sessions, and schedule remain untouched.
        </p>
        <input type="hidden" id="es-jg-user-id" value="" />
        <div class="es-field" style="margin-bottom:14px;">
            <label class="es-label">Student</label>
            <div id="es-jg-user-name" style="font-size:14px;font-weight:600;color:#1e293b;padding:8px 0;"></div>
        </div>
        <div class="es-field" style="margin-bottom:14px;">
            <label class="es-label">Select Group</label>
            <select id="es-jg-group" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                <option value="">— Choose a group —</option>
                <?php foreach ( (array) $all_groups as $jg ) : ?>
                    <option value="<?php echo (int) $jg->id; ?>"><?php echo esc_html( $jg->group_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="es-jg-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button type="button" class="es-btn es-btn-ghost es-schedule-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-jg-submit">👥 Add to Group</button>
        </div>
    </div>
</div>

<!-- v4.5: Edit Session Modal -->
<div id="es-edit-session-modal" class="es-schedule-modal" aria-hidden="true" style="display:none;">
    <div class="es-schedule-modal-overlay"></div>
    <div class="es-schedule-modal-card" style="max-width:560px;">
        <button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
        <div class="es-section-label">Edit Session</div>
        <input type="hidden" id="es-es-slot-id" />
        <div class="es-modal-row">
            <div class="es-field"><label class="es-label">Date</label><input type="date" id="es-es-date" /></div>
            <div class="es-field"><label class="es-label">Start Time</label><input type="time" id="es-es-time" /></div>
        </div>
        <div class="es-modal-row">
            <div class="es-field"><label class="es-label">Duration (min)</label><input type="number" id="es-es-duration" min="5" step="5" /></div>
            <div class="es-field">
                <label class="es-label">Platform</label>
                <select id="es-es-platform">
                    <?php if ( $detail_mode && $selected ) : foreach ( $platforms as $p ) : ?><option value="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></option><?php endforeach; endif; ?>
                </select>
            </div>
        </div>
        <div class="es-field"><label class="es-label">Session Title</label><input type="text" id="es-es-title" placeholder="e.g. IELTS Speaking Practice" /></div>
        <div class="es-field"><label class="es-label">Notes</label><textarea id="es-es-notes" rows="2" placeholder="Agenda / notes"></textarea></div>
        <div id="es-es-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button type="button" class="es-btn es-btn-ghost es-schedule-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-es-submit">Save Changes</button>
        </div>
    </div>
</div>

<style>
.es-pkgacc{display:flex;flex-direction:column;gap:8px}
.es-pkgacc-row{border:1px solid #e5e7eb;border-radius:12px;background:#fff;overflow:hidden;transition:border-color .15s}
.es-pkgacc-row.is-current{border-color:#c4b5fd;box-shadow:0 0 0 3px rgba(196,181,253,.12)}
.es-pkgacc-head{width:100%;display:flex;align-items:center;gap:12px;padding:12px 14px;background:#f8fafc;border:0;cursor:pointer;text-align:left;font:inherit;flex-wrap:wrap}
.es-pkgacc-head:hover{background:#f1f5f9}
.es-pkgacc-caret{display:inline-block;width:14px;color:#6d28d9;font-size:18px;line-height:1;transition:transform .18s ease}
.es-pkgacc-row.is-open .es-pkgacc-caret{transform:rotate(90deg)}
.es-pkgacc-name{flex:1;min-width:140px;font-size:14px;font-weight:600;color:#1e293b}
.es-pkgacc-sub{font-size:12px;color:#64748b}
.es-pkgacc-body{padding:12px 16px 16px;border-top:1px solid #eef0f3;background:#fff}
.es-package-detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:8px}
.es-package-detail-grid div{background:#f8fafc;border:1px solid #eef0f3;border-radius:10px;padding:10px 12px}
.es-package-detail-grid span{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:4px}
.es-package-detail-grid strong{font-size:14px;color:#1e293b}
.es-package-desc{margin-top:12px;padding-top:12px;border-top:1px solid #eef0f3;font-size:13px;line-height:1.6;color:#475569}
.es-aftercall-linkbox{background:#faf5ff;border:1px solid #d8b4fe;border-radius:12px;padding:16px}
.es-aftercall-linklabel{font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#a16207;margin-bottom:8px}
.es-aftercall-linklabel span{font-weight:600;color:#64748b;text-transform:none;letter-spacing:0}
.es-aftercall-linkrow{display:flex;gap:10px;align-items:center}
.es-aftercall-linkrow input{flex:1;min-width:0;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;background:#fff;font-size:12px}
.es-schedule-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:100000;padding:24px}
.es-schedule-modal.is-open{display:flex}
.es-schedule-modal-overlay{position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(2px)}
.es-schedule-modal-card{position:relative;z-index:1;width:min(860px,calc(100vw - 48px));max-height:calc(100vh - 64px);overflow:auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 24px 80px rgba(15,23,42,.28)}
.es-schedule-modal-close{position:absolute;top:12px;right:12px;width:34px;height:34px;border:1px solid #e5e7eb;border-radius:999px;background:#fff;color:#64748b;font-size:22px;line-height:1;cursor:pointer}
.es-schedule-holder > div:not(.es-schedule-modal){display:none}
body.es-modal-open{overflow:hidden}
.es-ss-payment-preview .es-ss-pay-card{background:#fff;border:1px solid #ddd6fe;border-radius:12px;padding:12px;box-shadow:0 8px 24px rgba(109,40,217,.08)}
.es-ss-pay-top{display:flex;align-items:center;justify-content:space-between;gap:10px;color:#312e81;font-size:13px}
.es-ss-pay-top span{background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;border-radius:999px;padding:3px 9px;font-size:12px;font-weight:700;white-space:nowrap}
.es-ss-pay-course{margin-top:4px;color:#64748b;font-size:12px;line-height:1.4}
.es-ss-pay-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:10px}
.es-ss-pay-grid div{background:#f8fafc;border:1px solid #eef0f3;border-radius:8px;padding:8px;text-align:center}
.es-ss-pay-grid b{display:block;color:#111827;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.es-ss-pay-grid small{display:block;color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.04em;margin-top:2px}
.es-ss-pay-bar{height:5px;background:#ede9fe;border-radius:999px;overflow:hidden;margin-top:10px}
.es-ss-pay-bar span{display:block;height:100%;background:#6d28d9;border-radius:999px}
.es-slot-file-item,.es-file-chip{max-width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:5px 8px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.es-slot-files-wrap{display:flex!important;flex-wrap:wrap!important;flex-direction:row!important;gap:6px!important;max-width:360px}
.es-table td{vertical-align:middle}
@media(max-width:700px){.es-ss-pay-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

/* Upload drop zone */
.es-upload-drop-zone{display:block;position:relative;border:2px dashed #c4b5fd;border-radius:12px;background:#faf5ff;padding:32px 20px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s}
.es-upload-drop-zone:hover,.es-upload-drop-zone.is-drag-over{border-color:#7c3aed;background:#f3e8ff}
.es-upload-drop-inner{pointer-events:none}
.es-upload-drop-icon{margin-bottom:10px}
.es-upload-drop-text{font-size:14px;color:#374151;margin-bottom:4px}
.es-upload-browse-link{color:#7c3aed;font-weight:600;text-decoration:underline}
.es-upload-drop-hint{font-size:11px;color:#9ca3af;margin-top:2px}
.es-upload-preview{display:flex;flex-wrap:wrap;gap:6px}
.es-upload-preview-item{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:5px 10px;font-size:12px;max-width:220px}
.es-upload-preview-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1}

/* Schedule package accordion */
.es-schedpkg-accordion{display:flex;flex-direction:column;gap:0}

</style>

<script>
jQuery(function($){
    $('#es-1to1-search').on('input', function(){
        var q = $(this).val().toLowerCase();
        $('#es-1to1-list .es-tabui-item').each(function(){
            $(this).toggle( $(this).data('name').indexOf(q) !== -1 );
        });
    });

    /* ── Tab switching ── */
    $(document).on('click', '.es-tab', function(){
        var tab = $(this).data('tab');
        $(this).closest('.es-tabbar').find('.es-tab').removeClass('is-active');
        $(this).addClass('is-active');
        $(this).closest('.es-tab-body, .es-tabui-detail').find('.es-tabpane').hide();
        $(this).closest('.es-tab-body, .es-tabui-detail').find('[data-pane="' + tab + '"]').show();
    });
    // Programmatic tab jump (e.g. "Go to Renew tab" link)
    $(document).on('click', '.es-tab-link[data-goto]', function(e){
        e.preventDefault();
        var tab = $(this).data('goto');
        $('.es-tab[data-tab="' + tab + '"]').trigger('click');
    });

    $(document).on('click', '.es-copy-renew-link', function(){
        var target = $(this).data('target');
        var $inp = $(target);
        if (!$inp.length || !$inp.val()) return;
        $inp[0].select();
        $inp[0].setSelectionRange(0, 99999);
        document.execCommand('copy');
        var $btn = $(this), old = $btn.text();
        $btn.text('Copied');
        setTimeout(function(){ $btn.text(old); }, 1200);
    });

    /* ── Schedule modal open/close ── */
    $(document).on('click', '.es-open-schedule-modal', function(){
        $('#es-schedule-modal').css('display','').addClass('is-open').attr('aria-hidden','false');
        $('body').addClass('es-modal-open');
        setTimeout(function(){ $('#es-ss-date').trigger('focus'); }, 80);
    });
    $(document).on('click', '.es-schedule-modal-close, .es-schedule-modal-overlay', function(){
        $(this).closest('.es-schedule-modal').removeClass('is-open').attr('aria-hidden','true').css('display','none');
        $('body').removeClass('es-modal-open');
    });

    /* ── All-packages accordion ── */
    $(document).on('click', '.es-pkgacc-head', function(){
        var $row  = $(this).closest('.es-pkgacc-row');
        var $body = $row.find('.es-pkgacc-body');
        var open  = $row.hasClass('is-open') || $body.is(':visible');
        if (open) { $body.slideUp(150); $row.removeClass('is-open'); $(this).attr('aria-expanded', 'false'); }
        else      { $body.slideDown(150); $row.addClass('is-open'); $(this).attr('aria-expanded', 'true'); }
    });
    $('.es-pkgacc-head[aria-expanded="true"]').closest('.es-pkgacc-row').addClass('is-open');

    /* ── Schedule modal: preview queued files ── */
    $(document).on('change', '#es-ss-files', function(){
        var files = this.files;
        var $list = $('#es-ss-files-list').empty();
        if (!files || !files.length) return;
        var names = [];
        for (var i = 0; i < files.length; i++) names.push(files[i].name + ' (' + Math.round(files[i].size / 1024) + ' KB)');
        $list.text(files.length + ' file' + (files.length === 1 ? '' : 's') + ' queued: ' + names.join(', '));
    });

    /* ── Delete a file (slot or global) ── */
    $(document).on('click', '.es-delete-file-btn', function(){
        var fileId = $(this).data('file-id');
        var $item  = $(this).closest('.es-slot-file-item, .es-file-chip');
        if (!confirm('Delete this file?')) return;
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_delete_session_file',
            nonce: ES_ADMIN.nonce,
            id: fileId
        }).done(function(res){
            if (res && res.success) {
                $item.remove();
                if (typeof toast === 'function') toast('File deleted');
            } else {
                alert((res && res.data && res.data.message) || 'Could not delete file.');
            }
        });
    });

    /* ── Add files to a slot ── */
    $(document).on('click', '.es-slot-add-files', function(){
        var slotId = $(this).data('slot-id');
        $('.es-slot-file-input[data-slot-id="' + slotId + '"]').trigger('click');
    });
    $(document).on('change', '.es-slot-file-input', function(){
        var $inp    = $(this);
        var slotId  = $inp.data('slot-id');
        var type    = $inp.data('target-type');
        var tid     = $inp.data('target-id');
        var $wrap   = $('.es-slot-files-wrap[data-slot-id="' + slotId + '"]');
        var files   = this.files;
        if (!files || !files.length) return;
        var uploaded = 0;
        function doNext(i) {
            if (i >= files.length) { $inp.val(''); return; }
            var fd = new FormData();
            fd.append('action', 'es_admin_upload_session_file');
            fd.append('nonce', ES_ADMIN.nonce);
            fd.append('target_type', type);
            fd.append('target_id', tid);
            fd.append('slot_id', slotId);
            fd.append('file', files[i]);
            $.ajax({ url: ES_ADMIN.ajax_url, type: 'POST', data: fd, processData: false, contentType: false }).done(function(res){
                if (res && res.success && res.data.file) {
                    var f = res.data.file;
                    var html = '<div class="es-slot-file-item" data-file-id="' + f.id + '" style="display:inline-flex;align-items:center;gap:6px;">' +
                        '<a href="' + f.file_url + '" target="_blank" rel="noopener" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;">' +
                        '<span class="es-pill es-pill-info" style="font-size:10px;padding:2px 6px;">' + f.file_type.toUpperCase() + '</span>' +
                        '<span style="text-overflow:ellipsis;overflow:hidden;white-space:nowrap;max-width:140px;display:inline-block;">' + f.file_name + '</span></a>' +
                        '<button type="button" class="es-delete-file-btn" data-file-id="' + f.id + '" title="Delete" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:0 2px;">×</button>' +
                        '</div>';
                    $wrap.append(html);
                }
                doNext(i + 1);
            }).fail(function(){ doNext(i + 1); });
        }
        doNext(0);
    });

    /* ── Global / package upload modal ── */
    $(document).on('click', '.es-open-pkg-upload', function(){
        var pkgId   = $(this).data('pkg-id') || 0;
        var pkgName = $(this).data('pkg-name') || '';
        $('#es-gu-file').val('');
        $('#es-gu-msg').hide();
        $('#es-gu-preview').empty().hide();
        $('#es-gu-package-id').val(pkgId);
        if (pkgId && pkgName) {
            $('#es-gu-pkg-label').text('📦 ' + pkgName).show();
        } else {
            $('#es-gu-pkg-label').hide();
        }
        var $modal = $('#es-global-upload-modal');
        $modal.addClass('is-open').attr('aria-hidden','false').css('display','flex');
        $('body').addClass('es-modal-open');
    });
    // Keep legacy .es-open-global-upload support (no package)
    $(document).on('click', '.es-open-global-upload', function(){
        $('#es-gu-file').val('');
        $('#es-gu-msg').hide();
        $('#es-gu-preview').empty().hide();
        $('#es-gu-package-id').val('');
        $('#es-gu-pkg-label').hide();
        var $modal = $('#es-global-upload-modal');
        $modal.addClass('is-open').attr('aria-hidden','false').css('display','flex');
        $('body').addClass('es-modal-open');
    });
    $(document).on('click', '#es-gu-submit', function(){
        var files = document.getElementById('es-gu-file').files;
        if (!files || !files.length) { $('#es-gu-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Please choose at least one file.'); return; }
        var type = $('#es-gu-target-type').val();
        var tid  = $('#es-gu-target-id').val();
        var $btn = $(this).prop('disabled',true).text('Uploading…');
        var uploaded = 0, total = files.length;
        function doNext(i) {
            if (i >= total) {
                $btn.prop('disabled',false).text('Upload');
                $('#es-gu-msg').css({display:'block',background:'#d1fae5',color:'#065f46'}).text(uploaded + ' file(s) uploaded.');
                // Reload page after short delay to refresh global files list
                setTimeout(function(){ window.location.reload(); }, 1200);
                return;
            }
            var fd = new FormData();
            fd.append('action', 'es_admin_global_upload');
            fd.append('nonce', ES_ADMIN.nonce);
            fd.append('target_type', type);
            fd.append('target_id', tid);
            fd.append('package_id', $('#es-gu-package-id').val() || 0);
            fd.append('file', files[i]);
            $.ajax({ url: ES_ADMIN.ajax_url, type:'POST', data: fd, processData: false, contentType: false }).done(function(res){
                if (res && res.success) uploaded++;
                else $('#es-gu-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Upload failed for ' + files[i].name);
                doNext(i + 1);
            }).fail(function(){ doNext(i + 1); });
        }
        doNext(0);
    });

    /* ── Edit session modal ── */
    $(document).on('click', '.es-edit-session-btn', function(){
        var $btn = $(this);
        $('#es-es-slot-id').val( $btn.data('slot-id') );
        $('#es-es-date').val( $btn.data('slot-date') );
        $('#es-es-time').val( $btn.data('start-time') );
        $('#es-es-duration').val( $btn.data('duration') );
        $('#es-es-title').val( $btn.data('title') );
        $('#es-es-notes').val( $btn.data('notes') );
        var plat = $btn.data('platform');
        $('#es-es-platform option').each(function(){ $(this).prop('selected', $(this).val() === plat); });
        $('#es-es-msg').hide();
        $('#es-edit-session-modal').addClass('is-open').attr('aria-hidden','false').css('display','flex');
        $('body').addClass('es-modal-open');
    });
    $(document).on('click', '#es-es-submit', function(){
        var $btn = $(this).prop('disabled',true).text('Saving…');
        var slotId = $('#es-es-slot-id').val();
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_edit_schedule_session',
            nonce: ES_ADMIN.nonce,
            slot_id: slotId,
            slot_date:   $('#es-es-date').val(),
            start_time:  $('#es-es-time').val(),
            duration_min: $('#es-es-duration').val(),
            platform:    $('#es-es-platform').val(),
            title:       $('#es-es-title').val(),
            notes:       $('#es-es-notes').val()
        }).done(function(res){
            $btn.prop('disabled',false).text('Save Changes');
            if (res && res.success) {
                $('#es-edit-session-modal').removeClass('is-open').hide();
                $('body').removeClass('es-modal-open');
                if (typeof toast === 'function') toast('Session updated');
                setTimeout(function(){ window.location.reload(); }, 600);
            } else {
                $('#es-es-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not save changes.');
            }
        }).fail(function(){
            $btn.prop('disabled',false).text('Save Changes');
            $('#es-es-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Server error.');
        });
    });

    /* ── Delete session ── */
    $(document).on('click', '.es-delete-session-btn', function(){
        var slotId = $(this).data('slot-id');
        var title  = $(this).data('title') || 'this session';
        if (!confirm('Delete "' + title + '"? All bookings will be removed and sessions refunded.')) return;
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_delete_schedule_session',
            nonce: ES_ADMIN.nonce,
            slot_id: slotId
        }).done(function(res){
            if (res && res.success) {
                if (typeof toast === 'function') toast('Session deleted');
                setTimeout(function(){ window.location.reload(); }, 600);
            } else {
                alert((res && res.data && res.data.message) || 'Could not delete session.');
            }
        });
    });

    function esRefreshSchedulePaymentPreview(){
        var $sel = $('#es-ss-payment');
        var $box = $('#es-ss-payment-preview');
        if (!$sel.length || !$box.length) return;
        var $opt = $sel.find(':selected');
        if (!$opt.length || !$opt.val()) { $box.hide().empty(); return; }
        var pkg    = $opt.data('package-name') || $.trim($opt.text());
        var course = $opt.data('course-name') || '—';
        var total  = parseInt($opt.data('total'), 10) || 0;
        var used   = parseInt($opt.data('used'), 10) || 0;
        var left   = parseInt($opt.data('left'), 10) || 0;
        var until  = $opt.data('valid-until') || '—';
        var pct    = total > 0 ? Math.min(100, Math.round((used / total) * 100)) : 0;
        var html = '' +
            '<div class="es-ss-pay-card">' +
                '<div class="es-ss-pay-top"><strong>' + $('<div>').text(pkg).html() + '</strong><span>' + left + ' left</span></div>' +
                '<div class="es-ss-pay-course">Course: ' + $('<div>').text(course).html() + '</div>' +
                '<div class="es-ss-pay-grid"><div><b>' + total + '</b><small>Total</small></div><div><b>' + used + '</b><small>Used</small></div><div><b>' + left + '</b><small>Left</small></div><div><b>' + $('<div>').text(until).html() + '</b><small>Valid Until</small></div></div>' +
                '<div class="es-ss-pay-bar"><span style="width:' + pct + '%"></span></div>' +
            '</div>';
        $box.html(html).show();
    }
    $(document).on('change', '#es-ss-payment', esRefreshSchedulePaymentPreview);
    $(document).on('click', '.es-open-schedule-modal, #es-sched-new-btn', function(){ setTimeout(esRefreshSchedulePaymentPreview, 80); });
    esRefreshSchedulePaymentPreview();

    /* ── Renew Package ── */
    $('#es-renew-package').on('change', function(){
        var $opt = $(this).find(':selected');
        $('#es-renew-link').val('');
        if (!$opt.val()) { $('#es-renew-pkg-preview').hide(); return; }
        var sessions = $opt.data('sessions');
        var months   = $opt.data('months');
        var price    = parseFloat($opt.data('price') || 0);
        var validFrom = new Date();
        var validUntil = new Date( validFrom );
        validUntil.setMonth( validUntil.getMonth() + parseInt(months, 10) );
        $('#es-renew-pkg-name').text( $opt.text().split('(')[0].trim() );
        $('#es-renew-pkg-meta').text( sessions + ' sessions · ' + months + ' month' + (months > 1 ? 's' : '') + (price > 0 ? ' · ₹' + price.toLocaleString('en-IN') : '') );
        $('#es-renew-valid-note').text( 'Valid: ' + validFrom.toLocaleDateString('en-IN', {day:'numeric',month:'short',year:'numeric'}) + ' → ' + validUntil.toLocaleDateString('en-IN', {day:'numeric',month:'short',year:'numeric'}) );
        $('#es-renew-pkg-preview').show();
    });

    if ($.fn.select2 && $('#es-renew-course').length) {
        $('#es-renew-course').select2({ placeholder: 'Search course…', width: '100%', allowClear: true });
    }

    /* ── Purchase Package tab: filter package dropdown by type ── */
    $(document).on('change', '#es-renew-type', function(){
        var selectedType = $(this).val();
        $('#es-renew-package option').each(function(){
            var $opt = $(this);
            if (!$opt.val()) { $opt.show(); return; } // keep placeholder
            if (!selectedType || $opt.data('type') === selectedType) {
                $opt.show();
            } else {
                $opt.hide();
            }
        });
        // Reset selection when filter changes
        $('#es-renew-package').val('').trigger('change');
    });

    $(document).on('click', '#es-renew-submit', function(){
        var pkgId = $('#es-renew-package').val();
        if (!pkgId) { $('#es-renew-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Please select a package.'); return; }
        var $btn = $(this).prop('disabled',true).text('Renewing…');
        var renewCourseId = $('#es-renew-course').val();
        var courseIds = renewCourseId ? [renewCourseId] : [];
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_renew_package',
            nonce: ES_ADMIN.nonce,
            user_id: $(this).data('user-id'),
            package_id: pkgId,
            comments: $('#es-renew-comments').val(),
            course_ids: courseIds,
            send_email: $('#es-renew-email').is(':checked') ? 1 : 0
        }).done(function(res){
            $btn.prop('disabled',false).text('Renew Package');
            if (res && res.success) {
                var msg = (res.data && res.data.message) ? res.data.message : 'Renewal link created.';
                if (res.data && res.data.share_link) {
                    $('#es-renew-link').val(res.data.share_link);
                }
                $('#es-renew-msg').css({display:'block',background:'#d1fae5',color:'#065f46'}).text(msg);
            } else {
                $('#es-renew-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not renew package.');
            }
        }).fail(function(){
            $btn.prop('disabled',false).text('Renew Package');
            $('#es-renew-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Server error.');
        });
    });

    $(document).on('click', '.es-attpkg-head', function(){
        $(this).closest('.es-attpkg').toggleClass('is-open');
    });

    /* ── Schedule package accordion ── */
    $(document).on('click', '.es-schedpkg-head', function(){
        $(this).closest('.es-schedpkg').toggleClass('is-open');
    });

    /* ── Upload drop zone: drag-over highlight + file preview ── */
    var $dropZone = $('#es-gu-drop-zone');
    $dropZone.on('dragenter dragover', function(e){ e.preventDefault(); $(this).addClass('is-drag-over'); });
    $dropZone.on('dragleave drop', function(e){ e.preventDefault(); $(this).removeClass('is-drag-over'); });
    $(document).on('change', '#es-gu-file', function(){
        var files = this.files;
        var $prev = $('#es-gu-preview').empty();
        if (!files || !files.length) { $prev.hide(); return; }
        for (var i = 0; i < Math.min(files.length, 8); i++) {
            var f = files[i];
            var icon = f.type.startsWith('image/') ? '🖼' : (f.type.startsWith('video/') ? '🎬' : '📄');
            $prev.append('<div class="es-upload-preview-item"><span>' + icon + '</span><span title="' + $('<div>').text(f.name).html() + '">' + $('<div>').text(f.name).html() + '</span><span style="color:#9ca3af;white-space:nowrap;">' + (f.size > 1048576 ? (f.size/1048576).toFixed(1) + ' MB' : Math.round(f.size/1024) + ' KB') + '</span></div>');
        }
        if (files.length > 8) $prev.append('<div style="font-size:12px;color:#6b7280;padding:4px 6px;">+' + (files.length - 8) + ' more</div>');
        $prev.show();
    });

    /* ── Join Group modal ── */
    $(document).on('click', '.es-open-join-group-modal', function(){
        var uid   = $(this).data('user-id');
        var uname = $(this).data('user-name');
        $('#es-jg-user-id').val(uid);
        $('#es-jg-user-name').text(uname);
        $('#es-jg-group').val('');
        $('#es-jg-msg').hide();
        $('#es-join-group-modal').addClass('is-open').attr('aria-hidden','false').css('display','flex');
        $('body').addClass('es-modal-open');
    });
    $(document).on('click', '#es-jg-submit', function(){
        var gid = $('#es-jg-group').val();
        var uid = $('#es-jg-user-id').val();
        if (!gid) { $('#es-jg-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Please select a group.'); return; }
        var $btn = $(this).prop('disabled', true).text('Adding…');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_add_group_member',
            nonce: ES_ADMIN.nonce,
            group_id: gid,
            user_id: uid
        }).done(function(res){
            $btn.prop('disabled', false).text('👥 Add to Group');
            if (res && res.success) {
                $('#es-jg-msg').css({display:'block',background:'#d1fae5',color:'#065f46'}).text((res.data && res.data.message) || 'Added to group successfully. 1:1 data unchanged.');
                $('#es-jg-group').val('');
            } else {
                $('#es-jg-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not add to group.');
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('👥 Add to Group');
            $('#es-jg-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Server error.');
        });
    });

    $(document).on('change', '#es-att-package-filter', function(){
        var pid = $(this).val();
        $('.es-attpkg').each(function(){
            var boxPid = String($(this).data('package-id') || '');
            $(this).toggle(!pid || boxPid === String(pid));
        });
        $('.es-att-row').each(function(){
            var rowPid = String($(this).data('package-id') || '');
            $(this).toggle(!pid || rowPid === String(pid));
        });
    });

    /* ── Course tab: Select2 multi-select ── */
    if ($.fn.select2 && $('#es-course-select').length) {
        $('#es-course-select').select2({ placeholder: 'Search courses…', width: '100%', closeOnSelect: false });
    }
    $(document).on('click', '.es-courses-save', function(){
        var ids = $('#es-course-select').val() || [];
        var $msg = $('#es-course-msg').hide();
        $.post(ajaxurl, {
            action: 'es_admin_save_student_courses',
            nonce: (window.ES_ADMIN && ES_ADMIN.nonce) ? ES_ADMIN.nonce : '',
            user_id: <?php echo $selected ? (int) $selected->ID : 0; ?>,
            course_ids: ids
        }).done(function(res){
            $msg.css('color', res && res.success ? '#10b981' : '#ef4444')
                .text(res && res.success ? '✓ Courses saved' : ((res.data && res.data.message) || 'Could not save courses'))
                .show();
        });
    });
});

</script>
