<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Terms URL
 *
 * Provides a dynamic tag for Elementor to output the current post terms URL.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Term_URL extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-term-url';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Term URL', 'tpebl' );
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
			'select_taxonomy',
			[
				'label'   => esc_html__( 'Select Taxonomy', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_public_taxonomies(),
				'default' => 'category',
			]
		);
	}

	/**
	 * Get public taxonomies for the select control.
	 *
	 * @since 6.4.7
	 * @return array
	 */
	private function get_public_taxonomies(): array {
		$tax_list = [];
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );

		foreach ( $taxonomies as $tax ) {
			$tax_list[ $tax->name ] = $tax->label;
		}
		return $tax_list;
	}

    /**
     * Render the dynamic post terms URL on frontend.
     *
     * Gets the current post ID and prints its terms URL.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$post_id  = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$taxonomy = $this->get_settings( 'select_taxonomy' );
		if ( ! $taxonomy ) {
			return;
		}

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$term = $terms[0]; // first term

		$term_url = get_term_link( $term );

		if ( ! is_wp_error( $term_url ) ) {
			echo esc_url( $term_url );
		}
	}
}
