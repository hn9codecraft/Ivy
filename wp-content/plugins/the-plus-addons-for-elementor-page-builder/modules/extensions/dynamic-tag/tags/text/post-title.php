<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Title
 *
 * Provides a dynamic tag for Elementor to output the current post title.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Title extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
    public function get_name(): string {
        return 'plus-tag-post-title';
    }

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
    public function get_title(): string {
        return esc_html__('Post Title', 'tpebl');
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
    protected function register_controls(): void {}

    /**
     * Render the dynamic post title on frontend.
     *
     * Gets the current post ID and prints its title.
     *
     * @since 6.4.7
     * @return void
     */
    public function render(): void {
        $value = '';

        $post_id = get_the_ID();

        if ( $post_id ) {
            $value = get_the_title( $post_id );
        }

        echo wp_kses_post( $value );
    }
}
