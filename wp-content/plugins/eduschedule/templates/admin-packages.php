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

    <div class="es-packages-grid">
        <?php if ( empty( $packages ) ) : ?>
            <div class="es-card" style="padding:40px;text-align:center">
                <p class="es-empty-cell">No packages created yet. Click "Add Package" to get started.</p>
            </div>
        <?php else : foreach ( $packages as $pkg ) : ?>
            <div class="es-package-card <?php echo ! $pkg->is_active ? 'is-inactive' : ''; ?>" data-package-id="<?php echo (int) $pkg->id; ?>">
                <div class="es-package-header">
                    <div>
                        <h3 class="es-package-name"><?php echo esc_html( $pkg->package_name ); ?></h3>
                        <?php if ( ! empty( $pkg->sub_heading ) ) : ?>
                            <p class="es-package-sub"><?php echo esc_html( $pkg->sub_heading ); ?></p>
                        <?php endif; ?>
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
                <div class="es-pkgvids" data-package-id="<?php echo (int) $pkg->id; ?>">
                    <div class="es-pkgvids-head">
                        <div class="es-pkgvids-title">
                            <span class="dashicons dashicons-portfolio"></span>
                            Package Library
                            <span class="es-pkgvids-count"><?php echo (int) ( count( $pkg_videos ) + count( $pkg_files ) ); ?></span>
                        </div>
                        <div style="display:inline-flex;gap:6px;">
                            <input type="file" class="es-pkgfile-input" data-id="<?php echo (int) $pkg->id; ?>" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mov,.webm,.mkv,.avi" style="display:none;" />
                            <button type="button" class="es-btn es-btn-sm es-btn-ghost es-pkgfile-add" data-id="<?php echo (int) $pkg->id; ?>" title="Upload a file (PDF, DOC, PPT or video) from your computer">
                                <span class="dashicons dashicons-upload"></span> File
                            </button>
                            <button type="button" class="es-btn es-btn-sm es-btn-ghost es-pkgvid-add" data-id="<?php echo (int) $pkg->id; ?>" title="Pick a video from the WordPress media library">
                                <span class="dashicons dashicons-format-video"></span> Video
                            </button>
                        </div>
                    </div>
                    <div class="es-pkgvids-progress" data-id="<?php echo (int) $pkg->id; ?>" style="display:none;font-size:12px;color:#a5b4fc;margin-bottom:8px;">Uploading…</div>
                    <div class="es-pkgvids-grid">
                        <?php if ( empty( $pkg_videos ) && empty( $pkg_files ) ) : ?>
                            <p class="es-pkgvids-empty">No course materials yet — files and videos you add here are visible to every student of this package, automatically.</p>
                        <?php else : ?>
                            <?php foreach ( $pkg_videos as $pv ) : ?>
                                <div class="es-pkgvid-card" data-video-id="<?php echo (int) $pv->id; ?>">
                                    <a href="<?php echo esc_url( $pv->video_url ); ?>" target="_blank" rel="noopener" class="es-pkgvid-thumb">
                                        <span class="es-pkgvid-play">▶</span>
                                    </a>
                                    <div class="es-pkgvid-meta">
                                        <div class="es-pkgvid-title-row">
                                            <span class="es-pkgvid-title"><?php echo esc_html( $pv->title ); ?></span>
                                            <button type="button" class="es-pkgvid-del" data-id="<?php echo (int) $pv->id; ?>" aria-label="Delete">×</button>
                                        </div>
                                        <?php if ( ! empty( $pv->duration ) ) : ?>
                                            <div class="es-pkgvid-dur">⏱ <?php echo esc_html( $pv->duration ); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php foreach ( $pkg_files as $pf ) :
                                $pf_size = $pf->file_size ? size_format( (int) $pf->file_size ) : '';
                            ?>
                                <div class="es-pkgvid-card es-pkgfile-card" data-file-id="<?php echo (int) $pf->id; ?>">
                                    <a href="<?php echo esc_url( $pf->file_url ); ?>" target="_blank" rel="noopener" class="es-pkgfile-thumb">
                                        <span class="es-pkgfile-type"><?php echo esc_html( strtoupper( $pf->file_type ) ); ?></span>
                                    </a>
                                    <div class="es-pkgvid-meta">
                                        <div class="es-pkgvid-title-row">
                                            <span class="es-pkgvid-title"><?php echo esc_html( $pf->file_name ); ?></span>
                                            <button type="button" class="es-pkgfile-del" data-id="<?php echo (int) $pf->id; ?>" aria-label="Delete">×</button>
                                        </div>
                                        <?php if ( $pf_size ) : ?>
                                            <div class="es-pkgvid-dur"><?php echo esc_html( $pf_size ); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Add/Edit Package Modal -->
