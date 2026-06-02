<?php
/**
 * Admin → Payments
 *
 * Lists every payment (default: paid) with the buyer's name, email, the course
 * (package) they bought, the amount, the billing cycle / number of months, and
 * whether they are a 1:1 or Group student.
 *
 * In scope:
 *   $payments  array of payment rows (joined to user + package)
 *   $args      array( 'status' => ..., 'search' => ... )
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$status = isset( $args['status'] ) ? $args['status'] : 'paid';
$search = isset( $args['search'] ) ? $args['search'] : '';

// Quick totals (paid only)
$total_paid   = 0.0;
$paid_count   = 0;
$paid_currency = '';
foreach ( $payments as $p ) {
    if ( $p->status === 'paid' ) {
        $total_paid += (float) $p->amount;
        $paid_count++;
        if ( ! $paid_currency ) $paid_currency = $p->currency;
    }
}
?>
<div class="es-admin es-payments-page">
    <div class="es-page-head">
        <div>
            <h1>Payments</h1>
            <p class="es-page-sub">All package purchases made through Stripe.</p>
        </div>
        <div class="es-page-actions">
            <?php if ( $paid_count ) : ?>
                <span class="es-pill es-pill-success" style="font-size:13px">
                    Collected: <strong><?php echo esc_html( ES_Helpers::format_price( $total_paid, $paid_currency ?: 'INR' ) ); ?></strong>
                    (<?php echo (int) $paid_count; ?> paid)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <form method="get" class="es-filter-bar">
        <input type="hidden" name="page" value="eduschedule-payments" />
        <label>Status
            <select name="status">
                <option value="paid"    <?php selected( $status, 'paid' ); ?>>Paid</option>
                <option value="pending" <?php selected( $status, 'pending' ); ?>>Pending</option>
                <option value="all"     <?php selected( $status, 'all' ); ?>>All</option>
            </select>
        </label>
        <input type="search" name="s" placeholder="Search name, email or course" value="<?php echo esc_attr( $search ); ?>" />
        <button class="es-btn es-btn-primary" type="submit">Filter</button>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=eduschedule-payments' ) ); ?>" class="es-btn es-btn-ghost">Reset</a>
        <span class="es-pill es-pill-info" style="margin-left:auto">Showing: <?php echo count( $payments ); ?></span>
    </form>

    <div class="es-card es-card-flush">
        <table class="es-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Course / Package</th>
                    <th>Type</th>
                    <th>Payment</th>
                    <th>Plan Length</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $payments ) ) : ?>
                    <tr><td colspan="9" class="es-empty-cell">No payments found.</td></tr>
                <?php else : foreach ( $payments as $p ) :
                    $name   = $p->display_name ?: '(deleted user)';
                    $email  = $p->user_email ?: '—';
                    $course = $p->package_name ?: '—';
                    $amount = ES_Helpers::format_price( $p->amount, $p->currency );
                    $months = max( 1, (int) ( $p->months ?? 1 ) );
                    $type   = $p->user_id ? ES_Packages::category_label( (int) $p->user_id ) : '—';

                    // Type pill colour
                    $type_class = 'es-pill-info';
                    if ( $type === '1:1' )   $type_class = 'es-pill-purple';
                    if ( $type === 'Group' ) $type_class = 'es-pill-teal';

                    // Status pill colour
                    $status_class = 'es-pill-info';
                    if ( $p->status === 'paid' )    $status_class = 'es-pill-success';
                    if ( $p->status === 'pending' ) $status_class = 'es-pill-warn';
                ?>
                    <tr>
                        <td>#<?php echo (int) $p->id; ?></td>
                        <td>
                            <?php if ( $p->user_id ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=eduschedule-students&view=detail&user_id=' . (int) $p->user_id ) ); ?>">
                                    <strong><?php echo esc_html( $name ); ?></strong>
                                </a>
                            <?php else : ?>
                                <strong><?php echo esc_html( $name ); ?></strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $email !== '—' ) : ?>
                                <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                            <?php else : echo '—'; endif; ?>
                        </td>
                        <td><?php echo esc_html( $course ); ?></td>
                        <td><span class="es-pill <?php echo esc_attr( $type_class ); ?>"><?php echo esc_html( $type ); ?></span></td>
                        <td><strong><?php echo esc_html( $amount ); ?></strong></td>
                        <td>
                            <?php echo esc_html( $months . ' month' . ( $months > 1 ? 's' : '' ) ); ?>
                            <div class="es-cell-sub"><?php echo esc_html( ucfirst( $p->billing_cycle ) ); ?></div>
                        </td>
                        <td><span class="es-pill <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( ucfirst( $p->status ) ); ?></span></td>
                        <td>
                            <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $p->created_at ) ) ); ?>
                            <div class="es-cell-sub"><?php echo esc_html( date_i18n( 'g:i A', strtotime( $p->created_at ) ) ); ?></div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Pill colours used on this page (kept local so they work even if not in admin.css) */
.es-payments-page .es-pill{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.3px;padding:3px 10px;border-radius:999px;text-transform:uppercase}
.es-payments-page .es-pill-success{background:#dcfce7;color:#166534}
.es-payments-page .es-pill-warn{background:#fef3c7;color:#92400e}
.es-payments-page .es-pill-info{background:#e0e7ff;color:#3730a3}
.es-payments-page .es-pill-purple{background:#ede9fe;color:#6d28d9}
.es-payments-page .es-pill-teal{background:#ccfbf1;color:#0f766e}
.es-payments-page .es-filter-bar select{padding:6px 10px;border:1px solid #d1d5db;border-radius:6px}
</style>
