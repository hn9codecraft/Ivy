<?php

namespace ThePlusAddons\Elementor;

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
class TP_GSAP_Global extends Tab_Base {

	public function get_id() {
		return 'settings-tp-gsap-global';
	}

	public function get_title() {
		return esc_html__( 'Global Scroll Interactions', 'tpebl' );
	}

	public function get_group() {
		return 'global';
	}

	public function get_icon() {
		return 'eicon-animation';
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
				'description' => esc_html__( 'Assign a descriptive name to manage your animation efficiently.', 'tpebl' ),
				'ai'      => false,
			)
		);
		$repeater->add_control(
			'tp_gsap_trigger',
			array(
				'label'              => esc_html__( 'Trigger', 'tpebl' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'on_scroll',
				'render_type'        => 'ui',
				'default'            => 'tp_on_load',
				'options'            => array(
					'tp_on_load'   => esc_html__( 'On Load', 'tpebl' ),
					'tp_on_scroll' => esc_html__( 'On Scroll', 'tpebl' ),
				),
				'description'        => esc_html__( 'Select whether the animation triggers upon page load or based on scroll position.', 'tpebl' ),
				'render_type'        => 'ui',
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
			'tp_delay',
			array(
				'label'              => esc_html__( 'Delay', 'tpebl' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 0,
				'max'                => 10,
				'step'               => 0.1,
				'default'            => .15,
				'description'        => esc_html__( 'Specify the delay before the animation begins.', 'tpebl' ),
				'render_type'        => 'ui',
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
			'tp_duration',
			array(
				'label'              => esc_html__( 'Duration', 'tpebl' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 1.5,
				'description'        => esc_html__( 'Determine the total length of the animation in seconds.', 'tpebl' ),
				'render_type'        => 'ui',
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
			'tp_fade_offset',
			array(
				'label'              => esc_html__( 'Offset', 'tpebl' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 50,
				'description'        => esc_html__( 'Define the pixel distance for the movement during entrance.', 'tpebl' ),
				'render_type'        => 'ui',
				// 'condition'          => array(
				// 	'tp_fade_from!' => array( 'in', 'scale' ),
				// ),
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
			'tp_ani_type',
				array(
					'label'              => esc_html__( 'Animation Style', 'tpebl' ),
					'type'               => Controls_Manager::SELECT,
					'default'            => 'tp_fade',
					'render_type'        => 'ui',
					'options'            => array(
						'tp_fade'  => esc_html__( 'Fade', 'tpebl' ),
						'tp_slide' => esc_html__( 'Slide', 'tpebl' ),
						'tp_scale' => esc_html__( 'Scale', 'tpebl' ),
					),
					'description'        => esc_html__( 'Choose the preferred motion style for the animation.', 'tpebl' ),
					'frontend_available' => true,
				)
			);
		$repeater->add_control(
			'tp_ease',
			array(
				'label'              => esc_html__( 'Animation Effect', 'tpebl' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'power1.out',
				'render_type'        => 'ui',
				'options'            => array(
					'power1.out'  => esc_html__( 'Power1 Out', 'tpebl' ),
					'power2.out'  => esc_html__( 'Power2 Out', 'tpebl' ),
					'power3.out'  => esc_html__( 'Power3 Out', 'tpebl' ),
					'power4.out'  => esc_html__( 'Power4 Out', 'tpebl' ),
					'sine.out'    => esc_html__( 'Sine Out', 'tpebl' ),
					'expo.out'    => esc_html__( 'Expo Out', 'tpebl' ),
					'circ.out'    => esc_html__( 'Circ Out', 'tpebl' ),
					'back.out'    => esc_html__( 'Back Out', 'tpebl' ),
					'elastic.out' => esc_html__( 'Elastic Out', 'tpebl' ),
					'bounce.out'  => esc_html__( 'Bounce Out', 'tpebl' ),
				),
				'description'        => esc_html__( 'Select an easing style to define the acceleration curve of the animation.', 'tpebl' ),
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
		'tp_fade_from',
			array(
				'label'              => esc_html__( 'Animation Direction', 'tpebl' ),
				'type'               => Controls_Manager::CHOOSE,
				'options'            => array(
					'top'    => array(
						'title' => esc_html__( 'From Top', 'tpebl' ),
						'icon'  => 'eicon-arrow-down',
					),
					'bottom' => array(
						'title' => esc_html__( 'From Bottom', 'tpebl' ),
						'icon'  => 'eicon-arrow-up',
					),
					'left'   => array(
						'title' => esc_html__( 'From Left', 'tpebl' ),
						'icon'  => 'eicon-arrow-right',
					),
					'right'  => array(
						'title' => esc_html__( 'From Right', 'tpebl' ),
						'icon'  => 'eicon-arrow-left',
					),
				),
				'default'            => 'bottom',
				'description'        => esc_html__( 'Set the starting direction for the animation transition.', 'tpebl' ),
				'toggle'             => false,
				'label_block'        => false,
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
			'tp_stagger',
			array(
				'label'              => esc_html__( 'Stagger Effect', 'tpebl' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'label_off',
				'label_on'           => esc_html__( 'Enable', 'tpebl' ),
				'label_off'          => esc_html__( 'Disable', 'tpebl' ),
				'description'        => esc_html__( 'Enable to apply the animation sequentially to multiple elements.', 'tpebl' ),
				'frontend_available' => true,
			)
		);
		$repeater->add_control(
			'tp_repeat',
			array(
				'label'              => esc_html__( 'Repeat', 'tpebl' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'label_off',
				'label_on'           => esc_html__( 'Enable', 'tpebl' ),
				'label_off'          => esc_html__( 'Disable', 'tpebl' ),
				'description'        => esc_html__( 'Toggle this to allow the animation to re-trigger upon subsequent visits.', 'tpebl' ),
				'frontend_available' => true,
			)
		);
		// $repeater->add_control(
		// 	'tp_yoyo',
		// 	array(
		// 		'label'              => esc_html__( 'Reverse Loop', 'tpebl' ),
		// 		'type'               => Controls_Manager::SWITCHER,
		// 		'default'            => 'label_off',
		// 		'label_on'           => esc_html__( 'Show', 'tpebl' ),
		// 		'label_off'          => esc_html__( 'Hide', 'tpebl' ),
		// 		'condition'          => array(
		// 			'tp_repeat' => array( 'yes' ),
		// 		),
		// 		'frontend_available' => true,
		// 	)
		// );
		$this->add_control(
			'tp_global_gsap_list',
			array(
				// 'label'       => esc_html__( 'Global GSAP Animations', 'tpebl' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'button_text' => esc_html__( 'Add Scroll Interaction', 'tpebl' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Helper: Get all saved GSAP animations (for widgets)
	 *
	 * @since v6.4.5
	 */
	public static function get_global_gsap_list() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit ) {
			return array();
		}

		$animations = $kit->get_settings( 'tp_global_gsap_list' );

		return ! empty( $animations ) ? $animations : array();
	}
}
