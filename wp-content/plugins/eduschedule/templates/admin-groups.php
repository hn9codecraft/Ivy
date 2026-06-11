<?php
/**
 * Groups admin page
 * URL: admin.php?page=eduschedule-groups&group_id=X
 * v4.5 — Package section (same as 1:1), Schedule edit/delete, Global upload, Renew tab
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$base         = admin_url( 'admin.php?page=eduschedule-groups' );
$all_packages     = ES_Packages::get_all( false );
$all_course_posts = ES_Packages::get_course_posts();
$detail_mode      = ! empty( $selected );
?>
<div class="es-admin es-groups-page">

    <div class="es-page-head">
        <div>
            <h1><?php echo $detail_mode ? esc_html( $selected->group_name . ' Details' ) : 'Groups'; ?></h1>
            <p class="es-page-sub"><?php echo $detail_mode ? 'Group student details, package, attendance and bookings.' : 'Manage student groups &mdash; ' . count( $groups ) . ' total'; ?></p>
        </div>
        <div class="es-page-actions">
            <?php if ( $detail_mode ) : ?>
                <a class="es-btn es-btn-ghost" href="<?php echo esc_url( $base ); ?>">&larr; Back to Groups</a>
            <?php else : ?>
                <button type="button" class="es-btn es-btn-primary" id="es-add-group-btn"><span class="dashicons dashicons-plus"></span> New Group</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $detail_mode ) : ?>
        <style>
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
.es-slot-file-item,.es-file-chip{max-width:100%;background:#fff;border:1px solid #e2e8f0!important;border-radius:999px!important;padding:5px 8px!important;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.es-slot-files-wrap{display:flex!important;flex-wrap:wrap!important;flex-direction:row!important;gap:6px!important;max-width:420px}
.es-table td{vertical-align:middle}
@media(max-width:700px){.es-ss-pay-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.es-groups-page .es-tabui-shell{display:block}.es-groups-page .es-tabui-shell>.es-card{display:none}.es-groups-page .es-tabui-shell>div{width:100%}.es-groups-page .es-tabui-detail{max-width:none}
    /* Group create/edit + add-member modal polish */
    .es-groups-page .es-modal{position:fixed;inset:0;z-index:100001;align-items:center;justify-content:center;padding:24px}
    .es-groups-page .es-modal[style*="display: block"]{display:flex!important}
    .es-groups-page .es-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.52);backdrop-filter:blur(3px)}
    .es-groups-page .es-modal-card{position:relative;z-index:1;width:min(560px,calc(100vw - 48px));background:#fff;border-radius:18px;box-shadow:0 24px 80px rgba(15,23,42,.25);overflow:hidden;border:1px solid #eef0f3}
    .es-groups-page .es-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:22px 26px;border-bottom:1px solid #f1f5f9;background:linear-gradient(180deg,#fff,#fbfbff)}
    .es-groups-page .es-modal-head h2{margin:0;font-size:22px;line-height:1.2;color:#1f2937;letter-spacing:.01em}
    .es-groups-page .es-modal-close{width:38px;height:38px;border:1px solid #e5e7eb;background:#fff;border-radius:12px;color:#64748b;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center}
    .es-groups-page .es-modal-close:hover{border-color:#c4b5fd;color:#6d28d9;background:#faf5ff}
    .es-groups-page .es-modal-body{padding:24px 26px;display:grid;gap:18px}
    .es-groups-page .es-modal-foot{display:flex;justify-content:flex-end;gap:10px;padding:18px 26px;background:#fafafa;border-top:1px solid #f1f5f9}
    .es-groups-page .es-modal-card .es-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700;margin-bottom:8px;display:block}
    .es-groups-page .es-modal-card input[type="text"],
    .es-groups-page .es-modal-card textarea,
    .es-groups-page .es-modal-card select{width:100%;border:1px solid #dbe2ea;border-radius:12px;padding:12px 14px;background:#fff;font-size:14px;line-height:1.45;box-shadow:0 1px 0 rgba(15,23,42,.02)}
    .es-groups-page .es-modal-card textarea{min-height:96px;resize:vertical}
    .es-groups-page .es-modal-card input:focus,
    .es-groups-page .es-modal-card textarea:focus,
    .es-groups-page .es-modal-card select:focus{outline:none;border-color:#8b5cf6;box-shadow:0 0 0 4px rgba(139,92,246,.10)}
    .es-groups-page .es-helper{font-size:12.5px;line-height:1.5;color:#64748b}

    </style>
    <?php endif; ?>

    <?php if ( ! $detail_mode ) : ?>
        <!-- ===== LIST VIEW ===== -->
        <div class="es-card es-card-flush">
            <table class="es-table">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Members</th>
                        <th>Package</th>
                        <th>Course</th>
                        <th>Sessions Left</th>
                        <th>Duration</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $groups ) ) : ?>
                        <tr><td colspan="7" class="es-empty-cell">No groups yet. Click "New Group" to create one.</td></tr>
                    <?php else : foreach ( $groups as $g ) :
                        $color   = $g->color ?: '#6366f1';
                        $count   = ES_Packages::count_group_members( $g->id );
                        $g_pkg   = $g->package_id ? ES_Packages::get( $g->package_id ) : null;
                        $g_course_list = ! empty( $g->course_name ) ? $g->course_name : ES_Packages::course_names_str( ES_Packages::get_group_course_ids( $g->id ) );
                        $gl_total = (int) ( $g->total_sessions ?? 0 );
                        if ( $gl_total <= 0 && $g_pkg ) $gl_total = (int) ( $g_pkg->total_sessions ?? 0 );
                        $gl_used  = (int) ( $g->used_sessions ?? 0 );
                        $gl_left  = max( 0, $gl_total - $gl_used );
                    ?>
                        <tr>
                            <td>
                                <div class="es-cell-user">
                                    <div class="es-tabui-avatar" style="background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;">👥</div>
                                    <div>
                                        <div class="es-cell-user-name"><?php echo esc_html( $g->group_name ); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="es-badge"><?php echo (int) $count; ?></span></td>
                            <td><?php echo ! empty( $g->package_name ) ? esc_html( $g->package_name ) : ( $g_pkg ? esc_html( $g_pkg->package_name ) : '—' ); ?></td>
                            <td><?php echo $g_course_list !== '' ? esc_html( $g_course_list ) : '—'; ?></td>
                            <td>
                                <?php if ( $gl_total > 0 ) : ?>
                                    <span class="es-badge"><?php echo (int) $gl_left; ?></span>
                                <?php else : ?>
                                    <span class="es-cell-sub">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="es-cell-sub"><?php echo $g->duration ? esc_html( $g->duration ) : '—'; ?></td>
                            <td>
                                <a href="<?php echo esc_url( add_query_arg( 'group_id', (int) $g->id, $base ) ); ?>" class="es-btn es-btn-primary es-btn-sm">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>

    <div class="es-tabui-shell">

        <!-- LEFT: Group list -->
        <div class="es-card" style="padding:12px;">
            <div class="es-tabui-listlabel">Groups</div>
            <input type="text" id="es-group-search" class="es-tabui-search" placeholder="Search groups..." />

            <div id="es-group-list" class="es-tabui-list">
                <?php if ( empty( $groups ) ) : ?>
                    <p class="es-empty-cell" style="font-size:12px;">No groups yet. Click "New Group" to create one.</p>
                <?php else : foreach ( $groups as $g ) :
                    $initial = strtoupper( substr( $g->group_name, 0, 2 ) );
                    $active  = ( (int) $g->id === (int) $gid );
                    $color   = $g->color ?: '#6366f1';
                    $count   = ES_Packages::count_group_members( $g->id );
                ?>
                    <a href="<?php echo esc_url( add_query_arg( 'group_id', $g->id, $base ) ); ?>"
                       class="es-tabui-item <?php echo $active ? 'is-active' : ''; ?>"
                       data-name="<?php echo esc_attr( strtolower( $g->group_name ) ); ?>">
                        <div class="es-tabui-avatar" style="background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;">👥</div>
                        <div style="flex:1;min-width:0;">
                            <div class="es-tabui-item-name"><?php echo esc_html( $g->group_name ); ?></div>
                            <div class="es-tabui-item-sub"><?php echo (int) $count; ?> member<?php echo $count === 1 ? '' : 's'; ?><?php if ( $g->duration ) : ?> · <?php echo esc_html( $g->duration ); ?><?php endif; ?></div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- RIGHT: Tabbed Detail -->
        <div>
            <?php if ( ! $selected ) : ?>
                <div class="es-card" style="padding:60px 20px;text-align:center;color:var(--es-text-muted);">
                    <div style="font-size:48px;margin-bottom:12px;">👥</div>
                    <div style="font-size:14px;">Select a group from the list, or create a new one</div>
                </div>
            <?php else :
                $members   = ES_Packages::get_group_members( $selected->id );
                $assignable_users = ES_Packages::get_group_assignable_users( $selected->id );
                $pkg       = $selected->package_id ? ES_Packages::get( $selected->package_id ) : null;
                $color     = $selected->color ?: '#6366f1';
                $platforms = ES_Helpers::platforms();
                $files     = ES_Packages::get_session_files( 'group', $selected->id );
                $files_by_slot = ES_Packages::get_session_files_by_slot( 'group', $selected->id );
                $global_files  = array_values( array_filter( $files, function($f){ return empty($f->slot_id); } ) );
                $videos    = ES_Packages::get_videos( 'group', $selected->id );
                $g_schedule = ES_Packages::get_group_schedule( $selected->id, 100 );
                $g_att_map  = ES_Packages::get_group_attendance_map( $selected->id );
                $g_payments = ES_Packages::get_group_payments( $selected->id, true );
                $g_schedulable = array();
                $g_now_ts = current_time( 'timestamp' );
                $g_current_payment = null;
                $g_total = 0;
                $g_used  = 0;
                foreach ( (array) $g_payments as $gp ) {
                    $gp_total = (int) ( $gp->total_sessions ?? 0 );
                    $gp_used  = (int) ( $gp->used_sessions ?? 0 );
                    $gp_left  = max( 0, $gp_total - $gp_used );
                    $gp_valid = empty( $gp->valid_until ) || strtotime( $gp->valid_until ) >= $g_now_ts;
                    $g_total += $gp_total;
                    $g_used  += min( $gp_used, $gp_total > 0 ? $gp_total : $gp_used );
                    if ( $gp_left > 0 && $gp_valid ) {
                        $g_schedulable[] = $gp;
                        if ( ! $g_current_payment ) $g_current_payment = $gp;
                    }
                }

                $course_posts         = ES_Packages::get_course_posts();
                $selected_course_ids  = ES_Packages::get_group_course_ids( $selected->id );
                $selected_course_id   = ES_Packages::first_course_id( $selected_course_ids );
                $g_course_names       = $g_current_payment && ! empty( $g_current_payment->course_name ) ? $g_current_payment->course_name : ( ! empty( $selected->course_name ) ? $selected->course_name : ES_Packages::course_names_str( $selected_course_ids ) );
                $group_renew_packages = ES_Packages::get_group_renewable_packages( $selected->id, false );

                if ( $g_total <= 0 ) {
                    $g_total = (int) ( $selected->total_sessions ?? 0 );
                    $g_used  = (int) ( $selected->used_sessions ?? 0 );
                    if ( $g_total <= 0 && $pkg ) $g_total = (int) ( $pkg->total_sessions ?? 0 );
                }
                $g_left  = max( 0, $g_total - $g_used );
                $g_pct   = $g_total > 0 ? round( ( $g_used / $g_total ) * 100 ) : 0;
                $g_dur   = $g_current_payment ? (int) ( $g_current_payment->months ?? 1 ) : ( $pkg ? (int) ( $pkg->months ?? 1 ) : 0 );
                if ( $g_current_payment ) {
                    $pkg = ES_Packages::get( (int) $g_current_payment->package_id );
                }

                $g_schedule_package_options = array();
                foreach ( (array) $g_schedule as $gsopt ) {
                    $opt_pid = ! empty( $gsopt->schedule_package_id ) ? (int) $gsopt->schedule_package_id : ( ! empty( $gsopt->slot_package_id ) ? (int) $gsopt->slot_package_id : 0 );
                    $opt_key = $opt_pid ? 'pkg_' . $opt_pid : 'no_package';
                    if ( ! isset( $g_schedule_package_options[ $opt_key ] ) ) {
                        $g_schedule_package_options[ $opt_key ] = array(
                            'id'     => $opt_pid,
                            'name'   => ! empty( $gsopt->package_name ) ? $gsopt->package_name : ( $opt_pid ? 'Package #' . $opt_pid : 'No linked package' ),
                            'course' => ! empty( $gsopt->schedule_course_name ) ? $gsopt->schedule_course_name : ( ! empty( $gsopt->slot_course_name ) ? $gsopt->slot_course_name : $g_course_names ),
                            'count'  => 0,
                        );
                    }
                    $g_schedule_package_options[ $opt_key ]['count']++;
                }

                $g_sched_blocked = false;
                $g_sched_reason  = '';
                if ( empty( $members ) ) {
                    $g_sched_blocked = true;
                    $g_sched_reason  = 'No members in this group yet.';
                } elseif ( empty( $g_schedulable ) ) {
                    $g_sched_blocked = true;
                    $g_sched_reason  = 'No active group package with sessions left. Use the Renew tab to send a package-selection/payment link.';
                }
            ?>
                <div class="es-tabui-detail" data-target-type="group" data-target-id="<?php echo (int) $selected->id; ?>">

                    <!-- Header -->
                    <div class="es-tabui-header">
                        <div class="es-tabui-header-avatar" style="background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;font-size:22px;">👥</div>
                        <div class="es-tabui-header-info">
                            <h2><?php echo esc_html( $selected->group_name ); ?></h2>
                            <div class="es-tabui-header-meta">
                                <span><?php echo count( $members ); ?> students</span>
                                <?php if ( $g_current_payment || $pkg ) : ?><span><?php echo esc_html( $g_current_payment && ! empty( $g_current_payment->package_name ) ? $g_current_payment->package_name : ( $pkg ? $pkg->package_name : '' ) ); ?></span><?php endif; ?>
                                <?php if ( ! empty( $g_course_names ) ) : ?><span><?php echo esc_html( $g_course_names ); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <!-- v4.5: Global upload button -->
                        <button type="button" class="es-btn es-btn-ghost es-open-global-upload" title="Upload a file for this group (not tied to a session)">
                            <span class="dashicons dashicons-upload" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;"></span> Upload
                        </button>
                        <?php if ( $g_sched_blocked ) : ?>
                            <button type="button" class="es-btn es-btn-ghost" disabled title="<?php echo esc_attr( $g_sched_reason ); ?>">+ Schedule</button>
                        <?php else : ?>
                            <button type="button" class="es-btn es-btn-ghost es-open-schedule-modal">+ Schedule</button>
                        <?php endif; ?>
                        <button type="button" class="es-btn es-btn-ghost es-edit-group-btn" data-id="<?php echo (int) $selected->id; ?>"><span class="dashicons dashicons-edit"></span> Edit</button>
                        <button type="button" class="es-btn es-btn-danger es-delete-group-btn" data-id="<?php echo (int) $selected->id; ?>"><span class="dashicons dashicons-trash"></span> Delete</button>
                    </div>

                    <!-- Tab bar -->
                    <div class="es-tabbar">
                        <button type="button" class="es-tab is-active" data-tab="pkg">Package</button>
                        <button type="button" class="es-tab" data-tab="att">Attendance</button>
                        <button type="button" class="es-tab" data-tab="schedule">Schedule</button>
                        <button type="button" class="es-tab" data-tab="members">Members</button>
                        <button type="button" class="es-tab" data-tab="renew">Purchase</button>
                    </div>

                    <div class="es-tab-body">

                        <!-- ATTENDANCE (package-wise accordion) -->
                        <div class="es-tabpane" data-pane="att" style="display:none;">
                            <div class="es-section-label">Session Attendance</div>
                            <p class="es-att-legend" style="font-size:12px;opacity:.7;margin:0 0 12px;">
                                Tap a package first, then expand a session to mark each student. &nbsp;·&nbsp;
                                <strong>Present</strong> / <strong>Absent</strong> is saved per student, per session.
                            </p>

                            <?php if ( empty( $members ) ) : ?>
                                <p class="es-empty-cell">No members in this group yet.</p>
                            <?php elseif ( empty( $g_schedule ) ) : ?>
                                <p class="es-empty-cell">No group sessions scheduled yet. Schedule a meeting first, then mark attendance per session.</p>
                            <?php else : ?>
                                <?php
                                $att_json = array();
                                foreach ( $g_att_map as $sid => $byuser ) {
                                    foreach ( $byuser as $uid => $info ) {
                                        $att_json[ (int) $sid ][ (int) $uid ] = array(
                                            'status'  => $info['status'],
                                            'comment' => $info['comment'],
                                        );
                                    }
                                }

                                $g_att_groups = array();
                                foreach ( $g_schedule as $gs_row ) {
                                    $pkg_id = ! empty( $gs_row->schedule_package_id ) ? (int) $gs_row->schedule_package_id : ( ! empty( $gs_row->slot_package_id ) ? (int) $gs_row->slot_package_id : 0 );
                                    $key    = $pkg_id ? 'pkg_' . $pkg_id : 'no_package';
                                    if ( ! isset( $g_att_groups[ $key ] ) ) {
                                        $g_att_groups[ $key ] = array(
                                            'package_id'   => $pkg_id,
                                            'package_name' => ! empty( $gs_row->package_name ) ? $gs_row->package_name : ( $pkg_id ? 'Package #' . $pkg_id : 'No linked package' ),
                                            'course_name'  => ! empty( $gs_row->schedule_course_name ) ? $gs_row->schedule_course_name : ( ! empty( $gs_row->slot_course_name ) ? $gs_row->slot_course_name : $g_course_names ),
                                            'rows'         => array(),
                                        );
                                    }
                                    $g_att_groups[ $key ]['rows'][] = $gs_row;
                                }
                                ?>
                                <div id="es-gatt-data" data-att='<?php echo esc_attr( wp_json_encode( $att_json ) ); ?>'></div>

                                <?php if ( ! empty( $g_att_groups ) ) : ?>
                                    <div class="es-field" style="max-width:380px;margin-bottom:12px;">
                                        <label class="es-label">Filter by Package</label>
                                        <select id="es-gatt-package-filter" style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
                                            <option value="">All packages</option>
                                            <?php foreach ( $g_att_groups as $grp_opt ) : ?>
                                                <option value="<?php echo (int) $grp_opt['package_id']; ?>"><?php echo esc_html( $grp_opt['package_name'] ); ?><?php if ( ! empty( $grp_opt['course_name'] ) ) : ?> · <?php echo esc_html( $grp_opt['course_name'] ); ?><?php endif; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div class="es-gattpkg-accordion">
                                    <?php foreach ( $g_att_groups as $gpi => $grp ) :
                                        $pkg_open = ( $gpi === array_key_first( $g_att_groups ) );
                                    ?>
                                        <div class="es-gatt-pkg <?php echo $pkg_open ? 'is-open' : ''; ?>" data-package-id="<?php echo (int) $grp['package_id']; ?>">
                                            <button type="button" class="es-gatt-pkg-head">
                                                <span class="es-gatt-pkg-caret">▸</span>
                                                <span style="flex:1;min-width:0;">
                                                    <span class="es-gatt-pkg-title"><?php echo esc_html( $grp['package_name'] ); ?></span>
                                                    <span class="es-gatt-pkg-sub"> · <?php echo count( $grp['rows'] ); ?> session<?php echo count( $grp['rows'] ) === 1 ? '' : 's'; ?></span>
                                                    <?php if ( ! empty( $grp['course_name'] ) ) : ?><span class="es-pill es-pill-info" style="font-size:10px;margin-left:8px;">Course: <?php echo esc_html( $grp['course_name'] ); ?></span><?php endif; ?>
                                                </span>
                                            </button>
                                            <div class="es-gatt-pkg-body">
                                                <div class="es-gatt-accordion">
                                                    <?php foreach ( $grp['rows'] as $idx => $gs ) :
                                                        $sid  = (int) $gs->slot_id;
                                                        $gsd  = strtotime( $gs->slot_date );
                                                        $open = ( $pkg_open && $idx === 0 );
                                                        $marked = 0;
                                                        if ( ! empty( $g_att_map[ $sid ] ) ) {
                                                            foreach ( $g_att_map[ $sid ] as $info ) {
                                                                if ( ES_Packages::normalize_att_status( $info['status'] ) !== 'none' ) $marked++;
                                                            }
                                                        }
                                                        $gs_pkg_name = ! empty( $gs->package_name ) ? $gs->package_name : $grp['package_name'];
                                                        $gs_course_name = ! empty( $gs->schedule_course_name ) ? $gs->schedule_course_name : ( ! empty( $gs->slot_course_name ) ? $gs->slot_course_name : $grp['course_name'] );
                                                    ?>
                                                        <div class="es-gatt-sess <?php echo $open ? 'is-open' : ''; ?>" data-slot-id="<?php echo $sid; ?>" data-package-id="<?php echo (int) $grp['package_id']; ?>">
                                                            <button type="button" class="es-gatt-sess-head">
                                                                <span class="es-gatt-sess-caret">▸</span>
                                                                <span class="es-gatt-sess-date">
                                                                    <strong><?php echo esc_html( date_i18n( 'M j, Y', $gsd ) ); ?></strong>
                                                                    <span class="es-gatt-sess-time"><?php echo esc_html( substr( $gs->start_time, 0, 5 ) . ' · ' . ( $gs->title ?: 'Group Session' ) ); ?></span>
                                                                    <?php if ( $gs_pkg_name ) : ?><span class="es-pill es-pill-info" style="font-size:10px;margin-left:8px;">Package: <?php echo esc_html( $gs_pkg_name ); ?></span><?php endif; ?>
                                                                    <?php if ( $gs_course_name ) : ?><span class="es-pill" style="font-size:10px;margin-left:4px;background:#eef2ff;color:#3730a3;">Course: <?php echo esc_html( $gs_course_name ); ?></span><?php endif; ?>
                                                                </span>
                                                                <span class="es-gatt-sess-count"><?php echo (int) $marked; ?>/<?php echo count( $members ); ?> marked</span>
                                                            </button>

                                                            <div class="es-gatt-sess-body">
                                                                <div class="es-gatt-bulk" data-slot-id="<?php echo $sid; ?>">
                                                                    <span class="es-gatt-bulk-label">Mark all:</span>
                                                                    <button type="button" class="es-gatt-bulk-btn es-gatt-bulk-present" data-status="present">✓ Present</button>
                                                                    <button type="button" class="es-gatt-bulk-btn es-gatt-bulk-unexcused" data-status="absent_unexcused">✗ Absent (no permission)</button>
                                                                    <button type="button" class="es-gatt-bulk-btn es-gatt-bulk-excused" data-status="absent_excused">○ Absent (with permission)</button>
                                                                    <button type="button" class="es-gatt-bulk-btn es-gatt-bulk-clear" data-status="none">⊘ Clear all</button>
                                                                    <span class="es-gatt-bulk-msg" aria-live="polite"></span>
                                                                </div>
                                                                <?php foreach ( $members as $m ) :
                                                                    $mi      = strtoupper( substr( $m->display_name, 0, 2 ) );
                                                                    $member_pkg_course = trim( $gs_pkg_name . ( $gs_course_name ? ' · ' . $gs_course_name : '' ) );
                                                                    $cur     = isset( $g_att_map[ $sid ][ (int) $m->ID ] )
                                                                                ? ES_Packages::normalize_att_status( $g_att_map[ $sid ][ (int) $m->ID ]['status'] )
                                                                                : 'none';
                                                                    $cur_comment = isset( $g_att_map[ $sid ][ (int) $m->ID ] )
                                                                                    ? $g_att_map[ $sid ][ (int) $m->ID ]['comment'] : '';
                                                                ?>
                                                                    <div class="es-gatt-row" data-user-id="<?php echo (int) $m->ID; ?>" data-slot-id="<?php echo $sid; ?>" data-package-id="<?php echo (int) $grp['package_id']; ?>">
                                                                        <div class="es-tabui-avatar" style="width:32px;height:32px;background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;font-size:11px;"><?php echo esc_html( $mi ); ?></div>
                                                                        <div style="flex:1;min-width:160px;" class="es-member-name">
                                                                            <strong><?php echo esc_html( $m->display_name ); ?></strong>
                                                                            <div class="es-cell-sub"><?php echo esc_html( $member_pkg_course ); ?></div>
                                                                        </div>
                                                                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                                            <button type="button" class="es-gatt-btn <?php echo $cur === 'present' ? 'is-on' : ''; ?>" data-status="present">✓ Present</button>
                                                                            <button type="button" class="es-gatt-btn <?php echo $cur === 'absent_unexcused' ? 'is-on' : ''; ?>" data-status="absent_unexcused">✗ Absent - without permission</button>
                                                                            <button type="button" class="es-gatt-btn <?php echo $cur === 'absent_excused' ? 'is-on' : ''; ?>" data-status="absent_excused">○ Absent - with permission</button>
                                                                        </div>
                                                                        <input type="text" class="es-gatt-comment" value="<?php echo esc_attr( $cur_comment ); ?>" placeholder="Add attendance note..." />
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- PACKAGE (package-wise, same pattern as 1:1) -->
                        <div class="es-tabpane" data-pane="pkg">
                            <?php /* Course name is resolved from the selected/paid group package. */ ?>
                            <?php if ( $g_sched_blocked ) : ?>
                                <div class="es-alert es-alert-warning" style="margin-bottom:14px;padding:10px 12px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;color:#92400e;font-size:13px;line-height:1.5;">
                                    <strong>Scheduling disabled.</strong> <?php echo esc_html( $g_sched_reason ); ?>
                                    &nbsp;<a href="#" class="es-tab-link" data-goto="renew" style="color:#92400e;font-weight:600;text-decoration:underline;">Go to Renew tab →</a>
                                </div>
                            <?php endif; ?>

                            <div class="es-pkgcourse-head">
                                <div class="es-pkgcourse-col">
                                    <span class="es-pkgcourse-label">Active Package</span>
                                    <strong class="es-pkgcourse-val">
                                        <?php echo $g_current_payment && ! empty( $g_current_payment->package_name ) ? esc_html( $g_current_payment->package_name ) : ( $pkg ? esc_html( $pkg->package_name ) : 'No Active Package' ); ?>
                                    </strong>
                                </div>
                                <div class="es-pkgcourse-col">
                                    <span class="es-pkgcourse-label">Course</span>
                                    <strong class="es-pkgcourse-val"><?php echo $g_course_names !== '' ? esc_html( $g_course_names ) : '—'; ?></strong>
                                </div>
                            </div>

                            <?php if ( empty( $g_payments ) ) : ?>
                                <div class="es-card" style="padding:24px;text-align:center;background:#f8fafc;border:1px dashed #cbd5e1;margin-bottom:14px;">
                                    <div style="font-size:14px;color:#475569;margin-bottom:6px;">No paid group package is active yet.</div>
                                    <div style="font-size:12.5px;color:#64748b;">Use the <strong>Renew</strong> tab to send a package-selection/payment link to group members. After payment, packages will show here.</div>
                                </div>
                            <?php else : ?>
                                <div class="es-pkgacc-wrap" style="margin-bottom:14px;">
                                    <div class="es-section-label" style="margin-bottom:8px;">All Group Packages (<?php echo count( $g_payments ); ?>)</div>
                                    <div class="es-pkgacc">
                                        <?php foreach ( $g_payments as $i => $payRow ) :
                                            $payPkg  = ES_Packages::get( (int) $payRow->package_id );
                                            $p_total = (int) ( $payRow->total_sessions ?? 0 );
                                            $p_used  = (int) ( $payRow->used_sessions ?? 0 );
                                            $p_left  = max( 0, $p_total - $p_used );
                                            $p_pct   = $p_total > 0 ? round( ( $p_used / $p_total ) * 100 ) : 0;
                                            $is_expired = ! empty( $payRow->valid_until ) && strtotime( $payRow->valid_until ) < $g_now_ts;
                                            $is_empty   = ( $p_total > 0 && $p_left <= 0 );
                                            if ( $is_expired )      { $st_cls = 'es-pill-danger';  $st_txt = 'EXPIRED'; }
                                            elseif ( $is_empty )    { $st_cls = 'es-pill-warning'; $st_txt = 'NO SESSIONS LEFT'; }
                                            else                    { $st_cls = 'es-pill-success'; $st_txt = 'ACTIVE'; }
                                            $is_current = $g_current_payment && (int) $g_current_payment->id === (int) $payRow->id;
                                            $pay_course_name = ! empty( $payRow->course_name ) ? $payRow->course_name : $g_course_names;
                                        ?>
                                            <div class="es-pkgacc-row<?php echo $is_current ? ' is-current is-open' : ''; ?>">
                                                <button type="button" class="es-pkgacc-head" aria-expanded="<?php echo $is_current ? 'true' : 'false'; ?>">
                                                    <span class="es-pkgacc-caret">›</span>
                                                    <span class="es-pkgacc-name">
                                                        <?php echo esc_html( $payRow->package_name ? $payRow->package_name : ( 'Package #' . (int) $payRow->package_id ) ); ?>
                                                        <?php if ( $is_current ) : ?><span class="es-pill es-pill-info" style="margin-left:6px;font-size:10px;">CURRENT</span><?php endif; ?>
                                                    </span>
                                                    <span class="es-pkgacc-sub">
                                                        <?php echo (int) $p_used; ?> / <?php echo (int) $p_total; ?> used · <?php echo (int) $p_left; ?> left<?php if ( $pay_course_name ) : ?> · <?php echo esc_html( $pay_course_name ); ?><?php endif; ?>
                                                    </span>
                                                    <span class="es-pill <?php echo esc_attr( $st_cls ); ?>"><?php echo esc_html( $st_txt ); ?></span>
                                                </button>
                                                <div class="es-pkgacc-body" <?php echo $is_current ? '' : 'style="display:none;"'; ?>>
                                                    <div class="es-package-detail-grid">
                                                        <div><span>Package</span><strong><?php echo esc_html( $payRow->package_name ? $payRow->package_name : '—' ); ?></strong></div>
                                                        <?php if ( $pay_course_name !== '' ) : ?><div><span>Course</span><strong><?php echo esc_html( $pay_course_name ); ?></strong></div><?php endif; ?>
                                                        <?php if ( $payPkg && ! empty( $payPkg->sub_heading ) ) : ?><div><span>Sub Heading</span><strong><?php echo esc_html( $payPkg->sub_heading ); ?></strong></div><?php endif; ?>
                                                        <?php $p_dur = (int) ( $payRow->months ?? 0 ); ?>
                                                        <?php if ( $p_dur > 0 ) : ?><div><span>Duration</span><strong><?php echo (int) $p_dur; ?> month<?php echo $p_dur > 1 ? 's' : ''; ?></strong></div><?php endif; ?>
                                                        <div><span>Monthly Sessions</span><strong><?php echo (int) ( $payRow->monthly_session_limit ?? 0 ); ?></strong></div>
                                                        <div><span>Total Sessions</span><strong><?php echo (int) $p_total; ?></strong></div>
                                                        <div><span>Used Sessions</span><strong><?php echo (int) $p_used; ?></strong></div>
                                                        <div><span>Remaining</span><strong><?php echo (int) $p_left; ?></strong></div>
                                                        <?php if ( ! empty( $payRow->valid_until ) ) : ?><div><span>Valid Until</span><strong><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $payRow->valid_until ) ) ); ?></strong></div><?php endif; ?>
                                                        <?php if ( ! empty( $payRow->amount ) ) : ?><div><span>Paid</span><strong><?php echo esc_html( ES_Helpers::format_price( $payRow->amount, ! empty( $payRow->currency ) ? $payRow->currency : 'INR' ) ); ?></strong></div><?php endif; ?>
                                                    </div>
                                                    <div class="es-usage-bar" style="margin-top:10px;"><div class="es-usage-bar-fill" style="width:<?php echo (int) $p_pct; ?>%;"></div></div>
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
                                <div class="es-section-label">Group Package Summary</div>
                                <div class="es-package-detail-grid">
                                    <div><span>Total Sessions</span><strong><?php echo (int) $g_total; ?></strong></div>
                                    <div><span>Used Sessions</span><strong><?php echo (int) $g_used; ?></strong></div>
                                    <div><span>Remaining Sessions</span><strong><?php echo (int) $g_left; ?></strong></div>
                                    <div><span>Members</span><strong><?php echo count( $members ); ?></strong></div>
                                </div>
                            </div>
                        </div>

                        <!-- SCHEDULE (with edit/delete + per-package upload) -->
                        <div class="es-tabpane" data-pane="schedule" style="display:none;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                                <div class="es-section-label" style="margin:0;">All Scheduled Meetings</div>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <?php if ( $g_sched_blocked ) : ?>
                                        <button type="button" class="es-btn es-btn-primary es-btn-sm" disabled title="<?php echo esc_attr( $g_sched_reason ); ?>">+ Schedule</button>
                                    <?php else : ?>
                                        <button type="button" class="es-btn es-btn-primary es-btn-sm es-open-schedule-modal">+ Schedule</button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ( ! empty( $g_schedule_package_options ) ) : ?>
                                <div class="es-field" style="max-width:420px;margin:0 0 14px;">
                                    <label class="es-label">Filter Schedule by Package</label>
                                    <select id="es-gschedule-package-filter" style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
                                        <option value="">All packages</option>
                                        <?php foreach ( $g_schedule_package_options as $sched_opt ) : ?>
                                            <option value="<?php echo (int) $sched_opt['id']; ?>">
                                                <?php echo esc_html( $sched_opt['name'] ); ?><?php if ( ! empty( $sched_opt['course'] ) ) : ?> · <?php echo esc_html( $sched_opt['course'] ); ?><?php endif; ?> · <?php echo (int) $sched_opt['count']; ?> session<?php echo (int) $sched_opt['count'] === 1 ? '' : 's'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <!-- Global files -->
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

                            <?php if ( empty( $g_schedule ) ) : ?>
                                <p class="es-empty-cell">No meetings scheduled for this group yet.</p>
                            <?php else : ?>
                                <div class="es-card es-card-flush">
                                    <table class="es-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Duration</th>
                                                <th>Title</th>
                                                <th>Package</th>
                                                <th>Platform</th>
                                                <th>Attendees</th>
                                                <th>Meeting</th>
                                                <th>Files / Videos</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $g_schedule as $row ) :
                                                $d = strtotime( $row->slot_date );
                                                $end_lbl = $row->end_time ? substr( $row->end_time, 0, 5 ) : '';
                                                $row_files = isset( $files_by_slot[ (int) $row->slot_id ] ) ? $files_by_slot[ (int) $row->slot_id ] : array();
                                                $row_pkg_id = ! empty( $row->schedule_package_id ) ? (int) $row->schedule_package_id : ( ! empty( $row->slot_package_id ) ? (int) $row->slot_package_id : 0 );
                                                $row_pkg_name = ! empty( $row->package_name ) ? $row->package_name : ( $pkg ? $pkg->package_name : '' );
                                                $row_course_name = ! empty( $row->schedule_course_name ) ? $row->schedule_course_name : ( ! empty( $row->slot_course_name ) ? $row->slot_course_name : ( ! empty( $row->course_id ) ? ES_Packages::course_name( (int) $row->course_id ) : $g_course_names ) );
                                            ?>
                                                <tr class="es-gschedule-row" data-slot-id="<?php echo (int) $row->slot_id; ?>" data-package-id="<?php echo (int) $row_pkg_id; ?>">
                                                    <td><strong><?php echo esc_html( date_i18n( 'M j, Y', $d ) ); ?></strong></td>
                                                    <td><?php echo esc_html( substr( $row->start_time, 0, 5 ) . ( $end_lbl ? ' – ' . $end_lbl : '' ) ); ?></td>
                                                    <td><?php echo (int) $row->duration_min; ?> min</td>
                                                    <td>
                                                        <?php echo esc_html( $row->title ?: 'Group Session' ); ?>
                                                        <?php if ( $row_course_name ) : ?><div class="es-cell-sub">Course: <?php echo esc_html( $row_course_name ); ?></div><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ( $row_pkg_name ) : ?>
                                                            <span class="es-pill es-pill-info" style="font-size:11px;">📦 <?php echo esc_html( $row_pkg_name ); ?></span>
                                                        <?php else : ?>
                                                            <span class="es-cell-sub">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo esc_html( $row->platform ?: '—' ); ?></td>
                                                    <td><span class="es-badge"><?php echo (int) $row->attendee_count; ?></span></td>
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
                                                            <input type="file" class="es-slot-file-input" data-slot-id="<?php echo (int) $row->slot_id; ?>" data-target-type="group" data-target-id="<?php echo (int) $selected->id; ?>" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mov,.webm,.mkv,.avi" multiple style="display:none;" />
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
                                                            data-title="<?php echo esc_attr( $row->title ?: 'Group Session' ); ?>"
                                                            title="Delete session">🗑 Delete</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            <!-- Global package files (not tied to a session) -->
                            <?php if ( ! empty( $global_files ) ) : ?>
                                <div style="padding:10px 0 4px;">
                                    <div class="es-section-label" style="font-size:11px;margin-bottom:6px;">Group Files (not tied to a session)</div>
                                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                                        <?php foreach ( $global_files as $gf ) : ?>
                                            <div class="es-file-chip" data-file-id="<?php echo (int) $gf->id; ?>" style="display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;">
                                                <span class="es-pill es-pill-info" style="font-size:10px;padding:2px 6px;"><?php echo esc_html( strtoupper( $gf->file_type ) ); ?></span>
                                                <a href="<?php echo esc_url( $gf->file_url ); ?>" target="_blank" rel="noopener" style="font-size:12px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;"><?php echo esc_html( $gf->file_name ); ?></a>
                                                <button type="button" class="es-delete-file-btn" data-file-id="<?php echo (int) $gf->id; ?>" title="Delete file" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:0;">×</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div style="display:flex;justify-content:flex-end;margin-top:8px;">
                                <button type="button" class="es-btn es-btn-ghost es-btn-sm es-open-pkg-upload"
                                    data-pkg-id="<?php echo $selected->package_id ? (int) $selected->package_id : 0; ?>"
                                    data-pkg-name="<?php echo esc_attr( $selected->package_name ?: $selected->group_name ); ?>"
                                    title="Upload files for this group">
                                    <span class="dashicons dashicons-upload" style="font-size:14px;width:14px;height:14px;vertical-align:text-bottom;"></span> Upload Files
                                </button>
                            </div>
                        </div>

                        <!-- MEMBERS -->
                        <div class="es-tabpane" data-pane="members" style="display:none;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;">
                                <div class="es-section-label" style="margin:0;">Members (<?php echo count( $members ); ?>)</div>
                                <button type="button" class="es-btn es-btn-primary es-btn-sm es-add-member-btn" data-group-id="<?php echo (int) $selected->id; ?>">+ Add Member</button>
                            </div>
                            <?php if ( empty( $members ) ) : ?>
                                <p class="es-empty-cell">No members yet.</p>
                            <?php else : foreach ( $members as $m ) : $mi = strtoupper( substr( $m->display_name, 0, 2 ) ); ?>
                                <div class="es-member-item">
                                    <div class="es-tabui-avatar" style="width:32px;height:32px;background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;font-size:11px;"><?php echo esc_html( $mi ); ?></div>
                                    <div style="flex:1;min-width:0;"><div class="es-member-name"><?php echo esc_html( $m->display_name ); ?></div><div class="es-member-email"><?php echo esc_html( $m->user_email ); ?></div></div>
                                    <button type="button" class="es-btn es-btn-danger es-btn-sm es-remove-member-btn" data-group="<?php echo (int) $selected->id; ?>" data-user="<?php echo (int) $m->ID; ?>">Remove</button>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                        <!-- RENEW TAB -->
                        <div class="es-tabpane" data-pane="renew" style="display:none;">
                            <div class="es-section-label" style="margin-bottom:10px;">Renew / Send Group Package Link</div>
                            <p style="font-size:13px;color:var(--es-text-muted);margin:0 0 16px;line-height:1.5;">
                                This works the same as After Call. Select a package and course, then send the package-selection/payment link to group members. The package becomes active only after the student completes the selection/payment.
                            </p>

                            <div class="es-aftercall-linkbox" style="margin-bottom:16px;">
                                <div class="es-aftercall-linklabel">Package Selection Link <span>(created after submit)</span></div>
                                <div class="es-aftercall-linkrow">
                                    <input type="text" id="es-grp-renew-link" readonly value="" placeholder="The renewal link will appear here after submit" />
                                    <button type="button" class="es-btn es-btn-primary es-copy-renew-link" data-target="#es-grp-renew-link">Copy</button>
                                </div>
                                <div id="es-grp-renew-links-note" class="es-helper" style="display:none;margin-top:8px;"></div>
                            </div>

                            <?php if ( $g_total > 0 && $g_left > 0 ) : ?>
                                <div class="es-alert" style="padding:10px 12px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;color:#065f46;font-size:13px;margin-bottom:16px;">
                                    <strong>Note:</strong> This group still has <?php echo (int) $g_left; ?> session<?php echo $g_left === 1 ? '' : 's'; ?> left. Active same-package plans are hidden from the renew dropdown until they expire or have no sessions left.
                                </div>
                            <?php endif; ?>

                            <div class="es-card es-renew-card" style="padding:20px;">
                                <div class="es-renew-grid">
                                    <div class="es-field">
                                        <label class="es-label">Outcome</label>
                                        <input type="text" value="Group Student" readonly style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#f8fafc;" />
                                    </div>
                                    <div class="es-field">
                                        <label class="es-label">Group</label>
                                        <input type="text" value="<?php echo esc_attr( $selected->group_name ); ?>" readonly style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#f8fafc;" />
                                    </div>
                                </div>

                                <div class="es-field" style="margin-bottom:14px;">
                                    <label class="es-label">Select Package to Renew With</label>
                                    <select id="es-grp-renew-package" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                                        <option value="">— Select a package —</option>
                                        <?php foreach ( $group_renew_packages as $ap ) : ?>
                                            <option value="<?php echo (int) $ap->id; ?>"
                                                data-sessions="<?php echo (int) ( $ap->total_sessions ?? 0 ); ?>"
                                                data-months="<?php echo (int) ( $ap->months ?? 1 ); ?>">
                                                <?php echo esc_html( $ap->package_name ); ?>
                                                (<?php echo (int) ( $ap->total_sessions ?? 0 ); ?> sessions · <?php echo (int) ( $ap->months ?? 1 ); ?> months)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ( empty( $group_renew_packages ) ) : ?>
                                        <small class="es-helper" style="display:block;margin-top:6px;color:#b45309;">No renewable package is available right now because the same package is still active or has sessions left.</small>
                                    <?php endif; ?>
                                </div>

                                <div id="es-grp-renew-preview" style="display:none;padding:12px 14px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:10px;margin-bottom:14px;font-size:13px;">
                                    <strong id="es-grp-renew-pkg-name"></strong>
                                    <span id="es-grp-renew-pkg-meta" style="color:#6d28d9;margin-left:8px;"></span>
                                </div>

                                <?php if ( ! empty( $course_posts ) ) : ?>
                                <div class="es-field" style="margin-bottom:14px;">
                                    <label class="es-label">Course <span style="font-weight:400;color:var(--es-text-muted);">(included in email subject + body)</span></label>
                                    <select id="es-grp-renew-course" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;">
                                        <option value="">Select course...</option>
                                        <?php foreach ( $course_posts as $cp ) : ?>
                                            <option value="<?php echo (int) $cp->ID; ?>" <?php selected( (int) $cp->ID, $selected_course_id ); ?>><?php echo esc_html( $cp->post_title ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <div class="es-field" style="margin-bottom:14px;">
                                    <label class="es-label">Notes / Comments <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label>
                                    <textarea id="es-grp-renew-notes" rows="3" style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;font-size:13px;" placeholder="Renewal notes..."></textarea>
                                </div>

                                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:16px;cursor:pointer;">
                                    <input type="checkbox" id="es-grp-renew-email" checked /> Send package-selection email to group members
                                </label>

                                <div id="es-grp-renew-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>

                                <div style="display:flex;justify-content:flex-end;">
                                    <button type="button" class="es-btn es-btn-primary" id="es-grp-renew-submit" data-group-id="<?php echo (int) $selected->id; ?>" <?php disabled( empty( $group_renew_packages ) ); ?>>
                                        <span class="dashicons dashicons-email-alt" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;margin-right:4px;"></span>
                                        Send Renew Link
                                    </button>
                                </div>
                            </div>

                            <!-- Add Student to Group (admin-only, no payment) -->
                            <div class="es-section-label" style="margin:20px 0 10px;">Add Student to This Group</div>
                            <p style="font-size:13px;color:var(--es-text-muted);margin:0 0 14px;line-height:1.5;">
                                Manually add any registered student to this group without sending a payment link. Their existing 1:1 data remains unchanged.
                            </p>
                            <div class="es-card" style="padding:18px 20px;">
                                <div class="es-field" style="margin-bottom:14px;">
                                    <label class="es-label">Select Student</label>
                                    <select id="es-grp-add-student" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;">
                                        <option value="">— Choose a student —</option>
                                        <?php
                                        // Offer ALL registered users except existing members of this group
                                        $existing_member_ids = array_map( function($m){ return (int) $m->ID; }, $members );
                                        $all_site_users = get_users( array( 'role__not_in' => array( 'administrator' ), 'number' => 500, 'orderby' => 'display_name', 'order' => 'ASC' ) );
                                        foreach ( $all_site_users as $su ) :
                                            if ( in_array( (int) $su->ID, $existing_member_ids, true ) ) continue;
                                        ?>
                                            <option value="<?php echo (int) $su->ID; ?>"><?php echo esc_html( $su->display_name . ' (' . $su->user_email . ')' ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="es-grp-add-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>
                                <div style="display:flex;justify-content:flex-end;">
                                    <button type="button" class="es-btn es-btn-primary" id="es-grp-add-submit" data-group-id="<?php echo (int) $selected->id; ?>">
                                        <span class="dashicons dashicons-groups" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;margin-right:4px;"></span>
                                        Add to Group
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- SCHEDULE MODAL (group) -->
                        <div id="es-schedule-modal" class="es-schedule-modal" aria-hidden="true">
                            <div class="es-schedule-modal-overlay"></div>
                            <div id="es-schedule-session" class="es-newsession es-schedule-modal-card" data-target-type="group" data-target-id="<?php echo (int) $selected->id; ?>">
                                <button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
                                <div class="es-section-label">New Group Session</div>
                                <p class="es-helper" style="margin:0 0 14px;color:var(--es-text-muted);font-size:12.5px;">Schedules a meeting for <strong><?php echo esc_html( $selected->group_name ); ?></strong> and books all <?php echo count( $members ); ?> member<?php echo count( $members ) === 1 ? '' : 's'; ?>.</p>
                                <?php if ( ! empty( $g_schedulable ) ) : ?>
                                    <div class="es-ss-pkg-pick" style="margin:0 0 16px;padding:14px 16px;background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%);border:1px solid #c4b5fd;border-radius:12px;">
                                        <label class="es-label" style="color:#6d28d9;font-weight:600;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                                            <span class="dashicons dashicons-archive" style="font-size:16px;width:16px;height:16px;"></span>
                                            Group Package
                                        </label>
                                        <select id="es-ss-payment" style="width:100%;padding:9px 12px;border:1px solid #c4b5fd;border-radius:8px;background:#fff;font-size:14px;font-weight:500;">
                                            <?php foreach ( $g_schedulable as $gp ) :
                                                $gp_label = $gp->package_name ? $gp->package_name : ( 'Package #' . (int) $gp->package_id );
                                                $gp_course = ! empty( $gp->course_name ) ? $gp->course_name : $g_course_names;
                                                $gp_left = max( 0, (int) ( $gp->total_sessions ?? 0 ) - (int) ( $gp->used_sessions ?? 0 ) );
                                                $gp_until = ! empty( $gp->valid_until ) ? date_i18n( 'M j, Y', strtotime( $gp->valid_until ) ) : '';
                                            ?>
                                                <option value="<?php echo (int) $gp->id; ?>"
                                                    data-package-name="<?php echo esc_attr( $gp_label ); ?>"
                                                    data-course-name="<?php echo esc_attr( $gp_course ); ?>"
                                                    data-total="<?php echo (int) ( $gp->total_sessions ?? 0 ); ?>"
                                                    data-used="<?php echo (int) ( $gp->used_sessions ?? 0 ); ?>"
                                                    data-left="<?php echo (int) $gp_left; ?>"
                                                    data-valid-until="<?php echo esc_attr( $gp_until ); ?>">
                                                    <?php echo esc_html( $gp_label ); ?><?php if ( $gp_course !== '' ) : ?> · Course: <?php echo esc_html( $gp_course ); ?><?php endif; ?> · <?php echo (int) $gp_left; ?> session<?php echo $gp_left === 1 ? '' : 's'; ?> left<?php if ( $gp_until ) : ?> · until <?php echo esc_html( $gp_until ); ?><?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="es-ss-payment-preview" class="es-ss-payment-preview" style="display:none;margin-top:10px;"></div>
                                        <small style="margin-top:6px;display:block;font-size:12px;color:#6d28d9;">Pick which group package this session consumes from.</small>
                                    </div>
                                <?php endif; ?>
                                <?php /* Course is taken from the linked group package. No manual course field in group session popup. */ ?>
                                <div class="es-modal-row">
                                    <div class="es-field"><label class="es-label">Date</label><input type="date" id="es-ss-date" min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" /></div>
                                    <div class="es-field"><label class="es-label">Start Time</label><input type="time" id="es-ss-time" /></div>
                                </div>
                                <div class="es-modal-row">
                                    <div class="es-field"><label class="es-label">Duration (min)</label><input type="number" id="es-ss-duration" value="60" min="5" step="5" /></div>
                                    <div class="es-field"><label class="es-label">Platform</label><select id="es-ss-platform"><?php foreach ( $platforms as $p ) : ?><option value="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></option><?php endforeach; ?></select></div>
                                </div>
                                <div class="es-field"><label class="es-label">Session Title <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label><input type="text" id="es-ss-title" placeholder="e.g. Week 3 — Group Speaking" /></div>
                                <div class="es-field"><label class="es-label">Notes <small style="font-weight:400;color:var(--es-text-muted);">(optional)</small></label><textarea id="es-ss-notes" rows="2" placeholder="Agenda / notes"></textarea></div>

                                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin:6px 0 12px;cursor:pointer;color:var(--es-text-soft);"><input type="checkbox" id="es-ss-email" checked /> Send confirmation email to members</label>
                                <div id="es-ss-msg" style="display:none;font-size:13px;margin-bottom:10px;"></div>
                                <div style="display:flex;justify-content:flex-end;"><button type="button" class="es-btn es-btn-primary" id="es-ss-submit"><span class="dashicons dashicons-video-alt2"></span> Schedule Session</button></div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- v4.6: Global Upload Modal (Group) — package-wise -->
<div id="es-global-upload-modal" class="es-schedule-modal" aria-hidden="true" style="display:none;">
    <div class="es-schedule-modal-overlay"></div>
    <div class="es-schedule-modal-card" style="max-width:480px;">
        <button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
        <div class="es-section-label">Upload File / Video</div>
        <p style="font-size:12.5px;color:var(--es-text-muted);margin:0 0 4px;line-height:1.5;">
            Attach files to this group (not tied to any specific session).
        </p>
        <div id="es-gu-pkg-label" style="display:none;font-size:12px;font-weight:600;color:#6d28d9;margin-bottom:12px;padding:6px 10px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:8px;"></div>
        <input type="hidden" id="es-gu-target-type" value="group" />
        <input type="hidden" id="es-gu-target-id" value="<?php echo $detail_mode && $selected ? (int) $selected->id : 0; ?>" />
        <input type="hidden" id="es-gu-package-id" value="" />
        <div class="es-field">
            <label class="es-label">Choose file(s)</label>
            <input type="file" id="es-gu-file" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mov,.webm,.mkv,.avi" style="display:block;width:100%;font-size:13px;" />
        </div>
        <div id="es-gu-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button type="button" class="es-btn es-btn-ghost es-schedule-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-gu-submit">
                <span class="dashicons dashicons-upload" style="font-size:16px;width:16px;height:16px;vertical-align:text-bottom;"></span> Upload
            </button>
        </div>
    </div>
</div>

<!-- v4.5: Edit Session Modal (Group) -->
<div id="es-edit-session-modal" class="es-schedule-modal" aria-hidden="true" style="display:none;">
    <div class="es-schedule-modal-overlay"></div>
    <div class="es-schedule-modal-card" style="max-width:560px;">
        <button type="button" class="es-schedule-modal-close" aria-label="Close">×</button>
        <div class="es-section-label">Edit Group Session</div>
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
        <div class="es-field"><label class="es-label">Session Title</label><input type="text" id="es-es-title" /></div>
        <div class="es-field"><label class="es-label">Notes</label><textarea id="es-es-notes" rows="2"></textarea></div>
        <div id="es-es-msg" style="display:none;font-size:13px;margin-bottom:10px;padding:8px 12px;border-radius:8px;"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button type="button" class="es-btn es-btn-ghost es-schedule-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-es-submit">Save Changes</button>
        </div>
    </div>
</div>

<style>
.es-package-detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:8px}
.es-package-detail-grid div{background:#f8fafc;border:1px solid #eef0f3;border-radius:10px;padding:10px 12px}
.es-package-detail-grid span{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:4px}
.es-package-detail-grid strong{font-size:14px;color:#1e293b}
.es-package-desc{margin-top:12px;padding-top:12px;border-top:1px solid #eef0f3;font-size:13px;line-height:1.6;color:#475569}
/* Package accordion */
.es-pkgacc{display:flex;flex-direction:column;gap:8px}
.es-pkgacc-row{border:1px solid #e5e7eb;border-radius:12px;background:#fff;overflow:hidden}
.es-pkgacc-row.is-current{border-color:#c4b5fd;background:#fbfaff}
.es-pkgacc-head{width:100%;display:flex;align-items:center;gap:10px;padding:13px 14px;background:transparent;border:0;cursor:pointer;text-align:left;font:inherit}
.es-pkgacc-head:hover{background:#f8fafc}
.es-pkgacc-caret{display:inline-block;transition:transform .18s ease;color:#6d28d9;font-size:18px;line-height:1}
.es-pkgacc-row.is-open .es-pkgacc-caret{transform:rotate(90deg)}
.es-pkgacc-name{font-weight:700;color:#1e293b;white-space:nowrap}
.es-pkgacc-sub{flex:1;min-width:0;color:#64748b;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.es-pkgacc-body{padding:0 14px 14px}
.es-pill-danger{background:#fee2e2;color:#b91c1c}
.es-pill-warning{background:#fef3c7;color:#92400e}
.es-pill-success{background:#d1fae5;color:#047857}
/* Package grouped attendance */
.es-gattpkg-accordion{display:flex;flex-direction:column;gap:12px}
.es-gatt-pkg{border:1px solid #e5e7eb;border-radius:14px;background:#fff;overflow:hidden}
.es-gatt-pkg.is-open{border-color:#c4b5fd;background:#fbfaff}
.es-gatt-pkg-head{width:100%;display:flex;align-items:center;gap:10px;padding:14px 16px;background:#f8fafc;border:0;cursor:pointer;text-align:left;font:inherit}
.es-gatt-pkg-head:hover{background:#f1f5f9}
.es-gatt-pkg-caret{display:inline-block;transition:transform .18s ease;color:#6d28d9;font-size:13px}
.es-gatt-pkg.is-open .es-gatt-pkg-caret{transform:rotate(90deg)}
.es-gatt-pkg-title{font-weight:800;color:#1e293b}
.es-gatt-pkg-sub{font-size:12px;color:#64748b}
.es-gatt-pkg-body{display:none;padding:10px}
.es-gatt-pkg.is-open .es-gatt-pkg-body{display:block}
/* After Call style renew link box */
.es-aftercall-linkbox{background:#faf5ff;border:1px solid #d8b4fe;border-radius:12px;padding:16px}
.es-aftercall-linklabel{font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#a16207;margin-bottom:8px}
.es-aftercall-linklabel span{font-weight:600;color:#64748b;text-transform:none;letter-spacing:0}
.es-aftercall-linkrow{display:flex;gap:10px;align-items:center}
.es-aftercall-linkrow input{flex:1;min-width:0;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;background:#fff;font-size:12px}
.es-renew-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:14px}
/* Attendance accordion */
.es-gatt-accordion{display:flex;flex-direction:column;gap:10px}
.es-gatt-sess{border:1px solid #e5e7eb;border-radius:12px;background:#fff;overflow:hidden}
.es-gatt-sess-head{width:100%;display:flex;align-items:center;gap:12px;padding:14px 16px;background:#f8fafc;border:0;cursor:pointer;text-align:left;font:inherit}
.es-gatt-sess-head:hover{background:#f1f5f9}
.es-gatt-sess-caret{display:inline-block;transition:transform .18s ease;color:#6d28d9;font-size:13px}
.es-gatt-sess.is-open .es-gatt-sess-caret{transform:rotate(90deg)}
.es-gatt-sess-date{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.es-gatt-sess-date strong{font-size:14px;color:#1e293b}
.es-gatt-sess-time{font-size:12px;color:#64748b}
.es-gatt-sess-count{font-size:12px;color:#6d28d9;background:#f5f3ff;border:1px solid #e9d5ff;border-radius:999px;padding:3px 10px;white-space:nowrap}
.es-gatt-sess-body{display:none;padding:8px 14px 14px;flex-direction:column;gap:8px}
.es-gatt-sess.is-open .es-gatt-sess-body{display:flex}
.es-gatt-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:10px 12px;border:1px solid #eef0f3;border-radius:10px;background:#fff}
.es-gatt-btn{border:1px solid #e5e7eb;border-radius:8px;background:transparent;padding:6px 10px;font-size:12px;cursor:pointer;color:#334155}
.es-gatt-btn.is-on{border-color:#6d28d9;font-weight:600}
.es-gatt-comment{flex:1;min-width:160px;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:12px}
.es-gatt-bulk{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:8px 12px;margin-bottom:4px;background:#f8fafc;border:1px dashed #e2e8f0;border-radius:10px}
.es-gatt-bulk-label{font-size:12px;font-weight:600;color:#475569}
.es-gatt-bulk-btn{border:1px solid #e5e7eb;border-radius:8px;background:#fff;padding:5px 10px;font-size:12px;cursor:pointer;color:#334155;line-height:1.2}
.es-gatt-bulk-btn:hover{border-color:#6d28d9;color:#6d28d9}
.es-gatt-bulk-present:hover{border-color:#10b981;color:#047857}
.es-gatt-bulk-unexcused:hover{border-color:#ef4444;color:#b91c1c}
.es-gatt-bulk-excused:hover{border-color:#f59e0b;color:#b45309}
.es-gatt-bulk-btn:disabled{opacity:.55;cursor:default}
.es-gatt-bulk-msg{font-size:12px;color:#10b981;margin-left:auto}
/* Schedule modal */
.es-schedule-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:100000;padding:24px}
.es-schedule-modal.is-open{display:flex}
.es-schedule-modal-overlay{position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(2px)}
.es-schedule-modal-card{position:relative;z-index:1;width:min(860px,calc(100vw - 48px));max-height:calc(100vh - 64px);overflow:auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 24px 80px rgba(15,23,42,.28)}
.es-schedule-modal-close{position:absolute;top:12px;right:12px;width:34px;height:34px;border:1px solid #e5e7eb;border-radius:999px;background:#fff;color:#64748b;font-size:22px;line-height:1;cursor:pointer}
body.es-modal-open{overflow:hidden}

    /* Group create/edit + add-member modal polish */
    .es-groups-page .es-modal{position:fixed;inset:0;z-index:100001;align-items:center;justify-content:center;padding:24px}
    .es-groups-page .es-modal[style*="display: block"]{display:flex!important}
    .es-groups-page .es-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.52);backdrop-filter:blur(3px)}
    .es-groups-page .es-modal-card{position:relative;z-index:1;width:min(560px,calc(100vw - 48px));background:#fff;border-radius:18px;box-shadow:0 24px 80px rgba(15,23,42,.25);overflow:hidden;border:1px solid #eef0f3}
    .es-groups-page .es-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:22px 26px;border-bottom:1px solid #f1f5f9;background:linear-gradient(180deg,#fff,#fbfbff)}
    .es-groups-page .es-modal-head h2{margin:0;font-size:22px;line-height:1.2;color:#1f2937;letter-spacing:.01em}
    .es-groups-page .es-modal-close{width:38px;height:38px;border:1px solid #e5e7eb;background:#fff;border-radius:12px;color:#64748b;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center}
    .es-groups-page .es-modal-close:hover{border-color:#c4b5fd;color:#6d28d9;background:#faf5ff}
    .es-groups-page .es-modal-body{padding:24px 26px;display:grid;gap:18px}
    .es-groups-page .es-modal-foot{display:flex;justify-content:flex-end;gap:10px;padding:18px 26px;background:#fafafa;border-top:1px solid #f1f5f9}
    .es-groups-page .es-modal-card .es-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700;margin-bottom:8px;display:block}
    .es-groups-page .es-modal-card input[type="text"],
    .es-groups-page .es-modal-card textarea,
    .es-groups-page .es-modal-card select{width:100%;border:1px solid #dbe2ea;border-radius:12px;padding:12px 14px;background:#fff;font-size:14px;line-height:1.45;box-shadow:0 1px 0 rgba(15,23,42,.02)}
    .es-groups-page .es-modal-card textarea{min-height:96px;resize:vertical}
    .es-groups-page .es-modal-card input:focus,
    .es-groups-page .es-modal-card textarea:focus,
    .es-groups-page .es-modal-card select:focus{outline:none;border-color:#8b5cf6;box-shadow:0 0 0 4px rgba(139,92,246,.10)}
    .es-groups-page .es-helper{font-size:12.5px;line-height:1.5;color:#64748b}

    </style>

<!-- ============ Add/Edit Group Modal ============ -->
<div class="es-modal" id="es-group-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card" style="max-width:580px;">
        <div class="es-modal-head" style="background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);padding:24px 28px;">
            <div>
                <div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;">Groups</div>
                <h2 id="es-group-modal-title" style="color:#fff;margin:0;font-size:22px;font-weight:700;letter-spacing:-.3px;">New Group</h2>
            </div>
            <button type="button" class="es-modal-close" aria-label="Close" style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;border-radius:12px;width:36px;height:36px;font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;">×</button>
        </div>
        <div class="es-modal-body" style="padding:28px;display:grid;gap:20px;">
            <input type="hidden" id="es-group-id" value="" />

            <div class="es-field">
                <label class="es-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700;display:block;margin-bottom:8px;">Group Name <span style="color:#e53e3e;">*</span></label>
                <input type="text" id="es-group-name" placeholder="e.g. IELTS Batch A — June 2026"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;background:#fff;transition:border-color .15s;box-sizing:border-box;" />
            </div>

            <div class="es-field">
                <label class="es-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700;display:block;margin-bottom:8px;">Package <span style="color:#9ca3af;font-weight:400;text-transform:none;letter-spacing:0;">(optional — links sessions to this package)</span></label>
                <select id="es-group-package" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;background:#fff;cursor:pointer;box-sizing:border-box;">
                    <option value="">— No package linked yet —</option>
                    <?php foreach ( ES_Packages::get_all( true ) as $gp ) :
                        $gp_type = ! empty( $gp->package_type ) ? $gp->package_type : '1to1';
                        $gp_label = array( '1to1' => '1:1', 'group' => 'Group', 'consultancy' => 'Consultancy' )[ $gp_type ] ?? $gp_type;
                    ?>
                        <option value="<?php echo (int) $gp->id; ?>" data-sessions="<?php echo (int) ($gp->total_sessions ?? 0); ?>" data-months="<?php echo (int) ($gp->months ?? 1); ?>">
                            [<?php echo esc_html( $gp_label ); ?>] <?php echo esc_html( $gp->package_name ); ?>
                            (<?php echo (int) ($gp->total_sessions ?? 0); ?> sessions · <?php echo (int) ($gp->months ?? 1); ?> mo)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="es-group-pkg-preview" style="display:none;margin-top:10px;padding:10px 14px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:8px;font-size:13px;color:#5b21b6;"></div>
            </div>

            <div class="es-field">
                <label class="es-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700;display:block;margin-bottom:8px;">Description <span style="color:#9ca3af;font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span></label>
                <textarea id="es-group-notes" rows="3" placeholder="Optional notes about this batch, schedule, or focus area…"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:11px 14px;font-size:14px;background:#fff;resize:vertical;box-sizing:border-box;min-height:80px;"></textarea>
            </div>

            <div style="padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:12px;color:#64748b;line-height:1.6;">
                <strong style="color:#374151;">ℹ️ About packages:</strong> The selected package defines how many sessions each student gets. Individual per-student session counts are tracked automatically as attendance is marked.
            </div>
        </div>
        <div class="es-modal-foot" style="padding:18px 28px;background:#fafbff;border-top:1px solid #eef2f7;display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-group-save" style="min-width:140px;">
                <span id="es-group-save-text">Create Group</span>
            </button>
        </div>
    </div>
</div>

<?php if ( $detail_mode && $selected ) : ?>
<!-- ============ Add Member Modal ============ -->
<div class="es-modal" id="es-add-member-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card">
        <div class="es-modal-head">
            <h2>Add Student to Group</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">
            <div class="es-field">
                <label class="es-label">Select Student</label>
                <select id="es-add-member-user">
                    <option value="">— Select student —</option>
                    <?php foreach ( $assignable_users as $au ) : ?>
                        <option value="<?php echo (int) $au->ID; ?>"><?php echo esc_html( $au->display_name . ' — ' . $au->user_email ); ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="es-helper" style="display:block;margin-top:8px;">Adding a student to this group will not change their 1:1 package or 1:1 schedule.</small>
            </div>
            <div id="es-add-member-msg" style="display:none;font-size:13px;padding:10px 12px;border-radius:8px;"></div>
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-add-member-save" data-group-id="<?php echo (int) $selected->id; ?>">Add Student</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
jQuery(function($){
    $('#es-group-search').on('input', function(){
        var q = $(this).val().toLowerCase();
        $('#es-group-list .es-tabui-item').each(function(){
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
    $(document).on('click', '.es-tab-link[data-goto]', function(e){
        e.preventDefault();
        $('.es-tab[data-tab="' + $(this).data('goto') + '"]').trigger('click');
    });

    /* ── Package accordions and package filters ── */
    $(document).on('click', '.es-pkgacc-head', function(){
        var $row = $(this).closest('.es-pkgacc-row');
        $row.toggleClass('is-open');
        $(this).attr('aria-expanded', $row.hasClass('is-open') ? 'true' : 'false');
        $row.find('> .es-pkgacc-body').stop(true,true).slideToggle(160);
    });
    $(document).on('click', '.es-gatt-pkg-head', function(){
        var $pkg = $(this).closest('.es-gatt-pkg');
        $pkg.toggleClass('is-open');
        $pkg.find('> .es-gatt-pkg-body').stop(true,true).slideToggle(160);
    });
    $(document).on('change', '#es-gatt-package-filter', function(){
        var pid = String($(this).val() || '');
        $('.es-gatt-pkg').each(function(){
            var rowPid = String($(this).data('package-id') || '');
            $(this).toggle(!pid || rowPid === pid);
        });
    });
    $(document).on('change', '#es-gschedule-package-filter', function(){
        var pid = String($(this).val() || '');
        $('.es-gschedule-row').each(function(){
            var rowPid = String($(this).data('package-id') || '');
            $(this).toggle(!pid || rowPid === pid);
        });
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

    /* ── Schedule modal ── */
    $(document).on('click', '.es-open-schedule-modal', function(){
        $('#es-schedule-modal').css('display','').addClass('is-open').attr('aria-hidden','false');
        $('body').addClass('es-modal-open');
        setTimeout(function(){ $('#es-ss-date').trigger('focus'); }, 80);
    });
    $(document).on('click', '.es-schedule-modal-close, .es-schedule-modal-overlay', function(){
        $(this).closest('.es-schedule-modal').removeClass('is-open').attr('aria-hidden','true').css('display','none');
        $('body').removeClass('es-modal-open');
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
    $(document).on('click', '.es-open-schedule-modal', function(){ setTimeout(esRefreshSchedulePaymentPreview, 80); });
    esRefreshSchedulePaymentPreview();

    /* ── Schedule modal: preview queued files ── */
    $(document).on('change', '#es-ss-files', function(){
        var files = this.files; var $list = $('#es-ss-files-list').empty();
        if (!files || !files.length) return;
        var names = []; for (var i=0;i<files.length;i++) names.push(files[i].name);
        $list.text(files.length + ' file(s) queued: ' + names.join(', '));
    });

    /* ── Delete file ── */
    $(document).on('click', '.es-delete-file-btn', function(){
        var fileId = $(this).data('file-id');
        var $item  = $(this).closest('.es-slot-file-item, .es-file-chip');
        if (!confirm('Delete this file?')) return;
        $.post(ES_ADMIN.ajax_url, { action:'es_admin_delete_session_file', nonce:ES_ADMIN.nonce, id:fileId }).done(function(res){
            if (res && res.success) $item.remove();
            else alert((res && res.data && res.data.message) || 'Could not delete file.');
        });
    });

    /* ── Add files to slot ── */
    $(document).on('click', '.es-slot-add-files', function(){
        var slotId = $(this).data('slot-id');
        $('.es-slot-file-input[data-slot-id="' + slotId + '"]').trigger('click');
    });
    $(document).on('change', '.es-slot-file-input', function(){
        var $inp = $(this), slotId = $inp.data('slot-id'), type = $inp.data('target-type'), tid = $inp.data('target-id');
        var $wrap = $('.es-slot-files-wrap[data-slot-id="' + slotId + '"]');
        var files = this.files; if (!files || !files.length) return;
        function doNext(i) {
            if (i >= files.length) { $inp.val(''); return; }
            var fd = new FormData();
            fd.append('action','es_admin_upload_session_file'); fd.append('nonce',ES_ADMIN.nonce);
            fd.append('target_type',type); fd.append('target_id',tid);
            fd.append('slot_id',slotId); fd.append('file',files[i]);
            $.ajax({url:ES_ADMIN.ajax_url,type:'POST',data:fd,processData:false,contentType:false}).done(function(res){
                if (res && res.success && res.data.file) {
                    var f = res.data.file;
                    var html = '<div class="es-slot-file-item" data-file-id="'+f.id+'" style="display:inline-flex;align-items:center;gap:6px;">' +
                        '<a href="'+f.file_url+'" target="_blank" rel="noopener" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;">' +
                        '<span class="es-pill es-pill-info" style="font-size:10px;padding:2px 6px;">'+f.file_type.toUpperCase()+'</span>' +
                        '<span style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;">'+f.file_name+'</span></a>' +
                        '<button type="button" class="es-delete-file-btn" data-file-id="'+f.id+'" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;line-height:1;padding:0 2px;">×</button></div>';
                    $wrap.append(html);
                }
                doNext(i+1);
            }).fail(function(){ doNext(i+1); });
        }
        doNext(0);
    });

    /* ── Global / package upload modal (Group) ── */
    $(document).on('click', '.es-open-pkg-upload', function(){
        var pkgId   = $(this).data('pkg-id') || 0;
        var pkgName = $(this).data('pkg-name') || '';
        $('#es-gu-file').val(''); $('#es-gu-msg').hide();
        $('#es-gu-package-id').val(pkgId);
        if (pkgId && pkgName) {
            $('#es-gu-pkg-label').text('📦 ' + pkgName).show();
        } else {
            $('#es-gu-pkg-label').hide();
        }
        $('#es-global-upload-modal').addClass('is-open').attr('aria-hidden','false').css('display','flex');
        $('body').addClass('es-modal-open');
    });
    $(document).on('click', '.es-open-global-upload', function(){
        $('#es-gu-file').val(''); $('#es-gu-msg').hide();
        $('#es-gu-package-id').val('');
        $('#es-gu-pkg-label').hide();
        $('#es-global-upload-modal').addClass('is-open').attr('aria-hidden','false').css('display','flex');
        $('body').addClass('es-modal-open');
    });
    $(document).on('click', '#es-gu-submit', function(){
        var files = document.getElementById('es-gu-file').files;
        if (!files || !files.length) { $('#es-gu-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Please choose at least one file.'); return; }
        var type = $('#es-gu-target-type').val(), tid = $('#es-gu-target-id').val(), pkgId = $('#es-gu-package-id').val() || 0;
        var $btn = $(this).prop('disabled',true).text('Uploading…');
        var uploaded = 0, total = files.length;
        function doNext(i) {
            if (i >= total) {
                $btn.prop('disabled',false).text('Upload');
                $('#es-gu-msg').css({display:'block',background:'#d1fae5',color:'#065f46'}).text(uploaded + ' file(s) uploaded.');
                setTimeout(function(){ window.location.reload(); }, 1200);
                return;
            }
            var fd = new FormData();
            fd.append('action','es_admin_global_upload'); fd.append('nonce',ES_ADMIN.nonce);
            fd.append('target_type',type); fd.append('target_id',tid);
            fd.append('package_id', pkgId); fd.append('file',files[i]);
            $.ajax({url:ES_ADMIN.ajax_url,type:'POST',data:fd,processData:false,contentType:false}).done(function(res){
                if (res && res.success) uploaded++;
                doNext(i+1);
            }).fail(function(){ doNext(i+1); });
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
        $.post(ES_ADMIN.ajax_url, {
            action:'es_admin_edit_schedule_session', nonce:ES_ADMIN.nonce,
            slot_id:$('#es-es-slot-id').val(), slot_date:$('#es-es-date').val(),
            start_time:$('#es-es-time').val(), duration_min:$('#es-es-duration').val(),
            platform:$('#es-es-platform').val(), title:$('#es-es-title').val(), notes:$('#es-es-notes').val()
        }).done(function(res){
            $btn.prop('disabled',false).text('Save Changes');
            if (res && res.success) { $('#es-edit-session-modal').removeClass('is-open').hide(); $('body').removeClass('es-modal-open'); setTimeout(function(){ window.location.reload(); }, 600); }
            else $('#es-es-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not save.');
        }).fail(function(){ $btn.prop('disabled',false).text('Save Changes'); });
    });

    /* ── Delete session ── */
    $(document).on('click', '.es-delete-session-btn', function(){
        var slotId = $(this).data('slot-id'), title = $(this).data('title') || 'this session';
        if (!confirm('Delete "' + title + '"? All bookings will be removed and sessions refunded.')) return;
        $.post(ES_ADMIN.ajax_url, { action:'es_admin_delete_schedule_session', nonce:ES_ADMIN.nonce, slot_id:slotId }).done(function(res){
            if (res && res.success) { if (typeof toast === 'function') toast('Session deleted'); setTimeout(function(){ window.location.reload(); }, 600); }
            else alert((res && res.data && res.data.message) || 'Could not delete session.');
        });
    });

    /* ── Group renew ── */
    $('#es-grp-renew-package').on('change', function(){
        var $opt = $(this).find(':selected');
        $('#es-grp-renew-link').val('');
        $('#es-grp-renew-links-note').hide().empty();
        if (!$opt.val()) { $('#es-grp-renew-preview').hide(); return; }
        $('#es-grp-renew-pkg-name').text( $opt.text().split('(')[0].trim() );
        $('#es-grp-renew-pkg-meta').text( $opt.data('sessions') + ' sessions · ' + $opt.data('months') + ' months' );
        $('#es-grp-renew-preview').show();
    });
    $(document).on('click', '#es-grp-renew-submit', function(){
        var pkgId = $('#es-grp-renew-package').val();
        if (!pkgId) { $('#es-grp-renew-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Please select a package.'); return; }
        var groupId = $(this).data('group-id');
        var $btn = $(this).prop('disabled',true).text('Creating link…');
        $('#es-grp-renew-link').val('');
        $('#es-grp-renew-links-note').hide().empty();
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_save_group',
            nonce: ES_ADMIN.nonce,
            id: groupId,
            package_id: pkgId,
            course_ids: $('#es-grp-renew-course').val() ? [$('#es-grp-renew-course').val()] : [],
            renew: 1,
            notes: $('#es-grp-renew-notes').val(),
            send_email: $('#es-grp-renew-email').is(':checked') ? 1 : 0
        }).done(function(res){
            $btn.prop('disabled',false).text('Send Renew Link');
            if (res && res.success) {
                var msg = (res.data && res.data.message) ? res.data.message : 'Renew package link created.';
                var link = (res.data && res.data.share_link) ? res.data.share_link : '';
                var count = (res.data && res.data.link_count) ? parseInt(res.data.link_count, 10) : 0;
                if (link) $('#es-grp-renew-link').val(link);
                if (count > 1) $('#es-grp-renew-links-note').show().text(count + ' member links created. The first link is shown above; each member receives their own link by email when email is enabled.');
                $('#es-grp-renew-msg').css({display:'block',background:'#d1fae5',color:'#065f46'}).text(msg);
            } else {
                $('#es-grp-renew-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not create renew link.');
            }
        }).fail(function(){
            $btn.prop('disabled',false).text('Send Renew Link');
            $('#es-grp-renew-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Server error.');
        });
    });

    if ($.fn.select2 && $('#es-grp-renew-course').length) {
        $('#es-grp-renew-course').select2({ placeholder: 'Search course…', width: '100%', allowClear: true });
    }

    /* ── Add Student to Group (Purchase tab) ── */
    $(document).on('click', '#es-grp-add-submit', function(){
        var groupId = $(this).data('group-id');
        var userId  = $('#es-grp-add-student').val();
        if (!userId) { $('#es-grp-add-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Please select a student.'); return; }
        var $btn = $(this).prop('disabled', true).text('Adding…');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_add_group_member',
            nonce: ES_ADMIN.nonce,
            group_id: groupId,
            user_id: userId
        }).done(function(res){
            $btn.prop('disabled', false).text('Add to Group');
            if (res && res.success) {
                $('#es-grp-add-msg').css({display:'block',background:'#d1fae5',color:'#065f46'}).text((res.data && res.data.message) || 'Student added successfully.');
                // Remove student from dropdown so they can't be added twice
                $('#es-grp-add-student option[value="' + userId + '"]').remove();
                $('#es-grp-add-student').val('');
            } else {
                $('#es-grp-add-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not add student.');
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('Add to Group');
            $('#es-grp-add-msg').css({display:'block',background:'#fee2e2',color:'#b91c1c'}).text('Server error.');
        });
    });

    /* ── Course Select2 ── */
    if ($.fn.select2 && $('#es-group-course-select').length && !$('#es-group-course-select').prop('disabled')) {
        $('#es-group-course-select').select2({ placeholder: 'Search course…', width: '100%', allowClear: true });
    }
    $(document).on('click', '.es-group-courses-save', function(){
        var gid = $('#es-group-course-select').val();
        var ids = gid ? [gid] : [];
        var $msg = $('#es-group-course-msg').hide();
        $.post(ajaxurl, {
            action: 'es_admin_save_group_courses',
            nonce: (window.ES_ADMIN && ES_ADMIN.nonce) ? ES_ADMIN.nonce : '',
            group_id: <?php echo $detail_mode ? (int) $selected->id : 0; ?>,
            course_ids: ids
        }).done(function(res){
            $msg.css('color', res && res.success ? '#10b981' : '#ef4444')
                .text(res && res.success ? '✓ Courses saved' : ((res.data && res.data.message) || 'Could not save courses'))
                .show();
        });
    });
});
</script>
