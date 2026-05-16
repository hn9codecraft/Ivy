function Tp_image_animetions($wrapper, tp_dataset, tp_image_ani_type) {

    var $ = jQuery

    if ('tp_elementor_editor' === tp_image_ani_type) {
        $wrapper = $($wrapper);
    }

    $wrapper.each(function () {

        const $this = $(this);

        var tp_dataset_val = '';

        if ('tp_elementor_editor' === tp_image_ani_type) {
            tp_dataset_val = tp_dataset
        } else {
            tp_dataset_val = $this.data(tp_dataset);
        }

        const config = tp_dataset_val;


        if (!config || config.tp_enable_image_ani !== 'yes') return;

        const $image = $this.find('img');


        if (!$image.length) return;

        gsap.registerPlugin(ScrollTrigger);

        const trigger = config.tp_trigger || 'onload';
        const x = parseFloat(config.tp_x) || 0;
        const y = parseFloat(config.tp_y) || 0;
        const skewX = parseFloat(config.tp_skewx) || 0;
        const skewY = parseFloat(config.tp_skewy) || 0;
        const scale = parseFloat(config.tp_scale) || 1;
        const rotation = parseFloat(config.tp_rotation) || 0;
        const opacity = parseFloat(config.tp_opacity) || 1;
        const transformOrigin = config.tp_origin || '50% 50%';
        const duration = parseFloat(config.tp_duration) || 1.2;
        const delay = parseFloat(config.tp_delay) || 0.3;
        const ease = config.tp_ease || 'power3.out';
        const clipPathType = config.tp_clip_path_type || 'none';
        const tp_scrub = config.tp_scrub === 'yes';
        const tp_custom_clippath = config.tp_custom_clip_path_value;
        // const tp_img_stagger = config.tp_img_stagger;



        const ClipPresets = {

            circle_center: {
                from: "inset(0 0 0 0)",
                to: "circle(50% at 50% 50%)"
            },
            circle_left: {
                from: "inset(0 0 0 0)",
                to: "circle(50% at 0% 50%)"
            },
            circle_right: {
                from: "inset(0 0 0 0)",
                to: "circle(50% at 100% 50%)"
            },
            ellipse_center: {
                from: "inset(0 0 0 0)",
                to: "ellipse(60% 40% at 50% 50%)"
            },
            ellipse_horizontal: {
                from: "inset(0 0 0 0)",
                to: "ellipse(100% 40% at 50% 50%)"
            },
            inset_top: {
                from: "inset(0 0 0 0)",
                to: "inset(100% 0 0 0)"
            },
            inset_bottom: {
                from: "inset(0 0 0 0)",
                to: "inset(0 0 100% 0)"
            },
            inset_left: {
                from: "inset(0 0 0 0)",
                to: "inset(0 100% 0 0)"
            },
            inset_right: {
                from: "inset(0 0 0 0)",
                to: "inset(0 0 0 100%)"
            },
            poly_triangle: {
                from: "inset(0 0 0 0)",
                to: "polygon(50% 0%, 0% 100%, 100% 100%)"
            },
            poly_diamond: {
                from: "inset(0 0 0 0)",
                to: "polygon(50% 0%, 100% 50%, 50% 100%, 0 50%)"
            },
            poly_hexagon: {
                from: "inset(0 0 0 0)",
                to: "polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0 50%)"
            },
            poly_diag_left: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 0, 0 100%, 100% 0)"
            },
            poly_diag_right: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 0, 100% 100%, 100% 0)"
            },
            blob_organic: {
                from: "inset(0 0 0 0)",
                to: "polygon(60% 0%, 100% 40%, 80% 100%, 20% 100%, 0% 40%)"
            },

            blob_irregular: {
                from: "inset(0 0 0 0)",
                to: "polygon(40% 0%, 80% 10%, 100% 40%, 90% 80%, 50% 100%, 10% 80%, 0% 40%, 10% 10%)"
            },

            star: {
                from: "inset(0 0 0 0)",
                to: "polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)"
            },

            skew_right: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 0, 100% 15%, 100% 100%, 0% 85%)"
            },

            skew_left: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 15%, 100% 0, 100% 85%, 0% 100%)"
            },

            wave_top: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 10%, 20% 0%, 40% 10%, 60% 0%, 80% 10%, 100% 0%, 100% 100%, 0 100%)"
            },

            wave_bottom: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 100%, 20% 90%, 40% 100%, 60% 90%, 80% 100%, 100% 90%, 100% 0%, 0 0%)"
            },

            diagonal_cut_double: {
                from: "inset(0 0 0 0)",
                to: "polygon(0 10%, 100% 0%, 100% 90%, 0 100%)"
            },

            corner_round: {
                from: "inset(0 0 0 0)",
                to: "polygon(10% 0%, 100% 0%, 100% 90%, 90% 100%, 0 100%, 0 10%)"
            },
            custom: {
                from: "inset(0 0 0 0)",
                to: tp_custom_clippath
            },
            none: {
                from: "inset(0 0 0 0)",
                to: "inset(0 0 0 0)"
            }
        };



        const preset = ClipPresets[clipPathType] || ClipPresets.none;

        const runImageAnimation = () => {
            gsap.fromTo(
                $image,
                {
                    opacity: 0,
                    x: x,
                    y: y,
                    skewX: skewX,
                    skewY: skewY,
                    scale: scale,
                    rotation: rotation,
                    transformOrigin: transformOrigin,
                    clipPath: preset.from,
                    willChange: "clip-path, transform, opacity",
                },
                {
                    opacity: opacity,
                    x: 0,
                    y: 0,
                    skewX: 0,
                    skewY: 0,
                    scale: 1,
                    rotation: 0,
                    clipPath: preset.to,
                    duration: duration,
                    delay: delay,
                    ease: ease,
                    // stagger: tp_img_stagger
                }
            );
        };

        switch (trigger) {


            case 'onload':
                gsap.delayedCall(0.1, runImageAnimation);
                break;

            case 'onscroll':

                if (tp_scrub) {

                    let tl = gsap.timeline({
                        scrollTrigger: {
                            trigger: $this[0],
                            start: "top 90%",
                            end: "top 10%",
                            scrub: true,
                            // markers: true,
                        }
                    });

                    tl.fromTo(
                        $image,
                        {
                            opacity: 0,
                            x: x,
                            y: y,
                            skewX: skewX,
                            skewY: skewY,
                            scale: scale,
                            rotation: rotation,
                            transformOrigin: transformOrigin,
                            clipPath: preset.from,
                            willChange: "clip-path, transform, opacity",
                        },
                        {
                            opacity: opacity,
                            x: 0,
                            y: 0,
                            skewX: 0,
                            skewY: 0,
                            scale: 1,
                            rotation: 0,
                            clipPath: preset.to,
                            ease: ease,
                            duration: duration,
                        }
                    );

                } else {

                    ScrollTrigger.create({
                        trigger: $this[0],
                        start: "top 80%",
                        once: true,
                        // markers: true,
                        // stagger: tp_img_stagger,

                        onEnter: runImageAnimation
                    });
                }
                break;

            case 'onhover':

                const $realImages = $this.find('img');

                $realImages.each(function () {

                    const $img = $(this);


                    const hoverAnim = gsap.fromTo(
                        $img,
                        {
                            opacity: opacity,
                            x: 0,
                            y: 0,
                            rotation: 0,
                            scale: 1,
                            clipPath: preset.to,
                        },
                        {
                            opacity: opacity,
                            x: x,
                            y: y,
                            scale: scale,
                            rotation: rotation,
                            clipPath: preset.from,
                            duration: duration,
                            ease: ease,
                            paused: true
                        }
                    );

                    $this.on("mouseenter.tpImg", () => {
                        hoverAnim.play();
                    });

                    $this.on("mouseleave.tpImg", () => {
                        hoverAnim.reverse();
                    });

                });

                break;
        }

    });

}