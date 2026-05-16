<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Featured Image Data
 *
 * Provides a dynamic tag for Elementor to output the current post featured image data.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Featured_Image_Data extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-featured-image-data';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Featured Image Data', 'tpebl' );
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
			'data_type',
			[
				'label'   => esc_html__( 'Data Type', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'id'          => esc_html__( 'ID', 'tpebl' ),
					'alt'         => esc_html__( 'Alt Text', 'tpebl' ),
					'title'       => esc_html__( 'Title', 'tpebl' ),
					'caption'     => esc_html__( 'Caption', 'tpebl' ),
					'description' => esc_html__( 'Description', 'tpebl' ),
				],
				'default' => 'alt',
			]
		);
	}

    /**
     * Render the dynamic post featured image data on frontend.
     *
     * Gets the current post ID and prints its featured image data.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$settings = $this->get_settings();
		$data_type = $settings['data_type'] ?? 'alt';

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$image_id = get_post_thumbnail_id( $post_id );
		if ( ! $image_id ) {
			return;
		}

		// Return based on selected option
		switch ( $data_type ) {

			case 'id':
				echo esc_html( $image_id );
				break;

			case 'alt':
				echo esc_html( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
				break;

			case 'title':
				echo esc_html( get_the_title( $image_id ) );
				break;

			case 'caption':
				$caption = wp_get_attachment_caption( $image_id );
				echo esc_html( $caption );
				break;

			case 'description':
				$attachment = get_post( $image_id );
				echo esc_html( $attachment ? $attachment->post_content : '' );
				break;
		}
	}
}
