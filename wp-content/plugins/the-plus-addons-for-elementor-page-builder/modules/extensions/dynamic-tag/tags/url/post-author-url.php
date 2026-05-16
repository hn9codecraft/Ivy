<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Author URL
 *
 * Provides a dynamic tag for Elementor to output the current post author URL.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Author_URL extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-author-url';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Author URL', 'tpebl' );
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
     * Defines the category type (URL) for this dynamic tag.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_categories(): array {
		return [
			Module::URL_CATEGORY,
		];
	}

    /**
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
	protected function register_controls(): void {}

    /**
     * Render the dynamic post author URL on frontend.
     *
     * Gets the current post ID and prints its author URL.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$author_id = get_post_field( 'post_author', $post_id );

		if ( ! $author_id ) {
			return;
		}

		$author_url = get_author_posts_url( $author_id );

		if ( $author_url ) {
			echo esc_url( $author_url );
		}
	}
}
