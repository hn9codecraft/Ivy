<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plus Addons Dynamic Tag - Post Tag Post Count
 *
 * Provides a dynamic tag for Elementor to output the current post tag post count.
 *
 * @since 6.4.8
 */
class ThePlus_Dynamic_Tag_Post_Tag_Post_Count extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.8
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-tag-post-count';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.8
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Tag Post Count', 'tpebl' );
	}

    /**
     * Registers the group under which this tag will appear.
     *
     * @since 6.4.8
     * @return array
     */
	public function get_group(): array {
		return [ 'plus-opt-post' ];
	}

    /**
     * Defines the category type (Text) for this dynamic tag.
     *
     * @since 6.4.8
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
     * @since 6.4.8
     * @return bool
     */
	public function is_settings_required() {
		return true;
	}

    /**
     * Register controls for this dynamic tag.
     *
     * @since 6.4.8
     * @return void
     */
	protected function register_controls(): void {}

    /**
     * Render the dynamic post tag post count on frontend.
     *
     * Gets the current post ID and prints its tag post count.
     *
     * @since 6.4.8
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();

		// Elementor editor fallback
		if ( ! $post_id && ! empty( $_REQUEST['post_id'] ) ) {
			$post_id = absint( $_REQUEST['post_id'] );
		}

		if ( ! $post_id ) {
			return;
		}

		$terms = get_the_terms( $post_id, 'post_tag' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$tag = $terms[0];

		if ( ! isset( $tag->count ) ) {
			return;
		}

		echo esc_html( $tag->count );
	}
}
