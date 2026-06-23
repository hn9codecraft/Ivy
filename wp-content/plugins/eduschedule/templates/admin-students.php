<?php
/**
 * EduSchedule v3.5.0 — Students admin page
 * URL: admin.php?page=eduschedule-students
 *
 * Vars in scope:
 *   $students = array of rows  ['ID','display_name','email','phone','parent_name','reference','registered','count','is_admin']
 *   $filter   = 'all' | 'with_bookings'
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$total = count( $students );
$base  = admin_url( 'admin.php?page=eduschedule-students' );
?>
<div class="es-admin es-students-page">

    <div class="es-page-head">
        <div>
            <h1>All Users</h1>
            <p class="es-page-sub">All registered students &mdash; <?php echo (int) $total; ?> shown</p>
        </div>
        <div class="es-page-actions">
            <button type="button" class="es-btn es-btn-primary" id="es-add-student-btn">
                <span class="dashicons dashicons-plus"></span> Add Student
            </button>
        </div>
    </div>

    <div class="es-card es-card-flush">
        <table class="es-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Reference</th>
                    <th>Bookings</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $students ) ) : ?>
                    <tr><td colspan="7" class="es-empty-cell">No students found.</td></tr>
                <?php else : foreach ( $students as $s ) :
                    $initial = strtoupper( substr( $s['display_name'] ?: 'U', 0, 1 ) );
                    $short   = strtolower( substr( $s['display_name'] ?: 'usr', 0, 3 ) );
                ?>
                    <tr>
                        <td>
                            <div class="es-cell-user">
                                <div class="es-avatar">
                                    <span class="es-avatar-letter"><?php echo esc_html( $initial ); ?></span>
                                    <span class="es-avatar-tag"><?php echo esc_html( $short ); ?></span>
                                </div>
                                <div>
                                    <div class="es-cell-user-name">
                                        <?php echo esc_html( $s['display_name'] ); ?>
                                        <?php if ( ! empty( $s['is_admin'] ) ) : ?>
                                            <span class="es-pill es-pill-info" style="margin-left:6px">ADMIN</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ( ! empty( $s['parent_name'] ) ) : ?>
                                        <div class="es-cell-user-sub">Parent: <?php echo esc_html( $s['parent_name'] ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><a href="mailto:<?php echo esc_attr( $s['email'] ); ?>"><?php echo esc_html( $s['email'] ); ?></a></td>
                        <td><?php echo esc_html( $s['phone'] ?: '—' ); ?></td>
                        <td><?php echo esc_html( $s['reference'] ? ucfirst( $s['reference'] ) : '—' ); ?></td>
                        <td><span class="es-badge"><?php echo (int) $s['count']; ?></span></td>
                        <td class="es-cell-sub"><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $s['registered'] ) ) ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'detail', 'user_id' => (int) $s['ID'] ), $base ) ); ?>" class="es-btn es-btn-primary es-btn-sm">
                                Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Student Modal -->
<div class="es-modal" id="es-add-student-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card">
        <div class="es-modal-head">
            <h2>Add Student</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">
            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">First Name</label>
                    <input type="text" id="es-st-first" placeholder="John" />
                </div>
                <div class="es-field">
                    <label class="es-label">Last Name</label>
                    <input type="text" id="es-st-last" placeholder="Doe" />
                </div>
            </div>
            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Email</label>
                    <input type="email" id="es-st-email" placeholder="john@example.com" />
                </div>
                <div class="es-field">
                    <label class="es-label">Phone</label>
                    <input type="text" id="es-st-phone" placeholder="+1 (555) 000-0000" />
                </div>
            </div>
            <div class="es-field">
                <label class="es-label">Parent Name</label>
                <input type="text" id="es-st-parent" placeholder="Parent Name" />
            </div>
            <div class="es-field">
                <label class="es-label">From Reference</label>
                <select id="es-st-ref">
                    <option value="">Select source…</option>
                    <option value="google">Google</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="referral">Referral / Word of mouth</option>
                    <option value="website">Website</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="es-field">
                <label class="es-label">Additional Comment</label>
                <textarea id="es-st-comment" rows="3" placeholder="Add any notes..."></textarea>
            </div>
            <label class="es-checkbox-row">
                <input type="checkbox" id="es-st-send-email" checked />
                <span>Send "set your password" email to the student</span>
            </label>
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-st-save">Add Student</button>
        </div>
    </div>
</div>

<!-- Student Details modal removed in v3.5.1 — now opens as full page via ?view=detail -->
