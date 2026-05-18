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
    if ($('.es-pp-shell').length || $('.es-public-packages').length) {

        var $root = $('.es-pp-shell').length ? $('.es-pp-shell') : $('.es-public-packages');
        var currentCycle = $root.data('default-cycle') || 'monthly';

        var stripe = null;
        var cardElement = null;
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

        // Mount the Card Element once the panel is in the DOM
        function ensureCardElement() {
            if (!stripeReady || cardElement) return;
            elements = stripe.elements();
            cardElement = elements.create('card', {
                hidePostalCode: false,
                style: {
                    base: {
                        color: '#1e293b',
                        fontFamily: '-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif',
                        fontSize: '15px',
                        '::placeholder': { color: '#94a3b8' }
                    },
                    invalid: { color: '#dc2626' }
                }
            });
            cardElement.mount('#es-pp-card-element');
            cardElement.on('change', function (event) {
                $('#es-pp-card-errors').text(event.error ? event.error.message : '');
            });
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

            // If panel is open, refresh the summary + total to match new cycle
            if ($panel.hasClass('is-open')) {
                updatePaymentSummary();
            }
        });

        function updatePaymentSummary() {
            var pkgId = $('#es-pp-package-id').val();
            if (!pkgId) return;
            var $card = $('.es-pp-card[data-package-id="' + pkgId + '"]');
            if (!$card.length) return;

            var label = currentCycle === 'yearly'
                ? $card.data('yearly-label')
                : $card.data('monthly-label');

            var name = $card.data('package-name') || '';
            $('#es-pp-summary-plan').text(name + ' (' + (currentCycle === 'yearly' ? 'Yearly / Semester' : 'Monthly') + ')');
            $('#es-pp-summary-amount').text(label);
            $('#es-pp-cycle').val(currentCycle);
            $('.es-pp-pay-submit-text').text('Pay ' + label);
        }

        function openPanel(pkgId) {
            $('#es-pp-package-id').val(pkgId);
            updatePaymentSummary();
            ensureCardElement();
            $panel.addClass('is-open').attr('aria-hidden', 'false');
            $overlay.addClass('is-open').attr('aria-hidden', 'false');
            // Reset any prior error/disabled state
            $('#es-pp-pay-submit').prop('disabled', false);
            $('#es-pp-card-errors').text('');
        }

        function closePanel() {
            $panel.removeClass('is-open').attr('aria-hidden', 'true');
            $overlay.removeClass('is-open').attr('aria-hidden', 'true');
        }

        // Select This Plan → redirect to hosted Stripe Checkout
        // (Inline Elements panel kept available as a legacy fallback when needed.)
        $(document).on('click', '.es-pp-select-btn, .es-pkg-pay-btn, .es-pkg-select-btn', function () {
            var $btn = $(this);
            var pkgId = $btn.data('package-id');
            var userId = $btn.data('user-id');
            var token = $btn.data('token');

            if (!pkgId || !userId || !token) {
                alert('Missing selection details. Please refresh the page.');
                return;
            }

            // Preferred flow: hosted Stripe Checkout (you stay off-site for PCI safety)
            if (window.ES_FE && ES_FE.stripe && ES_FE.stripe.enabled) {
                redirectToHostedCheckout(pkgId, userId, token, $btn);
                return;
            }

            // No Stripe — simple selection (mark as chosen)
            if (!confirm('Confirm your selection?')) return;
            selectPackage(pkgId, userId, token, $btn);
        });

        $(document).on('click', '#es-pp-pay-close, #es-pp-overlay', function () {
            closePanel();
        });

        // Submit payment form
        $(document).on('submit', '#es-pp-pay-form', function (e) {
            e.preventDefault();
            submitInlinePayment();
        });

        function submitInlinePayment() {
            if (!stripeReady || !cardElement) {
                alert('Payment system is not ready. Please refresh and try again.');
                return;
            }

            var pkgId = $('#es-pp-package-id').val();
            var userId = $('#es-pp-user-id').val();
            var token = $('#es-pp-token').val();
            var name = $('#es-pp-name').val().trim();
            var email = $('#es-pp-email').val().trim();
            var cycle = $('#es-pp-cycle').val() || currentCycle;

            if (!name) { $('#es-pp-name').focus(); alert('Please enter your name.'); return; }
            if (!email || email.indexOf('@') === -1) { $('#es-pp-email').focus(); alert('Please enter a valid email.'); return; }

            var $submit = $('#es-pp-pay-submit');
            var origText = $('.es-pp-pay-submit-text').text();
            $submit.prop('disabled', true);
            $('.es-pp-pay-submit-text').text('Processing...');
            $('#es-pp-card-errors').text('');

            // 1) Create PaymentIntent on the server
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
                    email: email
                },
                success: function (res) {
                    if (!res.success) {
                        $('#es-pp-card-errors').text((res.data && res.data.message) || 'Could not start payment.');
                        $submit.prop('disabled', false);
                        $('.es-pp-pay-submit-text').text(origText);
                        return;
                    }
                    var clientSecret = res.data.client_secret;
                    var piId = res.data.payment_intent_id;

                    // 2) Confirm card payment client-side via Stripe Elements
                    stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: { name: name, email: email }
                        }
                    }).then(function (result) {
                        if (result.error) {
                            $('#es-pp-card-errors').text(result.error.message || 'Payment failed.');
                            $submit.prop('disabled', false);
                            $('.es-pp-pay-submit-text').text(origText);
                            return;
                        }
                        if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                            finalizePayment(piId, $submit, origText);
                        } else {
                            $('#es-pp-card-errors').text('Payment status: ' + (result.paymentIntent ? result.paymentIntent.status : 'unknown'));
                            $submit.prop('disabled', false);
                            $('.es-pp-pay-submit-text').text(origText);
                        }
                    });
                },
                error: function () {
                    $('#es-pp-card-errors').text('Network error. Please try again.');
                    $submit.prop('disabled', false);
                    $('.es-pp-pay-submit-text').text(origText);
                }
            });
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
                        $('#es-pp-card-errors').text((res.data && res.data.message) || 'Could not confirm payment.');
                        $submit.prop('disabled', false);
                        $('.es-pp-pay-submit-text').text(origText);
                        return;
                    }
                    // Success — replace the panel content with a thank-you state.
                    var data = res.data || {};
                    var thanksHtml =
                        '<div style="text-align:center;padding:40px 10px">' +
                        '  <div style="font-size:56px;color:#10b981;margin-bottom:14px">✓</div>' +
                        '  <h2 style="color:#fff;font-size:22px;margin:0 0 8px">Payment Successful</h2>' +
                        '  <p style="color:#cbd5e1;font-size:14px;margin:0 0 18px">A receipt has been emailed to you.</p>' +
                        '  <div style="background:rgba(255,255,255,.06);border-radius:8px;padding:14px;color:#fff;font-size:14px;text-align:left">' +
                        '    <div style="color:#94a3b8;font-size:12px;margin-bottom:4px">Plan</div>' +
                        '    <div style="font-weight:600;margin-bottom:10px">' + (data.package_name || '') + '</div>' +
                        '    <div style="color:#94a3b8;font-size:12px;margin-bottom:4px">Amount</div>' +
                        '    <div style="color:#caa657;font-weight:700;font-size:16px">' + (data.amount_label || '') + '</div>' +
                        '  </div>' +
                        '  <button type="button" class="es-pp-pay-submit" style="margin-top:18px" onclick="window.location.reload()">Done</button>' +
                        '</div>';
                    $('#es-pp-pay-form').replaceWith(thanksHtml);
                },
                error: function () {
                    $('#es-pp-card-errors').text('Payment confirmed but we couldn\'t finalize. Contact support.');
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

        /* ─── PUBLIC PRICING PAGE — Buy Now flow (no token required) ───
         * 1) User clicks "Select This Plan" on a card → modal opens to collect
         *    name + email + chosen billing cycle.
         * 2) Submit → server creates a Stripe Checkout Session → redirect.
         * 3) On return, finalize_session() creates a WP user if needed and
         *    sends the receipt email.
         */
        var $buyModal = $('#es-pp-buy-modal');

        function openBuyModal(pkgId, pkgName) {
            if (!$buyModal.length) return;

            var $card = $('.es-pp-card[data-package-id="' + pkgId + '"]');
            var amountLabel = currentCycle === 'yearly'
                ? ($card.data('yearly-label') || '')
                : ($card.data('monthly-label') || '');

            $buyModal.data('package-id', pkgId);
            $('#es-pp-buy-plan-line').text(
                pkgName + ' · ' + amountLabel +
                ' (' + (currentCycle === 'yearly' ? 'Yearly' : 'Monthly') + ')'
            );
            $('#es-pp-buy-error').text('');
            $('#es-pp-buy-submit').prop('disabled', false);
            $('.es-pp-buy-submit-text').text('Continue to secure payment');
            $buyModal.addClass('is-open').attr('aria-hidden', 'false');
            setTimeout(function () { $('#es-pp-buy-name').trigger('focus'); }, 100);
        }

        function closeBuyModal() {
            $buyModal.removeClass('is-open').attr('aria-hidden', 'true');
        }

        $(document).on('click', '.es-pp-buy-btn', function () {
            var $btn = $(this);
            var pkgId = $btn.data('package-id');
            var pkgName = $btn.data('package-name') || '';
            if (!pkgId) return;
            openBuyModal(pkgId, pkgName);
        });

        $(document).on('click', '#es-pp-buy-close, #es-pp-buy-backdrop', closeBuyModal);

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $buyModal.hasClass('is-open')) closeBuyModal();
        });

        $(document).on('submit', '#es-pp-buy-form', function (e) {
            e.preventDefault();

            var pkgId = $buyModal.data('package-id');
            var name  = $('#es-pp-buy-name').val().trim();
            var email = $('#es-pp-buy-email').val().trim();

            if (!pkgId) {
                $('#es-pp-buy-error').text('Please select a package first.');
                return;
            }
            if (!name) {
                $('#es-pp-buy-error').text('Please enter your name.');
                $('#es-pp-buy-name').trigger('focus');
                return;
            }
            if (!email || email.indexOf('@') === -1 || email.indexOf('.') === -1) {
                $('#es-pp-buy-error').text('Please enter a valid email address.');
                $('#es-pp-buy-email').trigger('focus');
                return;
            }

            $('#es-pp-buy-error').text('');
            var $submit = $('#es-pp-buy-submit');
            $submit.prop('disabled', true);
            $('.es-pp-buy-submit-text').text('Redirecting to Stripe…');

            $.ajax({
                url: ES_FE.ajax_url,
                method: 'POST',
                data: {
                    action: 'es_stripe_public_checkout',
                    nonce: ES_FE.nonce,
                    package_id: pkgId,
                    billing_cycle: currentCycle,
                    name: name,
                    email: email
                },
                success: function (res) {
                    if (res.success && res.data && res.data.url) {
                        window.location.href = res.data.url;
                    } else {
                        var msg = (res && res.data && res.data.message) || 'Could not start checkout. Please try again.';
                        $('#es-pp-buy-error').text(msg);
                        $submit.prop('disabled', false);
                        $('.es-pp-buy-submit-text').text('Continue to secure payment');
                    }
                },
                error: function () {
                    $('#es-pp-buy-error').text('Network error. Please check your connection and try again.');
                    $submit.prop('disabled', false);
                    $('.es-pp-buy-submit-text').text('Continue to secure payment');
                }
            });
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
        $('#es-pkg-currency').val('INR');
        $('#es-pkg-price').val('');
        $('#es-pkg-hours').val('');
        $('#es-pkg-tagline').val('');
        $('#es-pkg-description').val('');
        $('#es-pkg-order').val('0');
        // Hidden field — packages are visible to students by default
        $('#es-pkg-active').val('1');
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
                    $('#es-pkg-price').val(p.price);
                    $('#es-pkg-hours').val(p.hours);
                    $('#es-pkg-tagline').val(p.tagline);
                    $('#es-pkg-description').val(p.description);
                    $('#es-pkg-order').val(p.display_order);
                    // Preserve existing active state silently (default 1)
                    $('#es-pkg-active').val(p.is_active == 0 ? '0' : '1');
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
                currency: $('#es-pkg-currency').val(),
                price: $('#es-pkg-price').val(),
                hours: $('#es-pkg-hours').val(),
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
        var $section = $('#es-after-call-section');
        var userId   = $section.data('user-id');
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
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation:spin 1s linear infinite"></span> Processing...');

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
