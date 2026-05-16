<?php

namespace ElementsKit_Lite\Core;

defined('ABSPATH') || exit;

class Editor_Promotion{

	use \ElementsKit_Lite\Traits\Singleton;

	public function init() {
		// Enqueue promotion scripts
		add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
	}

	/**
	 * Get promotion widgets data
	 */
	private function get_promotion_widgets_data() {
		$widget_list = \ElementsKit_Lite\Config\Widget_List::instance()->get_list('all');
		$current_tier = \ElementsKit_Lite\Utils::get_tier();
		$promotion_data = [];


		foreach ($widget_list as $slug => $widget) {
			$is_pro_disabled = isset($widget['package']) && $widget['package'] === 'pro-disabled';
			$is_tier_locked  = isset($widget['package']) && $widget['package'] === 'pro'
				&& isset($widget['tier']) && ! \ElementsKit_Lite\Utils::is_tier($widget['tier']);

			if ($is_pro_disabled || $is_tier_locked) {
				// Check if widget has tier restrictions
				$tier_restricted = isset($widget['tier']);

				$promotion_data[] = [
					'name' => 'ekit-' . $slug,
					'title' => isset($widget['title']) ? $widget['title'] : ucwords(str_replace('-', ' ', $slug)),
					'icon' => isset($widget['icon']) ? $widget['icon'] : 'eicon-star',
					'categories' => ['elementskit'],
					'promotion' => [
						'title' => sprintf(__('%s Widget', 'elementskit-lite'), isset($widget['title']) ? $widget['title'] : ucwords(str_replace('-', ' ', $slug))),
						'description' => $is_tier_locked
							? sprintf(
								__('The %1$s widget requires a %2$s or higher plan. Upgrade your current %3$s plan to access this widget and other advanced features.', 'elementskit-lite'),
								isset($widget['title']) ? $widget['title'] : ucwords(str_replace('-', ' ', $slug)),
								ucfirst($widget['tier']),
								ucfirst($current_tier)
							)
							: sprintf(
								__('Unlock the %s widget and dozens of powerful ElementsKit Pro features to design faster, smarter, and more flexible websites.', 'elementskit-lite'),
								isset($widget['title']) ? $widget['title'] : ucwords(str_replace('-', ' ', $slug))
							),
						'upgrade_url' => 'https://wpmet.com/plugin/elementskit/pricing/',
						'upgrade_text' => __('Upgrade Now', 'elementskit-lite'),
						'tier_restricted' => $tier_restricted,
						'required_tier' => isset($widget['tier']) ? $widget['tier'] : null,
						'current_tier' => $current_tier,
					],
				];
			}
		}
		return $promotion_data;
	}

	/**
	 * Enqueue editor scripts for promotion
	 */
	public function enqueue_editor_scripts() {
		$promotion_widgets = $this->get_promotion_widgets_data();

		wp_enqueue_script(
			'elementskit-editor-promotion',
			\ElementsKit_Lite::widget_url() . 'init/assets/js/editor-promotion.js',
			['elementor-editor', 'elementor-common'],
			\ElementsKit_Lite::version(),
			true
		);

		wp_localize_script(
			'elementskit-editor-promotion',
			'ekitPromotion',
			[
				'promotionWidgets' => $promotion_widgets,
				'upgradeUrl' => 'https://wpmet.com/plugin/elementskit/pricing/',
				'debug' => true,
				'i18n' => [
					'proFeature' => __('Pro Feature', 'elementskit-lite'),
					'upgradeNow' => __('Upgrade Now', 'elementskit-lite'),
					'learnMore' => __('Learn More', 'elementskit-lite'),
				],
			]
		);

		// Enqueue styles
		wp_enqueue_style(
			'elementskit-editor-promotion',
			\ElementsKit_Lite::widget_url() . 'init/assets/css/editor-promotion.css',
			[],
			\ElementsKit_Lite::version()
		);
	}
}