<div class="es-modal" id="es-package-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card es-modal-lg">
        <div class="es-modal-head">
            <h2 id="es-pkg-modal-title">Create Package</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">
            <input type="hidden" id="es-pkg-id" value="" />
            
            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Package Name</label>
                    <input type="text" id="es-pkg-name" placeholder="Premium IELTS Coaching" />
                </div>
                <div class="es-field">
                    <label class="es-label">Sub Heading</label>
                    <input type="text" id="es-pkg-subheading" placeholder="For Band 7.5+" />
                </div>
            </div>

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



            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Discount Percentage (%)</label>
                    <input type="number" id="es-pkg-discount-percent" placeholder="12" step="0.1" min="0" max="100" />
                    <small class="es-field-hint">Optional. Used for frontend discounted plan toggle.</small>
                </div>
                <div class="es-field">
                    <label class="es-label">Discount Months</label>
                    <input type="number" id="es-pkg-discount-months" placeholder="6" step="1" min="0" max="60" />
                    <small class="es-field-hint">Example: 12% for 6 months. Leave blank to use global settings.</small>
                </div>
            </div>

            <!-- Auto-calculated summary (read-only) -->
            <div class="es-field">
                <div class="es-pkg-calc-box" style="padding:14px 16px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.25);border-radius:8px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <div class="es-pkg-calc-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;opacity:.65;">Total Package Price</div>
                        <div class="es-pkg-calc-value" id="es-pkg-calc-total" style="font-size:22px;font-weight:700;color:#6366f1;">—</div>
                    </div>
                    <div>
                        <div class="es-pkg-calc-label" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;opacity:.65;">Total Sessions</div>
                        <div class="es-pkg-calc-value" id="es-pkg-calc-sessions" style="font-size:22px;font-weight:700;color:#10b981;">—</div>
                    </div>
                </div>
                <small class="es-field-hint">Total Price = Monthly Price × Months &nbsp;·&nbsp; Total Sessions = Monthly Limit × Months</small>
            </div>

            <!-- Final total price (read-only mirror, stored on save) -->
            <input type="hidden" id="es-pkg-price" value="" />

            <div class="es-field">
                <label class="es-label">Tagline / Period</label>
                <input type="text" id="es-pkg-tagline" placeholder="3 months program" />
            </div>
            <div class="es-field">
                <label class="es-label">Display Order</label>
                <input type="number" id="es-pkg-order" placeholder="0" value="0" />
                <small class="es-field-hint">Lower numbers appear first. New packages are visible to students by default.</small>
            </div>
            <div class="es-field">
                <label class="es-label">Full Description</label>
                <textarea id="es-pkg-description" rows="6" placeholder="• 24 sessions of 1-hour each&#10;• Speaking, Writing, Reading & Listening&#10;• Mock tests every 2 weeks&#10;• Personalized feedback on every essay"></textarea>
            </div>
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

<style>
.es-packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.es-package-card {
    background: #ffffff;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
}

.es-package-card:hover {
    border-color: #6366f1;
    box-shadow: 0 8px 24px rgba(99,102,241,0.15);
}

.es-package-card.is-inactive {
    opacity: 0.6;
}

.es-package-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}

.es-package-name {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.es-package-sub {
    font-size: 13px;
    margin: 0;
}

.es-package-actions {
    display: flex;
    gap: 6px;
}

.es-package-price {
    font-size: 28px;
    font-weight: 700;
    color: #6366f1;
    margin-bottom: 12px;
}

.es-package-period {
    font-size: 14px;
    font-weight: 400;
    color: rgba(255,255,255,0.5);
}

.es-package-hours {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(99,102,241,0.15);
    color: #6366f1;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 12px;
}

.es-package-hours .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.es-package-yearly {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(16,185,129,0.12);
    color: #10b981;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 12px;
    margin-left: 6px;
}
.es-package-yearly .dashicons { font-size: 16px; width: 16px; height: 16px; }
.es-package-yearly small { opacity: 0.8; font-weight: 400; }

.es-package-desc {
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    margin-top: 12px;
}

.es-package-status {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.es-modal-lg {
    max-width: 700px;
}

/* ── v4.4 — Per-package "global" videos on the Packages admin page ── */
.es-pkgvids {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.10);
}
.es-pkgvids-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
}
.es-pkgvids-title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgba(255,255,255,0.85);
}
.es-pkgvids-title .dashicons { font-size: 16px; width: 16px; height: 16px; }
.es-pkgvids-count {
    background: rgba(99,102,241,0.2);
    color: #c7d2fe;
    border-radius: 999px;
    padding: 1px 8px;
    font-size: 11px;
    font-weight: 600;
}
.es-pkgvids-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 8px;
}
.es-pkgvids-empty {
    grid-column: 1 / -1;
    margin: 0;
    padding: 12px;
    background: rgba(255,255,255,0.05);
    border: 1px dashed rgba(255,255,255,0.15);
    border-radius: 8px;
    font-size: 12.5px;
    color: rgba(255,255,255,0.65);
    text-align: center;
}
.es-pkgvid-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.es-pkgvid-thumb {
    aspect-ratio: 16 / 9;
    background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    font-size: 22px;
    line-height: 1;
}
.es-pkgvid-thumb:hover { opacity: 0.92; }
.es-pkgfile-thumb {
    aspect-ratio: 16 / 9;
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
}
.es-pkgfile-thumb:hover { opacity: 0.92; }
.es-pkgfile-type {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 4px 10px;
    background: rgba(255,255,255,0.18);
    border-radius: 6px;
}
.es-pkgvid-meta { padding: 8px 10px; }
.es-pkgvid-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 6px;
}
.es-pkgvid-title {
    font-size: 12.5px;
    font-weight: 500;
    color: #fff;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.es-pkgvid-del {
    background: transparent;
    border: 0;
    color: rgba(255,255,255,0.55);
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    padding: 0 2px;
}
.es-pkgvid-del:hover { color: #f87171; }
.es-pkgvid-dur {
    font-size: 11px;
    color: rgba(255,255,255,0.55);
    margin-top: 3px;
}
</style>
