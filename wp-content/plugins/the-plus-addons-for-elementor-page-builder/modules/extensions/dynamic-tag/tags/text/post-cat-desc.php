<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Category Description
 *
 * Provides a dynamic tag for Elementor to output the current post category description.
 *
 * @since 6.4.8
 */
class ThePlus_Dynamic_Tag_Post_Category_Description extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.8
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-category-desc';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.8
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Category Description', 'tpebl' );
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
     * Render the dynamic post category description on frontend.
     *
     * Gets the current post ID and prints its category description.
     *
     * @since 6.4.8
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$terms = get_the_terms( $post_id, 'category' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$category = $terms[0];

		if ( empty( $category->description ) ) {
			return;
		}

		echo wp_kses_post( $category->description );
	}
}
