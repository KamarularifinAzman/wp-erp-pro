<div class="form-group">

    <ul>
        <li class="policy-form-list-item margin-bottom-5">
            <?php erp_html_form_input(array(
                'label'   => esc_html__('Carryover Days', 'erp-pro'),
                'name'    => 'carryover-days',
                'value'   => ! empty( $leave_policy ) ? $leave_policy->carryover_days : '0',
                'type'    => 'number',
                'tooltip' => esc_attr__( 'Maximum days employees will be able to carry available leaves to next year', 'erp-pro' ),
            )); ?>

            <div>
                <?php erp_html_form_input(array(
                    'label'   => esc_html__('Expired by', 'erp-pro'),
                    'name'    => 'carryover-uses-limit',
                    'value'   => ! empty( $leave_policy ) ? $leave_policy->carryover_uses_limit : '0',
                    'type'    => 'number',
                )); ?>
            </div>

            <div><?php esc_attr_e( 'Days', 'erp-pro' ); ?></div>
        </li>
        <p class="description"><?php echo esc_attr__( 'Enter 0 (zero) to disable carryover feature for this policy.' ) ?></p>

        <li class="policy-form-list-item margin-bottom-5">
            <?php erp_html_form_input(array(
                'label'   => esc_html__('Encashment Days', 'erp-pro'),
                'name'    => 'encashment-days',
                'value'   =>  ! empty( $leave_policy ) ? $leave_policy->encashment_days : '0',
                'type'    => 'number',
                'tooltip' => esc_attr__( 'Maximum days employees will be able to encash available leaves.', 'erp-pro' ),
            )); ?>

            <div>
                <?php //esc_attr_e( 'Calculate On', 'erp-pro' ); ?>

                <?php erp_html_form_input(array(
                    'label'   => esc_attr__( 'Calculate On', 'erp-pro' ),
                    'name'    => 'encashment-based-on',
                    'value'   => 'pay_rate',
                    'type'    => 'select',
                    'options'  => apply_filters('erp-pro-hr-leave-pay-type-options',
                        array( 'pay_rate' => 'Pay Rate' )
                    )
                )); ?>
            </div>
        </li>
        <p class="description"><?php echo esc_attr__( 'Enter 0 (zero) to disable encashment feature for this policy.' ) ?></p>

        <li class="policy-form-list-item forward-default-box">
            <?php erp_html_form_input(array(
                'label'   => esc_html__('Priority', 'erp-pro'),
                'name'    => 'forward-default',
                'value'   => ! empty( $leave_policy ) ? $leave_policy->forward_default : 'encashment',
                'type'    => 'radio',
                'options' => array( 'carryover' => 'Carryover', 'encashment'=> 'Encashment' ),
                'tooltip' => esc_attr__( 'Starting point for calculation.', 'erp-pro' ),
            )); ?>
        </li>
    </ul>
</div>
