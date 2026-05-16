== Slide everything for Elementor ==
Contributors: migaweb
Tags: elementor, widgets, slider, swiper
Donate link: https://www.buymeacoffee.com/miga
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.7.0
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

== Description ==
Creates a simple Swiper slider out of container elements. Flexbox has to be active.

== How to use it ==

https://youtu.be/s8oyX1u-T6E

* create a horizontal container and give it an ID (Advanced - CSS ID)
* add multiple sub containers (those are the slides)
* add the `Slide everything` widget somewhere in the page
* set the ID of the container in "Slider ID"
* change some settings
* preview the page

Events:

You can listen to the `miga_slide_everything_init` event to get informed when the slider is ready to use:
```
document.addEventListener("miga_slide_everything_init", function(e) {
  alert("ready");
});
```

Currently only working with one slider in your page!

== Installation ==
* install [Elementor](https://be.elementor.com/visit/?bta=207645&nci=5383)
* install the plugin
* insert the widget
* create a container with subcontainers
* assign an ID to the outer container
* set the ID (with #) in the widget

== Screenshots ==

1. Editor view

== Changelog ==

1.7.0
* freeMode option
* tested with WP 6.8 and Elementor 3.30

1.6.4
* change init method
* tested with WP 6.8 and Elementor 3.28

1.6.3
* fix for double arrows
* add new "autoplay reverse" property

1.6.2
* small internal code update

1.6.1
* improvements for Elementor 3.26.3

1.6.0
* tested with WP 6.7.1
* update for Elementor 3.26.0

1.5.3
* added "miga_slide_everything_init" event

1.5.2
* new property "disable touch move"

1.5.1
* tested with WP 6.5

1.5.0
* fix arrow colors

1.4.0
* new output method that adds another <div> around each slide to keep more Elementor settings (e.g. padding).

1.3.0
* add Mouse wheel switch, bugfix for boxed containers

1.2.0
* fix arrows for new Swiper version

1.1.0
* arrow styles and absolute positioning

1.0.7
* tested with 6.1.0

1.0.6
* add slider for pagination position

1.0.5
* use multiple sliders in one page

1.0.4
* pagination and arrows

1.0.3
* some more output/info

1.0.2
* fix center slides bug
* add # automatically if missing

1.0.1
* Center slides

1.0.0
* Initial release.
