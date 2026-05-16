<?php
/**
 * LearnDash Elementor template-related integration class.
 *
 * @since 1.0.5
 *
 * @package LearnDash\Elementor
 */

namespace LearnDash\Elementor\Templates\Controllers;

use LearnDash\Core\Utilities\Cast;
use LearnDash\Elementor\Templates\Base as Template;
use LearnDash\Elementor\Utilities\Post;

/**
 * Frontend templates handler class.
 *
 * @since 1.0.11
 */
class Frontend {
	/**
	 * Template instance.
	 *
	 * @since 1.0.11
	 *
	 * @var Template
	 */
	private Template $template;

	/**
	 * Constructor.
	 *
	 * @since 1.0.11
	 *
	 * @param Template $template Template instance.
	 */
	public function __construct( Template $template ) {
		$this->template = $template;
	}

	/**
	 * Filter LearnDash templates.
	 *
	 * @since 1.0.11
	 *
	 * @param string                     $filepath         Template file path.
	 * @param string                     $name             Template name.
	 * @param array<string, string>|null $args             Template data.
	 * @param bool|null                  $echo             Whether to echo the template output or not.
	 * @param bool                       $return_file_path Whether to return file or path or not.
	 *
	 * @return string
	 */
	public function filter_learndash_template( $filepath, $name, $args, $echo, $return_file_path ) {
		if ( ! Post::is_elementor() ) {
			return $filepath;
		}

		switch ( $name ) {
			case 'course':
				$filepath = $this->template->get_template_file( 'themes/ld30/course/index' );
				break;

			case 'lesson':
				$filepath = $this->template->get_template_file( 'themes/ld30/lesson/index' );
				break;

			case 'topic':
				$filepath = $this->template->get_template_file( 'themes/ld30/topic/index' );
				break;

			case 'quiz':
				$filepath = $this->template->get_template_file( 'themes/ld30/quiz/index' );
				break;

			case 'course/listing.php':
				// We fallback to the default LearnDash template if the template is not called via LD Elementor addon.
				if (
					(
						! isset( $args['source'] )
						|| $args['source'] !== 'elementor'
					) && (
						! isset( $args['context'] )
						|| ! strpos( $args['context'], 'shortcode' )
					)
				) {
					$filepath = '';
				}
				break;
		}

		return Cast::to_string( $filepath );
	}
}
