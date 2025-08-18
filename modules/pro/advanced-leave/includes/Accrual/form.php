<div class="form-group">

    <ul>
        <li class="policy-form-list-item">
            <?php erp_html_form_input(array(
                'label'       => esc_html__('Amount Accrued', 'erp'),
                'name'        => 'accrued-amount',
                'value'       => ! empty( $leave_policy ) ? $leave_policy->accrued_amount : '0',
                'type'        => 'number',
                'custom_attr' => array( 'step' => '0.1' ),
            )); ?>
            <div>
                <?php echo esc_attr__('Days per month', 'erp-pro'); ?>
            </div>
        </li>

        <li class="policy-form-list-item">
            <?php erp_html_form_input(array(
                'label' => esc_html__('Maximum Accrual', 'erp-pro'),
                'name'  => 'accrued-max-days',
                'value' => ! empty( $leave_policy ) ? $leave_policy->accrued_max_days : '0',
                'type'  => 'number'
            )); ?>
            <div>Days</div>
        </li>
    </ul>
</div>
