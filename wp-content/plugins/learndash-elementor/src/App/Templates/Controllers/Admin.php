<?php
/**
 * LearnDash Elementor template-related integration class.
 *
 * @since 1.0.11
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Templates\Controllers;

use LearnDash\Elementor\Templates\Base as Template;

/**
 * Template-related integration class.
 *
 * @since 1.0.11
 */
class Admin {
	/**
	 * Template instance.
	 *
	 * @since 1.0.11
	 *
	 * @var Template
	 */
	private Template $template; // @phpstan-ignore-line -- We set the template instance in constructor even though it's still not used yet to match with frontend template controller.

	/**
	 * Constructor.
	 *
	 * @since 1.0.11
	 *
	 * @param Template $template Template.
	 */
	public function __construct( Template $template ) {
		$this->template = $template;
	}

	/**
	 * Check whether we need to import default templates or not.
	 *
	 * @since 1.0.11
	 *
	 * @return void
	 */
	public function check_import_templates(): void {
		$changes                  = false;
		$learndash_elementor_data = get_option( 'learndash_elementor_data', [] );

		if (
			! is_array( $learndash_elementor_data )
			|| empty( $learndash_elementor_data )
		) {
			$changes                  = true;
			$learndash_elementor_data = [];
		}

		if ( ! isset( $learndash_elementor_data['version'] ) ) {
			$changes                             = true;
			$learndash_elementor_data['version'] = LEARNDASH_ELEMENTOR_VERSION;
		}

		if ( ! isset( $learndash_elementor_data['templates_imported'] ) ) {
			$changes            = true;
			$templates_imported = $this->import_templates();

			if ( $templates_imported ) {
				$learndash_elementor_data['templates_imported'] = $templates_imported;
			}
		}

		if ( true === $changes ) {
			update_option( 'learndash_elementor_data', $learndash_elementor_data );
		}
	}

	/**
	 * Import default Course, Lesson, Topic and Quiz templates.
	 *
	 * Called from the admin_footer action hook.
	 *
	 * @since 1.0.11
	 *
	 * @return bool True if import is successful, false otherwise.
	 */
	private function import_templates(): bool {
		$exports_dir = LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/data/templates';

		if (
			! file_exists( $exports_dir )
			|| ! function_exists( 'learndash_scandir_recursive' )
		) {
			return false;
		}

		$import_files = learndash_scandir_recursive( $exports_dir );

		if ( empty( $import_files ) ) {
			return false;
		}

		$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );

		if ( ! $source instanceof \Elementor\TemplateLibrary\Source_Local ) {
			return false;
		}

		foreach ( $import_files as $import_file ) {
			if (
				'.' !== $import_file[0]
				&& '.json' === substr( $import_file, -1 * strlen( '.json' ), strlen( '.json' ) )
			) {
				$source->import_template( basename( $import_file ), $import_file );
			}
		}

		return true;
	}
}
