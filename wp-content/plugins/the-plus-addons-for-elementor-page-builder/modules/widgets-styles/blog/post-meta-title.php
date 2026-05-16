<?php
/**
 * Blog meta title
 *
 * @package ThePlus
 * @since 1.0.0
 * @version 5.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! isset( $post_title_tag ) && empty( $post_title_tag ) ) {
	$post_title_tag = 'h3';
}
$title_text_main = get_the_title();
$title_text      = '';
if ( ( ! empty( $display_title_limit ) && 'yes' === $display_title_limit ) && ! empty( $display_title_input ) ) {
	if ( ! empty( $display_title_by ) ) {
		if ( 'char' === $display_title_by ) {
			$title_text = substr( $title_text_main, 0, $display_title_input );
		} elseif ( 'word' === $display_title_by ) {
			$title_text = l_limit_words( $title_text_main, $display_title_input );
		}
	}
	if ( 'char' === $display_title_by ) {
		if ( strlen( $title_text_main ) > $display_title_input ) {
			if ( ! empty( $display_title_3_dots ) && 'yes' === $display_title_3_dots ) {
				$title_text .= '...';
			}
		}
	} elseif ( 'word' === $display_title_by ) {
		if ( str_word_count( $title_text_main ) > $display_title_input ) {
			if ( ! empty( $display_title_3_dots ) && 'yes' === $display_title_3_dots ) {
				$title_text .= '...';
			}
		}
	}
} else {
	$title_text = esc_html( $title_text_main );
}
?>
<<?php echo esc_attr( l_theplus_validate_html_tag( $post_title_tag ) ); ?> class="post-title">
	<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo ( wp_kses_post( $title_text ) ); ?></a>
</<?php echo esc_attr( l_theplus_validate_html_tag( $post_title_tag ) ); ?>>
