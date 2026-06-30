<?php
/**
 * EduSchedule Packages Admin Page
 * URL: admin.php?page=eduschedule-packages
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$packages = ES_Packages::get_all( false );
$base = admin_url( 'admin.php?page=eduschedule-packages' );
?>
<div class="es-admin es-packages-page">

    <div class="es-page-head">
        <div>
            <h1>Packages</h1>
            <p class="es-page-sub">Create and manage your coaching packages</p>
        </div>
        <div class="es-page-actions">
            <button type="button" class="es-btn es-btn-primary" id="es-add-package-btn">
                <span class="dashicons dashicons-plus"></span> Add Package
            </button>
        </div>
    </div>

    <?php
    // Split packages by type for sectioned display
    $pkgs_1to1  = array();
    $pkgs_group = array();
    $pkgs_other = array();
    if ( ! empty( $packages ) ) {
        foreach ( $packages as $pkg ) {
            $t = ! empty( $pkg->package_type ) ? $pkg->package_type : '1to1';
            if ( $t === 'group' )      $pkgs_group[] = $pkg;
            elseif ( $t === '1to1' )   $pkgs_1to1[]  = $pkg;
            else                       $pkgs_other[] = $pkg;
        }
    }
    ?>

    <?php if ( empty( $packages ) ) : ?>
        <div class="es-card" style="padding:40px;text-align:center;margin-top:24px;">
            <p class="es-empty-cell">No packages created yet. Click "Add Package" to get started.</p>
        </div>
    <?php else : ?>

    <!-- 1:1 Packages Section -->
    <div class="es-pkg-section-label" style="margin-top:28px;margin-bottom:4px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:18px;font-weight:700;color:#2271b1;">🎯 1:1 Packages</span>
        <span class="es-pill es-pill-info" style="font-size:11px;"><?php echo count( $pkgs_1to1 ); ?></span>
    </div>
    <div class="es-packages-grid">
        <?php if ( empty( $pkgs_1to1 ) ) : ?>
            <div class="es-card" style="padding:24px;text-align:center;grid-column:1/-1;">
                <p class="es-empty-cell">No 1:1 packages yet.</p>
            </div>
        <?php else : foreach ( $pkgs_1to1 as $pkg ) :
            $pkg_type_raw   = '1to1';
            $pkg_type_label = '1:1';
        ?>
            <div class="es-package-card <?php echo ! $pkg->is_active ? 'is-inactive' : ''; ?>" data-package-id="<?php echo (int) $pkg->id; ?>" data-package-type="<?php echo esc_attr( $pkg_type_raw ); ?>">
                <div class="es-package-header">
                    <div>
                        <h3 class="es-package-name"><?php echo esc_html( $pkg->package_name ); ?></h3>
                        <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                            <p class="es-package-sub"><?php echo esc_html( $pkg->sub_heading ); ?></p>
                        <?php endif; ?>
                        <span class="es-pill es-pill-info" style="font-size:10px;margin-top:4px;display:inline-block;"><?php echo esc_html( $pkg_type_label ); ?></span>
                    </div>
                    <div class="es-package-actions">
                        <button type="button" class="es-btn es-btn-sm es-btn-ghost es-edit-package" data-id="<?php echo (int) $pkg->id; ?>">
                            Edit
                        </button>
                        <button type="button" class="es-btn es-btn-sm es-btn-danger es-delete-package" data-id="<?php echo (int) $pkg->id; ?>">
                            ×
                        </button>
                    </div>
                </div>

                <div class="es-package-price">
                    <?php
                    $cur = ! empty( $pkg->currency ) ? $pkg->currency : 'INR';
                    // Final total price = monthly_price × months (stored in `price`)
                    echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) );
                    ?>
                    <?php
                    $pkg_months = max( 1, (int) ( $pkg->months ?? 1 ) );
                    ?>
                    <span class="es-package-period">/ <?php echo (int) $pkg_months; ?> month<?php echo $pkg_months > 1 ? 's' : ''; ?></span>
                </div>

                <?php if ( ! empty( $pkg->monthly_price ) ) : ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php echo esc_html( ES_Helpers::format_price( $pkg->monthly_price, $cur ) ); ?> / month × <?php echo (int) $pkg_months; ?>
                    </div>
                <?php endif; ?>

                <?php
                $pkg_total_sessions = (int) ( $pkg->total_sessions ?? 0 );
                $pkg_monthly_limit  = (int) ( $pkg->monthly_session_limit ?? 0 );
                if ( $pkg_total_sessions > 0 || $pkg_monthly_limit > 0 ) :
                ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-groups"></span>
                        <?php echo (int) $pkg_total_sessions; ?> sessions
                        <?php if ( $pkg_monthly_limit > 0 ) : ?>
                            <small style="opacity:.75">(<?php echo (int) $pkg_monthly_limit; ?>/mo)</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>


                <?php if ( ! empty( $pkg->discount_percent ) && ! empty( $pkg->discount_months ) ) : ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-tag"></span>
                        <?php echo esc_html( rtrim( rtrim( number_format( (float) $pkg->discount_percent, 1 ), '0' ), '.' ) ); ?>% off for <?php echo (int) $pkg->discount_months; ?> month<?php echo (int) $pkg->discount_months > 1 ? 's' : ''; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $pkg->description ) ) : ?>
                    <div class="es-package-desc">
                        <?php echo nl2br( esc_html( $pkg->description ) ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! $pkg->is_active ) : ?>
                    <div class="es-package-status">
                        <span class="es-pill es-pill-warning">Inactive</span>
                    </div>
                <?php endif; ?>

                <?php
                // v4.4 — Per-package "global" library: files AND videos linked
                // to THIS package. Shown to every student who owns it,
                // independent of any per-session uploads from the Schedule
                // modal. Visible only to admins on this Packages page.
                $pkg_videos = ES_Packages::get_package_videos( (int) $pkg->id );
                $pkg_files  = ES_Packages::get_package_files( (int) $pkg->id );
                ?>
               
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Group Packages Section -->
    <div class="es-pkg-section-label" style="margin-top:32px;margin-bottom:4px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:18px;font-weight:700;color:#00a32a;">👥 Group Packages</span>
        <span class="es-pill es-pill-success" style="font-size:11px;"><?php echo count( $pkgs_group ); ?></span>
    </div>
    <div class="es-packages-grid">
        <?php if ( empty( $pkgs_group ) ) : ?>
            <div class="es-card" style="padding:24px;text-align:center;grid-column:1/-1;">
                <p class="es-empty-cell">No Group packages yet.</p>
            </div>
        <?php else : foreach ( $pkgs_group as $pkg ) :
            $pkg_type_raw   = 'group';
            $pkg_type_label = 'Group';
        ?>
            <div class="es-package-card <?php echo ! $pkg->is_active ? 'is-inactive' : ''; ?>" data-package-id="<?php echo (int) $pkg->id; ?>" data-package-type="<?php echo esc_attr( $pkg_type_raw ); ?>">
                <div class="es-package-header">
                    <div>
                        <h3 class="es-package-name"><?php echo esc_html( $pkg->package_name ); ?></h3>
                        <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                            <p class="es-package-sub"><?php echo esc_html( $pkg->sub_heading ); ?></p>
                        <?php endif; ?>
                        <span class="es-pill es-pill-success" style="font-size:10px;margin-top:4px;display:inline-block;"><?php echo esc_html( $pkg_type_label ); ?></span>
                    </div>
                    <div class="es-package-actions">
                        <button type="button" class="es-btn es-btn-sm es-btn-ghost es-edit-package" data-id="<?php echo (int) $pkg->id; ?>">
                            Edit
                        </button>
                        <button type="button" class="es-btn es-btn-sm es-btn-danger es-delete-package" data-id="<?php echo (int) $pkg->id; ?>">
                            ×
                        </button>
                    </div>
                </div>

                <div class="es-package-price">
                    <?php
                    $cur = ! empty( $pkg->currency ) ? $pkg->currency : 'INR';
                    echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) );
                    ?>
                    <?php
                    $pkg_months = max( 1, (int) ( $pkg->months ?? 1 ) );
                    ?>
                    <span class="es-package-period">/ <?php echo (int) $pkg_months; ?> month<?php echo $pkg_months > 1 ? 's' : ''; ?></span>
                </div>

                <?php if ( ! empty( $pkg->monthly_price ) ) : ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php echo esc_html( ES_Helpers::format_price( $pkg->monthly_price, $cur ) ); ?> / month × <?php echo (int) $pkg_months; ?>
                    </div>
                <?php endif; ?>

                <?php
                $pkg_total_sessions = (int) ( $pkg->total_sessions ?? 0 );
                $pkg_monthly_limit  = (int) ( $pkg->monthly_session_limit ?? 0 );
                if ( $pkg_total_sessions > 0 || $pkg_monthly_limit > 0 ) :
                ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-groups"></span>
                        <?php echo (int) $pkg_total_sessions; ?> sessions
                        <?php if ( $pkg_monthly_limit > 0 ) : ?>
                            <small style="opacity:.75">(<?php echo (int) $pkg_monthly_limit; ?>/mo)</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $pkg->discount_percent ) && ! empty( $pkg->discount_months ) ) : ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-tag"></span>
                        <?php echo esc_html( rtrim( rtrim( number_format( (float) $pkg->discount_percent, 1 ), '0' ), '.' ) ); ?>% off for <?php echo (int) $pkg->discount_months; ?> month<?php echo (int) $pkg->discount_months > 1 ? 's' : ''; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $pkg->description ) ) : ?>
                    <div class="es-package-desc">
                        <?php echo nl2br( esc_html( $pkg->description ) ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! $pkg->is_active ) : ?>
                    <div class="es-package-status">
                        <span class="es-pill es-pill-warning">Inactive</span>
                    </div>
                <?php endif; ?>

                <?php
                $pkg_videos = ES_Packages::get_package_videos( (int) $pkg->id );
                $pkg_files  = ES_Packages::get_package_files( (int) $pkg->id );
                ?>
               
            </div>
        <?php endforeach; endif; ?>
    </div>

    <?php if ( ! empty( $pkgs_other ) ) : ?>
    <!-- Other Packages -->
    <div class="es-pkg-section-label" style="margin-top:32px;margin-bottom:4px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:18px;font-weight:700;color:#2271b1;">💼 Other Packages</span>
        <span class="es-pill es-pill-info" style="font-size:11px;"><?php echo count( $pkgs_other ); ?></span>
    </div>
    <div class="es-packages-grid">
        <?php foreach ( $pkgs_other as $pkg ) :
            $pkg_type_raw   = ! empty( $pkg->package_type ) ? $pkg->package_type : '1to1';
            $pkg_type_label = array( '1to1' => '1:1', 'group' => 'Group', 'consultancy' => 'Consultancy' )[ $pkg_type_raw ] ?? strtoupper( $pkg_type_raw );
        ?>
            <div class="es-package-card <?php echo ! $pkg->is_active ? 'is-inactive' : ''; ?>" data-package-id="<?php echo (int) $pkg->id; ?>" data-package-type="<?php echo esc_attr( $pkg_type_raw ); ?>">
                <div class="es-package-header">
                    <div>
                        <h3 class="es-package-name"><?php echo esc_html( $pkg->package_name ); ?></h3>
                        <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                            <p class="es-package-sub"><?php echo esc_html( $pkg->sub_heading ); ?></p>
                        <?php endif; ?>
                        <span class="es-pill es-pill-info" style="font-size:10px;margin-top:4px;display:inline-block;"><?php echo esc_html( $pkg_type_label ); ?></span>
                    </div>
                    <div class="es-package-actions">
                        <button type="button" class="es-btn es-btn-sm es-btn-ghost es-edit-package" data-id="<?php echo (int) $pkg->id; ?>">Edit</button>
                        <button type="button" class="es-btn es-btn-sm es-btn-danger es-delete-package" data-id="<?php echo (int) $pkg->id; ?>">×</button>
                    </div>
                </div>
                <div class="es-package-price">
                    <?php $cur = ! empty( $pkg->currency ) ? $pkg->currency : 'INR'; echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) ); ?>
                    <?php $pkg_months = max( 1, (int) ( $pkg->months ?? 1 ) ); ?>
                    <span class="es-package-period">/ <?php echo (int) $pkg_months; ?> month<?php echo $pkg_months > 1 ? 's' : ''; ?></span>
                </div>
                <?php if ( ! empty( $pkg->description ) ) : ?>
                    <div class="es-package-desc"><?php echo nl2br( esc_html( $pkg->description ) ); ?></div>
                <?php endif; ?>
                <?php if ( ! $pkg->is_active ) : ?>
                    <div class="es-package-status"><span class="es-pill es-pill-warning">Inactive</span></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; /* empty packages */ ?>
