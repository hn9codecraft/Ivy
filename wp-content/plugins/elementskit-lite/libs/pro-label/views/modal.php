<?php
defined( 'ABSPATH' ) || exit;

$current_tier = \ElementsKit_Lite\Utils::get_tier();
$is_pro_active = \ElementsKit_Lite\Utils::ekit_is_plugin_active( 'elementskit/elementskit.php' );

if ( $is_pro_active && $current_tier === 'free' ) {
	// Pro plugin active but license not activated
	$modal_title = esc_html__( 'Activate Your License', 'elementskit-lite' );
	$modal_desc  = sprintf(
		/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
		esc_html__( 'ElementsKit Pro is installed but your license is not activated. Please %1$sactivate your license%2$s to unlock all pro features.', 'elementskit-lite' ),
		'<a href="' . esc_url( admin_url( 'admin.php?page=elementskit-license' ) ) . '">',
		'</a>'
	);
} elseif ( $is_pro_active && $current_tier !== 'free' ) {
	// Pro active with a paid tier, but feature requires higher tier
	$modal_title = esc_html__( 'Upgrade Your Plan', 'elementskit-lite' );
	$modal_desc  = sprintf(
		/* translators: %1$s: current tier name, %2$s: opening anchor tag, %3$s: closing anchor tag */
		esc_html__( 'Your current %1$s plan doesn\'t include this feature. %2$sUpgrade your plan%3$s to unlock this and other advanced features.', 'elementskit-lite' ),
		'<strong>' . esc_html( ucfirst( $current_tier ) ) . '</strong>',
		'<a target="_blank" href="https://wpmet.com/elementskit-pricing">',
		'</a>'
	);
} else {
	$modal_title = esc_html__( 'Go Premium', 'elementskit-lite' );
	$modal_desc  = sprintf(
		/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
		esc_html__( 'Purchase our %1$spro version%2$s to unlock these premium features!', 'elementskit-lite' ),
		'<a target="_blank" href="https://wpmet.com/elementskit-pricing">',
		'</a>'
	);
}
?>

<div class="attr-modal attr-fade ekit-wid-con" id="elementskit_go_pro_modal" tabindex="-1" role="dialog" aria-labelledby="elementskit_go_pro_modalLabel" style="display: none;">
	<div class="attr-modal-dialog attr-modal-dialog-centered ekit-go-pro-con" role="document">
        <div class="attr-modal-content">
        <button type="button" class="close attr-hidden" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <div class="attr-modal-body attr-text-center">
                <i class="icon icon-information"></i>
                <h2><?php echo esc_html( $modal_title ); ?></h2>
                <p><?php echo wp_kses( $modal_desc, [ 'a' => [ 'href' => [], 'target' => [] ], 'strong' => [] ] ); ?></p>
            </div>
        </div>
	</div>
</div>
