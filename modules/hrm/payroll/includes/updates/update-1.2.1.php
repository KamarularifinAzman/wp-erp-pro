<?php
/**
 * Update 3 amount holder table
 *
 * @since 1.0.2
 *
 * @return void
 */
function erp_hr_payroll_calendar_type_settings_add_one_column() {
    global $wpdb;
    //alter query for change three table pay_item_amount int to decimal(10,2)
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_calendar_type_settings ADD `pay_day_mode` INT(1) NOT NULL DEFAULT 0 AFTER `custom_month_day`";
    $wpdb->query($query);
}
erp_hr_payroll_calendar_type_settings_add_one_column();
