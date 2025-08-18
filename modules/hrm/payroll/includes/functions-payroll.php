<?php

/** Callbacks **/

function erp_payroll_overview_widget_left_callback() {
    erp_admin_dash_metabox( __( '<i class="fa fa-file-text-o"></i> Payroll History of Current Month', 'erp-pro' ), 'erp_payroll_overview_widget_paid_history' );
}

function erp_payroll_overview_widget_paid_history() { ?>
    <canvas id="salary-chart" width="1040" height="200"></canvas>
    <?php
}

/**
 * Get total pay item number
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_payroll_get_total_payitem() {
    global $wpdb;

    $query = "SELECT COUNT(payitem)
              FROM {$wpdb->prefix}erp_hr_payroll_payitem";

    return $wpdb->get_var( $query );
}

/**
 * Get total pay item category number
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_payroll_get_total_payitem_category() {
    global $wpdb;

    $query = "SELECT COUNT(payitem_category)
              FROM {$wpdb->prefix}erp_hr_payroll_payitem_category";

    return $wpdb->get_var( $query );
}

/**
 * Get total calendar created
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_payroll_get_total_calendar_created() {
    global $wpdb;

    $query = "SELECT COUNT(pay_calendar_name)
              FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar";

    return $wpdb->get_var( $query );
}

/**
 * Get total execution of all pay calendars
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_payroll_get_total_execution_pay_calendar() {
    global $wpdb;

    $query = "SELECT COUNT(pay_cal_id)
              FROM {$wpdb->prefix}erp_hr_payroll_payrun
              WHERE approve_status=1";

    return $wpdb->get_var( $query );
}

/**
 * Get total expense of all pay calendars
 *
 * @since 1.0.0
 *
 * @return float
 */
function erp_payroll_get_total_expense() {
    global $wpdb;

    $query               = "SELECT (SUM(pay_item_amount) + SUM(allowance)) - SUM(deduction) as total
                        FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as pemp
                        WHERE pemp.approve_status=1";

    return $wpdb->get_var( $query );

}

/**
 * Check setup wizard done or not
 *
 * @since 1.0.0
 *
 * @return bool
 */
