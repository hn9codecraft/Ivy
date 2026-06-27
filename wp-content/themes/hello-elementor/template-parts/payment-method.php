<?php
/**
 * Template Name: Payment Method
 *
 * @package HelloElementor
 */

get_header();

/**
 * Demo card data — replace with real saved-payment-method data
 * (e.g. from WooCommerce tokens / your gateway) when available.
 */
$card = array(
	'number'  => '2345 14** **** 1289',
	'created' => '21 Feb, 2026',
	'expiry'  => '21 Feb, 2030',
);
?>

<div class="dashboard">

	<?php get_template_part( 'template-parts/dashboard-sidebar' ); ?>

	<main class="dashboard__main">
		<section class="profile">

			<!-- Tabs -->
			<nav class="profile__tabs" aria-label="<?php esc_attr_e( 'Account sections', 'hello-elementor' ); ?>">
				<a href="#profile" class="profile__tab"><?php esc_html_e( 'Profile', 'hello-elementor' ); ?></a>
				<a href="#payment" class="profile__tab profile__tab--active"><?php esc_html_e( 'Payment method', 'hello-elementor' ); ?></a>
				<a href="#invoice" class="profile__tab"><?php esc_html_e( 'Invoice', 'hello-elementor' ); ?></a>
				<a href="#package" class="profile__tab"><?php esc_html_e( 'Package detail', 'hello-elementor' ); ?></a>
			</nav>

			<!-- Payment method -->
			<div class="payment">
				<div class="payment__card">

					<!-- Card visual -->
					<img
						class="payment__visual"
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/card.png' ); ?>"
						alt="<?php esc_attr_e( 'Saved card', 'hello-elementor' ); ?>"
					>

					<!-- Details -->
					<div class="payment__details">
						<div class="payment__detail">
							<p class="payment__detail-label"><?php esc_html_e( 'Card Number', 'hello-elementor' ); ?></p>
							<p class="payment__detail-value"><?php echo esc_html( $card['number'] ); ?></p>
						</div>
						<div class="payment__detail">
							<p class="payment__detail-label"><?php esc_html_e( 'Created', 'hello-elementor' ); ?></p>
							<p class="payment__detail-value"><?php echo esc_html( $card['created'] ); ?></p>
						</div>
						<div class="payment__detail">
							<p class="payment__detail-label"><?php esc_html_e( 'Expiry Date', 'hello-elementor' ); ?></p>
							<p class="payment__detail-value"><?php echo esc_html( $card['expiry'] ); ?></p>
						</div>
					</div>

					<!-- Action -->
					<button type="button" class="btn btn-primary payment__change">
						<?php esc_html_e( 'Change Card', 'hello-elementor' ); ?>
					</button>

				</div>
			</div>

		</section>
	</main>

</div>

<?php
get_footer();
