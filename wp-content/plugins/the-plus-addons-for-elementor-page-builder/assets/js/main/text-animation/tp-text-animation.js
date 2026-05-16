function TP_common_txt_gsap($wrapper, tp_gsap_dataset, tp_text_finder, tp_widget_type) {

    $wrapper.each(function () {

        var $ = jQuery

        var $this = $(this);
        var config = $this.data(tp_gsap_dataset);



        if (!config) return;

        if (tp_widget_type === "tp_heading_title") {
            if (config.tp_enable_ani !== "yes") return;
        }

        if (tp_widget_type === "tp_text_block") {
            if (config.tp_enable_ani !== "yes") return;
        }

        if (tp_widget_type === "tp_sub_heading_title") {
            if (config.tp_enable_ani_sub_txt !== "yes") return;
        }

        function TP_getTextEls($this, tp_text_finder, tp_widget_type) {

            let $targets = $this.find(`.${tp_text_finder}`);

            if ($targets.length === 0) {
                $targets = $this;
            }

            // let selectors = tp_widget_type === "tp_heading_title" || tp_widget_type === "tp_sub_heading_title"
            //         ? "h1, h2, h3, h4, h5, h6, span, p"
            //         : "p, span, div";
            let selectors = "p, span, div";

            let $found = $targets.find(selectors);

            if ($found.length === 0) {
                $found = $this.find(selectors);
            }

            return $found;
        }

        var $textEls;

        if (tp_widget_type === "tp_heading_title" || tp_widget_type === "tp_sub_heading_title") {
            $textEls = $this.find(`.${tp_text_finder}`).find("h1, h2, h3, h4, h5, h6, span , p");
        } else {
            $textEls = TP_getTextEls($this, tp_text_finder, tp_widget_type);
            // $textEls = $this.find(`.${tp_text_finder}`).find("p, span, div");
        }

        if (tp_widget_type === "tp_sub_heading_title") {

            var effect = config.tp_effect_sub_txt || "normal";
            var ease = config.tp_ease_sub_txt || "power3.out";
            var duration = parseFloat(config.tp_duration_sub_txt) || 1.2;
            var delay = parseFloat(config.tp_delay_sub_txt) || 0;
            var stagger = parseFloat(config.tp_stagger_sub_txt) || 0.03;
            var tp_scrub = config.tp_scrub_sub_txt || '';

            var splitType = config.tp_split_type_sub_txt || "chars";

            var x = parseFloat(config.transform_x_sub_txt) || 0;
            var y = parseFloat(config.transform_y_sub_txt) || 0;
            var skewX = parseFloat(config.transform_skewx_sub_txt) || 0;
            var skewY = parseFloat(config.transform_skewy_sub_txt) || 0;
            var scale = parseFloat(config.transform_scale_sub_txt) || 1;
            var rotation = parseFloat(config.transform_rotation_sub_txt) || 0;
            var transformOrigin = config.transform_origin_sub_txt || "50% 50%";

            var repeat = config.tp_repeat_sub_txt || 'no';
            // var repeat_yoyo = config.tp_repeat_yoyo_sub_txt || 'no';
            var trigger = config.tp_trigger_sub_txt || "onload";

        } else {
            var effect = config.tp_effect || "normal";
            var ease = config.tp_ease || "power3.out";
            var duration = parseFloat(config.tp_duration) || 1.2;
            var delay = parseFloat(config.tp_delay) || 0;
            var stagger = parseFloat(config.tp_stagger) || 0.03;
            var tp_scrub = config.tp_scrub || '';

            var splitType = config.tp_split_type || "chars";

            var x = parseFloat(config.transform_x) || 0;
            var y = parseFloat(config.transform_y) || 0;
            var skewX = parseFloat(config.transform_skewx) || 0;
            var skewY = parseFloat(config.transform_skewy) || 0;
            var scale = parseFloat(config.transform_scale) || 1;
            var rotation = parseFloat(config.transform_rotation) || 0;
            var transformOrigin = config.transform_origin || "50% 50%";

            var repeat = config.tp_repeat || 'no';
            var trigger = config.tp_trigger || "onload";
            var tp_swap_txt_color = config.tp_swap_txt_color || "";
            var tp_swap_typography = config.tp_swap_typography || {};
        }


        /* Save original text */
        $textEls.each(function () {
            if (!$(this).data("tp-original-text")) {
                $(this).data("tp-original-text", $(this).text());
            }
        });


        const getSplit = (el) => {

            if (el.splitInstance) {
                return el.splitInstance;
            }
            el.style.whiteSpace = "pre-wrap";

            el.splitInstance = new SplitText(el, { type: splitType });

            return el.splitInstance;
        };

        const resetElement = (el) => {

            if (el.splitInstance) {
                el.splitInstance.revert();
                el.splitInstance = null;
            }

            gsap.set(el, {
                opacity: 1,
                x: 0,
                y: 0,
                skewX: 0,
                skewY: 0,
                scale: 1,
                rotation: 0,
                clearProps: "all"
            });
        };

        const runEffect = (el, stConfig = null) => {
            const $el = $(el);
            const finalText = $el.data("tp-original-text");

            if (effect === "typing" || effect === "scramble") {
                resetElement(el);
            }

            switch (effect) {

                case "normal": {
                    const split = getSplit(el);
                    gsap.from(split[splitType], {
                        x, y, skewX, skewY, scale, rotation,
                        transformOrigin,
                        opacity: 0,
                        ease,
                        duration,
                        stagger,
                        delay,
                        repeat: repeat === 'yes' ? -1 : 0,
                        yoyo: repeat === 'yes' ? true : false,
                        scrollTrigger: stConfig,
                    });
                    break;
                }
                case "explode": {
                    const split = getSplit(el);
                    gsap.from(split[splitType], {
                        x: "random(-150,150)",
                        y: "random(-150,150)",
                        scale: 0,
                        rotation: "random(-180,180)",
                        opacity: 0,
                        ease,
                        duration,
                        stagger,
                        delay,
                        repeat: repeat === 'yes' ? -1 : 0,
                        yoyo: repeat === 'yes' ? true : false,
                        scrollTrigger: stConfig,
                    });
                    break;
                }
                case "scramble":
                    gsap.to(el, {
                        duration,
                        delay,
                        opacity: 1,
                        scrambleText: {
                            text: finalText,
                            chars: "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()",
                            speed: 0.1,
                            revealDelay: 0.15
                        },
                        ease,
                        repeat: repeat === 'yes' ? -1 : 0,
                        scrollTrigger: stConfig,
                        // yoyo: repeat_yoyo === 'yes',
                    });
                    break;
                case "typing": {

                    // 1. Text holder detect karo (IMPORTANT)
                    let container =
                        el.querySelector(".tp-text-content") ||
                        el.querySelector(".tp-heading-inner") ||
                        el.querySelector(".tp-sub-heading-inner") ||
                        el;

                    // 2. Original text lo
                    const text = $(el).data("tp-original-text");

                    // 3. Existing HTML clear karo
                    container.innerHTML = "";

                    // 4. Typing run
                    gsap.to(container, {
                        duration,
                        delay,
                        text: text,
                        ease: "none",
                        repeat: repeat === 'yes' ? -1 : 0,
                        scrollTrigger: stConfig,
                    });

                    break;
                }
                case "tp_text_swap": {
                    var split = getSplit(el);

                    var animConfig = {
                        color: tp_swap_txt_color,
                        duration: duration,
                        stagger: stagger,
                        ease: ease,
                        repeat: repeat === 'yes' ? -1 : 0,
                        yoyo: repeat === 'yes' ? true : false,
                        delay: delay,
                    };

                    if (tp_swap_typography) {
                        Object.assign(animConfig, tp_swap_typography);
                    }

                    animConfig.scrollTrigger = stConfig;
                    gsap.to(split.chars, animConfig);
                    break;
                }
            }
        };

        if (trigger === "onload") {
            gsap.delayedCall(0.05, () => {
                $textEls.each(function () {
                    runEffect(this);
                });
            });
        }

        else if (trigger === "onscroll") {

            $textEls.each(function () {

                const el = this;

                const splitInstance = getSplit(el);

                if ((effect === 'normal' || effect === 'explode' || effect === 'tp_text_swap') && tp_scrub === 'yes') {

                    if (effect === 'tp_text_swap') {
                        var scrollAnimConfig = {
                            color: tp_swap_txt_color,
                            ease: ease,
                            stagger: stagger,
                            scrollTrigger: {
                                trigger: $this[0],
                                start: "top 70%",
                                end: "bottom 30%",
                                scrub: true,
                            }
                        };

                        if (tp_swap_typography) {
                            Object.assign(scrollAnimConfig, tp_swap_typography);
                        }

                        gsap.to(splitInstance[splitType], scrollAnimConfig);
                    } else {
                        gsap.from(splitInstance[splitType], {
                            x: effect === 'normal' ? x : "random(-200,200)",
                            y: effect === 'normal' ? y : "random(-200,200)",
                            rotation: effect === 'normal' ? rotation : "random(-360,360)",
                            scale: effect === 'normal' ? scale : 0,
                            skewX: effect === 'normal' ? skewX : 0,
                            skewY: effect === 'normal' ? skewY : 0,
                            transformOrigin,
                            opacity: 0,
                            ease,
                            stagger,
                            scrollTrigger: {
                                trigger: $this[0],
                                start: "top 70%",
                                end: "bottom 30%",
                                scrub: true,
                            }
                        });
                    }
                } else {

                    runEffect(el, {
                        trigger: $this[0],
                        start: "top 70%",
                        once: true,
                    });

                }

            });
        }

        else if (trigger === "onhover") {

            $this.on("mouseenter", () => {
                $textEls.each(function () {
                    runEffect(this);
                });
            });

            $this.on("mouseleave", () => {
                $textEls.each(function () {
                    resetElement(this);
                });
            });
        }

    });


}