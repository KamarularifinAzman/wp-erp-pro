<div class="erp-request-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Resign Date', 'erp-pro' ),
            'name'        => 'resign_date',
            'value'       => '{{data.resign_date}}',
            'required'    => true,
            'id'          => 'erp-resign-date',
            'custom_attr' => [ 'autocomplete' => 'off' ],
            'class'       => 'erp-date-field',
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.resign_reason }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Resign Reason', 'erp-pro' ),
            'name'     => 'resign_reason',
            'value'    => '',
            'class'    => 'erp-hrm-select2',
            'required' => true,
            'type'     => 'select',
            'id'       => 'resign_reason',
            'options'  => [ '' => __( 'Select a reason', 'erp-pro' ) ] + erp_hr_get_resignation_reason(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.resign_details }}">
        <?php erp_html_form_input( [
            'label'       => __( 'Reason Details', 'erp-pro' ),
            'type'        => 'textarea',
            'id'          => 'erp-resign-details',
            'name'        => 'resign_details',
            'placeholder' => esc_html__( 'It will be the body of email. Leave it blank to send default texts.', 'erp-pro' ),
            'custom_attr' => [
                'cols' => 51,
                'rows' => 9,
            ],
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_resign_request' ); ?>

    <input type="hidden" name="action" id="employee-resign-action" value="erp_hr_employee_resign">

    <input type="hidden" name="user_id" id="emp-id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
</div>
