<?php
/**
 * Base template class file.
 *
 * @since 1.0.11
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Templates;

use LearnDash\Core\Utilities\Cast;
use LearnDash\Elementor\StellarWP\Templates\Template as StellarWP_Template;

/**
 * Base template class.
 *
 * @since 1.0.11
 */
abstract class Base extends StellarWP_Template {
	/**
	 * Defines the base path for the templates.
	 *
	 * @since 1.0.11
	 *
	 * @var array<string>
	 */
	protected array $template_base_path = [ LEARNDASH_ELEMENTOR_PLUGIN_DIR ];

	/**
	 * Constructor.
	 *
	 * @since 1.0.11
	 */
	public function __construct() {
		$this->set_template_folder(
			$this->current_template_folder()
		);

		$this->set_template_folder_lookup(
			$this->allow_template_folder_lookup()
		);
	}

	/**
	 * Gets current template folder.
	 *
	 * @since 1.0.11
	 *
	 * @return string
	 */
	abstract protected function current_template_folder(): string;

	/**
	 * Gets boolean value whether to allow template folder lookup or not.
	 *
	 * @since 1.0.11
	 *
	 * @return bool
	 */
	abstract protected function allow_template_folder_lookup(): bool;
}
