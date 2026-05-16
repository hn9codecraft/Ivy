<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since   6.2.7
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Plugin;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/*
 * Equal Height Theplus.
 */
if ( ! class_exists( 'Tpae_Equal_Height' ) ) {

	/**
	 * Define Tpae_Equal_Height class
	 *
	 * @since 6.2.7
	 */
	class Tpae_Equal_Height {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 6.2.7
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns a singleton instance of the class.
		 *
		 * This method ensures that only one instance of the class is created (singleton pattern).
		 * If an instance doesn't exist, it creates one using the provided shortcodes.
		 *
		 * @since 6.2.7
		 *
		 * @param array $shortcodes Optional. An array of shortcodes to initialize the instance with.
		 * @return self The single instance of the class.
		 */
		public static function get_instance( $shortcodes = array() ) {

			if ( null === self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;
		}

		/**
		 * Document Link For Need help.
		 *
		 * @var tp_doc of the class.
		 */
		public $tp_doc = L_THEPLUS_TPDOC;

		/**
		 * Get the widget name.
		 *
		 * @since 6.2.7
		 */
		public function get_name() {
			return 'plus-equal-height';
		}

		/**
		 * Initalize integration hooks
		 *
		 * @since 6.2.7
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'tp_equalheight_controls' ), 10, 2 );
			add_action( 'elementor/element/column/_section_responsive/after_section_end', array( $this, 'tp_equalheight_controls' ), 10, 2 );
			add_action( 'elementor/element/common/section_custom_css_pro/after_section_end', array( $this, 'tp_equalheight_controls' ), 10, 2 );

			if ( \Elementor\Plugin::instance()->experiments->is_feature_active( 'container' ) ) {
				add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'tp_equalheight_controls' ), 10, 2 );
			}

			// add_action( 'elementor/frontend/section/before_render', [ $this, 'tp_equalheight_before_render'], 10, 1 );!
			// add_action( 'elementor/frontend/widget/before_render', [ $this, 'tp_equalheight_before_render' ], 10, 1 );!
			add_action( 'elementor/frontend/before_render', array( $this, 'tp_equalheight_before_render' ), 10, 1 );

			add_action( 'wp_enqueue_scripts', array( $this, 'tp_register_scripts' ) );

			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'tp_register_scripts' ), 10 );
			add_action( 'elementor/editor/after_enqueue_scripts', function() { wp_enqueue_script( 'plus-equal-height' ); } , 10 );
		}

		/**
		 * Register controls for the Equal Height feature
		 *
		 * @since 6.2.7
		 */
		public function tp_equalheight_controls( $element ) {
			$element->start_controls_section(
				'plus_equal_height_section',
				array(
					'label' => esc_html__( 'Equal Height', 'tpebl' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);
			$element->add_control(
				'seh_switch',
				array(
					'label'        => esc_html__( 'Equal Height', 'tpebl' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'On', 'tpebl' ),
					'label_off'    => esc_html__( 'Off', 'tpebl' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'description'  => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
							esc_html__( 'If your content boxes have different heights, Turn this on to make elements the same height. You can use a CSS class or the container level to keep your layout even.', 'tpebl' ),
							esc_url( $this->tp_doc . 'equal-column-height-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
							esc_html__( 'Learn More', 'tpebl' ),
						)
					),
				)
			);
			$element->add_control(
				'seh_mode',
				array(
					'label'     => esc_html__( 'Mode Based on', 'tpebl' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'bodl' => esc_html__( 'Div Level', 'tpebl' ),
						'bouc' => esc_html__( 'Unique Class', 'tpebl' ),
					),
					'default'   => 'bodl',
					'condition' => array(
						'seh_switch' => 'yes',
					),
				)
			);
			$element->add_control(
                'seh_mode_bodl_label',
                array(
                    'type'  => Controls_Manager::RAW_HTML,
                    'raw'   => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
							esc_html__( 'Use this option to match the height of elements based on their nesting level. It helps keep layouts consistent when elements are inside different containers.', 'tpebl' ),
							esc_url( $this->tp_doc . 'equal-column-height-elementor/#Method-1-Using-Div-Level?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
							esc_html__( 'Learn More', 'tpebl' ),
						)
					),
                    'label_block' => true,
					'condition' => array(
						'seh_switch' => 'yes',
						'seh_mode'   => 'bodl',
					),
                )
            );
			$element->add_control(
                'seh_mode_bouc_label',
                array(
                    'type'  => Controls_Manager::RAW_HTML,
                    'raw'   => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text"><i> %s <a class="tp-docs-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a></i></p>',
							esc_html__( 'Use this option to match height of elements by the CSS class name of the target element or its parent container.', 'tpebl' ),
							esc_url( $this->tp_doc . 'equal-column-height-elementor/#Method-2-Using-Unique-CSS-Class?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=widget' ),
							esc_html__( 'Learn More', 'tpebl' ),
						)
					),
                    'label_block' => true,
					'condition' => array(
						'seh_switch' => 'yes',
						'seh_mode'   => 'bouc',
					),
                )
            );
			$element->add_control(
				'seh_opt',
				array(
					'label'     => esc_html__( 'Select Nested Level', 'tpebl' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'widgets' => esc_html__( 'Widgets', 'tpebl' ),
						'1'       => esc_html__( 'Nested Level 1', 'tpebl' ),
						'2'       => esc_html__( 'Nested Level 2', 'tpebl' ),
						'3'       => esc_html__( 'Nested Level 3', 'tpebl' ),
						'4'       => esc_html__( 'Nested Level 4', 'tpebl' ),
						'5'       => esc_html__( 'Nested Level 5', 'tpebl' ),
						'6'       => esc_html__( 'Nested Level 6', 'tpebl' ),
						'7'       => esc_html__( 'Nested Level 7', 'tpebl' ),
						'8'       => esc_html__( 'Nested Level 8', 'tpebl' ),
						'9'       => esc_html__( 'Nested Level 9', 'tpebl' ),
						'10'      => esc_html__( 'Nested Level 10', 'tpebl' ),
					),
					'default'   => 'widgets',
					'description'  => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text"><i>%s</i></p>',
							esc_html__( 'Count the number of parent containers between the target element and the container with the "elementor-widget-container" class is the nested level.', 'tpebl' ),
						)
					),
					'condition' => array(
						'seh_switch' => 'yes',
						'seh_mode'   => 'bodl',
					),
				)
			);
			$element->add_control(
				'seh_eql_opt',
				array(
					'label'     => esc_html__( 'Select Sub Nested Level', 'tpebl' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'1'  => esc_html__( 'Level 1', 'tpebl' ),
						'2'  => esc_html__( 'Level 2', 'tpebl' ),
						'3'  => esc_html__( 'Level 3', 'tpebl' ),
						'4'  => esc_html__( 'Level 4', 'tpebl' ),
						'5'  => esc_html__( 'Level 5', 'tpebl' ),
						'6'  => esc_html__( 'Level 6', 'tpebl' ),
						'7'  => esc_html__( 'Level 7', 'tpebl' ),
						'8'  => esc_html__( 'Level 8', 'tpebl' ),
						'9'  => esc_html__( 'Level 9', 'tpebl' ),
						'10' => esc_html__( 'Level 10', 'tpebl' ),
					),
					'default'   => '1',
					'description'  => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text"><i>%s</i></p>',
							esc_html__( 'The number of div`s between the target element and its immediate parent level is the sub nested level.', 'tpebl' ),
						)
					),
					'condition' => array(
						'seh_switch' => 'yes',
						'seh_mode'   => 'bodl',
					),
				)
			);
			$element->add_control(
				'seh_opt_custom',
				array(
					'label'       => esc_html__( 'Enter Unique Class', 'tpebl' ),
					'type'        => Controls_Manager::TEXT,
					'ai'          => false,
					'placeholder' => esc_html__( '.class-name', 'tpebl' ),
					'condition'   => array(
						'seh_switch' => 'yes',
						'seh_mode'   => 'bouc',
					),
				)
			);
			$element->end_controls_section();
		}

		/**
		 * Register necessary scripts feature.
		 *
		 * @since 6.4.6
		 */
		public function tp_register_scripts() {
			wp_register_script( 'plus-equal-height', L_THEPLUS_URL . 'modules/extensions/equal-height/plus-equal-height.min.js', array( 'jquery' ), L_THEPLUS_VERSION, true );
		}

		/**
		 * Apply Equal Height settings before rendering the widget.
		 *
		 * @since 6.2.7
		 */
		public function tp_equalheight_before_render( $element ) {
			$settings = $element->get_settings();

			$seh_switch = ! empty( $settings['seh_switch'] ) ? $settings['seh_switch'] : '';

			if ( 'yes' === $seh_switch ) {

				wp_enqueue_script( 'plus-equal-height' );

				$opt = '';

				$seh_mode = ! empty( $settings['seh_mode'] ) ? $settings['seh_mode'] : '';

				if ( 'bodl' === $seh_mode ) {

					$seh_opt     = ! empty( $settings['seh_opt'] ) ? $settings['seh_opt'] : '';
					$seh_eql_opt = ! empty( $settings['seh_eql_opt'] ) ? $settings['seh_eql_opt'] : '';

					$widget = '-widget';
					$con    = '-container';

					if ( 'widgets' === $seh_opt ) {
						$opt = '.elementor-widget';
					}
					if ( 'widgets_l1' === $seh_opt && '1' === $seh_eql_opt ) {
						$opt = '.elementor' . $widget . $con . ' > div:nth-of-type(1)';
					}

					$nested_opt = '';
					$eql_opt    = '';

					$nested_opt = array( '2', '3', '4', '5', '6', '7', '8', '9', '10' );

					// $eql_opt= array('2', '3', '4', '5',"6","7","8","9","10");!

					if ( ( in_array( $seh_opt, $nested_opt ) ) && ! empty( $seh_eql_opt ) ) {
						$seh_opt_add = '';
						for ( $i = 2;$i <= $seh_opt;$i++ ) {
							$seh_opt_add .= ' > div ';
						}
						$opt = '.elementor' . $widget . $con . ' ' . $seh_opt_add . ' > div:nth-of-type(' . $seh_eql_opt . ')';
					}
				}

				$seh_opt_custom = ! empty( $settings['seh_opt_custom'] ) ? $settings['seh_opt_custom'] : '';

				if ( 'bouc' === $seh_mode ) {
					$opt = esc_attr( $seh_opt_custom );
				}

				if ( $opt ) {
					$element->add_render_attribute(
						'_wrapper',
						array(
							'class'                        => 'theplus-equal-height',
							'data-tp-equal-height-loadded' => $opt,
						)
					);
				}
			}
		}
	}
}

Tpae_Equal_Height::get_instance();
