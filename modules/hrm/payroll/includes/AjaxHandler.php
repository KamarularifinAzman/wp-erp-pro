<?php
namespace WeDevs\Payroll;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\HRM\Employee;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class AjaxHandler {

    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for HR Payroll
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {

        $this->action( 'wp_ajax_erp_payroll_get_payrun', 'get_payrun' );
        $this->action( 'wp_ajax_erp_payroll_get_payrun_list', 'get_payrun_list' );

        $this->action( 'wp_ajax_erp_payroll_get_payitem', 'get_payitem' );
        $this->action( 'wp_ajax_erp_payroll_add_payitem', 'add_payitem' );
        $this->action( 'wp_ajax_erp_payroll_edit_payitem', 'edit_payitem' );
        $this->action( 'wp_ajax_erp_payroll_remove_payitem', 'remove_payitem' );
        $this->action( 'wp_ajax_erp_payroll_remove_payrun', 'remove_payrun' );
        $this->action( 'wp_ajax_erp_payroll_get_payitem_by_type', 'get_payitem_by_type' );

        //payitem
        $this->action( 'wp_ajax_erp_payroll_add_payitem_info', 'add_payitem_info' );
        $this->action( 'wp_ajax_erp_payroll_get_allowance_info', 'get_allowance_info' );
        $this->action( 'wp_ajax_erp_payroll_get_deduction_info', 'get_deduction_info' );
        $this->action( 'wp_ajax_erp_payroll_get_tax_info', 'get_tax_info' );
        $this->action( 'wp_ajax_erp_payroll_remove_add_or_deduct_info', 'remove_add_or_deduct_info' );

        $this->action( 'wp_ajax_erp_payroll_get_pay_calendar', 'get_pay_calendar' );
        $this->action( 'wp_ajax_erp_payroll_get_emp', 'get_employees' );
        $this->action( 'wp_ajax_erp_payroll_create_pay_calendar', 'create_pay_calendar' );
        $this->action( 'wp_ajax_erp_payroll_remove_calendar', 'remove_calendar' );
        $this->action( 'wp_ajax_erp_payroll_get_calendar_emp', 'get_calendar_emp' );
        $this->action( 'wp_ajax_erp_payroll_update_pay_calendar', 'update_pay_calendar' );
        $this->action( 'wp_ajax_erp_payroll_get_calendar_info', 'get_calendar_info' );
        $this->action( 'wp_ajax_erp_payroll_get_selected_calendar_info', 'get_selected_calendar_info' );
        $this->action( 'wp_ajax_erp_payroll_get_cal_list', 'get_cal_list' );

        $this->action( 'wp_ajax_erp_payroll_get_payitem_category', 'get_payitem_category' );
        $this->action( 'wp_ajax_erp_payroll_add_payitem_category', 'add_payitem_category' );
        $this->action( 'wp_ajax_erp_payroll_edit_payitem_category', 'edit_payitem_category' );
        $this->action( 'wp_ajax_erp_payroll_remove_payitem_category', 'remove_payitem_category' );

        $this->action( 'wp_ajax_erp_payroll_get_employee_list', 'get_employee_list' );
        $this->action( 'wp_ajax_erp_payroll_get_employee_rules_data', 'get_employee_rules_data' );
        $this->action( 'wp_ajax_erp_payroll_get_employee_summarize_data', 'get_employee_summarize_data' );
        $this->action( 'wp_ajax_erp_payroll_add_salary_part_settings', 'add_salary_part_settings' );

        $this->action( 'wp_ajax_erp_payroll_get_employee_list_by_calid', 'get_employee_list_by_calid' );
        $this->action( 'wp_ajax_erp_payroll_get_first_employee_info_by_calid', 'get_first_employee_info_by_calid' );
        $this->action( 'wp_ajax_erp_payroll_get_employee_info', 'get_employee_info' );
        $this->action( 'wp_ajax_erp_payroll_get_extra_info', 'get_extra_info' );

        $this->action( 'wp_ajax_erp_payroll_add_payrun', 'add_payrun' );

        //approve list task
        $this->action( 'wp_ajax_erp_payroll_get_payrun_approved_list', 'get_payrun_approved_list' );
        $this->action( 'wp_ajax_erp_payroll_get_payrun_approved_sum_data', 'get_payrun_approved_sum_data' );
        $this->action( 'wp_ajax_erp_payroll_remove_single_approved_employee', 'remove_single_approved_employee' );
        $this->action( 'wp_ajax_erp_payroll_update_payrun_range_date', 'update_payrun_range_date' );
        $this->action( 'wp_ajax_erp_payroll_update_payrun_payment_date', 'update_payrun_payment_date' );

        //copy payrun
        $this->action( 'wp_ajax_erp_payroll_get_payrun_approved_data', 'get_payrun_approved_data' );

        //approve data for single employee
        $this->action( 'wp_ajax_erp_payroll_get_payrun_single_employee_approved_earning_data', 'get_payrun_single_employee_approved_earning_data' );
        $this->action( 'wp_ajax_erp_payroll_get_payrun_single_employee_approved_deduction_data', 'get_payrun_single_employee_approved_deduct_data' );
        $this->action( 'wp_ajax_erp_payroll_get_payrun_single_employee_payment_status', 'get_payrun_single_employee_payment_status' );
        $this->action( 'wp_ajax_erp_payroll_send_payslip_single', 'send_payslip_single' );
        $this->action( 'wp_ajax_erp_payroll_send_payslip_bulk', 'send_payslip_bulk' );

        $this->action( 'wp_ajax_erp_payroll_get_basic_info', 'get_basic_info' );
        $this->action( 'wp_ajax_erp_payroll_add_basic_info', 'add_basic_info' );

        $this->action( 'wp_ajax_erp_payroll_get_payment_method', 'get_payment_method' );
        $this->action( 'wp_ajax_erp_payroll_add_payment_method', 'add_payment_method' );

        // NEW functions
        $this->action( 'wp_ajax_erp_payroll_get_payment_date', 'get_payment_date' );
        $this->action( 'wp_ajax_erp_payroll_set_payment_date', 'set_payment_date' );
        $this->action( 'wp_ajax_erp_payroll_add_additional_allowance_deduction', 'add_additional_allowance_deduction' );
        $this->action( 'wp_ajax_erp_payroll_delete_extra_payment_info', 'delete_extra_payment_info' );

        $this->action( 'wp_ajax_erp_payroll_start_payrun', 'start_payrun' );
        $this->action( 'wp_ajax_erp_payroll_start_variable_input', 'start_variable_input' );
        $this->action( 'wp_ajax_erp_payroll_approve_payment', 'approve_payment' );
        $this->action( 'wp_ajax_erp_payroll_undo_approve_payment', 'undo_approve_payment' );
        $this->action( 'wp_ajax_erp_payroll_copy_payment', 'copy_payment' );

        //reports
        $this->action( 'wp_ajax_erp_payroll_get_paytype_info', 'get_paytype_info' );
        $this->action( 'wp_ajax_erp_payroll_get_payitem_info', 'get_payitem_info' );
        $this->action( 'wp_ajax_erp_payroll_get_paysum_info', 'get_paysum_info' );
        $this->action( 'wp_ajax_erp_payroll_get_emp_pay_info', 'get_emp_pay_info' );

        //get employee payslip details by specific payrun
        $this->action( 'wp_ajax_erp_payroll_get_payslip_by_payrun', 'get_payslip_by_payrun' );

        //get fixed payments for bulk edit
        $this->action( 'wp_ajax_erp_payroll_get_fixed_payitems', 'get_fixed_payitems' );
        //update fixed payments for bulk edit
        $this->action( 'wp_ajax_erp_payroll_update_fixed_payitems', 'update_fixed_payitems' );

        // Get employees who are eligible to be assigned to a certain pay calender
        $this->action( 'wp_ajax_erp_payroll_get_available_employees', 'get_employees_for_dropdown' );
    }

    /**
     * Get pay run for graph
     *
     * @since 1.0.0
     * @since 1.0.3 Get currency symbol in from ERP v1.1.14
     *
     * @return json
     */
    public function get_payrun() {
        global $wpdb;

        $currency_symbol           = erp_get_currency_symbol( erp_get_currency() );
        $get_current_month         = date( 'm' );
        $get_current_year          = date( 'Y' );
        $number_of_days            = cal_days_in_month( CAL_GREGORIAN, $get_current_month, $get_current_year );
        $set_payrun_data_with_date = [];
        for ( $i = 1; $i <= $number_of_days; $i++ ) {
            $ddate                       = $get_current_year . '-' . $get_current_month . '-' . $i;
            $set_payrun_data_with_date[] = [
                'payment_date' => $ddate,
                'paid_amount'  => erp_payroll_get_pay_run_data_by_date( $ddate )
            ];
        }

        $this->send_success( $set_payrun_data_with_date );
    }

