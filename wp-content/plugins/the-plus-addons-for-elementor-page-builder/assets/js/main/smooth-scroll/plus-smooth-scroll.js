/*Smooth Scroll*/
(function ($) {
	"use strict";
	var WidgetSmoothScrollHandler = function ($scope, $) {
		$(document).ready(function ($) {
			var $container = $('.plus-smooth-scroll', $scope);
			if ($container.length) {
				var data_scrollType = $container.attr("data-scroll-type");

				if (data_scrollType === 'advanced') {
					/*Check for multiple instances*/
					if ($('html').hasClass('lenis-plus-initialized')) {
						return;
					}
					$('html').addClass('lenis-plus-initialized');

					/*Destroy basic SmoothScroll to prevent conflict if it exists*/
					if (typeof SmoothScroll !== 'undefined' && typeof SmoothScroll.destroy === 'function') {
						SmoothScroll.destroy();
					}

					var duration = parseFloat($container.attr("data-lenis-duration")) || 1.2;
					var orientation = $container.attr("data-lenis-orientation") || 'vertical';
					var gestureOrientation = $container.attr("data-lenis-gestureOrientation") || 'vertical';
					var smoothWheel = $container.attr("data-lenis-smoothWheel") === 'yes';
					var smoothTouch = $container.attr("data-lenis-smoothTouch") === 'yes';
					var wheelMultiplier = parseFloat($container.attr("data-lenis-wheelMultiplier")) || 1;
					var touchMultiplier = parseFloat($container.attr("data-lenis-touchMultiplier")) || 1.5;
					var infinite = $container.attr("data-lenis-infinite") === 'yes';
					var autoResize = $container.attr("data-lenis-autoResize") === 'yes';
					var lerp = parseFloat($container.attr("data-lenis-lerp")) || 0.1;

					if (!$('html').hasClass('lenis')) {
						var lenisStyle = `
							<style>
								html.lenis, html.lenis body { height: auto; }
								.lenis.lenis-smooth { scroll-behavior: auto !important; }
								.lenis.lenis-smooth [data-lenis-prevent] { overscroll-behavior: contain; }
								iframe { pointer-events: none; }
							</style>
						`;
						$('head').append(lenisStyle);
						$('html').addClass('lenis');
					}

					var lenisOptions = {
						duration: duration,
						lerp: lerp,
						wheelMultiplier: wheelMultiplier,
						touchMultiplier: touchMultiplier,
						smoothWheel: smoothWheel,
						syncTouch: smoothTouch,
						orientation: orientation,
						gestureOrientation: gestureOrientation,
						autoResize: autoResize,
						infinite: infinite,
						easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
					};

					var lenis = new Lenis(lenisOptions);
					window.plus_lenis = lenis;

					function raf(time) {
						lenis.raf(time);
						requestAnimationFrame(raf);
					}
					requestAnimationFrame(raf);

					if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
						lenis.on('scroll', ScrollTrigger.update);
						gsap.ticker.lagSmoothing(0);
					}

				} else {
					var data_frameRate = ($container.attr("data-frameRate") == undefined) ? 150 : $container.attr("data-frameRate"),
						data_animationTime = ($container.attr("data-animationTime") == undefined) ? 1000 : $container.attr("data-animationTime"),
						data_stepSize = ($container.attr("data-stepSize") == undefined) ? 100 : $container.attr("data-stepSize"),
						data_pulseAlgorithm = ($container.attr("data-pulseAlgorithm") == undefined) ? 1 : $container.attr("data-pulseAlgorithm"),
						data_pulseScale = ($container.attr("data-pulseScale") == undefined) ? 4 : $container.attr("data-pulseScale"),
						data_pulseNormalize = ($container.attr("data-pulseNormalize") == undefined) ? 1 : $container.attr("data-pulseNormalize"),
						data_accelerationDelta = ($container.attr("data-accelerationDelta") == undefined) ? 50 : $container.attr("data-accelerationDelta"),
						data_accelerationMax = ($container.attr("data-accelerationMax") == undefined) ? 3 : $container.attr("data-accelerationMax"),
						data_keyboardSupport = ($container.attr("data-keyboardSupport") == undefined) ? 1 : $container.attr("data-keyboardSupport"),
						data_arrowScroll = ($container.attr("data-arrowScroll") == undefined) ? 50 : $container.attr("data-arrowScroll"),
						data_touchpadSupport = ($container.attr("data-touchpadSupport") == undefined) ? 0 : $container.attr("data-touchpadSupport"),
						data_fixedBackground = ($container.attr("data-fixedBackground") == undefined) ? 1 : $container.attr("data-fixedBackground"),
						data_tablet_off = ($container.attr("data-tablet-off") == undefined) ? 50 : $container.attr("data-tablet-off"),
						data_Basic = ($container.attr("data-basicdata")) ? JSON.parse($container.attr("data-basicdata")) : [];

					if (!$('body').hasClass("plus-smooth-scroll-tras")) {
						$('body').addClass("plus-smooth-scroll-tras");
						$('head').append('<style>.plus-smooth-scroll-tras .magic-scroll .parallax-scroll,.plus-smooth-scroll-tras .magic-scroll .scale-scroll,.plus-smooth-scroll-tras .magic-scroll .both-scroll{-webkit-transition: -webkit-transform 0s ease .0s;-ms-transition: -ms-transform 0s ease .0s;-moz-transition: -moz-transform 0s ease .0s;-o-transition: -o-transform 0s ease .0s;transition: transform 0s ease .0s;will-change: transform;}</style>');
					}
					if (data_tablet_off == 'yes') {
						var width = window.innerWidth;
						if (width > 800) {
							if (!$('body').hasClass("plus-smooth-scroll-tras")) {
								$('body').addClass("plus-smooth-scroll-tras");
							}
							SmoothScroll({ frameRate: data_frameRate, animationTime: data_animationTime, stepSize: data_stepSize, pulseAlgorithm: data_pulseAlgorithm, pulseScale: data_pulseScale, pulseNormalize: data_pulseNormalize, accelerationDelta: data_accelerationDelta, accelerationMax: data_accelerationMax, keyboardSupport: data_keyboardSupport, arrowScroll: data_arrowScroll, touchpadSupport: data_touchpadSupport, fixedBackground: data_fixedBackground, allowedBrowsers: data_Basic.Browsers });
						} else {
							if ($('body').hasClass("plus-smooth-scroll-tras")) {
								$('body').removeClass("plus-smooth-scroll-tras");
							}
						}
					} else {
						SmoothScroll({ frameRate: data_frameRate, animationTime: data_animationTime, stepSize: data_stepSize, pulseAlgorithm: data_pulseAlgorithm, pulseScale: data_pulseScale, pulseNormalize: data_pulseNormalize, accelerationDelta: data_accelerationDelta, accelerationMax: data_accelerationMax, keyboardSupport: data_keyboardSupport, arrowScroll: data_arrowScroll, touchpadSupport: data_touchpadSupport, fixedBackground: data_fixedBackground, allowedBrowsers: data_Basic.Browsers })
					}
				}


			}
		});
	};
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/tp-smooth-scroll.default', WidgetSmoothScrollHandler);
	});
})(jQuery);