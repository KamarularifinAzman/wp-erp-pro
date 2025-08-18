<?php
namespace WeDevs\AdvancedLeave\Accrual;

use WeDevs\ERP\HRM\Employee;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
}

/**
 * Class AccrualBgProcess
 * @package WeDevs\ERP_PRO\HR\Leave
 */
class AccrualBgProcess extends \WP_Background_Process {

    /**
     * Background process id, must be unique.
     *
     * @var string
     */
    protected $action = 'erp_pro_accrual_bg';

    protected $request_data = array(
        'task'              => 'task_process_policy',
        'policy_id'         => 0,
        'accrued_amount'    => 0,
        'accrued_max_days'  => 0,
        'f_year'            => 0,
        'employees'         => array(),
    );


    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param array $leave_entitlement Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $leave_entitlement ) {
        $this->request_data = wp_parse_args( $leave_entitlement, $this->request_data );

        $ret = '';

        switch ( $this->request_data['task'] ) {

            case 'task_process_policy':
                $ret = $this->task_process_policy_data();
                break;

            case 'task_handle_employees':
                $ret = $this->task_handle_employees();
                break;

            default:
                $ret = false;
                break;

        }
        return $ret;
    }

    public function task_process_policy_data() {
        // 1. get policy info
        global $wpdb;

        $policy = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_hr_leave_policies WHERE id = %d",
                array( $this->request_data['policy_id'] )
            )
        );

        if ( null === $policy ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => '(Query error) No data found with given policy id.' . $wpdb->last_error,
                    ),
                    true
                )
            );
            return false;
        }

        $this->request_data = wp_parse_args( $policy, $this->request_data );


        // 2. Validate policy is for current financial year
        $financial_year = erp_hr_get_financial_year_from_date();

        if ( empty( $financial_year ) ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'No financial year data found.',
                    ),
                    true
                )
            );
            return false;
        }

        if ( $this->request_data['f_year'] != $financial_year->id ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'Current financial year doesnt match with policy financial year.',
                    ),
                    true
                )
            );
            return false;
        }


        // 3. get employees
        $args = array(
            'number'        => '-1',
            'no_object'     => true,
            'department'    => $policy->department_id,
            'location'      => $policy->location_id,
            'designation'   => $policy->designation_id,
            'gender'        => $policy->gender,
            'marital_status'    => $policy->marital,
        );

        $employees = erp_hr_get_employees( $args );

        if ( ! is_array( $employees ) || empty( $employees ) ) {
            return false;
        }

        $this->request_data['employees'] = $employees;
        $this->request_data['task'] = 'task_handle_employees';

        return $this->request_data;
    }

    public function task_handle_employees() {
        if ( ! is_array( $this->request_data['employees'] ) || empty( $this->request_data['employees'] ) ) {
            return false;
        }

        global $wpdb;

        $employee_data = array_pop( $this->request_data['employees'] );
        $employee_id = $employee_data->user_id;

        $employee = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'Leave Accrual: Invalid Employee ID: ' . $employee_id,
                    ),
                    true
                )
            );
            return $this->request_data;
        }

        if ( ! $employee->get_hiring_date() ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => "Leave Accrual: Invalid Employee Hiring Date. Employee id: $employee_id, Hiring Date: " . $employee->get_hiring_date(),
                    ),
                    true
                )
            );
            return $this->request_data;
        }

        // get total accrual added for this employee
        $accrued_added = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(day_in) FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE trn_type = %s AND trn_id = %d AND user_id = %d AND f_year = %d AND leave_id = %d",
                array(
                    'leave_accrual',
                    $this->request_data['policy_id'],
                    $employee_id,
                    $this->request_data['f_year'],
                    $this->request_data['leave_id']
                )
            )
        );

        // return if already accrued max days
        if ( null !== $accrued_added && floatval( $accrued_added ) >= floatval( $this->request_data['accrued_max_days'] ) ) {
            return $this->request_data;
        }


        // get employee joining date
        $today = erp_current_datetime()->setTime( 0, 0, 0 );
        $employee_joining_date = erp_current_datetime()->modify( $employee->get_hiring_date() )->setTime( 0, 0, 0 );

        // check joining date is valid
        $difference = date_diff( $employee_joining_date, $today );

        if ( $difference->invert ) {
            // negative date, joining date is in the future
            return $this->request_data;
        }

        if ( $difference->days < 26 ) {
            // must complete at least one month of service
            return $this->request_data;
        }

        // get accrual date for this employee based on joining date
        $new_date_string = "{$today->format('Y')}-{$today->format('m')}-{$employee_joining_date->format('d')}";
        $new_date = erp_current_datetime()->modify( $new_date_string )->setTime( 0, 0, 0 )->modify( '- 1 days' );


        // check accrual already added for this employee
        $added_this_month = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE trn_type = %s AND trn_id = %d AND user_id = %d AND f_year = %d AND leave_id = %d AND created_at = %d",
                array(
                    'leave_accrual',
                    $this->request_data['policy_id'],
                    $employee_id,
                    $this->request_data['f_year'],
                    $this->request_data['leave_id'],
                    $new_date->getTimestamp()
                )
            )
        );

        if ( null !== $added_this_month && $added_this_month == 1 ) {
            // accrual already added for this user, so return for current user
            return $this->request_data;
        }

        // check current date is less than new date
        if ( $new_date > $today ) {
            // 30 days isn't completed yet.
            return $this->request_data;
        }

        //get amount to add as accrual
        $day_in = floatval( $this->request_data['accrued_amount'] );
        $accrual_sum = floatval( $accrued_added + $day_in );
        if ( $accrual_sum > floatval( $this->request_data['accrued_max_days'] ) ) {
            $day_in -= ( $accrual_sum - floatval( $this->request_data['accrued_max_days'] ) );
        }

        // finally add this value to database
        $data = array(
            'user_id'       => $employee_id,
            'leave_id'      => $this->request_data['leave_id'],
            'trn_id'        => $this->request_data['policy_id'],
            'trn_type'      => 'leave_accrual',
            'description'   => 'Generated',
            'f_year'        => $this->request_data['f_year'],
            'day_in'        => $day_in,
            'created_at'    => $new_date->getTimestamp(),
            'updated_at'    => erp_current_datetime()->getTimestamp()
        );

        $format = array(
            '%d', '%d', '%d', '%s', '%s', '%d', '%f', '%d', '%d'
        );

        if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_entitlements", $data, $format ) ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'Leave Accrual: Invalid Query: ' . $wpdb->last_error,
                    ),
                    true
                )
            );
        }

        return $this->request_data;
    }

    /**
     * Complete
     */
    protected function complete() {
        parent::complete();
    }
}