    /**
     * Get payrun list
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_payrun_list() {
        global $wpdb;

        $query = "SELECT payrun.id,
                     payrun.pay_cal_id,
                     payrun.payment_date,
                     payrun.from_date,
                     payrun.to_date,
                     CONCAT('PR-',payrun.id) as Pay_Run,
                     (IFNULL(sum(prd.pay_item_amount), 0) +
                     IFNULL(sum(prd.allowance), 0) )  -
                     IFNULL(sum(prd.deduction), 0) as employees_payment,
                     (IF(payrun.approve_status=1,'Approved','Not Approved')) as status

              FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
              JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as prd  ON prd.payrun_id = payrun.id
              GROUP BY payrun.id
              ORDER BY payrun.id DESC LIMIT 5";

        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get pay item
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_payitem() {
        global $wpdb;

        $query = "SELECT id,
                        type,
                        payitem,
                        pay_item_add_or_deduct
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem
                  ORDER BY type";
        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get pay item by type
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_payitem_by_type() {
        global $wpdb;

        $type = $_GET['type'];

        $query = "SELECT id,
                        type,
                        payitem,
                        pay_item_add_or_deduct
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem
                  WHERE type='%s'";
        $udata = $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get pay calendar
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_pay_calendar() {
        global $wpdb;

        $last_changed  = erp_cache_get_last_changed( 'hrm', 'pay_calendar', 'erp-payroll' );
        $cache_key     = 'erp-all-pay-calendar-'.": $last_changed";
        $pay_calendars = wp_cache_get( $cache_key, 'erp-payroll' );

        if ( false === $pay_calendars ) {
            $query = "SELECT cal.id, cal.pay_calendar_name, cal.pay_calendar_type,
                        (
                            SELECT COUNT(*)
                            FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar as innercal
                            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee as pay_cal_emp
                            ON innercal.id=pay_cal_emp.pay_calendar_id
                            WHERE innercal.id=cal.id
                        ) as cal_emp_number
                        FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar as cal";
            $udata = $wpdb->get_results( $query, ARRAY_A );

            if ( is_array( $udata ) && count( $udata ) > 0 ) {
                $pay_calendars = $udata;
            } else {
                $pay_calendars = [];
            }

            wp_cache_set( $cache_key, $pay_calendars, 'erp-payroll' );
        }

        $this->send_success( $pay_calendars );
    }

    /**
     * Get employee
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_employees() {
        error_reporting( 0 );
        global $wpdb;

        // validate
        $department   = ! empty( $_GET['dept'] ) ? array_map( 'intval', (array) wp_unslash( $_GET['dept'] ) ) : [];
        $designation  = ! empty( $_GET['desig'] ) ? array_map( 'intval', (array) wp_unslash( $_GET['desig'] ) ) : [];
        $pay_type     = ! empty( $_GET['pay_type'] ) ?  sanitize_text_field( wp_unslash( $_GET['pay_type'] ) ) : '';
        $selected_emp = ! empty( $_GET['selectedEmp'] ) ? json_decode( stripslashes( sanitize_text_field( $_GET['selectedEmp'] ) ) ) : [];

        $department_ids   = count( $department ) ? join( ',', $department ) : null;
        $designation_ids  = count( $designation ) ? join( ',', $designation ) : null;
        $selected_emp_ids = count( $selected_emp ) ? join( ',', $selected_emp ) : null;

        $query = "SELECT user.id, user.display_name, user.user_email,
                    IFNULL(emp.department, 0) AS dept_id,
                    IFNULL(emp.designation, 0) AS desig_id,
                    IFNULL(emp.pay_rate, 0) AS pay_basic,
                    emp.pay_type AS pay_type
                    FROM {$wpdb->prefix}erp_hr_employees as emp
                    LEFT JOIN {$wpdb->prefix}users as user
                    ON emp.user_id = user.id
                    LEFT JOIN {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee as calendar_employee
                    ON calendar_employee.empid = user.id
                    WHERE emp.status = 'active'
                    AND emp.pay_type = '{$pay_type}'
                    AND calendar_employee.empid IS NULL"
                    . ( $department_ids ? " AND emp.department IN ($department_ids) " : '' )
                    . ( $designation_ids ? " AND emp.designation IN ($designation_ids) " : '' )
                    . ( $selected_emp_ids ? " AND emp.user_id IN ($selected_emp_ids) " : '' );

        $employees    = $wpdb->get_results( $query, ARRAY_A );
        $departments  = erp_hr_get_departments_dropdown_raw( __( 'None', 'erp' ) );
        $designations = erp_hr_get_designation_dropdown_raw( __( 'None', 'erp' ) );

        foreach ( $employees as &$emp ) {
            if ( empty( $emp['dept_id'] ) || '0' == $emp['dept_id'] ) {
                $dept_name = $departments['-1'];
            } else {
                $dept_name = $departments[ $emp['dept_id'] ];
            }

            if ( empty( $emp['desig_id'] ) || '0' == $emp['desig_id'] ) {
                $desig_name = $designations['-1'];
            } else {
                $desig_name = $designations[ $emp['desig_id'] ];
            }

            unset( $emp['desig_id'], $emp['dept_id'] );
            $emp['dept_name']  = $dept_name;
            $emp['desig_name'] = $desig_name;
        }

        $this->send_success( $employees );
    }

    /**
     * Get calendar information
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_calendar_info() {
        global $wpdb;

        $calid = isset($_GET['calid']) ? $_GET['calid'] : 0;
        $prid  = isset($_GET['prid']) ? $_GET['prid'] : 0;
        $query = '';
        $udata = [];

        if ( $calid > 0 ) {
            $query = "SELECT id,
                            pay_calendar_name,
                            pay_calendar_type,
                            0 as approve_status
                            FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar
                            WHERE id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $calid ), ARRAY_A );
        } else {
            $query = "SELECT cal.id,
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
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $prid ), ARRAY_A );
        }
        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get calendar list
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_cal_list() {
        global $wpdb;

        $iquery = "SELECT * FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar";
        $udata  = $wpdb->get_results( $iquery, ARRAY_A );
        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get selected calendar information
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_selected_calendar_info() {
        global $wpdb;

        $calid = isset($_GET['calendarid']) ? $_GET['calendarid'] : 0;
        $udata = [];

        if ( $calid > 0 ) {
            $query = "SELECT pay_calendar.pay_calendar_name,
                             pay_calendar.pay_calendar_type,
                             calendar_type_settings.custom_month_day,
                             calendar_type_settings.pay_day_mode,
                             calendar_type_settings.pay_day,
                             pay_calendar.pay_calendar_type as cal_type
                      FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar as pay_calendar
                      LEFT JOIN {$wpdb->prefix}erp_hr_payroll_calendar_type_settings as calendar_type_settings
                      ON pay_calendar.id = calendar_type_settings.pay_calendar_id
                      WHERE pay_calendar.id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $calid ), ARRAY_A );
        }
        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Create pay calendar
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function create_pay_calendar() {
        global $wpdb;

        $cal_name = $_POST['cal_name'];
        $cal_type = $_POST['cal_type'];
        $empids   = $_POST['empids'];
        $settings = false;

        //check this calendar type calendar already exist or not
        $query = "SELECT id
                  FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar
                  WHERE pay_calendar_type='%s'";

        $res   = $wpdb->get_results( $wpdb->prepare( $query, $cal_type ), ARRAY_A );

        if ( count( $res ) > 0 ) {
            return $this->send_error( __( 'Please choose another calendar type, this calendar type already exist!', 'erp-pro' ) );
        }

        // Insert this new pay calendar in DB
        $data   = array(
            'pay_calendar_name' => $cal_name,
            'pay_calendar_type' => $cal_type
        );

        $format = array(
            '%s',
            '%s'
        );

        $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_pay_calendar', $data, $format );

        $cal_id         = $wpdb->insert_id;
        $pay_mode       = 0;
        $custom_pay_day = 0;
        $pay_day        = -1;

        if ( $cal_type == 'monthly' ) {
            $pay_mode = isset( $_POST['paydaymode'] ) ? intval( wp_unslash( $_POST['paydaymode'] ) ) : 0;

            if ( 2 === $pay_mode ) {
                $custom_pay_day = isset( $_POST['specific_monthly_pay_date'] ) ? intval( wp_unslash( $_POST['specific_monthly_pay_date'] ) ) : 1;
            }

            $settings = true;
        } else {
            $pay_day        = isset( $_POST['weekday'] ) ? intval( wp_unslash( $_POST['weekday'] ) ) : 6;
            $settings       = true;
        }

        if ( $settings ) {
            $data = [
                'pay_calendar_id'  => $cal_id,
                'pay_day'          => $pay_day,
                'custom_month_day' => $custom_pay_day,
                'pay_day_mode'     => $pay_mode
            ];

            $format = [
                '%d',
                '%d',
                '%d',
                '%d'
            ];

            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_calendar_type_settings', $data, $format );
        }

        $values        = [];
        $place_holders = [];
        $query         = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee (pay_calendar_id, empid) VALUES ";
        foreach ( $empids as $emp ) {
            array_push( $values, $cal_id );
            array_push( $values, $emp );
            $place_holders[] = "('%d', '%d')";
        }

        $query .= implode( ', ', $place_holders );
        $wpdb->query( $wpdb->prepare( "$query ", $values ) );

        erp_payroll_purge_cache( ['list' => 'pay_calendar'] );

        $this->send_success( __( 'Pay calendar created successfully', 'erp-pro' ) );
    }

    /**
     * Remove pay calendar
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function remove_calendar() {
        global $wpdb;

        $cal_id = $_GET['calendarid'];
        //check dependecy
        $query                    = "SELECT COUNT(*)
            FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
            WHERE pay_cal_id='%d'";
        $dependency_checked_fixed = $wpdb->get_var( $wpdb->prepare( $query, $cal_id ) );

        if ( $dependency_checked_fixed > 0 ) {
            $this->send_error( __( 'Cannot Delete. This Pay Calendar have one or more transation', 'erp-pro' ) );
        } else {
            // Remove this new pay calendar in DB
            $data   = array(
                'id' => $cal_id,
            );
            $format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_pay_calendar', $data, $format );

            // Remove this new pay calendar in DB
            $data   = array(
                'pay_calendar_id' => $cal_id,
            );
            $format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_pay_calendar_employee', $data, $format );

            // Remove this new pay calendar in DB
            $data   = array(
                'pay_calendar_id' => $cal_id,
            );
            $format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_calendar_type_settings', $data, $format );

            erp_payroll_purge_cache( ['list' => 'payrun,pay_calendar'] );

            $this->send_success( __( 'Pay calendar revmoed successfully', 'erp-pro' ) );
        }
    }

    /**
     * Get calendar employee
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_calendar_emp() {
        global $wpdb;

        $calid = isset($_GET['calendarid']) ? $_GET['calendarid'] : '';
        $query = '';

        if ( $calid > 0 ) {
            $query = "SELECT user.id, emp.id as empid, user.display_name, user.user_email,
                    dept.title as dept_name,
                    desig.title as desig_name,
                    IFNULL(emp.pay_rate, 0) AS pay_basic
                    FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee as pemp
                    LEFT JOIN {$wpdb->prefix}erp_hr_employees as emp
                    ON emp.user_id=pemp.empid
                    LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                    ON emp.designation=desig.id
                    LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                    ON emp.department=dept.id
                    LEFT JOIN {$wpdb->prefix}users as user
                    ON emp.user_id=user.id
                    WHERE pemp.pay_calendar_id=%d";
        }
        $udata = $wpdb->get_results( $wpdb->prepare( $query, $calid ), ARRAY_A );
        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Update pay calendar
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function update_pay_calendar() {
        global $wpdb;

        $cal_name    = $_POST['cal_name'];
        $cal_type    = $_POST['cal_type'];
        $cal_pay_day = $_POST['cal_pay_day'];
        $empids      = $_POST['empids'];
        $calid       = $_POST['calid'];

        // update pay calendar table in DB
        $data         = array(
            'pay_calendar_name' => $cal_name,
            'pay_calendar_type' => $cal_type,
            //'pay_calendar_pay_day' => $cal_pay_day,
        );
        $format       = array(
            '%s',
            '%s',
            //'%d'
        );
        $where        = [
            'id' => $calid
        ];
        $where_format = [
            '%d'
        ];
        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_pay_calendar', $data, $where, $format, $where_format );
        // update pay calendar employee table
        // first remove all from this calendar then insert
        $where        = [
            'pay_calendar_id' => $calid
        ];
        $where_format = [
            '%d'
        ];
        $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_pay_calendar_employee', $where, $where_format );

        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_calendar_type_settings',
            [
                'pay_day'           => $_POST['weekday'],
                'pay_day_mode'      => $_POST['paydaymode'],
                'custom_month_day'  => $_POST['specific_monthly_pay_date']
            ],
            [
                'pay_calendar_id' => $calid
            ] );


        $values        = [];
        $place_holders = [];
        $query         = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee (pay_calendar_id, empid) VALUES ";
        foreach ( $empids as $emp ) {
            array_push( $values, $calid );
            array_push( $values, $emp );
            $place_holders[] = "('%d', '%d')";
        }

        $query .= implode( ', ', $place_holders );
        $wpdb->query( $wpdb->prepare( "$query ", $values ) );

        erp_payroll_purge_cache( ['list' => 'payrun,pay_calendar'] );

        $this->send_success( __( 'Pay calendar updated successfully', 'erp-pro' ) );
    }

    /**
     * Create pay item
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_payitem() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $payType    = sanitize_text_field( wp_unslash( $_POST['paytype'] ) );
        $payItem    = sanitize_text_field( wp_unslash( $_POST['payitem'] ) );
        $amountType = sanitize_text_field( wp_unslash( $_POST['amounttype'] ) );

        if ( $payType == '' ) {
            $this->send_error( __( 'Pay Type is empty!', 'erp-pro' ) );
        } else if ( $payItem == '' ) {
            $this->send_error( __( 'Pay Item is empty!', 'erp-pro' ) );
        } else {
            $amountType = empty ( $amountType ) ? '' : $amountType;

            if ( $payType == 'Tax' ) $amountType = 2;
            if ( $payType == 'Allowance' ) $amountType = 1;
            if ( $payType == 'Deduction' ) $amountType = 0;
            if ( $payType == 'Non-Taxable Payments' ) $amountType = 1;

            $data   = array(
                'type'                   => $payType,
                'payitem'                => $payItem,
                'pay_item_add_or_deduct' => $amountType
            );

            $format = array(
                '%s',
                '%s',
                '%d'
            );

            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payitem', $data, $format );

            $this->send_success( __( 'Pay Item Added Successfully', 'erp-pro' ) );
        }
    }

    /**
     * Edit pay item
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function edit_payitem() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $payitemid = sanitize_text_field( wp_unslash( intval( $_POST['id'] ) ) );
        $payitem   = sanitize_text_field( wp_unslash( $_POST['payitem'] ) );

        if ( !isset($payitem) ) {
            $this->send_error( __( 'Pay item cannot be empty!', 'erp-pro' ) );
        } elseif ( erp_payroll_check_duplicate_pay_item_name_during_edit( $payitemid, $payitem ) ) {
            //check given category item name already in the list or not
            $this->send_error( __( 'Given pay item already exist! Please choose another name.', 'erp-pro' ) );
        } else {
            $data         = [
                'payitem' => $payitem
            ];
            $where        = [
                'id' => $payitemid
            ];
            $data_format  = [
                '%s'
            ];
            $where_format = [
                '%d'
            ];
            $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payitem', $data, $where, $data_format, $where_format );
            $this->send_success( __( 'Pay item edited successfully', 'erp-pro' ) );
        }
    }

    /**
     * Remove pay item
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function remove_payitem() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        if ( isset($_POST['id']) ) {
            $payitem_id = intval( wp_unslash( $_POST['id'] ) );

            //check this payitem have any dependency or not
            $query = "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment WHERE pay_item_id='%d'";

            $dependency_checked_fixed = $wpdb->get_var( $wpdb->prepare( $query, $payitem_id ) );

            $query = "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction WHERE pay_item_id='%d'";

            $dependency_checked_additional = $wpdb->get_var( $wpdb->prepare( $query, $payitem_id ) );

            $query = "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail WHERE pay_item_id='%d'";

            $dependency_checked_detail = $wpdb->get_var( $wpdb->prepare( $query, $payitem_id ) );

            if ( $dependency_checked_fixed > 0 || $dependency_checked_additional > 0 || $dependency_checked_detail > 0 ) {
                $this->send_error( __( 'This Pay item cannot be deleted. It is already applied to one or more employees', 'erp-pro' ) );
            } else {
                $where        = array(
                    'id' => $payitem_id
                );
                $where_format = array(
                    '%d'
                );
                $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_payitem', $where, $where_format );

                $this->send_success( __( 'Pay item deleted successfully', 'erp-pro' ) );
            }
        } else {
            $this->send_error( __( 'Pay item delete operation failed', 'erp-pro' ) );
        }
    }

    /**
     * Get pay item category
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_payitem_category() {
        global $wpdb;

        $query = "SELECT id, payitem_category, payitem_category_type
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem_category";
        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Create pay item category
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_payitem_category() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $params = [];

        parse_str( $_POST['postdata'], $params );

        $pay_item_category_name = $params['pic_name'];
        $pay_item_category_type = $params['pic_type'];

        if ( !isset($pay_item_category_name) ) {
            $this->send_error( __( 'Pay item category cannot be empty!', 'erp-pro' ) );
        } elseif ( erp_payroll_check_duplicate_pay_item_category_name( $pay_item_category_name ) ) {
            $this->send_error( __( 'Given pay item category name already exist. Please provide another pay item category name.', 'erp-pro' ) );
        } else {
            $data   = array(
                'payitem_category'      => $pay_item_category_name,
                'payitem_category_type' => $pay_item_category_type
            );
            $format = array(
                '%s',
                '%d'
            );
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payitem_category', $data, $format );

            $this->send_success( __( 'Pay item category created successfully', 'erp-pro' ) );
        }
    }

    /**
     * Edit pay item category
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function edit_payitem_category() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $params = [];

        parse_str( $_POST['postdata'], $params );

        $pay_item_category_id   = $params['cat_id'];
        $pay_item_category_name = $params['pi_cat'];

        if ( !isset($pay_item_category_name) ) {
            $this->send_error( __( 'Pay item category cannot be empty!', 'erp-pro' ) );
        } else {
            //check given category item name already in the list or not
            if ( erp_payroll_check_duplicate_pay_item_category_name( $pay_item_category_name ) ) {
                $this->send_error( __( 'Given pay item category already exist! Please choose another name.', 'erp-pro' ) );
            } else {
                $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payitem_category', array(
                    'payitem_category' => $pay_item_category_name
                ), array('id' => $pay_item_category_id), array(
                    '%s'
                ), array('%d') );
                $this->send_success( __( 'Pay item category edited successfully', 'erp-pro' ) );
            }
        }
    }

    /**
     * Remove pay item category
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function remove_payitem_category() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        if ( isset($_POST['cat_id']) ) {
            $catid = $_POST['cat_id'];
            //check dependency
            if ( erp_payroll_check_payitem_category_dependency( $catid ) ) {
                $this->send_error( __( 'This Pay item category has one or more pay item, so it cannot be deleted!', 'erp-pro' ) );
            } else {
                $where        = array(
                    'id' => $catid
                );
                $where_format = array(
                    '%d'
                );
                $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_payitem_category', $where, $where_format );

                $this->send_success( __( 'Pay item category deleted successfully', 'erp-pro' ) );
            }
        } else {
            $this->send_error( __( 'Pay item category delete operation failed', 'erp-pro' ) );
        }
    }

    /**
     * Get employee list by calendar id
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_employee_list_by_calid() {
        global $wpdb;

        $payrunid  = isset( $_GET['prid'] ) ? intval( wp_unslash( $_GET['prid'] ) ) : 0;
        $calid     = isset( $_GET['calid'] ) ? intval( wp_unslash( $_GET['calid'] ) ) : 0;
        $from_date = isset( $_GET['from_date'] ) ? sanitize_text_field( wp_unslash( $_GET['from_date'] ) ) : '';
        $to_date   = isset( $_GET['to_date'] ) ? sanitize_text_field( wp_unslash( $_GET['to_date'] ) ) : '';

        if ( ! empty( $from_date ) ) {
            $from_date = erp_current_datetime()->modify( $from_date )->format( 'Y-m-d 00:00:00' );
        }

        if ( ! empty( $to_date ) ) {
            $to_date = erp_current_datetime()->modify( $to_date )->format( 'Y-m-d 23:59:59' );
        }

        //first check this calendar id and payment date exist or not
        $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail WHERE payrun_id='%d'";

        $res   = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );

        if ( count( $res ) > 0 ) {
            $query = "SELECT DISTINCT(emp.empid),
                      user.display_name,
                      dept.title as dept,
                      desig.title as desig,
                      IFNULL((SELECT SUM( IF(pay_item_id < 0, pay_item_amount, 0))
                                FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
                                WHERE empid=emp.empid
                                AND payrun_id='" . $payrunid . "'),0) as pay_basic,

                      IFNULL((SELECT SUM(allowance)
                                FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
                                WHERE empid=emp.empid
                                AND payrun_id='" . $payrunid . "'),
                        0) as payment,

                      IFNULL((SELECT SUM(deduction)
                                FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
                                WHERE empid=emp.empid
                                AND payrun_id='" . $payrunid . "'
                                 AND pay_item_add_or_deduct=0),
                        0) as deduction,

                      IFNULL((SELECT SUM(deduction)
                                FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail
                                WHERE empid=emp.empid
                                AND payrun_id='" . $payrunid . "'
                                AND pay_item_add_or_deduct=2),
                        0) as tax
                      FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as emp
                      LEFT JOIN {$wpdb->prefix}users as user
                      ON emp.empid=user.id
                      LEFT JOIN {$wpdb->prefix}erp_hr_employees as memp
                      ON memp.user_id=emp.empid
                      LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                      ON dept.id=memp.department
                      LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                      ON desig.id=memp.designation
                      WHERE emp.payrun_id='%d'
                      ORDER BY emp.empid";

            $udata = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );
        } else {
            $query = "SELECT emp.empid,
                      user.display_name,
                      dept.title as dept,
                      desig.title as desig,
                      pay_cal.pay_calendar_type as cal_type,
                      IFNULL((SELECT `type`
                                FROM {$wpdb->prefix}erp_hr_employee_history
                                WHERE module = 'compensation'
                                AND category = pay_cal.pay_calendar_type
                                AND user_id = memp.user_id
                                AND `date` BETWEEN %s AND %s
                                ORDER BY `date` ASC LIMIT 1),
                                    IFNULL((SELECT `type`
                                            FROM {$wpdb->prefix}erp_hr_employee_history
                                            WHERE module = 'compensation'
                                            AND category = pay_cal.pay_calendar_type
                                            AND user_id = memp.user_id
                                            AND `date` < %s
                                            ORDER BY `date` DESC LIMIT 1),
                                                IFNULL(memp.pay_rate, 0))) as pay_rate,
                      IFNULL((SELECT SUM(pay_item_amount)
                                FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
                                WHERE empid=emp.empid AND pay_item_add_or_deduct=1),0) as payment,

                      IFNULL((SELECT SUM(pay_item_amount)
                                FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
                                WHERE empid=emp.empid AND pay_item_add_or_deduct=0),0) as deduction,

                      IFNULL((SELECT SUM(pay_item_amount)
                                FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
                                WHERE empid=emp.empid AND pay_item_add_or_deduct=2),
                        0) as tax
                      FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee as emp
                      LEFT JOIN {$wpdb->prefix}erp_hr_payroll_pay_calendar as pay_cal
                      ON pay_cal.id = emp.pay_calendar_id
                      LEFT JOIN {$wpdb->prefix}users as user
                      ON emp.empid = user.id
                      LEFT JOIN {$wpdb->prefix}erp_hr_employees as memp
                      ON memp.user_id = emp.empid
                      LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                      ON dept.id = memp.department
                      LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                      ON desig.id = memp.designation
                      WHERE emp.pay_calendar_id = '%d'
                      AND memp.hiring_date <= %s
                      ORDER BY emp.empid";

            $udata = $wpdb->get_results( $wpdb->prepare( $query, [ $from_date, $to_date, $from_date, $calid, $from_date ] ), ARRAY_A );
        }

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            if ( count( $res ) <= 0 ) {
                foreach ( $udata as &$data ) {
                    if ( $data['cal_type'] === 'hourly' ) {
                        if ( ! wp_erp_pro()->module->is_active( 'attendance' ) ) {
                            $this->send_error( __( 'Attendance extension is required for Hourly Calender. Please activate the Attendance extension first or choose another calender type.', 'erp-pro' ) );
                        }

                        $data['pay_basic']   = 0;
                        $data['time_worked'] = '00:00:00';

                        $atts = get_employee_att_report( $data['empid'], [ 'start' => $from_date, 'end' => $to_date ] );

                        if ( ! empty( $atts['attendance_summary']['worktime'] ) ) {
                            $time  = explode( ':', $atts['attendance_summary']['worktime'] );
                            $hours = intval( $time[0] ) + ( intval( $time[1] ) / 60 ) + ( intval( $time[2] ) / 3600 );

                            $data['pay_basic'] = $hours * $data['pay_rate'];

                            foreach( $time as $i => &$t ) {
                                if ( strlen( $t ) < 2 ) {
                                    $t = '0' . $t;
                                }
                            }

                            $data['time_worked'] = implode( ':', $time );
                        }
                    } else {
                        $data['pay_basic'] = $data['pay_rate'];
                    }
                }
            }

            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get first employee addition deduct info by calendar id
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_first_employee_info_by_calid() {
        global $wpdb;

        $payrunid      = isset($_GET['prid']) ? $_GET['prid'] : 0;
        $add_or_deduct = $_GET['add_or_deduct'];
        $eid           = isset($_GET['eid']) ? $_GET['eid'] : 0;

        if ( $add_or_deduct == 1 ) {
            $query = "SELECT emp.empid,
                      emp.allowance as pay_item_amount,
                      emp.note,
                      payitem.payitem
                      FROM {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                      LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as emp
                      ON emp.pay_item_id=payitem.id
                      WHERE emp.empid='%d'
                            AND emp.pay_item_add_or_deduct=1
                            AND emp.payrun_id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $eid, $payrunid ), ARRAY_A );
        } else {
            $query = "SELECT emp.empid,
                      emp.deduction as pay_item_amount,
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
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $eid, $payrunid, $eid, $payrunid ), ARRAY_A );
        }

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get employee list
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_employee_list() {
        global $wpdb;

        $params = [];

        parse_str( $_REQUEST['postdata'], $params );

        if ( isset($params['department']) ) {
            $ids   = join( "','", $params['department'] );
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as pay_rate,
                          user.display_name,
                          user.user_email,
                          dept.title as department_title,
                          desig.title as desig_title
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  WHERE dept.id IN ('$ids')
                  ORDER BY emp.user_id";
        } else if ( isset($params['designation']) ) {
            $ids   = join( "','", $params['designation'] );
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as pay_rate,
                          user.display_name,
                          user.user_email,
                          dept.title as department_title,
                          desig.title as desig_title
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  WHERE desig.id IN ('$ids')
                  ORDER BY emp.user_id";
        } else {
            $query = "SELECT emp.user_id,
                          user.display_name,
                          user.user_email,
                          IFNULL(emp.pay_rate, 0) as pay_rate,
                          dept.title as department_title,
                          desig.title as desig_title
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  ORDER BY emp.user_id";
        }

        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get employee pay item data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_employee_rules_data() {
        global $wpdb;

        $params = [];

        parse_str( $_REQUEST['postdata'], $params );

        if ( isset($params['department']) ) {
            $ids   = join( "','", $params['department'] );
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as pay_rate,
                          user.display_name,
                          dept.title as department_title,
                          desig.title as desig_title,
                          payitem_rules.payitem_id as payitem_id,
                          IFNULL(payitem_rules.amount,0) as amount,
                          payitem.payitem as payitem_name,
                          payitem.status as status,
                          payitem_cat.id as payitem_category_id,
                          payitem_cat.payitem_category as payitem_category,
                          payitem_cat.payitem_category_type as payitem_category_type
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_rules_settings as payitem_rules
                  ON emp.user_id=payitem_rules.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON payitem_rules.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  WHERE dept.id IN ('$ids')
                  ORDER BY emp.user_id";
        } else if ( isset($params['designation']) ) {
            $ids   = join( "','", $params['designation'] );
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as pay_rate,
                          user.display_name,
                          dept.title as department_title,
                          desig.title as desig_title,
                          payitem_rules.payitem_id as payitem_id,
                          IFNULL(payitem_rules.amount,0) as amount,
                          payitem.payitem as payitem_name,
                          payitem.status as status,
                          payitem_cat.id as payitem_category_id,
                          payitem_cat.payitem_category as payitem_category,
                          payitem_cat.payitem_category_type as payitem_category_type
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_rules_settings as payitem_rules
                  ON emp.user_id=payitem_rules.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON payitem_rules.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  WHERE desig.id IN ('$ids')
                  ORDER BY emp.user_id";
        } else {
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as pay_rate,
                          user.display_name,
                          dept.title as department_title,
                          desig.title as desig_title,
                          payitem_rules.payitem_id as payitem_id,
                          IFNULL(payitem_rules.amount,0) as amount,
                          payitem.payitem as payitem_name,
                          payitem.status as status,
                          payitem_cat.id as payitem_category_id,
                          payitem_cat.payitem_category as payitem_category,
                          payitem_cat.payitem_category_type as payitem_category_type
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_rules_settings as payitem_rules
                  ON emp.user_id=payitem_rules.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON payitem_rules.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  ORDER BY emp.user_id";
        }

        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get employee summarize pay item data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_employee_summarize_data() {
        global $wpdb;

        $params = [];

        parse_str( $_REQUEST['postdata'], $params );

        if ( isset($params['department']) ) {
            $ids   = join( "','", $params['department'] );
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as basic,
                          user.display_name as display_name,
                          dept.id as dept_id,
                          dept.title as department_title,
                          desig.title as desig_title,
                          IFNULL(payitem_rules.amount,0) as amount,
                          payitem.payitem as payitem_name,
                          payitem.status as status,
                          payitem_cat.id as payitem_category_id,
                          payitem_cat.payitem_category as payitem_category,
                          payitem_cat.payitem_category_type as payitem_category_type,
                          TRUNCATE(SUM(CASE WHEN payitem_category_type = 1 AND payitem.status = 1
                                        THEN IFNULL(amount,0) ELSE 0 END),2) as allowance,
                          TRUNCATE(SUM(CASE WHEN payitem_category_type = 0 AND payitem.status = 1
                                        THEN IFNULL(amount,0) ELSE 0 END),2) as deduction
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_rules_settings as payitem_rules
                  ON emp.user_id=payitem_rules.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON payitem_rules.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  GROUP BY display_name
                  HAVING dept.id IN ('$ids')";
        } else if ( isset($params['designation']) ) {
            $ids   = join( "','", $params['designation'] );
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as basic,
                          user.display_name as display_name,
                          dept.title as department_title,
                          desig.id as desig_id,
                          desig.title as desig_title,
                          IFNULL(payitem_rules.amount,0) as amount,
                          payitem.payitem as payitem_name,
                          payitem.status as status,
                          payitem_cat.id as payitem_category_id,
                          payitem_cat.payitem_category as payitem_category,
                          payitem_cat.payitem_category_type as payitem_category_type,
                          TRUNCATE(SUM(CASE WHEN payitem_category_type = 1 AND payitem.status = 1
                                        THEN IFNULL(amount,0) ELSE 0 END),2) as allowance,
                          TRUNCATE(SUM(CASE WHEN payitem_category_type = 0 AND payitem.status = 1
                                        THEN IFNULL(amount,0) ELSE 0 END),2) as deduction
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_rules_settings as payitem_rules
                  ON emp.user_id=payitem_rules.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON payitem_rules.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  GROUP BY display_name
                  HAVING desig.id IN('$ids')";
        } else {
            $query = "SELECT emp.user_id,
                          IFNULL(emp.pay_rate, 0) as basic,
                          user.display_name as display_name,
                          dept.title as department_title,
                          desig.title as desig_title,
                          IFNULL(payitem_rules.amount,0) as amount,
                          payitem.payitem as payitem_name,
                          payitem.status as status,
                          payitem_cat.id as payitem_category_id,
                          payitem_cat.payitem_category as payitem_category,
                          payitem_cat.payitem_category_type as payitem_category_type,
                          TRUNCATE(SUM(CASE WHEN payitem_category_type = 1 AND payitem.status = 1
                                        THEN IFNULL(amount,0) ELSE 0 END),2) as allowance,
                          TRUNCATE(SUM(CASE WHEN payitem_category_type = 0 AND payitem.status = 1
                                        THEN IFNULL(amount,0) ELSE 0 END),2) as deduction
                  FROM {$wpdb->prefix}erp_hr_employees as emp
                  LEFT JOIN {$wpdb->prefix}erp_hr_depts as dept
                  ON emp.department=dept.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_designations as desig
                  ON emp.designation=desig.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_rules_settings as payitem_rules
                  ON emp.user_id=payitem_rules.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON payitem_rules.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  GROUP BY display_name";
        }

        $udata = $wpdb->get_results( $query, ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get employee summarize payment amount with dates
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payrun_approved_sum_data() {
        global $wpdb;

        $payrunid = $_GET['payrunid'];

        $query = "SELECT emp.user_id,
                  payrun.from_date as fdate,
                  payrun.to_date as tdate,
                  payrun.payment_date as pdate,
                  payrun.note as note,
                  approved_employee.amount as amount,
                  payitem.status as status,
                  payitem_cat.payitem_category_type as payitem_category_type,
                  IFNULL((SELECT IF(meta_value = '',0,meta_value)
                            FROM {$wpdb->prefix}usermeta
                            WHERE meta_key='ordinary_rate' AND user_id=emp.user_id),0) +
                  TRUNCATE(SUM(CASE WHEN payitem_category_type = 1 AND payitem.status = 1
                         THEN approved_employee.amount ELSE 0 END),2) -
                  TRUNCATE(SUM(CASE WHEN payitem_category_type = 0 AND payitem.status = 1
                         THEN approved_employee.amount ELSE 0 END),2) as payrun_payment
              FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
              ON payrun.id=approved_employee.payrun_id
              LEFT JOIN {$wpdb->prefix}erp_hr_employees as emp
              ON emp.user_id=approved_employee.emp_id
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
              ON approved_employee.payitem_id=payitem.id
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
              ON payitem.payitem_category_id=payitem_cat.id
              LEFT JOIN {$wpdb->prefix}users as user
              ON emp.user_id=user.id
              WHERE payrun.id='%d'
              GROUP BY user_id";

        $udata = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Remove single approved employee from the approve list
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function remove_single_approved_employee() {
        global $wpdb;

        $payrunid = $_POST['payrunid'];
        $userid   = $_POST['userid'];

        $where_data   = [
            'payrun_id' => $payrunid,
            'emp_id'    => $userid
        ];
        $where_format = [
            '%d',
            '%d'
        ];
        $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_employee', $where_data, $where_format );
        //check this was the last row of this payrun IF yes then delete the payrun too
        $query = "SELECT id FROM {$wpdb->prefix}erp_hr_payroll_employee WHERE payrun_id=%d";
        $res   = $wpdb->get_results( $wpdb->prepare( $query, $payrunid, $userid ), ARRAY_A );
        if ( count( $res ) == 0 ) {
            $where_data   = [
                'id' => $payrunid
            ];
            $where_format = [
                '%d'
            ];
            $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_payrun', $where_data, $where_format );
        }
        if ( count( $res ) == 0 ) {
            $this->send_success( [
                'signal' => 'leave',
                'msg'    => __( 'Employee information removed successfully', 'erp-pro' )
            ] );
        } else {
            $this->send_success( [
                'signal' => 'stay',
                'msg'    => __( 'Employee information removed successfully', 'erp-pro' )
            ] );
        }
    }

    /**
     * Update payrun range dates
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update_payrun_range_date() {
        global $wpdb;

        $payrun_id = $_POST['payrunid'];
        $fdate     = $_POST['fdate'];
        $tdate     = $_POST['tdate'];

        $update_data = [
            'from_date' => $fdate,
            'to_date'   => $tdate
        ];

        $where_data = [
            'id' => $payrun_id
        ];

        $update_format_data = [
            '%s',
            '%s'
        ];

        $where_format_data = [
            '%d'
        ];

        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun', $update_data, $where_data, $update_format_data, $where_format_data );

        $this->send_success( __( 'Date period updated successfully', 'erp-pro' ) );
    }

    /**
     * Update payrun payment date
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update_payrun_payment_date() {
        global $wpdb;

        $payrun_id   = $_POST['payrunid'];
        $paymentdate = $_POST['pdate'];

        $update_data = [
            'payment_date' => $paymentdate
        ];

        $where_data = [
            'id' => $payrun_id
        ];

        $update_format_data = [
            '%s'
        ];

        $where_format_data = [
            '%d'
        ];

        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun', $update_data, $where_data, $update_format_data, $where_format_data );

        $this->send_success( __( 'Payment date updated successfully', 'erp-pro' ) );
    }

    /**
     * Get employee payment approved data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payrun_approved_list() {
        global $wpdb;

        $payrunid = $_GET['payrunid'];

        $query = "SELECT emp.user_id,
                  payrun.from_date as fdate,
                  payrun.to_date as tdate,
                  payrun.payment_date as pdate,
                  IFNULL(emp.pay_rate, 0) as basic,
                  user.display_name as display_name,
                  approved_employee.amount as amount,
                  payitem.payitem as payitem_name,
                  payitem.status as status,
                  approved_employee.status as estatus,
                  payitem_cat.payitem_category_type as payitem_category_type,
                  TRUNCATE(SUM(CASE WHEN payitem_category_type = 1 AND payitem.status = 1
                         THEN approved_employee.amount ELSE 0 END),2) as allowance,
                  TRUNCATE(SUM(CASE WHEN payitem_category_type = 0 AND payitem.status = 1
                         THEN approved_employee.amount ELSE 0 END),2) as deduction
              FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
              ON payrun.id=approved_employee.payrun_id
              LEFT JOIN {$wpdb->prefix}erp_hr_employees as emp
              ON emp.user_id=approved_employee.emp_id
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
              ON approved_employee.payitem_id=payitem.id
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
              ON payitem.payitem_category_id=payitem_cat.id
              LEFT JOIN {$wpdb->prefix}users as user
              ON emp.user_id=user.id
              WHERE payrun.id='%d'
              GROUP BY user_id";

        $udata = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get single employee payment approved data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payrun_single_employee_approved_earning_data() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $payrunid = $_GET['payrunid'];
        $empid    = $_GET['empid'];

        if ( isset($payrunid) && isset($empid) ) {
            $query = "SELECT emp.user_id,
                  payrun.from_date as fdate,
                  payrun.to_date as tdate,
                  payrun.payment_date as pdate,
                  IFNULL((SELECT IF(meta_value = '',0,meta_value) FROM wp_usermeta WHERE meta_key='ordinary_rate' AND user_id=emp.user_id),0) as basic,
                  user.display_name as display_name,
                  approved_employee.amount as amount,
                  payitem.payitem as payitem_name,
                  payitem.status as status,
                  payrun.note as note,
                  CONCAT('PR-',payrun.id) as reference,
                  ledger.name as account_head,
                  payitem_cat.payitem_category_type as payitem_category_type
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON payrun.id=approved_employee.payrun_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_employees as emp
                  ON emp.user_id=approved_employee.emp_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON approved_employee.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_ac_ledger as ledger
                  ON ledger.id=payitem.account_head
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
                  ON payitem.payitem_category_id=payitem_cat.id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  WHERE payrun.id='%d' AND emp.user_id='%d' AND payitem_cat.payitem_category_type=1";

            $udata = $wpdb->get_results( $wpdb->prepare( $query, $payrunid, $empid ), ARRAY_A );
            if ( count( $udata ) == 0 ) { //if only basic salary has but no payitem created for this employee then run this IF
                $query = "SELECT emp.user_id,
                  payrun.from_date as fdate,
                  payrun.to_date as tdate,
                  payrun.payment_date as pdate,
                  IFNULL((SELECT IF(meta_value = '',0,meta_value) FROM wp_usermeta WHERE meta_key='ordinary_rate' AND user_id=emp.user_id),0) as basic,
                  user.display_name as display_name,
                  approved_employee.amount as amount,
                  payrun.note as note,
                  CONCAT('PR-',payrun.id) as reference
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON payrun.id=approved_employee.payrun_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_employees as emp
                  ON emp.user_id=approved_employee.emp_id
                  LEFT JOIN {$wpdb->prefix}users as user
                  ON emp.user_id=user.id
                  WHERE payrun.id='%d' AND emp.user_id='%d'";

                $udata     = $wpdb->get_results( $wpdb->prepare( $query, $payrunid, $empid ), ARRAY_A );
                $user_data = [];
                foreach ( $udata as $ud ) {
                    $user_data[] = array(
                        'user_id'      => $ud['user_id'],
                        'fdate'        => $ud['fdate'],
                        'tdate'        => $ud['tdate'],
                        'pdate'        => $ud['pdate'],
                        'basic'        => $ud['basic'],
                        'display_name' => $ud['display_name'],
                        'amount'       => $ud['amount'],
                        'note'         => $ud['note'],
                        'reference'    => $ud['reference'],
                        'account_head' => $ud['account_head']
                    );
                }
            } else {
                $user_data = [];
                foreach ( $udata as $ud ) {
                    $user_data[] = array(
                        'user_id'               => $ud['user_id'],
                        'fdate'                 => $ud['fdate'],
                        'tdate'                 => $ud['tdate'],
                        'pdate'                 => $ud['pdate'],
                        'basic'                 => $ud['basic'],
                        'display_name'          => $ud['display_name'],
                        'amount'                => $ud['amount'],
                        'payitem_name'          => $ud['payitem_name'],
                        'status'                => $ud['status'],
                        'note'                  => $ud['note'],
                        'reference'             => $ud['reference'],
                        'account_head'          => $ud['account_head'],
                        'payitem_category_type' => $ud['payitem_category_type']
                    );
                }
            }

            $udata_ordinary_data = (get_user_meta( $empid, 'ordinary_rate', true ) == "") ? 0 : get_user_meta( $empid, 'ordinary_rate', true );
            $udata_account_head  = (int)get_user_meta( $empid, 'account_head', true );

            $account_name_query = "SELECT name FROM {$wpdb->prefix}erp_ac_ledger WHERE id='%d'";
            $get_account_head   = $wpdb->get_var( $wpdb->prepare( $account_name_query, $udata_account_head ) );
            $new_row            = array(
                'user_id'               => '',
                'fdate'                 => '',
                'tdate'                 => '',
                'pdate'                 => '',
                'basic'                 => $udata_ordinary_data,
                'display_name'          => '',
                'amount'                => $udata_ordinary_data,
                'payitem_name'          => 'Ordinary Rate',
                'status'                => '',
                'note'                  => '',
                'reference'             => '',
                'account_head'          => $get_account_head,
                'payitem_category_type' => 1
            );

            array_push( $user_data, $new_row );

            if ( is_array( $udata ) && count( $udata ) > 0 ) {
                $this->send_success( $user_data );
            } else {
                $this->send_success( [] );
            }
        }
    }

    /**
     * Get single employee deduct payment approved data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payrun_single_employee_approved_deduct_data() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $payrunid = $_GET['payrunid'];
        $empid    = $_GET['empid'];

        if ( isset($payrunid) && isset($empid) ) {
            $query = "SELECT emp.user_id,
                  approved_employee.amount as amount,
                  payitem.payitem as payitem_name,
                  payitem.status as status,
                  payitem_cat.payitem_category_type as payitem_category_type
              FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
              ON payrun.id=approved_employee.payrun_id
              LEFT JOIN {$wpdb->prefix}erp_hr_employees as emp
              ON emp.user_id=approved_employee.emp_id
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
              ON approved_employee.payitem_id=payitem.id
              LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as payitem_cat
              ON payitem.payitem_category_id=payitem_cat.id
              LEFT JOIN {$wpdb->prefix}users as user
              ON emp.user_id=user.id
              WHERE payrun.id='%d' AND emp.user_id='%d' AND payitem_cat.payitem_category_type=0";

            $udata = $wpdb->get_results( $wpdb->prepare( $query, $payrunid, $empid ), ARRAY_A );

            if ( is_array( $udata ) && count( $udata ) > 0 ) {
                $this->send_success( $udata );
            } else {
                $this->send_success( [] );
            }
        }
    }

    /**
     * Get single employee payment status
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payrun_single_employee_payment_status() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $payrunid = $_GET['payrunid'];
        $empid    = $_GET['empid'];

        if ( isset($payrunid) && isset($empid) ) {
            $query = "SELECT status
              FROM {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
              WHERE approved_employee.payrun_id='%d' AND approved_employee.emp_id='%d'";

            $udata = $wpdb->get_var( $wpdb->prepare( $query, $payrunid, $empid ) );

            $this->send_success( $udata );
        }
    }

    /**
     * Send payslip to single employee
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function send_payslip_single() {
        global $wpdb;

        $payrunid     = $_GET['payrunid'];
        $empid        = $_GET['empid'];
        $amount_paid  = $_GET['amount_paid'];
        $payment_date = $_GET['payment_date'];
        $from_date    = $_GET['from_date'];
        $to_date      = $_GET['to_date'];

        // send email
        $data['assigned_user_id'] = $empid;
        $data['amount_paid']      = $amount_paid;
        $data['payment_date']     = $payment_date;
        $data['from_date']        = $from_date;
        $data['to_date']          = $to_date;
        $email                    = new Emails\EmailPayslipSingle();
        $email->trigger( $data );
        $this->send_success( __( 'Payslip sent successfully', 'erp-pro' ) );
    }

    /**
     * Send payslip to bulk employee
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function send_payslip_bulk() {
        global $wpdb;

        $payrunid     = $_GET['payrunid'];
        $payment_date = $_GET['payment_date'];
        $from_date    = $_GET['from_date'];
        $to_date      = $_GET['to_date'];
        $sum_data     = $_GET['sumamounts'];

        // send email
        $data['sumdata']      = $sum_data;
        $data['payment_date'] = $payment_date;
        $data['from_date']    = $from_date;
        $data['to_date']      = $to_date;
        $email                = new Emails\EmailPayslipBulk();
        $email->trigger( $data );
        $this->send_success( __( 'Payslip(s) sent successfully', 'erp-pro' ) );
    }

    /**
     * Add new salary part settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_salary_part_settings() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $payitem     = $_POST['payitem'];
        $department  = $_POST['department'];
        $designation = $_POST['designation'];
        $employee    = $_POST['employee'];
        $amount      = $_POST['amount'];
        $remarks     = $_POST['remarks'];

        if ( !isset($payitem) ) {
            $this->send_error( __( 'Pay item cannot be empty!', 'erp-pro' ) );
        } elseif ( $amount == '' ) {
            $this->send_error( __( 'Please input amount.', 'erp-pro' ) );
        } else {
            //*** if department value is zero 0, means this payitem will be applicable to every employee
            if ( count( $department ) == 1 && $department[0] == 0 || in_array( 0, $department ) ) {
                $all_department  = erp_hr_get_departments( array('no_object' => true) );
                $all_designation = erp_hr_get_designations( array('no_object' => true) );
                $all_employee    = erp_hr_get_employees( array('no_object' => true) );

                $items         = [];
                $values        = [];
                $place_holders = [];
                $query         = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_rules_settings (payitem_id, dept_id, desig_id, emp_id, amount) VALUES ";
                foreach ( $all_employee as $employee ) {
                    $department_id  = erp_payroll_get_department_id( $employee->user_id );
                    $designation_id = erp_payroll_get_designation_id( $employee->user_id );
                    array_push( $values, $payitem );
                    array_push( $values, $department_id );
                    array_push( $values, $designation_id );
                    array_push( $values, $employee->user_id );
                    array_push( $values, $amount );
                    $place_holders[] = "('%d', '%d', '%d', '%d', '%f')";
                }

                $query .= implode( ', ', $place_holders );
                $wpdb->query( $wpdb->prepare( "$query ", $values ) );

                // check and insert remarks only first time, remarks can be edited and deleted by
                // edit and delete operation

                if ( erp_payroll_check_remarks_exist( $payitem ) === false ) {
                    $data   = array(
                        'payitem_id' => $payitem,
                        'remarks'    => $remarks
                    );
                    $format = array(
                        '%d',
                        '%s'
                    );
                    $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payitem_remakrs', $data, $format );
                }

                $this->send_success( __( 'Rules added successfully', 'erp-pro' ) );
            }
        }
    }

    /**
     * Add pay run
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_payrun() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );

        $fromdate        = $_POST['fromdate'];
        $todate          = $_POST['todate'];
        $paymentdate     = $_POST['paymentdate'];
        $employeepaydata = $_POST['payrundata'];
        $paymentslipnote = $_POST['paymentslipnote'];

        // Insert this new pay run data to DB
        $data   = array(
            'from_date'    => $fromdate,
            'to_date'      => $todate,
            'payment_date' => $paymentdate,
            'note'         => $paymentslipnote,
            'status'       => 1
        );
        $format = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%d'
        );
        $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $format );
        $pay_run_id = $wpdb->insert_id;

        $values        = [];
        $place_holders = [];
        $query         = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_employee (payrun_id, emp_id, payitem_id, amount) VALUES ";
        foreach ( $employeepaydata as $payrundata ) {
            if ( $payrundata['payitem_id'] == 0 || $payrundata['payitem_id'] == '' ) {
                continue;
            }
            array_push( $values, $pay_run_id );
            array_push( $values, $payrundata['user_id'] );
            array_push( $values, $payrundata['payitem_id'] );
            array_push( $values, $payrundata['amount'] );
            $place_holders[] = "('%d', '%d', '%d', '%f')";
        }
        $query .= implode( ', ', $place_holders );
        $wpdb->query( $wpdb->prepare( "$query ", $values ) );

        $this->send_success( [
            'payrunid' => $pay_run_id,
            'message'  => __( 'Pay Run Data Saved Successfully.', 'erp-pro' )
        ] );

    }

    /**
     * Get payroll basic info
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_basic_info() {
        $this->verify_nonce( 'payroll_nonce' );

        $eid                       = $_REQUEST['id'];
        $udata                     = [];
        $udata_employee_tax_number = get_user_meta( $eid, 'employee_tax_number', true );
        $udata_ordinary_data       = get_user_meta( $eid, 'ordinary_rate', true );
        $udata_bank_acc_number     = get_user_meta( $eid, 'bank_acc_number', true );
        $udata_bank_acc_name       = get_user_meta( $eid, 'bank_acc_name', true );
        $udata_bank_name           = get_user_meta( $eid, 'bank_name', true );
        $udata_payment_method      = get_option( 'erp_payroll_payment_method_settings', 'cash' );

        $udata = [
            'employee_tax_number' => $udata_employee_tax_number,
            'ordinary_rate'       => $udata_ordinary_data,
            'bank_acc_number'     => $udata_bank_acc_number,
            'bank_acc_name'       => $udata_bank_acc_name,
            'bank_name'           => $udata_bank_name,
            'payment_method'      => $udata_payment_method
        ];

        $this->send_success( $udata );
    }

    /**
     * Add payroll basic info
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_basic_info() {
        $this->verify_nonce( 'payroll_nonce' );

        if ( empty( $_REQUEST['id'] ) ) {
            $this->send_error( __( 'User not found! Please refresh the page and try again.', 'erp-pro' ) );
        }

        $user_id = intval( wp_unslash( $_REQUEST['id'] ) );

        if ( ! empty( $_POST['employee_tax_number'] ) ) {
            update_user_meta( $user_id, 'employee_tax_number', sanitize_text_field( wp_unslash( $_REQUEST['employee_tax_number'] ) ) );
        }

        if ( ! empty( $_POST['bank_acc_number'] ) ) {
            update_user_meta( $user_id, 'bank_acc_number', sanitize_text_field( wp_unslash( $_REQUEST['bank_acc_number'] ) ) );
        }

        if ( ! empty( $_POST['bank_acc_name'] ) ) {
            update_user_meta( $user_id, 'bank_acc_name', sanitize_text_field( wp_unslash( $_REQUEST['bank_acc_name'] ) ) );
        }

        if ( ! empty( $_POST['bank_name'] ) ) {
            update_user_meta( $user_id, 'bank_name', sanitize_text_field( wp_unslash( $_REQUEST['bank_name'] ) ) );
        }

        $this->send_success( __( 'Basic info updated successfully', 'erp-pro' ) );
    }

    /**
     * Get payroll payment method
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payment_method() {
        $this->verify_nonce( 'payroll_nonce' );

        $eid                           = $_REQUEST['id'];
        $udata_employee_payment_method = get_user_meta( $eid, 'employee_payment_method', true );

        $udata = [
            'employee_payment_method' => $udata_employee_payment_method,
        ];

        $this->send_success( $udata );
    }

    /**
     * Add payroll payment method
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_payment_method() {
        $this->verify_nonce( 'payroll_nonce' );

        $eid            = $_POST['id'];
        $payment_method = $_POST['payment_method'];

        update_user_meta( $eid, 'employee_payment_method', $payment_method );

        $this->send_success( __( 'Payment Method Inserted Successfully', 'erp-pro' ) );
    }

    /**
     * Get pay type data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_paytype_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $from_date = $_GET['from_date'];
        $to_date   = $_GET['to_date'];
        $query     = '';
        $udata     = [];

        if ( $from_date == '' && $to_date == '' ) {
            $query = "SELECT pcat.payitem_category as ptype,
                  SUM(approved_employee.amount) as amount
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON payrun.id=approved_employee.payrun_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON approved_employee.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as pcat
                  ON pcat.id=payitem.payitem_category_id
                  GROUP BY ptype";
            $udata = $wpdb->get_results( $query, ARRAY_A );
        } elseif ( $from_date != '' && $to_date != '' ) {
            $query = "SELECT pcat.payitem_category as ptype,
                  SUM(approved_employee.amount) as amount
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON payrun.id=approved_employee.payrun_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON approved_employee.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as pcat
                  ON pcat.id=payitem.payitem_category_id
                  WHERE payrun.payment_date BETWEEN '%s' AND '%s'
                  GROUP BY ptype";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), ARRAY_A );
        }

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }

    }

    /**
     * Get pay item data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_payitem_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $from_date = $_GET['from_date'];
        $to_date   = $_GET['to_date'];
        $query     = '';
        $udata     = [];

        if ( $from_date == '' && $to_date == '' ) {
            $query = "SELECT ledger.name as ledger_name,
                              payitem.payitem as pitem,
		                      pcat.payitem_category as ptype,
                  SUM(approved_employee.amount) as amount
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON payrun.id=approved_employee.payrun_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON approved_employee.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as pcat
                  ON pcat.id=payitem.payitem_category_id
                  LEFT JOIN {$wpdb->prefix}erp_ac_ledger as ledger
                  ON ledger.id=payitem.account_head
                  GROUP BY pitem";
            $udata = $wpdb->get_results( $query, ARRAY_A );
        } elseif ( $from_date != '' && $to_date != '' ) {
            $query = "SELECT ledger.name as ledger_name,
                              payitem.payitem as pitem,
		                      pcat.payitem_category as ptype,
                  SUM(approved_employee.amount) as amount
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON payrun.id=approved_employee.payrun_id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  ON approved_employee.payitem_id=payitem.id
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem_category as pcat
                  ON pcat.id=payitem.payitem_category_id
                  LEFT JOIN {$wpdb->prefix}erp_ac_ledger as ledger
                  ON ledger.id=payitem.account_head
                  WHERE payrun.payment_date BETWEEN '%s' AND '%s'
                  GROUP BY pitem";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), ARRAY_A );
        }

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }

    }

    /**
     * Get pay sum data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_paysum_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $from_date   = $_GET['from_date'];
        $to_date     = $_GET['to_date'];
        $query       = '';
        $udata       = [];
        $user_data   = [];
        $report_data = [];

      if ( $from_date != '' && $to_date != '' ) {
            $query       =  "SELECT payrun.id as pid,
                                   payrun.payment_date,
                                   payrun_details.empid,
                                   wpuser.display_name,
                                   SUM( IF(payrun_details.pay_item_id < 0, payrun_details.pay_item_amount, 0))  as gross_wages,
                                   SUM(CASE WHEN payrun_details.pay_item_add_or_deduct=1 AND payrun_details.pay_item_id > 0  THEN payrun_details.allowance ELSE 0 END) AS allowance_amount,
                                   SUM(CASE WHEN payrun_details.pay_item_add_or_deduct=0   THEN payrun_details.deduction ELSE 0 END) AS deduction_amount,
                                   SUM(CASE WHEN payrun_details.pay_item_add_or_deduct=2   THEN payrun_details.deduction ELSE 0 END) AS tax_amount
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as payrun_details
                            ON payrun_details.payrun_id = payrun.id
                            LEFT JOIN {$wpdb->prefix}users as wpuser
                            ON wpuser.id=payrun_details.empid
                            WHERE payrun.approve_status=1
                            AND payrun.payment_date BETWEEN '%s' AND '%s'
                            AND payrun_details.approve_status=1
                            GROUP BY wpuser.id";
          $report_data       = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), ARRAY_A );


        }

        if ( is_array( $report_data ) && count( $report_data ) > 0 ) {
            $this->send_success( $report_data );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get pay employee data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_emp_pay_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $from_date   = $_GET['from_date'];
        $to_date     = $_GET['to_date'];
        $query       = '';
        $udata       = [];
        $user_data   = [];
        $report_data = [];

        $query       = "SELECT
                payrun.*,
                payrun_details.empid,
                wpuser.display_name,
                SUM(
                   IF(payrun_details.pay_item_id < 0, payrun_details.pay_item_amount, 0)
            ) AS gross_wages,
            CONCAT(
                '[',
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'id',
                        payitem.id,
                        'title',
                        payitem.payitem,
                        'allowance',
                        payrun_details.allowance,
                        'deduction',
                        payrun_details.deduction,
                        'pay_item_add_or_deduct',
                        payrun_details.pay_item_add_or_deduct
                    )
                ),
                ']'
            ) AS payItemBreakDowns
            FROM
                {$wpdb->prefix}erp_hr_payroll_payrun AS payrun
            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail AS payrun_details
            ON
                payrun_details.payrun_id = payrun.id
            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem AS payitem
            ON
                payitem.id = payrun_details.pay_item_id
            LEFT JOIN {$wpdb->prefix}users AS wpuser
            ON
                wpuser.id = payrun_details.empid
            WHERE
                payrun.approve_status = 1 AND payrun_details.approve_status = 1
                AND payrun.payment_date BETWEEN '%s' AND '%s'
            GROUP BY
                payrun_details.empid";


        $reportData    = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), OBJECT );

        $finalPaymentDetails = [] ;
        foreach($reportData as $item){

            array_push($finalPaymentDetails,[ // add basic
                'display_name' => $item->display_name,
                'gross_wages' => $item->gross_wages,
                'allowance_item_name' => '',
                'allowance_amount' => '',
                'deduction_item_name' => '',
                'deduction_amount' => '',
                'tax_item_name' => '',
                'tax_amount' => '',
                'net_pay' => $item->gross_wages,
            ]);

            $item->payItemBreakDowns  = json_decode($item->payItemBreakDowns);

            $allowances  = [] ;
            $deductions  = [] ;
            $taxes  = [] ;
            foreach($item->payItemBreakDowns as $payItem){

                if($payItem->pay_item_add_or_deduct == 1){ // calculate allowance items
                    array_push($allowances, [
                        'display_name' =>'',
                        'gross_wages' => '',
                        'allowance_item_name' => $payItem->title,
                        'allowance_amount' => $payItem->allowance,
                        'deduction_item_name' => '',
                        'deduction_amount' => '',
                        'tax_item_name' => '',
                        'tax_amount' => '',
                        'net_pay' => $payItem->allowance,
                    ]);
                }

                if($payItem->pay_item_add_or_deduct == 0){ // calculate deduction items
                    array_push($deductions, [
                        'display_name' =>'',
                        'gross_wages' => '',
                        'allowance_item_name' => '',
                        'allowance_amount' => '',
                        'deduction_item_name' => $payItem->title,
                        'deduction_amount' => $payItem->deduction,
                        'tax_item_name' => '',
                        'tax_amount' => '',
                        'net_pay' => $payItem->deduction,
                    ]);
                }

                if($payItem->pay_item_add_or_deduct == 2){ // calculate tax items
                    array_push($taxes, [
                        'display_name' =>'',
                        'gross_wages' => '',
                        'allowance_item_name' => '',
                        'allowance_amount' => '',
                        'deduction_item_name' => '',
                        'deduction_amount' => '',
                        'tax_item_name' => $payItem->title,
                        'tax_amount' => $payItem->deduction,
                        'net_pay' => $payItem->deduction,
                    ]);
                }

            }

            // all all allowance
            $totalAllowance = 0 ;
            foreach ($allowances as $allowance){
                array_push($finalPaymentDetails, $allowance) ;
                $totalAllowance += (float)$allowance['allowance_amount'];
            }

            // all all deduction
            $totalDeduction = 0 ;
            foreach ($deductions as $deduction){
                array_push($finalPaymentDetails, $deduction) ;
                $totalDeduction += (float)$deduction['deduction_amount'];
            }

            // all all tax
            $totalTax = 0 ;
            foreach ($taxes as $tax){
                array_push($finalPaymentDetails, $tax) ;
                $totalTax += (float)$tax['tax_amount'];
            }


            array_push($finalPaymentDetails,[ // add total payment to a employee
                'display_name' => '',
                'gross_wages' => '',
                'allowance_item_name' => '',
                'allowance_amount' => '',
                'deduction_item_name' => '',
                'deduction_amount' => '',
                'tax_item_name' => '<b style="font-size:12px">Total Paid</b>',
                'tax_amount' => '',
                'net_pay' => ((float)$item->gross_wages + $totalAllowance) - ( $totalDeduction + $totalTax),
            ]);

        }


        $this->send_success( $finalPaymentDetails );

    }
    /**
     * Get pay run approved data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_payrun_approved_data() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $payrunid = $_GET['payrunid'];
        $query    = '';
        $udata    = [];

        if ( isset($payrunid) ) {
            $query = "SELECT payrun.payment_date,
                      payrun.from_date,
                      payrun.to_date,
                      payrun.note,
                      approved_employee.emp_id,
                      approved_employee.payitem_id,
                      approved_employee.amount
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_employee as approved_employee
                  ON approved_employee.payrun_id=payrun.id
                  WHERE payrun.id='%d'";
            $udata = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );

            foreach ( $udata as $user_data ) {
                $payment_date = $user_data['payment_date'];
                $from_date    = $user_data['from_date'];
                $to_date      = $user_data['to_date'];
                break;
            }
            // make the date for the next pay run
            $old_from_date                 = new \DateTime( $from_date );
            $old_to_date                   = new \DateTime( $to_date );
            $diff                          = $old_to_date->diff( $old_from_date )->format( "%a" );
            $days_add_number_for_from_date = intval( $diff ) + 1;
            $days_add_number_for_to_date   = intval( $diff ) * 2;

            $new_from_date = date( 'Y-m-d', strtotime( $from_date . ' + ' . $days_add_number_for_from_date . ' days' ) );
            $new_to_date   = date( 'Y-m-d', strtotime( $from_date . ' + ' . $days_add_number_for_to_date . ' days' ) );

            // first insert one row in payrun table with status 0
            $data   = [
                'payment_date' => $new_to_date,
                'from_date'    => $new_from_date,
                'to_date'      => $new_to_date
            ];
            $format = [
                '%s',
                '%s',
                '%s'
            ];
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $format );
            $pay_run_id    = $wpdb->insert_id;
            $values        = [];
            $place_holders = [];
            $query         = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_employee (payrun_id, emp_id, payitem_id, amount) VALUES ";
            foreach ( $udata as $payrundata ) {
                //$employee_has_this_payitem = erp_payroll_check_employee_has_this_payitem( $pay_item_name_id, $emp_id['user_id'] );
                //if ( $employee_has_this_payitem === false ) {
                array_push( $values, $pay_run_id );
                array_push( $values, $payrundata['emp_id'] );
                array_push( $values, $payrundata['payitem_id'] );
                array_push( $values, $payrundata['amount'] );
                $place_holders[] = "('%d', '%d', '%d', '%f')";
                //}
            }
            $query .= implode( ', ', $place_holders );
            $wpdb->query( $wpdb->prepare( "$query ", $values ) );
        }

        $this->send_success( __( 'A new payrun has been created by copied this payrun', 'erp-pro' ) );
    }

    /**
     * Remove pay run
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function remove_payrun() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $payrunid = $_POST['payrunid'];
        //check this payrun is approved or not, if approved then user cannot delete
        $query = "SELECT id
                  FROM {$wpdb->prefix}erp_hr_payroll_payrun
                  WHERE approve_status=1
                  AND id='%d'";

        $result = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );

        erp_payroll_purge_cache( ['list' => 'payrun'] );

        if ( count( $result ) > 0 ) {
            $this->send_error( __( 'Cannot delete, because it is an appoved transaction!', 'erp-pro' ) );
        } else {
            if ( isset($payrunid) ) {
                $where  = [
                    'id' => $payrunid
                ];
                $format = ['%d'];
                $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_payrun', $where, $format );
                $where  = [
                    'payrun_id' => $payrunid
                ];
                $format = ['%d'];
                $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $where, $format );
                $this->send_success( __( 'Payrun deleted successfully', 'erp-pro' ) );
            }
        }
    }

    /**
     * Get pay item allowances
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_allowance_info() {
        global $wpdb;

        $empid = $_GET['eid'];

        $query = "SELECT fixed.id,
                         fixed.pay_item_id,
                         fixed.pay_item_amount,
                         payitem.payitem
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_fixed_payment as fixed
                  ON fixed.pay_item_id=payitem.id
                  WHERE fixed.empid='%d' AND fixed.pay_item_add_or_deduct=1";
        $udata = $wpdb->get_results( $wpdb->prepare( $query, $empid ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get pay item deducitons
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_deduction_info() {
        global $wpdb;

        $empid = $_GET['eid'];

        $query = "SELECT fixed.id,
                         fixed.pay_item_id,
                         fixed.pay_item_amount,
                         payitem.payitem
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_fixed_payment as fixed
                  ON fixed.pay_item_id=payitem.id
                  WHERE fixed.empid='%d' AND fixed.pay_item_add_or_deduct=0";
        $udata = $wpdb->get_results( $wpdb->prepare( $query, $empid ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get tax items and amount
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_tax_info() {
        global $wpdb;

        $empid = $_GET['eid'];

        $query = "SELECT fixed.id,
                         fixed.pay_item_id,
                         fixed.pay_item_amount,
                         payitem.payitem
                  FROM {$wpdb->prefix}erp_hr_payroll_payitem as payitem
                  LEFT JOIN {$wpdb->prefix}erp_hr_payroll_fixed_payment as fixed
                  ON fixed.pay_item_id=payitem.id
                  WHERE fixed.empid='%d' AND fixed.pay_item_add_or_deduct=2";
        $udata = $wpdb->get_results( $wpdb->prepare( $query, $empid ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Add pay item with amount
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function add_payitem_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $pay_allowance_id     = $_POST['pay_allowance_title'];
        $pay_allowance_amount = $_POST['pay_allowance_amount'];
        $eid                  = $_POST['eid'];
        $pay_item_all_or_ded  = $_POST['pay_item_all_or_ded'];

        $checkItem = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment WHERE pay_item_id=%d AND empid=%d", [$pay_allowance_id, $eid] ), OBJECT );
        if($checkItem){
            // Update  pay item in DB
            $data   = array(
                'pay_item_id'            => $pay_allowance_id,
                'pay_item_amount'        => $pay_allowance_amount,
                'empid'                  => $eid,
                'pay_item_add_or_deduct' => $pay_item_all_or_ded
            );
            $format = array(
                '%d',
                '%.2f',
                '%d',
                '%d',
            );
            $where        = [
                'id' => $checkItem->id
            ];
            $where_format = [
                '%d'
            ];

            $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_fixed_payment', $data, $where, $format, $where_format );
        }else {
            // Insert this new pay item in DB
            $data   = array(
                'pay_item_id'            => $pay_allowance_id,
                'pay_item_amount'        => $pay_allowance_amount,
                'empid'                  => $eid,
                'pay_item_add_or_deduct' => $pay_item_all_or_ded
            );
            $format = array(
                '%d',
                '%.2f',
                '%d',
                '%d',
            );
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_fixed_payment', $data, $format );
        }
        $this->send_success( __( 'Pay Item Added successfully', 'erp-pro' ));
    }

    /**
     * Remove pay item with amount from fixed table
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function remove_add_or_deduct_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $rowid = $_POST['rowid'];
        // Delete this new pay item from DB
        $data   = array(
            'id' => $rowid
        );
        $format = array(
            '%d'
        );
        $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_fixed_payment', $data, $format );

        $this->send_success( __( 'Removed successfully', 'erp-pro' ) );
    }

    /**
     * Add paymen mehtod for single employee
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function add_payitem_method() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $pay_allowance_title  = $_POST['pay_allowance_title'];
        $pay_allowance_amount = $_POST['pay_allowance_amount'];
        $eid                  = $_POST['eid'];

        update_user_meta( $eid, $pay_allowance_title, $pay_allowance_amount );

        $this->send_success( __( 'Pay Allowance Added successfully', 'erp-pro' ) );
    }

    /**
     * Get employee profile info for payslip
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_employee_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $empid = $_GET['eid'];

        $query = "SELECT empid,
                    IFNULL(dept.title,'-') as dept,
                    IFNULL(desig.title,'-') as desig,
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
        $udata = $wpdb->get_results( $wpdb->prepare( $query, $empid ), ARRAY_A );

        if ( is_array( $udata ) && count( $udata ) > 0 ) {
            $this->send_success( $udata );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get extra info for payslip
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function get_extra_info() {
        global $wpdb;
        $this->verify_nonce( 'payroll_nonce' );
        $empid = absint( $_GET['eid'] );

        $user_id = $wpdb->get_var( "SELECT user_id from {$wpdb->prefix}erp_hr_employees WHERE id={$empid} LIMIT 1" );

        $employee = new Employee( $user_id );

        $company = new \WeDevs\ERP\Company();

        $data = [
            'company_name'    => $company->name,
            'company_address' => str_replace( "<br/>", ", ", $company->get_formatted_address() ),
            'emp_name'        => $employee->data['personal']['first_name'] . ' ' . $employee->data['personal']['last_name'],
            'emp_address'     => $employee->data['personal']['address'],
        ];


        if ( is_array( $data ) && count( $data ) > 0 ) {
            $this->send_success( $data );
        } else {
            $this->send_success( [] );
        }
    }

    /**
     * Get payment date
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_payment_date() {
        $this->verify_nonce( 'payroll_nonce' );

        $pdate = get_transient( 'payment_date' );
        $this->send_success( $pdate );
    }

    /**
     * Set payment date
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function set_payment_date() {
        $this->verify_nonce( 'payroll_nonce' );
        $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : '';

        set_transient( 'payment_date', $payment_date );
    }

    /**
     * Add additional allowance deduction data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function add_additional_allowance_deduction() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $empid           = $_POST['eid'];
        $payrunid        = $_POST['payrunid'];
        $calid           = $_POST['calid'];
        $payment_date    = $_POST['payment_date'];
        $additional_info = $_POST['additional_info'];
        $deduct_info     = $_POST['deduct_info'];
        $note            = $_POST['note'];
        $pay_item        = $_POST['pay_item'];
        $pay_item_amount = $_POST['pay_item_amount'];

        if ( $additional_info == 1 ) {
            //insert into additional allowance deduction table
            $data        = [
                'pay_item_id'            => $pay_item,
                'pay_item_amount'        => $pay_item_amount,
                'empid'                  => $empid,
                'payrun_id'              => $payrunid,
                'pay_item_add_or_deduct' => 1,
                'note'                   => $note
            ];
            $data_format = [
                '%d',
                '%.2f',
                '%d',
                '%d',
                '%d',
                '%s'
            ];
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_additional_allowance_deduction', $data, $data_format );
            //insert this data to payrun detail table
            $data        = [
                'pay_item_id'            => $pay_item,
                'pay_cal_id'             => $calid,
                'payment_date'           => $payment_date,
                'pay_item_amount'        => $pay_item_amount,
                'allowance'              => $pay_item_amount,
                'empid'                  => $empid,
                'payrun_id'              => $payrunid,
                'pay_item_add_or_deduct' => 1,
                'note'                   => $note
            ];
            $data_format = [
                '%d',
                '%d',
                '%s',
                '%.2f',
                '%.2f',
                '%d',
                '%d',
                '%d',
                '%s'
            ];
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $data, $data_format );
        }

        if ( $deduct_info == 1 ) {
            //insert additional allowance deduction table
            $data        = [
                'pay_item_id'            => $pay_item,
                'pay_item_amount'        => $pay_item_amount,
                'empid'                  => $empid,
                'payrun_id'              => $payrunid,
                'pay_item_add_or_deduct' => 0,
                'note'                   => $note
            ];
            $data_format = [
                '%d',
                '%.2f',
                '%d',
                '%d',
                '%d',
                '%s'
            ];
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_additional_allowance_deduction', $data, $data_format );
            //insert this data to payrun detail table
            $data        = [
                'pay_item_id'            => $pay_item,
                'pay_cal_id'             => $calid,
                'payment_date'           => $payment_date,
                'pay_item_amount'        => $pay_item_amount,
                'deduction'              => $pay_item_amount,
                'empid'                  => $empid,
                'payrun_id'              => $payrunid,
                'pay_item_add_or_deduct' => 0,
                'note'                   => $note
            ];
            $data_format = [
                '%d',
                '%d',
                '%s',
                '%.2f',
                '%.2f',
                '%d',
                '%d',
                '%d',
                '%s'
            ];
            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $data, $data_format );
        }

        $this->send_success( __( 'Additional amount added successfully', 'erp-pro' ) );
    }

    /**
     * Delete extra additional allowance deduction data
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function delete_extra_payment_info() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $payrunid      = $_POST['payrunid'];
        $empid         = $_POST['eid'];
        $add_or_deduct = $_POST['add_or_deduct'];
        $pay_item      = $_POST['payitem'];
        //get pay item id by pay item name
        $payitem_id = erp_payroll_get_pay_item_id($pay_item);
        $where_data    = [
            'payrun_id'              => $payrunid,
            'empid'                  => $empid,
            'pay_item_id'            => $payitem_id,
            'pay_item_add_or_deduct' => $add_or_deduct
        ];
        $where_format  = [
            '%d',
            '%d',
            '%s',
            '%d'
        ];
        $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_additional_allowance_deduction', $where_data, $where_format );
        $wpdb->delete( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $where_data, $where_format );

        $this->send_success( __( 'Removed successfully', 'erp-pro' ) );
    }

    /**
     * Start payrun for selected calendar
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function start_payrun() {
        $this->verify_nonce( 'payroll_nonce' );

        global $wpdb;

        $calid        = isset( $_POST['calid'] ) ? intval( wp_unslash( $_POST['calid'] ) ) : 0;
        $payment_date = isset( $_POST['payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_date'] ) ) : date( 'Y-m-d' );
        $to_date      = isset( $_POST['payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_date'] ) ) : date( 'Y-m-d' );
        $from_date    = isset( $_POST['payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_date'] ) ) : date( 'Y-m-d' );
        //first check this calendar has run before or not
        $query   = "SELECT id
                    FROM {$wpdb->prefix}erp_hr_payroll_payrun
                    WHERE pay_cal_id='%d' AND payment_date='%s'";

        $has_row = $wpdb->get_results( $wpdb->prepare( $query, $calid, $payment_date ) );

        if ( count( $has_row ) === 0 ) { // if zero that means you did not run this cal ever in this payment date
            //find out from date from calendar type
            $cal_type = $_POST['cal_type'];

            if ( $cal_type === 'hourly' ) {
                $from_date = date( 'Y-m-d', strtotime( '-1 day', strtotime( $payment_date ) ) );
            } else if ( $cal_type == 'weekly' ) {
                $from_date = date( 'Y-m-d', strtotime( '-7 day', strtotime( $payment_date ) ) );
            } elseif ( $cal_type == 'biweekly' ) {
                $from_date = date( 'Y-m-d', strtotime( '-15 day', strtotime( $payment_date ) ) );
            } elseif ( $cal_type == 'monthly' ) {
                $from_date = date( 'Y-m-d', strtotime( '-30 day', strtotime( $payment_date ) ) );
            }

            $data        = [
                'pay_cal_id'   => $calid,
                'payment_date' => $payment_date,
                'from_date'    => $from_date,
                'to_date'      => $to_date
            ];
            $data_format = [
                '%d',
                '%s',
                '%s',
                '%s'
            ];

            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $data_format );

            erp_payroll_purge_cache( ['list' => 'payrun,pay_calendar'] );

            $this->send_success( [
                'status' => 'save',
                'msg'    => __( 'Start payrun', 'erp-pro' )
            ] );
        } else {
            $this->send_success( [
                'status' => 'edit',
                'msg'    => __( 'Start payrun', 'erp-pro' )
            ] );
        }
    }

    /**
     * Update dates and start variable input step
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function start_variable_input() {
        $this->verify_nonce( 'payroll_nonce' );

        global $wpdb;

        $calid        = $_POST['calid'];
        $payrunid     = $_POST['payrunid'];
        $payment_date = $_POST['payment_date'];
        $to_date      = $_POST['to_date'];
        $from_date    = $_POST['from_date'];
        $empidlist    = $_POST['empidlist'];
        //first check,this payrun id is approved or not, if approved then
        //update and return with payrun id

        $sp_pay_item  = ( isset( $_POST['specify_pay_item'] ) ) ? sanitize_text_field( wp_unslash( $_POST['specify_pay_item'] ) ) : false;
        $sl_pay_items = ( isset( $_POST['selected_pay_items'] ) ) ? json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['selected_pay_items'] ) ) ) ) : [];

        if ( is_null( $sl_pay_items ) ) {
            $sl_pay_items = [];
        }

        $query   = "SELECT id
                    FROM {$wpdb->prefix}erp_hr_payroll_payrun
                    WHERE id='%d'";

        $has_row = $wpdb->get_results( $wpdb->prepare( $query, $payrunid ), ARRAY_A );

        if ( count( $has_row ) > 0 ) {
            $data         = [
                'payment_date' => $payment_date,
                'from_date'    => $from_date,
                'to_date'      => $to_date
            ];

            $data_format  = [
                '%s',
                '%s',
                '%s'
            ];

            $where        = [
                'id' => $payrunid
            ];

            $where_format = [
                '%d'
            ];

            $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $where, $data_format, $where_format );

            //update payrun detail table too
            $data         = [
                'payment_date' => $payment_date
            ];

            $data_format  = [
                '%s'
            ];

            $where        = [
                'payrun_id' => $payrunid
            ];

            $where_format = [
                '%d'
            ];

            $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $data, $where, $data_format, $where_format );

            $this->send_success( [
                'prun' => $payrunid,
                'msg'  => __( 'You are ready to go to variable input step', 'erp-pro' )
            ] );
        } else {
            $data        = [
                'pay_cal_id'   => $calid,
                'payment_date' => $payment_date,
                'from_date'    => $from_date,
                'to_date'      => $to_date
            ];
            $data_format = [
                '%d',
                '%s',
                '%s',
                '%s'
            ];

            $wpdb->insert( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $data_format );
            $payrun_id = $wpdb->insert_id;

            //insert employee pay item and payment and pay status to detail payrun table
            //get basic pay amount by emaployee id then insert to payrun detail table


            /*if ( $sp_pay_item == 'false' || ( $sp_pay_item == 'true' && in_array( -1, $sl_pay_items ) ) ) {

                foreach ($empidlist as $eid) {
                    //get basic
                    $ordinary_rate = get_user_meta($eid, 'ordinary_rate', true);
                    $basic = !empty($ordinary_rate) ? $ordinary_rate : 0;
                    $values = [];
                    $place_holders = [];
                    $query = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_payrun_detail (payrun_id, pay_cal_id, payment_date, empid, pay_item_id, pay_item_amount, pay_item_add_or_deduct) VALUES ";
                    array_push($values, $payrun_id);
                    array_push($values, $calid);
                    array_push($values, $payment_date);
                    array_push($values, $eid);
                    array_push($values, -1);
                    array_push($values, $basic);
                    array_push($values, 1);
                    $place_holders[] = "('%d', '%d', '%s', '%d', '%.2f', '%d', '%d')";
                    $query .= implode(', ', $place_holders);
                    $wpdb->query($wpdb->prepare("$query ", $values));
                }
            }*/

