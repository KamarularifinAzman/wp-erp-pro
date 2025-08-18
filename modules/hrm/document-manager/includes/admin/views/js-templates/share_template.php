<div class="share_template_wrap">


    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Share files by', 'erp-pro' ),
            'name'        => 'share_by',
            'class'       => 'share_by',
            'type'        => 'select',
            'options' => array(
                "all_employees"  => __( 'All employees', 'erp-pro' ),
                "by_department"  => __( 'By department', 'erp-pro' ),
                "by_designation" => __( 'By designation', 'erp-pro' ),
                "by_employee"    => __( 'By employee', 'erp-pro' ),
            )
        ) ); ?>
    </div>

    <div class="row by_department no-show">
        <?php erp_html_form_input( array(
            'label'       => __( 'Department', 'erp-pro' ),
            'name'        => 'department',
            'class'       => 'doc-select-dropdown',
            'type'        => 'select',
            'options'     => erp_hr_get_departments_dropdown_raw( __( 'All Department', 'erp-pro' ) )
        ) ); ?>
    </div>

    <div class="row by_designation no-show">
        <?php erp_html_form_input( array(
            'label'       => __( 'Designation', 'erp-pro' ),
            'name'        => 'designation',
            'class'       => 'doc-select-dropdown',
            'type'        => 'select',
            'options'     => erp_hr_get_designation_dropdown_raw( __( 'All Designations', 'erp-pro' ) )
        ) ); ?>
    </div>

    <div class="row by_employee no-show">
        <?php
        $emp = erp_hr_get_employees_dropdown_raw();
        unset($emp[0]);
        erp_html_form_input(array(
            'label'       => __('Selected employees', 'erp-pro'),
            'name'        => 'selected_emp[]',
            'class'       => 'doc-select-dropdown',
            'type'        => 'select',
            'options'     => $emp,
            'custom_attr' => array(
                'multiple' => 'multiple'
            ),
        )); ?>
    </div>
    <?php
        if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
            $user_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
        } else {
            $user_id = get_current_user_id();
        }
    ?>
    <input type="hidden" name="owner_id" value="<?php echo $user_id ;?>">

</div>



