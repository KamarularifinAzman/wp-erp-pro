<?php

/**
 * Migrates erp_hr_payroll_pay_calendar table data
 *
 * @return void
 */
function erp_payroll_migrate_pay_calender_data_2_0_0() {
    global $wpdb;

    $pay_cal_tbl   = $wpdb->prefix . 'erp_hr_payroll_pay_calendar';
    $existing_data = $wpdb->get_results( "SELECT id, pay_calendar_type FROM $pay_cal_tbl", ARRAY_A );

    if ( ! empty( $existing_data ) ) {
        $pay_cal_types = [
            'Monthly'   => 'monthly',
            'Weekly'    => 'weekly',
            'Bi-Weekly' => 'biweekly',
        ];
        
        foreach ( $existing_data as $data ) {
            $type = array_key_exists( $data['pay_calendar_type'], $pay_cal_types ) ? $pay_cal_types[ $data['pay_calendar_type'] ] : '';

            if ( empty( $type ) || ! in_array( $type, [ 'monthly', 'weekly', 'biweekly' ], true ) ) {
                continue;
            }
            
            $wpdb->update(
                $pay_cal_tbl,
                [ 'pay_calendar_type' => $type ],
                [ 'id' => $data['id'] ],
                [ '%s' ],
                [ '%d' ]
            );
        }
    }
}

/**
 * Migrates erp_hr_payroll_calendar_type_settings table data
 *
 * @return void
 */
function erp_payroll_alter_calendar_type_settings_data_2_0_0() {
    global $wpdb;

    $type_settings_tbl_cols = $wpdb->get_col( "DESC {$wpdb->prefix}erp_hr_payroll_calendar_type_settings" );

    if ( in_array( 'cal_type', $type_settings_tbl_cols ) ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}erp_hr_payroll_calendar_type_settings DROP COLUMN `cal_type`;"
        );
    }
}

erp_payroll_migrate_pay_calender_data_2_0_0();
erp_payroll_alter_calendar_type_settings_data_2_0_0();