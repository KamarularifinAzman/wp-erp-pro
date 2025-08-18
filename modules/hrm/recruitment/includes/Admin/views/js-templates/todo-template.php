<div class="wrap erp">
    <div class="todo-template-container">
        <div class="row">
            <label><?php _e( 'Title (required)', 'erp-pro' ); ?></label>
            <input name="todo_title" id="todo_title" value="" tabindex="1" type="text">
        </div>
        <div class="row">
            <?php $employee_list = erp_hr_get_employees_dropdown_raw(); unset($employee_list[0]);?>
            <label><?php _e( 'Assign user (required)', 'erp-pro' ); ?></label><?php erp_html_form_input( array(
                'label'       => __( '', 'erp-pro' ),
                'name'        => 'assign_user_id[]',
                'value'       => '',
                'type'        => 'select',
                'id'          => 'assign_user_id',
                'custom_attr' => [ 'multiple' => 'multiple', 'data-placeholder' => 'Select an employee' ],
                'class'       => 'multiple-selection',
                'options'     => $employee_list,
                'required'    => true
            ) ); ?>
        </div>
        <div class="row">
            <label><?php _e( 'Dead Line', 'erp-pro' ); ?></label><?php erp_html_form_input( array(
                'label'       => __( '', 'erp-pro' ),
                'name'        => 'deadlinedate',
                'value'       => '',
                'type'        => 'text',
                'class'       => 'erp-date-field-todo-deadline',
                'custom_attr' => [ 'placeholder' => esc_attr__( 'Date', 'erp-pro' ) ],
                'required'    => false
            ) ); ?>

            <?php erp_html_form_input( array(
                'label'       => __( '', 'erp-pro' ),
                'name'        => 'deadlinetime',
                'value'       => '',
                'type'        => 'text',
                'class'       => 'erp-time-field',
                'custom_attr' => [ 'placeholder' => esc_attr__( 'Time', 'erp-pro' ) ],
                'required'    => false
            ) ); ?>
        </div>
        <input type="hidden" id="todo_application_id" name="todo_application_id" value="">
    </div>
</div>
