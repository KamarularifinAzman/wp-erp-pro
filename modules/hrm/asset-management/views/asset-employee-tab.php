<?php
$assets         = erp_hr_get_assets( intval( $employee->user_id ) );
$categories     = erp_hr_get_asset_categories();
$asset_requests = erp_hr_get_asset_requests( $employee->user_id );
?>

<div class="postbox leads-actions erp-hr-employee-assets-add">
    <div class="postbox-header">
        <h3 class="hndle"><?php esc_html_e( 'Assets', 'erp-pro' ); ?></h3>

        <div class="handle-actions hide-if-no-js">
            <button type="button" class="handlediv" aria-expanded="true">
                <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                <span class="toggle-indicator" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="inside">
        <?php if ( count( $assets ) > 0 ): ?>
            <table class="widefat striped" style="margin-bottom:15px;">
                <thead>
                    <tr>
                        <th><?php _e( 'Asset Code', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Item Name', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Given Date', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Return Date', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Instructions', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Status', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Actions', 'erp-pro' ); ?></th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                        foreach ( $assets as $asset ) {
                    ?>
                        <tr>
                            <td><?php echo esc_html( $asset->item_id ); ?></td>
                            <td><?php echo esc_html( $asset->item_group ); ?></td>
                            <td><?php echo erp_format_date( esc_html( $asset->date_given ) ); ?></td>
                            <td><?php echo '0000-00-00' != $asset->date_return_proposed ? erp_format_date( esc_html( $asset->date_return_proposed ) ) : '&mdash;'; ?></td>
                            <td><span title="<?php echo $asset->reply_msg; ?>" style="cursor:help" class="dashicons dashicons-format-aside erp-tips"></span></td>
                            <td><?php echo esc_html( ucwords( str_replace( '_', ' ', $asset->status ) ) ); ?></td>
                            <td width="10%">
                                <?php if ( !current_user_can( 'erp_hr_manager', $employee->user_id ) && current_user_can( 'employee', $employee->user_id ) && 'allotted' == $asset->status ) { ?>
                                    <div class="row-actions erp-hide-print">
                                        <a title="<?php _e( 'Request Return', 'erp-pro' ); ?>" href="#" class="allott-request-return" data-id="<?php echo $asset->id; ?>"><span class="dashicons dashicons-undo"></span></a>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php
                        }
                    ?>
                </tbody>
            </table>

        <?php else: ?>
            <p><?php _e( 'No Assets Found', 'erp-pro' ); ?></p>
        <?php endif; ?>

        <?php if ( erp_hr_assets_get_count([]) !== '0' ) : ?>

        <?php if ( current_user_can( 'erp_hr_manager', $employee->user_id ) ) : ?>
            <a class="button button-secondary erp-hide-print" id="erp-hr-emp-add-asset" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->user_id ) ); ?>'  data-template="erp-hr-emp-add-asset" data-title="<?php esc_attr_e( 'Asset', 'erp-pro' ); ?>" data-button="<?php esc_attr_e( 'Assign', 'erp-pro' ); ?>"><?php _e( '+ Add Asset', 'erp-pro' ); ?></a>
        <?php endif; endif; ?>
    </div>
</div>


<div class="postbox leads-actions erp-hr-employee-assets-request">
    <div class="postbox-header">
        <h3 class="hndle"><?php esc_html_e( 'Assets Requests', 'erp-pro' ); ?></h3>

        <div class="handle-actions hide-if-no-js">
            <button type="button" class="handlediv" aria-expanded="true">
                <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                <span class="toggle-indicator" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="inside">
        <?php if ( count( $asset_requests ) > 0 ) : ?>
            <table class="widefat striped" style="margin-bottom:15px;">
                <thead>
                <tr>
                    <th><?php _e( 'Requested Date', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Category', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Item', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Status', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Messages', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Actions', 'erp-pro' ); ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                foreach ( $asset_requests as $request ) {
                    ?>
                    <tr>
                        <td><?php echo erp_format_date( esc_html( $request->date_requested ) ); ?></td>
                        <td><?php echo 'on' == $request->not_in_list ? '' : esc_html( $request->category_name ); ?></td>
                        <td><?php echo 'on' == $request->not_in_list ? esc_html( $request->request_desc ) : esc_html( $request->item_name ); ?></td>
                        <td><?php echo ucfirst( $request->status ); ?></td>
                        <td><span title="<?php echo $request->reply_msg; ?>" style="cursor:help" class="dashicons dashicons-format-aside erp-tips"></span></td>

                        <td width="10%">
                            <?php if ( current_user_can( 'erp_edit_employee', $employee->user_id ) ) { ?>
                                <div class="row-actions erp-hide-print">
                                    <?php if ( 'pending' == $request->status ) { ?>
                                    <a href="#" title="<?php _e( 'Edit Request', 'erp-pro' ); ?>" class="emp-asset-request-edit" data-title="<?php esc_attr_e( 'Edit Asset Request', 'erp-asset' ); ?>" data-row_id='<?php echo $request->id; ?>' data-button="<?php esc_attr_e( 'Update Asset', 'erp-asset' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                    <a href="#" title="<?php _e( 'Delete Request', 'erp-pro' ); ?>" class="emp-asset-request-delete" data-id="<?php echo $request->id; ?>" data-action="erp-hr-emp-delete-asset"><span class="dashicons dashicons-trash"></span></a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e( 'No Request Found', 'erp-pro' ); ?></p>
        <?php endif; ?>

        <?php if ( !current_user_can( 'erp_hr_manager', $employee->user_id ) && current_user_can( 'employee', $employee->user_id ) ) : ?>
            <a class="button button-secondary erp-hide-print" id="erp-hr-emp-request-asset" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->user_id ) ); ?>'  data-template="erp-hr-emp-request-asset" data-title="<?php esc_attr_e( 'Asset Request', 'erp-pro' ); ?>" data-button="<?php esc_attr_e( 'Submit Request', 'erp-pro' ); ?>"><?php _e( 'Request Asset', 'erp-pro' ); ?></a>
        <?php endif; ?>
    </div>
</div>

