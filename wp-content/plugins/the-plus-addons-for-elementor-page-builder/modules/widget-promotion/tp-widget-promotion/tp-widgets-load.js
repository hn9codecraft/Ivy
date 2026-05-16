(function ($) {
    const { __, sprintf } = wp.i18n;

    elementor.hooks.addFilter("panel/elements/regionViews", function (panel) {

        function tpae_applyTpPromotionClass() {
            $('.elementor-element--promotion', parent.document).each(function () {
                const $promo = $(this);
                const hasThePlusIcon = $promo.find('i[class^="theplus-"]').length > 0;

                if (hasThePlusIcon) {
                    $promo.addClass('tp-widgets-promotion');
                }
            });
        }

        const observerTarget = parent.document.querySelector('#elementor-panel');

        if (observerTarget) {
            const observer = new MutationObserver(() => {
                tpae_applyTpPromotionClass();
            });

            observer.observe(observerTarget, {
                childList: true,
                subtree: true
            });
        }

        const widgetCollection = panel.elements.options.collection;
        const categoryCollection = panel.categories.options.collection;
        const myProWidgets = [];

        tpPanelSettings.tp_pro_widgets.forEach(function (widget) {

            widgetCollection.add({
                name: widget.key,
                title: widget.title,
                icon: widget.icon,
                categories: ["tp-pro-widgets"],
                editable: false
            });
        });

        widgetCollection.each(function (widget) {
            const widgetCategories = widget.get("categories");
            if (widgetCategories[0] === "tp-pro-widgets") {
                myProWidgets.push(widget);
            }
        });

        const generalIndex = categoryCollection.findIndex({ name: "general" });

        categoryCollection.add({
            name: "tp-pro-widgets",
            title: __('The Plus Addons Pro', 'tpebl'),
            defaultActive: true,
            sort: true,
            hideIfEmpty: true,
            items: myProWidgets,
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
                '#elementor-panel-category-tp-pro-widgets .elementor-panel-category-title .elementor-panel-heading-promotion a';
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
    $(parent.document).on('mousedown', '#elementor-panel-category-tp-pro-widgets .elementor-element--promotion , .elementor-element-wrapper.elementor-element--promotion.tp-widgets-promotion', function (e) {
        const dialogSelector = '#elementor-element--promotion__dialog';
        $(dialogSelector, parent.document).remove();
    });
    $(parent.document).on('click', '#elementor-panel-category-tp-pro-widgets .elementor-element--promotion, .elementor-element-wrapper.elementor-element--promotion.tp-widgets-promotion', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const widget = $(this);
        const offset = widget.offset();
        const widgetTitle = widget.find('.title-wrapper .title').text() || __('Pro Widget', 'tpebl');

        const matchedWidget = tpPanelSettings.tp_pro_widgets.find(item => item.title === widgetTitle);
        const demoUrl = matchedWidget?.demo_url || 'https://theplusaddons.com/';

        $('#tp-custom-dialog', parent.document).remove();

        const dialog = `
        <div id="tp-custom-dialog" class="tp-elem-dialog" style="position: absolute;top: ${offset.top - 50}px;left: ${offset.left + widget.outerWidth() + 10}px; height: 190px;display: flex;flex-direction: column;align-items: flex-start;">
           <div class="tp-side-icon">
           
           </div>
           
            <div class="tp-dialog-header">
               <div class="tp-dialog-header-middel">
                <div class="tp-dialog-top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"><path fill="#fff" fill-opacity=".7" d="M7.135 1.167c.83 0 1.626.296 2.213.82.587.524.918 1.234.919 1.976v1.37c.371 0 .727.133.99.367.262.234.41.552.41.884v3.875c-.002.63-.282 1.232-.78 1.677a2.836 2.836 0 0 1-1.88.698H4.993a2.836 2.836 0 0 1-1.88-.698c-.499-.445-.78-1.048-.78-1.677V6.584c0-.332.147-.65.41-.884a1.49 1.49 0 0 1 .99-.366V3.963c.001-.742.33-1.452.918-1.976.587-.524 1.383-.82 2.213-.82h.27ZM7 7.432a.876.876 0 0 0-.808 1.21.875.875 0 0 0 .37.423V10.5a.438.438 0 1 0 .876 0V9.065A.876.876 0 0 0 7 7.432ZM6.865 2c-.583 0-1.142.207-1.554.575a1.863 1.863 0 0 0-.645 1.388v1.37h4.667v-1.37c0-.52-.231-1.02-.643-1.388A2.34 2.34 0 0 0 7.135 2h-.27Z"/></svg>
                    <div class="tp-dialog-title">${widgetTitle}</div>
                    <div class="tp-pro-tag"><span>${__('PRO', 'tpebl')}</span></div>
                </div>
                <i class="eicon-close tp-dialog-close-icon" id="tp-dialog-close"></i>
               </div>
            </div>
            <div class="tp-dialog-content">
                <p>${sprintf(__('Unlock this widget by upgrading to %1$s. Use Code %2$s to get FLAT 20%% OFF now.', 'tpebl'), 'The Plus Addons for Elementor Pro', 'FIRST20')}</p>
            </div>
            <div class="tp-dialog-footer">
                <a href="https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links" class="tp-widget-dwl" target="_blank">${__('Get Pro', 'tpebl')}</a>
                <a href="${demoUrl}?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links" class="tp-widget-demo" target="_blank">${__('Live Demos', 'tpebl')}</a>
            </div>
        </div>
    `;
        $('body', parent.document).append(dialog);
    });
    $(parent.document).on('click', '#tp-dialog-close', function () {
        $('#tp-custom-dialog', parent.document).remove();
    });
    $(parent.document).on('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            const $dialog = $('#tp-custom-dialog', parent.document);
            if ($dialog.length) {
                $dialog.remove();
            }
        }
    });
    $(parent.document).on('mousedown', function (e) {
        var $dialog = $('#tp-custom-dialog', parent.document);
        if ($dialog.length && !$dialog.is(e.target) && $dialog.has(e.target).length === 0) {
            $dialog.remove();
        }
    });
    const iframe = parent.document.querySelector('#elementor-preview-iframe');

    if (iframe) {
        iframe.addEventListener('load', function () {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            $(iframeDoc).on('mousedown', function () {
                const $dialog = $('#tp-custom-dialog', parent.document);
                if ($dialog.length) {
                    $dialog.remove();
                }
            });
        });
    }
})(jQuery);