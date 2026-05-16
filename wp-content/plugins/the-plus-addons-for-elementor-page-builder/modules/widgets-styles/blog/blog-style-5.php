<?php
/**
 * The file blog style - 5
 *
 * @link    https://posimyth.com/
 * @since   6.0.15
 *
 * @package Theplus
 */

$tp_absolute_class = '';
$wrap_classes      = '';

if ( 'yes' === $tp_absolute_layout ) {
	$tp_absolute_class = 'tpae-protebal-absolute';
}

$postid  = get_the_ID();
$bg_attr = $lazyclass = '';
if ( 'metro' === $layout ) {

	$wrap_classes .= ' tpae-compect-metro';

	if ( ( ! empty( $display_thumbnail ) && 'yes' === $display_thumbnail ) && ! empty( $thumbnail ) ) {
		$featured_image = get_the_post_thumbnail_url( $postid, $thumbnail );
		if ( ! empty( $featured_image ) ) {
			$bg_attr = 'style="background:url(' . $featured_image . ') #f7f7f7;"';
		} else {
			$bg_attr = l_theplus_loading_image_grid( $postid, 'background' );
		}
	} else {
		$featured_image = get_the_post_thumbnail_url( $postid, 'full' );

		if ( ! empty( $featured_image ) ) {
			$bg_attr = l_theplus_loading_bg_image( $postid );
		} else {
			$bg_attr = l_theplus_loading_image_grid( $postid, 'background' );
		}
	}
	if ( tp_has_lazyload() ) {
		$lazyclass = ' lazy-background';
	}
}

		echo '<article id="post-' . get_the_ID() . '" ';
post_class();
			echo '>';

			echo '<div class="tpae-compect-blog-wrap ' . esc_attr( $wrap_classes ) . '">';

				echo '<div class="tp-blog-cetegory-style-5">';
if ( 'yes' === $display_post_category ) {
	include L_THEPLUS_WSTYLES . 'blog/blog-category-' . $post_category_style . '.php';
}
				echo '</div>';
if ( 'metro' !== $layout ) {
	echo '<div class="tpae-blog-image">';
	echo '<div class="tpae-overlay-blog"></div>';
		echo '<a href="' . esc_url( get_the_permalink() ) . '">';
			include L_THEPLUS_WSTYLES . 'blog/format-image.php';
		echo '</a>';



		echo '<div class="tpae-blog-meta-overflow">';

	if ( 'yes' === $show_date_time && 'yes' === $tp_date_overflow ) {
			include L_THEPLUS_WSTYLES . 'blog/meta-date.php';
	}

	if ( 'yes' === $tp_meta_overflow && 'yes' === $show_author_name ) {
				echo '<span class="tpae-blog-author">';
					echo $author_prefix . ' ';
					echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="fn">';
						echo esc_html( get_the_author() );
					echo '</a>';
				echo '</span>';
	}

	if ( 'yes' === $show_read_time && 'yes' === $tp_post_tima_overflow ) {
		$time_diff = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) );
		echo '<span class="tpae-blog-read-time">' . esc_html( $time_diff ) . ' ago</span>';
	}

						echo '</div>';
						echo '</div>';
}

				echo '<div class="tpae-blog-content ' . esc_attr( $tp_absolute_class ) . ' ' . esc_attr( $wrap_classes ) . '">';

					echo '<div class="tpae-blog-meta">';

if ( 'yes' === $show_date_time && 'yes' !== $tp_date_overflow ) {
	include L_THEPLUS_WSTYLES . 'blog/meta-date.php';
}

if ( 'yes' === $show_author_name && 'yes' !== $tp_meta_overflow ) {
	echo '<span class="tpae-blog-author">';
		echo $author_prefix . ' ';
		echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="fn">';
			echo esc_html( get_the_author() );
		echo '</a>';
	echo '</span>';
}

if ( 'yes' === $show_read_time && 'yes' !== $tp_post_tima_overflow ) {
	$time_diff = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) );
	echo '<span class="tpae-blog-read-time">' . esc_html( $time_diff ) . ' ago</span>';
}

				echo '</div>';

				require L_THEPLUS_WSTYLES . 'blog/post-meta-title.php';

if ( 'yes' === $display_excerpt && get_the_excerpt() ) {
	echo '<div class="tpae-blog-excerpt">';
		include L_THEPLUS_WSTYLES . 'blog/get-excerpt.php';
	echo '</div>';
}

if ( 'yes' === $display_btn ) {
		echo $the_button;
}

				echo '</div>';

if ( 'metro' === $layout ) {
		echo '<div class="blog-bg-image-metro ' . esc_attr( $lazyclass ) . '" ' . $bg_attr . '></div>';
}

				echo '</div>';

			echo '</article>';