            //get fixed pay item and amount by emaployee id then insert to payrun detail table
            foreach ( $empidlist as $emp ) {

                // insert basic pay
                // $ordinary_rate = get_user_meta( $emp['id'], 'ordinary_rate', true );

                $basic = ! empty( $emp['pay_basic'] ) ? $emp['pay_basic'] : 0;

                $query = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_payrun_detail (payrun_id, pay_cal_id, payment_date, empid, pay_item_id, pay_item_amount, pay_item_add_or_deduct) VALUES ('%d', '%d', '%s', '%d', '%.2f', '%d', '%d')";

                $wpdb->query( $wpdb->prepare( "$query ", [ $payrun_id, $calid, $payment_date, $emp['id'], -1, $basic, 1 ] ) );

                update_user_meta( $emp['id'], 'ordinary_rate', $basic );

                $query_fixed = "SELECT pay_item_id, pay_item_amount, pay_item_add_or_deduct
                            FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment
                            WHERE empid='%d'";

                $result      = $wpdb->get_results( $wpdb->prepare( $query_fixed, $emp['id'] ), ARRAY_A );

                if ( count( $result ) > 0 ) {
                    $values        = [];
                    $place_holders = [];

                    $query         = "INSERT INTO {$wpdb->prefix}erp_hr_payroll_payrun_detail (payrun_id, pay_cal_id, payment_date, empid, pay_item_id, pay_item_amount, pay_item_add_or_deduct, allowance, deduction ) VALUES ";

                    foreach ( $result as $edata ) {
                        if ( ( $sp_pay_item == 'true' && in_array( $edata['pay_item_id'], $sl_pay_items ) ) || $sp_pay_item == 'false' ) {
                            array_push($values, $payrun_id);
                            array_push($values, $calid);
                            array_push($values, $payment_date);
                            array_push($values, $emp['id']);
                            array_push($values, $edata['pay_item_id']);
                            array_push($values, 0);
                            array_push($values, $edata['pay_item_add_or_deduct']);

                            if ( $edata['pay_item_add_or_deduct'] == (int) 1 ) {
                                array_push( $values, $edata['pay_item_amount'] );
                                array_push($values, 0 );
                            } else {
                                array_push($values, 0 );
                                array_push( $values, $edata['pay_item_amount'] );
                            }

                            $place_holders[] = "('%d', '%d', '%s', '%d', '%d', '%.2f', '%d', '%d', '%d' )";
                        }
                    }
                    $query .= implode( ', ', $place_holders );
                    $wpdb->query( $wpdb->prepare( "$query ", $values ) );
                }
            }
            $this->send_success( [
                'prun' => $payrun_id,
                'msg'  => __( 'You are ready to go to variable input step', 'erp-pro' )
            ] );
        }
    }

    /**
     * Approve payment and insert to journal too
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function approve_payment() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $payrunid     = $_POST['payrunid'];
        $payment_date = $_POST['payment_date'];
        $params       = $_POST;

        //update payrun approve status to 1
        $data         = [
            'approve_status' => 1
        ];
        $where_data   = [
            'id' => $payrunid
        ];
        $data_format  = ['%d'];
        $where_format = ['%d'];
        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $where_data, $data_format, $where_format );
        //now insert this caledar's employees payment to journal table
        $edata                = $params['employeedata'];
        $total_salary_payment = 0;
        foreach ( $edata as $ed ) {
            $total_salary_payment += $ed['pay_basic'] + $ed['payment'] - $ed['deduction'] - $ed['tax'];
        }
        //for tax payment
        $total_tax_payment = 0;
        foreach ( $edata as $ed ) {
            $total_tax_payment += $ed['tax'];
        }

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();

            $pay_method = get_option( 'erp_payroll_payment_method_settings', 'cash' );

            if ( 'cash' == $pay_method ) {
                $assets_head = $ledger_map->get_ledger_id_by_slug( 'cash' );
            } else {
                $assets_head = get_option( 'erp_payroll_payment_bank_settings' );
            }

            $salary_head     = $ledger_map->get_ledger_id_by_slug( 'wages_salaries' );
            $salary_tax_head = $ledger_map->get_ledger_id_by_slug( 'payroll_tax_expense' );
        } else {
            //get account head for salary and tax salary head
            $assets_head     = get_option( 'erp_payroll_account_head_assets' );
            $salary_head     = get_option( 'erp_payroll_account_head_salary' );
            $salary_tax_head = get_option( 'erp_payroll_account_head_salary_tax' );
            $assets_head     = isset($assets_head) ? $assets_head : 0;
            $salary_head     = isset($salary_head) ? $salary_head : 0;
            $salary_tax_head = isset($salary_tax_head) ? $salary_tax_head : 0;
        }

        $args = [
            'issue_date' => $payment_date,
            'summary'    => __( 'Salary Payment', 'erp-pro' ),
        ];

        $tax_amount = $total_tax_payment;

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $journal_id = null;

            // insert data into erp_acct_voucher_no
            $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
                'type'       => 'payroll',
                'currency'   => '$',
                'editable'   => 0,
                'created_at' => date('Y-m-d'),
                'created_by' => get_current_user_id(),
                'updated_at' => null,
                'updated_by' => null
            ) );

            $defaults = [
                'trn_no'      => $wpdb->insert_id,
                'particulars' => 'Payroll',
                'trn_date'    => date('Y-m-d'),
                'debit'       => 0,
                'credit'      => 0,
                'created_at'  => date('Y-m-d'),
                'created_by'  => get_current_user_id(),
                'updated_at'  => null,
                'updated_by'  => null
            ];

            // insert data into ledger_details
            $data = wp_parse_args( [
                'ledger_id' => $assets_head,
                'credit'    => $total_salary_payment + $tax_amount
            ], $defaults );

            $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', $data );

            // insert data into ledger_details
            $data = wp_parse_args( [
                'ledger_id' => $salary_head,
                'debit'     => $total_salary_payment
            ], $defaults );

            $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', $data );

            // insert data into ledger_details
            $data = wp_parse_args( [
                'ledger_id' => $salary_tax_head,
                'debit'     => $tax_amount
            ], $defaults );

            $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', $data );
        } else {
            $items = [
                [
                    'credit'    => $total_salary_payment,
                    'ledger_id' => $salary_head,
                ],
                [
                    'credit'    => $tax_amount,
                    'ledger_id' => $salary_tax_head,
                ],
                [
                    'debit'     => $total_salary_payment + $tax_amount,
                    'ledger_id' => $assets_head,
                ]
            ];

            $journal_id = erp_ac_new_journal( $args, $items );
        }

        $data         = [
            'jr_tran_id' => $journal_id
        ];
        $where_data   = [
            'id' => $payrunid
        ];
        $data_format  = ['%d'];
        $where_format = ['%d'];
        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $where_data, $data_format, $where_format );
        //update approve status to 1 in payrun detail table by this calendar id and payment date
        $data         = [
            'approve_status' => 1
        ];
        $where_data   = [
            'payrun_id' => $payrunid
        ];
        $data_format  = ['%d'];
        $where_format = ['%d'];
        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $data, $where_data, $data_format, $where_format );

        do_action( 'after_approve_payrun', $payrunid );

        erp_payroll_purge_cache( ['list' => 'payrun,pay_calendar'] );

        $this->send_success( __( 'Pay run approved successfully', 'erp-pro' ) );
    }

    /**
     * Undo approved payment and remove journal too
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function undo_approve_payment() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $payrunid = $_POST['payrunid'];
        $params   = $_POST;

        //first get the journal transaction id
        $query      = "SELECT jr_tran_id
                    FROM {$wpdb->prefix}erp_hr_payroll_payrun
                    WHERE id='%d'";
        $jr_tran_id = $wpdb->get_var( $wpdb->prepare( $query, $payrunid ) );

        //update payrun approve status and journal id to zero
        $data         = [
            'approve_status' => 0,
            'jr_tran_id'     => 0
        ];
        $where_data   = [
            'id' => $payrunid
        ];
        $data_format  = [
            '%d',
            '%d'
        ];
        $where_format = ['%d'];
        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun', $data, $where_data, $data_format, $where_format );
        //update payrun approve status to zero in payrun detail table too
        $data         = [
            'approve_status' => 0
        ];
        $where_data   = [
            'payrun_id' => $payrunid
        ];
        $data_format  = ['%d'];
        $where_format = ['%d'];
        $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_payrun_detail', $data, $where_data, $data_format, $where_format );
        //now remove this caledar's employees payment from journal table
        //erp_ac_remove_transaction( $jr_tran_id );

        erp_payroll_purge_cache( ['list' => 'payrun,pay_calendar'] );

        $this->send_success( __( 'Approved transaction undo successfully', 'erp-pro' ) );
    }

    /**
     * Get pay slip by payrun for specific year & month
     *
     * @since 1.0.0
     * @since 1.0.3 Get currency symbol in from ERP v1.1.14
     *
     * @return json
     */
    public function get_payslip_by_payrun() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'payroll_nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }
        $data = get_payslip_preview_by_specific_year_month( intval( $_POST['prid'] ), intval( $_POST['eid'] ) );
        $this->send_success( $data );
    }

    /**
     * Copy a payment and remove journal too
     *
     * @since 1.0.0
     *
     * @return json
     */
    public function copy_payment() {
        $this->verify_nonce( 'payroll_nonce' );
        global $wpdb;

        $payrunid = $_POST['payrunid'];

        $get_payroll_payrun_by_id = $wpdb->get_row( $wpdb->prepare(
            "
                SELECT * FROM {$wpdb->prefix}erp_hr_payroll_payrun WHERE id='%d'
            ",
            $payrunid ) );


        $get_payroll_payrun_detail_by_payrun_id = $wpdb->get_results( $wpdb->prepare(
            "
                SELECT * FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail WHERE payrun_id='%d'
            ",
            $payrunid ) );

        $get_payroll_additional_allowance_deduction_by_payrun_id = $wpdb->get_results( $wpdb->prepare(
            "
                SELECT * FROM {$wpdb->prefix}erp_hr_payroll_additional_allowance_deduction WHERE payrun_id='%d'
            ",
            $payrunid ) );

        $get_emp_by_pay_cal = $wpdb->get_results( $wpdb->prepare(
            "
                SELECT * FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee WHERE pay_calendar_id='%d'
            ",
            $get_payroll_payrun_by_id->pay_cal_id ) );


        $get_emp_by_pay_cal_array                       = wp_list_pluck( $get_emp_by_pay_cal, 'empid' );

        $get_payroll_payrun_detail_by_payrun_id_array   = wp_list_pluck( $get_payroll_payrun_detail_by_payrun_id, 'empid' );
        $get_payroll_payrun_detail_by_payrun_id_array   = array_unique( $get_payroll_payrun_detail_by_payrun_id_array );
        $new_emp_ids = array_diff( $get_emp_by_pay_cal_array, $get_payroll_payrun_detail_by_payrun_id_array );

        $wpdb->insert(
            $wpdb->prefix . "erp_hr_payroll_payrun",
            array(
                "pay_cal_id"   => $get_payroll_payrun_by_id->pay_cal_id,
                "payment_date" => $get_payroll_payrun_by_id->payment_date,
                "from_date"    => $get_payroll_payrun_by_id->from_date,
                "to_date"      => $get_payroll_payrun_by_id->to_date,
            )
        );

        $new_payrun_id = $wpdb->insert_id;

        foreach ( $get_payroll_payrun_detail_by_payrun_id as $pdpi) {

            if( in_array( $pdpi->empid, $get_emp_by_pay_cal_array ) ) {
                $wpdb->insert(
                    $wpdb->prefix . "erp_hr_payroll_payrun_detail",
                    array(
                        "payrun_id"              => $new_payrun_id,
                        "pay_cal_id"             => $pdpi->pay_cal_id,
                        "payment_date"           => $pdpi->payment_date,
                        "empid"                  => $pdpi->empid,
                        "pay_item_id"            => $pdpi->pay_item_id,
                        "pay_item_amount"        => $pdpi->pay_item_amount,
                        "allowance"              => $pdpi->allowance,
                        "deduction"              => $pdpi->deduction,
                        "pay_item_add_or_deduct" => $pdpi->pay_item_add_or_deduct,
                        "note"                   => $pdpi->note
                    )
                );
            }
        }

        foreach ( $get_payroll_additional_allowance_deduction_by_payrun_id as $padpi) {
            if( in_array( $padpi->empid, $get_emp_by_pay_cal_array ) ) {
                $wpdb->insert(
                    $wpdb->prefix . "erp_hr_payroll_additional_allowance_deduction",
                    array(
                        "pay_item_id"            => $padpi->pay_item_id,
                        "pay_item_amount"        => $padpi->pay_item_amount,
                        "empid"                  => $padpi->empid,
                        "pay_item_add_or_deduct" => $padpi->pay_item_add_or_deduct,
                        "payrun_id"              => $new_payrun_id,
                        "note"                   => $padpi->note
                    )
                );
            }
        }

        if ( count( $new_emp_ids ) > 0 ) {
            foreach ( $new_emp_ids as $new_emp_id ) {
                $wpdb->insert(
                    $wpdb->prefix . "erp_hr_payroll_payrun_detail",
                    array(
                        "payrun_id"              => $new_payrun_id,
                        "pay_cal_id"             => $get_payroll_payrun_by_id->pay_cal_id,
                        "payment_date"           => $get_payroll_payrun_by_id->payment_date,
                        "empid"                  => $new_emp_id,
                        "pay_item_id"            => -1,
                        "pay_item_amount"        => ! empty( get_user_meta( $new_emp_id, 'ordinary_rate', true ) ) ? get_user_meta( $new_emp_id, 'ordinary_rate', true ) : 0,
                        "pay_item_add_or_deduct" => 1
                    )
                );
            }
        }

        erp_payroll_purge_cache( ['list' => 'payrun'] );

        $this->send_success( __( 'Transaction copied successfully', 'erp-pro' ) );
    }

    /**
     * Get fixed payitems of employees
     *
     * @since 1.4.0
     *
     * @return json
     */
    public function get_fixed_payitems() {
        $this->verify_nonce( 'payroll_nonce' );

        $pay_item_id    = ( isset( $_POST['pay_item_id'] ) ) ? sanitize_text_field( $_POST['pay_item_id'] ) : -1 ;
        $emp_dept_id    = ( isset( $_POST['emp_dept'] ) ) ? sanitize_text_field( $_POST['emp_dept'] ) : -1 ;
        $emp_desig_id   = ( isset( $_POST['emp_desig'] ) ) ? sanitize_text_field( $_POST['emp_desig'] ) : -1 ;
        $emp_name       = ( isset( $_POST['emp_name'] ) ) ? sanitize_text_field( $_POST['emp_name'] ) : "" ;
        $pay_rate       = ( isset( $_POST['pay_rate'] ) ) ? floatval( wp_unslash( $_POST['pay_rate'] ) ) : 0;
        $date           = [];

        if ( isset( $_POST['date'] ) ) {
            $date = $_POST['date'];

            array_walk( $date, function( &$value, $key ) {
                $value = sanitize_text_field( wp_unslash( $value ) );
            } );
        }

        if ( $pay_item_id == -1 ) {
            $this->send_error( __( 'Please select an payitem first', 'erp-pro' ) );
        }

        $response = get_payitems_of_all_employees_by_payid( $pay_item_id, $emp_dept_id, $emp_desig_id, $emp_name );

        if ( $pay_rate > 0 && ! empty( $date ) ) {
            foreach ( $response as &$res ) {
                $data                  = get_employee_att_report( $res->user_id, $date );
                $present               = ! empty( $data ) ? (int) $data['attendance_summary']['present'] : 0;
                $res->pay_item_value   = $present * $pay_rate;
                $res->days_worked      = $present;
            }
        }

        $this->send_success( $response );
    }

    /**
     * Update fixed payitems of employees
     *
     * @since 1.4.0
     *
     * @return json
     */
    public function update_fixed_payitems() {
        global $wpdb;

        $this->verify_nonce( 'payroll_nonce' );
        $pay_item_id          = ( isset( $_POST['pay_item_id'] ) ) ? sanitize_text_field( $_POST['pay_item_id'] )  : -1;
        $items                = ( isset( $_POST['items'] ) ) ? $_POST['items']  : [];
        $get_pay_item_details = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}erp_hr_payroll_payitem WHERE id={$pay_item_id}" );

        foreach ( $items as $item ) {
            if ( $item['pay_item_value'] > 0 ) {

                $get_existing_row_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_payroll_fixed_payment WHERE pay_item_id=%d AND empid=%d AND pay_item_add_or_deduct=%d", $pay_item_id, $item['user_id'], $get_pay_item_details->pay_item_add_or_deduct ) );

                if ( $get_existing_row_count > 0 ) {

                    $wpdb->update(
                        $wpdb->prefix . "erp_hr_payroll_fixed_payment",
                        array(
                            'pay_item_amount'        => $item['pay_item_value'],
                        ),
                        array(
                            'pay_item_id'            => $pay_item_id,
                            'empid'                  => $item['user_id'],
                            'pay_item_add_or_deduct' => $get_pay_item_details->pay_item_add_or_deduct
                        )
                    );
                } else {

                    $wpdb->insert(
                        $wpdb->prefix . "erp_hr_payroll_fixed_payment",
                        array(
                            'pay_item_id'            => $pay_item_id,
                            'pay_item_amount'        => $item['pay_item_value'],
                            'empid'                  => $item['user_id'],
                            'pay_item_add_or_deduct' => $get_pay_item_details->pay_item_add_or_deduct
                        )
                    );
                }
            }
        }


        $this->send_success( __( 'Data successfully updated', 'erp-pro' ) );
    }

    /**
     * Get employees who are eligible to be assigned to a certain pay calender
     *
     * @since 2.0.0
     *
     * @return array
     */
    public function get_employees_for_dropdown() {
        $this->verify_nonce( 'payroll_nonce' );

        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'hr_manager' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        global $wpdb;

        $dropdown = [];
        $pay_type = isset( $_REQUEST['pay_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pay_type'] ) ) : '';

        if ( empty( $pay_type ) ) {
            $this->send_success( $dropdown );
        }

        $sql = "SELECT user.id, user.display_name
                FROM {$wpdb->prefix}erp_hr_employees AS emp
                LEFT JOIN {$wpdb->prefix}users AS user
                ON emp.user_id = user.id
                WHERE emp.status = 'active'
                AND emp.deleted_at IS NULL
                AND emp.pay_type = %s
                AND user.id NOT IN (
                    SELECT empid
                    FROM {$wpdb->prefix}erp_hr_payroll_pay_calendar_employee
                )";

        $employees = $wpdb->get_results( $wpdb->prepare( $sql, [ $pay_type ] ), ARRAY_A );

        if ( is_array( $employees ) && count( $employees ) > 0 ) {
            foreach ( $employees as $emp ) {
                $dropdown[ $emp['id'] ] = $emp['display_name'];
            }
        }

        $this->send_success( $dropdown );
    }
}
