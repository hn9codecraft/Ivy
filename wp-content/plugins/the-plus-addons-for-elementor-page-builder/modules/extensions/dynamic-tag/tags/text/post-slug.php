<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Slug
 *
 * Provides a dynamic tag for Elementor to output the current post slug.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Slug extends Tag {

	/**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-slug';
	}

	/**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Slug', 'tpebl' );
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
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
	protected function register_controls(): void {}

	/**
     * Render the dynamic post slug on frontend.
     *
     * Gets the current post ID and prints its slug.
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

		$slug = $post->post_name; // The post slug

		echo esc_html( $slug );
	}
}
