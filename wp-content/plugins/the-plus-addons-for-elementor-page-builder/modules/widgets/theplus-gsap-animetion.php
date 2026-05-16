<?php
/**
 * The file that defines the widget plugin.
 *
 * @link       https://posimyth.com/
 * @since      1.0.0
 *
 * @package    ThePlus
 */

namespace TheplusAddons\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	$this->start_controls_section(
		'tpebl_image_gsap_section',
		array(
			'label' => esc_html__( 'Image Animation', 'tpebl' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		)
	);
		$this->add_control(
			'enable_image_animation',
			array(
				'label'        => esc_html__( 'Enable Animation', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'tpebl' ),
				'label_off'    => esc_html__( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Toggle to enable GSAP-powered image animations.', 'tpebl' ),
			)
		);
		$this->add_control(
			'image_animations',
			array(
				'label'     => esc_html__( 'Animation', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'tp_basic',
				'options'   => array(
					'tp_basic'  => esc_html__( 'Basic', 'tpebl' ),
					'tp_global' => esc_html__( 'Global', 'tpebl' ),
				),
				'condition' => array(
					'enable_image_animation' => 'yes',
				),
				'description' => esc_html__( 'Choose between basic animation presets or global image animations defined in Site Settings.', 'tpebl' ),
			)
		);

		$theplus_options = get_option( 'theplus_options' );
		$extras_elements = ! empty( $theplus_options['extras_elements'] ) ? $theplus_options['extras_elements'] : array();

		$image_global_enabled = in_array( 'plus_image_global_animation', $extras_elements );

		$global_animations = array();
		$global_options    = array();

		$global_options = array( '' => esc_html__( 'Select Animation', 'tpebl' ) ) + $global_options;


		if ( $image_global_enabled && class_exists( '\ThePlusAddons\Elementor\Image\TP_GSAP_Image_Global' ) ) {
			$global_animations = \ThePlusAddons\Elementor\Image\TP_GSAP_Image_Global::get_image_global_gsap_list();

			if ( ! empty( $global_animations ) ) {
				foreach ( $global_animations as $animation ) {
					$id                    = $animation['_id'] ?? '';
					$name                  = $animation['name'] ?? 'Unnamed';
					$global_options[ $id ] = $name;
				}
			}
		}

		if ( $image_global_enabled ) {
			$this->add_control(
				'tp_select_image_global_animation',
				array(
					'label'     => esc_html__( 'Global Animation', 'tpebl' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $global_options,
					'default'   => '',
					'condition' => array(
						'image_animations'       => 'tp_global',
						'enable_image_animation' => 'yes',
					),
					'description' => esc_html__( 'Select an image animation previously created in the Global Scroll Interactions section of Site Settings.', 'tpebl' ),
				)
			);

		} else {
			$this->add_control(
				'tp_image_global_animation_notice',
				array(
					'type'        => Controls_Manager::RAW_HTML,
					'raw'         => wp_kses_post(
						sprintf(
							'<p class="tp-controller-label-text">
								<i>
									%s<br>
									<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>
								</i>
							</p>',
							esc_html__( 'Image Global Animation is disabled. Please enable it from Dashboard → Extensions.', 'tpebl' ),
							esc_url( admin_url( 'admin.php?page=theplus_welcome_page#/extension' ) ),
							esc_html__( 'Click here to enable', 'tpebl' )
						)
					),
					'label_block' => true,
					'condition'   => array(
						'image_animations' => 'tp_global',
					),
				)
			);
		}


		$this->add_control(
			'image_trigger',
			array(
				'label'     => esc_html__( 'Trigger Type', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'onload',
				'options'   => array(
					'onload'   => esc_html__( 'On Load', 'tpebl' ),
					'onscroll' => esc_html__( 'On Scroll', 'tpebl' ),
					'onhover'  => esc_html__( 'On Hover', 'tpebl' ),
				),
				'description' => esc_html__( 'Set when the animation starts: on initial page load, as the user scrolls, or when hovering.', 'tpebl' ),
				'condition' => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'tp_scrub',
			array(
				'label'        => __( 'Enable Scroll Scrub', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'tpebl' ),
				'label_off'    => __( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'image_trigger'    => 'onscroll',
					'image_animations' => 'tp_basic',
				),
				'description'  => esc_html__( 'Enable this to sync the animation progress directly with the page scroll speed.', 'tpebl' ),
			)
		);
		$this->add_control(
			'tp_scrub_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'Animation follows your scrolling', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'image_trigger'    => 'onscroll',
					'image_animations' => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'image_transform_toggle',
			array(
				'label'        => esc_html__( 'Transform Effects', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable custom transformation properties like position, scale, and rotation.', 'tpebl' ),
				'condition'    => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);

		$this->start_popover();
		$this->add_control(
			'img_x',
			array(
				'label'   => esc_html__( 'X Offset', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => -500,
						'max' => 500,
					),
				),
				'default' => array( 'size' => 0 ),
				'description' => esc_html__( 'Moves the image horizontally from its original position.', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_y',
			array(
				'label'   => esc_html__( 'Y Offset', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => -500,
						'max' => 500,
					),
				),
				'default' => array( 'size' => 0 ),
				'description' => esc_html__( 'Moves the image vertically from its original position.', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_skewx',
			array(
				'label'   => esc_html__( 'Skew X', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => -180,
						'max' => 180,
					),
				),
				'default' => array( 'size' => 0 ),
				'description' => esc_html__( 'Skews the image along the X-axis.', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_skewy',
			array(
				'label'   => esc_html__( 'Skew Y', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => -180,
						'max' => 180,
					),
				),
				'default' => array( 'size' => 0 ),
				'description' => esc_html__( 'Skews the image along the Y-axis.', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_scale',
			array(
				'label'   => esc_html__( 'Scale', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 3,
						'step' => 0.01,
					),
				),
				'default' => array( 'size' => 1 ),
				'description' => esc_html__( 'Resizes the image (1 is original size).', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_rotation',
			array(
				'label'   => esc_html__( 'Rotation', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => -360,
						'max' => 360,
					),
				),
				'default' => array( 'size' => 0 ),
				'description' => esc_html__( 'Rotates the image around its origin.', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_opacity',
			array(
				'label'   => esc_html__( 'Opacity', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.01,
					),
				),
				'default' => array( 'size' => 1 ),
				'description' => esc_html__( 'Controls the transparency level of the image.', 'tpebl' ),
			)
		);
		$this->add_control(
			'img_origin',
			array(
				'label'   => esc_html__( 'Transform Origin', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '50% 50%',
				'options' => array(
					'50% 50%'   => esc_html__( 'Center', 'tpebl' ),
					'0% 0%'     => esc_html__( 'Top Left', 'tpebl' ),
					'100% 0%'   => esc_html__( 'Top Right', 'tpebl' ),
					'0% 100%'   => esc_html__( 'Bottom Left', 'tpebl' ),
					'100% 100%' => esc_html__( 'Bottom Right', 'tpebl' ),
				),
				'description' => esc_html__( 'Define the anchor point for transformations like rotation and scale.', 'tpebl' ),
			)
		);
		$this->add_control(
			'tp_clip_path_type',
			array(
				'label'   => esc_html__( 'Clip Path', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''                    => esc_html__( 'None', 'tpebl' ),
					'circle_center'       => esc_html__( 'Circle — Center', 'tpebl' ),
					'circle_left'         => esc_html__( 'Circle — Left', 'tpebl' ),
					'circle_right'        => esc_html__( 'Circle — Right', 'tpebl' ),
					'ellipse_center'      => esc_html__( 'Ellipse — Center', 'tpebl' ),
					'ellipse_horizontal'  => esc_html__( 'Ellipse — Horizontal', 'tpebl' ),
					'inset_top'           => esc_html__( 'Inset — Top Reveal', 'tpebl' ),
					'inset_bottom'        => esc_html__( 'Inset — Bottom Reveal', 'tpebl' ),
					'inset_left'          => esc_html__( 'Inset — Left Reveal', 'tpebl' ),
					'inset_right'         => esc_html__( 'Inset — Right Reveal', 'tpebl' ),
					'poly_triangle'       => esc_html__( 'Triangle', 'tpebl' ),
					'poly_diamond'        => esc_html__( 'Diamond', 'tpebl' ),
					'poly_hexagon'        => esc_html__( 'Hexagon', 'tpebl' ),
					'poly_diag_left'      => esc_html__( 'Diagonal Left', 'tpebl' ),
					'poly_diag_right'     => esc_html__( 'Diagonal Right', 'tpebl' ),
					'blob_organic'        => esc_html__( 'Organic Blob', 'tpebl' ),
					'blob_irregular'      => esc_html__( 'Irregular Blob', 'tpebl' ),
					'star'                => esc_html__( 'Star', 'tpebl' ),
					'skew_right'          => esc_html__( 'Skew Right', 'tpebl' ),
					'skew_left'           => esc_html__( 'Skew Left', 'tpebl' ),
					'wave_top'            => esc_html__( 'Wave Top', 'tpebl' ),
					'wave_bottom'         => esc_html__( 'Wave Bottom', 'tpebl' ),
					'diagonal_cut_double' => esc_html__( 'Double Diagonal', 'tpebl' ),
					'corner_round'        => esc_html__( 'Rounded Corners', 'tpebl' ),
					'custom'              => esc_html__( 'Custom Clip Path', 'tpebl' ),
				),
				'description' => esc_html__( 'Apply a clip-path mask to the image for unique reveal shapes.', 'tpebl' ),
			)
		);
		$this->add_control(
			'tp_custom_clip_path_value',
			array(
				'label'       => esc_html__( 'Custom Clip Path Value', 'tpebl' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'polygon(50% 0%, 0% 100%, 100% 100%)', 'tpebl' ),
				'description' => esc_html__( 'Enter any valid CSS clip-path value', 'tpebl' ),
				'condition'   => array(
					'tp_clip_path_type' => 'custom',
				),
				'ai'          => false,
			)
		);
		$this->add_control(
			'tp_custom_clip_path_info',
			array(
				'type'        => \Elementor\Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"><i>%s 
							<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>
						</i></p>',
						esc_html__( 'If you want to create more custom clip-path shapes, you can generate them here:', 'tpebl' ),
						esc_url( 'https://bennettfeely.com/clippy/' ),
						esc_html__( 'Open Clip-Path Generator', 'tpebl' )
					)
				),
				'label_block' => true,
				'condition'   => array(
					'tp_clip_path_type' => 'custom',
				),
			)
		);

		$this->end_popover();
		$this->add_control(
			'tp_animetions_controller',
			array(
				'label'        => esc_html__( 'Animation Controls', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'tpebl' ),
				'label_on'     => esc_html__( 'Custom', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable custom duration and delay timing for basic presets.', 'tpebl' ),
				'condition'    => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->start_popover();
		$this->add_control(
			'img_duration',
			array(
				'label'     => esc_html__( 'Duration', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 1.2,
				'description' => esc_html__( 'Total time in seconds for the animation to complete.', 'tpebl' ),
				'condition' => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'img_duration_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'How long the animation runs', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'img_delay',
			array(
				'label'     => esc_html__( 'Delay', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0.3,
				'description' => esc_html__( 'Idle time in seconds before the animation begins.', 'tpebl' ),
				'condition' => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'img_delay_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'Animation begins after this delay', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->end_popover();
		// $this->add_control(
		// 'img_stagger',
		// [
		// 'label' => esc_html__( 'Stagger', 'tpebl' ),
		// 'type'  => \Elementor\Controls_Manager::NUMBER,
		// 'min'   => 0.1,
		// 'max'   => 10,
		// 'step'  => 0.1,
		// 'default' => 1.2,
		// 'condition' => [
		// 'enable_image_animation' => 'yes',
		// 'image_animations' => 'tp_basic',
		// ],
		// ]
		// );
		// $this->add_control(
		// 'img_stagger_label',
		// array(
		// 'type'        => Controls_Manager::RAW_HTML,
		// 'raw'         => wp_kses_post(
		// sprintf(
		// '<p class="tp-controller-label-text"> %s </p>',
		// esc_html__( 'Play animation in sequence', 'tpebl' ),
		// )
		// ),
		// 'label_block' => true,
		// 'condition' => [
		// 'enable_image_animation' => 'yes',
		// 'image_animations' => 'tp_basic',
		// ],
		// )
		// );
		$this->add_control(
			'img_ease',
			array(
				'label'     => esc_html__( 'Animation Effects', 'tpebl' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'power3.out',
				'description' => esc_html__( 'Define the acceleration and deceleration curve for the animation movement.', 'tpebl' ),
				'options'   => array(
					'power1.out'  => esc_html__( 'Power1', 'tpebl' ),
					'power2.out'  => esc_html__( 'Power2', 'tpebl' ),
					'power3.out'  => esc_html__( 'Power3', 'tpebl' ),
					'elastic.out' => esc_html__( 'Elastic', 'tpebl' ),
					'back.out'    => esc_html__( 'Back', 'tpebl' ),
					'bounce.out'  => esc_html__( 'Bounce', 'tpebl' ),
					'none'        => esc_html__( 'Linear', 'tpebl' ),
				),
				'condition' => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => 'tp_basic',
				),
			)
		);
		$this->add_control(
			'tp_play_image_animation',
			array(
				'label'     => __( 'Play Animation', 'tpebl' ),
				'type'      => Controls_Manager::BUTTON,
				'text'      => __( 'Preview Animation', 'tpebl' ),
				'event'     => 'tp:play_gsap_animation',
				'classes'   => 'tp-preview-animation-button',
				'condition' => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => array( 'tp_basic' ),
				),
			)
		);
		$this->add_control(
			'tp_play_image_animation_label',
			array(
				'type'        => Controls_Manager::RAW_HTML,
				'raw'         => wp_kses_post(
					sprintf(
						'<p class="tp-controller-label-text"> %s </p>',
						esc_html__( 'For the best visual experience, preview this animation on the frontend.', 'tpebl' ),
					)
				),
				'label_block' => true,
				'condition'   => array(
					'enable_image_animation' => 'yes',
					'image_animations'       => array( 'tp_basic' ),
				),
			)
		);
		$this->end_controls_section();
