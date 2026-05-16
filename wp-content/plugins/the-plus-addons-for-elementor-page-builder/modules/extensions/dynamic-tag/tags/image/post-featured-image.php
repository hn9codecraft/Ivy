<?php

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Featured Image
 *
 * Provides a dynamic tag for Elementor to output the current post featured image.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Featured_Image extends Data_Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
    public function get_name(): string {
        return 'plus-tag-post-featured-image';
    }

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
    public function get_title(): string {
        return esc_html__('Post Featured Image', 'tpebl');
    }

    /**
     * Registers the group under which this tag will appear.
     *
     * @since 6.4.7
     * @return array
     */
    public function get_group(): array {
        return ['plus-opt-post'];
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
                'label' => esc_html__('Fallback Image', 'tpebl'),
                'type'  => \Elementor\Controls_Manager::MEDIA,
                'description' => wp_kses_post(
                    sprintf(
                        '<p class="tp-controller-label-text"><i>%s</i></p>',
                        esc_html__( 'Displayed when the post has no featured image.', 'tpebl' )
                    )
                ),
            ]
        );
    }

    /**
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
    protected function register_advanced_section(){}

    /**
     * Get the dynamic post featured image value.
     *
     * @since 6.4.7
     * @param array $options Optional. Additional options for getting the value.
     * @return array Associative array with 'id' and 'url' of the featured image.
     */
	public function get_value( array $options = [] ) {
		
		$post_id = get_the_ID();

		// Fallback for Dynamic CSS context: CSS is generated during wp_head (before the loop starts),
		// so get_the_ID() returns 0. get_queried_object_id() works even outside the loop.
		if ( ! $post_id ) {
			$queried_id = get_queried_object_id();
			if ( $queried_id && get_post( $queried_id ) ) {
				$post_id = $queried_id;
			}
		}

		// Elementor editor / AJAX context fallback (e.g. dynamic CSS preview request).
		if ( ! $post_id && ! empty( $_REQUEST['post_id'] ) ) {
			$post_id = absint( $_REQUEST['post_id'] );
		}

		if ( ! $post_id ) {
			return false;
		}

		$thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( $thumbnail_id ) {

            $image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );

            return [
                'id'  => $thumbnail_id,
                'url' => $image_url,
            ];
        }

		$fallback = $this->get_settings( 'fallback_image' );

        if ( ! empty( $fallback['id'] ) && ! empty( $fallback['url'] ) ) {
            return [
                'id'  => $fallback['id'],
                'url' => $fallback['url'],
            ];
        }

        return false;
	}

    /**
     * Render the dynamic post featured image on frontend.
     *
     * Gets the current post ID and prints its featured image.
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
