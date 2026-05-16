(function ($) {
	"use strict";

	if (typeof gsap === 'undefined') return;
	if (typeof ScrollTrigger !== 'undefined') gsap.registerPlugin(ScrollTrigger);


	function tpPlayGSAPAnimation(tp_wrap, tp_gsap_options) {

		if (tp_gsap_options?.plus_gsap_animation_type === 'tp_custom' || tp_gsap_options?.plus_gsap_animation_type === 'none') {
			return;
		}

		const tp_ani_direction = tp_gsap_options?.tp_fade_from || '';
		const tp_offset = parseFloat(tp_gsap_options.tp_fade_offset) || 50;
		const tp_duration = parseFloat(tp_gsap_options.tp_duration) || 1.2;
		const tp_delay = parseFloat(tp_gsap_options.tp_delay) || 0;
		const tp_ease = tp_gsap_options.tp_ease || 'power2.out';
		const tp_trigger = tp_gsap_options.tp_trigger || 'tp_on_load';
		const tp_stagger_switch = tp_gsap_options.tp_stagger || '';
		const tp_repeat = tp_gsap_options.tp_repeat || '';
		const tp_ani_type = tp_gsap_options.tp_ani_type || '';

		let tp_targets = tp_wrap;

		if (tp_stagger_switch === 'yes') {
			tp_targets = tp_wrap.querySelectorAll('.elementor-widget');
			tp_targets.forEach(function (widget) {
				widget.classList.add('tp-standard-gsap');
			});
			tp_targets = tp_wrap.querySelectorAll('.tp-standard-gsap');
		}

		let fromVars = {};


		if ('tp_fade' === tp_ani_type) {
			fromVars.autoAlpha = 0;
		}
		if ('tp_scale' === tp_ani_type) {
			fromVars.scale = 0.5;
		}

		switch (tp_ani_direction) {
			case 'top':
				fromVars.y = -tp_offset;
				break;
			case 'bottom':
				fromVars.y = tp_offset;
				break;
			case 'left':
				fromVars.x = -tp_offset;
				break;
			case 'right':
				fromVars.x = tp_offset;
				break;
			default:
				fromVars.y = tp_offset;
		}


		var toVars = {
			x: 0,
			y: 0,
			duration: tp_duration,
			delay: tp_delay,
			ease: tp_ease,
			repeat: tp_repeat === 'yes' ? -1 : 0,
			yoyo: tp_repeat === 'yes' ? true : false,
			overwrite: true,
		};

		if ('tp_fade' === tp_ani_type) {
			toVars.autoAlpha = 1
		}

		if ('tp_scale' === tp_ani_type) {
			toVars.scale = 1
		}

		if (tp_stagger_switch === 'yes') {
			toVars.stagger = tp_delay;
		}

		if (tp_trigger !== 'tp_on_load') {
			toVars.scrollTrigger = {
				trigger: tp_wrap,
				start: "top 50%",
				toggleActions: "play none none reverse",
				once: false,
			};
		}

		gsap.fromTo(
			tp_targets,
			{ ...fromVars },
			toVars
		);
	}

	function tpInitGSAPAnimations() {

		document.querySelectorAll('.tp-standard-gsap').forEach(function (tp_wrap) {

			setTimeout(() => {

				const tp_gsap_control = tp_wrap.getAttribute('data-tp_gsap_control');
				const tp_gsap_options = tp_gsap_control ? JSON.parse(tp_gsap_control) : {};

				tpPlayGSAPAnimation(tp_wrap, tp_gsap_options);

			}, 100);
		});
	}

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/widget', tpInitGSAPAnimations);
		elementorFrontend.hooks.addAction('frontend/element_ready/section', tpInitGSAPAnimations);
		elementorFrontend.hooks.addAction('frontend/element_ready/column', tpInitGSAPAnimations);
		elementorFrontend.hooks.addAction('frontend/element_ready/container', tpInitGSAPAnimations);
	});

	// $(window).on("elementor/frontend/init", function () {

	// 	if (typeof elementor === "undefined" || typeof elementorFrontend === "undefined") {
	// 		return;
	// 	}

	// 	elementor.on("preview:loaded", function () {

	// 		elementor.channels.editor.on("tp:play_gsap_animation", function (controlView) {

	// 			let elementObj = controlView.options.element;
	// 			let elementId = elementObj.model.id;
	// 			let settings = elementObj.model.get('settings').attributes;

	// 			let tp_wrap = document.querySelector('.elementor-element-' + elementId);
	// 			if (!tp_wrap) return;

	// 			let tp_gsap_control = {
	// 				plus_gsap_animation_type: settings.plus_gsap_animation_type,
	// 				tp_select_global_animation: settings.tp_select_global_animation,
	// 				tp_trigger: settings.tp_gsap_trigger,
	// 				tp_delay: settings.tp_delay,
	// 				tp_fade_from: settings.tp_fade_from,
	// 				tp_duration: settings.tp_duration,
	// 				tp_ease: settings.tp_ease,
	// 				tp_fade_offset: settings.tp_fade_offset,
	// 				tp_stagger: settings.tp_stagger,
	// 				tp_repeat: settings.tp_repeat,
	// 				tp_ani_type: settings.tp_ani_type,
	// 			};

	// 			tpPlayGSAPAnimation(tp_wrap, tp_gsap_control);
	// 		});
	// 	});
	// });

})(jQuery);