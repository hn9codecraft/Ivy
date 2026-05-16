<?php
/**
 * LearnDash Elementor template-related integration class.
 *
 * @since 1.0.5
 * @deprecated 1.0.11
 *
 * @package LearnDash\Elementor\Deprecated
 */

namespace LearnDash\Elementor\Deprecated;

use LearnDash\Core\App as LearnDash_App;
use LearnDash\Elementor\Templates\Controllers;

/**
 * Template-related integration class.
 *
 * @since 1.0.5
 * @deprecated 1.0.11
 */
class Templates {
	/**
	 * Check whether we need to import default templates or not.
	 *
	 * @since 1.0.5
	 * @deprecated 1.0.11
	 *
	 * @return void
	 */
	public function check_import_templates(): void {
		_deprecated_function(
			__METHOD__,
			'1.0.11',
			'\LearnDash\Elementor\Templates\Controllers\Admin::check_import_templates'
		);

		LearnDash_App::get( Controllers\Admin::class )->check_import_templates();
	}

	/**
	 * Import default Course, Lesson, Topic and Quiz templates.
	 *
	 * Called from the admin_footer action hook.
	 *
	 * @since 1.0.5
	 * @since 1.0.6 Return bool value.
	 * @deprecated 1.0.11
	 *
	 * @return bool True if import is successful, false otherwise.
	 */
	private function import_templates(): bool {
		_deprecated_function(
			__METHOD__,
			'1.0.11',
			'\LearnDash\Elementor\Templates\Controllers\Admin::import_templates'
		);

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

	/**
	 * Filter LearnDash templates.
	 *
	 * @since 1.0.5
	 * @deprecated 1.0.11
	 *
	 * @param string     $filepath         Template file path.
	 * @param string     $name             Template name.
	 * @param array|null $args             Template data.
	 * @param bool|null  $echo             Whether to echo the template output or not.
	 * @param bool       $return_file_path Whether to return file or path or not.
	 *
	 * @return string
	 */
	public function filter_learndash_template( $filepath, $name, $args, $echo, $return_file_path ): string {
		_deprecated_function(
			__METHOD__,
			'1.0.11',
			'\LearnDash\Elementor\Templates\Controllers\Frontend::filter_learndash_template'
		);

		return LearnDash_App::get( Controllers\Frontend::class )->filter_learndash_template();
	}
}
