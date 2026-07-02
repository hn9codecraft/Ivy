<?php
/**
 * Dashboard Sidebar Component
 *
 * Reusable navigation sidebar for all dashboard pages.
 * Include with: get_template_part( 'template-parts/dashboard-sidebar' );
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();

/**
 * Navigation tree.
 *
 * A node is either:
 *   - a link  : array( 'label', 'url' )
 *   - a group : array( 'label', 'icon', 'open', 'children' => array( ...nodes ) )
 *
 * Groups can nest arbitrarily. The top-level "Stem Academy" group holds the
 * 1:1 and Group sub-groups, each of which holds its own links.
 */
$nav_tree = array(
	array(
		'label'    => __( 'Stem Academy', 'hello-elementor' ),
		'icon'     => 'cap',
		'variant'  => 'academy',
		'caret'    => 'caret-down',
		'open'     => true,
		'children' => array(
			array(
				'label'    => __( '1:1', 'hello-elementor' ),
				'icon'     => 'one',
				'open'     => true,
				'children' => array(
					array( 'label' => __( 'Tutee Progress', 'hello-elementor' ), 'url' => '#' ),
					array( 'label' => __( 'Schedule and Attendace', 'hello-elementor' ), 'url' => '#' ),
				),
			),
			array(
				'label'    => __( 'Group', 'hello-elementor' ),
				'icon'     => 'group',
				'open'     => true,
				'children' => array(
					array( 'label' => __( 'Tutee Progress', 'hello-elementor' ), 'url' => '#' ),
					array( 'label' => __( 'Schedule and Attendace', 'hello-elementor' ), 'url' => '#' ),
				),
			),
		),
	),
	array(
		'label'    => __( 'Consultancy', 'hello-elementor' ),
		'icon'     => 'consultancy',
		'open'     => false,
		'children' => array(),
	),
	array(
		'label'    => __( 'Test Prep', 'hello-elementor' ),
		'icon'     => 'testprep',
		'open'     => false,
		'children' => array(),
	),
);

/** Account dropdown links (bottom card) — share slugs/icons with the profile tabs. */
$account_links = function_exists( 'ivy_profile_nav_items' ) ? ivy_profile_nav_items() : array();

/** Inline SVG icon helper. */
if ( ! function_exists( 'iq_sidebar_icon' ) ) {
	function iq_sidebar_icon( $name ) {
		$icons = array(
			'cap'         => '<path d="M22 10L12 5 2 10l10 5 10-5z"></path><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"></path>',
			'one'         => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M8 9h2v6"></path><path d="M14 9h2"></path>',
			'group'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
			'calendar'    => '<rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path>',
			'consultancy' => '<rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>',
			'testprep'    => '<rect x="4" y="3" width="16" height="18" rx="2"></rect><path d="M9 7h6M9 11h6M9 15h4"></path>',
			'profile'     => '<circle cx="12" cy="8" r="4"></circle><path d="M4 21v-1a8 8 0 0 1 16 0v1"></path>',
			'payment'     => '<rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path>',
			'invoice'     => '<rect x="4" y="3" width="16" height="18" rx="2"></rect><path d="M8 8h8M8 12h8M8 16h5"></path>',
			'package'     => '<path d="M12 2l9 5v10l-9 5-9-5V7l9-5z"></path><path d="M3 7l9 5 9-5M12 12v10"></path>',
			'caret-right' => '<path d="M9 6l6 6-6 6"></path>',
			'caret-down'  => '<path d="M6 9l6 6 6-6"></path>',
		);
		$path = isset( $icons[ $name ] ) ? $icons[ $name ] : '';
		return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
	}
}

