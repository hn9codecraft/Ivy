<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="es-admin">
    <div class="es-page-head">
        <div>
            <h1>Calendar</h1>
            <p class="es-page-sub">Full view of all scheduled slots, sessions &amp; bookings.</p>
        </div>
        <div class="es-page-actions">
            <button type="button" class="es-btn es-btn-primary" id="es-cal-add-slot"><span class="dashicons dashicons-plus"></span>Schedule a meeting</button>
        </div>
    </div>

    <div class="es-cal-legend">
        <?php foreach ( ES_Helpers::slot_types() as $key => $info ) : ?>
            <span class="es-legend-item"><i class="es-legend-dot" style="background:<?php echo esc_attr( $info['color'] ); ?>"></i> <?php echo esc_html( $info['label'] ); ?></span>
        <?php endforeach; ?>
        <span class="es-legend-tip">Click any day to manage</span>
    </div>

    <div class="es-card es-cal-card">
        <div class="es-cal-toolbar">
            <h2 id="es-cal-title">—</h2>
            <div class="es-cal-nav">
                <button type="button" class="es-btn es-btn-ghost" id="es-cal-prev"><span class="dashicons dashicons-arrow-left-alt2"></span></button>
                <button type="button" class="es-btn es-btn-ghost" id="es-cal-today">Today</button>
                <button type="button" class="es-btn es-btn-ghost" id="es-cal-next"><span class="dashicons dashicons-arrow-right-alt2"></span></button>
            </div>
        </div>
        <div id="es-calview" data-year="<?php echo (int) current_time( 'Y' ); ?>" data-month="<?php echo (int) current_time( 'n' ); ?>">
            <div class="es-loading">Loading…</div>
        </div>
    </div>
</div>

<!-- Add/Edit Slot Modal (Calendar — v3.5.0: assigns users for 1to1/group, creates Zoom + bookings directly) -->
<div class="es-modal" id="es-slot-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card">
        <div class="es-modal-head">
            <h2 id="es-slot-modal-title">Add Availability Slot</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">
            <input type="hidden" id="es-slot-id" value="" />
            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Date</label>
                    <input type="date" id="es-slot-date" />
                </div>
                <div class="es-field">
                    <label class="es-label">Slot Type</label>
                    <select id="es-slot-type">
                        <?php foreach ( ES_Helpers::slot_types() as $k => $info ) : ?>
                            <option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $info['label'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Start Time</label>
                    <input type="time" id="es-slot-start" value="09:00" />
                </div>
                <div class="es-field">
                    <label class="es-label">Duration</label>
                    <select id="es-slot-duration">
                        <option value="15">15 min</option>
                        <option value="30">30 min</option>
                        <option value="45">45 min</option>
                        <option value="60" selected>1 hour</option>
                        <option value="90">1 hour 30 min</option>
                        <option value="120">2 hours</option>
                    </select>
                </div>
            </div>
            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Meeting Platform</label>
                    <select id="es-slot-platform">
                        <?php foreach ( ES_Helpers::platforms() as $p ) : ?>
                            <option value="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="es-field" id="es-slot-cap-field">
                    <label class="es-label">Capacity</label>
                    <input type="number" id="es-slot-capacity" min="1" max="999" value="1" />
                    <span class="es-helper" id="es-slot-cap-helper">1 = one-on-one</span>
                </div>
            </div>

            <!-- v3.5.0 — Direct meeting: assign user(s) -->
            <div class="es-field" id="es-slot-users-field">
                <label class="es-label">
                    Assign User(s)
                    <span class="es-helper" id="es-slot-users-mode-hint" style="margin-left:6px">— pick one user for 1:1</span>
                </label>
                <div class="es-user-picker">
                    <input type="text" id="es-slot-user-search" placeholder="Search by name or email…" autocomplete="off" />
                    <div class="es-user-results" id="es-slot-user-results"></div>
                </div>
                <div class="es-user-chips" id="es-slot-user-chips"></div>
                <input type="hidden" id="es-slot-user-ids" value="[]" />
                <span class="es-helper" style="margin-top:6px;display:block">
                    A Zoom meeting will be auto-created and a confirmation email sent to each assigned user.
                </span>
            </div>

            <!-- <div class="es-field">
                <label class="es-label">Title (Optional)</label>
                <input type="text" id="es-slot-title" placeholder="e.g. IELTS Speaking Practice" />
            </div>
            <div class="es-field">
                <label class="es-label">Notes (Optional)</label>
                <textarea id="es-slot-notes" rows="2" placeholder="e.g. Bring your essay draft"></textarea>
            </div> -->

            <label class="es-checkbox-row" id="es-slot-send-email-row">
                <input type="checkbox" id="es-slot-send-email" checked />
                <span>Send confirmation email to assigned user(s)</span>
            </label>

            <p class="es-helper" style="margin-top:14px">Time zone: <strong><?php echo esc_html( ES_Helpers::work_tz()->getName() ); ?></strong> (Work country: <?php echo esc_html( ES_Helpers::settings()['work_country'] ); ?>). Customers will see this time in their own timezone.</p>
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn" id="es-slot-save">Add Slot</button>
        </div>
    </div>
</div>

<!-- Day Detail Modal (drill-down) -->
<div class="es-modal" id="es-day-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card es-modal-lg">
        <div class="es-modal-head">
            <h2 id="es-day-modal-title">—</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body" id="es-day-modal-body">
            <div class="es-loading">Loading…</div>
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Close</button>
            <button type="button" class="es-btn" id="es-day-add-slot"><span class="dashicons dashicons-plus"></span>Schedule a meeting</button>
        </div>
    </div>
</div>

<!-- Manual Booking Modal (shared with Bookings page) -->
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
                <textarea id="es-mb-note" rows="3" placeholder="Any context for the user"></textarea>
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