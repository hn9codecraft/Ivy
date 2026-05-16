<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Terms
 *
 * Provides a dynamic tag for Elementor to output the current post terms.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Terms extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-terms';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Terms', 'tpebl' );
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
			'taxonomy',
			[
				'label'   => esc_html__( 'Taxonomy', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_taxonomies(),
				'default' => 'category',
			]
		);
		$this->add_control(
			'separator',
			[
				'label'   => esc_html__( 'Separator', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => ', ',
				'ai'      => 'false',
			]
		);
	}

	/**
	 * Retrieve available taxonomies for selection.
	 *
	 * @since 6.4.7
	 * @return array
	 */
	private function get_taxonomies() {

		$taxonomies = get_taxonomies(
			[
				'public' => true,
			],
			'objects'
		);

		$options = [];
		foreach ( $taxonomies as $tax ) {
			$options[ $tax->name ] = $tax->label;
		}

		return $options;
	}

    /**
     * Render the dynamic post terms on frontend.
     *
     * Gets the current post ID and prints its terms.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$taxonomy  = $this->get_settings( 'taxonomy' );
		$separator = $this->get_settings( 'separator' );

		if ( empty( $taxonomy ) ) {
			return;
		}

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$term_names = wp_list_pluck( $terms, 'name' );
		$output     = implode( $separator, $term_names );

		echo esc_html( $output );
	}
}
