<?php
/**
 * Profile section tab bar.
 *
 * Shared across the four account pages (Profile, Payment method, Invoice,
 * Package detail). Each tab links to its real page so the browser navigates
 * to /user-profile, /payment-method, /invoice, /package-detail.
 *
 * Usage:
 *   get_template_part( 'template-parts/profile/profile-tabs', null, array( 'active' => 'profile' ) );
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active = isset( $args['active'] ) ? $args['active'] : 'profile';
$items  = function_exists( 'ivy_profile_nav_items' ) ? ivy_profile_nav_items() : array();
?>
<nav class="profile__tabs" aria-label="<?php esc_attr_e( 'Account sections', 'hello-elementor' ); ?>">
	<?php foreach ( $items as $key => $item ) :
		$is_active = ( $key === $active );
		?>
		<a
			href="<?php echo esc_url( ivy_profile_url( $item['slug'] ) ); ?>"
			class="profile__tab<?php echo $is_active ? ' profile__tab--active' : ''; ?>"
			<?php echo $is_active ? 'aria-current="page"' : ''; ?>
		>
			<?php echo esc_html( $item['label'] ); ?>
		</a>
	<?php endforeach; ?>
</nav>
