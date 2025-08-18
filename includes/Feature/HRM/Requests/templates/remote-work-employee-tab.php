<h3><?php esc_html_e( 'Request History', 'erp-pro' ); ?></h3>

<form action="#" id="erp-hr-empl-remote-work-filter">
    <div class="erp-hr-rw-filters">
        <div data-selected="<?php echo intval( $cur_year ); ?>">
            <?php erp_html_form_input( [
                'name'     => 'year',
                'value'    => '',
                'class'    => 'erp-hrm-select2',
                'required' => true,
                'type'     => 'select',
                'id'       => 'rw-filter-date',
                'options'  => $years
            ] ); ?>
        </div>
    
        <div data-selected="-1">
            <?php erp_html_form_input( [
                'name'     => 'status',
                'class'    => 'erp-hrm-select2',
                'required' => true,
                'type'     => 'select',
                'id'       => 'rw-filter-status',
                'options'  => [ '-1' => __( 'All Status', 'erp-pro' ) ] + $statuses
            ] ); ?>
        </div>
    
        <?php wp_nonce_field( 'employee_remote_work_request' ); ?>

        <input type="hidden" name="action" value="erp_hr_employee_filter_remote_work_history">

        <input type="hidden" name="id" value="<?php echo esc_attr( get_current_user_id() ); ?>">

        <?php submit_button( esc_html__( 'Filter', 'erp-pro' ), 'secondary', 'submit', false ); ?>
    </div>
</form>


<table class="widefat" id="erp-hr-empl-remote-work-history">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Date From', 'erp-pro' ); ?></th>

            <th><?php esc_html_e( 'Date To', 'erp-pro' ); ?></th>

            <th><?php esc_html_e( 'Reason', 'erp-pro' ); ?></th>

            <th><?php esc_html_e( 'Status', 'erp-pro' ); ?></th>
            
            <th class="cen-align"><?php esc_html_e( 'Actions', 'erp-pro' ); ?></th>
        </tr>
    </thead>

    <tbody id="erp-hr-remote-work-history">
        <?php include_once ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/remote-work-history-data.php'; ?>
    </tbody>
</table>