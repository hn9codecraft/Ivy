
(function ($) {
    const { __ } = wp.i18n;

    const INSTALL_NXT = __("Installing Nexter Extension...", "tpebl");
    const AFTER_INSTALL_NXT = __("Create Header Template", "tpebl");

    const INSTALL_WOO = __("Installing WooCommerce...", "tpebl");
    const AFTER_INSTALL_WOO = __("Open Advanced Settings", "tpebl");

    $(document).ready(function () {
        $(document).on('click', '.tp-install-nexter-ext', function (e) {
            e.preventDefault();
            const $btn = $(this);
            // const loader = $btn.find('.tp-nextbtn-loader');
            // loader.css('display', 'block');

            let btn_txt = $btn.data('tp_btn_txt');

            $btn.contents().filter(function () {
                return this.nodeType === 3;
            }).first().replaceWith(INSTALL_NXT);

            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                type: 'post',
                data: {
                    action: 'tp_install_promotions_plugin',
                    security: theplus_editor_theme_builder.nonce,
                    plugin_type: 'nexter_ext',
                },
                success: function (res) {

                    if (res.success === false) {
                        alert('Only site admins can install plugin. Please ask your admin to complete the installation.')
                        $btn.contents().filter(function () {
                            return this.nodeType === 3;
                        }).first().replaceWith(__("Enable Theme Builder", "tpebl"));
                    }

                    if (res.success === true) {
                        // loader.hide();
                        $btn.contents().filter(function () {
                            return this.nodeType === 3;
                        }).first().replaceWith(btn_txt);
                        $btn.removeClass('tp-install-nexter-ext').addClass('tp-page-create');

                    } 
                    // else {
                    //     loader.hide();
                    // }
                },
                error: function (xhr, status, error) {
                    // loader.hide();
                    $btn.contents().filter(function () {
                        return this.nodeType === 3;
                    }).first().replaceWith(__("Enable Theme Builder", "tpebl"));
                }
            });
        });

        $(document).on('click', '.tp-page-create', function (e) {

            e.preventDefault();
            const $btn = $(this);
            const loader = $btn.find('.tp-nextbtn-loader');

            loader.css('display', 'block');
            loader.css('left', 'unset');

            const tp_post_type = $btn.data('post_type');
            const tp_page_type = $btn.data('page_type');
            const tp_btn_txt = $btn.data('tp_btn_txt');

            $btn.contents().filter(function () {
                return this.nodeType === 3;
            }).remove();

            var page_type = '';
            var page_name = '';

            if ("nxt_builder" === tp_post_type) {
                if ("tp_header" === tp_page_type) {
                    page_type = "header";
                    page_name = "theplus-header"
                } else if ("tp_singular_page" === tp_page_type) {
                    page_type = "singular";
                    page_name = "theplus-singular"
                } else if ("tp_archives" === tp_page_type) {
                    page_type = "archives";
                    page_name = "theplus-archive"
                } else if ("tp_singular_product" === tp_page_type) {
                    page_type = "singular";
                    page_name = "theplus-singular-product"
                }
            } else {
                if ("tp_header" === tp_page_type) {
                    page_type = "header";
                    page_name = "theplus-header"
                } else if ("tp_singular_page" === tp_page_type) {
                    page_type = "single-page";
                    page_name = "theplus-singular"
                } else if ("tp_archives" === tp_page_type) {
                    page_type = "archive";
                    page_name = "theplus-archive"
                } else if ("tp_singular_product" === tp_page_type) {
                    page_type = "product";
                    page_name = "theplus-singular-product"
                }
            }

            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                type: 'post',
                data: {
                    action: 'tpae_create_page',
                    security: theplus_editor_theme_builder.nonce,
                    post_type: tp_post_type,
                    page_type: page_type,
                    page_name: page_name,
                },
                success: function (res) {

                    if (res.success === false) {
                        alert('Only site admins can create templates or page .')
                    }

                    loader.css('display', 'none');
                    $btn.prepend(tp_btn_txt);

                    if (res.success) {

                        var editUrl = res.data.edit_url.replace(/&amp;/g, '&');
                        // window.location.href = editUrl;
                        window.open(editUrl, '_blank');
                    } else {

                    }
                },
                error: function (xhr, status, error) {

                }
            });

        });

        $(document).on('click', '.tp-install-woo', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const tp_btn_type = $btn.data('tp_install');
            const tp_woo_redirect = $btn.data('tp_redirect_link');

            if (tp_btn_type) {

                // const loader = $btn.find('.tp-nextbtn-loader');
                // loader.css('display', 'block');

                $btn.contents().filter(function () {
                    return this.nodeType === 3;
                }).first().replaceWith(INSTALL_WOO);

                $.ajax({
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'post',
                    data: {
                        action: 'tp_install_promotions_plugin',
                        security: theplus_editor_theme_builder.nonce,
                        plugin_type: 'tp_woo',
                    },
                    success: function (res) {

                        if (res.success === false) {
                            alert('Only site admins can install plugin. Please ask your admin to complete the installation.')
                            $btn.contents().filter(function () {
                                return this.nodeType === 3;
                            }).first().replaceWith(__("Install WooCommerce", "tpebl"));
                        }

                        if (res.success === true) {
                            // loader.hide();
                            $btn.attr('data-tp_install', 'false');
                            $btn.data('tp_install', false);
                            $btn.contents().filter(function () {
                                return this.nodeType === 3;
                            }).first().replaceWith(AFTER_INSTALL_WOO);
                        } 
                        // else {
                        //     // loader.hide();
                        // }
                    },
                    error: function (xhr, status, error) {
                        // loader.hide();
                    }
                });
            } else {
                window.open(tp_woo_redirect, '_blank');
            }
        })

    });
})(window.jQuery);