</div>

<!-- Add/Edit Package Modal -->
<div class="es-modal" id="es-package-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card es-modal-lg es-pkg-modal-card" style="max-width:760px;">
        <div class="es-modal-head">
            <div>
                <div class="es-pkg-modal-kicker">Packages</div>
                <h2 id="es-pkg-modal-title">Create Package</h2>
            </div>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body es-pkg-modal-body">
            <input type="hidden" id="es-pkg-id" value="" />

            <!-- Section: Identity -->
            <div style="margin-bottom:24px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#2271b1;font-weight:700;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #dcdcde;">📋 Package Identity</div>
                <div class="es-modal-row">
                    <div class="es-field">
                        <label class="es-label">Package Name <span style="color:#d63638;">*</span></label>
                        <input type="text" id="es-pkg-name" placeholder="e.g. Premium IELTS Coaching" />
                    </div>
                    <div class="es-field">
                        <label class="es-label">Sub Heading</label>
                        <input type="text" id="es-pkg-subheading" placeholder="e.g. For Band 7.5+" />
                    </div>
                </div>
                <div class="es-modal-row" style="margin-top:14px;">
                    <div class="es-field">
                        <label class="es-label">Package Type</label>
                        <select id="es-pkg-type" style="width:100%;cursor:pointer;">
                            <option value="1to1">🎯 1:1 (One-on-One)</option>
                            <option value="group">👥 Group</option>
                            <option value="consultancy">💼 Consultancy</option>
                        </select>
                        <small class="es-field-hint">Filters which packages appear in After Call and Purchase flows.</small>
                    </div>
                    <div class="es-field">
                        <label class="es-label">Tagline / Period</label>
                        <input type="text" id="es-pkg-tagline" placeholder="e.g. 3 months program" />
                    </div>
                </div>
            </div>

            <!-- Section: Pricing -->
            <div style="margin-bottom:24px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#2271b1;font-weight:700;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #dcdcde;">💰 Pricing</div>
                <div class="es-modal-row">
                    <div class="es-field">
                        <label class="es-label">Currency</label>
                        <select id="es-pkg-currency">
                            <?php foreach ( ES_Helpers::currencies() as $code => $info ) : ?>
                                <option value="<?php echo esc_attr( $code ); ?>">
                                    <?php echo esc_html( $info['symbol'] . '  ' . $code . ' — ' . $info['name'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="es-field">
                        <label class="es-label">Monthly Price</label>
                        <input type="number" id="es-pkg-monthly-price" placeholder="5000" step="0.01" min="0" />
                        <small class="es-field-hint">Amount charged per month.</small>
                    </div>
                </div>
            </div>

            <!-- Section: Sessions -->
            <div style="margin-bottom:24px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#2271b1;font-weight:700;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #dcdcde;">🗓 Sessions & Duration</div>
                <div class="es-modal-row">
                    <div class="es-field">
                        <label class="es-label">Duration (Months)</label>
                        <input type="number" id="es-pkg-months" placeholder="3" step="1" min="1" value="1" />
                        <small class="es-field-hint">Number of months this package runs.</small>
                    </div>
                    <div class="es-field">
                        <label class="es-label">Monthly Session Limit</label>
                        <input type="number" id="es-pkg-monthly-sessions" placeholder="8" step="1" min="0" />
                        <small class="es-field-hint">Sessions a student can use each month.</small>
                    </div>
                </div>

                <div class="es-modal-row" style="margin-top:14px;">
                    <div class="es-field">
                        <label class="es-label">Discount %</label>
                        <input type="number" id="es-pkg-discount-percent" placeholder="12" step="0.1" min="0" max="100" />
                        <small class="es-field-hint">Optional. For frontend discounted plan toggle.</small>
                    </div>
                    <div class="es-field">
                        <label class="es-label">Discount Months</label>
                        <input type="number" id="es-pkg-discount-months" placeholder="6" step="1" min="0" max="60" />
                        <small class="es-field-hint">e.g. 12% off for 6 months.</small>
                    </div>
                </div>

                <!-- Auto-calculated summary -->
                <div class="es-field" style="margin-top:14px;">
                    <div style="padding:14px 18px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;background:#2271b1;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;">₹</div>
                            <div>
                                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#646970;font-weight:600;">Total Package Price</div>
                                <div id="es-pkg-calc-total" style="font-size:24px;font-weight:800;color:#2271b1;letter-spacing:-.5px;">—</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;background:#00a32a;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;">✓</div>
                            <div>
                                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#646970;font-weight:600;">Total Sessions</div>
                                <div id="es-pkg-calc-sessions" style="font-size:24px;font-weight:800;color:#00a32a;letter-spacing:-.5px;">—</div>
                            </div>
                        </div>
                    </div>
                    <small class="es-field-hint">Total Price = Monthly Price × Months &nbsp;·&nbsp; Total Sessions = Monthly Limit × Months</small>
                </div>
            </div>
          

            <!-- Section: Details -->
            <div style="margin-bottom:8px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#2271b1;font-weight:700;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #dcdcde;">📝 Details</div>
                <div class="es-modal-row">
                    <div class="es-field">
                        <label class="es-label">Display Order</label>
                        <input type="number" id="es-pkg-order" placeholder="0" value="0" />
                        <small class="es-field-hint">Lower = appears first.</small>
                    </div>
                    <div class="es-field" style="opacity:0;pointer-events:none;"></div>
                </div>
                <div class="es-field" style="margin-top:14px;">
                    <label class="es-label">Full Description</label>
                    <textarea id="es-pkg-description" rows="5" placeholder="• 24 sessions of 1-hour each&#10;• Speaking, Writing, Reading & Listening&#10;• Mock tests every 2 weeks&#10;• Personalized feedback on every essay" style="font-family:inherit;"></textarea>
                </div>
            </div>

            <!-- Hidden fields -->
            <input type="hidden" id="es-pkg-price" value="" />
            <input type="hidden" id="es-pkg-active" value="1" />
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn" id="es-pkg-save">
                <span id="es-pkg-save-text">Create Package</span>
            </button>
        </div>
    </div>
</div>

