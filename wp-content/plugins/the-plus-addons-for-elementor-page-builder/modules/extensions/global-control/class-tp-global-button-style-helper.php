<?php
/**
 * Global Button Style Helper Trait
 *
 * Shared helpers for widgets that support global button presets,
 * including nested global dimensions and box shadow presets.
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ThePlusAddons\Elementor\ButtonStyle;

use ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global;
use ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'ThePlusAddons\Elementor\ButtonStyle\TP_Global_Button_Style_Helper' ) ) {
	trait TP_Global_Button_Style_Helper {

		protected function ensure_global_button_style_controller() {
			if ( class_exists( '\ThePlusAddons\Elementor\ButtonStyle\TP_Button_Style_Global' ) ) {
				return;
			}

			$path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-button-style-controller.php';

			if ( file_exists( $path ) ) {
				include_once $path;
			}
		}

		protected function ensure_global_dimensions_controller() {
			if ( class_exists( '\ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global' ) ) {
				return;
			}

			$path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-dimensions-controller.php';

			if ( file_exists( $path ) ) {
				include_once $path;
			}
		}

		protected function ensure_global_box_shadow_controller() {
			if ( class_exists( '\ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global' ) ) {
				return;
			}

			$path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-box-shadow-controller.php';

			if ( file_exists( $path ) ) {
				include_once $path;
			}
		}

		protected function get_global_button_style_options() {
			$this->ensure_global_button_style_controller();

			if ( class_exists( '\ThePlusAddons\Elementor\ButtonStyle\TP_Button_Style_Global' ) ) {
				return TP_Button_Style_Global::get_preset_options();
			}

			return array( '' => esc_html__( 'Select Button Styles', 'tpebl' ) );
		}

		protected function format_dimensions_css( $value ) {
			if ( empty( $value ) || ! is_array( $value ) ) {
				return '';
			}

			$unit = ! empty( $value['unit'] ) ? sanitize_text_field( $value['unit'] ) : 'px';
			$keys = array( 'top', 'right', 'bottom', 'left' );
			$out  = array();

			foreach ( $keys as $key ) {
				$out[] = ( '' === (string) ( $value[ $key ] ?? '' ) ? '0' : (float) $value[ $key ] ) . $unit;
			}

			return implode( ' ', $out );
		}

		protected function format_slider_css( $value ) {
			if ( empty( $value ) || ! is_array( $value ) || ! isset( $value['size'] ) || '' === (string) $value['size'] ) {
				return '';
			}

			$unit = ! empty( $value['unit'] ) ? sanitize_text_field( $value['unit'] ) : 'px';

			return (float) $value['size'] . $unit;
		}

		protected function format_box_shadow_css( $preset, $prefix = '' ) {
			$type_key   = $prefix . 'shadow_type';
			$x_key      = $prefix . 'shadow_x';
			$y_key      = $prefix . 'shadow_y';
			$blur_key   = $prefix . 'shadow_blur';
			$spread_key = $prefix . 'shadow_spread';
			$color_key  = $prefix . 'shadow_color';

			$x      = $this->format_slider_css( $preset[ $x_key ] ?? array() );
			$y      = $this->format_slider_css( $preset[ $y_key ] ?? array() );
			$blur   = $this->format_slider_css( $preset[ $blur_key ] ?? array() );
			$spread = $this->format_slider_css( $preset[ $spread_key ] ?? array() );
			$color  = $this->resolve_color_value( $preset, $color_key );

			if ( '' === $x && '' === $y && '' === $blur && '' === $spread && '' === $color ) {
				return '';
			}

			$parts = array();

			if ( ! empty( $preset[ $type_key ] ) && 'bst_inset' === $preset[ $type_key ] ) {
				$parts[] = 'inset';
			}

			$parts[] = '' !== $x ? $x : '0px';
			$parts[] = '' !== $y ? $y : '0px';
			$parts[] = '' !== $blur ? $blur : '0px';
			$parts[] = '' !== $spread ? $spread : '0px';

			if ( '' !== $color ) {
				$parts[] = $color;
			}

			return implode( ' ', $parts );
		}

		/**
		 * Get the max-width pixel value for a named Elementor breakpoint.
		 *
		 * Falls back to safe defaults (tablet = 1024, mobile = 767) when the
		 * Elementor breakpoints API is unavailable.
		 *
		 * @since v6.5.0
		 *
		 * @param string $device  Breakpoint name, e.g. 'tablet' or 'mobile'.
		 * @return int  Max-width value in pixels.
		 */
		protected function get_breakpoint_max( $device ) {
			$defaults = array(
				'widescreen'    => 2400,
				'laptop'        => 1366,
				'tablet_extra'  => 1200,
				'tablet'        => 1024,
				'mobile_extra'  => 880,
				'mobile'        => 767,
			);

			try {
				if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->breakpoints ) ) {
					$bp = \Elementor\Plugin::$instance->breakpoints->get_breakpoints();
					if ( isset( $bp[ $device ] ) ) {
						return (int) $bp[ $device ]->get_value();
					}
				}
			} catch ( \Exception $e ) {
				// Ignore — fall back to defaults below.
			}

			return isset( $defaults[ $device ] ) ? $defaults[ $device ] : 1024;
		}

		/**
		 * Build media-query-wrapped CSS for one responsive breakpoint.
		 *
		 * Only dimension properties (margin, padding, border-width, border-radius)
		 * are responsive. Colors and shadows are desktop-only.
		 *
		 * @since v6.5.0
		 *
		 * @param array  $preset       Full repeater-item preset data.
		 * @param string $scope        CSS scope selector (e.g. "#uid123").
		 * @param string $device       Breakpoint name: 'tablet' | 'mobile' | etc.
		 * @param string $normal_sel   CSS selector appended to $scope for normal state.
		 * @param string $hover_sel    CSS selector appended to $scope for hover state.
		 * @return string  Complete @media block, or '' if nothing to output.
		 */
		protected function build_button_responsive_css( $preset, $scope, $device, $normal_sel, $hover_sel ) {
			$sfx    = '_' . $device;
			$normal = array();
			$hover  = array();

			// --- Normal-state responsive dimensions ---
			$margin = ! empty( $preset[ 'margin' . $sfx ] )
				? $this->resolve_dimensions_value( $preset[ 'margin' . $sfx ] )
				: array();

			$padding = ! empty( $preset[ 'padding' . $sfx ] )
				? $this->resolve_dimensions_value( $preset[ 'padding' . $sfx ] )
				: array();

			$border_width = ! empty( $preset[ 'border_width' . $sfx ] )
				? $this->resolve_dimensions_value( $preset[ 'border_width' . $sfx ] )
				: array();

			$border_radius = ! empty( $preset[ 'border_radius' . $sfx ] )
				? $this->resolve_dimensions_value( $preset[ 'border_radius' . $sfx ] )
				: array();

			// --- Hover-state responsive dimensions ---
			$hover_border_width = ! empty( $preset[ 'hover_border_width' . $sfx ] )
				? $this->resolve_dimensions_value( $preset[ 'hover_border_width' . $sfx ] )
				: array();

			$hover_border_radius = ! empty( $preset[ 'hover_border_radius' . $sfx ] )
				? $this->resolve_dimensions_value( $preset[ 'hover_border_radius' . $sfx ] )
				: array();

			// Build normal declarations.
			if ( ! empty( $margin ) ) {
				$v = $this->format_dimensions_css( $margin );
				if ( '' !== $v ) {
					$normal[] = 'margin:' . $v;
				}
			}

			if ( ! empty( $padding ) ) {
				$v = $this->format_dimensions_css( $padding );
				if ( '' !== $v ) {
					$normal[] = 'padding:' . $v;
				}
			}

			if ( ! empty( $border_width ) ) {
				$v = $this->format_dimensions_css( $border_width );
				if ( '' !== $v ) {
					$normal[] = 'border-width:' . $v;
				}
			}

			if ( ! empty( $border_radius ) ) {
				$v = $this->format_dimensions_css( $border_radius );
				if ( '' !== $v ) {
					$normal[] = 'border-radius:' . $v;
				}
			}

			// Build hover declarations.
			if ( ! empty( $hover_border_width ) ) {
				$v = $this->format_dimensions_css( $hover_border_width );
				if ( '' !== $v ) {
					$hover[] = 'border-width:' . $v;
				}
			}

			if ( ! empty( $hover_border_radius ) ) {
				$v = $this->format_dimensions_css( $hover_border_radius );
				if ( '' !== $v ) {
					$hover[] = 'border-radius:' . $v;
				}
			}

			if ( empty( $normal ) && empty( $hover ) ) {
				return '';
			}

			$max = $this->get_breakpoint_max( $device );
			$css = '@media (max-width:' . $max . 'px){';

			if ( ! empty( $normal ) ) {
				$css .= $scope . ' ' . $normal_sel . '{' . implode( ';', $normal ) . ';}';
			}

			if ( ! empty( $hover ) ) {
				$css .= $scope . ' ' . $hover_sel . '{' . implode( ';', $hover ) . ';}';
			}

			$css .= '}';

			return $css;
		}

		protected function resolve_dimensions_value( $value ) {
			if ( empty( $value ) || ! is_array( $value ) ) {
				return array();
			}

			if ( empty( $value['tp_global_preset'] ) ) {
				return $value;
			}

			$this->ensure_global_dimensions_controller();

			if ( ! class_exists( '\ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global' ) ) {
				return $value;
			}

			$preset_values = TP_Dimensions_Global::get_preset_raw_values( $value['tp_global_preset'] );

			if ( empty( $preset_values ) ) {
				return $value;
			}

			return array_merge( $value, $preset_values );
		}

		protected function resolve_box_shadow_css( $preset, $prefix = '' ) {
			$global_key = $prefix . 'shadow_global_preset';

			if ( ! empty( $preset[ $global_key ] ) ) {
				$this->ensure_global_box_shadow_controller();

				if ( class_exists( '\ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global' ) ) {
					$global_css = TP_Box_Shadow_Global::get_preset_css( $preset[ $global_key ] );

					if ( '' !== $global_css ) {
						return $global_css;
					}
				}
			}

			return $this->format_box_shadow_css( $preset, $prefix );
		}

		protected function build_global_button_style_css( $preset_id, $scope ) {
			$this->ensure_global_button_style_controller();

			if ( ! class_exists( '\ThePlusAddons\Elementor\ButtonStyle\TP_Button_Style_Global' ) ) {
				return '';
			}

			$preset = TP_Button_Style_Global::get_preset( $preset_id );

			if ( empty( $preset ) ) {
				return '';
			}

			$normal = array();
			$hover  = array();

			$margin              = ! empty( $preset['margin'] ) ? $this->resolve_dimensions_value( $preset['margin'] ) : array();
			$padding             = ! empty( $preset['padding'] ) ? $this->resolve_dimensions_value( $preset['padding'] ) : array();
			$border_width        = ! empty( $preset['border_width'] ) ? $this->resolve_dimensions_value( $preset['border_width'] ) : array();
			$border_radius       = ! empty( $preset['border_radius'] ) ? $this->resolve_dimensions_value( $preset['border_radius'] ) : array();
			$hover_border_width  = ! empty( $preset['hover_border_width'] ) ? $this->resolve_dimensions_value( $preset['hover_border_width'] ) : array();
			$hover_border_radius = ! empty( $preset['hover_border_radius'] ) ? $this->resolve_dimensions_value( $preset['hover_border_radius'] ) : array();

			if ( ! empty( $margin ) ) {
				$normal[] = 'margin:' . $this->format_dimensions_css( $margin );
			}

			if ( ! empty( $padding ) ) {
				$normal[] = 'padding:' . $this->format_dimensions_css( $padding );
			}

			$text_color = $this->resolve_color_value( $preset, 'text_color' );
			if ( '' !== $text_color ) {
				$normal[] = 'color:' . $text_color;
			}

			$background_color = $this->resolve_color_value( $preset, 'background_color' );
			if ( '' !== $background_color ) {
				$normal[] = 'background-color:' . $background_color;
			}

			if ( isset( $preset['border_style'] ) && '' !== $preset['border_style'] ) {
				$normal[] = 'border-style:' . sanitize_text_field( $preset['border_style'] );
			}

			if ( ! empty( $border_width ) ) {
				$normal[] = 'border-width:' . $this->format_dimensions_css( $border_width );
			}

			$border_color = $this->resolve_color_value( $preset, 'border_color' );
			if ( '' !== $border_color ) {
				$normal[] = 'border-color:' . $border_color;
			}

			if ( ! empty( $border_radius ) ) {
				$normal[] = 'border-radius:' . $this->format_dimensions_css( $border_radius );
			}

			$normal_shadow = $this->resolve_box_shadow_css( $preset );
			if ( '' !== $normal_shadow ) {
				$normal[] = 'box-shadow:' . $normal_shadow;
			}

			$hover_text_color = $this->resolve_color_value( $preset, 'hover_text_color' );
			if ( '' !== $hover_text_color ) {
				$hover[] = 'color:' . $hover_text_color;
			}

			$hover_background_color = $this->resolve_color_value( $preset, 'hover_background_color' );
			if ( '' !== $hover_background_color ) {
				$hover[] = 'background-color:' . $hover_background_color;
			}

			if ( isset( $preset['hover_border_style'] ) && '' !== $preset['hover_border_style'] ) {
				$hover[] = 'border-style:' . sanitize_text_field( $preset['hover_border_style'] );
			}

			if ( ! empty( $hover_border_width ) ) {
				$hover[] = 'border-width:' . $this->format_dimensions_css( $hover_border_width );
			}

			$hover_border_color = $this->resolve_color_value( $preset, 'hover_border_color' );
			if ( '' !== $hover_border_color ) {
				$hover[] = 'border-color:' . $hover_border_color;
			}

			if ( ! empty( $hover_border_radius ) ) {
				$hover[] = 'border-radius:' . $this->format_dimensions_css( $hover_border_radius );
			}

			$hover_shadow = $this->resolve_box_shadow_css( $preset, 'hover_' );
			if ( '' !== $hover_shadow ) {
				$hover[] = 'box-shadow:' . $hover_shadow;
			}

			$css = '';

			if ( ! empty( $normal ) ) {
				$css .= $scope . ' .pt_plus_button .button-link-wrap{' . implode( ';', $normal ) . ';}';
			}

			$icon_color = $this->resolve_color_value( $preset, 'icon_color' );
			if ( '' !== $icon_color ) {
				$css .= $scope . ' .pt_plus_button .button-link-wrap .btn-icon,' . $scope . ' .pt_plus_button .button-link-wrap svg{color:' . $icon_color . ';fill:' . $icon_color . ';}';
			}

			if ( ! empty( $hover ) ) {
				$css .= $scope . ' .pt_plus_button .button-link-wrap:hover{' . implode( ';', $hover ) . ';}';
			}

			$hover_icon_color = $this->resolve_color_value( $preset, 'hover_icon_color' );
			if ( '' !== $hover_icon_color ) {
				$css .= $scope . ' .pt_plus_button .button-link-wrap:hover .btn-icon,' . $scope . ' .pt_plus_button .button-link-wrap:hover svg{color:' . $hover_icon_color . ';fill:' . $hover_icon_color . ';}';
			}

			// Responsive dimension overrides (tablet → mobile, highest-to-lowest so cascade works correctly).
			foreach ( array( 'tablet', 'mobile' ) as $device ) {
				$css .= $this->build_button_responsive_css(
					$preset,
					$scope,
					$device,
					'.pt_plus_button .button-link-wrap',
					'.pt_plus_button .button-link-wrap:hover'
				);
			}

			return $css;
		}

		/**
		 * Resolve a color value from a preset, falling back to Elementor global color CSS variables.
		 *
		 * When the user picks a global Elementor color in a repeater COLOR control, Elementor
		 * stores an empty string in the value key and puts the reference in __globals__. This
		 * helper checks both locations and returns either the direct value or the resolved
		 * CSS variable (e.g. var(--e-global-color-primary)).
		 *
		 * @since v6.5.0
		 *
		 * @param array  $preset Repeater item data.
		 * @param string $key    Control key to resolve.
		 * @return string  Color value or CSS variable string, or '' if unset.
		 */
		protected function resolve_color_value( $preset, $key ) {
			if ( ! empty( $preset['__globals__'][ $key ] ) ) {
				$parsed = wp_parse_url( $preset['__globals__'][ $key ] );
				if ( ! empty( $parsed['query'] ) ) {
					parse_str( $parsed['query'], $q );
					if ( ! empty( $q['id'] ) ) {
						return 'var(--e-global-color-' . sanitize_key( $q['id'] ) . ')';
					}
				}
			}

			return ! empty( $preset[ $key ] ) ? sanitize_text_field( $preset[ $key ] ) : '';
		}
	}
}
