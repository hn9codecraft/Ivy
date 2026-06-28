<?php
/**
 * Template Name: Package Detail
 *
 * @package HelloElementor
 */

get_header();

/**
 * Demo package rows — replace with real subscription/package data when available.
 */
$packages = array(
	array( 'name' => 'STEM Academy Premium', 'course' => 'Advanced Mathematics', 'duration' => '6 Months', 'sessions' => '48', 'valid' => '2026-12-05', 'paid' => true,  'status' => 'active' ),
	array( 'name' => 'STEM Academy Premium', 'course' => 'Advanced Mathematics', 'duration' => '6 Months', 'sessions' => '48', 'valid' => '2026-12-05', 'paid' => false, 'status' => 'active' ),
	array( 'name' => 'STEM Academy Premium', 'course' => 'Advanced Mathematics', 'duration' => '6 Months', 'sessions' => '48', 'valid' => '2026-12-05', 'paid' => true,  'status' => 'active' ),
	array( 'name' => 'STEM Academy Premium', 'course' => 'Advanced Mathematics', 'duration' => '6 Months', 'sessions' => '48', 'valid' => '2026-12-05', 'paid' => true,  'status' => 'active' ),
	array( 'name' => 'STEM Academy Premium', 'course' => 'Advanced Mathematics', 'duration' => '6 Months', 'sessions' => '48', 'valid' => '2026-12-05', 'paid' => true,  'status' => 'active' ),
	array( 'name' => 'STEM Academy Premium', 'course' => 'Advanced Mathematics', 'duration' => '6 Months', 'sessions' => '48', 'valid' => '2026-12-05', 'paid' => true,  'status' => 'active' ),
);
?>

<div class="dashboard">

	<?php get_template_part( 'template-parts/dashboard-sidebar' ); ?>

	<main class="dashboard__main">
		<section class="profile">

			<!-- Tabs -->
			<?php get_template_part( 'template-parts/profile/profile-tabs', null, array( 'active' => 'package' ) ); ?>

			<!-- Package detail table -->
			<div class="package">
				<table class="package__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Package Name', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Course Name', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Duration', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Total Session', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Valid Until', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Paid / unpaid', 'hello-elementor' ); ?></th>
							<th><?php esc_html_e( 'Status', 'hello-elementor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $packages as $row ) : ?>
							<tr>
								<td data-label="<?php esc_attr_e( 'Package Name', 'hello-elementor' ); ?>"><?php echo esc_html( $row['name'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Course Name', 'hello-elementor' ); ?>"><?php echo esc_html( $row['course'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Duration', 'hello-elementor' ); ?>"><?php echo esc_html( $row['duration'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Total Session', 'hello-elementor' ); ?>"><?php echo esc_html( $row['sessions'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Valid Until', 'hello-elementor' ); ?>"><?php echo esc_html( $row['valid'] ); ?></td>
								<td data-label="<?php esc_attr_e( 'Paid / unpaid', 'hello-elementor' ); ?>">
									<span class="<?php echo $row['paid'] ? 'package__paid' : 'package__unpaid'; ?>">
										<?php echo $row['paid'] ? esc_html__( 'Paid', 'hello-elementor' ) : esc_html__( 'Unpaid', 'hello-elementor' ); ?>
									</span>
								</td>
								<td data-label="<?php esc_attr_e( 'Status', 'hello-elementor' ); ?>">
									<span class="badge badge-success"><?php echo esc_html( $row['status'] ); ?></span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		</section>
	</main>

</div>
