<?php
/**
 * The file that defines the widget plugin for the free version.
 *
 * @link       https://posimyth.com/
 * @since      6.2.7
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define L_Tpae_Extensions_Main class for the free version.
 * 
 * @since 6.2.7
 */
if ( ! class_exists( 'L_Tpae_Extensions_Main' ) ) {

    /**
     * Define L_Tpaef_Extensions_Main class for the free version
     * 
     * @since 6.2.7
     */
    class L_Tpae_Extensions_Main {

        /**
         * Call __construct.
         * 
         * @since 6.2.7
         */
        public function __construct() {

            $theplus_options = get_option( 'theplus_options' );

            $extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : [];
            $get_widget = ! empty( $theplus_options['check_elements'] ) ? $theplus_options['check_elements'] : [];

            if (  in_array( 'plus_cross_cp', $extras_elements ) ) {
                include L_THEPLUS_PATH . 'modules/extensions/copy-paste/class-tpae-copy-paste.php';
            }

            if (  in_array( 'plus_equal_height', $extras_elements ) ) {
                include L_THEPLUS_PATH . 'modules/extensions/class-tpae-equal-height.php';
            }

            if (  in_array( 'plus_section_column_link', $extras_elements ) ) {
                include L_THEPLUS_PATH . 'modules/extensions/wrapper-link/class-tpae-wrapper-link.php';
            }

            if (  in_array( 'plus_adv_shadow', $extras_elements ) ) {
                include L_THEPLUS_PATH . 'modules/extensions/class-tpae-advanced-shadow.php';
            }

            if (  in_array( 'plus_glass_morphism', $extras_elements ) ) {
                include L_THEPLUS_PATH . 'modules/extensions/class-tpae-glass-morphism.php';
            }

            $required_keys = array(
                'plus_text_global_animation',
                'plus_image_global_animation',
                'plus_adv_scroll_interactions',
            );

            if ( ! empty( array_intersect( $required_keys, (array) $extras_elements ) ) ) {
                include_once L_THEPLUS_PATH . 'modules/extensions/animation/class-tp-gsap-main.php';
            }

            if ( in_array( 'plus_dynamic_tag', $extras_elements ) ) {
                include L_THEPLUS_PATH . 'modules/extensions/dynamic-tag/class-tpae-dynamic-tag.php';
			}

            include_once L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-controller-main.php';
        }
    }
}

new L_Tpae_Extensions_Main();
