<?php
/**
 * The file that defines the widget plugin for the free version.
 *
 * @link       https://posimyth.com/
 * @since      v6.5.0
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define Tpae_Global_Controllers_Main class for the free version.
 * 
 * @since v6.5.0
 */
if ( ! class_exists( 'Tpae_Global_Controllers_Main' ) ) {

    /**
     * Define L_Tpaef_Extensions_Main class for the free version
     * 
     * @since v6.5.0
     */
    class Tpae_Global_Controllers_Main {

        /**
         * Call __construct.
         * 
         * @since v6.5.0
         */
        public function __construct() {

            $theplus_options = get_option( 'theplus_options' );

            $extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : [];
            $get_widget = ! empty( $theplus_options['check_elements'] ) ? $theplus_options['check_elements'] : [];

            add_action( 'elementor/kit/register_tabs', array( $this, 'register_setting_tabs' ) );

            if ( in_array( 'plus_global_box_shadow', $extras_elements, true ) ) {
                add_action(
                    'elementor/controls/register',
                    function ( $controls_manager ) {
                        $global_path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-box-shadow-controller.php';
                        if ( ! class_exists( 'ThePlusAddons\\Elementor\\BoxShadow\\TP_Box_Shadow_Global' ) && file_exists( $global_path ) ) {
                            include_once $global_path;
                        }

                        $group_path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-group-box-shadow.php';
                        if ( file_exists( $group_path ) ) {
                            include_once $group_path;
                        }

                        if ( class_exists( 'ThePlusAddons\\Elementor\\BoxShadow\\TP_Group_Control_Box_Shadow' ) ) {
                            $controls_manager->add_group_control(
                                'box-shadow',
                                new ThePlusAddons\Elementor\BoxShadow\TP_Group_Control_Box_Shadow()
                            );
                        }
                    }
                );

                add_action(
                    'elementor/frontend/before_render',
                    function ( $element ) {
                        if ( ! class_exists( 'ThePlusAddons\\Elementor\\BoxShadow\\TP_Box_Shadow_Global' ) ) {
                            return;
                        }

                        $settings   = $element->get_settings();
                        $element_id = $element->get_id();

                        foreach ( $settings as $key => $value ) {
                            if ( empty( $value ) || substr( $key, -20 ) !== '_tp_bs_global_preset' ) {
                                continue;
                            }

                            $css = ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global::get_preset_css( $value );
                            if ( ! $css ) {
                                continue;
                            }

                            // $key example: "box_shadow_tp_bs_global_preset"
                            // Strip the 20-char suffix "_tp_bs_global_preset" to get the group name prefix.
                            $base_name    = substr( $key, 0, -20 );
                            $selector_key = $base_name . '_' . $base_name . '_tp_bs_selector_store';

                            $selector_tmpl = ! empty( $settings[ $selector_key ] ) ? $settings[ $selector_key ] : '{{WRAPPER}}';

                            $wrapper_class = '.elementor-element-' . $element_id;
                            $selector      = str_replace( '{{WRAPPER}}', $wrapper_class, $selector_tmpl );

                            echo '<style>' . wp_strip_all_tags( $selector ) . '{box-shadow:' . wp_strip_all_tags( $css ) . ' !important;}</style>';
                        }
                    },
                    10
                );
            }

            if ( in_array( 'plus_global_gradient_color', $extras_elements, true ) ) {
                add_action(
                    'elementor/controls/register',
                    function ( $controls_manager ) {
                        $global_path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-gradient-color-controller.php';
                        if ( ! class_exists( 'ThePlusAddons\\Elementor\\Gradient\\TP_Gradient_Global' ) && file_exists( $global_path ) ) {
                            include_once $global_path;
                        }
                        $group_path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-group-background.php';
                        if ( file_exists( $group_path ) ) {
                            include_once $group_path;
                        }
                        if ( class_exists( 'ThePlusAddons\\Elementor\\Gradient\\TP_Group_Control_Background' ) ) {
                            $controls_manager->add_group_control(
                                'background',
                                new ThePlusAddons\Elementor\Gradient\TP_Group_Control_Background()
                            );
                        }
                    }
                );

                add_action(
                    'elementor/frontend/before_render',
                    function ( $element ) {
                        if ( ! class_exists( 'ThePlusAddons\\Elementor\\Gradient\\TP_Gradient_Global' ) ) {
                            return;
                        }
                        $settings   = $element->get_settings();
                        $element_id = $element->get_id();

                        foreach ( $settings as $key => $value ) {
                            if ( empty( $value ) || substr( $key, -20 ) !== '_tp_gg_global_preset' ) {
                                continue;
                            }

                            $css = ThePlusAddons\Elementor\Gradient\TP_Gradient_Global::get_preset_css( $value );
                            if ( ! $css ) {
                                continue;
                            }

                            // $key example: "bg_tp_gg_global_preset"
                            // Strip the 20-char suffix "_tp_gg_global_preset" to get the group name prefix.
                            $base_name    = substr( $key, 0, -20 );
                            $selector_key = $base_name . '_' . $base_name . '_tp_gg_selector_store';

                            $selector_tmpl = ! empty( $settings[ $selector_key ] ) ? $settings[ $selector_key ] : '{{WRAPPER}}';

                            $wrapper_class = '.elementor-element-' . $element_id;
                            $selector      = str_replace( '{{WRAPPER}}', $wrapper_class, $selector_tmpl );

                            echo '<style>' . wp_strip_all_tags( $selector ) . '{background-image:' . wp_strip_all_tags( $css ) . ' !important;}</style>';
                        }
                    },
                    10
                );
            }

            if ( in_array( 'plus_global_dimensions', $extras_elements, true ) ) {
                add_action(
                    'elementor/controls/register',
                    function ( $controls_manager ) {
                        $global_path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-global-dimensions-controller.php';
                        if ( ! class_exists( 'ThePlusAddons\\Elementor\\Dimensions\\TP_Dimensions_Global' ) && file_exists( $global_path ) ) {
                            include_once $global_path;
                        }

                        $has_global_dimensions = false;
                        if ( class_exists( 'ThePlusAddons\\Elementor\\Dimensions\\TP_Dimensions_Global' ) ) {
                            $global_dimensions = \ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global::get_global_dimensions_list();
                            if ( ! empty( $global_dimensions ) ) {
                                $has_global_dimensions = true;
                            }
                        }

                        if ( $has_global_dimensions ) {
                            $control_path = L_THEPLUS_PATH . 'modules/extensions/global-control/class-tp-control-dimensions.php';
                            if ( file_exists( $control_path ) ) {
                                include_once $control_path;
                            }

                            if ( class_exists( 'ThePlusAddons\\Elementor\\Dimensions\\TP_Control_Dimensions' ) ) {
                                $controls_manager->unregister( 'dimensions' );
                                $controls_manager->register( new ThePlusAddons\Elementor\Dimensions\TP_Control_Dimensions(), 'dimensions' );
                            }
                        }
                    },
                    100
                );

                // Inject global-preset SELECT into Elementor's native Image Box widget
                // (its image_border_radius uses SLIDER, not DIMENSIONS, so the
                // TP_Control_Dimensions override cannot reach it).
                add_action(
                    'elementor/element/image-box/section_style_image/before_section_end',
                    function ( $element ) {
                        if ( ! class_exists( 'ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global' ) ) {
                            return;
                        }
                        $presets = \ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global::get_preset_options();
                        // get_preset_options() always includes the empty '' placeholder, so
                        // skip if there are no real presets yet.
                        if ( count( $presets ) <= 1 ) {
                            return;
                        }
                        $element->add_control(
                            'image_border_radius_tp_global_preset',
                            array(
                                'label'       => esc_html__( 'Global Border Radius', 'tpebl' ),
                                'type'        => \Elementor\Controls_Manager::SELECT,
                                'options'     => $presets,
                                'default'     => '',
                                'description' => esc_html__( 'Select a Global Dimensions preset to override the Border Radius slider above.', 'tpebl' ),
                            ),
                            array(
                                'position' => array(
                                    'type' => 'control',
                                    'at'   => 'after',
                                    'of'   => 'image_border_radius',
                                ),
                            )
                        );
                    },
                    10,
                    1
                );

                // Output inline CSS for the Image Box global border-radius preset.
                add_action(
                    'elementor/frontend/before_render',
                    function ( $element ) {
                        if ( 'image-box' !== $element->get_name() ) {
                            return;
                        }
                        if ( ! class_exists( 'ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global' ) ) {
                            return;
                        }
                        $settings  = $element->get_settings_for_display();
                        $preset_id = ! empty( $settings['image_border_radius_tp_global_preset'] )
                            ? $settings['image_border_radius_tp_global_preset']
                            : '';
                        if ( empty( $preset_id ) ) {
                            return;
                        }
                        $css = \ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global::get_preset_css( $preset_id );
                        if ( empty( $css ) ) {
                            return;
                        }
                        $element_id = $element->get_id();
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo '<style>.elementor-element-' . esc_attr( $element_id ) . ' .elementor-image-box-img img{border-radius:' . esc_attr( $css ) . ' !important;}</style>';
                    },
                    10
                );
            }
        }

        /**
         * Register setting tabs.
         * 
         * @since v6.5.0.0
         */
        public function register_setting_tabs( $tabs_manager ) {
            $theplus_options = get_option( 'theplus_options' );
			$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

			$available_tabs = array(
				'plus_global_box_shadow'     => array(
					'file'  => 'modules/extensions/global-control/class-tp-global-box-shadow-controller.php',
					'key'   => 'tp-box-shadow-global',
					'class' => 'ThePlusAddons\Elementor\BoxShadow\TP_Box_Shadow_Global',
				),
				'plus_global_gradient_color' => array(
					'file'  => 'modules/extensions/global-control/class-tp-global-gradient-color-controller.php',
					'key'   => 'tp-global-gradient-color',
					'class' => 'ThePlusAddons\Elementor\Gradient\TP_Gradient_Global',
				),
				'plus_global_dimensions' => array(
					'file'  => 'modules/extensions/global-control/class-tp-global-dimensions-controller.php',
					'key'   => 'tp-global-dimensions',
					'class' => 'ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global',
				),
				'plus_global_button' => array(
					'file'  => 'modules/extensions/global-control/class-tp-global-button-style-controller.php',
					'key'   => 'tp-global-button-styles',
					'class' => 'ThePlusAddons\Elementor\ButtonStyle\TP_Button_Style_Global',
				),
				'plus_global_scroll_animation' => array(
					'file'  => 'modules/extensions/global-control/class-tp-global-scroll-animation-controller.php',
					'key'   => 'tp-global-scroll-animation',
					'class' => 'ThePlusAddons\Elementor\ScrollAnimation\TP_Global_Scroll_Animation_Controller',
				),
			);

			$styles_tabs = array();

			foreach ( $available_tabs as $feature_key => $tab ) {
				if ( in_array( $feature_key, $extras_elements, true ) ) {
					$styles_tabs[] = $tab;
				}
			}

			foreach ( $styles_tabs as $tab ) {

                $path = L_THEPLUS_PATH . $tab['file'];

                if ( file_exists( $path ) ) {
                    include_once $path;
                }

                if ( class_exists( $tab['class'] ) ) {
                    $tabs_manager->register_tab( $tab['key'], $tab['class'] );
                }
			}
        }
    }
}

new Tpae_Global_Controllers_Main();
