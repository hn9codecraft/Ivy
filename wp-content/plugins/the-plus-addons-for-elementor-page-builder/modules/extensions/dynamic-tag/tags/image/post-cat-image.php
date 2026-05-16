<?php

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plus Addons Dynamic Tag - Post Category Image
 *
 * Provides a dynamic tag for Elementor to output the current post category image.
 *
 * @since 6.4.8
 */
class ThePlus_Dynamic_Tag_Post_Category_Image extends Data_Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.8
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-category-image';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.8
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Category Image', 'tpebl' );
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
     * Defines the category type (Image, Media) for this dynamic tag.
     *
     * @since 6.4.8
     * @return array
     */
	public function get_categories(): array {
		return [
			Module::IMAGE_CATEGORY,
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
	 * Set dynamic tag value type as image.
	 *
	 * Required for Elementor to properly render
	 * the attachment ID inside Image widgets.
	 *
	 * @since 6.4.8
	 * @return string
	 */
	public function get_value_type() {
		return 'image';
	}

	/**
	 * Get the dynamic post category image value.
	 *
	 * @since 6.4.8
	 * @param array $options Optional. Additional options for getting the value.
	 * @return array Associative array with 'id' and 'url' of the category image.
	 */
	public function get_value( array $options = [] ) {

		$post_id = get_the_ID();

		// Elementor editor fallback
		if ( ! $post_id && isset( $_REQUEST['post_id'] ) ) {
			$post_id = absint( $_REQUEST['post_id'] );
		}

		if ( ! $post_id ) {
			return null;
		}

		$terms = get_the_terms( $post_id, 'category' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		$term = $terms[0];

		// YOUR ACTUAL SAVED META KEY
		$image_id = get_term_meta( $term->term_id, 'tp_taxonomy_image_id', true );

		if ( empty( $image_id ) ) {
			return null;
		}

		return [
			'id'  => (int) $image_id,
			'url' => wp_get_attachment_url( $image_id ),
		];
	}
}
