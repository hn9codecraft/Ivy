<?php
/**
 * Groups admin page
 * URL: admin.php?page=eduschedule-groups&group_id=X
 *
 * Vars in scope:
 *   $groups   = array of stdClass (all groups)
 *   $selected = stdClass|null
 *   $gid      = int
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$base         = admin_url( 'admin.php?page=eduschedule-groups' );
$all_packages = ES_Packages::get_all( false );
?>
<div class="es-admin es-groups-page">

    <div class="es-page-head">
        <div>
            <h1>Groups</h1>
            <p class="es-page-sub">Manage student groups &mdash; <?php echo count( $groups ); ?> total</p>
        </div>
        <div class="es-page-actions">
            <button type="button" class="es-btn es-btn-primary" id="es-add-group-btn">
                <span class="dashicons dashicons-plus"></span> New Group
            </button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:16px;align-items:flex-start;">

        <!-- LEFT: List -->
        <div class="es-card" style="padding:12px;">
            <input type="text" id="es-group-search" placeholder="Search groups..." style="width:100%;padding:8px 12px;margin-bottom:10px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.08);border-radius:6px;color:#fff;font-size:13px;" />

            <div id="es-group-list" style="display:flex;flex-direction:column;gap:4px;max-height:600px;overflow-y:auto;">
                <?php if ( empty( $groups ) ) : ?>
                    <p class="es-empty-cell" style="text-align:center;font-size:12px;">No groups yet. Click "New Group" to create one.</p>
                <?php else : foreach ( $groups as $g ) :
                    $initial = strtoupper( substr( $g->group_name, 0, 2 ) );
                    $active  = ( (int) $g->id === (int) $gid );
                    $color   = $g->color ?: '#6366f1';
                    $count   = ES_Packages::count_group_members( $g->id );
                ?>
                    <a href="<?php echo esc_url( add_query_arg( 'group_id', $g->id, $base ) ); ?>"
                       class="es-group-item <?php echo $active ? 'is-active' : ''; ?>"
                       data-name="<?php echo esc_attr( strtolower( $g->group_name ) ); ?>"
                       style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;text-decoration:none;color:inherit;background:<?php echo $active ? 'rgba(99,102,241,0.15)' : 'transparent'; ?>;border:1px solid <?php echo $active ? 'rgba(99,102,241,0.4)' : 'transparent'; ?>;">
                        <div style="width:36px;height:36px;border-radius:50%;background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0;">
                            <?php echo esc_html( $initial ); ?>
                        </div>
                        <div style="flex:1;min-width:0;overflow:hidden;">
                            <div style="font-size:13px;font-weight:500;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo esc_html( $g->group_name ); ?>
                            </div>
                            <div style="font-size:11px;color:rgba(255,255,255,0.55);">
                                <?php echo (int) $count; ?> member<?php echo $count === 1 ? '' : 's'; ?>
                                <?php if ( $g->duration ) : ?> · <?php echo esc_html( $g->duration ); ?><?php endif; ?>
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
                    <div style="font-size:48px;margin-bottom:12px;">👥</div>
                    <div style="font-size:14px;">Select a group from the list, or create a new one</div>
                </div>
            <?php else :
                $members = ES_Packages::get_group_members( $selected->id );
                $pkg     = $selected->package_id ? ES_Packages::get( $selected->package_id ) : null;
                $color   = $selected->color ?: '#6366f1';
            ?>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                    <div style="width:50px;height:50px;border-radius:50%;background:<?php echo esc_attr( $color ); ?>22;color:<?php echo esc_attr( $color ); ?>;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:600;">
                        <?php echo esc_html( strtoupper( substr( $selected->group_name, 0, 2 ) ) ); ?>
                    </div>
                    <div style="flex:1;">
                        <h2 style="margin:0 0 4px;font-size:18px;color:#fff;"><?php echo esc_html( $selected->group_name ); ?></h2>
                        <div style="font-size:12px;color:rgba(255,255,255,0.6);">
                            <?php if ( $pkg ) : ?><?php echo esc_html( $pkg->package_name ); ?> · <?php endif; ?>
                            <?php echo count( $members ); ?> members
                            <?php if ( $selected->duration ) : ?> · <?php echo esc_html( $selected->duration ); ?><?php endif; ?>
                        </div>
                    </div>
                    <button type="button" class="es-btn es-btn-ghost es-edit-group-btn"
                            data-id="<?php echo (int) $selected->id; ?>">
                        <span class="dashicons dashicons-edit"></span> Edit
                    </button>
                    <button type="button" class="es-btn es-btn-danger es-delete-group-btn"
                            data-id="<?php echo (int) $selected->id; ?>">
                        <span class="dashicons dashicons-trash"></span> Delete
                    </button>
                </div>

                <?php if ( $pkg ) : ?>
                    <div class="es-card" style="padding:16px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);margin-bottom:16px;">
                        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Linked Package</div>
                        <div style="font-size:15px;font-weight:600;color:#fff;"><?php echo esc_html( $pkg->package_name ); ?></div>
                    </div>
                <?php endif; ?>

                <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);margin:16px 0 8px;text-transform:uppercase;letter-spacing:0.5px;">
                    Members (<?php echo count( $members ); ?>)
                </div>
                <?php if ( empty( $members ) ) : ?>
                    <p class="es-empty-cell" style="font-size:13px;">No members yet. Convert demo leads via After Call → "Group Student" to add members.</p>
                <?php else : ?>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                    <?php foreach ( $members as $m ) :
                        $u_url = admin_url( 'admin.php?page=eduschedule-students&view=detail&user_id=' . $m->ID );
                    ?>
                        <div style="display:flex;align-items:center;gap:10px;padding:10px;background:rgba(0,0,0,0.15);border-radius:8px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:rgba(99,102,241,0.15);color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;">
                                <?php echo esc_html( strtoupper( substr( $m->display_name, 0, 2 ) ) ); ?>
                            </div>
                            <div style="flex:1;">
                                <div style="font-size:13px;color:#fff;"><?php echo esc_html( $m->display_name ); ?></div>
                                <div style="font-size:11px;color:rgba(255,255,255,0.55);"><?php echo esc_html( $m->user_email ); ?></div>
                            </div>
                            <a href="<?php echo esc_url( $u_url ); ?>" class="es-btn es-btn-ghost es-btn-sm">
                                View
                            </a>
                            <button type="button" class="es-btn es-btn-ghost es-btn-sm es-remove-member-btn"
                                    data-group="<?php echo (int) $selected->id; ?>"
                                    data-user="<?php echo (int) $m->ID; ?>"
                                    style="color:#f87171;">
                                Remove
                            </button>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============ Add/Edit Group Modal ============ -->
<div class="es-modal" id="es-group-modal" style="display:none">
    <div class="es-modal-backdrop"></div>
    <div class="es-modal-card">
        <div class="es-modal-head">
            <h2 id="es-group-modal-title">New Group</h2>
            <button type="button" class="es-modal-close" aria-label="Close">×</button>
        </div>
        <div class="es-modal-body">
            <input type="hidden" id="es-group-id" value="" />

            <div class="es-field">
                <label class="es-label">Group Name</label>
                <input type="text" id="es-group-name" placeholder="e.g. IELTS Batch A" />
            </div>

            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Linked Package</label>
                    <select id="es-group-package">
                        <option value="">— None —</option>
                        <?php foreach ( $all_packages as $p ) : ?>
                            <option value="<?php echo (int) $p->id; ?>"><?php echo esc_html( $p->package_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="es-field">
                    <label class="es-label">Duration</label>
                    <input type="text" id="es-group-duration" placeholder="e.g. 2 Months" />
                </div>
            </div>

            <div class="es-modal-row">
                <div class="es-field">
                    <label class="es-label">Total Sessions</label>
                    <input type="number" id="es-group-total" placeholder="16" min="0" />
                </div>
                <div class="es-field">
                    <label class="es-label">Color</label>
                    <input type="color" id="es-group-color" value="#6366f1" />
                </div>
            </div>

            <div class="es-field">
                <label class="es-label">Notes</label>
                <textarea id="es-group-notes" rows="2" placeholder="Optional notes..."></textarea>
            </div>
        </div>
        <div class="es-modal-foot">
            <button type="button" class="es-btn es-btn-ghost es-modal-close">Cancel</button>
            <button type="button" class="es-btn es-btn-primary" id="es-group-save">
                <span id="es-group-save-text">Create Group</span>
            </button>
        </div>
    </div>
</div>

<script>
jQuery(function($){
    $('#es-group-search').on('input', function(){
        var q = $(this).val().toLowerCase();
        $('#es-group-list .es-group-item').each(function(){
            $(this).toggle( $(this).data('name').indexOf(q) !== -1 );
        });
    });
});
</script>