function is_setup_wizard_done() {
    if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
        if ( get_option( 'erp_payroll_payment_method_settings' ) ) {
            return true;
        }

        return false;
    }

    $assets_account_head     = get_option( 'erp_payroll_account_head_assets' );
    $salary_account_head     = get_option( 'erp_payroll_account_head_salary' );
    $salary_tax_account_head = get_option( 'erp_payroll_account_head_salary_tax' );
    $payment_method_settings = get_option( 'erp_payroll_payment_method_settings', 'cash' );
    if ( $assets_account_head && $salary_account_head && $salary_tax_account_head && $payment_method_settings ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check pay calendar created or not
 *
 * @since 1.0.0
 *
 * @return array
 */
function is_pay_calendar_created() {
    global $wpdb;

    $query = "SELECT COUNT(pay_calendar_name)
              FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar";

    if ( $wpdb->get_var( $query ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check pay calendar created or not
 *
 * @since 1.0.0
 *
 * @return array
 */
function is_payrun_executed_ever() {
    global $wpdb;

    $query = "SELECT approve_status
              FROM {$wpdb->prefix}erp_hr_payroll_payrun
              WHERE approve_status=1";

    if ( count( $wpdb->get_results( $query ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get a cell value with one where condition
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_a_cell_value( $field, $table, $condition_field, $where_format, $condition_value ) {
    global $wpdb;

    if ( $where_format == 'int' ) {
        $wf = '%d';
    } elseif ( $where_format == 'string' ) {
        $wf = '%s';
    } else {
        $wf = '%f';
    }

    $query = "SELECT " . $field . "
              FROM {$wpdb->prefix}" . $table . "
              WHERE " . $condition_field . "=" . $wf;

    return $wpdb->get_var( $wpdb->prepare( $query, $condition_value ) );
}

/**
 * Check this employee has this pay item id or not
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_check_employee_has_this_payitem( $payitem_id, $empid ) {
    global $wpdb;
    $query = "SELECT id
              FROM {$wpdb->prefix}erp_hr_payroll_rules_settings
              WHERE payitem_id='%d' AND emp_id='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $payitem_id, $empid ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get employee list by department id
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_employee_id_list_by_department_id( $deptid ) {
    global $wpdb;
    $query = "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE department='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $deptid ), ARRAY_A ) ) > 0 ) {
        return $wpdb->get_results( $wpdb->prepare( $query, $deptid ), ARRAY_A );
    } else {
        return [];
    }
}

/**
 * Get employee list by designation id
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_employee_id_list_by_designation_id( $desigid ) {
    global $wpdb;
    $query = "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE designation='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $desigid ), ARRAY_A ) ) > 0 ) {
        return $wpdb->get_results( $wpdb->prepare( $query, $desigid ), ARRAY_A );
    } else {
        return [];
    }
}

/**
 * Get employee id by department id
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_employee_id( $deptid ) {
    global $wpdb;
    $query = "SELECT employee_id FROM {$wpdb->prefix}erp_hr_employees WHERE department='%d'";

    return $wpdb->get_var( $wpdb->prepare( $query, $deptid ) );
}

/**
 * Get department id of specific employee
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_department_id( $empid ) {
    global $wpdb;
    $query = "SELECT department FROM {$wpdb->prefix}erp_hr_employees WHERE user_id='%d'";

    return $wpdb->get_var( $wpdb->prepare( $query, $empid ) );
}

/**
 * Get designation id of specific employee
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_designation_id( $empid ) {
    global $wpdb;
    $query = "SELECT designation FROM {$wpdb->prefix}erp_hr_employees WHERE user_id='%d'";

    return $wpdb->get_var( $wpdb->prepare( $query, $empid ) );
}

/**
 * Check remarks exist or not
 *
 * @since 1.0.0
 *
 * @return bool
 */
function erp_payroll_check_remarks_exist( $payitem_id ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem_remakrs WHERE payitem_id='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $payitem_id ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get pay item name
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_pay_item_name() {
    global $wpdb;
    $query = "SELECT id, payitem
              FROM {$wpdb->prefix}erp_hr_payroll_payitem
              ORDER BY payitem";

    if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
        return $wpdb->get_results( $query, ARRAY_A );
    } else {
        return [];
    }
}

/**
 * Get pay item id
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_payroll_get_pay_item_id( $payitem ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem WHERE payitem='%s'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $payitem ), ARRAY_A ) ) > 0 ) {
        return $wpdb->get_var( $wpdb->prepare( $query, $payitem ) );
    } else {
        return 0;
    }
}

/**
 * Check duplicate pay item name or not
 *
 * @since 1.0.0
 *
 * @return bool
 */
function erp_payroll_check_duplicate_pay_item_name( $payitem_name ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem WHERE payitem='%s'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $payitem_name ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check duplicate pay item name or not during edit
 *
 * @since 1.0.0
 *
 * @return bool
 */
function erp_payroll_check_duplicate_pay_item_name_during_edit( $payitem_id, $payitem_name ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem WHERE id<>'%d' AND payitem='%s'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $payitem_id, $payitem_name ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get pay item category name
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_pay_item_category_name() {
    global $wpdb;
    $query = "SELECT id, payitem_category
              FROM {$wpdb->prefix}erp_hr_payroll_payitem_category
              ORDER BY payitem_category";

    if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
        return $wpdb->get_results( $query, ARRAY_A );
    } else {
        return [];
    }
}

/**
 * Get pay run data for graph
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_pay_run_data_by_date( $date ) {
    global $wpdb;

    $query               = "SELECT IFNULL((SUM(allowance) - SUM(deduction)),0) as total
                        FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as pemp
                        WHERE pemp.payment_date='%s'
                        AND pemp.approve_status=1";

    return $wpdb->get_var( $wpdb->prepare( $query, $date ) );

    //return $allowance_and_basic - $deduct;
}

/**
 * Get previous month spent amount
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_spent_of_previous_month() {
    global $wpdb;
    $last_month        = date( 'Y-m-d', strtotime( '-1 month' ) );

    $query = "SELECT ((SUM(pay_item_amount)+ SUM(allowance)) - SUM(deduction)) as total
              FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
              WHERE approve_status=1 AND MONTH(payment_date)=MONTH('%s') AND YEAR(payment_date)=YEAR('%s')";

    return $wpdb->get_var( $wpdb->prepare( $query, $last_month, $last_month ) );

}

/**
 * Get ordinary rate of employee
 *
 * @since 1.0.0
 *
 * @return number
 */
function erp_payroll_get_ordinary_rate( $emp_id ) {
    return (get_user_meta( $emp_id, 'ordinary_rate', true ) ? get_user_meta( $emp_id, 'ordinary_rate', true ) : 0);
}

/**
 * Get fixed amount of employee
 *
 * @since 1.0.0
 *
 * @return number
 */
function erp_payroll_get_fixed_pay( $emp_id ) {
    global $wpdb;

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
              WHERE empid='%d'
              AND pay_item_add_or_deduct=1";

    $add = $wpdb->get_var( $wpdb->prepare( $query, $emp_id ) );

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
              WHERE empid='%d'
              AND pay_item_add_or_deduct=0";

    $sub = $wpdb->get_var( $wpdb->prepare( $query, $emp_id ) );

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
              WHERE empid='%d'
              AND pay_item_add_or_deduct=2";

    $sub_tax = $wpdb->get_var( $wpdb->prepare( $query, $emp_id ) );

    return $add - $sub - $sub_tax;
}

/**
 * Get additional amount of employee of previous month
 *
 * @since 1.0.0
 *
 * @return number
 */
function erp_payroll_additional_payment_of_previous_month( $emp_id, $last_month ) {
    global $wpdb;

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
              WHERE empid='%d'
              AND MONTH(payment_date)=MONTH('%s')
              AND YEAR(payment_date)=YEAR('%s')
              AND pay_item_add_or_deduct=1";

    $add = $wpdb->get_var( $wpdb->prepare( $query, $emp_id, $last_month, $last_month ) );

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
              WHERE empid='%d'
              AND MONTH(payment_date)=MONTH('%s')
              AND YEAR(payment_date)=YEAR('%s')
              AND pay_item_add_or_deduct=0";

    $sub = $wpdb->get_var( $wpdb->prepare( $query, $emp_id, $last_month, $last_month ) );

    return $add - $sub;
}

/**
 * Get additional amount of employee
 *
 * @since 1.0.0
 *
 * @return number
 */
function erp_payroll_additional_payment( $emp_id ) {
    global $wpdb;

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction
              WHERE empid='%d'
              AND pay_item_add_or_deduct=1";

    $add = $wpdb->get_var( $wpdb->prepare( $query, $emp_id ) );

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction
              WHERE empid='%d'
              AND pay_item_add_or_deduct=0";

    $sub = $wpdb->get_var( $wpdb->prepare( $query, $emp_id ) );

    return $add - $sub;
}

/**
 * Get additional amount of employee
 *
 * @since 1.0.0
 *
 * @return number
 */
function erp_payroll_additional_payment_of_current_month( $payment_date, $emp_id ) {
    global $wpdb;

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
              WHERE empid='%d'
              AND payment_date='%s'
              AND pay_item_add_or_deduct=1";

    $add = $wpdb->get_var( $wpdb->prepare( $query, $emp_id, $payment_date ) );

    $query = "SELECT SUM(pay_item_amount)
              FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
              WHERE empid='%d'
              AND payment_date='%s'
              AND pay_item_add_or_deduct=0";

    $sub = $wpdb->get_var( $wpdb->prepare( $query, $emp_id, $payment_date ) );

    return $add - $sub;
}

/**
 * Check duplicate pay item category name or not
 *
 * @since 1.0.0
 *
 * @return bool
 */
function erp_payroll_check_duplicate_pay_item_category_name( $cat_name ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem_category WHERE payitem_category='%s'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $cat_name ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check pay item category dependency
 *
 * @since 1.0.0
 *
 * @return bool
 */
function erp_payroll_check_payitem_category_dependency( $payitem_cat_id ) {
    global $wpdb;
    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payitem WHERE payitem_category_id='%d'";

    if ( count( $wpdb->get_results( $wpdb->prepare( $query, $payitem_cat_id ), ARRAY_A ) ) > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get total payrun rows
 *
 * @since 1.0.0
 *
 * @return int
 */
function get_total_payrun_rows( $args ) {
    global $wpdb;

    $last_changed = erp_cache_get_last_changed( 'hrm', 'payrun', 'erp-payroll' );
    $cache_key    = 'erp-all-payrun-' . md5( serialize( $args ) ).": $last_changed";
    $payrun_rows  = wp_cache_get( $cache_key, 'erp-payroll' );

    $cache_key_count   = 'erp-count-payrun-' . md5( serialize( $args ) ).": $last_changed";
    $payrun_rows_count = wp_cache_get( $cache_key_count, 'erp-payroll' );

    if ( false === $payrun_rows ) {
        $query = "SELECT payrun.id,
                            payrun.payment_date,
                            payrun.from_date,
                            payrun.to_date,
                            CONCAT('PR-',payrun.id) as Pay_Run,
                                (SELECT COUNT(DISTINCT empid)
                                        FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee
                                        WHERE pay_calendar_id=payrun.pay_cal_id) as effected_employees,

                                (IF(payrun.approve_status=1,'Approved','Not Approved')) as status

                    FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun";
        if ( isset($args['status']) && $args['status'] == 'Approved' ) {
            $query .= " WHERE payrun.approve_status=1";
        }
        if ( isset($args['status']) && $args['status'] == 'Not Approved' ) {
            $query .= " WHERE payrun.approve_status=0";
        }

        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $payrun_rows_count = count( $udata );
        } else {
            $payrun_rows_count = 0;
        }

        wp_cache_set( $cache_key_count, $payrun_rows_count, 'erp-payroll' );
    }

    return $payrun_rows_count;
}

/**
 * Get payrun list
 *
 * @since 1.0.0
 *
 * @return array
 */
function get_payrun_rows( $args ) {
    global $wpdb;

    $defaults = array(
        'number' => 5,
        'offset' => 0
    );

    $args = wp_parse_args( $args, $defaults );

    $last_changed = erp_cache_get_last_changed( 'hrm', 'payrun', 'erp-payroll' );
    $cache_key    = 'erp-all-payrun-' . md5( serialize( $args ) ).": $last_changed";
    $payrun_rows  = wp_cache_get( $cache_key, 'erp-payroll' );

    if ( false === $payrun_rows ) {
        $query = "SELECT payrun.id,
                        payrun.pay_cal_id,
                        payrun.payment_date,
                        payrun.from_date,
                        payrun.to_date,
                        CONCAT('PR-',payrun.id) as Pay_Run,
                        COUNT(DISTINCT empid) as effected_employees,
                        ( SUM(IFNULL(cemp.pay_item_amount,0)) +  SUM(IFNULL(cemp.allowance,0)) ) - SUM(IFNULL(cemp.deduction,0)) as employees_payment ,
                        (IF(payrun.approve_status=1,'Approved','Not Approved')) as status

                FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as cemp ON cemp.payrun_id=payrun.id";

        if ( isset($args['status']) && $args['status'] == 'Approved' ) {
            $query .= " WHERE payrun.approve_status=1";
        }

        if ( isset($args['status']) && $args['status'] == 'Not Approved' ) {
            $query .= " WHERE payrun.approve_status=0";
        }

        if ( isset($args['orderby']) ) {
            $query .= " ORDER BY " . $args['orderby'] . " " . $args['order'] . " LIMIT {$args['offset']}, {$args['number']}";
        } else {
            $query .= " GROUP BY payrun.id ORDER BY payrun.id DESC LIMIT {$args['offset']}, {$args['number']}";
        }

        $payrun_rows = $wpdb->get_results( $query, ARRAY_A );

        wp_cache_set( $cache_key, $payrun_rows, 'erp-payroll' );
    }

    return $payrun_rows;
}

/**
 * Get pay run status
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_get_status() {
    global $wpdb;

    $query = "SELECT DISTINCT (IF(payrun.approve_status=1,'Approved','Not Approved')) as status
              FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun";

    return $wpdb->get_results( $query, ARRAY_A );
}

/**
 * Retrieves departments those have employees
 *
 * @since 2.0.1
 *
 * @return array
 */
function erp_hr_payroll_get_depts_having_employee() {
    global $wpdb;

    $non_empty_depts = [];
    $departments     = erp_hr_get_departments( [ 'no_object' => true ] );

    foreach ( $departments as $dept ) {
        $res = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                FROM {$wpdb->prefix}erp_hr_employees
                WHERE department = %d",
                [ $dept->id ]
            )
        );

        if ( ! empty( $res ) ) {
            $non_empty_depts[] = [
                'id'    => $dept->id,
                'title' => $dept->title
            ];
        }
    }

    return $non_empty_depts;
}

/**
 * Generates dropdown values of
 * departments that have employees
 *
 * @since 2.0.1
 *
 * @param string $selected
 *
 * @return array
 */
function erp_hr_payroll_get_dept_dropdown_raw( $selected = '' ) {
    $dropdown        = [];
    $non_empty_depts = erp_hr_payroll_get_depts_having_employee();

    if ( ! empty( $selected ) ) {
        $dropdown    = [ '-1' => $selected ];
    }

    if ( ! empty( $non_empty_depts ) ) {
        foreach ( $non_empty_depts as $key => $dept ) {
            $dropdown[ $dept['id'] ] = stripslashes( $dept['title'] );
        }
    }

    return $dropdown;
}

/**
 * Get designation list those designations have employee
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_hr_payroll_get_designation_with_employees( $designations ) {
    global $wpdb;
    $designations_have_employee = [];

    foreach ( $designations as $des ) {
        $did   = $des->id;
        $query = "SELECT id FROM {$wpdb->prefix}erp_hr_employees WHERE designation='%d'";
        $res   = $wpdb->get_results( $wpdb->prepare( $query, $did ), ARRAY_A );
        if ( count( $res ) > 0 ) {
            $designations_have_employee[] = [
                'id'    => $des->id,
                'title' => $des->title
            ];
        }
    }

    return $designations_have_employee;
}

/**
 * Get pay run status drop down
 *
 * @since 1.0.0
 *
 * @return string the drop down
 */
function erp_hr_payroll_get_status_dropdown( $selected = '' ) {
    $status   = erp_payroll_get_status();
    $dropdown = '';

    if ( $status ) {
        foreach ( $status as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $title['status'], selected( $selected, $key, false ), $title['status'] );
        }
    }

    return $dropdown;
}

/**
 * Settings pay types
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_payroll_paytypes() {
    $paytypes = [
        'Allowance'            => 'Allowance',
        'Deduction'            => 'Deduction',
        'Tax'                  => 'Tax',
        'Non-Taxable Payments' => 'Non-Taxable Payments'
    ];
    return apply_filters( 'erp_payroll_paytypes', $paytypes );
}

/**
 * Show Payrun Tab
 *
 * @since 1.0.0
 *
 * @return string
 */
function erp_payroll_payrun_tab( $selected, $payrunid ) {
    $steps = array(
        'employees'      => __( 'Employees', 'erp-pro' ),
        'variable_input' => __( 'Variable Input', 'erp-pro' ),
        'payslips'       => __( 'PaySlips', 'erp-pro' ),
        'approve'        => __( 'Approval', 'erp-pro' )
    );

    $page_url = [
        'employees'      => '?page=erp-hr-payroll-pay-run&tab=employees&prid=' . $payrunid,
        'variable_input' => '?page=erp-hr-payroll-pay-run&tab=variable_input&prid=' . $payrunid,
        'payslips'       => '?page=erp-hr-payroll-pay-run&tab=payslips&prid=' . $payrunid,
        'approve'        => '?page=erp-hr-payroll-pay-run&tab=approve&prid=' . $payrunid
    ];

    $step_counter = 1;
    $html         = '';
    $html .= '<ul class="payroll-step-progress">';
    foreach ( $steps as $key => $value ) {
        //$html .= sprintf( '<li class="%s"><span class="step-number">%d</span><a href="%s" class="step-content">%s</a></li>', ($key == $selected) ? 'active' : 'not-active', $step_counter, $page_url[$key], $value );
        $html .= sprintf( '<li class="%s"><span class="step-number">%d</span><span class="step-content">%s</span></li>', ($key == $selected) ? 'active' : 'not-active', $step_counter, $value );
        $step_counter++;
    }

    $html .= '</ul>';

    return $html;
}

/**
 *
 * Get admin links
 *
 * @param bool $submenu
 * @param array $args
 *
 * @return string
 */
function erp_payroll_get_admin_link( $submenu = false, $args = [] ) {

    if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {

        $url = add_query_arg( [ 'page' => 'erp-hr', 'section' => 'payroll' ], admin_url( 'admin.php' ) );
        if ( !empty( $submenu ) ) {
            $url = add_query_arg( [ 'sub-section' => $submenu ], $url );
        }

        if ( !empty( $args ) ) {
            $url = add_query_arg( $args, $url );
        }

        return $url;
    }

    $url = admin_url( 'admin.php?page=erp-hr-payroll' );

    switch ( $submenu ) {
        case 'calendar' :
            $url = admin_url( 'admin.php?page=erp-hr-payroll-pay-calendar' );
            break;
        case 'payrun' :
            $url = admin_url( 'admin.php?page=erp-hr-payroll-pay-run' );
            break;
        case 'reports' :
            $url = admin_url( 'admin.php?page=erp-hr-payroll-reports' );
            break;
        default :
    }

    if ( !empty( $args ) ) {
        $url = add_query_arg( $args, $url );
    }

    return $url;
}
/******* get employee payslip information by year & month start *******/

function get_employee_payslip_by_year_month( $year = null, $month = null, $emp_id = null, $all = false, $offset = null, $per_page = null ) {
    global $wpdb;

    if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
        $emp_id = intval( $_GET['id'] );
    }

    $year  = ( isset( $year ) && ! empty( $year ) ) ? $year : date( 'Y' );
    $month = ( isset( $month ) && ! empty( $month ) ) ? $month : date( 'm' );

    if ( $all == false ) {
        $year_month = "
            AND
            YEAR(pd.payment_date) = {$year} AND
            MONTH(pd.payment_date) = {$month}
        ";
    } else {
        $year_month = "";
    }

    if (  isset( $offset ) && isset( $per_page ) ) {
        $limit = "LIMIT {$offset}, {$per_page}";
    } else {
        $limit = "";
    }

    $sql = "SELECT
                pd.payrun_id,
                pd.pay_cal_id,
                pd.payment_date,
                pd.empid,
               ( SELECT (SUM( IF(tmp_pd.pay_item_id < 0 AND tmp_pd.allowance < 1 , tmp_pd.pay_item_amount, 0)) + SUM(tmp_pd.allowance)) - SUM(tmp_pd.deduction) FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as tmp_pd WHERE tmp_pd.empid = pd.empid  AND tmp_pd.payrun_id = pd.payrun_id ) as total
            FROM `{$wpdb->prefix}erp_hr_payroll_payrun_detail` as pd
            where
                pd.empid = {$emp_id} AND
                pd.approve_status = 1
                {$year_month}
            GROUP BY pd.payrun_id
            ORDER BY pd.payment_date DESC
            {$limit}";

    return $wpdb->get_results( $sql );
}


/******* get employee payslip information by year & month end *******/

/******* get employee payslip information by year & month specific payrun start*******/

function get_payslip_preview_by_specific_year_month( $prid, $eid ) {
    global $wpdb;
    $empid = absint( $eid );
    $user_id = $empid;
    $employee = new WeDevs\ERP\HRM\Employee( $user_id );

    $company = new \WeDevs\ERP\Company();

    $data = [
        'company_name'    => $company->name,
        'company_address' => str_replace( "<br/>", ", ", $company->get_formatted_address() ),
        'emp_name'        => $employee->data['personal']['first_name'] . ' ' . $employee->data['personal']['last_name'],
        'emp_address'     => $employee->data['personal']['address'],
    ];

    $query = "SELECT empid,
                    IFNULL(dept.title,'-') as dept,
                    IFNULL(desig.title,'-') as desig,
                    IFNULL((SELECT SUM( IF(payrun_details.pay_item_id < 0, payrun_details.pay_item_amount, 0))
                       FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as payrun_details
                        WHERE payrun_details.empid = emp.empid AND payrun_details.payrun_id = '%d'),'-') as basic_pay,
                    IFNULL((SELECT meta_value
                        FROM {$wpdb->prefix}usermeta
                        WHERE meta_key='employee_tax_number' AND user_id=main_emp.user_id),'-') as tax_number,
                    IFNULL((SELECT meta_value
                        FROM {$wpdb->prefix}usermeta
                        WHERE meta_key='bank_acc_number' AND user_id=main_emp.user_id),'-') as bank_acc_number,
                    IFNULL((SELECT meta_value
                        FROM {$wpdb->prefix}usermeta
                        WHERE meta_key='employee_payment_method' AND user_id=main_emp.user_id),'-') as payment_method
                  FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_employees as main_emp
                  ON main_emp.user_id=emp.empid
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON dept.id=main_emp.department
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON desig.id=main_emp.designation
                  WHERE emp.empid='%d'";
    $udata = $wpdb->get_results( $wpdb->prepare( $query, $prid, $empid ), ARRAY_A );

    $data['emp_details'] = $udata[0];

    $payrunid      = $prid;

    $query1 = "SELECT emp.empid,
                  emp.allowance,
                  emp.note,
                  payitem.payitem
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as emp
                  ON emp.pay_item_id=payitem.id
                  WHERE emp.empid='%d'
                        AND emp.pay_item_add_or_deduct=1
                        AND emp.payrun_id='%d'";
    $udata1 = $wpdb->get_results( $wpdb->prepare( $query1, $eid, $payrunid ), ARRAY_A );

    $data['emp_added_payrun'] = $udata1;

    $query2 = "SELECT emp.empid,
                  emp.deduction,
                  emp.note,
                  payitem.payitem
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as emp
                  ON emp.pay_item_id=payitem.id
                  WHERE ( emp.empid='%d'
                    AND emp.pay_item_add_or_deduct=0
                    AND emp.payrun_id='%d')
                    OR ( emp.empid='%d'
                    AND emp.pay_item_add_or_deduct=2
                    AND emp.payrun_id='%d')";
    $udata2 = $wpdb->get_results( $wpdb->prepare( $query2, $eid, $payrunid, $eid, $payrunid ), ARRAY_A );

    $data['emp_deducted_payrun'] = $udata2;

    $query3 = "SELECT cal.id,
                             cal.pay_calendar_name,
                             cal.pay_calendar_type,
                             prun.payment_date,
                             prun.to_date,
                             prun.from_date,
                             prun.approve_status
                      FROM {$wpdb->prefix}erp_hr_payroll_payrun as prun
                      LEFT JOIN {$wpdb->prefix}erp_hr_payroll_pay_calendar as cal
                      ON cal.id=prun.pay_cal_id
                      WHERE prun.id='%d'
                      ORDER BY prun.payment_date";
    $udata3 = $wpdb->get_results( $wpdb->prepare( $query3, $prid ), ARRAY_A );


    if ( is_array( $udata3 ) && count( $udata3 ) > 0 ) {
        $data['emp_calendar_info'] = $udata3[0];
    }

    return $data;

}
/******* get employee payslip information by year & month specific payrun end*******/

/****** Send payslip email notification to employee start *******/

function send_queue_list_payslip_email_by_prid( $args ) {

    $payrun_id = (isset($args['payrun_id'])) ? $args['payrun_id'] : 10;
    $emp_id = (isset($args['emp_id'])) ? $args['emp_id'] : 73;
    ob_start();
    include_once WPERP_PAYROLL_INCLUDES . "/Emails/payslip_template.php";
    $template = ob_get_clean();
    $emailer = wperp()->emailer->get_email( 'EmailPayslip' );
    if ( is_a( $emailer, '\WeDevs\ERP\Email') ) {
        $emailer->trigger( [
            'user_id' => $emp_id,
            'content' => $template
        ] );
    }
}
/****** Send payslip email notification to employee start *******/



/**
 * Get all pay items
 *
 * @since 1.4.0
 *
 * @return int
 */
function get_pay_items( $args = [] ) {
    global $wpdb;

    $where = "";

    if ( ! empty( $args ) ) {

        $where .= " WHERE";

        if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
            $where .= " payitem LIKE '%" . $args['search'] . "%' AND";
        }

        if ( isset( $args['type'] ) && ! empty( $args['type'] ) ) {
            $where .= " type='" . $args['type'] . "' AND";
        }

        if ( isset( $args['add_or_deduct'] ) && ! empty( $args['add_or_deduct'] ) ) {
            $where .= " pay_item_add_or_deduct=" . $args['add_or_deduct'] . " AND";
        }
    }

    $sql = "SELECT * FROM {$wpdb->prefix}erp_hr_payroll_payitem {$where}";
    $sql = rtrim( $sql,"AND" );

    $result = $wpdb->get_results( $sql );
    return $result;
}

/**
 * Get individual pay items of all employees
 *
 * @since 1.4.0
 *
 * @return object
 */

function get_payitems_of_all_employees_by_payid( $payid, $emp_dept_id, $emp_desig_id, $emp_name ) {
    global $wpdb;

    $where = "";

    if ( $emp_dept_id != -1 ) {
        $where.= " AND emp.department=" . $emp_dept_id;
    }

    if ( $emp_desig_id != -1 ) {
        $where.= " AND emp.designation=" . $emp_desig_id;
    }

    $sql = "SELECT
            emp.user_id as user_id,
            (SELECT desig.title FROM {$wpdb->prefix}erp_hr_designations as desig WHERE desig.id = emp.designation) as designation,
            (SELECT dept.title FROM {$wpdb->prefix}erp_hr_depts as dept WHERE dept.id = emp.department) as department,
            (SELECT usermeta.meta_value FROM {$wpdb->prefix}usermeta as usermeta WHERE usermeta.user_id = emp.user_id AND usermeta.meta_key = 'first_name' ) as first_name,
            (SELECT usermeta.meta_value FROM {$wpdb->prefix}usermeta as usermeta WHERE usermeta.user_id = emp.user_id AND usermeta.meta_key = 'last_name' ) as last_name,
            (SELECT payroll_fixed_payment.pay_item_amount FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment as payroll_fixed_payment WHERE payroll_fixed_payment.empid = emp.user_id AND payroll_fixed_payment.pay_item_id = {$payid} ) as pay_item_value
            FROM {$wpdb->prefix}erp_hr_employees as emp
            WHERE emp.status = 'active' {$where}";

    if ( ! empty( $emp_name ) || $emp_name != "" ) {
        $sql = "SELECT * FROM ({$sql}) as bulk_pay WHERE bulk_pay.first_name LIKE '%$emp_name%' OR bulk_pay.last_name LIKE '%$emp_name%'";
    }

    $result = $wpdb->get_results( $sql );

    usort($result, function ( $a, $b ) {
        return strcmp( $a->first_name, $b->first_name );
    } );

    return $result;
}

/*
 * Retrieves pay calender types as dropdown data
 *
 * @since 2.0.0
 *
 * @param string $select_text
 *
 * @return array
 */
function erp_payroll_get_pay_calendar_types_dropdown_raw( $select_text = '' ) {
    $types = erp_hr_get_pay_type();

    unset( $types['daily'], $types['contract'] );

    return ( '' !== $select_text ) ? [ '' => $select_text ] + $types : $types;
}

/**
 * Remove employee from payroll when employee status is not active
 *
 * @since 2.0.0
 *
 * @param int|string $emp_id
 * @param string $emp_status
 * @param boolean|string $from_date
 *
 * @return mixed
 */
function erp_payroll_remove_employee_from_payroll( $emp_id, $emp_status, $from_date = false ) {
    if ( 'active' === $emp_status ) {
        return false;
    }

    global $wpdb;

    $paycal_tbl        = $wpdb->prefix . 'erp_hr_payroll_pay_calendar_employee';
    $payrun_tbl        = $wpdb->prefix . 'erp_hr_payroll_payrun';
    $payrun_detail_tbl = $wpdb->prefix . 'erp_hr_payroll_payrun_detail';
    $from_date         = $from_date ? erp_current_datetime()->modify( $from_date )->format( 'Y-m-d' ) : erp_current_datetime()->format( 'Y-m-d' );

    $pay_cal_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT pay_calendar_id FROM $paycal_tbl WHERE empid = %d",
            [ $emp_id ]
        )
    );

    if ( ! empty( $pay_cal_id ) ) {
        $pay_run_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT payrun.id
                FROM $paycal_tbl AS paycal
                LEFT JOIN $payrun_tbl AS payrun
                ON  paycal.pay_calendar_id = payrun.pay_cal_id
                WHERE paycal.pay_calendar_id = %d
                AND paycal.empid = %d
                AND payrun.from_date >= %s",
                [ $pay_cal_id, $emp_id, $from_date ]
            )
        );

        $delete_id = $wpdb->delete( $paycal_tbl, [ 'empid' => $emp_id ], [ '%d' ] );

        if ( ! is_wp_error( $delete_id ) && ! empty( $pay_run_ids ) ) {
            foreach ( $pay_run_ids as $payrun_id ) {
                $wpdb->delete(
                    $payrun_detail_tbl,
                    [ 'pay_cal_id' => $pay_cal_id, 'payrun_id' => $payrun_id, 'empid' => $emp_id ],
                    [ '%d', '%d', '%d' ]
                );
            }

            $pay_cal_exist = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $paycal_tbl WHERE pay_calendar_id = %d",
                    [ $pay_cal_id ]
                )
            );

            if ( empty( $pay_cal_exist ) ) {
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM $payrun_tbl WHERE pay_cal_id = %d AND from_date >= %s",
                        [ $pay_cal_id, $from_date ]
                    )
                );
            }
        }

        return $delete_id;
    }

    return false;
}

/**
 * Purge cache data for payroll addon
 *
 * Remove all cache for payroll addon
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return void
 */
function erp_payroll_purge_cache( $args = [] ) {
    $group = 'erp-payroll';

    if ( isset( $args['payroll_id'] ) ) {
        wp_cache_delete( "erp-payroll-by-" . $args['payroll_id'], $group );
    }

    if ( isset( $args['list'] ) ) {
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => $args['list'] ] );
    }
}
