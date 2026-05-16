<?php
/**
 * The file cetegory style- 1
 *
 * @link    https://posimyth.com/
 * @since   6.0.15
 *
 * @package Theplus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$postid = get_the_ID(); ?>
<div class="post-category-list style-1">	
	<?php
	$categories = get_the_category( $postid );
	if ( ! empty( $dpc_all ) && 'yes' === $dpc_all ) {
		$i = 1;
	} else {
		$i = 0;
	}
	foreach ( $categories as $category ) {
		if ( ! empty( $dpc_all ) && 'yes' === $dpc_all ) {
			echo '<span><a href="' . esc_url( get_category_link( $category->cat_ID ) ) . '">' . esc_html( $category->name ) . '</a></span>';
		} else {
			if ( $i == 0 ) {
				echo '<span><a href="' . esc_url( get_category_link( $category->cat_ID ) ) . '">' . esc_html( $category->name ) . '</a></span>';
			}
			++$i;
		}
	}
	?>
</div>
