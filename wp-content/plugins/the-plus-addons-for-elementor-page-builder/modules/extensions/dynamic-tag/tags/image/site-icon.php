<?php

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Plus Addons Dynamic Tag - Site Icon
 *
 * Provides a dynamic tag for Elementor to output the icon.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Site_Icon extends Data_Tag  {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name() {
		return 'plus-tag-site-icon';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title() {
		return esc_html__( 'Site Icon', 'tpebl' );
	}

    /**
     * Registers the group under which this tag will appear.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_group() {
		return 'plus-opt-site';
	}

    /**
     * Defines the category type (Image, Media) for this dynamic tag.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_categories() {
		return [
			Module::IMAGE_CATEGORY,
			Module::MEDIA_CATEGORY,
		];
	}

    /**
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
	protected function register_controls() {
		$this->add_control(
			'fallback_image',
			[
				'label' => __( 'Fallback Image', 'tpebl' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);
	}

	/**
	 * Get the value of the dynamic tag.
	 *
	 * @since 6.4.7
	 * @return array
	 */
	public function get_value( array $options = [] ) {

		$icon_id = get_option( 'site_icon' );
		$url     = $icon_id ? wp_get_attachment_image_url( $icon_id, 'full' ) : '';

		if ( ! empty( $url ) ) {
			return [
				'id'  => $icon_id,
				'url' => $url,
			];
		}

		$fallback = $this->get_settings( 'fallback_image' );
		if ( ! empty( $fallback['url'] ) ) {
			return [
				'id'  => $fallback['id'] ?? 0,
				'url' => $fallback['url'],
			];
		}

		return [
			'id'  => 0,
			'url' => Utils::get_placeholder_image_src(),
		];
	}

    /**
     * Render the dynamic site icon on frontend.
     *
     * Gets the current site icon.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {
		$value = $this->get_value();

		if ( ! empty( $value['url'] ) ) {
			echo esc_url( $value['url'] );
		}
	}
}
