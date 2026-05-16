<?php
/**
 * Admin Template retrieval class.
 *
 * @since 1.0.11
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Templates;

use LearnDash\Elementor\StellarWP\Templates\Template as StellarWP_Template;

/**
 * Admin Template retrieval class.
 *
 * @since 1.0.11
 */
class Admin_Template extends StellarWP_Template {
	/**
	 * Base template for where to look for template.
	 *
	 * @since 1.0.11
	 *
	 * @var string[]
	 */
	protected array $template_base_path = [ LEARNDASH_ELEMENTOR_PLUGIN_DIR . 'src/admin-views' ];

	/**
	 * Allow changing if class will extract data from the local context.
	 *
	 * @since 1.0.11
	 *
	 * @var boolean
	 */
	protected bool $template_context_extract = true;
}
