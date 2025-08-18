<?php
/**
 *
 * @since 1.4.1
 *
 * @return void
 */
function erp_hr_payroll_update_1_4_1() {
    global $wpdb;
    $query1 = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_payrun_detail ADD `allowance` DECIMAL(20,2) NOT NULL DEFAULT '0' AFTER `pay_item_add_or_deduct`, ADD `deduction` DECIMAL(20,2) NOT NULL DEFAULT '0' AFTER `allowance`";
    $wpdb->query($query1);
    $query2 = "
	UPDATE {$wpdb->prefix}erp_hr_payroll_payrun_detail
	SET
	allowance = ( SELECT IF(pay_item_id > 0 AND pay_item_add_or_deduct = 1 , pay_item_amount , 0) ),
	deduction = ( SELECT IF(pay_item_id > 0 AND (pay_item_add_or_deduct = 0 OR pay_item_add_or_deduct = 2) , pay_item_amount , 0) ),
	pay_item_amount = ( SELECT IF(pay_item_id  < 0 AND pay_item_add_or_deduct = 1 , pay_item_amount , 0) )";

    $wpdb->query($query2);

    //alter query for change three table pay_item_amount int to decimal(20,2)
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_fixed_payment CHANGE `pay_item_amount` `pay_item_amount` decimal(20,2)";
    $wpdb->query($query);
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction CHANGE `pay_item_amount` `pay_item_amount` decimal(20,2)";
    $wpdb->query($query);
    $query = "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_payrun_detail CHANGE `pay_item_amount` `pay_item_amount` decimal(20,2)";
    $wpdb->query($query);


  /*  $wpdb->insert(
        $wpdb->prefix . 'erp_hr_payroll_payitem',
        array(
            'type'                   => 'Allowance',
            'payitem'                => 'Basic Pay',
            'pay_item_add_or_deduct' => 1
        )
    );

    $inserted_item_id = $wpdb->insert_id;
    $query3 = "UPDATE {$wpdb->prefix}erp_hr_payroll_payrun_detail SET pay_item_id = {$inserted_item_id} WHERE pay_item_id = -1";
    $wpdb->query($query3);
  */
}

erp_hr_payroll_update_1_4_1();
