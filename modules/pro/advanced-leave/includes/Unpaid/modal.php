<?php

use WeDevs\ERP\HRM\Models\FinancialYear;

$financial_years = wp_list_pluck( FinancialYear::all()->toArray(), 'fy_name', 'id' );
?>

<div class="calculate-form">
    <div class="row">
        <?php
        erp_html_form_input(array(
            'label'    => esc_html__('Leave Year', 'erp'),
            'name'     => 'f-year',
            'value'    => '',
            'required' => true,
            'class'    => 'erp-hrm-select2-add-more',
            'type'     => 'select',
            'options'  => $financial_years
        )); ?>
    </div>

    <div class="row">
        <?php
        erp_html_form_input(array(
            'label'    => esc_html__('Calculate On', 'erp-pro'),
            'name'     => 'salary-type',
            'value'    => '',
            'required' => true,
            'class'    => 'erp-hrm-select2-add-more',
            'type'     => 'select',
            'options'  => apply_filters('erp-pro-hr-leave-pay-type-options',
                array( 'pay_rate' => 'Pay Rate' )
            )
        )); ?>
    </div>

    <?php wp_nonce_field('erp-pro-hr-leave'); ?>
    <input type="hidden" name="action" value="erp_pro_hr_unpaid_leave_calc">

</div>
