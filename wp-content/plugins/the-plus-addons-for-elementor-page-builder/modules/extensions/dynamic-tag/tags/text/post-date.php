<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Post Date
 *
 * Provides a dynamic tag for Elementor to output the current post date.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Post_Date extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-post-date';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Post Date', 'tpebl' );
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
			'tp_date_type',
			[
				'label'   => esc_html__( 'Date Type', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'post_date'     => esc_html__( 'Publish Date', 'tpebl' ),
					'post_modified' => esc_html__( 'Modified Date', 'tpebl' ),
				],
				'default' => 'post_date',
			]
		);
		$this->add_control(
			'tp_format_type',
			[
				'label'   => esc_html__( 'Format', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Default (WP Format)', 'tpebl' ),
					'F j, Y'  => date_i18n( 'F j, Y' ),
					'Y-m-d'   => date_i18n( 'Y-m-d' ),
					'm/d/Y'   => date_i18n( 'm/d/Y' ),
					'd/m/Y'   => date_i18n( 'd/m/Y' ),
					'human'   => esc_html__( 'Human Readable', 'tpebl' ),
					'custom'  => esc_html__( 'Custom', 'tpebl' ),
				],
				'default' => 'default',
			]
		);
		$this->add_control(
			'tp_custom_format',
			[
				'label'     => esc_html__( 'Custom Format', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => 'F j, Y',
				'ai' => [
					'active' => false,
				],
				'description' => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i><a href="%s" target="_blank">%s</a></i></p>',
						esc_url( 'https://wordpress.org/support/article/formatting-date-and-time/' ),
						esc_html__( 'Documentation on date formatting', 'tpebl' )
					),
				),
				'condition' => [
					'tp_format_type' => 'custom',
				],
			]
		);
	}

    /**
     * Render the dynamic post date on frontend.
     *
     * Gets the current post ID and prints its date.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$settings = $this->get_settings();

		$date = $settings['tp_date_type'] === 'post_modified'
			? $post->post_modified
			: $post->post_date;

		$timestamp = strtotime( $date );

		if ( 'human' === $settings['tp_format_type'] ) {

			$value = human_time_diff( $timestamp, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'tpebl' );

		} elseif ( 'default' === $settings['tp_format_type'] ) {

			$value = date_i18n( get_option( 'date_format' ), $timestamp );

		} elseif ( 'custom' === $settings['tp_format_type'] ) {

			$format = $settings['tp_custom_format'];
			$value  = date_i18n( $format, $timestamp );

		} else {

			$value = date_i18n( $settings['tp_format_type'], $timestamp );
		}

		echo esc_html( $value );
	}
}
