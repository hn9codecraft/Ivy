<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Excerpt
 *
 * Provides a dynamic tag for Elementor to output the current post excerpt.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Excerpt extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-excerpt';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Excerpt', 'tpebl' );
	}

    /**
     * Registers the group under which this tag will appear.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_group(): array {
		return [ 'plus-opt-post' ];
	}

    /**
     * Defines the category type (Text) for this dynamic tag.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_categories(): array {
		return [
			Module::TEXT_CATEGORY,
		];
	}

    /**
     * Indicates that settings panel should be shown (even if empty).
     *
     * @since 6.4.7
     * @return bool
     */
    public function is_settings_required() {
		return true;
	}

    /**
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
	protected function register_controls(): void {
		$this->add_control(
			'excerpt_length',
			[
				'label'   => esc_html__( 'Excerpt Length (words)', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 30,
				'min'     => 5,
				'max'     => 200,
			]
		);
	}

    /**
     * Render the dynamic post excerpt on frontend.
     *
     * Gets the current post ID and prints its excerpt.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		$excerpt = $post->post_excerpt;

		if ( empty( $excerpt ) ) {
			$content = strip_shortcodes( $post->post_content );
			$content = wp_strip_all_tags( $content );

			$length = $this->get_settings( 'excerpt_length' ) ?: 30;

			$excerpt = wp_trim_words( $content, $length );
		}

		echo wp_kses_post( $excerpt );
	}
}
