<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Content
 *
 * Provides a dynamic tag for Elementor to output the current post content.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Content extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-content';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Content', 'tpebl' );
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
			'limit_words',
			[
				'label'       => __( 'Limit Words', 'tpebl' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'default'     => '',
				'description' => wp_kses_post(
                    sprintf(
                        '<p class="tp-controller-label-text"><i>%s</i></p>',
                        esc_html__( 'Enter number of words. Leave empty to show full content.', 'tpebl' )
                    )
                ),
			]
		);
	}

    /**
     * Render the dynamic post content on frontend.
     *
     * Gets the current post ID and prints its content.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$settings = $this->get_settings();
		$limit    = $settings['limit_words'] ?? '';

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		$content = apply_filters( 'the_content', $post->post_content );

		if ( ! empty( $limit ) && is_numeric( $limit ) ) {
			$content = wp_trim_words( $content, $limit, '...' );
		}

		echo wp_kses_post( $content );
	}
}
