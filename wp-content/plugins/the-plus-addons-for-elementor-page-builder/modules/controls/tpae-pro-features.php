<?php
use Elementor\Base_Data_Control;

if ( ! defined( 'ABSPATH' ) ) exit;

class Tpae_Pro_Feature extends Base_Data_Control {
	
	public function get_type() {
		return 'tpae_pro_feature';
	}

	public function content_template() {

		echo '<div class="tpae-prof-main-container" style="display: flex;flex-direction: column;gap: 16px;padding: 15px; background-color:var(--e-a-bg-hover);border:1px solid var(--e-a-bg-active);border-radius: 5px;">';
            echo '<div class="tpae-prof-badge" >';
                echo '<img src="' . esc_url( L_THEPLUS_ASSETS_URL . 'images/pro-features/diamond.png' ) . '" style="width: 14px;height: 14px; "> ';
                echo '<span>';
                    echo esc_html__( 'Pro Feature', 'tpebl' );
                echo'</span>';
            echo '</div>';

            echo '<div class="tpae-prof-heading">';
                echo esc_html__( 'Unlock All Features with The Plus Addons for Elementor Pro', 'tpebl' );
            echo '</div>';

            echo '<div class="tpae-prof-list" style="display:flex; flex-direction: column; gap: 5px;">';
                echo '<div class="tpae-prof-text">';
                    echo '<i class="theplus-i-check-mark-fill" style="color: var(--e-a-color-txt-accent);"></i>';
                    echo '<span>';
                        echo esc_html__( 'Get all 120+ Elementor Widgets', 'tpebl' );
                    echo'</span>';
                echo '</div>';
                echo '<div class="tpae-prof-text">';
                    echo '<i class="theplus-i-check-mark-fill" style="color: var(--e-a-color-txt-accent);"></i>';
                    echo '<span>';
                        echo esc_html__( '1000+ Elementor Templates & Sections', 'tpebl' );
                    echo'</span>';
                echo '</div>';
                echo '<div class="tpae-prof-text">';
                    echo '<i class="theplus-i-check-mark-fill" style="color: var(--e-a-color-txt-accent);"></i>';
                    echo '<span>';
                        echo esc_html__( 'Whitelabel & More', 'tpebl' );
                    echo'</span>';
                echo '</div>';
            echo '</div>';

            echo '<div class="tpae-prof-btn-links">';
                echo '<a href="https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links" class="tpae-get-pro" target="_blank" rel="noopener noreferrer" style="background-color: #8073FC;color: #FFFFFF;padding: 8px 16px;">';
                    echo esc_html__( 'Get Pro', 'tpebl' );
                echo '</a>';
                echo '<a href="https://theplusaddons.com/free-vs-pro/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=links" class="tpae-compare-pro" target="_blank" rel="noopener noreferrer" style="color: #8073FC;">';
                    echo esc_html__( 'Compare Free vs Pro', 'tpebl' );
                echo '</a>';
            echo '</div>';
        echo '</div>';
	}
}
