<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Status
 *
 * Provides a dynamic tag for Elementor to output the current post status.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Status extends Tag {

	/**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-status';
	}

	/**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Status', 'tpebl' );
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
     * Defines the category type (Text, Post Meta) for this dynamic tag.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_categories(): array {
		return [
			Module::TEXT_CATEGORY,
			Module::POST_META_CATEGORY,
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
     * Render the dynamic post status on frontend.
     *
     * Gets the current post ID and prints its status.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$status = get_post_status( $post_id );

		if ( empty( $status ) ) {
			return;
		}

		$label = ucfirst( $status );

		echo esc_html( $label );
	}
}
