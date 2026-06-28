<?php
/**
 * Template Name: Invoice
 *
 * @package HelloElementor
 */

get_header();

/**
 * Demo invoice rows — replace with real order/invoice data
 * (e.g. from WooCommerce orders) when available.
 */
$invoices = array(
	array( 'date' => '24-09-26', 'plan' => 'Basic Plus Annual', 'price' => '$351.00', 'url' => '#' ),
	array( 'date' => '24-09-26', 'plan' => 'Basic Plus Annual', 'price' => '$351.00', 'url' => '#' ),
	array( 'date' => '24-09-26', 'plan' => 'Basic Plus Annual', 'price' => '$351.00', 'url' => '#' ),
	array( 'date' => '24-09-26', 'plan' => 'Basic Plus Annual', 'price' => '$351.00', 'url' => '#' ),
	array( 'date' => '24-09-26', 'plan' => 'Basic Plus Annual', 'price' => '$351.00', 'url' => '#' ),
);
?>

<div class="dashboard">

	<?php get_template_part( 'template-parts/dashboard-sidebar' ); ?>

	<main class="dashboard__main">
		<section class="profile">

			<!-- Tabs -->
			<?php get_template_part( 'template-parts/profile/profile-tabs', null, array( 'active' => 'invoice' ) ); ?>

			<!-- Invoice table -->
			<div class="invoice">
				<table class="invoice__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Plan', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Price', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Invoice', 'hello-elementor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $invoices as $row ) : ?>
							<tr>
								<td data-label="<?php esc_attr_e( 'Date', 'hello-elementor' ); ?>"><?php echo esc_html( $row['date'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Plan', 'hello-elementor' ); ?>"><?php echo esc_html( $row['plan'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Price', 'hello-elementor' ); ?>"><?php echo esc_html( $row['price'] ); ?></td>
								<td class="invoice__col-action" data-label="<?php esc_attr_e( 'Invoice', 'hello-elementor' ); ?>">
									<a href="<?php echo esc_url( $row['url'] ); ?>" class="btn btn-primary invoice__view">
										<?php esc_html_e( 'View', 'hello-elementor' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		</section>
	</main>

</div>
