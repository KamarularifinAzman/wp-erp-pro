<?php if ( count( $requests ) > 0 ) : ?>
    <?php foreach ( $requests as $num => $request ) : ?>
        <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
            <td><?php echo $request['start_date']; ?></td>

            <td><?php echo $request['end_date']; ?></td>

            <td><?php echo $request['reason']['title']; ?></td>

            <td><?php echo $request['status']['title']; ?></td>

            <td class="cen-align">
                <div class="erp-row-action-dropdown">
                    <a href="#"
                        class="erp-row-actions-btn <?php echo 'pending' !== $request['status']['id'] || ! erp_hr_is_employee_active() ? 'disabled' : '' ?>"
                        data-id="<?php echo esc_attr( $request['id'] ); ?>">
                        <span class="dashicons dashicons-ellipsis"></span>
                    </a>

                    <div id="request-row-actions-<?php echo esc_attr( $request['id'] ); ?>" class="dropdown-content">
                        <a href="#"
                            id="erp-hr-edit-remote-work-history"
                            data-id="<?php echo esc_attr( $request['id'] ); ?>"
                            data-template="erp-employee-remote-work-request"
                            data-nonce="<?php echo wp_create_nonce( 'employee_remote_work_request' ); ?>"
                            data-title="<?php esc_attr_e( 'Update Request', 'erp-pro' ); ?>">
                            <span class="dashicons dashicons-edit edit"></span>
                            <?php _e( 'Edit', 'erp' ); ?>
                        </a>

                        <a href="#"
                            id="erp-hr-del-remote-work-history"
                            data-id="<?php echo esc_attr( $request['id'] ); ?>"
                            data-action="erp_hr_employee_delete_remote_work_req"
                            data-title="<?php esc_attr_e( 'Delete Request?', 'erp-pro' ); ?>"
                            data-nonce="<?php echo wp_create_nonce( 'employee_remote_work_request' ); ?>">
                            <span class="dashicons dashicons-trash delete"></span>
                            <?php _e( 'Delete', 'erp' ); ?>
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else : ?>
    <tr colspan="4">
        <td><?php _e( 'No history found.', 'erp-pro' ); ?></td>
    </tr>
<?php endif; ?>