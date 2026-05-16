<?php
/**
 * The file blog style - 5 button
 *
 * @link    https://posimyth.com/
 * @since   6.0.15
 *
 * @package Theplus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( 'font_awesome' === $button_icon_style ) {
	$icons = $button_icon;
} else {
	$icons = '';
}
	$button_content = '';
	$icons_before   = $icons_after = '';
if ( 'before' === $before_after && ! empty( $icons ) ) {
		$icons_before = '<i class="btn-icon button-before ' . esc_attr( $icons ) . '"></i>';
}
if ( 'after' === $before_after && ! empty( $icons ) ) {
		$icons_after = '<i class="btn-icon button-after ' . esc_attr( $icons ) . '"></i>';
}

if ( 'style-8' === $button_style ) {
	$button_content = $icons_before . $button_text . $icons_after;
}

if ( 'style-7' === $button_style ) {
	$button_content = $button_text . '<span class="btn-arrow"></span>';
}
if ( 'style-9' === $button_style ) {
	$button_content = $button_text . '<span class="btn-arrow"><i class="fa-show fas fa-chevron-right" aria-hidden="true"></i><i class="fa-hide fas fa-chevron-right" aria-hidden="true"></i></span>';
}
	return $button_content;
