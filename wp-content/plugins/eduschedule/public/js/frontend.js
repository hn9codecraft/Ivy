(function ($) {
    'use strict';

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function toast(msg, kind) {
        kind = kind || 'success';
        var $c = $('.es-fe-toast-container');
        if (!$c.length) $c = $('<div class="es-fe-toast-container"></div>').appendTo('body');
        var $t = $('<div class="es-fe-toast es-toast-' + kind + '">' + escapeHtml(msg) + '</div>').appendTo($c);
        setTimeout(function () { $t.fadeOut(200, function () { $t.remove(); }); }, 3500);
    }

    /* ============= PASSWORD SHOW/HIDE ============= */
    $(document).on('click', '.es-fe-eye', function () {
        var $inp = $(this).siblings('input');
        var t = $inp.attr('type') === 'password' ? 'text' : 'password';
        $inp.attr('type', t);
        $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
    });

    /* ============= AUTH TOGGLE (Login <-> Register on same page) ============= */
    $(document).on('click', '.es-auth-toggle', function (e) {
        e.preventDefault();
        var target = $(this).data('target'); // 'login' or 'register'
        var $wrap = $(this).closest('.es-auth-combined');
        if (!$wrap.length) return;
        $wrap.find('.es-auth-view').hide();
        $wrap.find('.es-auth-view-' + target).fadeIn(180);
        // Clear any old messages
        $wrap.find('.es-fe-msg').hide().text('');
        // Focus first input in the visible view
        setTimeout(function () {
            $wrap.find('.es-auth-view-' + target + ' input:visible').first().trigger('focus');
        }, 200);
    });

    /* ============= LOGIN ============= */
    $(document).on('submit', '#es-login-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $('#es-login-msg').hide();

        // Pull nonce from hidden field if present (for [eduschedule_auth] form), else fall back to localized JS nonce
        var nonce = $form.find('[name="es_login_nonce_field"]').val() || ES_FE.login_nonce || ES_FE.nonce;

        var data = {
            action: 'es_login',
            nonce: nonce,
            email: $form.find('[name="email"]').val(),
            password: $form.find('[name="password"]').val(),
            remember: $form.find('[name="remember"]').is(':checked') ? 1 : 0,
            // Tell the server "stay on this page after login"
            stay_url: (typeof ES_FE !== 'undefined' && ES_FE.current_url) ? ES_FE.current_url : window.location.href,
        };

        $btn.prop('disabled', true).text('Logging in…');
        $.post(ES_FE.ajax_url, data).done(function (json) {
            if (json && json.success) {
                $msg.removeClass('es-msg-error').addClass('es-msg-success').text(json.data.message).show();
                // If the login happened inside the packages modal, reload the
                // SAME page so the payment form appears (instead of redirecting
                // to the dashboard).
                if ($form.find('[name="es_login_reload"]').length) {
                    setTimeout(function () { window.location.reload(); }, 500);
                } else {
                    setTimeout(function () { window.location.href = json.data.redirect; }, 400);
                }
            } else {
                $btn.prop('disabled', false).text('Log in →');
                $msg.removeClass('es-msg-success').addClass('es-msg-error').text((json && json.data && json.data.message) ? json.data.message : 'Login failed').show();
            }
        }).fail(function () {
            $btn.prop('disabled', false).text('Log in →');
            $msg.addClass('es-msg-error').text('Server error. Try again.').show();
        });
    });

    /* ============= FORGOT PASSWORD — request reset link ============= */
    $(document).on('submit', '#es-lostpw-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $('#es-lostpw-msg').hide();
        var nonce = ES_FE.login_nonce || ES_FE.nonce;

        $btn.prop('disabled', true).text('Sending…');
        $.post(ES_FE.ajax_url, {
            action: 'es_lost_password',
            nonce: nonce,
            email: $form.find('[name="email"]').val()
        }).done(function (json) {
            if (json && json.success) {
                $msg.removeClass('es-msg-error').addClass('es-msg-success').text(json.data.message).show();
                $form.find('input[name="email"]').val('');
                $btn.prop('disabled', false).text('Send reset link →');
            } else {
                $btn.prop('disabled', false).text('Send reset link →');
                $msg.removeClass('es-msg-success').addClass('es-msg-error').text((json && json.data && json.data.message) ? json.data.message : 'Could not send the reset link.').show();
            }
        }).fail(function () {
            $btn.prop('disabled', false).text('Send reset link →');
            $msg.addClass('es-msg-error').text('Server error. Try again.').show();
        });
    });

    /* ============= RESET PASSWORD — set a new password ============= */
    $(document).on('submit', '#es-resetpw-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $('#es-resetpw-msg').hide();
        var nonce = ES_FE.login_nonce || ES_FE.nonce;

        var pw  = $form.find('[name="password"]').val();
        var pw2 = $form.find('[name="password_confirm"]').val();
        if (!pw || pw.length < 6) {
            $msg.removeClass('es-msg-success').addClass('es-msg-error').text('Password must be at least 6 characters.').show();
            return;
        }
        if (pw !== pw2) {
            $msg.removeClass('es-msg-success').addClass('es-msg-error').text('The two passwords do not match.').show();
            return;
        }

        $btn.prop('disabled', true).text('Saving…');
        $.post(ES_FE.ajax_url, {
            action: 'es_reset_password',
            nonce: nonce,
            key: $form.find('[name="key"]').val(),
            login: $form.find('[name="login"]').val(),
            password: pw,
            password_confirm: pw2
        }).done(function (json) {
            if (json && json.success) {
                $msg.removeClass('es-msg-error').addClass('es-msg-success').text(json.data.message + ' Redirecting…').show();
                setTimeout(function () { window.location.href = json.data.login_url; }, 1200);
            } else {
                $btn.prop('disabled', false).text('Set new password →');
                $msg.removeClass('es-msg-success').addClass('es-msg-error').text((json && json.data && json.data.message) ? json.data.message : 'Could not reset password.').show();
            }
        }).fail(function () {
            $btn.prop('disabled', false).text('Set new password →');
            $msg.addClass('es-msg-error').text('Server error. Try again.').show();
        });
    });

    $(document).on('submit', '#es-register-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $('#es-register-msg').hide();

        // Pull nonce from hidden field if present, else fall back to localized JS nonce
        var nonce = $form.find('[name="es_register_nonce_field"]').val() || ES_FE.register_nonce || ES_FE.nonce;

        var data = {
            action: 'es_register',
            nonce: nonce,
            first_name: $form.find('[name="first_name"]').val(),
            last_name: $form.find('[name="last_name"]').val(),
            email: $form.find('[name="email"]').val(),
            phone: $form.find('[name="phone"]').val(),
            country: $form.find('[name="country"]').val(),
            password: $form.find('[name="password"]').val(),
            confirm_password: $form.find('[name="confirm_password"]').val(),
            // Stay on the same page after register
            stay_url: (typeof ES_FE !== 'undefined' && ES_FE.current_url) ? ES_FE.current_url : window.location.href,
        };

        if (!data.country) {
            $msg.addClass('es-msg-error').text('Please select your country.').show();
            return;
        }

        if ((data.password || '').length < 8) {
            $msg.removeClass('es-msg-success').addClass('es-msg-error').text('Password must be at least 8 characters.').show();
            return;
        }

        if (data.password !== data.confirm_password) {
            $msg.removeClass('es-msg-success').addClass('es-msg-error').text('Passwords do not match.').show();
            return;
        }

        $btn.prop('disabled', true).text('Creating account…');
        $.post(ES_FE.ajax_url, data).done(function (json) {
            if (json && json.success) {
                $msg.removeClass('es-msg-error').addClass('es-msg-success').text(json.data.message).show();
                setTimeout(function () { window.location.href = json.data.redirect; }, 600);
            } else {
                $btn.prop('disabled', false).text('Create Account →');
                $msg.removeClass('es-msg-success').addClass('es-msg-error').text((json && json.data && json.data.message) ? json.data.message : 'Registration failed').show();
            }
        }).fail(function () {
            $btn.prop('disabled', false).text('Create Account →');
            $msg.addClass('es-msg-error').text('Server error. Try again.').show();
        });
    });

    /* ============= LOGOUT ============= */
    $(document).on('click', '#es-logout-btn', function () {
        $.post(ES_FE.ajax_url, { action: 'es_logout', nonce: ES_FE.nonce }).done(function (json) {
            if (json.success) window.location.href = json.data.redirect;
        });
    });

    /* ============= DASHBOARD CALENDAR ============= */
    var FeState = { date: null, slot: null };

    function loadFeCalendar() {
        var $w = $('#es-fe-calview');
        if (!$w.length) return;
        var year = parseInt($w.data('year'), 10), month = parseInt($w.data('month'), 10);
        $w.html('<div class="es-loading">Loading…</div>');
        $.post(ES_FE.ajax_url, {
            action: 'es_get_calendar_month',
            nonce: ES_FE.nonce,
            year: year, month: month
        }).done(function (json) {
            if (json.success) renderFeCalendar(json.data);
            else $w.html('<p class="es-loading">Failed to load.</p>');
        });
    }

    function renderFeCalendar(d) {
        $('#es-fe-cal-title').text(d.month_name);
        var DOW = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        var html = '<div class="es-fe-calview-grid">';
        DOW.forEach(function (n) { html += '<div class="es-fe-calview-dow">' + n + '</div>'; });
        for (var i = 0; i < d.first_weekday; i++) html += '<div class="es-fe-calview-day is-empty"></div>';
        for (var dnum = 1; dnum <= d.days_in; dnum++) {
            var iso = d.year + '-' + pad(d.month) + '-' + pad(dnum);
            var info = d.days[iso];
            var open = info ? info.open : 0;
            var cls = 'es-fe-calview-day';
            if (iso < d.today) cls += ' is-past';
            if (iso === d.today) cls += ' is-today';
            if (open > 0) cls += ' has-open';
            if (FeState.date === iso) cls += ' is-active';

            html += '<button type="button" class="' + cls + '" data-iso="' + iso + '">';
            html += '<div class="day-num">' + dnum + '</div>';
            if (open > 0) html += '<div class="day-meta">' + open + ' open</div>';
            html += '</button>';
        }
        html += '</div>';
        $('#es-fe-calview').html(html);
    }

    $(document).on('click', '#es-fe-cal-prev', function () {
        var $w = $('#es-fe-calview');
        var y = parseInt($w.data('year'), 10), m = parseInt($w.data('month'), 10);
        m--; if (m < 1) { m = 12; y--; }
        $w.data('year', y).data('month', m); loadFeCalendar();
    });
    $(document).on('click', '#es-fe-cal-next', function () {
        var $w = $('#es-fe-calview');
        var y = parseInt($w.data('year'), 10), m = parseInt($w.data('month'), 10);
        m++; if (m > 12) { m = 1; y++; }
        $w.data('year', y).data('month', m); loadFeCalendar();
    });
    $(document).on('click', '#es-fe-cal-today', function () {
        var n = new Date();
        $('#es-fe-calview').data('year', n.getFullYear()).data('month', n.getMonth() + 1);
        loadFeCalendar();
    });

    /* Click a day → load slots into side panel */
    $(document).on('click', '.es-fe-calview-day:not(.is-empty):not(.is-past)', function () {
        var iso = $(this).data('iso');
        FeState.date = iso;
        $('.es-fe-calview-day').removeClass('is-active');
        $(this).addClass('is-active');
        loadFeDaySlots(iso);
    });

    function loadFeDaySlots(iso) {
        $('#es-fe-side').html('<div class="es-loading">Loading slots…</div>');
        $.post(ES_FE.ajax_url, { action: 'es_get_slot', nonce: ES_FE.nonce, date: iso })
            .done(function (json) {
                if (!json.success) { $('#es-fe-side').html('<p class="es-loading">Failed.</p>'); return; }
                renderFeDaySlots(json.data);
            });
    }

    function renderFeDaySlots(d) {
        var html = '<h4>Slots for ' + escapeHtml(d.date) + '</h4>';
        if (!d.slots.length) {
            html += '<div class="es-fe-side-empty"><p>No slots on this day.</p></div>';
        } else {
            d.slots.forEach(function (s) {
                var disabled = s.is_past || s.is_full || s.already_booked;
                var cls = 'es-fe-slot' + (disabled ? ' is-disabled' : '');
                var statusText = '';
                if (s.is_past) statusText = 'Past';
                else if (s.already_booked) statusText = 'Already booked';
                else if (s.is_full) statusText = 'Full';
                else statusText = 'Book →';

                html += '<div class="' + cls + '" data-slot-id="' + s.id + '">';
                html += '<div class="es-fe-slot-head">';
                html += '<div class="es-fe-slot-time">' + escapeHtml(s.start_user) + '</div>';
                html += '<span class="es-tag" style="background:' + s.type_color + '20;color:' + s.type_color + '">' + escapeHtml(s.type_label) + '</span>';
                html += '</div>';
                html += '<div class="es-fe-slot-meta">' + s.duration + ' min · ' + escapeHtml(s.platform);
                if (s.title) html += ' · ' + escapeHtml(s.title);
                html += '</div>';
                if (s.notes) html += '<div class="es-fe-slot-title">' + escapeHtml(s.notes) + '</div>';
                html += '<div class="es-fe-slot-status">';
                html += '<span class="es-fe-slot-cap">' + s.booked + '/' + s.capacity + ' booked</span>';
                html += '<span class="es-fe-slot-cta">' + statusText + '</span>';
                html += '</div>';
                html += '</div>';
            });
        }
        $('#es-fe-side').html(html);
    }

    /* Click slot → open booking confirm modal */
    $(document).on('click', '.es-fe-slot:not(.is-disabled)', function () {
        var slotId = $(this).data('slot-id');
        if (!ES_FE.is_logged) {
            window.location.href = ES_FE.login_url;
            return;
        }
        // Need slot details — use the data we have in state
        $.post(ES_FE.ajax_url, { action: 'es_get_slot', nonce: ES_FE.nonce, date: FeState.date })
            .done(function (json) {
                if (!json.success) { toast('Could not load slot.', 'danger'); return; }
                var slot = json.data.slots.filter(function (s) { return s.id === slotId; })[0];
                if (!slot) { toast('Slot not found.', 'danger'); return; }
                openBookConfirm(slot);
            });
    });

    function openBookConfirm(slot) {
        FeState.slot = slot;
        var html = '<div style="margin-bottom:14px"><strong style="display:block;color:#e91e63;font-size:11px;letter-spacing:1px;margin-bottom:4px">DATE &amp; TIME</strong>' +
                   escapeHtml(slot.date) + ' · <strong>' + escapeHtml(slot.start_user) + ' – ' + escapeHtml(slot.end_user) + '</strong></div>';
        html += '<div style="margin-bottom:14px"><strong style="display:block;color:#e91e63;font-size:11px;letter-spacing:1px;margin-bottom:4px">TYPE &amp; PLATFORM</strong>' +
                escapeHtml(slot.type_label) + ' on ' + escapeHtml(slot.platform) + ' · ' + slot.duration + ' min</div>';
        if (slot.title) html += '<div style="margin-bottom:14px"><strong style="display:block;color:#e91e63;font-size:11px;letter-spacing:1px;margin-bottom:4px">TOPIC</strong>' + escapeHtml(slot.title) + '</div>';
        html += '<div class="es-fe-field"><label class="es-fe-label">Anything you want the host to know? (optional)</label><textarea id="es-book-note" rows="3"></textarea></div>';
        $('#es-fe-book-body').html(html);
        $('#es-fe-book-modal').show();
    }

    $(document).on('click', '#es-fe-book-confirm', function () {
        var $btn = $(this);
        var note = $('#es-book-note').val();
        if (!FeState.slot) return;
        $btn.prop('disabled', true).text('Booking…');
        $.post(ES_FE.ajax_url, {
            action: 'es_book_slot',
            nonce: ES_FE.nonce,
            slot_id: FeState.slot.id,
            note: note,
        }).done(function (json) {
            $btn.prop('disabled', false).text('Confirm Booking');
            if (json && json.success) {
                $('#es-fe-book-modal').hide();
                toast('Booking confirmed! Check your email for details.');
                setTimeout(function () { window.location.reload(); }, 800);
            } else {
                toast((json && json.data && json.data.message) ? json.data.message : 'Booking failed.', 'danger');
            }
        }).fail(function (xhr) {
            $btn.prop('disabled', false).text('Confirm Booking');
            var msg = 'Booking failed. Please try again.';
            if (xhr && xhr.status === 403) msg = 'Your session expired. Please refresh the page and try again.';
            else if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) msg = xhr.responseJSON.data.message;
            toast(msg, 'danger');
        });
    });

    /* Cancel booking */
    $(document).on('click', '.es-cancel-booking', function () {
        var id = $(this).data('id');
        if (!confirm('Cancel this booking?')) return;
        $.post(ES_FE.ajax_url, {
            action: 'es_cancel_booking',
            nonce: ES_FE.nonce,
            booking_id: id,
        }).done(function (json) {
            if (json.success) {
                toast('Booking cancelled.');
                setTimeout(function () { window.location.reload(); }, 600);
            } else {
                toast(json.data.message || 'Failed.', 'danger');
            }
        });
    });

    /* Modal close */
    $(document).on('click', '.es-modal-close, .es-modal-backdrop', function () {
        $(this).closest('.es-modal').hide();
    });

    /* ============= PUBLIC BOOKING CALENDAR (v2-style multi-step) ============= */
    /* This is the [course_booking_calendar] shortcode UI.
       Frontend-only — uses existing v3.1 AJAX endpoints. */

    var PCal = {
        $app: null,
        $wrap: null,
        config: {},
        state: {
            step: 1,
            today: null,
            viewYear: 0,
            viewMonth: 0,
            monthData: null,
            selectedDates: [],   // ['2026-04-16', '2026-04-20']
            slotsByDate: {},     // { '2026-04-16': [{id, start_user, end_user, ...}] }
            chosenSlots: {},     // { '2026-04-16': slot_id }
            form: { first_name: '', last_name: '', email: '', topic: '', platform: 'Zoom' }
        }
    };

    var ICON = {
        arrowL: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
        arrowR: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
        check:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
    };

    var DOW = ['SUN','MON','TUE','WED','THU','FRI','SAT'];
    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function pcalFmtDate(iso) {
        var p = iso.split('-');
        return MONTHS[parseInt(p[1],10)-1] + ' ' + parseInt(p[2],10) + ', ' + p[0];
    }

    function pcalInit() {
        var $app = $('.es-pcal-app');
        if (!$app.length) return;
        PCal.$app = $app;
        PCal.$wrap = $app.find('.es-pcal-step-wrap');

        // Read config from data attributes
        PCal.config = {
            types: $app.data('types') || '',
            monthsAhead: parseInt($app.data('months-ahead'), 10) || 12,
            allowMulti: String($app.data('allow-multi')) === '1',
            isLogged: String($app.data('is-logged')) === '1',
            loginUrl: $app.data('login-url') || '',
            registerUrl: $app.data('register-url') || '',
            defaultPlatform: $app.data('default-platform') || 'Zoom',
            platforms: $app.data('platforms') || ['Zoom'],
            tz: $app.data('tz') || '',
            today: $app.data('today') || ''
        };

        // Pre-fill form for logged-in users
        if (PCal.config.isLogged) {
            PCal.state.form.first_name = $app.data('user-first') || '';
            PCal.state.form.last_name = $app.data('user-last') || '';
            PCal.state.form.email = $app.data('user-email') || '';
        }
        PCal.state.form.platform = PCal.config.defaultPlatform;
        PCal.state.today = PCal.config.today;

        var p = PCal.config.today.split('-');
        PCal.state.viewYear = parseInt(p[0], 10);
        PCal.state.viewMonth = parseInt(p[1], 10);

        pcalRender();
    }

    function pcalRender() {
        if (!PCal.$wrap) return;
        PCal.$wrap.empty().attr('data-step', PCal.state.step);
        if (PCal.state.step === 1) pcalRenderStep1();
        else if (PCal.state.step === 2) pcalRenderStep2();
        else if (PCal.state.step === 3) pcalRenderStep3();
        else if (PCal.state.step === 4) pcalRenderStep4();
        else if (PCal.state.step === 5) pcalRenderSuccess();
    }

    /* ============= STEP 1: CALENDAR ============= */
    function pcalRenderStep1() {
        var hasSel = PCal.state.selectedDates.length > 0;
        var html = '<div class="es-pcal-step1' + (hasSel ? ' has-selection' : '') + '">';

        // Calendar card
        html += '<div class="es-pcal-cal">';
        var monthTxt = PCal.state.monthData
            ? PCal.state.monthData.month_name
            : MONTHS[PCal.state.viewMonth - 1] + ' ' + PCal.state.viewYear;
        html += '<div class="es-pcal-cal-head">';
        html += '<div class="es-pcal-cal-month">' + escapeHtml(monthTxt) + '</div>';
        html += '<div class="es-pcal-cal-nav">';
        html += '<button type="button" class="es-pcal-icon-btn btn-link" data-act="prev"' + (pcalIsPrevBlocked() ? ' disabled' : '') + '>' + ICON.arrowL + '</button>';
        html += '<button type="button" class="es-pcal-icon-btn btn-link" data-act="next">' + ICON.arrowR + '</button>';
        html += '</div></div>';

        html += '<div class="es-pcal-cal-grid">';
        DOW.forEach(function (d) { html += '<div class="es-pcal-dow">' + d + '</div>'; });

        if (!PCal.state.monthData) {
            html += '</div><div class="es-pcal-loading">Loading calendar…</div></div>';
        } else {
            var d = PCal.state.monthData;
            for (var i = 0; i < d.first_weekday; i++) html += '<div class="es-pcal-day is-empty"></div>';

            // Apply months_ahead constraint
            var maxDate = new Date();
            maxDate.setMonth(maxDate.getMonth() + PCal.config.monthsAhead);
            var maxIso = maxDate.getFullYear() + '-' + pad(maxDate.getMonth() + 1) + '-' + pad(maxDate.getDate());

            for (var dnum = 1; dnum <= d.days_in; dnum++) {
                var iso = d.year + '-' + pad(d.month) + '-' + pad(dnum);
                var info = d.days[iso];
                var hasOpen = info && info.open > 0;
                var isPast = iso < d.today;
                var isFuture = iso > maxIso;
                var disabled = isPast || isFuture || !hasOpen;
                var selected = PCal.state.selectedDates.indexOf(iso) !== -1;
                var isToday = iso === d.today;

                var cls = 'es-pcal-day';
                if (disabled) cls += ' is-disabled';
                if (selected) cls += ' is-selected';
                if (isToday) cls += ' is-today';

                html += '<button type="button" class="' + cls + '"';
                if (!disabled) html += ' data-iso="' + iso + '"';
                if (disabled) html += ' disabled';
                html += '>';
                html += dnum;
                if (hasOpen && !disabled) html += '<span class="es-pcal-day-dot"></span>';
                html += '</button>';
            }
            html += '</div></div>';

            // Selected dates panel
            if (hasSel) {
                html += '<div class="es-pcal-selected-panel">';
                html += '<h3>Selected Dates</h3>';
                html += '<div class="es-pcal-sel-list">';
                PCal.state.selectedDates.forEach(function (date) {
                    html += '<div class="es-pcal-sel-pill">';
                    html += '<span>' + escapeHtml(pcalFmtDate(date)) + '</span>';
                    html += '<button type="button" class="btn-remove" data-remove-date="' + escapeHtml(date) + '" aria-label="Remove">✕</button>';
                    html += '</div>';
                });
                html += '</div></div>';
            }
        }
        html += '</div>';

        // Bottom actions
        html += '<div class="es-pcal-actions">';
        html += '<button type="button" class="btn-primary" data-act="next-step"' + (!hasSel ? ' disabled' : '') + '>';
        html += 'Next ' ;
        html += '</button>';
        html += '</div>';

        PCal.$wrap.html(html);

        if (!PCal.state.monthData) pcalLoadMonth();
    }

    function pcalIsPrevBlocked() {
        var p = PCal.state.today.split('-');
        var ty = parseInt(p[0], 10), tm = parseInt(p[1], 10);
        return (PCal.state.viewYear < ty) || (PCal.state.viewYear === ty && PCal.state.viewMonth <= tm);
    }

    function pcalLoadMonth() {
        $.post(ES_FE.ajax_url, {
            action: 'es_get_calendar_month',
            nonce: ES_FE.nonce,
            year: PCal.state.viewYear,
            month: PCal.state.viewMonth,
            types: PCal.config.types
        }).done(function (json) {
            if (json.success) {
                PCal.state.monthData = json.data;
                pcalRender();
            } else {
                PCal.$wrap.html('<div class="es-pcal-error">' + escapeHtml(json.data && json.data.message ? json.data.message : 'Failed to load.') + '</div>');
            }
        }).fail(function () {
            PCal.$wrap.html('<div class="es-pcal-error">Network error. Refresh and try again.</div>');
        });
    }

    function pcalToggleDate(date) {
        var idx = PCal.state.selectedDates.indexOf(date);
        if (idx >= 0) {
            PCal.state.selectedDates.splice(idx, 1);
            delete PCal.state.chosenSlots[date];
        } else {
            if (PCal.config.allowMulti) PCal.state.selectedDates.push(date);
            else PCal.state.selectedDates = [date];
        }
        PCal.state.selectedDates.sort();
        pcalRender();
    }

    /* Step 1 events */
    $(document).on('click', '.es-pcal-app [data-act="prev"]', function () {
        if (pcalIsPrevBlocked()) return;
        PCal.state.viewMonth--;
        if (PCal.state.viewMonth < 1) { PCal.state.viewMonth = 12; PCal.state.viewYear--; }
        PCal.state.monthData = null;
        pcalRender();
    });
    $(document).on('click', '.es-pcal-app [data-act="next"]', function () {
        PCal.state.viewMonth++;
        if (PCal.state.viewMonth > 12) { PCal.state.viewMonth = 1; PCal.state.viewYear++; }
        PCal.state.monthData = null;
        pcalRender();
    });
    $(document).on('click', '.es-pcal-app .es-pcal-day[data-iso]', function () {
        pcalToggleDate($(this).data('iso'));
    });
    $(document).on('click', '.es-pcal-app [data-remove-date]', function (e) {
        e.stopPropagation();
        pcalToggleDate($(this).data('remove-date'));
    });
    $(document).on('click', '.es-pcal-app [data-act="next-step"]', function () {
        if (!PCal.state.selectedDates.length) return;
        PCal.state.step = 2;
        pcalRender();
    });

    /* ============= STEP 2: TIME SLOTS ============= */
    function pcalRenderStep2() {
        var html = '<div class="es-pcal-slots-wrap">';
        PCal.state.selectedDates.forEach(function (date) {
            html += '<div class="es-pcal-date-card" data-date="' + escapeHtml(date) + '">';
            html += '<div class="es-pcal-date-head">';
            html += '<div class="es-pcal-date-title">' + escapeHtml(pcalFmtDate(date)) + '</div>';
            html += '<button type="button" class=" btn-primary" data-act="back-to-cal"> Change date</button>';
            html += '</div>';
            html += '<div class="es-pcal-slot-grid" data-date="' + escapeHtml(date) + '">';

            if (PCal.state.slotsByDate[date]) {
                html += pcalRenderSlots(date);
            } else {
                html += '<div class="es-pcal-no-slots">Loading slots…</div>';
            }

            html += '</div></div>';
        });
        html += '</div>';

        // Actions
        html += '<div class="es-pcal-actions">';
        html += '<button type="button" class="es-pcal-icon-btn-lg  btn-link" data-act="back-to-cal">' + ICON.arrowL + '</button>';
        html += '<button type="button" class="btn-primary" data-act="to-step3">Next </button>';
        html += '</div>';

        PCal.$wrap.html(html);

        // Load any unloaded dates
        PCal.state.selectedDates.forEach(function (date) {
            if (!PCal.state.slotsByDate[date]) pcalLoadSlots(date);
        });
    }

    function pcalRenderSlots(date) {
        var slots = PCal.state.slotsByDate && PCal.state.slotsByDate[date]
            ? PCal.state.slotsByDate[date]
            : [];

        if (!slots.length) {
            return '<div class="es-pcal-no-slots">No available slots for this date.</div>';
        }

        var html = '';

        slots.forEach(function (slot) {
            var bookable = !slot.is_past && !slot.already_booked && !slot.is_full;
            var selected = PCal.state.chosenSlots && PCal.state.chosenSlots[date] === slot.id;

            var cls = 'es-pcal-slot-btn' +
                (selected ? ' is-selected' : '') +
                (!bookable ? ' is-disabled' : '');

            var rem = Number(slot.capacity || 0) - Number(slot.booked || 0);

            html += '<button type="button" class="' + cls + '"';
            html += ' data-date="' + escapeHtml(date) + '"';
            html += ' data-slot-id="' + escapeHtml(String(slot.id)) + '"';

            if (!bookable) {
                html += ' disabled';
            }

            html += ' style="--type-color:' + escapeHtml(slot.type_color || '#999') + '">';

            html += '<div class="es-pcal-slot-time">' + escapeHtml(slot.start_user || '') + '</div>';
            html += '<div class="es-pcal-slot-time-range">' +
                escapeHtml(slot.start_user || '') + ' - ' + escapeHtml(slot.end_user || '') +
            '</div>';

            html += '<div class="es-pcal-slot-meta">' +
                escapeHtml(String(slot.duration || '')) + ' Min · ' +
                escapeHtml(slot.type_label || '') +
            '</div>';

            if (Number(slot.capacity) > 1) {
                html += '<div class="es-pcal-slot-cap">' +
                    (rem <= 0 ? 'Full' : rem + ' left') +
                '</div>';
            } else if (slot.is_full || slot.already_booked) {
                html += '<div class="es-pcal-slot-cap">' +
                    (slot.already_booked ? 'Booked' : 'Full') +
                '</div>';
            }

            html += '</button>';
        });

        return html;
    }

    function pcalLoadSlots(date) {
    $.post(ES_FE.ajax_url, {
        action: 'es_get_slot',
        nonce: ES_FE.nonce,
        date: date,
        types: PCal.config.types
    }).done(function (json) {
        var $grid = $('.es-pcal-app .es-pcal-slot-grid[data-date="' + date + '"]');

        if (json && json.success && json.data && Array.isArray(json.data.slots)) {
            PCal.state.slotsByDate[date] = json.data.slots;

            if ($grid.length) {
                $grid.html(pcalRenderSlots(date));
            }
        } else {
            PCal.state.slotsByDate[date] = [];

            if ($grid.length) {
                $grid.html('<div class="es-pcal-no-slots">No available slots for this date.</div>');
            }
        }
    }).fail(function () {
        var $grid = $('.es-pcal-app .es-pcal-slot-grid[data-date="' + date + '"]');

        if ($grid.length) {
            $grid.html('<div class="es-pcal-no-slots">Could not load slots. Please try again.</div>');
        }
    });
}
    $(document).on('click', '.es-pcal-app [data-act="back-to-cal"]', function () {
        PCal.state.step = 1;
        pcalRender();
    });
    $(document).on('click', '.es-pcal-app .es-pcal-slot-btn:not(.is-disabled)', function () {
        var date = $(this).data('date');
        var slotId = parseInt($(this).data('slot-id'), 10);
        PCal.state.chosenSlots[date] = slotId;
        // Update selected state in UI without full re-render
        $(this).closest('.es-pcal-slot-grid').find('.es-pcal-slot-btn').removeClass('is-selected');
        $(this).addClass('is-selected');
    });
    $(document).on('click', '.es-pcal-app [data-act="to-step3"]', function () {
        var ok = PCal.state.selectedDates.every(function (d) { return PCal.state.chosenSlots[d]; });
        if (!ok) {
            toast('Please choose a time slot for each selected date.', 'danger');
            return;
        }
        PCal.state.step = 3;
        pcalRender();
    });

    /* ============= STEP 3: USER FORM ============= */
    function pcalRenderStep3() {
        var f = PCal.state.form;
        var html = '<div class="es-pcal-form-wrap">';
        html += '<div class="es-pcal-form-card">';

        html += '<div class="es-pcal-form-row">';
        html += '<div class="es-pcal-field"><label>First Name</label><input type="text" data-field="first_name" placeholder="ENTER FIRST NAME" value="' + escapeHtml(f.first_name) + '"></div>';
        html += '<div class="es-pcal-field"><label>Last Name</label><input type="text" data-field="last_name" placeholder="ENTER LAST NAME" value="' + escapeHtml(f.last_name) + '"></div>';
        html += '</div>';

        html += '<div class="es-pcal-field"><label>Email</label><input type="email" data-field="email" placeholder="EMAIL ADDRESS" value="' + escapeHtml(f.email) + '"></div>';

        html += '<div class="es-pcal-field"><label>What\'s this meeting about?</label>';
        html += '<textarea data-field="topic" rows="4" placeholder="Brief description of what you\'d like to discuss..">' + escapeHtml(f.topic) + '</textarea></div>';

        // Platform is set per-slot by the admin — no need to ask the user.

        if (!PCal.config.isLogged) {
            html += '<div class="es-pcal-login-notice">';
            html += '<strong>Note:</strong> You\'ll need to log in or create an account on the next step to confirm your booking.';
            html += '</div>';
        }

        html += '</div></div>';

        html += '<div class="es-pcal-actions">';
        html += '<button type="button" class="es-pcal-icon-btn-lg  btn-link" data-act="back-to-slots">' + ICON.arrowL + '</button>';
        html += '<button type="button" class="btn-primary" data-act="to-step4">Review Booking </button>';
        html += '</div>';

        PCal.$wrap.html(html);
    }

    $(document).on('input change', '.es-pcal-app [data-field]', function () {
        var key = $(this).data('field');
        PCal.state.form[key] = $(this).val();
    });
    $(document).on('click', '.es-pcal-app [data-act="back-to-slots"]', function () {
        PCal.state.step = 2;
        pcalRender();
    });
    $(document).on('click', '.es-pcal-app [data-act="to-step3"]', function () {
        // already handled above
    });
    $(document).on('click', '.es-pcal-app [data-act="to-step4"]', function () {
        var f = PCal.state.form;
        if (!f.first_name || !f.last_name) { toast('Please enter your name.', 'danger'); return; }
        if (!/^\S+@\S+\.\S+$/.test(f.email)) { toast('Please enter a valid email.', 'danger'); return; }
        PCal.state.step = 4;
        pcalRender();
    });

    /* ============= STEP 4: CONFIRM ============= */
    function pcalRenderStep4() {
        var f = PCal.state.form;
        var html = '<div class="es-pcal-confirm-wrap">';
        html += '<div class="es-pcal-confirm-card">';

        html += pcalConfirmRow('Name', escapeHtml(f.first_name + ' ' + f.last_name));
        html += pcalConfirmRow('Email', escapeHtml(f.email));

        if (PCal.state.selectedDates.length === 1) {
            var date = PCal.state.selectedDates[0];
            var slot = (PCal.state.slotsByDate[date] || []).filter(function (s) { return s.id === PCal.state.chosenSlots[date]; })[0];
            if (slot) {
                html += pcalConfirmRow('Date', escapeHtml(pcalFmtDate(date)));
                html += pcalConfirmRow('Time', escapeHtml(slot.start_user) + ' - ' + escapeHtml(slot.end_user));
                html += pcalConfirmRow('Duration', slot.duration + ' min');
                html += pcalConfirmRow('Type', escapeHtml(slot.type_label));
                html += pcalConfirmRow('Platform', escapeHtml(slot.platform));
            }
        } else {
            // multi-day list with each slot's own platform
            var lines = PCal.state.selectedDates.map(function (d) {
                var s = (PCal.state.slotsByDate[d] || []).filter(function (x) { return x.id === PCal.state.chosenSlots[d]; })[0];
                return s
                    ? (escapeHtml(pcalFmtDate(d)) + ' • ' + escapeHtml(s.start_user) + ' - ' + escapeHtml(s.end_user) + ' (' + s.duration + ' min · ' + escapeHtml(s.platform) + ')')
                    : escapeHtml(pcalFmtDate(d));
            });
            lines.forEach(function (line, i) {
                html += pcalConfirmRow(i === 0 ? 'Sessions' : '', line);
            });
        }

        if (f.topic) html += pcalConfirmRow('Topic', escapeHtml(f.topic));

        html += '</div></div>';

        html += '<div class="es-pcal-actions">';
        html += '<button type="button" class="es-pcal-icon-btn-lg  btn-link" data-act="back-to-form">' + ICON.arrowL + '</button>';
        html += '<button type="button" class="btn-primary" data-act="submit-booking">Confirm &amp; Book </button>';
        html += '</div>';

        PCal.$wrap.html(html);
    }

    function pcalConfirmRow(k, v) {
        return '<div class="es-pcal-conf-row"><div class="es-pcal-conf-key">' + k + '</div><div class="es-pcal-conf-val">' + v + '</div></div>';
    }

    $(document).on('click', '.es-pcal-app [data-act="back-to-form"]', function () {
        PCal.state.step = 3;
        pcalRender();
    });

    $(document).on('click', '.es-pcal-app [data-act="submit-booking"]', function () {
        // If not logged in, show login modal — booking can't proceed
        if (!PCal.config.isLogged) {
            // Save state in sessionStorage so user can resume after login
            try {
                sessionStorage.setItem('es_pcal_resume', JSON.stringify({
                    step: 4,
                    selectedDates: PCal.state.selectedDates,
                    chosenSlots: PCal.state.chosenSlots,
                    form: PCal.state.form,
                    timestamp: Date.now()
                }));
            } catch (e) {}

            // Build login URL with redirect_to
            var $btn = $('#es-pcal-go-login');
            var loginHref = PCal.config.loginUrl;
            if (loginHref) {
                var sep = loginHref.indexOf('?') === -1 ? '?' : '&';
                $btn.attr('href', loginHref + sep + 'redirect_to=' + encodeURIComponent(window.location.href));
            }
            var $rbtn = $('#es-pcal-go-register');
            var regHref = PCal.config.registerUrl;
            if (regHref && $rbtn.length) {
                var sep2 = regHref.indexOf('?') === -1 ? '?' : '&';
                $rbtn.attr('href', regHref + sep2 + 'redirect_to=' + encodeURIComponent(window.location.href));
            }
            $('#es-pcal-login-modal').show();
            return;
        }

        pcalSubmitBookings();
    });

    function pcalSubmitBookings() {
        var dates = PCal.state.selectedDates.slice();
        var results = [];
        var $confirmBtn = $('.es-pcal-app [data-act="submit-booking"]');
        $confirmBtn.prop('disabled', true).text('Booking…');

        function next() {
            if (!dates.length) {
                PCal.state.bookingResults = results;
                PCal.state.step = 5;
                pcalRender();
                return;
            }
            var date = dates.shift();
            var slotId = PCal.state.chosenSlots[date];
            $.post(ES_FE.ajax_url, {
                action: 'es_book_slot',
                nonce: ES_FE.nonce,
                slot_id: slotId,
                note: PCal.state.form.topic
            }).done(function (json) {
                if (json.success) {
                    results.push({ date: date, ok: true, data: json.data });
                } else {
                    results.push({ date: date, ok: false, message: (json.data && json.data.message) || 'Booking failed' });
                }
                next();
            }).fail(function (xhr) {
                var msg = 'Network error';
                if (xhr && xhr.status === 403) msg = 'Your session expired — please refresh the page and book again.';
                else if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) msg = xhr.responseJSON.data.message;
                results.push({ date: date, ok: false, message: msg });
                next();
            });
        }
        next();
    }

    /* ============= STEP 5: SUCCESS ============= */
    function pcalRenderSuccess() {
        var f = PCal.state.form;
        var results = PCal.state.bookingResults || [];
        var allOk = results.length > 0 && results.every(function (r) { return r.ok; });

        var html = '<div class="es-pcal-success">';
        html += '<div class="es-pcal-success-icon">' + ICON.check + '</div>';

        if (allOk) {
            html += '<h3>You are all set, ' + escapeHtml(f.first_name) + '!</h3>';
            html += '<p>Your booking has been confirmed. A confirmation email is on its way.</p>';
        } else {
            var okCount = results.filter(function (r) { return r.ok; }).length;
            html += '<h3>' + okCount + ' of ' + results.length + ' bookings confirmed</h3>';
            html += '<p>Some bookings could not be completed. Details below.</p>';
        }

        // Per-date summary with Zoom links
        html += '<div class="es-pcal-success-list">';
        results.forEach(function (r) {
            html += '<div class="es-pcal-success-row' + (r.ok ? '' : ' is-failed') + '">';
            html += '<strong>' + escapeHtml(pcalFmtDate(r.date)) + '</strong>';
            if (r.ok) {
                if (r.data && r.data.zoom_join_url) {
                    html += '<a href="' + escapeHtml(r.data.zoom_join_url) + '" target="_blank" rel="noopener" class="es-fe-btn es-fe-btn-zoom es-fe-btn-zoom-sm"><span class="dashicons dashicons-video-alt2"></span> Join Zoom</a>';
                } else {
                    html += '<span class="es-pcal-success-pill">Confirmed ✓</span>';
                }
            } else {
                html += '<span class="es-pcal-fail-msg">' + escapeHtml(r.message) + '</span>';
            }
            html += '</div>';
        });
        html += '</div>';

        html += '<button type="button" class="btn-primary" data-act="reload">Make another booking</button>';
        html += '</div>';

        PCal.$wrap.html(html);
    }

    $(document).on('click', '.es-pcal-app [data-act="reload"]', function () {
        window.location.reload();
    });

    /* Resume booking after login */
    function pcalMaybeResume() {
        if (!PCal.config.isLogged) return;
        var raw;
        try { raw = sessionStorage.getItem('es_pcal_resume'); } catch (e) { return; }
        if (!raw) return;

        try {
            var saved = JSON.parse(raw);
            // Expire after 30 minutes
            if (!saved || !saved.timestamp || (Date.now() - saved.timestamp > 30 * 60 * 1000)) {
                sessionStorage.removeItem('es_pcal_resume');
                return;
            }
            sessionStorage.removeItem('es_pcal_resume');

            PCal.state.selectedDates = saved.selectedDates || [];
            PCal.state.chosenSlots = saved.chosenSlots || {};
            // Keep logged-in user's form data; but preserve topic from saved
            if (saved.form && saved.form.topic) PCal.state.form.topic = saved.form.topic;

            if (PCal.state.selectedDates.length) {
                // Reload slot data for selected dates, then jump to confirm
                var pending = PCal.state.selectedDates.length;
                PCal.state.selectedDates.forEach(function (date) {
                    $.post(ES_FE.ajax_url, {
                        action: 'es_get_slot',
                        nonce: ES_FE.nonce,
                        date: date,
                        types: PCal.config.types
                    }).done(function (json) {
                        if (json.success) PCal.state.slotsByDate[date] = json.data.slots;
                    }).always(function () {
                        pending--;
                        if (pending === 0) {
                            PCal.state.step = 4;
                            pcalRender();
                            toast('Welcome back! Confirm your booking to continue.');
                        }
                    });
                });
            }
        } catch (e) {
            sessionStorage.removeItem('es_pcal_resume');
        }
    }

    /* INIT */
    $(function () {
        if ($('#es-fe-calview').length) loadFeCalendar();
        if ($('.es-pcal-app').length) {
            pcalInit();
            pcalMaybeResume();
        }
    });

})(jQuery);