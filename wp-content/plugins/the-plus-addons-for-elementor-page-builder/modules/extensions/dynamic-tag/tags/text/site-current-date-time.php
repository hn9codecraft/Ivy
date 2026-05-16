<?php

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plus Addons Dynamic Tag - Site Current Date Time
 *
 * Provides a dynamic tag for Elementor to output the current site date and time.
 *
 * @since 6.4.7
 */
class ThePlus_Dynamic_Tag_Site_Current_Date_Time extends Tag {

    /**
     * Unique dynamic tag name used internally by Elementor.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_name(): string {
		return 'plus-tag-current-date-time';
	}

    /**
     * Label shown in Elementor Dynamic Tags list.
     *
     * @since 6.4.7
     * @return string
     */
	public function get_title(): string {
		return esc_html__( 'Current Date Time', 'tpebl' );
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
			'format',
			[
				'label'   => esc_html__( 'Format', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__( 'Default', 'tpebl' ),

					// Date Formats
					'F j, Y'     => date_i18n( 'F j, Y' ),
					'Y-m-d'      => date_i18n( 'Y-m-d' ),
					'm/d/Y'      => date_i18n( 'm/d/Y' ),
					'd-m-Y'      => date_i18n( 'd-m-Y' ),
					'D, M j, Y'  => date_i18n( 'D, M j, Y' ),
					'l, F j, Y'  => date_i18n( 'l, F j, Y' ),
					'Y/m/d'      => date_i18n( 'Y/m/d' ),

					// Time Formats
					'g:i a'      => date_i18n( 'g:i a' ),
					'g:i A'      => date_i18n( 'g:i A' ),
					'H:i'        => date_i18n( 'H:i' ),
					'h:i:s A'    => date_i18n( 'h:i:s A' ),
					'H:i:s'      => date_i18n( 'H:i:s' ),
					'g:i:s a'    => date_i18n( 'g:i:s a' ),

					// Combined
					'F j, Y g:i a'  => date_i18n( 'F j, Y g:i a' ),
					'Y-m-d H:i'     => date_i18n( 'Y-m-d H:i' ),
					'd/m/Y H:i'     => date_i18n( 'd/m/Y H:i' ),
					'M j, Y @ H:i'  => date_i18n( 'M j, Y @ H:i' ),
					'l, F j, Y g:i A' => date_i18n( 'l, F j, Y g:i A' ),

					'custom' => esc_html__( 'Custom', 'tpebl' ),
				],
			]
		);
		$this->add_control(
			'custom_format',
			[
				'label'     => esc_html__( 'Custom Format', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => 'F j, Y g:i a',
				'condition' => [
					'format' => 'custom',
				],
				'description' => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i><a href="%s" target="_blank">%s</a></i></p>',
						esc_url( 'https://wordpress.org/support/article/formatting-date-and-time/' ),
						esc_html__( 'Documentation on date and time formatting', 'tpebl' )
					),
				),
			]
		);
	}

    /**
     * Render the dynamic site current date time on frontend.
     *
     * Gets the current site current date and time.
     *
     * @since 6.4.7
     * @return void
     */
	public function render(): void {

		$settings = $this->get_settings();

		if ( 'default' === $settings['format'] ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		} elseif ( 'custom' === $settings['format'] ) {
			$format = $settings['custom_format'];

		} else {
			$format = $settings['format'];
		}

		echo wp_kses_post( date_i18n( $format ) );
	}
}