/** Recursive nav node renderer (groups + links, any depth). */
if ( ! function_exists( 'iq_sidebar_render_node' ) ) {
	function iq_sidebar_render_node( $node ) {
		// Leaf link.
		if ( empty( $node['children'] ) && isset( $node['url'] ) ) {
			$active = ! empty( $node['active'] ) ? ' is-active' : '';
			printf(
				'<a href="%1$s" class="sidebar__link%2$s"%3$s>%4$s</a>',
				esc_url( $node['url'] ),
				esc_attr( $active ),
				$active ? ' aria-current="page"' : '',
				esc_html( $node['label'] )
			);
			return;
		}

		$has_children = ! empty( $node['children'] );
		$is_open      = ( $has_children && ! empty( $node['open'] ) );
		$open_class   = $is_open ? ' is-open' : '';
		$variant      = ! empty( $node['variant'] ) ? ' sidebar__group--' . sanitize_html_class( $node['variant'] ) : '';
		$caret        = ! empty( $node['caret'] ) ? $node['caret'] : ( $has_children ? 'caret-right' : 'caret-down' );
		$caret_mod    = ( 'caret-down' === $caret ) ? ' sidebar__group-caret--down' : '';
		?>
		<div class="sidebar__group<?php echo esc_attr( $variant . $open_class ); ?>">
			<a href="#" type="button" class="sidebar__group-toggle"<?php echo $has_children ? ' aria-expanded="' . ( $is_open ? 'true' : 'false' ) . '"' : ''; ?>>
				<?php if ( ! empty( $node['icon'] ) ) : ?>
					<span class="sidebar__group-icon"><?php echo iq_sidebar_icon( $node['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
				<span class="sidebar__group-label"><?php echo esc_html( $node['label'] ); ?></span>
				<span class="sidebar__group-caret<?php echo esc_attr( $caret_mod ); ?>"><?php echo iq_sidebar_icon( $caret ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>

			<?php if ( $has_children ) : ?>
				<div class="sidebar__submenu">
					<div class="sidebar__submenu-inner">
						<?php foreach ( $node['children'] as $child ) {
							iq_sidebar_render_node( $child );
						} ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
?>

<a href="#" type="button" class="sidebar-mobile-toggle" id="sidebarMobileToggle" aria-label="<?php esc_attr_e( 'Open menu', 'hello-elementor' ); ?>" aria-controls="dashboardSidebar" aria-expanded="false">
	<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"></path></svg>
</a> 

<div class="sidebar__backdrop" id="sidebarBackdrop"></div>

<aside class="deshboard-sidebar" id="dashboardSidebar" aria-label="<?php esc_attr_e( 'Dashboard navigation', 'hello-elementor' ); ?>">
	<!-- Navigation -->
	<nav class="sidebar__nav">
		<?php foreach ( $nav_tree as $node ) {
			iq_sidebar_render_node( $node );
		} ?>
	</nav>

	<!-- Footer: account dropdown + toggle -->
	<div class="sidebar__footer">
		<div class="sidebar__account" id="sidebarAccount">
			<?php foreach ( $account_links as $link ) :
				$active = is_page( $link['slug'] ) ? ' is-active' : '';
				?>
				<a href="<?php echo esc_url( ivy_profile_url( $link['slug'] ) ); ?>" class="sidebar__account-link<?php echo esc_attr( $active ); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
					<?php echo iq_sidebar_icon( $link['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $link['label'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<a href="#" type="button" class="sidebar__toggle" id="sidebarAccountToggle" aria-label="<?php esc_attr_e( 'Account menu', 'hello-elementor' ); ?>" aria-controls="sidebarAccount" aria-expanded="false">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2L3 6v6c0 5 3.8 8.5 9 10 5.2-1.5 9-5 9-10V6l-9-4zm0 5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zm0 11c-2 0-3.8-1-4.8-2.5.8-1.3 2.6-2 4.8-2s4 .7 4.8 2C15.8 17 14 18 12 18z"></path></svg>
		</a>
	</div>

</aside>

<script>
( function () {
	var sidebar = document.getElementById( 'dashboardSidebar' );
	if ( ! sidebar ) { return; }

	/* Collapsible nav groups (works at any nesting depth) */
	sidebar.querySelectorAll( '.sidebar__group-toggle' ).forEach( function ( btn ) {
		var group = btn.closest( '.sidebar__group' );
		if ( ! group || ! group.querySelector( '.sidebar__submenu' ) ) { return; }
		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var open = group.classList.toggle( 'is-open' );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	} );

	/* Account dropdown */
	var accToggle = document.getElementById( 'sidebarAccountToggle' );
	var account   = document.getElementById( 'sidebarAccount' );
	if ( accToggle && account ) {
		accToggle.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var open = account.classList.toggle( 'is-open' );
			accToggle.classList.toggle( 'is-active', open );
			accToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	}

	/* Mobile open/close */
	var mToggle  = document.getElementById( 'sidebarMobileToggle' );
	var backdrop = document.getElementById( 'sidebarBackdrop' );
	function closeMobile() {
		sidebar.classList.remove( 'is-open' );
		if ( backdrop ) { backdrop.classList.remove( 'is-open' ); }
		if ( mToggle ) { mToggle.setAttribute( 'aria-expanded', 'false' ); }
	}
	if ( mToggle ) {
		mToggle.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var open = sidebar.classList.toggle( 'is-open' );
			if ( backdrop ) { backdrop.classList.toggle( 'is-open', open ); }
			mToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	}
	if ( backdrop ) { backdrop.addEventListener( 'click', closeMobile ); }
}() );
</script>
