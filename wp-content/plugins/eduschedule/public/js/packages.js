/**
 * EduSchedule Packages Module — Admin + Frontend JavaScript
 *
 * Handles:
 *  - Admin: Packages CRUD
 *  - Admin: Groups CRUD
 *  - Admin: After Call modal (multi-package + group)
 *  - Admin: Copy share link
 *  - Frontend: Student package selection → thank-you redirect
 */
(function ($) {
    'use strict';

    /* ============ PACKAGES PAGE ============ */
    if ($('.es-packages-page').length) {

        $('#es-add-package-btn').on('click', function () {
            resetPackageModal();
            $('#es-pkg-modal-title').text('Create Package');
            $('#es-pkg-save-text').text('Create Package');
            $('#es-package-modal').fadeIn(200);
        });

        $(document).on('click', '.es-edit-package', function () {
            loadPackageForEdit($(this).data('id'));
        });

        $(document).on('click', '.es-delete-package', function () {
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this package?')) {
                deletePackage(id);
            }
        });

        $('#es-pkg-save').on('click', savePackage);

        // Live recalculation of total price + total sessions
        $(document).on('input change',
            '#es-pkg-monthly-price, #es-pkg-months, #es-pkg-monthly-sessions, #es-pkg-currency, #es-pkg-discount-percent, #es-pkg-discount-months',
            recalcPackageTotals);

        $('#es-package-modal').on('click', '.es-modal-close, .es-modal-backdrop', function () {
            $('#es-package-modal').fadeOut(200);
        });

        // ── v4.4 — Per-package videos: open WP media library and POST to AJAX
        // endpoint. Each click instantiates a fresh frame so the title can vary
        // per package.
        var pkgVideoFrame = null;
        $(document).on('click', '.es-pkgvid-add', function () {
            var $btn = $(this);
            var packageId = parseInt($btn.data('id'), 10) || 0;
            if (!packageId) return;

            if (typeof wp === 'undefined' || !wp.media) {
                alert('WordPress media library not available on this page.');
                return;
            }

            pkgVideoFrame = wp.media({
                title: 'Choose a video for this package',
                button: { text: 'Use this video' },
                library: { type: 'video' },
                multiple: false
            });

            pkgVideoFrame.on('select', function () {
                var att = pkgVideoFrame.state().get('selection').first().toJSON();
                if (!att || !att.id) return;
                $btn.prop('disabled', true).find('.dashicons').addClass('dashicons-update').removeClass('dashicons-plus-alt2');
                $.post(ES_ADMIN.ajax_url, {
                    action:        'es_admin_add_package_video',
                    nonce:         ES_ADMIN.nonce,
                    package_id:    packageId,
                    attachment_id: att.id,
                    title:         att.title || att.filename || 'Video',
                    video_url:     att.url || ''
                }).done(function (res) {
                    if (res && res.success) {
                        location.reload();
                    } else {
                        alert((res && res.data && res.data.message) || 'Could not add the video.');
                        $btn.prop('disabled', false).find('.dashicons').addClass('dashicons-plus-alt2').removeClass('dashicons-update');
                    }
                }).fail(function () {
                    alert('Network error.');
                    $btn.prop('disabled', false).find('.dashicons').addClass('dashicons-plus-alt2').removeClass('dashicons-update');
                });
            });

            pkgVideoFrame.open();
        });

        $(document).on('click', '.es-pkgvid-del', function () {
            var $btn = $(this);
            var id = parseInt($btn.data('id'), 10) || 0;
            if (!id) return;
            if (!confirm('Delete this video from the package?')) return;
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_delete_video',
                nonce:  ES_ADMIN.nonce,
                id:     id
            }).done(function (res) {
                if (res && res.success) {
                    $btn.closest('.es-pkgvid-card').slideUp(150, function(){ $(this).remove(); });
                } else {
                    alert((res && res.data && res.data.message) || 'Could not delete the video.');
                }
            });
        });

        // ── v4.4.2 — Per-package FILE upload (PDF / DOC / PPT / video file).
        // Click button → trigger hidden file input for the matching package id.
        $(document).on('click', '.es-pkgfile-add', function () {
            var pid = parseInt($(this).data('id'), 10) || 0;
            if (!pid) return;
            $('.es-pkgfile-input[data-id="' + pid + '"]').trigger('click');
        });
        $(document).on('change', '.es-pkgfile-input', function () {
            var $input = $(this);
            var pid = parseInt($input.data('id'), 10) || 0;
            var file = this.files && this.files[0];
            if (!pid || !file) return;
            var $card = $input.closest('.es-pkgvids');
            var $prog = $card.find('.es-pkgvids-progress');
            $prog.show().text('Uploading "' + file.name + '"…');

            var fd = new FormData();
            fd.append('action',     'es_admin_upload_package_file');
            fd.append('nonce',      ES_ADMIN.nonce);
            fd.append('package_id', pid);
            fd.append('file',       file);

            $.ajax({
                url: ES_ADMIN.ajax_url,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    $prog.hide();
                    if (res && res.success) {
                        location.reload();
                    } else {
                        alert((res && res.data && res.data.message) || 'Upload failed.');
                    }
                },
                error: function () {
                    $prog.hide();
                    alert('Network error.');
                }
            });
            $input.val('');   // reset so re-picking same file works
        });

        $(document).on('click', '.es-pkgfile-del', function () {
            var $btn = $(this);
            var id = parseInt($btn.data('id'), 10) || 0;
            if (!id) return;
            if (!confirm('Delete this file from the package?')) return;
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_delete_session_file',
                nonce:  ES_ADMIN.nonce,
                id:     id
            }).done(function (res) {
                if (res && res.success) {
                    $btn.closest('.es-pkgvid-card').slideUp(150, function(){ $(this).remove(); });
                } else {
                    alert((res && res.data && res.data.message) || 'Could not delete the file.');
                }
            });
        });
    }

    /* ============ STUDENT DETAIL — AFTER CALL (ALWAYS VISIBLE) ============ */
    if ($('.es-student-detail-page').length) {

        // Initialize field visibility based on currently selected outcome
        toggleAfterCallFields();

        // Outcome change → show/hide group + package fields
        $(document).on('change', '#es-after-call-outcome', toggleAfterCallFields);

        // Submit
        $(document).on('click', '#es-after-call-submit', submitAfterCall);

        // Copy share link
        $('#es-copy-link').on('click', function () {
            var $input = $('#es-share-link');
            $input.select();
            try {
                document.execCommand('copy');
                $(this).find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
                var $b = $(this);
                setTimeout(function () {
                    $b.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard');
                }, 1500);
            } catch (e) {
                alert('Press Ctrl+C to copy');
            }
        });

        // Cap package checkbox count at 3
        $(document).on('change', '.es-pkg-check', function () {
            var checked = $('.es-pkg-check:checked').length;
            if (checked > 3) {
                $(this).prop('checked', false);
                alert('You can select up to 3 packages only.');
            }
        });

        // Single course select (After Call → email subject/body + student profile).
        if ($.fn.select2 && $('#es-after-call-course').length) {
            $('#es-after-call-course').select2({
                placeholder: 'Search course…',
                width: '100%',
                allowClear: true
            });
        }
    }

    /* ============ GROUPS PAGE ============ */
    if ($('.es-groups-page').length) {

        $('#es-add-group-btn').on('click', function () {
            resetGroupModal();
            $('#es-group-modal-title').text('New Group');
            $('#es-group-save-text').text('Create Group');
            $('#es-group-modal').fadeIn(200);
        });

        $(document).on('click', '.es-edit-group-btn', function () {
            loadGroupForEdit($(this).data('id'));
        });

        $(document).on('click', '.es-delete-group-btn', function () {
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this group? Members will be unassigned.')) {
                deleteGroup(id);
            }
        });

        $(document).on('click', '.es-remove-member-btn', function () {
            var gid = $(this).data('group');
            var uid = $(this).data('user');
            if (confirm('Remove this member from the group?')) {
                removeGroupMember(gid, uid);
            }
        });

        $(document).on('click', '.es-add-member-btn', function () {
            $('#es-add-member-msg').hide().text('');
            $('#es-add-member-user').val('');
            $('#es-add-member-modal').fadeIn(200);
        });

        $(document).on('click', '#es-add-member-save', addGroupMember);

        $('#es-group-save').on('click', saveGroup);

        $('#es-group-modal, #es-add-member-modal').on('click', '.es-modal-close, .es-modal-backdrop', function () {
            $(this).closest('.es-modal').fadeOut(200);
        });
    }

    /* ============ SESSION FILES + SCHEDULE (1:1 / Group) ============ */
    if ($('#es-session-files').length || $('#es-schedule-session').length) {

        // ── Uploads ──
        var $sfCard = $('#es-session-files');

        $('#es-sf-upload-btn').on('click', function () {
            $('#es-sf-input').trigger('click');
        });

        $('#es-sf-input').on('change', function () {
            var file = this.files && this.files[0];
            if (!file) return;
            uploadSessionFile(file);
            // Reset so the same file can be re-selected later
            $(this).val('');
        });

        $(document).on('click', '.es-sf-delete', function () {
            var id = $(this).data('id');
            if (confirm('Delete this file? This cannot be undone.')) {
                deleteSessionFile(id);
            }
        });

        // ── Schedule a session ──
        $('#es-ss-submit').on('click', scheduleSession);
    }

    /* ============ TABBED DETAIL UI (1:1 / Group) ============ */
    if ($('.es-tabui-detail').length) {

        var $detail = $('.es-tabui-detail');
        var TAB_TYPE = $detail.data('target-type');   // '1to1' | 'group'
        var TAB_ID   = $detail.data('target-id');
        var TAB_GROUP = (TAB_TYPE === 'group') ? TAB_ID : 0;

        // Tab switching
        $detail.on('click', '.es-tab', function () {
            var tab = $(this).data('tab');
            switchTab(tab);
        });
        // Header jump buttons. If a requested tab is not present on this page,
        // keep the current tab unchanged.
        $detail.on('click', '.es-tab-jump', function () {
            switchTab($(this).data('tab'));
        });
        $('#es-sched-new-btn').on('click', function () {
            if ($detail.find('.es-tab[data-tab="sched"]').length) {
                switchTab('sched');
                $('#es-ss-date').focus();
            }
        });

        function switchTab(tab) {
            if (!$detail.find('.es-tab[data-tab="' + tab + '"]').length || !$detail.find('.es-tabpane[data-pane="' + tab + '"]').length) return;
            $detail.find('.es-tab').removeClass('is-active');
            $detail.find('.es-tab[data-tab="' + tab + '"]').addClass('is-active');
            $detail.find('.es-tabpane').hide();
            $detail.find('.es-tabpane[data-pane="' + tab + '"]').show();
        }

        // ── Attendance ──
        $detail.on('click', '.es-att-btn', function () {
            var $btn  = $(this);
            var $row  = $btn.closest('.es-att-row');
            var slot  = $row.data('slot-id');
            var status= $btn.data('status');

            // If the active button is clicked again, clear it (set to none).
            if ($btn.hasClass('is-on')) {
                status = 'none';
                $btn.removeClass('is-on');
            } else {
                $row.find('.es-att-btn').removeClass('is-on');
                $btn.addClass('is-on');
            }

            saveAttendance(slot, status, $row.find('.es-att-comment').val());
        });

        // Save comment on blur/change
        $detail.on('change', '.es-att-comment', function () {
            var $row   = $(this).closest('.es-att-row');
            var slot   = $row.data('slot-id');
            var status = $row.find('.es-att-btn.is-on').data('status') || 'none';
            saveAttendance(slot, status, $(this).val());
        });

        function saveAttendance(slotId, status, comment) {
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_save_attendance',
                nonce: ES_ADMIN.nonce,
                user_id: (TAB_TYPE === 'group' ? 0 : TAB_ID),
                slot_id: slotId,
                group_id: TAB_GROUP,
                status: status,
                comment: comment || ''
            }).done(function (json) {
                // Attendance now changes the used-session count (Present and
                // Absent-without-permission consume a session; Absent-with-
                // permission does not). Reflect the new "sessions left" in the
                // student list and the Package tab without a full reload.
                if (json && json.success && json.data && typeof json.data.sessions_left !== 'undefined') {
                    var left = parseInt(json.data.sessions_left, 10);
                    var used = parseInt(json.data.sessions_used, 10);
                    var total = parseInt(json.data.sessions_total, 10);
                    // Update the selected student's list sub-label
                    $('.es-stu-item.is-active .es-stu-sub, .es-student-item.is-active .es-stu-sub, .es-tabui-item.is-active .es-tabui-item-sub')
                        .text(left + ' sessions left');
                    // Update Package tab usage numbers if present
                    $detail.find('.es-usage-stat-val.is-left').text(left);
                    if (!isNaN(used))  $detail.find('.es-usage-stat-val').eq(1).text(used);
                    if (!isNaN(total)) $detail.find('.es-usage-stat-val').eq(0).text(total);
                }
            });
        }

        // Group attendance: each member row carries its own user id + slot id
        // ── Session → users accordion ──
        // Each session is a collapsible header; expanding it reveals that
        // session's member rows. Rows already carry their own data-slot-id, so
        // the .es-gatt-btn handler below saves against the correct session.
        function esGattData() {
            var raw = $('#es-gatt-data').attr('data-att');
            if (!raw) return {};
            try { return JSON.parse(raw); } catch (e) { return {}; }
        }

        // Toggle a session open/closed.
        $detail.on('click', '.es-gatt-sess-head', function () {
            var $sess = $(this).closest('.es-gatt-sess');
            $sess.toggleClass('is-open');
        });

        $detail.on('click', '.es-gatt-btn', function () {
            var $btn  = $(this);
            var $row  = $btn.closest('.es-gatt-row');
            var uid   = $row.data('user-id');
            var slot  = parseInt($row.data('slot-id'), 10) || 0;
            var status= $btn.data('status');
            if (!slot) { alert('Please select a session first.'); return; }

            // Clicking an already-active button clears it (status = none).
            if ($btn.hasClass('is-on')) {
                status = 'none';
                $btn.removeClass('is-on').css('background', 'transparent');
            } else {
                $row.find('.es-gatt-btn').css('background', 'transparent').removeClass('is-on');
                $btn.addClass('is-on').css('background', status === 'present' ? 'rgba(16,185,129,0.25)' : (status === 'absent_excused' ? 'rgba(245,158,11,0.18)' : 'rgba(239,68,68,0.25)'));
            }

            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_save_attendance',
                nonce: ES_ADMIN.nonce,
                user_id: uid,
                slot_id: slot,
                group_id: TAB_GROUP,
                status: status,
                comment: $row.find('.es-gatt-comment').val() || ''
            }).done(function (json) {
                // Keep the local JSON cache in sync so re-opening the session
                // shows the new value without a page reload.
                try {
                    var data = esGattData();
                    if (!data[slot]) data[slot] = {};
                    data[slot][String(uid)] = { status: status, comment: $row.find('.es-gatt-comment').val() || '' };
                    $('#es-gatt-data').attr('data-att', JSON.stringify(data));
                } catch (e) {}
                esGattUpdateSessionCount(slot);
            });
        });

        // Refresh the "X/Y marked" badge on a session header from the cache.
        function esGattUpdateSessionCount(slot) {
            var $sess = $detail.find('.es-gatt-sess[data-slot-id="' + slot + '"]');
            if (!$sess.length) return;
            var total = $sess.find('.es-gatt-row').length;
            var data  = esGattData();
            var bySlot = data[slot] || {};
            var marked = 0;
            Object.keys(bySlot).forEach(function (uid) {
                if (bySlot[uid] && bySlot[uid].status && bySlot[uid].status !== 'none') marked++;
            });
            $sess.find('.es-gatt-sess-count').text(marked + '/' + total + ' marked');
        }

        // Paint a single member row's buttons to reflect a status (no request).
        function esGattPaintRow($row, status) {
            $row.find('.es-gatt-btn').css('background', 'transparent').removeClass('is-on');
            if (status && status !== 'none') {
                var $b = $row.find('.es-gatt-btn[data-status="' + status + '"]');
                $b.addClass('is-on').css('background',
                    status === 'present' ? 'rgba(16,185,129,0.25)'
                    : (status === 'absent_excused' ? 'rgba(245,158,11,0.18)' : 'rgba(239,68,68,0.25)'));
            }
        }

        // ── Bulk: mark every member of a session at once (advanced) ──
        $detail.on('click', '.es-gatt-bulk-btn', function (e) {
            // Don't let the click bubble to the session header toggle.
            e.preventDefault();
            e.stopPropagation();

            var $btn  = $(this);
            var $bar  = $btn.closest('.es-gatt-bulk');
            var slot  = parseInt($bar.data('slot-id'), 10) || 0;
            var status = $btn.data('status');
            if (!slot) { alert('Please select a session first.'); return; }

            var $sess = $detail.find('.es-gatt-sess[data-slot-id="' + slot + '"]');
            var $msg  = $bar.find('.es-gatt-bulk-msg').text('');
            var $btns = $bar.find('.es-gatt-bulk-btn').prop('disabled', true);

            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_save_group_attendance_bulk',
                nonce: ES_ADMIN.nonce,
                group_id: TAB_GROUP,
                slot_id: slot,
                status: status
            }).done(function (json) {
                $btns.prop('disabled', false);
                if (!json || !json.success) {
                    $msg.css('color', '#ef4444').text((json && json.data && json.data.message) || 'Could not update.');
                    return;
                }
                var d = json.data || {};
                // Repaint every member row in this session and refresh the cache.
                var data = esGattData();
                data[slot] = data[slot] || {};
                $sess.find('.es-gatt-row').each(function () {
                    var $row = $(this);
                    esGattPaintRow($row, status);
                    var uid = String($row.data('user-id'));
                    var comment = $row.find('.es-gatt-comment');
                    if (status === 'none') comment.val('');
                    data[slot][uid] = { status: status, comment: comment.val() || '' };
                });
                try { $('#es-gatt-data').attr('data-att', JSON.stringify(data)); } catch (ex) {}
                esGattUpdateSessionCount(slot);

                // Update the group usage panel (Total / Used / Left) if present.
                if (typeof d.group_left !== 'undefined') {
                    $detail.find('.es-usage-stat-val.is-left').text(d.group_left);
                    $detail.find('.es-usage-stat-val').eq(1).text(d.group_used);
                    $detail.find('.es-usage-stat-val').eq(0).text(d.group_total);
                }
                $msg.css('color', '#10b981').text('✓ ' + (d.message || 'Updated'));
                setTimeout(function () { $msg.text(''); }, 2500);
            }).fail(function () {
                $btns.prop('disabled', false);
                $msg.css('color', '#ef4444').text('Network error.');
            });
        });

        $detail.on('change', '.es-gatt-comment', function () {
            var $row = $(this).closest('.es-gatt-row');
            var slot = parseInt($row.data('slot-id'), 10) || 0;
            var status = $row.find('.es-gatt-btn.is-on').data('status') || 'none';
            if (!slot) return;
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_save_attendance', nonce: ES_ADMIN.nonce,
                user_id: $row.data('user-id'), slot_id: slot,
                group_id: TAB_GROUP, status: status, comment: $(this).val() || ''
            });
        });

        // ── Profile save (Package tab) ──
        $detail.on('click', '.es-profile-save', function () {
            var $form = $('#es-profile-form');
            var $msg  = $form.find('.es-profile-msg');
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_save_student_profile',
                nonce: ES_ADMIN.nonce,
                user_id: $form.data('user-id'),
                parent: $form.find('[name=parent]').val(),
                phone:  $form.find('[name=phone]').val(),
                source: $form.find('[name=source]').val(),
                goal:   $form.find('[name=goal]').val(),
                band:   $form.find('[name=band]').val(),
                notes:  $form.find('[name=notes]').val()
            }).done(function (res) {
                $msg.text(res.success ? '✓ Saved' : 'Save failed').css('color', res.success ? '#10b981' : '#ef4444').show().delay(2000).fadeOut();
            });
        });

        // ── Videos ── (upload / pick from the WordPress media library)
        var esVideoFrame = null;
        $('#es-video-add-btn').on('click', function (e) {
            e.preventDefault();

            if (typeof wp === 'undefined' || !wp.media) {
                alert('The media library is still loading. Please try again in a moment.');
                return;
            }

            // Reuse a single frame instance.
            if (esVideoFrame) { esVideoFrame.open(); return; }

            esVideoFrame = wp.media({
                title: 'Select or upload a lesson video',
                button: { text: 'Use this video' },
                library: { type: 'video' },
                multiple: false
            });

            esVideoFrame.on('select', function () {
                var att = esVideoFrame.state().get('selection').first().toJSON();
                // att: id, url, title, filename, mime, length_formatted (duration)
                var title = att.title || att.filename || 'Lesson video';
                var dur   = att.fileLength || att.length_formatted || '';
                addVideo(title, att.url, dur, att.id);
            });

            esVideoFrame.open();
        });

        $detail.on('click', '.es-video-del', function () {
            var id = $(this).data('id');
            if (!confirm('Delete this video?')) return;
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_delete_video', nonce: ES_ADMIN.nonce, id: id
            }).done(function (res) {
                if (res.success) {
                    $('.es-video-card[data-video-id="' + id + '"]').slideUp(150, function () {
                        $(this).remove();
                        if (!$('#es-video-grid .es-video-card').length) {
                            $('#es-video-grid').html('<p class="es-empty-cell" id="es-video-empty" style="grid-column:1/-1;">No videos yet.</p>');
                        }
                    });
                } else { alert(res.data && res.data.message || 'Delete failed'); }
            });
        });

        function addVideo(title, url, dur, attachmentId) {
            $.post(ES_ADMIN.ajax_url, {
                action: 'es_admin_add_video',
                nonce: ES_ADMIN.nonce,
                target_type: TAB_TYPE,
                target_id: TAB_ID,
                title: title, video_url: url, duration: dur,
                attachment_id: attachmentId || 0
            }).done(function (res) {
                if (res.success && res.data.video) {
                    $('#es-video-empty').remove();
                    var v = res.data.video;
                    var card = $(
                        '<div class="es-video-card" data-video-id="' + v.id + '" style="background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.08);border-radius:10px;overflow:hidden;">' +
                          '<a href="' + v.video_url + '" target="_blank" rel="noopener" style="display:flex;align-items:center;justify-content:center;height:96px;background:rgba(255,255,255,0.04);text-decoration:none;"><span style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.15);color:#fff;display:flex;align-items:center;justify-content:center;">▶</span></a>' +
                          '<div style="padding:10px;"><div style="font-size:12.5px;font-weight:500;color:#fff;word-break:break-word;">' + escapeHtmlPk(v.title) + '</div>' +
                          '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;"><span style="font-size:11px;color:rgba(255,255,255,0.5);">⏱ ' + escapeHtmlPk(v.duration || '—') + '</span>' +
                          '<button type="button" class="es-video-del" data-id="' + v.id + '" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px;">×</button></div></div></div>'
                    );
                    $('#es-video-grid').prepend(card);
                } else {
                    alert(res.data && res.data.message || 'Could not add video.');
                }
            });
        }
    }

    function uploadSessionFile(file) {
        var $card = $('#es-session-files');
        var targetType = $card.data('target-type');
        var targetId   = $card.data('target-id');

        // Client-side extension guard (server re-validates).
        var okExt = /\.(pdf|docx?|pptx?|mp4|mov|webm|mkv|avi)$/i;
        if (!okExt.test(file.name)) {
            alert('Unsupported file type. Allowed: PDF, DOC/DOCX, PPT/PPTX, video.');
            return;
        }

        var fd = new FormData();
        fd.append('action', 'es_admin_upload_session_file');
        fd.append('nonce', ES_ADMIN.nonce);
        fd.append('target_type', targetType);
        fd.append('target_id', targetId);
        fd.append('file', file);

        $('#es-sf-progress').show().text('Uploading "' + file.name + '"…');
        $('#es-sf-upload-btn').prop('disabled', true);

        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#es-sf-progress').hide();
                $('#es-sf-upload-btn').prop('disabled', false);
                if (res.success && res.data.file) {
                    appendFileRow(res.data.file);
                } else {
                    alert('Upload failed: ' + ((res.data && res.data.message) || 'Unknown error'));
                }
            },
            error: function () {
                $('#es-sf-progress').hide();
                $('#es-sf-upload-btn').prop('disabled', false);
                alert('Network error during upload.');
            }
        });
    }

    function appendFileRow(f) {
        $('#es-sf-empty').remove();
        var typeLabel = (f.file_type || 'file').toUpperCase();
        var sizeLabel = f.file_size ? humanSize(parseInt(f.file_size, 10)) : '';
        var row = $(
            '<div class="es-sf-item" data-file-id="' + f.id + '" ' +
            'style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.08);border-radius:8px;margin-bottom:8px;">' +
              '<span class="es-pill es-pill-info" style="min-width:48px;text-align:center;">' + typeLabel + '</span>' +
              '<div style="flex:1;min-width:0;">' +
                '<a href="' + f.file_url + '" target="_blank" rel="noopener" style="color:#fff;text-decoration:none;font-weight:500;word-break:break-all;">' + escapeHtmlPk(f.file_name) + '</a>' +
                (sizeLabel ? '<div class="es-cell-sub" style="font-size:12px;">' + sizeLabel + '</div>' : '') +
              '</div>' +
              '<button type="button" class="es-btn es-btn-sm es-btn-danger es-sf-delete" data-id="' + f.id + '">×</button>' +
            '</div>'
        );
        $('#es-sf-list').prepend(row);
    }

    function deleteSessionFile(id) {
        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: { action: 'es_admin_delete_session_file', nonce: ES_ADMIN.nonce, id: id },
            success: function (res) {
                if (res.success) {
                    $('.es-sf-item[data-file-id="' + id + '"]').slideUp(150, function () {
                        $(this).remove();
                        if (!$('#es-sf-list .es-sf-item').length) {
                            $('#es-sf-list').html('<p class="es-empty-cell" id="es-sf-empty">No files uploaded yet.</p>');
                        }
                    });
                } else {
                    alert('Delete failed: ' + ((res.data && res.data.message) || 'Unknown error'));
                }
            },
            error: function () { alert('Network error.'); }
        });
    }

    function scheduleSession() {
        var $card = $('#es-schedule-session');
        var targetType = $card.data('target-type');
        var targetId   = $card.data('target-id');

        var date = $('#es-ss-date').val();
        var time = $('#es-ss-time').val();
        if (!date) { alert('Please pick a date.'); $('#es-ss-date').focus(); return; }
        if (!time) { alert('Please pick a start time.'); $('#es-ss-time').focus(); return; }

        var $btn = $('#es-ss-submit');
        $btn.prop('disabled', true);
        var $msg = $('#es-ss-msg').hide();

        // v4.4 — which purchased package this session draws from (when shown).
        var paymentId = $('#es-ss-payment').length ? parseInt($('#es-ss-payment').val(), 10) || 0 : 0;

        // v4.4 — files queued in the modal (uploaded AFTER the slot is created
        // so we can attach them to the right slot_id).
        var queuedFiles = ($('#es-ss-files')[0] && $('#es-ss-files')[0].files) ? $('#es-ss-files')[0].files : null;

        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: {
                action: 'es_admin_schedule_session',
                nonce: ES_ADMIN.nonce,
                target_type: targetType,
                target_id: targetId,
                payment_id: paymentId,
                course_id: $('#es-ss-course').length ? ($('#es-ss-course').val() || 0) : 0,
                slot_date: date,
                start_time: time,
                duration_min: $('#es-ss-duration').val() || 30,
                platform: $('#es-ss-platform').val(),
                title: $('#es-ss-title').val(),
                notes: $('#es-ss-notes').val(),
                send_email: $('#es-ss-email').is(':checked') ? 1 : 0
            },
            success: function (res) {
                if (!res.success) {
                    $btn.prop('disabled', false);
                    $msg.css('color', '#f87171').text((res.data && res.data.message) || 'Could not schedule.').show();
                    return;
                }
                var slotId = res.data && res.data.slot_id ? parseInt(res.data.slot_id, 10) : 0;
                var n = queuedFiles ? queuedFiles.length : 0;
                if (slotId && n > 0) {
                    $msg.css('color', '#6366f1').text('Session created. Uploading ' + n + ' file' + (n === 1 ? '' : 's') + '…').show();
                    uploadQueuedFiles(queuedFiles, slotId, targetType, targetId, function(){
                        $msg.css('color', '#10b981').text('✓ Scheduled & files attached. Reloading…').show();
                        setTimeout(function(){ window.location.reload(); }, 600);
                    });
                } else {
                    $msg.css('color', '#10b981').text('✓ ' + res.data.message + ' Reloading…').show();
                    $('#es-ss-title, #es-ss-notes').val('');
                    setTimeout(function(){ window.location.reload(); }, 700);
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                $msg.css('color', '#f87171').text('Network error.').show();
            }
        });
    }

    // v4.4 — upload each queued file sequentially via the existing
    // es_admin_upload_session_file endpoint, attaching them to the new slot.
    function uploadQueuedFiles(files, slotId, targetType, targetId, done) {
        var i = 0;
        function next() {
            if (i >= files.length) { if (done) done(); return; }
            var fd = new FormData();
            fd.append('action',      'es_admin_upload_session_file');
            fd.append('nonce',       ES_ADMIN.nonce);
            fd.append('target_type', targetType);
            fd.append('target_id',   targetId);
            fd.append('slot_id',     slotId);
            fd.append('file',        files[i]);
            $.ajax({
                url: ES_ADMIN.ajax_url,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                complete: function () { i++; next(); }
            });
        }
        next();
    }

    function humanSize(bytes) {
        if (!bytes || bytes < 1024) return (bytes || 0) + ' B';
        var units = ['KB', 'MB', 'GB'], i = -1;
        do { bytes /= 1024; i++; } while (bytes >= 1024 && i < units.length - 1);
        return bytes.toFixed(1) + ' ' + units[i];
    }

    function escapeHtmlPk(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /* ============ PUBLIC PACKAGES PAGE (FRONTEND) ============ */
    if ($('.es-pp-shell').length || $('.es-public-packages').length) {

        var $root = $('.es-pp-shell').length ? $('.es-pp-shell') : $('.es-public-packages');
        var currentCycle = $root.data('default-cycle') || 'monthly';
        var yearlyDiscount = parseInt($root.data('yearly-discount'), 10) || 0;

        // Currency symbols for the live order-summary preview (display only;
        // the server formats the authoritative amount on charge).
        var ES_PP_SYMBOLS = {
            INR: '₹', USD: '$', EUR: '€', GBP: '£', AUD: 'A$',
            CAD: 'C$', AED: 'AED ', SGD: 'S$', JPY: '¥', NZD: 'NZ$'
        };

        var stripe = null;
        var elements = null;
        var stripeReady = !!(window.Stripe && window.ES_FE && ES_FE.stripe && ES_FE.stripe.enabled && ES_FE.stripe.publishable_key);
        if (stripeReady) {
            try {
                stripe = window.Stripe(ES_FE.stripe.publishable_key);
            } catch (e) {
                stripeReady = false;
                console.error('Stripe init failed:', e);
            }
        }

        var $panel   = $('#es-pp-pay-panel');
        var $overlay = $('#es-pp-overlay');

        // v3.9.6 — split card fields: card number on its own row, then
        // expiry + CVC side by side. We use Stripe's individual Elements
        // (cardNumber / cardExpiry / cardCvc) instead of the combined card.
        var cardNumber = null;
        var cardExpiry = null;
        var cardCvc    = null;

        // Mount the split Card Elements once the panel is in the DOM
        function ensureCardElement() {
            if (!stripeReady || cardNumber) return;
            elements = stripe.elements();

            var baseStyle = {
                base: {
                    color: '#1e293b',
                    fontFamily: '-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif',
                    fontSize: '15px',
                    '::placeholder': { color: '#94a3b8' }
                },
                invalid: { color: '#dc2626' }
            };

            cardNumber = elements.create('cardNumber', { style: baseStyle, showIcon: true });
            cardExpiry = elements.create('cardExpiry', { style: baseStyle });
            cardCvc    = elements.create('cardCvc',    { style: baseStyle });

            cardNumber.mount('#es-pp-card-number');
            cardExpiry.mount('#es-pp-card-expiry');
            cardCvc.mount('#es-pp-card-cvc');

            // Surface validation errors from any of the three fields
            var showErr = function (event) {
                if (event.error) { showCardError(event.error.message); }
                else { clearCardError(); }
            };
            cardNumber.on('change', showErr);
            cardExpiry.on('change', showErr);
            cardCvc.on('change', showErr);
        }

        // Toggle switch (Monthly ⇄ Yearly/Semester)
        $(document).on('click', '#es-pp-cycle-switch', function () {
            currentCycle = (currentCycle === 'monthly') ? 'yearly' : 'monthly';
            $(this).toggleClass('is-yearly', currentCycle === 'yearly');
            $('.es-pp-toggle-label').removeClass('is-active');
            $('.es-pp-toggle-label[data-side="' + currentCycle + '"]').addClass('is-active');

            if (currentCycle === 'yearly') {
                $('.es-pp-amount-monthly').hide();
                $('.es-pp-amount-yearly').show();
                // Legacy classes (kept for any embed using older markup)
                $('.es-price-monthly').hide();
                $('.es-price-yearly').show();
            } else {
                $('.es-pp-amount-yearly').hide();
                $('.es-pp-amount-monthly').show();
                $('.es-price-yearly').hide();
                $('.es-price-monthly').show();
            }

            // Panel is always visible now — refresh the summary + total so the
            // selected plan's price reflects the new cycle.
            updateAllCardBreakdowns();
            updatePaymentSummary();
        });

        // Discounted ("yearly") tab bills the package's DISCOUNT months (v4.3.3).

        // Compute the total payable for a card under the active cycle.
        //   monthly cycle  → monthly_price × package_months
        //   discounted tab → bill ONLY the discount_months:
        //        term  = discount_months (fallback to package months if unset)
        //        total = (term × monthly) − (monthly × discount_months × discount% / 100)
        //
        // The displayed DURATION and the charged amount both follow the discount
        // months, so a "5 discount months" plan is billed + shown for 5 months,
        // not the package's default duration. This mirrors the server's
        // ES_Stripe::resolve_price() / effective_term_months() exactly.
        function computeCardTotal($c, isYearly) {
            var monthly  = parseFloat($c.data('monthly')) || 0;
            var pkgMonths = parseInt($c.data('months'), 10) || 1;
            var discountmonths = parseInt($c.data('discount-months'), 10) || pkgMonths;
            if (!isYearly) {
                return { total: monthly * pkgMonths, months: pkgMonths };
            }
            var discPct    = parseFloat($c.data('discount-percent')) || 0;
            var discMonths = discountmonths;
            var termMonths = discMonths > 0 ? discMonths : pkgMonths;
            var gross      = monthly * termMonths;
            var discountAmt = (discPct > 0 && discMonths > 0)
                ? (monthly * discMonths * discPct / 100)
                : 0;
            return { total: Math.max(0, gross - discountAmt), months: termMonths };
        }

        // Recompute the per-card "Total payable" line for the active cycle.
        function updateAllCardBreakdowns() {
            var isYearly = currentCycle === 'yearly';
            $('.es-pp-card').each(function () {
                var $c = $(this);
                var res       = computeCardTotal($c, isYearly);
                var total     = res.total;
                var effMonths = res.months;
                var cur       = ($c.data('currency') || 'INR').toString();
                var sym       = ES_PP_SYMBOLS[cur] || (cur + ' ');
                var totalStr  = sym + total.toLocaleString(undefined, {
                    minimumFractionDigits: 0, maximumFractionDigits: 2
                });
                $c.find('.es-pp-bd-total-amount').text(totalStr);
                // Update the duration row inside the breakdown too.
                $c.find('.es-pp-bd-duration').text(effMonths + ' month' + (effMonths > 1 ? 's' : ''));
            });
        }

        function updatePaymentSummary() {
            var pkgId = $('#es-pp-package-id').val();
            if (!pkgId) return;
            var $card = $('.es-pp-card[data-package-id="' + pkgId + '"]');
            if (!$card.length) return;

            var isYearly       = currentCycle === 'yearly';
            var name           = $card.data('package-name') || '';
            var pkgMonths      = parseInt($card.data('months'), 10) || 1;
            var cardDiscount   = parseFloat($card.data('discount-percent')) || 0;
            var discMonths     = parseInt($card.data('discount-months'), 10) || 0;
            var monthlyLabel   = isYearly
                ? ($card.data('yearly-label') || '')
                : ($card.data('monthly-label') || '');
            var totalSessions  = parseInt($card.data('total-sessions'), 10) || 0;
            var monthlyLimit   = parseInt($card.data('monthly-limit'), 10) || 0;

            // Effective duration + total (discounted tab = package's own months).
            var res        = computeCardTotal($card, isYearly);
            var totalVal   = res.total;
            var effMonths  = res.months;

            var cur        = ($card.data('currency') || 'INR').toString();
            var sym        = ES_PP_SYMBOLS[cur] || (cur + ' ');
            var totalLabel = sym + totalVal.toLocaleString(undefined, {
                minimumFractionDigits: 0, maximumFractionDigits: 2
            });

            var cycleLabel = 'Standard';
            if (isYearly) {
                cycleLabel = (cardDiscount > 0 && discMonths > 0)
                    ? ('Discounted (' + cardDiscount + '% off ' + discMonths + ' mo)')
                    : ('Discounted (' + effMonths + ' mo)');
            }

            $('#es-pp-summary-plan').text(name + ' — ' + cycleLabel);
            $('#es-pp-summary-monthly').text(monthlyLabel + ' / month');
            $('#es-pp-summary-months').text(effMonths + ' month' + (effMonths > 1 ? 's' : ''));

            if ($('#es-pp-summary-sessions').length) {
                // Both cycles grant the package's own months of allowance, so the
                // session total is the same regardless of the discount toggle.
                var effSessions = (monthlyLimit > 0) ? (monthlyLimit * effMonths) : totalSessions;
                if (effSessions > 0) {
                    $('#es-pp-summary-sessions').text(
                        effSessions + (monthlyLimit > 0 ? (' (' + monthlyLimit + '/mo)') : '')
                    );
                } else {
                    $('#es-pp-summary-sessions').text('—');
                }
            }

            $('#es-pp-summary-amount').text(totalLabel);
            $('#es-pp-cycle').val(currentCycle);
            $('.es-pp-pay-submit-text').text('Pay ' + totalLabel);
        }

        function openPanel(pkgId) {
            $('#es-pp-package-id').val(pkgId);
            updatePaymentSummary();
            ensureCardElement();

            // v3.9.7 — The panel is always visible (a right column), so we no
            // longer slide it in. Instead we highlight the chosen card, mark
            // the panel active, and on small screens scroll it into view.
            $('.es-pp-card').removeClass('is-selected');
            $('.es-pp-card[data-package-id="' + pkgId + '"]').addClass('is-selected');
            $panel.addClass('is-active');

            // Reset any prior error/disabled state
            $('#es-pp-pay-submit').prop('disabled', false);
            clearCardError();

            $overlay.addClass('is-active').attr('aria-hidden', 'false');
            $panel.attr('aria-hidden', 'false');
            $('body').addClass('es-modal-open');
        }

        function closePanel() {
            $panel.removeClass('is-active').attr('aria-hidden', 'true');
            $overlay.removeClass('is-active').attr('aria-hidden', 'true');
            $('body').removeClass('es-modal-open');
            $('.es-pp-card').removeClass('is-selected');
        }

        // Select This Plan → open inline payment panel (same page, no redirect)
        $(document).on('click', '.es-pp-select-btn, .es-pkg-pay-btn, .es-pkg-select-btn', function () {
            var $btn = $(this);
            var pkgId = $btn.data('package-id');

            // v3.9.7 — Logged-OUT visitor: open the in-page login modal (same
            // window). After they log in, the page reloads in its logged-in
            // state and the payment form becomes available.
            if ($btn.data('guest')) {
                openLoginModal();
                return;
            }

            var userId = $btn.data('user-id');
            var token = $btn.data('token');

            if (!pkgId || !userId || !token) {
                alert('Missing selection details. Please refresh the page.');
                return;
            }

            // Preferred flow: inline Stripe Elements panel — stays on this page
            if (stripeReady && $panel.length) {
                openPanel(pkgId);
                return;
            }

            // No Stripe — simple selection (mark as chosen, no payment)
            if (!confirm('Confirm your selection?')) return;
            selectPackage(pkgId, userId, token, $btn);
        });

        /* ─── In-page login modal (guests) ─── */
        var $loginModal = $('#es-login-modal');

        function openLoginModal() {
            if (!$loginModal.length) {
                // No modal markup (shouldn't happen for guests) — hard fallback
                window.location.href = (typeof ES_FE !== 'undefined' && ES_FE.login_url) ? ES_FE.login_url : '/wp-login.php';
                return;
            }
            $loginModal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').addClass('es-modal-open');
            setTimeout(function () {
                $loginModal.find('input[name="email"]').trigger('focus');
            }, 150);
        }

        function closeLoginModal() {
            $loginModal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').removeClass('es-modal-open');
        }

        $(document).on('click', '#es-login-modal-close, #es-login-modal-overlay', closeLoginModal);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $loginModal.hasClass('is-open')) closeLoginModal();
        });
        // Note: on successful login, frontend.js reloads the page (it detects
        // the hidden es_login_reload field), so we don't need a watcher here.

        $(document).on('click', '#es-pp-pay-close, #es-pp-overlay', function () {
            closePanel();
        });

        // Submit payment form
        $(document).on('submit', '#es-pp-pay-form', function (e) {
            e.preventDefault();
            submitInlinePayment();
        });

        function submitInlinePayment() {
            if (!stripeReady || !cardNumber) {
                alert('Payment system is not ready. Please refresh and try again.');
                return;
            }

            var pkgId = $('#es-pp-package-id').val();
            var userId = $('#es-pp-user-id').val();
            var token = $('#es-pp-token').val();
            var name = $('#es-pp-name').val().trim();
            var email = $('#es-pp-email').val().trim();

            // Billing address (required for INR / India card payments)
            var addrLine1  = $('#es-pp-addr-line1').val().trim();
            var addrCity   = $('#es-pp-addr-city').val().trim();
            var addrState  = $('#es-pp-addr-state').val().trim();
            var addrPostal = $('#es-pp-addr-postal').val().trim();
            var addrCountry= $('#es-pp-addr-country').val();

            // Always trust currentCycle as the single source of truth — the
            // hidden field is kept in sync, but if it ever drifted, currentCycle
            // is what the user just saw on screen. That's what we charge.
            var cycle = currentCycle;
            $('#es-pp-cycle').val(cycle);

            if (!name) { $('#es-pp-name').focus(); alert('Please enter your name.'); return; }
            if (!email || email.indexOf('@') === -1) { $('#es-pp-email').focus(); alert('Please enter a valid email.'); return; }
            if (!addrLine1)   { $('#es-pp-addr-line1').focus();  alert('Please enter your billing address.'); return; }
            if (!addrCity)    { $('#es-pp-addr-city').focus();   alert('Please enter your city.'); return; }
            if (!addrPostal)  { $('#es-pp-addr-postal').focus(); alert('Please enter your postal / PIN code.'); return; }
            if (!addrCountry) { $('#es-pp-addr-country').focus(); alert('Please select your country.'); return; }

            var $submit = $('#es-pp-pay-submit');
            var origText = $('.es-pp-pay-submit-text').text();
            $submit.prop('disabled', true);
            $('.es-pp-pay-submit-text').text('Processing...');
            clearCardError();

            // 1) Create PaymentIntent on the server (also saves address to profile)
            $.ajax({
                url: ES_FE.ajax_url,
                method: 'POST',
                data: {
                    action: 'es_stripe_create_intent',
                    nonce: ES_FE.nonce,
                    package_id: pkgId,
                    user_id: userId,
                    token: token,
                    billing_cycle: cycle,
                    name: name,
                    email: email,
                    addr_line1:  addrLine1,
                    addr_city:   addrCity,
                    addr_state:  addrState,
                    addr_postal: addrPostal,
                    addr_country: addrCountry
                },
                success: function (res) {
                    if (!res.success) {
                        showCardError((res.data && res.data.message) || 'Could not start payment.');
                        $submit.prop('disabled', false);
                        $('.es-pp-pay-submit-text').text(origText);
                        return;
                    }
                    var clientSecret = res.data.client_secret;
                    var piId = res.data.payment_intent_id;

                    // 2) Confirm card payment client-side via Stripe Elements.
                    //    We pass the full billing_details (incl. address) so
                    //    Stripe accepts INR/India transactions per RBI rules.
                    stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: cardNumber,
                            billing_details: {
                                name: name,
                                email: email,
                                address: {
                                    line1:       addrLine1,
                                    city:        addrCity,
                                    state:       addrState,
                                    postal_code: addrPostal,
                                    country:     addrCountry
                                }
                            }
                        }
                    }).then(function (result) {
                        if (result.error) {
                            showCardError(result.error.message || 'Payment failed.');
                            $submit.prop('disabled', false);
                            $('.es-pp-pay-submit-text').text(origText);
                            return;
                        }
                        if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                            finalizePayment(piId, $submit, origText);
                        } else {
                            showCardError('Payment status: ' + (result.paymentIntent ? result.paymentIntent.status : 'unknown'));
                            $submit.prop('disabled', false);
                            $('.es-pp-pay-submit-text').text(origText);
                        }
                    });
                },
                error: function () {
                    showCardError('Network error. Please try again.');
                    $submit.prop('disabled', false);
                    $('.es-pp-pay-submit-text').text(origText);
                }
            });
        }

        // Render an error inside the styled error box. We keep Stripe's exact
        // wording (e.g. the India-exports notice) but present it cleanly with
        // an icon, and auto-link any URL Stripe includes in the message.
        function showCardError(raw) {
            var msg = String(raw || '');
            // Linkify any bare URL so "More info here: https://…" is clickable.
            var html = msg.replace(
                /(https?:\/\/[^\s]+)/g,
                '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
            );
            $('#es-pp-card-errors')
                .addClass('es-pp-card-errors-box')
                .html('<span class="es-pp-err-icon">⚠</span><span class="es-pp-err-text">' + html + '</span>');
        }

        function clearCardError() {
            $('#es-pp-card-errors').removeClass('es-pp-card-errors-box').empty();
        }

        function finalizePayment(piId, $submit, origText) {
            $.ajax({
                url: ES_FE.ajax_url,
                method: 'POST',
                data: {
                    action: 'es_stripe_finalize_intent',
                    nonce: ES_FE.nonce,
                    payment_intent_id: piId
                },
                success: function (res) {
                    if (!res.success) {
                        showCardError((res.data && res.data.message) || 'Could not confirm payment.');
                        $submit.prop('disabled', false);
                        $('.es-pp-pay-submit-text').text(origText);
                        return;
                    }
                    // Success — replace the panel content with a thank-you state.
                    var data = res.data || {};
                    var validLine = data.valid_until
                        ? '<div style="color:#94a3b8;font-size:12px;margin:14px 0 4px">Active Until</div>' +
                          '<div style="color:#fff;font-weight:500">' + data.valid_until + '</div>'
                        : '';
                    var thanksHtml =
                        '<div class="es-pp-pay-thanks" style="text-align:center;padding:30px 6px 10px">' +
                        '  <div style="width:64px;height:64px;border-radius:50%;background:#10b981;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px">' +
                        '    <span style="font-size:34px;color:#fff;line-height:1">✓</span>' +
                        '  </div>' +
                        '  <h2 style="color:#fff;font-size:22px;margin:0 0 6px;font-weight:600">Payment Successful</h2>' +
                        '  <p style="color:#cbd5e1;font-size:14px;margin:0 0 20px">Thank you! A receipt has been emailed to you.</p>' +
                        '  <div style="background:rgba(255,255,255,.06);border-radius:10px;padding:16px 18px;color:#fff;font-size:14px;text-align:left">' +
                        '    <div style="color:#94a3b8;font-size:12px;margin-bottom:4px">Plan</div>' +
                        '    <div style="font-weight:600;margin-bottom:12px">' + (data.package_name || '') + '</div>' +
                        '    <div style="color:#94a3b8;font-size:12px;margin-bottom:4px">Amount Paid</div>' +
                        '    <div style="color:#caa657;font-weight:700;font-size:18px">' + (data.amount_label || '') + '</div>' +
                        validLine +
                        '  </div>' +
                        '</div>';
                    $('#es-pp-pay-form').replaceWith(thanksHtml);
                },
                error: function () {
                    showCardError('Payment confirmed but we couldn\'t finalize. Contact support.');
                    $submit.prop('disabled', false);
                    $('.es-pp-pay-submit-text').text(origText);
                }
            });
        }

        // Hosted Checkout fallback (old flow)
        function redirectToHostedCheckout(packageId, userId, token, $btn) {
            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('Redirecting...');
            $.ajax({
                url: ES_FE.ajax_url,
                method: 'POST',
                data: {
                    action: 'es_stripe_create_checkout',
                    nonce: ES_FE.nonce,
                    package_id: packageId,
                    user_id: userId,
                    token: token,
                    billing_cycle: currentCycle
                },
                success: function (res) {
                    if (res.success && res.data.url) {
                        window.location.href = res.data.url;
                    } else {
                        alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Could not start checkout.'));
                        $btn.prop('disabled', false).html(origHtml);
                    }
                },
                error: function () {
                    alert('Network error. Please try again.');
                    $btn.prop('disabled', false).html(origHtml);
                }
            });
        }

        $(document).on('click', '.es-pkg-contact-btn', function () {
            alert('Please contact us to learn more about this package.');
        });

        // v3.9.7 — When the payment column is always visible (logged-in users),
        // pre-select a package so the form shows a real plan from the start.
        // Prefer the featured/recommended card, else the first one.
        if (stripeReady && $panel.length && $('.es-pp-card').length) {
            var $preferred = $('.es-pp-card.is-featured').not('.is-owned').first();
            if (!$preferred.length) $preferred = $('.es-pp-card').not('.is-owned').first();
            var preId = $preferred.data('package-id');
            if (preId) {
                openPanel(preId);
            }
        }

        // Ensure the per-card "Total payable" lines reflect the active cycle
        // (matters when the page defaults to the yearly tab).
        updateAllCardBreakdowns();
    }

    /* ============ HELPERS — PACKAGES ============ */

    function resetPackageModal() {
        $('#es-pkg-id').val('');
        $('#es-pkg-name').val('');
        $('#es-pkg-subheading').val('');
        $('#es-pkg-currency').val('INR');
        $('#es-pkg-monthly-price').val('');
        $('#es-pkg-months').val('1');
        $('#es-pkg-monthly-sessions').val('');
        $('#es-pkg-discount-percent').val('');
        $('#es-pkg-discount-months').val('');
        $('#es-pkg-price').val('');
        $('#es-pkg-tagline').val('');
        $('#es-pkg-description').val('');
        $('#es-pkg-order').val('0');
        // Hidden field — packages are visible to students by default
        $('#es-pkg-active').val('1');
        recalcPackageTotals();
    }

    // Currency symbols for the live preview (display only — server formats on save)
    var ES_CURRENCY_SYMBOLS = {
        INR: '₹', USD: '$', EUR: '€', GBP: '£', AUD: 'A$',
        CAD: 'C$', AED: 'AED ', SGD: 'S$', JPY: '¥', NZD: 'NZ$'
    };

    function recalcPackageTotals() {
        var monthly  = parseFloat($('#es-pkg-monthly-price').val()) || 0;
        var months   = parseInt($('#es-pkg-months').val(), 10) || 1;
        if (months < 1) months = 1;
        var perMonth = parseInt($('#es-pkg-monthly-sessions').val(), 10) || 0;

        var total       = monthly * months;
        var totalSess   = perMonth * months;
        var cur         = $('#es-pkg-currency').val() || 'INR';
        var sym         = ES_CURRENCY_SYMBOLS[cur] || (cur + ' ');

        // Format with thousands separators
        var totalStr = sym + total.toLocaleString(undefined, {
            minimumFractionDigits: 0, maximumFractionDigits: 2
        });

        $('#es-pkg-calc-total').text(monthly > 0 ? totalStr : '—');
        $('#es-pkg-calc-sessions').text(perMonth > 0 ? (totalSess + ' sessions') : '—');

        // Mirror the computed total into the hidden price field that gets saved.
        $('#es-pkg-price').val(total.toFixed(2));
    }

    function loadPackageForEdit(id) {
        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: { action: 'es_admin_get_package', nonce: ES_ADMIN.nonce, id: id },
            success: function (res) {
                if (res.success && res.data.package) {
                    var p = res.data.package;
                    $('#es-pkg-id').val(p.id);
                    $('#es-pkg-name').val(p.package_name);
                    $('#es-pkg-subheading').val(p.sub_heading);
                    $('#es-pkg-currency').val(p.currency || 'INR');
                    // New monthly model. Fall back to legacy `price` as the
                    // monthly figure for packages created before this update.
                    var months = parseInt(p.months, 10) || 1;
                    var monthly = (p.monthly_price && parseFloat(p.monthly_price) > 0)
                        ? p.monthly_price
                        : p.price;
                    $('#es-pkg-months').val(months);
                    $('#es-pkg-monthly-price').val(monthly);
                    $('#es-pkg-monthly-sessions').val(p.monthly_session_limit || '');
                    $('#es-pkg-discount-percent').val(p.discount_percent || '');
                    $('#es-pkg-discount-months').val(p.discount_months || '');
                    $('#es-pkg-price').val(p.price);
                    $('#es-pkg-tagline').val(p.tagline);
                    $('#es-pkg-description').val(p.description);
                    $('#es-pkg-order').val(p.display_order);
                    // Preserve existing active state silently (default 1)
                    $('#es-pkg-active').val(p.is_active == 0 ? '0' : '1');
                    recalcPackageTotals();
                    $('#es-pkg-modal-title').text('Edit Package');
                    $('#es-pkg-save-text').text('Update Package');
                    $('#es-package-modal').fadeIn(200);
                } else {
                    alert('Failed to load package');
                }
            }
        });
    }

    function savePackage() {
        var id = $('#es-pkg-id').val();
        var name = $('#es-pkg-name').val().trim();
        if (!name) { alert('Package name is required'); $('#es-pkg-name').focus(); return; }

        var months = parseInt($('#es-pkg-months').val(), 10) || 1;
        if (months < 1) { alert('Duration must be at least 1 month'); $('#es-pkg-months').focus(); return; }

        var $btn = $('#es-pkg-save');
        $btn.prop('disabled', true);
        $('#es-pkg-save-text').text('Saving...');

        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: {
                action: 'es_admin_save_package',
                nonce: ES_ADMIN.nonce,
                id: id,
                package_name: name,
                sub_heading: $('#es-pkg-subheading').val(),
                currency: $('#es-pkg-currency').val(),
                monthly_price: $('#es-pkg-monthly-price').val(),
                months: months,
                monthly_session_limit: $('#es-pkg-monthly-sessions').val(),
                discount_percent: $('#es-pkg-discount-percent').val(),
                discount_months: $('#es-pkg-discount-months').val(),
                tagline: $('#es-pkg-tagline').val(),
                description: $('#es-pkg-description').val(),
                display_order: $('#es-pkg-order').val(),
                is_active: parseInt($('#es-pkg-active').val(), 10) === 0 ? 0 : 1
            },
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (res.data.message || 'Save failed'));
                    $btn.prop('disabled', false);
                    $('#es-pkg-save-text').text(id > 0 ? 'Update Package' : 'Create Package');
                }
            },
            error: function () {
                alert('Network error.');
                $btn.prop('disabled', false);
                $('#es-pkg-save-text').text(id > 0 ? 'Update Package' : 'Create Package');
            }
        });
    }

    function deletePackage(id) {
        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: { action: 'es_admin_delete_package', nonce: ES_ADMIN.nonce, id: id },
            success: function (res) {
                if (res.success) location.reload();
                else alert('Error: ' + (res.data.message || 'Delete failed'));
            }
        });
    }

    /* ============ HELPERS — AFTER CALL ============ */

    function toggleAfterCallFields() {
        var outcome = $('#es-after-call-outcome').val();

        if (outcome === 'Group Student') {
            $('#es-group-field').slideDown(150);
            $('#es-group-package-note').slideDown(150);
            $('#es-package-field').slideUp(150);
            $('#es-course-after-call-field').slideUp(150);
        } else {
            $('#es-group-field').slideUp(150);
            $('#es-group-package-note').slideUp(150);
            if (outcome === '1:1 Student') {
                $('#es-package-field').slideDown(150);
                $('#es-course-after-call-field').slideDown(150);
            } else {
                $('#es-package-field').slideUp(150);
                $('#es-course-after-call-field').slideUp(150);
            }
        }
    }

    function submitAfterCall() {
        var $section = $('#es-after-call-section');
        var userId   = $section.data('user-id');
        var outcome  = $('#es-after-call-outcome').val();
        var groupId  = $('#es-after-call-group').val();
        var comments = $('#es-after-call-comments').val();

        var packageIds = [];
        $('.es-pkg-check:checked').each(function () {
            packageIds.push(parseInt($(this).val(), 10));
        });

        // Courses selected on this call — pass them so the back end can both
        // (a) save them on the student profile and (b) include the course
        // names in the After-Call email's subject and body.
        var courseId = $('#es-after-call-course').val();
        var courseIds = courseId ? [parseInt(courseId, 10)] : [];
        if (outcome === 'Group Student') {
            packageIds = [];
            courseIds = [];
        }

        if (!outcome) { alert('Please select an outcome'); return; }

        var needsPkg = (outcome === '1:1 Student');
        if (needsPkg && packageIds.length === 0) {
            alert('Please select at least one package');
            return;
        }
        if (outcome === 'Group Student' && !groupId) {
            alert('Please select a group');
            return;
        }

        var $btn = $('#es-after-call-submit');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation:spin 1s linear infinite"></span> Processing...');

        var data = {
            action: 'es_after_call_convert',
            nonce: ES_ADMIN.nonce,
            user_id: userId,
            outcome: outcome,
            group_id: groupId || 0,
            comments: comments,
            'package_ids[]': packageIds,
            'course_ids[]':  courseIds
        };

        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: data,
            success: function (res) {
                if (res.success) {
                    // Show a brief success state, then reload to refresh
                    // the share-link card / outcome card / pills.
                    $btn.html('<span class="dashicons dashicons-yes"></span> Saved — reloading...');
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    alert('Error: ' + (res.data.message || 'Failed'));
                    $btn.prop('disabled', false).html(origHtml);
                }
            },
            error: function () {
                alert('Network error.');
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    }

    /* ============ HELPERS — GROUPS ============ */

    function resetGroupModal() {
        $('#es-group-id').val('');
        $('#es-group-name').val('');
        $('#es-group-notes').val('');
    }

    function loadGroupForEdit(id) {
        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: { action: 'es_admin_get_group', nonce: ES_ADMIN.nonce, id: id },
            success: function (res) {
                if (res.success && res.data.group) {
                    var g = res.data.group;
                    $('#es-group-id').val(g.id);
                    $('#es-group-name').val(g.group_name);
                    $('#es-group-notes').val(g.notes || '');
                    $('#es-group-modal-title').text('Edit Group');
                    $('#es-group-save-text').text('Update Group');
                    $('#es-group-modal').fadeIn(200);
                } else {
                    alert('Failed to load group');
                }
            }
        });
    }

    function saveGroup() {
        var id = $('#es-group-id').val();
        var name = $('#es-group-name').val().trim();
        if (!name) { alert('Group name is required'); $('#es-group-name').focus(); return; }

        var $btn = $('#es-group-save');
        $btn.prop('disabled', true);
        $('#es-group-save-text').text('Saving...');

        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: {
                action: 'es_admin_save_group',
                nonce: ES_ADMIN.nonce,
                id: id,
                group_name: name,
                notes: $('#es-group-notes').val()
            },
            success: function (res) {
                if (res.success) location.reload();
                else {
                    alert('Error: ' + (res.data.message || 'Save failed'));
                    $btn.prop('disabled', false);
                    $('#es-group-save-text').text(id > 0 ? 'Update Group' : 'Create Group');
                }
            }
        });
    }

    function deleteGroup(id) {
        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: { action: 'es_admin_delete_group', nonce: ES_ADMIN.nonce, id: id },
            success: function (res) {
                if (res.success) location.href = window.location.pathname + '?page=eduschedule-groups';
                else alert('Error: ' + (res.data.message || 'Delete failed'));
            }
        });
    }

    function addGroupMember() {
        var gid = $('#es-add-member-save').data('group-id');
        var uid = $('#es-add-member-user').val();
        var $btn = $('#es-add-member-save');
        var $msg = $('#es-add-member-msg');
        if (!uid) {
            $msg.css({display:'block', background:'#fee2e2', color:'#b91c1c'}).text('Please select a student.');
            return;
        }
        $btn.prop('disabled', true).text('Adding...');
        $.post(ES_ADMIN.ajax_url, {
            action: 'es_admin_add_group_member',
            nonce: ES_ADMIN.nonce,
            group_id: gid,
            user_id: uid
        }).done(function(res){
            if (res && res.success) {
                $msg.css({display:'block', background:'#d1fae5', color:'#065f46'}).text('Student added. Reloading...');
                setTimeout(function(){ location.reload(); }, 700);
            } else {
                $btn.prop('disabled', false).text('Add Student');
                $msg.css({display:'block', background:'#fee2e2', color:'#b91c1c'}).text((res && res.data && res.data.message) || 'Could not add student.');
            }
        }).fail(function(){
            $btn.prop('disabled', false).text('Add Student');
            $msg.css({display:'block', background:'#fee2e2', color:'#b91c1c'}).text('Server error.');
        });
    }

    function removeGroupMember(gid, uid) {
        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: { action: 'es_admin_remove_group_member', nonce: ES_ADMIN.nonce, group_id: gid, user_id: uid },
            success: function (res) {
                if (res.success) location.reload();
                else alert('Error: ' + (res.data.message || 'Failed'));
            }
        });
    }

    /* ============ HELPERS — PUBLIC ============ */

    function selectPackage(packageId, userId, token, $btn) {
        var originalText = $btn.text();
        $btn.prop('disabled', true).text('Processing...');
        $('.es-pkg-select-btn').not($btn).prop('disabled', true).css('opacity', '0.5');

        $.ajax({
            url: ES_FE.ajax_url,
            method: 'POST',
            data: {
                action: 'es_student_select_package',
                nonce: ES_FE.nonce,
                package_id: packageId,
                user_id: userId,
                token: token
            },
            success: function (res) {
                if (res.success) {
                    if (res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        $btn.text('✓ Selected');
                    }
                } else {
                    alert('Error: ' + (res.data.message || 'Failed'));
                    $btn.prop('disabled', false).text(originalText);
                    $('.es-pkg-select-btn').prop('disabled', false).css('opacity', '1');
                }
            },
            error: function () {
                alert('Network error.');
                $btn.prop('disabled', false).text(originalText);
                $('.es-pkg-select-btn').prop('disabled', false).css('opacity', '1');
            }
        });
    }

})(jQuery);
