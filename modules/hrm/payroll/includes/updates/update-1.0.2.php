<?php
/**
 * Update 3 amount holder table
 *
 * @since 1.0.2
 *
 * @return void
 */
function update_three_table_fields_type() {
    global $wpdb;
    //alter query for change three table pay_item_amount int to decimal(10,2)
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_fixed_payment CHANGE `pay_item_amount` `pay_item_amount` decimal(20,2)";
    $wpdb->query($query);
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction CHANGE `pay_item_amount` `pay_item_amount` decimal(20,2)";
    $wpdb->query($query);
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_payrun_detail CHANGE `pay_item_amount` `pay_item_amount` decimal(20,2)";
    $wpdb->query($query);
}
update_three_table_fields_type();
