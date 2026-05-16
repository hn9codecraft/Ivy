<?php
/**
 * Global Dimensions Base Control Override
 *
 * @link    https://posimyth.com/
 * @since   v6.5.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */
namespace ThePlusAddons\Elementor\Dimensions;

use Elementor\Control_Dimensions;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ThePlusAddons\Elementor\Dimensions\TP_Control_Dimensions' ) && class_exists( '\Elementor\Control_Dimensions' ) ) {

	/**
	 * Extends Elementor's native Dimensions control to append a Global Setting preset check.
	 *
	 * @since v6.5.0
	 */
	class TP_Control_Dimensions extends Control_Dimensions {

		/**
		 * Retrieve dimensions control default value.
		 * Inject our preset key.
		 *
		 * @since v6.5.0
		 * @return array Default value.
		 */
		public function get_default_value() {
			return array_merge(
				parent::get_default_value(),
				array(
					'tp_global_preset' => '',
				)
			);
		}

		/**
		 * Resolve style placeholders from the selected global preset when present.
		 *
		 * This lets existing DIMENSIONS controls keep using their current selectors
		 * (`{{TOP}}`, `{{RIGHT}}`, `{{BOTTOM}}`, `{{LEFT}}`, `{{UNIT}}`) without any
		 * per-widget changes.
		 *
		 * @since v6.5.0
		 *
		 * @param string $css_property  CSS placeholder property.
		 * @param array  $control_value Control value.
		 * @param array  $control_data  Control data.
		 *
		 * @return string
		 */
		public function get_style_value( $css_property, $control_value, array $control_data ) {
			$css_property = strtoupper( $css_property );

			if (
				! empty( $control_value['tp_global_preset'] ) &&
				class_exists( 'ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global' )
			) {
				$preset_values = TP_Dimensions_Global::get_preset_raw_values( $control_value['tp_global_preset'] );

				if ( ! empty( $preset_values ) ) {
					if ( 'UNIT' === $css_property ) {
						return ! empty( $preset_values['unit'] ) ? $preset_values['unit'] : 'px';
					}

					$dimension_key = strtolower( $css_property );

					if ( isset( $preset_values[ $dimension_key ] ) ) {
						$val = $preset_values[ $dimension_key ];

						// Ensure 0 and '0' are returned as '0', not as empty string.
						// Elementor skips CSS generation when get_style_value returns ''.
						if ( '' === $val || null === $val ) {
							return '0';
						}

						return $val;
					}

					// Dimension key not found in preset — default to 0.
					return '0';
				}
			}

			return parent::get_style_value( $css_property, $control_value, $control_data );
		}

		/**
		 * Render dimensions control output in the editor.
		 * Used to generate the control HTML in the editor using Underscore JS template.
		 *
		 * @since v6.5.0
		 */
		public function content_template() {
			$class_name = $this->get_singular_name();
			?>
			<div class="elementor-control-field">
				<label class="elementor-control-title">{{{ data.label }}}</label>
				<?php $this->print_units_template(); ?>
				<div class="elementor-control-input-wrapper">
					
					<# if ( data.name !== 'tdm_values' ) { #>
					<div class="tp-global-dim-select-wrapper" style="margin-bottom: 8px;">
						<select data-setting="tp_global_preset" style="width: 100%; border-radius: 3px; font-size: 11px;">
							<option value=""><?php esc_html_e( 'None', 'tpebl' ); ?></option>
							<?php
							if ( class_exists( 'ThePlusAddons\Elementor\Dimensions\TP_Dimensions_Global' ) ) {
								$presets = TP_Dimensions_Global::get_global_dimensions_list();
								if ( ! empty( $presets ) ) {
									foreach ( $presets as $preset ) {
										$id    = ! empty( $preset['_id'] ) ? $preset['_id'] : '';
										$title = ! empty( $preset['name'] ) ? $preset['name'] : 'Unnamed';
										if ( '' === $id ) {
											continue;
										}
										echo '<option value="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</option>';
									}
								}
							}
							?>
						</select>
					</div>
					<# } #>

					<ul class="elementor-control-<?php echo esc_attr( $class_name ); ?>s tp-native-dim-inputs" <# if ( data.name !== 'tdm_values' && data.controlValue && data.controlValue.tp_global_preset ) { #> style="display:none;" <# } #>>
						<?php foreach ( $this->get_dimensions() as $dimension_key => $dimension_title ) : ?>
							<li class="elementor-control-<?php echo esc_attr( $class_name ); ?>">
								<input id="<?php $this->print_control_uid( $dimension_key ); ?>" type="text" data-setting="<?php
									echo esc_attr( $dimension_key );
								?>" placeholder="<#
										placeholder = view.getControlPlaceholder();
										if ( _.isObject( placeholder ) && ! _.isUndefined( placeholder.<?php echo esc_attr( $dimension_key ); ?> ) ) {
												print( encodeURIComponent( placeholder.<?php echo esc_attr( $dimension_key ); ?> ) );
										} else {
											print( placeholder ? encodeURIComponent( placeholder ) : '' );
										} #>"
								<# if ( -1 === _.indexOf( allowed_dimensions, '<?php echo esc_attr( $dimension_key ); ?>' ) ) { #>
									disabled
								<# } #>
										/>
								<label for="<?php $this->print_control_uid( $dimension_key ); ?>" class="elementor-control-<?php echo esc_attr( $class_name ); ?>-label"><?php
									echo esc_html( $dimension_title );
								?></label>
							</li>
						<?php endforeach; ?>
						<li>
							<button class="elementor-link-<?php echo esc_attr( $class_name ); ?>s tooltip-target" data-tooltip="<?php echo esc_attr__( 'Link values together', 'elementor' ); ?>">
								<span class="elementor-linked">
									<i class="eicon-link" aria-hidden="true"></i>
									<span class="elementor-screen-only"><?php echo esc_html__( 'Link values together', 'elementor' ); ?></span>
								</span>
								<span class="elementor-unlinked">
									<i class="eicon-chain-broken" aria-hidden="true"></i>
									<span class="elementor-screen-only"><?php echo esc_html__( 'Unlinked values', 'elementor' ); ?></span>
								</span>
							</button>
						</li>
					</ul>
				</div>
			</div>
			
			<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
			<# } #>
			<?php
		}
	}
}
