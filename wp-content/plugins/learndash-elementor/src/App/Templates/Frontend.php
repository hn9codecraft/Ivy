<?php
/**
 * LearnDash Elementor frontend template class file.
 *
 * @since 1.0.11
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Templates;

use LearnDash\Elementor\Templates\Base as Base_Template;

/**
 * Frontend template class.
 *
 * @since 1.0.11
 */
class Frontend extends Base_Template {
	/**
	 * Gets current template folder.
	 *
	 * @since 1.0.11
	 *
	 * @return string
	 */
	protected function current_template_folder(): string {
		return 'src/views';
	}

	/**
	 * Sets whether to allow template folder lookup.
	 *
	 * @since 1.0.11
	 *
	 * @return bool
	 */
	protected function allow_template_folder_lookup(): bool {
		return true;
	}
}
