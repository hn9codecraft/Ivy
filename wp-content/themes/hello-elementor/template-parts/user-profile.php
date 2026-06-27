<?php
/**
 * Template Name: User Profile
 *
 * @package HelloElementor
 */

get_header();

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;

$first_name = $user_id ? get_user_meta( $user_id, 'first_name', true ) : '';
$last_name  = $user_id ? get_user_meta( $user_id, 'last_name', true ) : '';
$phone      = $user_id ? get_user_meta( $user_id, 'billing_phone', true ) : '';
$country    = $user_id ? get_user_meta( $user_id, 'billing_country', true ) : '';
$email      = $current_user->user_email;

$display_name = trim( "$first_name $last_name" );
if ( '' === $display_name ) {
	$display_name = $current_user->display_name ? $current_user->display_name : __( 'Your Name', 'hello-elementor' );
}

$avatar = $user_id ? get_avatar_url( $user_id, array( 'size' => 360 ) ) : '';
?>

<div class="dashboard">

	<?php get_template_part( 'template-parts/dashboard-sidebar' ); ?>

	<main class="dashboard__main">
<section class="profile">

	<!-- Tabs -->
	<nav class="profile__tabs" aria-label="<?php esc_attr_e( 'Account sections', 'hello-elementor' ); ?>">
		<a href="#profile" class="profile__tab profile__tab--active"><?php esc_html_e( 'Profile', 'hello-elementor' ); ?></a>
		<a href="#payment" class="profile__tab"><?php esc_html_e( 'Payment method', 'hello-elementor' ); ?></a>
		<a href="#invoice" class="profile__tab"><?php esc_html_e( 'Invoice', 'hello-elementor' ); ?></a>
		<a href="#package" class="profile__tab"><?php esc_html_e( 'Package detail', 'hello-elementor' ); ?></a>
	</nav>

	<form class="profile__form" method="post" action="">
		<div class="profile__layout">

			<!-- Left: profile card -->
			<aside class="profile__card">
				<div class="profile__avatar-wrap">
					<img
						class="profile__avatar"
						src="<?php echo esc_url( $avatar ); ?>"
						alt="<?php echo esc_attr( $display_name ); ?>"
						id="profileAvatar"
					>
					<label class="profile__avatar-edit" for="profileAvatarInput" title="<?php esc_attr_e( 'Change photo', 'hello-elementor' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M12 20h9"></path>
							<path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
						</svg>
						<span class="sr-only"><?php esc_html_e( 'Change photo', 'hello-elementor' ); ?></span>
					</label>
					<input type="file" id="profileAvatarInput" name="profile_avatar" class="profile__avatar-input" accept="image/*">
				</div>

				<h3 class="profile__name"><?php echo esc_html( $display_name ); ?></h3>
				<p class="profile__email"><?php echo esc_html( $email ); ?></p>
			</aside>

			<!-- Right: form fields -->
			<div class="profile__fields">
				<div class="profile__grid">

					<div class="profile__field">
						<label class="profile__label" for="firstName"><?php esc_html_e( 'First name', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="text" id="firstName" name="first_name"
							value="<?php echo esc_attr( $first_name ); ?>"
							placeholder="<?php esc_attr_e( 'Your First Name', 'hello-elementor' ); ?>">
					</div>

					<div class="profile__field">
						<label class="profile__label" for="lastName"><?php esc_html_e( 'Last name', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="text" id="lastName" name="last_name"
							value="<?php echo esc_attr( $last_name ); ?>"
							placeholder="<?php esc_attr_e( 'Your Last name', 'hello-elementor' ); ?>">
					</div>

					<div class="profile__field">
						<label class="profile__label" for="phone"><?php esc_html_e( 'Phone number', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="tel" id="phone" name="billing_phone"
							value="<?php echo esc_attr( $phone ); ?>"
							placeholder="<?php esc_attr_e( 'phone number', 'hello-elementor' ); ?>">
					</div>

					<div class="profile__field">
						<label class="profile__label" for="email"><?php esc_html_e( 'Email', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="email" id="email" name="email"
							value="<?php echo esc_attr( $email ); ?>"
							placeholder="<?php esc_attr_e( 'Your Email Id', 'hello-elementor' ); ?>">
					</div>

					<div class="profile__field">
						<label class="profile__label" for="password"><?php esc_html_e( 'Password', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="password" id="password" name="password"
							placeholder="<?php esc_attr_e( 'Your Password', 'hello-elementor' ); ?>" autocomplete="new-password">
					</div>

					<div class="profile__field">
						<label class="profile__label" for="confirmPassword"><?php esc_html_e( 'Confirm Password', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="password" id="confirmPassword" name="confirm_password"
							placeholder="<?php esc_attr_e( 'your Confirm Password', 'hello-elementor' ); ?>" autocomplete="new-password">
					</div>

					<div class="profile__field profile__field--full">
						<label class="profile__label" for="country"><?php esc_html_e( 'Country', 'hello-elementor' ); ?></label>
						<input class="profile__input" type="text" id="country" name="billing_country"
							value="<?php echo esc_attr( $country ); ?>"
							placeholder="<?php esc_attr_e( 'Your Country', 'hello-elementor' ); ?>">
					</div>

				</div>

				<div class="profile__actions">
					<?php wp_nonce_field( 'save_user_profile', 'profile_nonce' ); ?>
					<button type="submit" class="btn btn-primary profile__save" name="save_profile">
						<?php esc_html_e( 'Save', 'hello-elementor' ); ?>
					</button>
				</div>
			</div>

		</div>
	</form>

</section>
	</main>

</div>

<script>
( function () {
	var input  = document.getElementById( 'profileAvatarInput' );
	var avatar = document.getElementById( 'profileAvatar' );
	if ( ! input || ! avatar ) { return; }
	input.addEventListener( 'change', function ( e ) {
		var file = e.target.files && e.target.files[0];
		if ( file ) { avatar.src = URL.createObjectURL( file ); }
	} );
}() );
</script>

<?php
get_footer();
