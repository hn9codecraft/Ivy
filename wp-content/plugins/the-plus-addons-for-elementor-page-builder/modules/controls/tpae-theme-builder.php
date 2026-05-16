<?php

use Elementor\Base_Data_Control;

if ( ! defined( 'ABSPATH' ) ) exit;

class Tpae_Theme_builder extends Base_Data_Control {
  
	public function get_type() {
		return 'tpae_theme_builder';
	}

	public function content_template() {

		$elementor_pro_plugin_status = apply_filters( 'tpae_get_plugin_status','elementor-pro/elementor-pro.php' );
		$nxt_ext_plugin_status = apply_filters( 'tpae_get_plugin_status','nexter-extension/nexter-extension.php' );

		?>
		<#
			var tp_notice = data.controlValue || data.notice || '';
			var tp_btn_txt = data.controlValue || data.button_text || '';
			var tp_page_type = data.controlValue || data.page_type || 'tp_header';
		#>

		<div style=" background-color:var(--e-a-bg-active);border-top:3px solid #6660EF;padding:10px 20px 15px 20px;font-style:italic;">
			<p style="margin:0; color:var(--e-a-bg-invert);font-size:12px;line-height:15px;font-weight:300;letter-spacing:0.3px;">
				<b style="color:var(--e-a-bg-logo)!important;font-size:13px;line-height:30px;"><?php echo esc_html__( 'Note :', 'tpebl' ); ?></b><br>
				{{{ tp_notice }}}
			</p>
		</div>

		<?php if ( 'active' === $elementor_pro_plugin_status ) { ?>
			<button class="elementor-button elementor-button-default tp-page-create" data-tp_btn_txt="{{{tp_btn_txt}}}" data-post_type="elementor_library" data-page_type="{{{tp_page_type}}}" style="position:relative;display:flex;justify-content:center;min-height:39.4px;color:#fff;font-weight:500;background-color:#6660EF;border-radius:3px;padding:12px 24px;margin-top:15px;border-block-end:none;">
				{{{tp_btn_txt}}}
					<span class="tp-nextbtn-loader" style="display: none; position: absolute; top: 50%; width: 16px; height: 16px; margin-top: -8px;border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: tp-spin 1s linear infinite;"></span>
				</button>
		<?php } else { 
			if('active' === $nxt_ext_plugin_status) { ?>
				<button class="elementor-button elementor-button-default tp-page-create" data-tp_btn_txt="{{{tp_btn_txt}}}"  data-post_type="nxt_builder" data-page_type="{{{tp_page_type}}}" style="position:relative;display:flex;justify-content:center;min-height:39.4px;color:#fff;font-weight:600;background-color:#6660EF;border-radius:3px;padding:12px 24px;margin-top:15px;border-block-end:none;">
				{{{tp_btn_txt}}}
					<span class="tp-nextbtn-loader" style="display: none; position: absolute; top: 50%; width: 16px; height: 16px; margin-top: -8px;border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: tp-spin 1s linear infinite;"></span>
				</button>
			<?php } else { ?>
				<div style="margin:0; color:var(--e-a-bg-invert);font-size:12px;line-height:15px;margin-top:15px;font-weight:300;"> <?php echo esc_html__( 'This will install Nexter Extension for FREE Elementor Theme Builder', 'tpebl' ); ?> </div>

				<button class="elementor-button elementor-button-default tp-install-nexter-ext" data-tp_btn_txt="{{{tp_btn_txt}}}"  data-post_type="nxt_builder" data-page_type="{{{tp_page_type}}}" style="position:relative;display:flex;justify-content:center;min-height:39.4px;color:#fff;font-weight:600;background-color:#6660EF;border-radius:3px;padding:12px 24px;margin-top:15px;border-block-end:none;">
					<?php echo esc_html__( 'Enable Theme Builder', 'tpebl' ); ?>
					<!-- <span class="tp-nextbtn-loader" style="display: none; position: absolute; left: 45px; top: 50%; width: 16px; height: 16px; margin-top: -8px;border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: tp-spin 1s linear infinite;"></span> -->
				</button>
			<?php } ?>
			<?php }; ?>
			<style>@keyframes tp-spin {from { transform: rotate(0deg); }to { transform: rotate(360deg); }}</style>
		<?php
	}
}
