<?php
/**
 * The file cetegory style- 2
 *
 * @link    https://posimyth.com/
 * @since   6.0.15
 *
 * @package Theplus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$postid = get_the_ID(); ?>
<div class="post-category-list style-2">	
	<?php
	$categories = get_the_category( $postid );
	foreach ( $categories as $category ) {
		echo '<span><a href="' . esc_url( get_category_link( $category->cat_ID ) ) . '">' . esc_html( $category->name ) . '</a></span>';
	}
	?>
</div>
