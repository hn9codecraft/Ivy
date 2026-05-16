<?php
namespace ThePlusAddons\Elementor\Image;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Repeater;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global GSAP Animations Tab
 * Appears under: Site Settings → Theme Style
 * 
 * @since v6.4.5
 */
class TP_GSAP_Image_Global extends Tab_Base {

	public function get_id() {
		return 'settings-tp-image-gsap-global';
	}

	public function get_title() {
		return esc_html__( 'Global Image Animations', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-image-rollover';
	}

	protected function register_tab_controls() {

		$this->start_controls_section(
			'section_' . $this->get_id(),
			array(
				'label' => $this->get_title(),
				'tab'   => $this->get_id(),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Animation Name', 'tpebl' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'My Animation', 'tpebl' ),
				'description' => esc_html__( 'Enter a unique name for this image animation to identify it easily.', 'tpebl' ),
				'ai'      => false,
			)
		);
		$repeater->add_control(
			'image_trigger',
			array(
				'label'   => esc_html__( 'Trigger Type', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'onload',
				'options' => array(
					'onload'   => esc_html__( 'On Load', 'tpebl' ),
					'onscroll' => esc_html__( 'On Scroll', 'tpebl' ),
					'onhover'  => esc_html__( 'On Hover', 'tpebl' ),
				),
				'description' => esc_html__( 'Choose when the animation should trigger: when the page loads, as the user scrolls, or on mouse hover.', 'tpebl' ),
			)
		);
		$repeater->add_control(
			'tp_scrub',
			array(
				'label'        => __( 'Enable Scroll Scrub', 'tpebl' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'tpebl' ),
				'label_off'    => __( 'No', 'tpebl' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Enable this to link the animation progress directly to the scrollbar movement.', 'tpebl' ),
				'condition'    => array(
					'image_trigger' => 'onscroll',
				),
			)
		);
		$repeater->add_control(
			'img_duration',
			array(
				'label'   => esc_html__( 'Duration', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 1.2,
				'description' => esc_html__( 'Set the length of the animation in seconds.', 'tpebl' ),
			)
		);
		$repeater->add_control(
			'img_delay',
			array(
				'label'   => esc_html__( 'Delay', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 0.3,
				'description' => esc_html__( 'Set a delay before the animation starts in seconds.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Adjust the horizontal translation of the image.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Adjust the vertical translation of the image.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Apply a horizontal skew effect to the image.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Apply a vertical skew effect to the image.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Set the initial scale factor of the image.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Set the rotation angle in degrees.', 'tpebl' ),
			)
		);
		$repeater->add_control(
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
				'description' => esc_html__( 'Adjust the starting opacity of the image.', 'tpebl' ),
			)
		);
		$repeater->add_control(
			'img_origin',
			array(
				'label'   => esc_html__( 'Transform Origin', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '50% 50%',
				'options' => array(
					'50% 50%'   => 'Center',
					'0% 0%'     => 'Top Left',
					'100% 0%'   => 'Top Right',
					'0% 100%'   => 'Bottom Left',
					'100% 100%' => 'Bottom Right',
				),
				'description' => esc_html__( 'Specify the pivot point for transformations like scale and rotation.', 'tpebl' ),
			)
		);
		$repeater->add_control(
			'tp_clip_path_type',
			array(
				'label'   => esc_html__( 'Clip Path', 'tpebl' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''                    => 'None',
					'circle_center'       => 'Circle — Center',
					'circle_left'         => 'Circle — Left',
					'circle_right'        => 'Circle — Right',
					'ellipse_center'      => 'Ellipse — Center',
					'ellipse_horizontal'  => 'Ellipse — Horizontal',
					'inset_top'           => 'Inset — Top Reveal',
					'inset_bottom'        => 'Inset — Bottom Reveal',
					'inset_left'          => 'Inset — Left Reveal',
					'inset_right'         => 'Inset — Right Reveal',
					'poly_triangle'       => 'Triangle',
					'poly_diamond'        => 'Diamond',
					'poly_hexagon'        => 'Hexagon',
					'poly_diag_left'      => 'Diagonal Left',
					'poly_diag_right'     => 'Diagonal Right',
					'blob_organic'        => 'Organic Blob',
					'blob_irregular'      => 'Irregular Blob',
					'star'                => 'Star',
					'skew_right'          => 'Skew Right',
					'skew_left'           => 'Skew Left',
					'wave_top'            => 'Wave Top',
					'wave_bottom'         => 'Wave Bottom',
					'diagonal_cut_double' => 'Double Diagonal',
					'corner_round'        => 'Rounded Corners',
					'custom'              => 'Custom Clip Path',
				),
				'description' => esc_html__( 'Select a predefined or custom clip-path shape for the entrance effect.', 'tpebl' ),
			)
		);
		$repeater->add_control(
			'tp_custom_clip_path_value',
			array(
				'label'       => esc_html__( 'Custom Clip Path Value', 'tpebl' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'polygon(50% 0%, 0% 100%, 100% 100%)', 'tpebl' ),
				'description' => esc_html__( 'Enter any valid CSS clip-path value', 'tpebl' ),
				'condition'   => array(
					'tp_clip_path_type' => 'custom',
				),
			)
		);
		$repeater->add_control(
			'tp_custom_clip_path_info',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => wp_kses_post(
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
				'condition'   => [
					'tp_clip_path_type' => 'custom',
				],
			]
		);
		// $repeater->add_control(
		// 	'img_stagger',
		// 	array(
		// 		'label'   => esc_html__( 'Stagger', 'tpebl' ),
		// 		'type'    => \Elementor\Controls_Manager::NUMBER,
		// 		'min'     => 0.1,
		// 		'max'     => 10,
		// 		'step'    => 0.1,
		// 		'default' => 1.2,
		// 	)
		// );
		$repeater->add_control(
			'img_ease',
			array(
				'label'   => esc_html__( 'Animation Effects', 'tpebl' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'power3.out',
				'options' => array(
					'power1.out'          => 'Power1',
					'power2.out'          => 'Power2',
					'power3.out'          => 'Power3',
					'elastic.out(1, 0.4)' => 'Elastic',
					'back.out(1.7)'       => 'Back',
					'bounce.out'          => 'Bounce',
					'none'                => 'Linear',
				),
				'description' => esc_html__( 'Choose the acceleration curve for your animation.', 'tpebl' ),
			)
		);
		$this->add_control(
			'tp_image_global_gsap_list',
			array(
				// 'label'       => esc_html__( 'Global GSAP Animations', 'tpebl' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'button_text' => esc_html__( 'Add Global Animation', 'tpebl' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Helper: Get all saved GSAP animations (for widgets)
	 * 
	 * @since v6.4.5
	 */
	public static function get_image_global_gsap_list() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit ) {
			return array();
		}

		$animations = $kit->get_settings( 'tp_image_global_gsap_list' );

		return ! empty( $animations ) ? $animations : array();
	}
}
