jQuery(document).on("click", ".tpae-ele-btn", function (e) {
    e.preventDefault();

    const { __ } = wp.i18n;

    let btn = jQuery(this);
    let slug = btn.data("slug");
    let name = btn.data("name");

    let currentText = btn.text().trim();

    if (currentText === __("Install Now", "tpebl")) {
        btn.text(__("Installing...", "tpebl"));
    } else if (currentText === __("Activate Now", "tpebl")) {
        btn.text(__("Activating...", "tpebl"));
    }

    jQuery.ajax({
        url: tpae_admins_js.ajax_url,
        type: "POST",
        data: {
            action: "tpae_elementor_ajax_call",
            nonce: tpae_admins_js.tpae_nonce,
            slug: slug,
            name: name
        },
        success: function (response) {

            if (response.success) {
                location.reload();
            } else {
                console.log(response.data?.message || __("Failed!", "tpebl"));
                btn.text(currentText);
            }
        },
        error: function () {
            console.log(__("Something went wrong!", "tpebl"));
            btn.text(currentText);
        }
    });
});
