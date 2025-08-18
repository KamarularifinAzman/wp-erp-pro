<div class="forward-form">

    <div class="row">
        <?php
            erp_html_form_input( array(
                'label'    => esc_html__( 'Forward to', 'erp-pro' ),
                'name'     => 'forward_to',
                'class'    => 'erp-hrm-select2',
                'type'     => 'select',
                'id'       => 'forward_to',
                'required' => true,
                'value'    => 0,
                'options'  => erp_pro_hr_leave_get_only_hr_managers()
            ) );
        ?>
    </div>

    <div class="row">
        <?php
            erp_html_form_input(array(
                'label'    => esc_html__('Reason', 'erp-pro'),
                'name'     => 'reason',
                'value'    => '',
                'required' => true,
                'type'     => 'textarea',
                'custom_attr' => array(
                    'rows' => 5,
                    'cols' => 60
                )
            ));
        ?>
    </div>

    <?php wp_nonce_field('erp-pro-hr-leave'); ?>
    <input type="hidden" name="action" value="erp_pro_hr_leave_forward">

</div>