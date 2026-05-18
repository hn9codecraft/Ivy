<?php
/**
 * EduSchedule — Student Detail page
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
$initial  = strtoupper( substr( $student['display_name'] ?: 'U', 0, 1 ) );
$short    = strtolower( substr( $student['display_name'] ?: 'usr', 0, 3 ) );

// Category labels
$cat_labels = array(
    'demo'  => array( 'label' => 'DEMO LEAD',    'class' => 'es-pill-warning' ),
    '1to1'  => array( 'label' => '1:1 STUDENT',  'class' => 'es-pill-info' ),
    'group' => array( 'label' => 'GROUP STUDENT','class' => 'es-pill-success' ),
    'lost'  => array( 'label' => 'NOT INTERESTED','class' => 'es-pill-danger' ),
);
$cat_info = isset( $cat_labels[ $student['category'] ] ) ? $cat_labels[ $student['category'] ] : $cat_labels['demo'];

// Already converted? Show different action label
$is_converted = in_array( $student['category'], array( '1to1', 'group' ), true );

// All active packages for dropdown
$all_packages = ES_Packages::get_all( true );
$all_groups   = ES_Packages::get_all_groups( true );

// Currently staged packages (admin pre-selected to send to student)
$staged_ids = ES_Packages::get_staged_packages( $student['id'] );

// Latest outcome details
$outcome_name = $latest_outcome ? $latest_outcome->outcome : '';
$outcome_pkg  = ( $latest_outcome && $latest_outcome->package_id ) ? ES_Packages::get( $latest_outcome->package_id ) : null;
$outcome_grp  = ( $latest_outcome && $latest_outcome->group_id )   ? ES_Packages::get_group( $latest_outcome->group_id ) : null;
?>
<div class="es-admin es-student-detail-page">

    <div class="es-page-head">
        <div>
            <p class="es-page-sub" style="margin-bottom:6px">
                <a href="<?php echo esc_url( $back_url ); ?>" class="es-back-link">&larr; Back to Students</a>
            </p>
            <h1 style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <?php echo esc_html( $student['display_name'] ); ?>
                <span class="es-pill <?php echo esc_attr( $cat_info['class'] ); ?>"><?php echo esc_html( $cat_info['label'] ); ?></span>
                <?php if ( ! empty( $student['is_admin'] ) ) : ?>
                    <span class="es-pill es-pill-info">ADMIN</span>
                <?php endif; ?>
            </h1>
            <p class="es-page-sub"><?php echo esc_html( $student['email'] ); ?></p>
        </div>
        <div class="es-page-actions">
            <a href="<?php echo esc_url( $back_url ); ?>" class="es-btn es-btn-ghost">
                <span class="dashicons dashicons-arrow-left-alt"></span> Back
            </a>
            <a href="mailto:<?php echo esc_attr( $student['email'] ); ?>" class="es-btn es-btn-ghost">
                <span class="dashicons dashicons-email"></span> Email
            </a>
        </div>
    </div>

    <?php if ( $latest_outcome ) : ?>
        <div class="es-card" style="padding:16px 20px;margin-bottom:16px;border-left:4px solid #6366f1;">
            <div style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px;color:#6366f1;margin-bottom:8px;">
                <span class="dashicons dashicons-yes-alt"></span> After Call Result
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <span class="es-pill es-pill-info"><?php echo esc_html( $outcome_name ); ?></span>
                <?php if ( $outcome_pkg ) : ?>
                    <span class="es-pill es-pill-success">📦 <?php echo esc_html( $outcome_pkg->package_name ); ?></span>
                <?php endif; ?>
                <?php if ( $outcome_grp ) : ?>
                    <span class="es-pill es-pill-info">👥 <?php echo esc_html( $outcome_grp->group_name ); ?></span>
                <?php endif; ?>
                <span class="es-cell-sub" style="font-size:12px;">
                    on <?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $latest_outcome->selected_at ) ) ); ?>
                </span>
            </div>
            <?php if ( ! empty( $latest_outcome->additional_comments ) ) : ?>
                <div style="margin-top:10px;font-size:13px;color:rgba(255,255,255,0.75);line-height:1.5;">
                    <?php echo nl2br( esc_html( $latest_outcome->additional_comments ) ); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $staged_ids ) ) : ?>
        <?php
        // Build the public shortcode link
        $token = get_user_meta( $student['id'], ES_Packages::META_TOKEN, true );
        $pkg_page_id = 0;
        $settings = ES_Helpers::settings();
        // Try to find page with [eduschedule_packages] shortcode
        $pages_q = get_pages();
        foreach ( $pages_q as $pg ) {
            if ( has_shortcode( $pg->post_content, 'eduschedule_packages' ) ) {
                $pkg_page_id = $pg->ID;
                break;
            }
        }
        $base_url = $pkg_page_id ? get_permalink( $pkg_page_id ) : home_url( '/packages/' );
        $share_link = add_query_arg( array(
            'user_id' => $student['id'],
            'token'   => $token,
        ), $base_url );
        ?>
        <div class="es-card" style="padding:16px 20px;margin-bottom:16px;border-left:4px solid #fbbf24;">
            <div style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13px;color:#fbbf24;margin-bottom:8px;">
                <span class="dashicons dashicons-share"></span> Package Selection Link (sent to student)
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="text" readonly id="es-share-link" value="<?php echo esc_attr( $share_link ); ?>"
                       style="flex:1;min-width:300px;padding:8px 12px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:6px;color:#fff;font-size:12px;font-family:monospace;" />
                <button type="button" class="es-btn es-btn-ghost es-btn-sm" id="es-copy-link">
                    <span class="dashicons dashicons-clipboard"></span> Copy
                </button>
            </div>
            <div style="margin-top:8px;font-size:12px;color:rgba(255,255,255,0.6);">
                Staged packages:
                <?php
                $names = array();
                foreach ( $staged_ids as $sid ) {
                    $p = ES_Packages::get( $sid );
                    if ( $p ) $names[] = $p->package_name;
                }
                echo esc_html( implode( ', ', $names ) );
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ============ After Call — Always-Visible Inline Form ============ -->
    <div class="es-card es-after-call-section" id="es-after-call-section"
         data-user-id="<?php echo (int) $student['id']; ?>"
         style="padding:20px 24px;margin-bottom:16px;border-left:4px solid #6366f1">
        <div class="es-after-call-head" style="margin-bottom:16px">
            <h3 style="margin:0;font-size:16px;display:flex;align-items:center;gap:8px;">
                <span class="dashicons dashicons-phone" style="color:#6366f1"></span>
                After Call — Convert Lead
            </h3>
            <p class="es-page-sub" style="margin:4px 0 0;font-size:12px;">
                Lead: <strong><?php echo esc_html( $student['display_name'] ); ?></strong> · <?php echo esc_html( $student['email'] ); ?>
            </p>
        </div>

        <div class="es-after-call-body">
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

            <div class="es-field" id="es-package-field">
                <label class="es-label">Package <small style="font-weight:400;opacity:0.6">(student will choose from these via link)</small></label>
                <div id="es-package-checkboxes" style="display:flex;flex-direction:column;gap:8px;padding:10px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.08);border-radius:8px;max-height:220px;overflow-y:auto;">
                    <?php if ( empty( $all_packages ) ) : ?>
                        <div style="font-size:12px;color:rgba(255,255,255,0.6);">No packages available. <a href="<?php echo esc_url( admin_url( 'admin.php?page=eduschedule-packages' ) ); ?>">Create one</a>.</div>
                    <?php else : foreach ( $all_packages as $pkg ) :
                        $cur = ! empty( $pkg->currency ) ? $pkg->currency : 'INR';
                    ?>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                            <input type="checkbox" class="es-pkg-check" value="<?php echo (int) $pkg->id; ?>"
                                   <?php checked( in_array( (int) $pkg->id, $staged_ids, true ) ); ?> />
                            <span>
                                <strong><?php echo esc_html( $pkg->package_name ); ?></strong>
                                <?php if ( $pkg->price > 0 ) : ?>
                                    <span style="opacity:0.7"> — <?php echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) ); ?></span>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; endif; ?>
                </div>
                <small class="es-field-hint" style="margin-top:6px;display:block;">
                    Tip: select up to 3 packages — the student will see only these on the package selection page.
                </small>
            </div>

            <div class="es-field">
                <label class="es-label">Additional Comments</label>
                <textarea id="es-after-call-comments" rows="3" placeholder="Notes from the call, next steps..."><?php
                    echo esc_textarea( $latest_outcome ? $latest_outcome->additional_comments : '' );
                ?></textarea>
            </div>

            <div class="es-alert es-alert-info" style="margin-top:6px;padding:10px 12px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);border-radius:6px;color:#fbbf24;font-size:12px;">
                📧 On submit: confirmation email with package selection link will be sent to the student and your admin email.
            </div>

            <div style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="es-btn" id="es-after-call-submit">
                    <span class="dashicons dashicons-yes" style="font-size:16px"></span>
                    Submit &amp; Convert
                </button>
            </div>
        </div>
    </div>

    <div class="es-card" style="padding:20px 24px">
        <div class="es-sd-grid">
            <div class="es-sd-label">Name</div>
            <div class="es-sd-value"><?php echo esc_html( $student['display_name'] ); ?></div>

            <div class="es-sd-label">Email</div>
            <div class="es-sd-value"><a href="mailto:<?php echo esc_attr( $student['email'] ); ?>"><?php echo esc_html( $student['email'] ); ?></a></div>

            <?php if ( ! empty( $student['phone'] ) ) : ?>
                <div class="es-sd-label">Phone</div>
                <div class="es-sd-value"><?php echo esc_html( $student['phone'] ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $student['parent_name'] ) ) : ?>
                <div class="es-sd-label">Parent</div>
                <div class="es-sd-value"><?php echo esc_html( $student['parent_name'] ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $student['reference'] ) ) : ?>
                <div class="es-sd-label">Reference</div>
                <div class="es-sd-value"><?php echo esc_html( ucfirst( $student['reference'] ) ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $student['comment'] ) ) : ?>
                <div class="es-sd-label">Comment</div>
                <div class="es-sd-value"><?php echo esc_html( $student['comment'] ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $student['country'] ) ) : ?>
                <div class="es-sd-label">Country</div>
                <div class="es-sd-value"><?php echo esc_html( $student['country'] ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $student['timezone'] ) ) : ?>
                <div class="es-sd-label">Timezone</div>
                <div class="es-sd-value"><?php echo esc_html( $student['timezone'] ); ?></div>
            <?php endif; ?>

            <?php 

            	 $forms = GFAPI::get_forms();
            	 $user_id = $student['id'];

			$all_data = [];

			if ( ! empty( $forms ) ) {

			    foreach ( $forms as $form ) {

			        $form_id = $form['id'];

			        /**
			         * Get user entries
			         */
			        $search_criteria = [
			            'field_filters' => [
			                [
			                    'key'   => 'created_by',
			                    'value' => $user_id
			                ]
			            ]
			        ];

			        $entries = GFAPI::get_entries(
			            $form_id,
			            $search_criteria
			        );

			        if ( is_wp_error( $entries ) || empty( $entries ) ) {
			            continue;
			        }

			        foreach ( $entries as $entry ) {

			            foreach ( $form['fields'] as $field ) {

			                $field_id = (string) $field->id;

			                $value = rgar( $entry, $field_id );

			                /**
			                 * Skip empty fields
			                 */
			                if ( empty( $value ) ) {
			                    continue;
			                }

			                /**
			                 * Get field label
			                 */
			                $field_label = $field->label;

			                /**
			                 * Store unique values only
			                 */
			                if ( ! isset( $all_data[ $field_label ] ) ) {

			                    $all_data[ $field_label ] = $value;
			                }
			            }
			        }
			    }
			}
			
	        if ( ! empty( $all_data ) ) : ?>
	            <?php foreach ( $all_data as $label => $value ) : ?>
	                <div class="es-sd-label">
	                    <?php echo esc_html( $label ); ?>
	                </div>
	               <div class="es-sd-value">
	                    <?php echo esc_html( $value ); ?>
	                </div>
	            <?php endforeach; ?>
	        <?php else : ?>
	            <div class="es-sd-value">
	                No Gravity Forms submissions found.
	            </div>
	        <?php endif; ?>




            <div class="es-sd-label">Joined</div>
            <div class="es-sd-value"><?php echo esc_html( $student['registered_label'] ); ?></div>
        </div>

        <div class="es-sd-stat-row">
            <div class="es-sd-stat"><span>Total</span><strong><?php echo (int) $stats['total']; ?></strong></div>
            <div class="es-sd-stat"><span>Upcoming</span><strong class="es-stat-upcoming"><?php echo (int) $stats['upcoming']; ?></strong></div>
            <div class="es-sd-stat"><span>Past</span><strong class="es-stat-past"><?php echo (int) $stats['past']; ?></strong></div>
        </div>

        <div class="es-sd-section-title">Booking History</div>

        <?php if ( empty( $bookings ) ) : ?>
            <p class="es-empty-cell">No bookings yet.</p>
        <?php else : foreach ( $bookings as $b ) :
            $status_class = ( $b['status'] === 'confirmed' ) ? 'es-pill-success'
                          : ( ( $b['status'] === 'pending' ) ? 'es-pill-warning' : 'es-pill-danger' );
            $type_color   = $b['type_color'] ? $b['type_color'] : '#6366f1';
        ?>
            <div class="es-sd-booking" style="--type-color:<?php echo esc_attr( $type_color ); ?>">
                <div class="es-sd-booking-date"><?php echo esc_html( $b['date_label'] ); ?></div>
                <div class="es-sd-booking-info">
                    <div class="es-sd-booking-title">
                        <?php echo esc_html( $b['title'] ? $b['title'] : ( $b['type_label'] ? $b['type_label'] : 'Booking' ) ); ?>
                    </div>
                    <div class="es-sd-booking-time">
                        <?php echo esc_html( $b['start'] ); ?> – <?php echo esc_html( $b['end'] ); ?>
                        (<?php echo (int) $b['duration']; ?> min) ·
                        <?php echo esc_html( $b['platform'] ? $b['platform'] : '—' ); ?>
                    </div>
                </div>
                <span class="es-pill <?php echo esc_attr( $status_class ); ?>">
                    <?php echo esc_html( strtoupper( $b['status'] ) ); ?>
                </span>
                <?php if ( ! empty( $b['zoom_join_url'] ) ) : ?>
                    <a class="es-zoom-btn" target="_blank" rel="noopener" href="<?php echo esc_url( $b['zoom_join_url'] ); ?>">
                        <span class="dashicons dashicons-video-alt2"></span> Join Zoom
                    </a>
                <?php else : ?>
                    <span class="es-cell-sub">—</span>
                <?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

