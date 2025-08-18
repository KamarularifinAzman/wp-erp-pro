<div class="erp-request-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Date From', 'erp-pro' ),
            'name'        => 'start_date',
            'value'       => '{{ data.start_date }}',
            'required'    => true,
            'id'          => 'erp-remote-work-date-from',
            'custom_attr' => [ 'autocomplete' => 'off' ],
            'class'       => 'erp-date-field',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Date To', 'erp-pro' ),
            'name'        => 'end_date',
            'value'       => '{{ data.end_date }}',
            'required'    => true,
            'id'          => 'erp-remote-work-date-to',
            'custom_attr' => [ 'autocomplete' => 'off' ],
            'class'       => 'erp-date-field',
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.reason.id }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Reason', 'erp-pro' ),
            'name'     => 'reason',
            'value'    => '',
            'class'    => 'erp-hrm-select2',
            'required' => true,
            'type'     => 'select',
            'id'       => 'rw_reason',
            'options'  => [ '' => __( 'Select a reason', 'erp-pro' ) ] + erp_hr_get_remote_work_reason(),
        ] ); ?>
    </div>

    <div class="row {{ data.reason.id != 'other' ? 'hide' : '' }}" id="erp-rw-other-reason">
        <?php erp_html_form_input( [
            'label'       => __( 'Other Reason', 'erp-pro' ),
            'type'        => 'textarea',
            'id'          => 'rw_other_reason',
            'name'        => 'other_reason',
            'class'       => 'erp-hr-rw-other-reason',
            'value'       => '{{ data.reason.others }}',
            'required'    => false,
            'custom_attr' => [
                'cols' => 30,
                'rows' => 5,
            ],
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_remote_work_request' ); ?>

    <input type="hidden" name="user_id" id="emp-id" value="<?php echo esc_attr( get_current_user_id() ); ?>">

    <# if ( data.id ) { #>
        <input type="hidden" name="req_id" id="request-id" value="{{ data.id }}">

        <input type="hidden" name="action" id="employee-remote-work-action" value="erp_hr_employee_edit_remote_work_req">
    <# } else { #>
        <input type="hidden" name="action" id="employee-remote-work-action" value="erp_hr_employee_remote_work_request">
    <# } #>
</div>