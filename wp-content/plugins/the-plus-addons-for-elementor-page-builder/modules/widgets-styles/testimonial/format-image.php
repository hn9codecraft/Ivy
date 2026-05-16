<?php
/**
 * Testimonial format-image
 *
 * @package ThePlus
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$tp_border_reduis = ! empty( $settings['featured_image_radius'] ) ? $settings['featured_image_radius'] : '';

$has_border_radius = ! empty( $tp_border_reduis['top'] ) || ! empty( $tp_border_reduis['right'] ) || ! empty( $tp_border_reduis['bottom'] ) || ! empty( $tp_border_reduis['left'] );

if ( 'tlrepeater' === $con_from ) {
	$tsize   = '';
	$feat_id = $testi_image_id;
	if ( ! empty( $feat_id ) ) {
		$featured_image = tp_get_image_rander( $feat_id, $tsize );
	} elseif ( ! empty( $testi_image['url'] ) ) {
		$featured_image = '<img src="' . esc_url( $testi_image['url'] ) . '" alt="' . esc_attr( $testi_label ) . '">';
	} else {
		$featured_image = l_theplus_get_thumb_url();
		$featured_image = '<img src="' . esc_url( $featured_image ) . '" alt="' . esc_attr( $testi_label ) . '">';
	}
} else {
	global $post;
	$postid = get_the_ID();

	$featured_image_url = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) );
	if ( ! empty( $featured_image_url ) ) {
		$featured_image = get_the_post_thumbnail_url( get_the_ID(), 'tp-image-grid' );
		$featured_image = '<img src="' . esc_url( $featured_image ) . '" alt="' . esc_attr( get_the_title() ) . '">';
	} else {
		$featured_image = l_theplus_get_thumb_url();
		$featured_image = '<img src="' . esc_url( $featured_image ) . '" alt="' . esc_attr( get_the_title() ) . '">';
	}
}

?>

<div class="testimonial-featured-image">
	<?php
	if ( ! empty( $has_border_radius ) ) {
		?>
		<span class="thumb-wrap">
			<?php echo $featured_image; ?>
		</span>
		<?php
	} else {
		?>
		<span class="thumb-wrap" style="
			display: inline-block;
			-webkit-mask-image: url('<?php echo esc_url( L_THEPLUS_ASSETS_URL . 'svg/testimonial-mask.svg' ); ?>');
			-webkit-mask-repeat: no-repeat;
			mask-image: url('<?php echo esc_url( L_THEPLUS_ASSETS_URL . 'svg/testimonial-mask.svg' ); ?>');
			mask-size: contain;
			width: 75px;
            height: 90px;
		">
			<?php echo $featured_image; ?>
		</span>
		<?php
	}
	?>
</div>
