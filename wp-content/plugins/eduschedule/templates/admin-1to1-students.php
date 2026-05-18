<?php
/**
 * 1:1 Students admin page
 * URL: admin.php?page=eduschedule-1to1&user_id=X
 *
 * Vars in scope:
 *   $users    = array of WP_User (1to1 category)
 *   $selected = WP_User|null
 *   $uid      = int
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$base = admin_url( 'admin.php?page=eduschedule-1to1' );
?>
<div class="es-admin es-1to1-page">

    <div class="es-page-head">
        <div>
            <h1>1:1 Students</h1>
            <p class="es-page-sub">Converted 1-on-1 students &mdash; <?php echo count( $users ); ?> total</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:16px;align-items:flex-start;">

        <!-- LEFT: List -->
        <div class="es-card" style="padding:12px;">
            <input type="text" id="es-1to1-search" placeholder="Search..." style="width:100%;padding:8px 12px;margin-bottom:10px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.08);border-radius:6px;color:#fff;font-size:13px;" />

            <div id="es-1to1-list" style="display:flex;flex-direction:column;gap:4px;max-height:600px;overflow-y:auto;">
                <?php if ( empty( $users ) ) : ?>
                    <p class="es-empty-cell" style="text-align:center;font-size:12px;">No 1:1 students yet. Convert leads via After Call.</p>
                <?php else : foreach ( $users as $u ) :
                    $initial   = strtoupper( substr( $u->display_name ?: 'U', 0, 2 ) );
                    $active    = ( (int) $u->ID === (int) $uid );
                    $pkg_id    = (int) get_user_meta( $u->ID, ES_Packages::META_PACKAGE_ID, true );
                    $pkg       = $pkg_id ? ES_Packages::get( $pkg_id ) : null;
                    $pkg_label = $pkg ? $pkg->package_name : 'No package';
                ?>
                    <a href="<?php echo esc_url( add_query_arg( 'user_id', $u->ID, $base ) ); ?>"
                       class="es-1to1-item <?php echo $active ? 'is-active' : ''; ?>"
                       data-name="<?php echo esc_attr( strtolower( $u->display_name ) ); ?>"
                       style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;text-decoration:none;color:inherit;background:<?php echo $active ? 'rgba(99,102,241,0.15)' : 'transparent'; ?>;border:1px solid <?php echo $active ? 'rgba(99,102,241,0.4)' : 'transparent'; ?>;">
                        <div style="width:36px;height:36px;border-radius:50%;background:rgba(56,189,248,0.15);color:#38bdf8;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0;">
                            <?php echo esc_html( $initial ); ?>
                        </div>
                        <div style="flex:1;min-width:0;overflow:hidden;">
                            <div style="font-size:13px;font-weight:500;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo esc_html( $u->display_name ); ?>
                            </div>
                            <div style="font-size:11px;color:rgba(255,255,255,0.55);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo esc_html( $pkg_label ); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- RIGHT: Detail -->
        <div class="es-card" style="padding:24px;">
            <?php if ( ! $selected ) : ?>
                <div style="text-align:center;padding:60px 20px;color:rgba(255,255,255,0.5);">
                    <div style="font-size:48px;margin-bottom:12px;">👤</div>
                    <div style="font-size:14px;">Select a student from the list</div>
                </div>
            <?php else :
                $detail_url = admin_url( 'admin.php?page=eduschedule-students&view=detail&user_id=' . $selected->ID );
                $pkg_id     = (int) get_user_meta( $selected->ID, ES_Packages::META_PACKAGE_ID, true );
                $pkg        = $pkg_id ? ES_Packages::get( $pkg_id ) : null;
            ?>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                    <div style="width:50px;height:50px;border-radius:50%;background:rgba(56,189,248,0.15);color:#38bdf8;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;">
                        <?php echo esc_html( strtoupper( substr( $selected->display_name, 0, 2 ) ) ); ?>
                    </div>
                    <div style="flex:1;">
                        <h2 style="margin:0 0 4px;font-size:18px;color:#fff;"><?php echo esc_html( $selected->display_name ); ?></h2>
                        <div style="font-size:12px;color:rgba(255,255,255,0.6);">
                            <?php echo esc_html( $selected->user_email ); ?>
                            <?php $ph = get_user_meta( $selected->ID, 'es_phone', true ); ?>
                            <?php if ( $ph ) : ?> · <?php echo esc_html( $ph ); ?><?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo esc_url( $detail_url ); ?>" class="es-btn es-btn-primary">
                        <span class="dashicons dashicons-visibility"></span> Open Full Detail
                    </a>
                </div>

                <?php if ( $pkg ) : ?>
                    <div class="es-card" style="padding:16px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);">
                        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Active Package</div>
                        <div style="font-size:16px;font-weight:600;color:#fff;margin-bottom:4px;"><?php echo esc_html( $pkg->package_name ); ?></div>
                        <?php if ( $pkg->sub_heading ) : ?>
                            <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-bottom:8px;"><?php echo esc_html( $pkg->sub_heading ); ?></div>
                        <?php endif; ?>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;font-size:12px;">
                            <?php if ( $pkg->price > 0 ) : ?>
                                <?php $cur = ! empty( $pkg->currency ) ? $pkg->currency : 'INR'; ?>
                                <span style="color:#6366f1;font-weight:600;"><?php echo esc_html( ES_Helpers::format_price( $pkg->price, $cur ) ); ?></span>
                            <?php endif; ?>
                            <?php if ( $pkg->hours > 0 ) : ?>
                                <span style="color:rgba(255,255,255,0.7);"><?php echo (int) $pkg->hours; ?> hrs</span>
                            <?php endif; ?>
                            <?php if ( $pkg->tagline ) : ?>
                                <span style="color:rgba(255,255,255,0.5);"><?php echo esc_html( $pkg->tagline ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(function($){
    $('#es-1to1-search').on('input', function(){
        var q = $(this).val().toLowerCase();
        $('#es-1to1-list .es-1to1-item').each(function(){
            $(this).toggle( $(this).data('name').indexOf(q) !== -1 );
        });
    });
});
</script>
