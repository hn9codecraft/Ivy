(function ($) {
    'use strict';

    /* ============= UTILITIES ============= */
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }
    function formatTime12(hhmm) {
        if (!hhmm) return '';
        var parts = hhmm.split(':');
        var h = parseInt(parts[0], 10);
        var m = parts[1] || '00';
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if (h === 0) h = 12;
        return h + ':' + m + ' ' + ampm;
    }
    function calcEndTime(start, dur) {
        if (!start) return '';
        var p = start.split(':');
        var t = parseInt(p[0], 10) * 60 + parseInt(p[1], 10) + parseInt(dur, 10);
        t = ((t % 1440) + 1440) % 1440;
        return pad(Math.floor(t / 60)) + ':' + pad(t % 60);
    }
    function monthName(y, m) {
        var names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return names[m - 1] + ' ' + y;
    }

    function toast(msg, kind) {
        kind = kind || 'success';
        var $c = $('.es-admin-toast-container');
        if (!$c.length) $c = $('<div class="es-admin-toast-container"></div>').appendTo('body');
        var $t = $('<div class="es-admin-toast es-toast-' + kind + '">' + escapeHtml(msg) + '</div>').appendTo($c);
        setTimeout(function () { $t.fadeOut(200, function () { $t.remove(); }); }, 3500);
    }

    function confirmModal(message, detail, onConfirm) {
        $('.es-confirm-modal').remove();
        var html = '<div class="es-modal es-confirm-modal">' +
            '<div class="es-modal-backdrop"></div>' +
            '<div class="es-modal-card es-confirm-card">' +
                '<div class="es-modal-body">' +
                    '<div class="es-confirm-msg">' + escapeHtml(message) + '</div>' +
                    (detail ? '<div class="es-confirm-detail">' + escapeHtml(detail) + '</div>' : '') +
                '</div>' +
                '<div class="es-modal-foot">' +
                    '<button type="button" class="es-btn es-btn-ghost es-confirm-cancel">Cancel</button>' +
                    '<button type="button" class="es-btn es-btn-primary es-confirm-ok">Confirm</button>' +
                '</div>' +
            '</div></div>';
        var $m = $(html).appendTo('body');
        $m.find('.es-confirm-cancel, .es-modal-backdrop').on('click', function () { $m.remove(); });
        $m.find('.es-confirm-ok').on('click', function () {
            $m.remove();
            if (typeof onConfirm === 'function') onConfirm();
        });
    }

    /* ============= MAIN CALENDAR (Calendar page) ============= */
    function loadCalView() {
        var $w = $('#es-calview');
        if (!$w.length) return;
        var year = parseInt($w.data('year'), 10), month = parseInt($w.data('month'), 10);
        $w.html('<div class="es-loading">Loading…</div>');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_calendar_month',
            nonce: ES_ADMIN.nonce,
            year: year, month: month
        }).done(function (json) {
            if (json.success) renderCalView(json.data);
            else $w.html('<p class="es-loading">Failed to load.</p>');
        });
    }

    function renderCalView(d) {
        $('#es-cal-title').text(d.month_name);
        var DOW = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        var html = '<div class="es-calview-grid">';
        DOW.forEach(function (n) { html += '<div class="es-calview-dow">' + n + '</div>'; });

        for (var i = 0; i < d.first_weekday; i++) html += '<div class="es-calview-day is-empty"></div>';

        for (var dnum = 1; dnum <= d.days_in; dnum++) {
            var iso = d.year + '-' + pad(d.month) + '-' + pad(dnum);
            var slots = d.days[iso] || [];
            var cls = 'es-calview-day';
            if (iso < d.today) cls += ' is-past';
            if (iso === d.today) cls += ' is-today';

            html += '<button type="button" class="' + cls + '" data-iso="' + iso + '">';
            html += '<div class="day-num">' + dnum + '</div>';

            // Show up to 3 events, then "+N more"
            var maxShow = 3;
            for (var j = 0; j < Math.min(slots.length, maxShow); j++) {
                var s = slots[j];
                var label = s.start + (s.title ? ' ' + s.title : '');
                html += '<span class="es-event" style="--type-color:' + s.type_color + '" title="' + escapeHtml(s.type_label + ' · ' + s.start + '-' + s.end) + '">';
                html += escapeHtml(label.length > 22 ? label.slice(0, 22) + '…' : label);
                html += '</span>';
            }
            if (slots.length > maxShow) {
                html += '<span class="es-event-more">+' + (slots.length - maxShow) + ' more</span>';
            }
            html += '</button>';
        }
        html += '</div>';
        $('#es-calview').html(html);
    }

    $(document).on('click', '#es-cal-prev', function () {
        var $w = $('#es-calview');
        var y = parseInt($w.data('year'), 10), m = parseInt($w.data('month'), 10);
        m--; if (m < 1) { m = 12; y--; }
        $w.data('year', y).data('month', m); loadCalView();
    });
    $(document).on('click', '#es-cal-next', function () {
        var $w = $('#es-calview');
        var y = parseInt($w.data('year'), 10), m = parseInt($w.data('month'), 10);
        m++; if (m > 12) { m = 1; y++; }
        $w.data('year', y).data('month', m); loadCalView();
    });
    $(document).on('click', '#es-cal-today', function () {
        var n = new Date();
        $('#es-calview').data('year', n.getFullYear()).data('month', n.getMonth() + 1);
        loadCalView();
    });

    /* Click day on main calendar → open day modal */
    $(document).on('click', '#es-calview .es-calview-day:not(.is-empty)', function () {
        var iso = $(this).data('iso');
        openDayModal(iso);
    });

    function openDayModal(iso) {
        var $modal = $('#es-day-modal');
        $('#es-day-modal-title').text('Slots for ' + iso);
        $modal.data('iso', iso);
        $('#es-day-modal-body').html('<div class="es-loading">Loading…</div>');
        $modal.show();

        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_day_slots',
            nonce: ES_ADMIN.nonce,
            date: iso
        }).done(function (json) {
            if (!json.success) { $('#es-day-modal-body').html('<p class="es-loading">Failed to load.</p>'); return; }
            renderDayModal(json.data);
        });
    }

    function renderDayModal(d) {
        var html = '';
        if (!d.slots.length) {
            html = '<p class="es-empty-text">No slots on this day. Add one →</p>';
        } else {
            d.slots.forEach(function (s) {
                var isFull = s.booked >= s.capacity;
                var isPersonal = s.type === 'personal';
                html += '<div class="es-slot-row" style="--type-color:' + s.type_color + '">';
                html += '<div class="es-slot-time">' + escapeHtml(s.start) + '</div>';
                html += '<div class="es-slot-info">';
                html += '<div class="es-slot-info-top">' + s.duration + ' min · ' + escapeHtml(s.platform);
                if (s.title) html += ' · ' + escapeHtml(s.title);
                html += '</div>';
                html += '<div class="es-slot-info-sub">' + escapeHtml(s.type_label) + ' · ' + s.booked + '/' + s.capacity + ' booked</div>';
                html += '</div>';
                html += '<span class="es-slot-status' + (isFull ? ' es-slot-status-full' : '') + '">' + (isFull ? 'FULL' : 'OPEN') + '</span>';
                html += '<div class="es-slot-actions">';
                if (!isFull && !isPersonal) {
                    html += '<button type="button" class="es-slot-book es-book-for-user" data-slot=\'' + JSON.stringify(s).replace(/'/g, '&#39;') + '\' title="Book for a user"><span class="dashicons dashicons-businessperson"></span></button>';
                }
                html += '<button type="button" class="es-slot-edit es-edit-slot" data-slot=\'' + JSON.stringify(s).replace(/'/g, '&#39;') + '\' title="Edit"><span class="dashicons dashicons-edit"></span></button>';
                html += '<button type="button" class="es-slot-del es-delete-slot" data-id="' + s.id + '" title="Delete">×</button>';
                html += '</div>';
                html += '</div>';
            });
        }
        $('#es-day-modal-body').html(html);
    }

    /* "+ Add Slot to This Day" inside day modal */
    $(document).on('click', '#es-day-add-slot', function () {
        var iso = $('#es-day-modal').data('iso');
        $('#es-day-modal').hide();
        openSlotModal({ slot_date: iso });
    });

    /* ============= SLOT MODAL (shared between Calendar & My Slots) ============= */
    // v3.5.0 — On the Calendar page, the slot modal can ALSO assign users for direct meetings.
    // We detect "Calendar page" by presence of #es-calview in the DOM.

    var pickedUsers = []; // [{id, name, email}] — current assignment in the slot modal
    var userSearchTimer;

    function isCalendarPage() {
        return $('#es-calview').length > 0;
    }

    function renderUserChips() {
        var $chips = $('#es-slot-user-chips');
        if (!$chips.length) return;
        $chips.empty();
        pickedUsers.forEach(function (u) {
            $chips.append(
                '<span class="es-user-chip">' +
                    escapeHtml(u.name) +
                    ' <button type="button" class="es-user-chip-x" data-id="' + u.id + '" aria-label="Remove">×</button>' +
                '</span>'
            );
        });
        $('#es-slot-user-ids').val(JSON.stringify(pickedUsers.map(function (u) { return u.id; })));
    }

    function updateUserPickerVisibility() {
        var $field = $('#es-slot-users-field');
        if (!$field.length) return;
        var type   = $('#es-slot-type').val();
        var isEdit = !!$('#es-slot-id').val();

        // Show user picker ONLY on Calendar page, only when creating (not editing), only for 1to1 / group
        if (isCalendarPage() && !isEdit && (type === '1to1' || type === 'group')) {
            $field.show();
            $('#es-slot-send-email-row').show();
            if (type === '1to1') {
                $('#es-slot-users-mode-hint').text('— pick exactly one user for 1:1');
                if (pickedUsers.length > 1) { pickedUsers = pickedUsers.slice(0, 1); renderUserChips(); }
            } else {
                $('#es-slot-users-mode-hint').text('— pick one or more users');
            }
        } else {
            $field.hide();
            $('#es-slot-send-email-row').hide();
        }
    }

    function openSlotModal(prefill) {
        prefill = prefill || {};
        var $modal = $('#es-slot-modal');
        var isEdit = !!prefill.id;

        $('#es-slot-modal-title').text(isEdit ? 'Edit Availability Slot' : 'Add Availability Slot');
        $('#es-slot-save').text(isEdit ? 'Save Changes' : 'Add Slot');
        $('#es-slot-id').val(prefill.id || '');
        $('#es-slot-date').val(prefill.slot_date || prefill.date || ES_ADMIN.today);
        $('#es-slot-type').val(prefill.slot_type || prefill.type || '1to1');
        $('#es-slot-start').val(prefill.start_time || prefill.start || '09:00');
        $('#es-slot-duration').val(prefill.duration_min || prefill.duration || 60);
        $('#es-slot-platform').val(prefill.platform || (ES_ADMIN.platforms[0] || 'Zoom'));
        $('#es-slot-capacity').val(prefill.capacity || 1);
        $('#es-slot-title').val(prefill.title || '');
        $('#es-slot-notes').val(prefill.notes || '');

        // Reset user picker
        pickedUsers = [];
        renderUserChips();
        $('#es-slot-user-search').val('');
        $('#es-slot-user-results').empty().hide();

        // Update field state based on type
        updateCapacityField();
        updateUserPickerVisibility();

        $modal.show();
        setTimeout(function () { $('#es-slot-date').focus(); }, 50);
    }

    function updateCapacityField() {
        var type = $('#es-slot-type').val();
        var $cap = $('#es-slot-capacity');
        var $help = $('#es-slot-cap-helper');
        if (type === '1to1') {
            $cap.val(1).prop('disabled', true);
            $help.text('1:1 calls are always capacity 1');
        } else if (type === 'personal') {
            $cap.val(1).prop('disabled', true);
            $help.text('Personal slots aren\'t bookable');
        } else if (type === 'group') {
            $cap.prop('disabled', false);
            if (parseInt($cap.val(), 10) < 2) $cap.val(5);
            $help.text('How many people can join');
        } else {
            $cap.prop('disabled', false);
            $help.text('Maximum bookings allowed');
        }
    }

    $(document).on('change', '#es-slot-type', function () {
        updateCapacityField();
        updateUserPickerVisibility();
    });

    /* User picker on slot modal — search */
    $(document).on('input', '#es-slot-user-search', function () {
        var $inp = $(this);
        var q = $inp.val().trim();
        var $r = $('#es-slot-user-results');
        clearTimeout(userSearchTimer);
        if (q.length < 2) { $r.empty().hide(); return; }
        userSearchTimer = setTimeout(function () {
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_search_users',
                nonce: ES_ADMIN.nonce,
                q: q
            }).done(function (json) {
                if (!json || !json.success) return;
                var users = json.data.users || [];
                $r.empty();
                if (!users.length) {
                    $r.append('<div class="es-user-result"><em>No users found</em></div>');
                } else {
                    users.forEach(function (u) {
                        if (pickedUsers.some(function (p) { return p.id == u.id; })) return;
                        var $row = $(
                            '<div class="es-user-result" data-id="' + u.id + '">' +
                                '<div>' + escapeHtml(u.name) + '</div>' +
                                '<div class="es-user-result-email">' + escapeHtml(u.email) + '</div>' +
                            '</div>'
                        );
                        $row.data('user', u);
                        $r.append($row);
                    });
                }
                $r.show();
            });
        }, 220);
    });

    $(document).on('click', '#es-slot-user-results .es-user-result[data-id]', function () {
        var u = $(this).data('user');
        if (!u) return;
        var type = $('#es-slot-type').val();
        if (type === '1to1' && pickedUsers.length >= 1) {
            toast('1:1 meetings can have only one user.', 'danger');
            return;
        }
        pickedUsers.push({ id: u.id, name: u.name, email: u.email });
        renderUserChips();
        $('#es-slot-user-search').val('');
        $('#es-slot-user-results').empty().hide();
    });

    $(document).on('click', '.es-user-chip-x', function () {
        var id = $(this).data('id');
        pickedUsers = pickedUsers.filter(function (u) { return u.id != id; });
        renderUserChips();
    });

    // Click outside the search results closes them
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#es-slot-user-search, #es-slot-user-results').length) {
            $('#es-slot-user-results').hide();
        }
    });

    /* Save slot — v3.5.0: branches to "create_meeting" when users are assigned on Calendar */
    $(document).on('click', '#es-slot-save', function () {
        var $btn = $(this);
        var id = $('#es-slot-id').val();
        var type = $('#es-slot-type').val();
        var assignUsers = isCalendarPage() && !id && (type === '1to1' || type === 'group') && pickedUsers.length > 0;

        var data = {
            nonce: ES_ADMIN.nonce,
            id: id,
            slot_date: $('#es-slot-date').val(),
            start_time: $('#es-slot-start').val(),
            duration_min: $('#es-slot-duration').val(),
            slot_type: type,
            capacity: $('#es-slot-capacity').val(),
            platform: $('#es-slot-platform').val(),
            title: $('#es-slot-title').val(),
            notes: $('#es-slot-notes').val(),
        };

        if (!data.slot_date || !data.start_time) {
            toast('Please fill in date and start time.', 'danger');
            return;
        }

        // On Calendar, when 1to1/group is chosen but no users picked, require it
        if (isCalendarPage() && !id && (type === '1to1' || type === 'group') && pickedUsers.length === 0) {
            toast('Please assign at least one user (or switch to "Open Slot" / "Personal").', 'danger');
            return;
        }
        if (type === '1to1' && pickedUsers.length > 1) {
            toast('1:1 meetings can have only one user.', 'danger');
            return;
        }

        if (assignUsers) {
            data.action     = 'es_admin_create_meeting';
            data.user_ids   = $('#es-slot-user-ids').val();
            data.send_email = $('#es-slot-send-email').is(':checked') ? 1 : 0;
        } else {
            data.action = id ? 'es_admin_update_slot' : 'es_admin_create_slot';
        }

        $btn.prop('disabled', true).text(id ? 'Saving…' : (assignUsers ? 'Creating Meeting…' : 'Adding…'));
        $.post(ES_ADMIN.ajax_url, data).done(function (json) {
            $btn.prop('disabled', false).text(id ? 'Save Changes' : 'Add Slot');
            if (json.success) {
                $('#es-slot-modal').hide();
                toast(json.data.message);
                refreshAll();
            } else {
                toast(json.data.message || 'Save failed', 'danger');
            }
        }).fail(function () {
            $btn.prop('disabled', false).text(id ? 'Save Changes' : 'Add Slot');
            toast('Server error', 'danger');
        });
    });

    /* Delete slot */
    $(document).on('click', '.es-delete-slot', function () {
        var id = $(this).data('id');
        confirmModal('Delete this slot?', 'All bookings on this slot will be cancelled and Zoom meetings deleted.', function () {
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_delete_slot',
                nonce: ES_ADMIN.nonce,
                id: id
            }).done(function (json) {
                if (json.success) { toast('Slot deleted.'); refreshAll(); }
                else toast(json.data.message || 'Delete failed', 'danger');
            });
        });
    });

    /* Edit slot */
    $(document).on('click', '.es-edit-slot', function () {
        var raw = $(this).attr('data-slot');
        if (!raw) return;
        var slot;
        try { slot = JSON.parse(raw.replace(/&#39;/g, "'")); } catch (e) { return; }
        $('#es-day-modal').hide();
        openSlotModal(slot);
    });

    /* "+ Add Slot" buttons on calendar/slots pages */
    $(document).on('click', '#es-cal-add-slot, #es-slots-add, #es-day-add', function () {
        var prefill = {};
        if (this.id === 'es-day-add') {
            // Use currently selected mini-cal date
            var iso = $('#es-mini-cal').data('selected') || ES_ADMIN.today;
            prefill.slot_date = iso;
        }
        openSlotModal(prefill);
    });

    /* ============= MY SLOTS PAGE ============= */
    function loadMiniCal() {
        var $w = $('#es-mini-cal');
        if (!$w.length) return;
        var year = parseInt($w.data('year'), 10), month = parseInt($w.data('month'), 10);
        $w.html('<div class="es-loading">Loading…</div>');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_calendar_month',
            nonce: ES_ADMIN.nonce,
            year: year, month: month
        }).done(function (json) {
            if (json.success) renderMiniCal(json.data);
        });
    }

    function renderMiniCal(d) {
        $('#es-mini-title').text(d.month_name);
        var DOW = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        var selected = $('#es-mini-cal').data('selected');
        var html = '<div class="es-calview-grid">';
        DOW.forEach(function (n) { html += '<div class="es-calview-dow">' + n + '</div>'; });
        for (var i = 0; i < d.first_weekday; i++) html += '<div class="es-calview-day is-empty"></div>';
        for (var dnum = 1; dnum <= d.days_in; dnum++) {
            var iso = d.year + '-' + pad(d.month) + '-' + pad(dnum);
            var slots = d.days[iso] || [];
            var cls = 'es-calview-day';
            if (iso < d.today) cls += ' is-past';
            if (iso === d.today) cls += ' is-today';
            if (iso === selected) cls += ' is-active';

            html += '<button type="button" class="' + cls + '" data-iso="' + iso + '">';
            html += '<div class="day-num">' + dnum + '</div>';
            if (slots.length) html += '<div class="day-meta">' + slots.length + ' open</div>';
            html += '</button>';
        }
        html += '</div>';
        $('#es-mini-cal').html(html);
    }

    $(document).on('click', '#es-mini-prev', function () {
        var $w = $('#es-mini-cal');
        var y = parseInt($w.data('year'), 10), m = parseInt($w.data('month'), 10);
        m--; if (m < 1) { m = 12; y--; }
        $w.data('year', y).data('month', m); loadMiniCal();
    });
    $(document).on('click', '#es-mini-next', function () {
        var $w = $('#es-mini-cal');
        var y = parseInt($w.data('year'), 10), m = parseInt($w.data('month'), 10);
        m++; if (m > 12) { m = 1; y++; }
        $w.data('year', y).data('month', m); loadMiniCal();
    });

    /* Click mini-cal day → load day's slots into left list */
    $(document).on('click', '#es-mini-cal .es-calview-day:not(.is-empty)', function () {
        var iso = $(this).data('iso');
        $('#es-mini-cal').data('selected', iso);
        $('#es-mini-cal .es-calview-day').removeClass('is-active');
        $(this).addClass('is-active');
        loadDayList(iso);
    });

    function loadDayList(iso) {
        $('#es-day-list-title').text('SLOTS FOR ' + new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).toUpperCase());
        $('#es-day-list').html('<div class="es-loading">Loading…</div>');

        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_day_slots',
            nonce: ES_ADMIN.nonce,
            date: iso
        }).done(function (json) {
            if (!json.success) { $('#es-day-list').html('<p class="es-empty-text">Failed to load.</p>'); return; }
            renderDayList(json.data);
        });
    }

    function renderDayList(d) {
        if (!d.slots.length) {
            $('#es-day-list').html('<p class="es-empty-text">No slots yet. Add one above.</p>');
            return;
        }
        var html = '';
        d.slots.forEach(function (s) {
            var isFull = s.booked >= s.capacity;
            var isPersonal = s.type === 'personal';
            html += '<div class="es-slot-row" style="--type-color:' + s.type_color + '">';
            html += '<div class="es-slot-time">' + escapeHtml(s.start) + '</div>';
            html += '<div class="es-slot-info">';
            html += '<div class="es-slot-info-top">' + s.duration + ' min · ' + escapeHtml(s.platform.toLowerCase()) + '</div>';
            html += '<div class="es-slot-info-sub">' + (s.title ? escapeHtml(s.title) : (s.notes ? escapeHtml(s.notes) : 'No note')) + '</div>';
            html += '</div>';
            html += '<span class="es-slot-status' + (isFull ? ' es-slot-status-full' : '') + '">' + (isFull ? 'FULL' : 'OPEN') + '</span>';
            html += '<div class="es-slot-actions">';
            if (!isFull && !isPersonal) {
                html += '<button type="button" class="es-slot-book es-book-for-user" data-slot=\'' + JSON.stringify(s).replace(/'/g, '&#39;') + '\' title="Book for a user"><span class="dashicons dashicons-businessperson"></span></button>';
            }
            html += '<button type="button" class="es-slot-edit es-edit-slot" data-slot=\'' + JSON.stringify(s).replace(/'/g, '&#39;') + '\' title="Edit"><span class="dashicons dashicons-edit"></span></button>';
            html += '<button type="button" class="es-slot-del es-delete-slot" data-id="' + s.id + '" title="Delete">×</button>';
            html += '</div>';
            html += '</div>';
        });
        $('#es-day-list').html(html);
    }

    /* All Defined Slots (right panel) */
    function loadAllSlots() {
        var $w = $('#es-all-slots');
        if (!$w.length) return;
        var upcomingOnly = $('#es-upcoming-only').is(':checked') ? 1 : 0;
        $w.html('<div class="es-loading">Loading…</div>');

        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_all_slots',
            nonce: ES_ADMIN.nonce,
            upcoming_only: upcomingOnly
        }).done(function (json) {
            if (!json.success) { $w.html('<p class="es-empty-text">Failed.</p>'); return; }
            renderAllSlots(json.data.slots);
        });
    }

    function renderAllSlots(slots) {
        if (!slots.length) {
            $('#es-all-slots').html('<p class="es-empty-text">No slots defined yet.</p>');
            return;
        }
        // Group by date
        var byDate = {};
        slots.forEach(function (s) {
            if (!byDate[s.date]) byDate[s.date] = [];
            byDate[s.date].push(s);
        });

        var html = '';
        Object.keys(byDate).sort().forEach(function (iso) {
            var dayslots = byDate[iso];
            var dt = new Date(iso);
            var dayNum = dt.getUTCDate();
            var monthShort = dt.toLocaleDateString('en-US', { month: 'short', timeZone: 'UTC' }).toUpperCase();

            var available = 0, booked = 0;
            dayslots.forEach(function (s) {
                available += Math.max(0, s.capacity - s.booked);
                booked += s.booked;
            });

            html += '<div class="es-defined-row">';
            html += '<div class="es-defined-date"><strong>' + dayNum + '</strong><span>' + monthShort + '</span></div>';
            html += '<div class="es-defined-info">';
            html += '<div class="es-defined-info-top">' + dayslots.length + ' slot' + (dayslots.length === 1 ? '' : 's') + '</div>';
            html += '<div class="es-defined-info-sub">' + available + ' available · ' + booked + ' booked</div>';
            html += '</div>';
            html += '<div class="es-defined-dots">';
            for (var i = 0; i < Math.min(dayslots.length, 6); i++) {
                var dotCls = dayslots[i].booked >= dayslots[i].capacity ? 'es-defined-dot es-defined-dot-booked' : 'es-defined-dot';
                html += '<span class="' + dotCls + '"></span>';
            }
            html += '</div>';
            html += '</div>';
        });

        $('#es-all-slots').html(html);
    }

    $(document).on('change', '#es-upcoming-only', loadAllSlots);

    /* Refresh both panels after a change */
    function refreshAll() {
        if ($('#es-calview').length) loadCalView();
        if ($('#es-mini-cal').length) loadMiniCal();
        if ($('#es-all-slots').length) loadAllSlots();
        if ($('#es-mini-cal').data('selected')) loadDayList($('#es-mini-cal').data('selected'));
    }

    /* ============= MODAL CLOSE ============= */
    $(document).on('click', '.es-modal-close, .es-modal-backdrop', function () {
        $(this).closest('.es-modal').hide();
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') $('.es-modal:visible').hide();
    });

    /* ============= MANUAL BOOKING ============= */
    var MB = { searchTimer: null, fixedSlot: null };

    function openManualBookModal(fixedSlot) {
        // Reset modal state
        $('#es-mb-user-search').val('');
        $('#es-mb-user-id').val('');
        $('#es-mb-user-results').empty().hide();
        $('#es-mb-user-selected').hide();
        $('#es-mb-note').val('');
        $('#es-mb-send-email').prop('checked', true);
        $('#es-mb-confirm').prop('disabled', false).text('Create Booking');

        if (fixedSlot) {
            // Slot already chosen — show fixed info
            MB.fixedSlot = fixedSlot;
            $('#es-mb-slot-field').hide();
            $('#es-mb-slot-fixed').show();
            $('#es-mb-title').text('Book Slot for User');
            var info = '<div class="es-mb-slot-info-box" style="--type-color:' + fixedSlot.type_color + '">' +
                '<div class="es-mb-slot-line1"><strong>' + escapeHtml(fixedSlot.date) + '</strong> · ' + escapeHtml(fixedSlot.start) + ' – ' + escapeHtml(fixedSlot.end) + ' (' + fixedSlot.duration + ' min)</div>' +
                '<div class="es-mb-slot-line2">' + escapeHtml(fixedSlot.type_label) + ' · ' + escapeHtml(fixedSlot.platform);
            if (fixedSlot.title) info += ' · ' + escapeHtml(fixedSlot.title);
            info += '</div></div>';
            $('.es-mb-slot-fixed-info').html(info);
        } else {
            // Free choice — load slot dropdown
            MB.fixedSlot = null;
            $('#es-mb-slot-field').show();
            $('#es-mb-slot-fixed').hide();
            $('#es-mb-title').text('Manual Booking');
            loadBookableSlots();
        }

        $('#es-manual-book-modal').show();
        setTimeout(function () { $('#es-mb-user-search').focus(); }, 50);
    }

    function loadBookableSlots() {
        var $sel = $('#es-mb-slot');
        $sel.html('<option value="">— Loading slots… —</option>').prop('disabled', true);

        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_bookable_slots',
            nonce: ES_ADMIN.nonce,
            date_from: ES_ADMIN.today
        }).done(function (json) {
            $sel.prop('disabled', false);
            if (!json.success || !json.data.slots.length) {
                $sel.html('<option value="">No upcoming slots available</option>');
                return;
            }
            // Group by date for grouped <optgroup>
            var byDate = {};
            json.data.slots.forEach(function (s) {
                if (!byDate[s.date]) byDate[s.date] = [];
                byDate[s.date].push(s);
            });
            var html = '<option value="">— Select a slot —</option>';
            Object.keys(byDate).sort().forEach(function (date) {
                var d = new Date(date + 'T00:00:00');
                var label = d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                html += '<optgroup label="' + escapeHtml(label) + '">';
                byDate[date].forEach(function (s) {
                    var disabled = s.is_full ? ' disabled' : '';
                    var status = s.is_full ? ' (FULL)' : (s.capacity > 1 ? ' (' + s.remaining + '/' + s.capacity + ' left)' : '');
                    html += '<option value="' + s.id + '"' + disabled + '>' + escapeHtml(s.start) + ' – ' + escapeHtml(s.end) + ' · ' + escapeHtml(s.type_label) + ' · ' + escapeHtml(s.platform) + status + '</option>';
                });
                html += '</optgroup>';
            });
            $sel.html(html);
        }).fail(function () {
            $sel.html('<option value="">Failed to load slots</option>');
        });
    }

    /* User search (autocomplete) */
    $(document).on('input', '#es-mb-user-search', function () {
        var q = $(this).val().trim();
        clearTimeout(MB.searchTimer);
        if (q.length < 2) {
            $('#es-mb-user-results').empty().hide();
            return;
        }
        MB.searchTimer = setTimeout(function () { runUserSearch(q); }, 250);
    });

    function runUserSearch(q) {
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_search_users',
            nonce: ES_ADMIN.nonce,
            q: q
        }).done(function (json) {
            if (!json.success) return;
            renderUserResults(json.data.users);
        });
    }

    function renderUserResults(users) {
        var $r = $('#es-mb-user-results');
        if (!users.length) {
            $r.html('<div class="es-user-result es-user-result-empty">No users found. Ask them to register first.</div>').show();
            return;
        }
        var html = '';
        users.forEach(function (u) {
            html += '<div class="es-user-result" data-user-id="' + u.id + '" data-user-name="' + escapeHtml(u.name) + '" data-user-email="' + escapeHtml(u.email) + '">';
            html += '<div class="es-user-result-name">' + escapeHtml(u.name) + (u.is_admin ? ' <span class="es-user-admin-tag">admin</span>' : '') + '</div>';
            html += '<div class="es-user-result-email">' + escapeHtml(u.email) + '</div>';
            html += '</div>';
        });
        $r.html(html).show();
    }

    /* Pick a user */
    $(document).on('click', '.es-user-result[data-user-id]', function () {
        var id = $(this).data('user-id');
        var name = $(this).data('user-name');
        var email = $(this).data('user-email');
        $('#es-mb-user-id').val(id);
        $('#es-mb-user-search').hide();
        $('#es-mb-user-results').hide();
        $('#es-mb-user-selected').show()
            .find('.es-user-selected-info').html('<strong>' + escapeHtml(name) + '</strong> · ' + escapeHtml(email));
    });

    /* Clear user */
    $(document).on('click', '.es-user-selected-clear', function () {
        $('#es-mb-user-id').val('');
        $('#es-mb-user-selected').hide();
        $('#es-mb-user-search').val('').show().focus();
        $('#es-mb-user-results').empty().hide();
    });

    /* Hide results when clicking outside */
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.es-user-picker').length) {
            $('#es-mb-user-results').hide();
        }
    });

    /* Confirm manual booking */
    $(document).on('click', '#es-mb-confirm', function () {
        var $btn = $(this);
        var userId = parseInt($('#es-mb-user-id').val(), 10) || 0;
        var slotId = MB.fixedSlot ? MB.fixedSlot.id : (parseInt($('#es-mb-slot').val(), 10) || 0);
        var note = $('#es-mb-note').val();
        var sendEmail = $('#es-mb-send-email').is(':checked') ? 1 : 0;

        if (!userId) { toast('Please select a user.', 'danger'); return; }
        if (!slotId) { toast('Please select a slot.', 'danger'); return; }

        $btn.prop('disabled', true).text('Creating…');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_create_booking',
            nonce: ES_ADMIN.nonce,
            slot_id: slotId,
            user_id: userId,
            note: note,
            send_email: sendEmail
        }).done(function (json) {
            $btn.prop('disabled', false).text('Create Booking');
            if (json.success) {
                $('#es-manual-book-modal').hide();
                toast(json.data.message);
                // Refresh whatever's on screen
                refreshAll();
                // If on bookings page, reload
                if (window.location.href.indexOf('eduschedule-bookings') !== -1) {
                    setTimeout(function () { window.location.reload(); }, 600);
                }
            } else {
                toast(json.data.message || 'Failed', 'danger');
            }
        }).fail(function () {
            $btn.prop('disabled', false).text('Create Booking');
            toast('Server error', 'danger');
        });
    });

    /* Open manual book — from Bookings page button */
    $(document).on('click', '#es-manual-book-btn', function () {
        openManualBookModal(null);
    });

    /* Open manual book — from a slot's "Book for User" icon */
    $(document).on('click', '.es-book-for-user', function () {
        var raw = $(this).attr('data-slot');
        if (!raw) return;
        var slot;
        try { slot = JSON.parse(raw.replace(/&#39;/g, "'")); } catch (e) { return; }
        // Close any open day-modal first
        $('#es-day-modal').hide();
        openManualBookModal(slot);
    });

    /* ============= INIT ============= */
    $(function () {
        if ($('#es-calview').length) loadCalView();
        if ($('#es-mini-cal').length) {
            loadMiniCal();
            // Auto-select today
            $('#es-mini-cal').data('selected', ES_ADMIN.today);
            loadDayList(ES_ADMIN.today);
        }
        if ($('#es-all-slots').length) loadAllSlots();
    });

    /* ============= v3.5.0 — STUDENTS PAGE ============= */

    function escapeAttr(s) { return escapeHtml(s); }

    // Open Add Student modal
    $(document).on('click', '#es-add-student-btn', function () {
        $('#es-st-first, #es-st-last, #es-st-email, #es-st-phone, #es-st-parent, #es-st-comment').val('');
        $('#es-st-ref').val('');
        $('#es-st-send-email').prop('checked', true);
        $('#es-st-save').prop('disabled', false).text('Add Student');
        $('#es-add-student-modal').show();
        setTimeout(function () { $('#es-st-first').focus(); }, 50);
    });

    // Submit Add Student
    $(document).on('click', '#es-st-save', function () {
        var $btn = $(this);
        var data = {
            action:      'es_admin_add_student',
            nonce:       ES_ADMIN.nonce,
            first_name:  $('#es-st-first').val().trim(),
            last_name:   $('#es-st-last').val().trim(),
            email:       $('#es-st-email').val().trim(),
            phone:       $('#es-st-phone').val().trim(),
            parent_name: $('#es-st-parent').val().trim(),
            reference:   $('#es-st-ref').val(),
            comment:     $('#es-st-comment').val().trim(),
            send_email:  $('#es-st-send-email').is(':checked') ? 1 : 0
        };
        if (!data.first_name) { toast('First name is required.', 'danger'); return; }
        if (!data.email)      { toast('Email is required.', 'danger'); return; }

        $btn.prop('disabled', true).text('Saving…');
        $.post(ES_ADMIN.ajax_url, data).done(function (json) {
            if (json && json.success) {
                toast(json.data.message);
                $('#es-add-student-modal').hide();
                setTimeout(function () { window.location.reload(); }, 600);
            } else {
                $btn.prop('disabled', false).text('Add Student');
                toast((json && json.data && json.data.message) || 'Could not add student.', 'danger');
            }
        }).fail(function () {
            $btn.prop('disabled', false).text('Add Student');
            toast('Server error', 'danger');
        });
    });

    // v3.5.1 — Student Details now opens as a full page (?view=detail&user_id=X),
    // no longer a modal. The .es-student-details handler has been removed.

})(jQuery);
