(function ($) {
    const { __ } = wp.i18n;

    elementor.hooks.addFilter("panel/elements/regionViews", function (panel) {

        function tpae_apply_widget_Class() {
            $('.elementor-element--promotion', parent.document).each(function () {
                const $promo = $(this);
                const hasThePlusIcon = $promo.find('i[class^="tpae-editor-logo theplus-"]').length > 0;

                if (hasThePlusIcon) {
                    $promo.addClass('tp-inactive-widgets-list');
                }
            });
        }

        const observerTarget = parent.document.querySelector('#elementor-panel');

        if (observerTarget) {
            const observer = new MutationObserver(() => {
                tpae_apply_widget_Class();
            });

            observer.observe(observerTarget, {
                childList: true,
                subtree: true
            });
        }

        const widgetCollection = panel.elements.options.collection;
        const categoryCollection = panel.categories.options.collection;
        const myAllWidgets = [];

        tp_widgets_list.tp_inactive_widgets_list.forEach(function (widget) {

            widgetCollection.add({
                name: widget.key,
                title: widget.title,
                icon: widget.icon,
                categories: ["tp-inactive-widgets"],
                editable: false
            });
        });

        widgetCollection.each(function (widget) {
            const widgetCategories = widget.get("categories");
            if (widgetCategories[0] === "tp-inactive-widgets") {
                myAllWidgets.push(widget);
            }
        });

        const generalIndex = categoryCollection.findIndex({ name: "general" });

        categoryCollection.add({
            name: "tp-inactive-widgets",
            title: tp_widgets_list.tpae_category,
            defaultActive: true,
            sort: true,
            hideIfEmpty: true,
            items: myAllWidgets,
            promotion: true
        }, {
            at: generalIndex + 1
        });

        return panel;
    });
    jQuery(window).on('elementor:init', function () {
        elementor.on('preview:loaded', function () {
            const parentDoc = parent.document;
            const targetSelector =
                '#elementor-panel-category-tp-inactive-widgets .elementor-panel-category-title .elementor-panel-heading-promotion a';
            const promoUrl =
                'https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links';

            const setPromoUrl = () => {
                const link = parentDoc.querySelector(targetSelector);
                if (link && !link.getAttribute('href')) {
                    link.setAttribute('href', promoUrl);
                    link.setAttribute('target', '_blank');
                    return true;
                }
                return !!link;
            };

            const checkExist = setInterval(() => {
                if (setPromoUrl()) {
                    clearInterval(checkExist);
                }
            }, 7500);

            const panel = parentDoc.querySelector('#elementor-panel-categories');
            if (panel) {
                const observer = new MutationObserver(() => {
                    setPromoUrl();
                });
                observer.observe(panel, { childList: true, subtree: true });
            }
        });
    });
    $(parent.document).on('mousedown', '#elementor-panel-category-tp-inactive-widgets .elementor-element--promotion , .elementor-element-wrapper.elementor-element--promotion.tp-inactive-widgets-list', function (e) {
        const dialogSelector = '#elementor-element--promotion__dialog';
        $(dialogSelector, parent.document).remove();
    });
    // $(parent.document).on('click', '#elementor-panel-category-tp-inactive-widgets .elementor-element--promotion', function (e) {
    $(parent.document).on('click', '#elementor-panel-category-tp-inactive-widgets .elementor-element--promotion, .elementor-element-wrapper.elementor-element--promotion.tp-inactive-widgets-list', function (e) {

        e.preventDefault();
        e.stopPropagation();

        const widget = $(this);
        const offset = widget.offset();
        const widgetTitle = widget.find('.title-wrapper .title').text() || __('Pro Widget', 'tpebl');

        const matchedWidget = tp_widgets_list.tp_inactive_widgets_list.find(item => item.title === widgetTitle);
        const demoUrl = matchedWidget?.demo_url || '#';
        const widget_id = matchedWidget?.name || '';

        $('#tp-widgets-custom-dialog', parent.document).remove();

        let demoLinkHtml = '';
         var tp_hight = '112px';
         var tp_gap = '25px';
         var tp_top = '4';
        if (!tp_widgets_list.is_help_enabled) {
            demoLinkHtml = `<a href="${demoUrl}?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links" class="tp-iaw-widget-demo" target="_blank">${__('View Demos', 'tpebl')}</a>`;
            tp_hight = '163px';
            tp_gap = '0px';
            tp_top = '30';
        }

        const dialog = `
        <div id="tp-widgets-custom-dialog" class="tp-iaw-elem-dialog" style="position: absolute;top: ${offset.top - tp_top}px;left: ${offset.left + widget.outerWidth() + 10}px; height: ${tp_hight}; gap: ${tp_gap}; display: flex;flex-direction: column;align-items: flex-start;">
           <div class="tp-iaw-side-icon">
           
           </div>
           
            <div class="tp-iaw-dialog-header">
               <div class="tp-iaw-dialog-header-middel">
                <div class="tp-iaw-dialog-top">
                    <div class="tp-iaw-dialog-title">${widgetTitle}</div>
                </div>
                <i class="eicon-close tp-iaw-dialog-close-icon" id="tp-dialog-close"></i>
               </div>
            </div>
            ${!tp_widgets_list.is_help_enabled ? `<div class="tp-iaw-dialog-content">
                <p>${__('This widget is turned off in The Plus settings. Click Activate to turn it on.', 'tpebl')}</p>
            </div>` : ''}
            <div class="tp-iaw-dialog-footer">
                <button class="tp-iaw-widget-dwl" data-widget-id="${widget_id}" data-widget-name="${widgetTitle}">
                <span class="tp-iaw-btn-text">${__('Activate Now', 'tpebl')}</span>
                <span class="tp-iaw-loader-spinner" style="display:none;"></span>
                </button>
                ${demoLinkHtml}
            </div>
        </div>
                       `;
        $('body', parent.document).append(dialog);
    });
    $(parent.document).on('click', '#tp-dialog-close', function () {
        $('#tp-widgets-custom-dialog', parent.document).remove();
    });
    $(parent.document).on('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            const $dialog = $('#tp-widgets-custom-dialog', parent.document);
            if ($dialog.length) {
                $dialog.remove();
            }
        }
    });
    $(parent.document).on('mousedown', function (e) {
        var $dialog = $('#tp-widgets-custom-dialog', parent.document);
        if ($dialog.length && !$dialog.is(e.target) && $dialog.has(e.target).length === 0) {
            $dialog.remove();
        }
    });
    const iframe = parent.document.querySelector('#elementor-preview-iframe');
    if (iframe) {
        iframe.addEventListener('load', function () {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            $(iframeDoc).on('mousedown', function () {
                const $dialog = $('#tp-widgets-custom-dialog', parent.document);
                if ($dialog.length) {
                    $dialog.remove();
                }
            });
        });
    }

    $(parent.document).on('click', '.tp-iaw-widget-dwl', function (e) {
        e.preventDefault();

        var $button = $(this);
        var $text = $button.find('.tp-iaw-btn-text');
        var $loader = $button.find('.tp-iaw-loader-spinner');
        var widgetId = $button.attr('data-widget-id');
        var widgetName = $button.attr('data-widget-name');

        $button.prop('disabled', true).addClass('tp-iaw-loading');
        $loader.show();
        $text.hide();

        $.ajax({
            url: tp_widgets_list.ajax_url,
            type: 'post',
            data: {
                action: 'tpae_handle_enable_widget',
                widget_id: widgetId,
                security: tp_widgets_list.nonce
            },
            success: function (response) {

                if (response.success) {
                    $loader.hide();
                    $button.removeClass('tp-iaw-loading');
                    $text.text(__('Activated', 'tpebl')).show();

                    elementor.saver.update.apply().then(function () {
                        var currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('tp_widget_search', 'Tp ' + widgetName);
                        setTimeout(function () {
                            window.location.href = currentUrl.toString();
                        }, 800);
                    });

                } else {
                    $loader.hide();
                    $button.removeClass('tp-iaw-loading');
                    $text.text(__('Active Widget', 'tpebl')).show();
                }
            },
            error: function () {
                $loader.hide();
                $button.removeClass('tp-iaw-loading');
                $text.text(__('Active Widget', 'tpebl')).show();
            }
        });
    });

    jQuery(window).on('load', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('tp_widget_search');

        if (searchTerm) {
            let searchAttempt = 0;
            const fillSearch = setInterval(function () {
                const $searchInput = jQuery('#elementor-panel-elements-search-input');
                searchAttempt++;

                if ($searchInput.length > 0) {
                    clearInterval(fillSearch);

                    try {
                        $searchInput.focus();
                        $searchInput.val(searchTerm);

                        var event = new Event('input', { bubbles: true });
                        $searchInput[0].dispatchEvent(event);

                        var keyEvent = new KeyboardEvent('keyup', {
                            bubbles: true,
                            cancelable: true,
                            key: 'Enter',
                            code: 'Enter'
                        });
                        $searchInput[0].dispatchEvent(keyEvent);
                    } catch (e) {
                         console.error('ThePlus Addons: Error triggering search', e);
                    } finally {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('tp_widget_search');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                }

                if (searchAttempt > 20) clearInterval(fillSearch);
            }, 800);
        }
    });

})(jQuery);