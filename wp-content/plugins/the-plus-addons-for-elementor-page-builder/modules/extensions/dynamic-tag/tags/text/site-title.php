<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Site Title
 *
 * Provides a dynamic tag for Elementor to output the current site title.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Site_Title extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-site-title';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Site Title', 'tpebl' );
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
     * Register controls for this dynamic tag.
     *
     * @since 6.4.7
     * @return void
     */
	protected function register_controls(): void {}

    /**
     * Render the dynamic site title on frontend.
     *
     * Gets the current site title.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$title = get_bloginfo( 'name' );

		if ( empty( $title ) ) {
			return;
		}

		echo esc_html( $title );
	}
}
