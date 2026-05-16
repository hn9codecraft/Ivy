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

        $('#es-package-modal').on('click', '.es-modal-close, .es-modal-backdrop', function () {
            $('#es-package-modal').fadeOut(200);
        });
    }

    /* ============ STUDENT DETAIL — AFTER CALL ============ */
    if ($('.es-student-detail-page').length) {

        // After Call button
        $('#es-after-call-btn').on('click', function () {
            $('#es-after-call-modal').fadeIn(200);
            // Initialize visibility based on currently selected outcome
            toggleAfterCallFields();
        });

        // Outcome change → show/hide group field
        $(document).on('change', '#es-after-call-outcome', toggleAfterCallFields);

        // Submit
        $('#es-after-call-submit').on('click', submitAfterCall);

        // Modal close
        $('#es-after-call-modal').on('click', '.es-modal-close, .es-modal-backdrop', function () {
            $('#es-after-call-modal').fadeOut(200);
        });

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

        $('#es-group-save').on('click', saveGroup);

        $('#es-group-modal').on('click', '.es-modal-close, .es-modal-backdrop', function () {
            $('#es-group-modal').fadeOut(200);
        });
    }

    /* ============ PUBLIC PACKAGES PAGE (FRONTEND) ============ */
    if ($('.es-public-packages').length) {

        $(document).on('click', '.es-pkg-select-btn', function () {
            var $btn = $(this);
            var packageId = $btn.data('package-id');
            var userId = $btn.data('user-id');
            var token = $btn.data('token');

            if (!confirm('Confirm your selection?')) return;
            selectPackage(packageId, userId, token, $btn);
        });

        $(document).on('click', '.es-pkg-contact-btn', function () {
            alert('Please contact us to learn more about this package.');
        });
    }

    /* ============ HELPERS — PACKAGES ============ */

    function resetPackageModal() {
        $('#es-pkg-id').val('');
        $('#es-pkg-name').val('');
        $('#es-pkg-subheading').val('');
        $('#es-pkg-price').val('');
        $('#es-pkg-hours').val('');
        $('#es-pkg-tagline').val('');
        $('#es-pkg-description').val('');
        $('#es-pkg-order').val('0');
        $('#es-pkg-active').prop('checked', true);
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
                    $('#es-pkg-price').val(p.price);
                    $('#es-pkg-hours').val(p.hours);
                    $('#es-pkg-tagline').val(p.tagline);
                    $('#es-pkg-description').val(p.description);
                    $('#es-pkg-order').val(p.display_order);
                    $('#es-pkg-active').prop('checked', p.is_active == 1);
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
                price: $('#es-pkg-price').val(),
                hours: $('#es-pkg-hours').val(),
                tagline: $('#es-pkg-tagline').val(),
                description: $('#es-pkg-description').val(),
                display_order: $('#es-pkg-order').val(),
                is_active: $('#es-pkg-active').is(':checked') ? 1 : 0
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

        // Group field shows only for Group Student
        if (outcome === 'Group Student') {
            $('#es-group-field').slideDown(150);
        } else {
            $('#es-group-field').slideUp(150);
        }

        // Package field shows for 1:1 Student or Group Student
        if (outcome === '1:1 Student' || outcome === 'Group Student') {
            $('#es-package-field').slideDown(150);
        } else {
            $('#es-package-field').slideUp(150);
        }
    }

    function submitAfterCall() {
        var userId   = $('#es-after-call-modal').data('user-id');
        var outcome  = $('#es-after-call-outcome').val();
        var groupId  = $('#es-after-call-group').val();
        var comments = $('#es-after-call-comments').val();

        var packageIds = [];
        $('.es-pkg-check:checked').each(function () {
            packageIds.push(parseInt($(this).val(), 10));
        });

        if (!outcome) { alert('Please select an outcome'); return; }

        var needsPkg = (outcome === '1:1 Student' || outcome === 'Group Student');
        if (needsPkg && packageIds.length === 0) {
            alert('Please select at least one package');
            return;
        }
        if (outcome === 'Group Student' && !groupId) {
            alert('Please select a group');
            return;
        }

        var $btn = $('#es-after-call-submit');
        $btn.prop('disabled', true).text('Processing...');

        var data = {
            action: 'es_after_call_convert',
            nonce: ES_ADMIN.nonce,
            user_id: userId,
            outcome: outcome,
            group_id: groupId || 0,
            comments: comments,
            'package_ids[]': packageIds
        };

        $.ajax({
            url: ES_ADMIN.ajax_url,
            method: 'POST',
            data: data,
            traditional: true,
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (res.data.message || 'Failed'));
                    $btn.prop('disabled', false).text('Submit & Convert');
                }
            },
            error: function () {
                alert('Network error.');
                $btn.prop('disabled', false).text('Submit & Convert');
            }
        });
    }

    /* ============ HELPERS — GROUPS ============ */

    function resetGroupModal() {
        $('#es-group-id').val('');
        $('#es-group-name').val('');
        $('#es-group-package').val('');
        $('#es-group-duration').val('');
        $('#es-group-total').val('');
        $('#es-group-color').val('#6366f1');
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
                    $('#es-group-package').val(g.package_id || '');
                    $('#es-group-duration').val(g.duration || '');
                    $('#es-group-total').val(g.total_sessions || '');
                    $('#es-group-color').val(g.color || '#6366f1');
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
                package_id: $('#es-group-package').val(),
                duration: $('#es-group-duration').val(),
                total_sessions: $('#es-group-total').val(),
                color: $('#es-group-color').val(),
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
