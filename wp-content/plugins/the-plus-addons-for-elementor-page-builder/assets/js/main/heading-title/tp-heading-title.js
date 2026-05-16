(function ($) {
    "use strict";

    var Tp_Heading_tltle = function ($scope, $) {
        var $wrapper = $scope.find(".heading.heading_style");
        if (!$wrapper.length) return;

        gsap.registerPlugin(ScrollTrigger, SplitText, ScrambleTextPlugin, TextPlugin);

        var tp_gsap_dataset = 'tp-gsap-heading-text';
        var tp_text_finder = 'head-title';
        var tp_widget_type = 'tp_heading_title'
        TP_common_txt_gsap( $wrapper ,  tp_gsap_dataset , tp_text_finder , tp_widget_type);

        var tp_sub_gsap_dataset = 'tp-gsap-sub-heading-text';
        var tp_sub_text_finder = 'sub-heading';
        var tp_sub_widget_type = 'tp_sub_heading_title'
        TP_common_txt_gsap( $wrapper ,  tp_sub_gsap_dataset , tp_sub_text_finder , tp_sub_widget_type);

    };

    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction(
            "frontend/element_ready/tp-heading-title.default",
            Tp_Heading_tltle
        );
    });

})(jQuery);