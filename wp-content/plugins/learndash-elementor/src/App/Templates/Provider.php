<?php
/**
 * Templates provider class file.
 *
 * @since 1.0.11
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Templates;

use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;
use LearnDash\Elementor\Templates\Base as Template;

/**
 * Templates provider class.
 *
 * @since 1.0.11
 */
class Provider extends ServiceProvider {
	/**
	 * Registers service providers.
	 *
	 * @since 1.0.11
	 */
	public function register() {
		$this->container->singleton( Template::class, Frontend::class );

		$this->container->when( Controllers\Admin::class )
			->needs( Template::class )
			->give( Admin::class );

		$this->hooks();
	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.11
	 *
	 * @throws ContainerException If unable to resolve a service.
	 *
	 * @return void
	 */
	public function hooks() {
		// Admin.

		add_action( 'admin_footer', $this->container->callback( Controllers\Admin::class, 'check_import_templates' ) );

		// Frontend.

		add_filter( 'learndash_template', $this->container->callback( Controllers\Frontend::class, 'filter_learndash_template' ), 100, 5 );
	}
}
