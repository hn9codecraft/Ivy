<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plus Addons Dynamic Tag - Site URL
 *
 * Provides a dynamic tag for Elementor to output the site URL.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Site_URL extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-site-url';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Site URL', 'tpebl' );
	}

    /**
     * Registers the group under which this tag will appear.
     *
     * @since 6.4.7
     * @return array
     */
	public function get_group(): array {
		return [ 'plus-opt-site' ];
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
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
	protected function register_controls(): void {}

    /**
     * Render the dynamic site URL on frontend.
     *
     * Gets the current site URL.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$url = home_url();

		if ( empty( $url ) ) {
			return;
		}

		echo esc_url( $url );
	}
}
