<?php

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Author Avatar
 *
 * Provides a dynamic tag for Elementor to output the current post author avatar.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Author_Avatar extends Data_Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-author-avatar';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Author Avatar', 'tpebl' );
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
     * Defines the category type (Image, Media) for this dynamic tag.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_categories(): array {
		return [
			Module::IMAGE_CATEGORY,
            Module::MEDIA_CATEGORY,
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
			'fallback_image',
			[
				'label' => esc_html__( 'Fallback Image', 'tpebl' ),
				'type'  => \Elementor\Controls_Manager::MEDIA,
				'description' => wp_kses_post(
                    sprintf(
                        '<p class="tp-controller-label-text"><i>%s</i></p>',
                        esc_html__( 'Displayed when the author has no avatar.', 'tpebl' )
                    )
                ),
			]
		);
	}
	/**
	 * Get the dynamic post author avatar value.
	 *
	 * @since 6.4.7
	 * @param array $options Optional. Additional options for getting the value.
	 * @return array Associative array with 'id' and 'url' of the avatar image.
	 */
	public function get_value( array $options = [] ) {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return false;
		}

		$author_id = get_post_field( 'post_author', $post_id );
		if ( ! $author_id ) {
			return false;
		}

		$avatar_url = get_avatar_url( $author_id );

		$settings       = $this->get_settings();
		$fallback_image = $settings['fallback_image']['url'] ?? '';

		if ( empty( $avatar_url ) ) {
			if ( ! empty( $fallback_image ) ) {
				return [
					'id'  => $settings['fallback_image']['id'],
					'url' => $fallback_image,
				];
			}

			return [
				'id'  => 0,
				'url' => Utils::get_placeholder_image_src(),
			];
		}

		return [
			'id'  => 0,
			'url' => $avatar_url,
		];
	}

	/**
     * Render the dynamic post author avatar on frontend.
     *
     * Gets the current post ID and prints its author avatar.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {
		$value = $this->get_value();

		if ( empty( $value['url'] ) ) {
			return;
		}

		echo esc_url( $value['url'] );
	}
}
