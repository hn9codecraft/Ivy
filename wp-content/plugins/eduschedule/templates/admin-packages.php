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
                    echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) );
                    ?>
                    <?php if ( ! empty( $pkg->tagline ) ) : ?>
                        <span class="es-package-period">/ <?php echo esc_html( $pkg->tagline ); ?></span>
                    <?php endif; ?>
                </div>

                <?php
                $s_yd = (float) ( ES_Helpers::settings()['yearly_discount'] ?? 0 );
                if ( (float) $pkg->price > 0 && $s_yd > 0 ) :
                    $gross = (float) $pkg->price * 12;
                    $net   = $gross * ( 1 - ( $s_yd / 100 ) );
                ?>
                    <div class="es-package-yearly">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        Yearly: <?php echo esc_html( ES_Helpers::format_price( $net, $cur ) ); ?>
                        <small>(<?php echo esc_html( rtrim( rtrim( number_format( $s_yd, 1 ), '0' ), '.' ) ); ?>% off)</small>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $pkg->hours ) ) : ?>
                    <div class="es-package-hours">
                        <span class="dashicons dashicons-clock"></span>
                        <?php echo (int) $pkg->hours; ?> hrs
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
                    <label class="es-label">Price (Monthly)</label>
                    <input type="number" id="es-pkg-price" placeholder="15000" step="0.01" min="0" />
                </div>
            </div>

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
    background: #1e1e2e;
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
    color: #fff;
    margin: 0 0 4px 0;
}

.es-package-sub {
    font-size: 13px;
    color: rgba(255,255,255,0.6);
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
    color: rgba(255,255,255,0.8);
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
</style>
