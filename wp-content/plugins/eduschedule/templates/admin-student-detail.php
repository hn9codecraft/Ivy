<?php
/**
 * EduSchedule — Student Detail page  (tabbed UI — matches 1:1 / Group style)
 * URL: admin.php?page=eduschedule-students&view=detail&user_id=X
 *
 * Vars in scope:
 *   $student        = array of user info (incl. 'category')
 *   $stats          = array (total, upcoming, past)
 *   $bookings       = array of booking rows
 *   $latest_outcome = stdClass|null (most recent es_lead_packages row)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$back_url = admin_url( 'admin.php?page=eduschedule-students' );
$initial  = strtoupper( substr( $student['display_name'] ?: 'U', 0, 2 ) );

// Category labels
$cat_labels = array(
    'demo'  => array( 'label' => 'DEMO LEAD',     'class' => 'es-pill-warning' ),
    '1to1'  => array( 'label' => '1:1 STUDENT',   'class' => 'es-pill-info' ),
    'group' => array( 'label' => 'GROUP STUDENT', 'class' => 'es-pill-success' ),
    'lost'  => array( 'label' => 'NOT INTERESTED','class' => 'es-pill-danger' ),
);
$cat_info     = isset( $cat_labels[ $student['category'] ] ) ? $cat_labels[ $student['category'] ] : $cat_labels['demo'];
$is_converted = in_array( $student['category'], array( '1to1', 'group' ), true );

// Packages / groups
$all_packages = ES_Packages::get_all( true );
$all_groups   = ES_Packages::get_all_groups( true );

// Course posts (for the After Call Course dropdown) + the student's currently
// linked courses (so the picker pre-fills with what's already on file).
$ac_course_posts       = ES_Packages::get_course_posts();
$ac_selected_course_ids = ES_Packages::get_student_course_ids( $student['id'] );
$ac_selected_course_id  = ES_Packages::first_course_id( $ac_selected_course_ids );

// Staged packages (admin pre-selected to send to student)
$staged_ids = ES_Packages::get_staged_packages( $student['id'] );

// Packages the student already owns an active (still-valid) plan for — disabled
// in the after-call selector so the admin can't re-offer an active plan.
$owned_active_ids = ES_Packages::get_active_package_ids( $student['id'] );

// Latest outcome
$outcome_name = $latest_outcome ? $latest_outcome->outcome : '';
$outcome_pkg  = ( $latest_outcome && $latest_outcome->package_id ) ? ES_Packages::get( $latest_outcome->package_id ) : null;
$outcome_grp  = ( $latest_outcome && $latest_outcome->group_id )   ? ES_Packages::get_group( $latest_outcome->group_id ) : null;

// Active plan / usage figures
$sd_plan = ES_Packages::get_active_plan( $student['id'] );
$sd_pkg  = null;
if ( $sd_plan ) {
    $sd_pkg = ES_Packages::get( $sd_plan->package_id );
}
$sd_total = $sd_plan ? (int) ( $sd_plan->total_sessions ?? 0 ) : ( $sd_pkg ? (int) ( $sd_pkg->total_sessions ?? 0 ) : 0 );
$sd_used  = $sd_plan ? (int) ( $sd_plan->used_sessions ?? 0 )  : 0;
$sd_left  = max( 0, $sd_total - $sd_used );
$sd_pct   = $sd_total > 0 ? round( ( $sd_used / $sd_total ) * 100 ) : 0;
$sd_dur   = $sd_plan ? (int) ( $sd_plan->months ?? 1 ) : ( $sd_pkg ? (int) ( $sd_pkg->months ?? 1 ) : 0 );

// Payments (paid + pending) for the Payments tab
$sd_payments = ES_Packages::get_user_payments( $student['id'], false );
$sd_now      = current_time( 'timestamp' );
if ( ! empty( $sd_payments ) ) {
    $sd_active_paid_packages = array();
    foreach ( $sd_payments as $sd_pay_row ) {
        $sd_pay_status = strtolower( (string) ( $sd_pay_row->status ?? '' ) );
        $sd_pkg_id     = (int) ( $sd_pay_row->package_id ?? 0 );
        $sd_valid_ts   = ! empty( $sd_pay_row->valid_until ) ? strtotime( $sd_pay_row->valid_until ) : false;
        $sd_is_active  = ( $sd_pay_status === 'paid' ) && ( empty( $sd_pay_row->valid_until ) || $sd_valid_ts >= $sd_now );
        if ( $sd_is_active && $sd_pkg_id > 0 ) {
            $sd_active_paid_packages[ $sd_pkg_id ] = true;
        }
    }
    if ( ! empty( $sd_active_paid_packages ) ) {
        $sd_payments = array_values( array_filter( $sd_payments, function ( $sd_pay_row ) use ( $sd_active_paid_packages ) {
            $sd_pay_status = strtolower( (string) ( $sd_pay_row->status ?? '' ) );
            $sd_pkg_id     = (int) ( $sd_pay_row->package_id ?? 0 );
            if ( $sd_pay_status !== 'pending' ) {
                return true;
            }
            return $sd_pkg_id < 1 || empty( $sd_active_paid_packages[ $sd_pkg_id ] );
        } ) );
    }
}

// Additional comments per package — sourced from the admin's After-Call records
// (es_lead_packages.additional_comments). Keyed by package_id so each purchase
// can show the note that was attached when that package was offered/converted.
// We also keep the most recent overall comment as a fallback.
$sd_pkg_comments  = array();
$sd_last_comment  = '';
$sd_lead_rows = ES_Packages::get_lead_packages( $student['id'] ); // newest first
if ( ! empty( $sd_lead_rows ) ) {
    foreach ( $sd_lead_rows as $lr ) {
        $c = isset( $lr->additional_comments ) ? trim( (string) $lr->additional_comments ) : '';
        if ( $c === '' ) continue;
        if ( $sd_last_comment === '' ) $sd_last_comment = $c; // first (newest) non-empty
        $pid = (int) $lr->package_id;
        // Keep the newest comment per package (rows are ordered newest first).
        if ( $pid && ! isset( $sd_pkg_comments[ $pid ] ) ) {
            $sd_pkg_comments[ $pid ] = $c;
        }
    }
}

// Platforms for the schedule form
$sd_plat = ES_Helpers::platforms();

// Build the staged-package public share link (if any)
$share_link = '';
if ( ! empty( $staged_ids ) ) {
    $token = get_user_meta( $student['id'], ES_Packages::META_TOKEN, true );
    $pkg_page_id = 0;
    foreach ( get_pages() as $pg ) {
        if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) { $pkg_page_id = $pg->ID; break; }
    }
    $base_url   = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/packages/' );
    $share_link = add_query_arg( array( 'user_id' => $student['id'], 'token' => $token ), $base_url );
}

// Gravity Forms submissions (Overview tab)
$gf_data = array();
if ( class_exists( 'GFAPI' ) ) {
    $forms = GFAPI::get_forms();
    if ( ! empty( $forms ) ) {
        foreach ( $forms as $form ) {
            $entries = GFAPI::get_entries( $form['id'], array(
                'field_filters' => array( array( 'key' => 'created_by', 'value' => $student['id'] ) ),
            ) );
            if ( is_wp_error( $entries ) || empty( $entries ) ) continue;
            foreach ( $entries as $entry ) {
                foreach ( $form['fields'] as $field ) {
                    $value = rgar( $entry, (string) $field->id );
                    if ( empty( $value ) ) continue;
                    if ( ! isset( $gf_data[ $field->label ] ) ) $gf_data[ $field->label ] = $value;
                }
            }
        }
    }
}
?>
<div class="es-admin es-student-detail-page es-1to1-page">

    <div class="es-page-head">
        <div>
            <p class="es-page-sub" style="margin-bottom:6px">
                <a href="<?php echo esc_url( $back_url ); ?>" class="es-back-link">← Back to Students</a>
            </p>
            <h1>Student Detail</h1>
            <p class="es-page-sub"><?php echo esc_html( $student['email'] ); ?></p>
        </div>
        <div class="es-page-actions">
            <a href="mailto:<?php echo esc_attr( $student['email'] ); ?>" class="es-btn es-btn-ghost">
                <span class="dashicons dashicons-email"></span> Email
            </a>
        </div>
    </div>

    <div class="es-tabui-detail" data-target-type="1to1" data-target-id="<?php echo (int) $student['id']; ?>">

        <!-- Header -->
        <div class="es-tabui-header">
            <div class="es-tabui-header-avatar" style="background:rgba(99,102,241,0.15);color:#6366f1;"><?php echo esc_html( $initial ); ?></div>
            <div class="es-tabui-header-info">
                <h2 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <?php echo esc_html( $student['display_name'] ); ?>
                    <span class="es-pill <?php echo esc_attr( $cat_info['class'] ); ?>"><?php echo esc_html( $cat_info['label'] ); ?></span>
                    <?php if ( ! empty( $student['is_admin'] ) ) : ?>
                        <span class="es-pill es-pill-info">ADMIN</span>
                    <?php endif; ?>
                </h2>
                <div class="es-tabui-header-meta">
                    <span><?php echo esc_html( $student['email'] ); ?></span>
                    <?php if ( ! empty( $student['phone'] ) ) : ?><span><?php echo esc_html( $student['phone'] ); ?></span><?php endif; ?>
                    <span><?php echo (int) $sd_left; ?> sessions left</span>
                </div>
            </div>
        </div>

        <!-- Tab bar -->
        <div class="es-tabbar">
            <button type="button" class="es-tab is-active" data-tab="overview">Overview</button>
            <button type="button" class="es-tab" data-tab="aftercall">After Call</button>
            <button type="button" class="es-tab" data-tab="pkg">Package</button>
            <button type="button" class="es-tab" data-tab="payments">Payments</button>
            <button type="button" class="es-tab" data-tab="bookings">Bookings</button>
        </div>

        <div class="es-tab-body">

            <!-- ============ OVERVIEW ============ -->
            <div class="es-tabpane" data-pane="overview">

                <?php if ( $latest_outcome ) : ?>
                    <div class="es-usage-panel" style="margin-bottom:18px;">
                        <div class="es-section-label" style="color:#6366f1;margin-bottom:10px;">
                            <span class="dashicons dashicons-yes-alt" style="vertical-align:-3px"></span> After Call Result
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                            <span class="es-pill es-pill-info"><?php echo esc_html( $outcome_name ); ?></span>
                            <?php if ( $outcome_pkg ) : ?><span class="es-pill es-pill-success">📦 <?php echo esc_html( $outcome_pkg->package_name ); ?></span><?php endif; ?>
                            <?php if ( $outcome_grp ) : ?><span class="es-pill es-pill-info">👥 <?php echo esc_html( $outcome_grp->group_name ); ?></span><?php endif; ?>
                            <span class="es-cell-sub" style="font-size:12px;">on <?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $latest_outcome->selected_at ) ) ); ?></span>
                        </div>
                        <?php if ( ! empty( $latest_outcome->additional_comments ) ) : ?>
                            <div style="margin-top:10px;font-size:13px;color:var(--es-text-soft);line-height:1.5;"><?php echo nl2br( esc_html( $latest_outcome->additional_comments ) ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="es-section-label">Session Stats</div>
                <div class="es-usage-stats" style="margin-bottom:20px;">
                    <div><div class="es-usage-stat-val"><?php echo (int) $stats['total']; ?></div><div class="es-usage-stat-label">Total</div></div>
                    <div><div class="es-usage-stat-val"><?php echo (int) $stats['upcoming']; ?></div><div class="es-usage-stat-label">Upcoming</div></div>
                    <div><div class="es-usage-stat-val is-left"><?php echo (int) $stats['past']; ?></div><div class="es-usage-stat-label">Past</div></div>
                </div>

                <div class="es-section-label">Contact & Details</div>
                <div class="es-card" style="padding:16px 18px;margin-bottom:18px;">
                    <?php $detail_parent = ! empty( $student['parent_name'] ) ? $student['parent_name'] : ( $gf_data['Parent Name'] ?? '' ); ?>
                    <?php $detail_source = ! empty( $student['reference'] ) ? ucfirst( $student['reference'] ) : ( $gf_data['Source'] ?? '' ); ?>
                    <?php $detail_goal   = $gf_data['What is your primary goal for your scholar today?'] ?? ''; ?>
                    <?php $detail_level  = $gf_data["What is the student's current academic level?"] ?? ''; ?>
                    <div class="es-detail-row"><span>Email</span><div style="text-align:right;"><a href="mailto:<?php echo esc_attr( $student['email'] ); ?>"><?php echo esc_html( $student['email'] ); ?></a></div></div>
                    <div class="es-detail-row"><span>Phone</span><div style="text-align:right;"><?php echo ! empty( $student['phone'] ) ? esc_html( $student['phone'] ) : '<span style="color:#9ca3af;">—</span>'; ?></div></div>
                    <?php if ( $detail_parent !== '' ) : ?><div class="es-detail-row"><span>Parent</span><div style="text-align:right;"><?php echo esc_html( $detail_parent ); ?></div></div><?php endif; ?>
                    <?php if ( $detail_source !== '' ) : ?><div class="es-detail-row"><span>Source</span><div style="text-align:right;"><?php echo esc_html( $detail_source ); ?></div></div><?php endif; ?>
                    <?php if ( $detail_goal !== '' ) : ?><div class="es-detail-row"><span>Goal</span><div style="text-align:right;"><?php echo esc_html( $detail_goal ); ?></div></div><?php endif; ?>
                    <?php if ( $detail_level !== '' ) : ?><div class="es-detail-row"><span>Level / Band</span><div style="text-align:right;"><?php echo esc_html( $detail_level ); ?></div></div><?php endif; ?>
                    <?php if ( ! empty( $student['comment'] ) ) : ?><div class="es-detail-row"><span>Comment</span><div style="text-align:right;"><?php echo esc_html( $student['comment'] ); ?></div></div><?php endif; ?>
                    <?php if ( ! empty( $student['country'] ) ) : ?><div class="es-detail-row"><span>Country</span><div style="text-align:right;"><?php echo esc_html( $student['country'] ); ?></div></div><?php endif; ?>
                    <?php if ( ! empty( $student['timezone'] ) ) : ?><div class="es-detail-row"><span>Timezone</span><div style="text-align:right;"><?php echo esc_html( $student['timezone'] ); ?></div></div><?php endif; ?>
                    <div class="es-detail-row"><span>Joined</span><div style="text-align:right;"><?php echo esc_html( $student['registered_label'] ); ?></div></div>
                </div>

                <?php if ( ! empty( $gf_data ) ) : ?>
                    <div class="es-section-label">Form Submissions</div>
                    <div class="es-card" style="padding:16px 18px;">
                        <?php foreach ( $gf_data as $label => $value ) : ?>
                            <div class="es-detail-row"><span><?php echo esc_html( $label ); ?></span><div style="text-align:right;"><?php echo esc_html( $value ); ?></div></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============ AFTER CALL ============ -->
            <div class="es-tabpane" data-pane="aftercall" style="display:none;">

                <?php if ( $share_link ) : ?>
                    <div class="es-usage-panel" style="margin-bottom:18px;">
                        <div class="es-section-label" style="color:#fbbf24;margin-bottom:10px;">
                            <span class="dashicons dashicons-share" style="vertical-align:-3px"></span> Package Selection Link (sent to student)
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            <input type="text" readonly id="es-share-link" value="<?php echo esc_attr( $share_link ); ?>"
                                   style="flex:1;min-width:280px;padding:8px 12px;background:var(--es-bg-input);border:1px solid var(--es-border);border-radius:6px;color:var(--es-text);font-size:12px;font-family:monospace;" />
                            <button type="button" class="es-btn es-btn-primary es-btn-sm" id="es-copy-link">
                                <span class="dashicons dashicons-clipboard"></span> Copy
                            </button>
                        </div>
                        <div style="margin-top:8px;font-size:12px;color:var(--es-text-muted);">
                            Staged packages:
                            <?php
                            $names = array();
                            foreach ( $staged_ids as $sid ) { $p = ES_Packages::get( $sid ); if ( $p ) $names[] = $p->package_name; }
                            echo esc_html( implode( ', ', $names ) );
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="es-after-call-section" id="es-after-call-section" data-user-id="<?php echo (int) $student['id']; ?>">
                    <div class="es-section-label" style="color:#6366f1;">
                        <span class="dashicons dashicons-phone" style="vertical-align:-3px"></span> After Call — Convert Lead
                    </div>
                    <p class="es-helper" style="margin:0 0 14px;color:var(--es-text-muted);">
                        Lead: <strong><?php echo esc_html( $student['display_name'] ); ?></strong> · <?php echo esc_html( $student['email'] ); ?>
                    </p>

                    <div class="es-modal-row">
                        <div class="es-field">
                            <label class="es-label">Outcome</label>
                            <select id="es-after-call-outcome">
                                <option value="1:1 Student" <?php selected( $outcome_name, '1:1 Student' ); ?>>1:1 Student</option>
                                <option value="Group Student" <?php selected( $outcome_name, 'Group Student' ); ?>>Group Student</option>
                                <option value="Follow-up Needed" <?php selected( $outcome_name, 'Follow-up Needed' ); ?>>Follow-up Needed</option>
                                <option value="Not Interested" <?php selected( $outcome_name, 'Not Interested' ); ?>>Not Interested</option>
                            </select>
                        </div>
                        <div class="es-field" id="es-group-field" style="display:none">
                            <label class="es-label">Assign to Group</label>
                            <select id="es-after-call-group">
                                <option value="">Select group...</option>
                                <?php foreach ( $all_groups as $g ) : ?>
                                    <option value="<?php echo (int) $g->id; ?>"><?php echo esc_html( $g->group_name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div id="es-group-package-note" class="es-alert es-alert-info" style="display:none;margin:0 0 14px;padding:10px 12px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;color:#3730a3;font-size:12.5px;line-height:1.5;">
                        Group flow uses the package and course already assigned on the selected group. No 1:1 package/course will be changed.
                    </div>

                    <div class="es-field" id="es-course-after-call-field">
                        <label class="es-label">Course <small style="font-weight:400;color:var(--es-text-muted);">(included in confirmation emails as subject + body)</small></label>
                        <?php if ( empty( $ac_course_posts ) ) : ?>
                            <div style="font-size:12px;color:var(--es-text-muted);padding:8px 10px;background:var(--es-bg-input);border:1px solid var(--es-border);border-radius:8px;">
                                No posts found for the <code>course</code> post type. Create one to enable this.
                            </div>
                        <?php else : ?>
                            <select id="es-after-call-course" class="es-course-select" style="width:100%;">
                                <option value="">Select course...</option>
                                <?php foreach ( $ac_course_posts as $cp ) : ?>
                                    <option value="<?php echo (int) $cp->ID; ?>" <?php selected( (int) $cp->ID, $ac_selected_course_id ); ?>>
                                        <?php echo esc_html( get_the_title( $cp ) ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="es-helper" style="margin-top:6px;display:block;color:var(--es-text-muted);">Pick one course — the course name appears in the After-Call email subject/body and is saved on the student.</small>
                        <?php endif; ?>
                    </div>

                    <div class="es-field" id="es-package-field">
                        <label class="es-label">Package <small style="font-weight:400;color:var(--es-text-muted);">(student will choose from these via link)</small></label>
                        <div id="es-package-checkboxes" style="display:flex;flex-direction:column;gap:8px;padding:10px;background:var(--es-bg-input);border:1px solid var(--es-border);border-radius:8px;max-height:220px;overflow-y:auto;">
                            <?php if ( empty( $all_packages ) ) : ?>
                                <div style="font-size:12px;color:var(--es-text-muted);">No packages available. <a href="<?php echo esc_url( admin_url( 'admin.php?page=eduschedule-packages' ) ); ?>">Create one</a>.</div>
                            <?php else : foreach ( $all_packages as $pkg ) :
                                $cur = ! empty( $pkg->currency ) ? $pkg->currency : 'INR';
                                $pkg_owned = in_array( (int) $pkg->id, $owned_active_ids, true );
                                $pkg_type  = ! empty( $pkg->package_type ) ? $pkg->package_type : '1to1';
                                $pkg_type_label = array( '1to1' => '1:1', 'group' => 'Group', 'consultancy' => 'Consultancy' )[ $pkg_type ] ?? $pkg_type;
                            ?>
                                <label class="es-pkg-check-row" data-pkg-type="<?php echo esc_attr( $pkg_type ); ?>"
                                       style="display:flex;align-items:center;gap:8px;cursor:<?php echo $pkg_owned ? 'not-allowed' : 'pointer'; ?>;font-size:13px;<?php echo $pkg_owned ? 'opacity:0.55;' : ''; ?>"
                                       <?php if ( $pkg_owned ) : ?>title="Student already has this plan active"<?php endif; ?>>
                                    <input type="checkbox" class="es-pkg-check" value="<?php echo (int) $pkg->id; ?>"
                                           data-pkg-type="<?php echo esc_attr( $pkg_type ); ?>"
                                           <?php checked( ! $pkg_owned && in_array( (int) $pkg->id, $staged_ids, true ) ); ?>
                                           <?php disabled( $pkg_owned ); ?> />
                                    <span>
                                        <strong><?php echo esc_html( $pkg->package_name ); ?></strong>
                                        <span style="font-size:10px;padding:1px 5px;border-radius:4px;background:<?php echo $pkg_type === 'group' ? '#dbeafe' : '#f3e8ff'; ?>;color:<?php echo $pkg_type === 'group' ? '#1d4ed8' : '#7c3aed'; ?>;margin-left:4px;"><?php echo esc_html( $pkg_type_label ); ?></span>
                                        <?php if ( $pkg->price > 0 ) : ?><span style="opacity:0.7"> — <?php echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) ); ?></span><?php endif; ?>
                                        <?php if ( $pkg_owned ) : ?><span class="es-pill es-pill-success" style="margin-left:6px;font-size:10px;vertical-align:middle;">ACTIVE</span><?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; endif; ?>
                        </div>
                        <small class="es-helper" style="margin-top:6px;display:block;color:var(--es-text-muted);">Tip: select up to 3 packages — the student will see only these on the package selection page.</small>
                    </div>

                    <div class="es-field">
                        <label class="es-label">Additional Comments</label>
                        <textarea id="es-after-call-comments" rows="3" placeholder="Notes from the call, next steps..."><?php echo esc_textarea( $latest_outcome ? $latest_outcome->additional_comments : '' ); ?></textarea>
                    </div>

                    <div class="es-alert es-alert-info" style="margin-top:6px;padding:10px 12px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);border-radius:6px;color:#d99e1a;font-size:12px;">
                        📧 On submit: confirmation email with package selection link will be sent to the student and your admin email.
                    </div>

                    <div style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end;">
                        <button type="button" class="es-btn es-btn-primary" id="es-after-call-submit">
                            <span class="dashicons dashicons-yes" style="font-size:16px"></span> Submit & Convert
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============ PACKAGE ============ -->
            <div class="es-tabpane" data-pane="pkg" style="display:none;">
                <div class="es-usage-panel">
                    <div class="es-usage-title"><span class="dashicons dashicons-archive"></span><?php echo $sd_pkg ? esc_html( $sd_pkg->package_name ) : 'No Package Assigned'; ?></div>
                    <div class="es-usage-stats">
                        <div><div class="es-usage-stat-val"><?php echo (int) $sd_total; ?></div><div class="es-usage-stat-label">Total</div></div>
                        <div><div class="es-usage-stat-val"><?php echo (int) $sd_used; ?></div><div class="es-usage-stat-label">Used</div></div>
                        <div><div class="es-usage-stat-val is-left"><?php echo (int) $sd_left; ?></div><div class="es-usage-stat-label">Left</div></div>
                    </div>
                    <div class="es-usage-bar"><div class="es-usage-bar-fill" style="width:<?php echo (int) $sd_pct; ?>%;"></div></div>
                    <div class="es-usage-foot"><span><?php echo (int) $sd_pct; ?>% used</span><span><?php echo $sd_dur > 0 ? ( (int) $sd_dur . ' month' . ( $sd_dur > 1 ? 's' : '' ) . ' program' ) : ''; ?></span></div>
                </div>

                <?php if ( $sd_pkg ) : ?>
                    <div class="es-card es-package-detail-card" style="padding:18px 20px;margin-bottom:14px;">
                        <div class="es-section-label">Selected Package Details</div>
                        <div class="es-package-detail-grid">
                            <div><span>Package</span><strong><?php echo esc_html( $sd_pkg->package_name ); ?></strong></div>
                            <?php if ( ! empty( $sd_pkg->sub_heading ) ) : ?><div><span>Sub Heading</span><strong><?php echo esc_html( $sd_pkg->sub_heading ); ?></strong></div><?php endif; ?>
                            <div><span>Duration</span><strong><?php echo (int) $sd_dur; ?> month<?php echo $sd_dur > 1 ? 's' : ''; ?></strong></div>
                            <div><span>Monthly Sessions</span><strong><?php echo $sd_plan ? (int) $sd_plan->monthly_session_limit : (int) ( $sd_pkg->monthly_session_limit ?? 0 ); ?></strong></div>
                            <div><span>Total Sessions</span><strong><?php echo (int) $sd_total; ?></strong></div>
                            <div><span>Used Sessions</span><strong><?php echo (int) $sd_used; ?></strong></div>
                            <div><span>Remaining Sessions</span><strong><?php echo (int) $sd_left; ?></strong></div>
                            <?php if ( $sd_plan && ! empty( $sd_plan->valid_until ) ) : ?><div><span>Valid Until</span><strong><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $sd_plan->valid_until ) ) ); ?></strong></div><?php endif; ?>
                            <?php if ( ! empty( $sd_pkg->discount_percent ) && ! empty( $sd_pkg->discount_months ) ) : ?><div><span>Package Discount</span><strong><?php echo esc_html( rtrim( rtrim( number_format( (float) $sd_pkg->discount_percent, 1 ), '0' ), '.' ) ); ?>% for <?php echo (int) $sd_pkg->discount_months; ?> months</strong></div><?php endif; ?>
                        </div>
                        <?php if ( ! empty( $sd_pkg->description ) ) : ?>
                            <div class="es-package-desc"><?php echo nl2br( esc_html( $sd_pkg->description ) ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============ PAYMENTS ============ -->
            <div class="es-tabpane" data-pane="payments" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                    <div class="es-section-label" style="margin:0;">Payments</div>
                    <?php if ( ! empty( $sd_payments ) ) : ?>
                        <span class="es-cell-sub" style="font-size:12px;"><?php echo count( $sd_payments ); ?> record<?php echo count( $sd_payments ) > 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                </div>

                <?php if ( $sd_pkg ) : ?>
                    <div class="es-card es-package-detail-card" style="padding:18px 20px;margin-bottom:14px;">
                        <div class="es-section-label">Selected Package Details</div>
                        <div class="es-package-detail-grid">
                            <div><span>Package</span><strong><?php echo esc_html( $sd_pkg->package_name ); ?></strong></div>
                            <?php if ( ! empty( $sd_pkg->sub_heading ) ) : ?><div><span>Sub Heading</span><strong><?php echo esc_html( $sd_pkg->sub_heading ); ?></strong></div><?php endif; ?>
                            <div><span>Duration</span><strong><?php echo (int) $sd_dur; ?> month<?php echo $sd_dur > 1 ? 's' : ''; ?></strong></div>
                            <div><span>Monthly Sessions</span><strong><?php echo $sd_plan ? (int) $sd_plan->monthly_session_limit : (int) ( $sd_pkg->monthly_session_limit ?? 0 ); ?></strong></div>
                            <div><span>Total Sessions</span><strong><?php echo (int) $sd_total; ?></strong></div>
                            <div><span>Used Sessions</span><strong><?php echo (int) $sd_used; ?></strong></div>
                            <div><span>Remaining Sessions</span><strong><?php echo (int) $sd_left; ?></strong></div>
                            <?php if ( $sd_plan && ! empty( $sd_plan->valid_until ) ) : ?><div><span>Valid Until</span><strong><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $sd_plan->valid_until ) ) ); ?></strong></div><?php endif; ?>
                        </div>
                        <?php if ( ! empty( $sd_pkg->description ) ) : ?>
                            <div class="es-package-desc"><?php echo nl2br( esc_html( $sd_pkg->description ) ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( empty( $sd_payments ) ) : ?>
                    <p class="es-empty-cell">No payments yet.</p>
                <?php else : foreach ( $sd_payments as $pay ) :
                    $pay_cur    = ! empty( $pay->currency ) ? $pay->currency : 'INR';
                    $pay_name   = $pay->package_name ? $pay->package_name : ( 'Package #' . (int) $pay->package_id );
                    $pay_amount = ES_Helpers::format_price( $pay->amount, $pay_cur );
                    $pay_cycle  = $pay->billing_cycle ? ucfirst( $pay->billing_cycle ) : '';
                    $pay_date   = $pay->created_at ? date_i18n( 'M j, Y', strtotime( $pay->created_at ) ) : '';
                    $pay_status = strtolower( (string) $pay->status );
                    $is_active  = ( $pay_status === 'paid' ) && ( empty( $pay->valid_until ) || strtotime( $pay->valid_until ) >= $sd_now );
                    if ( $pay_status === 'paid' ) {
                        $status_class = $is_active ? 'es-pill-success' : 'es-pill-info';
                        $status_text  = $is_active ? 'ACTIVE' : 'EXPIRED';
                    } elseif ( $pay_status === 'pending' ) {
                        $status_class = 'es-pill-warning'; $status_text = 'UNPAID';
                    } else {
                        $status_class = 'es-pill-danger'; $status_text = strtoupper( $pay_status ?: 'FAILED' );
                    }
                ?>
                    <div class="es-sess-item" style="margin-bottom:8px;flex-wrap:wrap;">
                        <div style="flex:1;min-width:160px;">
                            <div class="es-sess-desc" style="font-weight:600;"><?php echo esc_html( $pay_name ); ?></div>
                            <div class="es-sess-time">
                                <?php echo esc_html( $pay_date ); ?>
                                <?php if ( $pay_cycle ) : ?> · <?php echo esc_html( $pay_cycle ); ?><?php endif; ?>
                                <?php if ( ! empty( $pay->valid_until ) ) : ?> · until <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $pay->valid_until ) ) ); ?><?php endif; ?>
                            </div>
                            <div class="es-cell-sub" style="margin-top:4px;">
                                Total: <?php echo (int) ( $pay->total_sessions ?? 0 ); ?> ·
                                Used: <?php echo (int) ( $pay->used_sessions ?? 0 ); ?> ·
                                Left: <?php echo (int) ES_Packages::remaining_sessions( $pay ); ?>
                                <?php if ( ! empty( $pay->months ) ) : ?> · Duration: <?php echo (int) $pay->months; ?> month<?php echo (int) $pay->months > 1 ? 's' : ''; ?><?php endif; ?>
                            </div>
                            <?php
                            // Additional comment for this purchase: match by package, else
                            // fall back to the most recent after-call comment on file.
                            $pay_pid     = (int) $pay->package_id;
                            $pay_comment = '';
                            if ( $pay_pid && isset( $sd_pkg_comments[ $pay_pid ] ) ) {
                                $pay_comment = $sd_pkg_comments[ $pay_pid ];
                            } elseif ( $sd_last_comment !== '' ) {
                                $pay_comment = $sd_last_comment;
                            }
                            if ( $pay_comment !== '' ) :
                            ?>
                                <div class="es-pay-comment" style="margin-top:8px;padding:8px 10px;background:var(--es-bg-input);border-left:3px solid var(--es-indigo);border-radius:6px;font-size:12.5px;line-height:1.5;color:var(--es-text-soft);">
                                    <span style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;opacity:.7;margin-bottom:3px;">Additional Comment</span>
                                    <?php echo nl2br( esc_html( $pay_comment ) ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="font-weight:700;color:var(--es-indigo);font-size:15px;white-space:nowrap;margin-right:10px;"><?php echo esc_html( $pay_amount ); ?></div>
                        <span class="es-pill <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_text ); ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- ============ BOOKINGS ============ -->
            <div class="es-tabpane" data-pane="bookings" style="display:none;">
                <div class="es-section-label">Booking History</div>
                <?php if ( empty( $bookings ) ) : ?>
                    <p class="es-empty-cell">No bookings yet.</p>
                <?php else : foreach ( $bookings as $b ) :
                    $status_class = ( $b['status'] === 'confirmed' ) ? 'es-pill-success' : ( ( $b['status'] === 'pending' ) ? 'es-pill-warning' : 'es-pill-danger' );
                ?>
                    <div class="es-sess-item" style="margin-bottom:8px;">
                        <div style="flex:1;min-width:160px;">
                            <div class="es-sess-desc" style="font-weight:600;"><?php echo esc_html( $b['title'] ? $b['title'] : ( $b['type_label'] ? $b['type_label'] : 'Booking' ) ); ?></div>
                            <div class="es-sess-time">
                                <?php echo esc_html( $b['date_label'] ); ?> · <?php echo esc_html( $b['start'] ); ?>–<?php echo esc_html( $b['end'] ); ?>
                                (<?php echo (int) $b['duration']; ?> min) · <?php echo esc_html( $b['platform'] ? $b['platform'] : '—' ); ?>
                            </div>
                        </div>
                        <span class="es-pill <?php echo esc_attr( $status_class ); ?>" style="margin-right:10px;"><?php echo esc_html( strtoupper( $b['status'] ) ); ?></span>
                        <?php if ( ! empty( $b['zoom_join_url'] ) ) : ?>
                            <a class="es-btn es-btn-primary es-btn-sm" target="_blank" rel="noopener" href="<?php echo esc_url( $b['zoom_join_url'] ); ?>"><span class="dashicons dashicons-video-alt2"></span> Join</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>

        </div>
    </div>
</div>
