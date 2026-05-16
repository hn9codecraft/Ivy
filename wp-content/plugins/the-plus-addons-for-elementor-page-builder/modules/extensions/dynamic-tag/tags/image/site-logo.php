<?php

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Plus Addons Dynamic Tag - Site Logo
 *
 * Provides a dynamic tag for Elementor to output the site logo.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Site_Logo extends Data_Tag  {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name() {
		return 'plus-tag-site-logo';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title() {
		return esc_html__( 'Site Logo', 'tpebl' );
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
			Module::IMAGE_CATEGORY 
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
				'label' => esc_html__( 'Fallback Image', 'tpebl' ),
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

		$logo_id = get_theme_mod( 'custom_logo' );

		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );

			if ( $logo_url ) {
				return [
					'id'  => $logo_id,
					'url' => $logo_url,
				];
			}
		}

		$fallback = $this->get_settings( 'fallback_image' );
		if ( ! empty( $fallback['url'] ) ) {
			return [
				'id'  => $fallback['id'] ?? 0,
				'url' => $fallback['url'],
			];
		}

		$default = \Elementor\Utils::get_placeholder_image_src();

		return [
			'id'  => 0,
			'url' => $default,
		];
	}

    /**
     * Render the dynamic site logo on frontend.
     *
     * Gets the current site logo.
     *
     * @since 6.4.7
     * @return void
     */
	public function render() {
		$value = $this->get_value();

		if ( ! empty( $value['url'] ) ) {
			echo esc_url( $value['url'] );
		}
	}
}
