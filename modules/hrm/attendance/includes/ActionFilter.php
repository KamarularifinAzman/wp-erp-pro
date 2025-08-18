<?php

namespace WeDevs\Attendance;

use WeDevs\ERP\AddonTask;

/**
 * Action filter class
 *
 * Utilizes this class to set action & filter for attendance
 */
class ActionFilter {

    /**
     * Addon task
     *
     */
    public $addon_task;

    /**
     * Constructor class
     *
     */
    public function __construct() {
        $this->addon_task = new AddonTask();
        $this->action_hook();
    }

    /**
     * Call action & filter
     *
     */
    public function action_hook () {
        add_action( 'after_calling_erp_hr_holiday_create_hook_callback', [ $this, 'modify_date_shift_for_holiday_create' ], 10, 2 );
        add_action( 'after_calling_erp_hr_holiday_delete_hook_callback', [ $this, 'modify_date_shift_for_holiday_delete' ], 10, 2 );
        add_action( 'after_calling_erp_hr_leave_request_approved_hook_callback', [ $this, 'modify_date_shift_for_leave_approve' ], 10, 4 );
        add_action( 'after_calling_erp_hr_leave_request_pending_hook_callback', [ $this, 'modify_date_shift_for_leave_pending' ], 10, 3 );

        // Add Shift leave to calender.
        add_filter( 'filter_holidays', [ $this, 'add_shift_leave_to_hr_calender' ], 10, 3 );
        add_filter( 'work_days', [ $this, 'add_shift_work_days' ], 10, 3 );

        // Remove from shift if an employee is not active
        add_action( 'erp_hr_employee_after_update_status', [ $this, 'remove_employee_from_shift' ], 10, 3 );
    }

    /**
     * Modify date shift for creating holiday.
     *
     * @param object  $results_prev Previous result.
     * @param object  $results_now Current result.
     *
     * @since  1.0.2
     * @access public
     * @return mixed
     */
    public function modify_date_shift_for_holiday_create ( $results_prev, $results_now ) {
        if ( empty( $results_prev ) && ! empty( $results_now ) ) {
            $holidays = wp_list_pluck( $results_now, 'date' );
            foreach ( $holidays as $holiday ) {
                $this->addon_task->make_query( 'delete', 'erp_attendance_date_shift', [ 'where' => [ 'date' => $holiday ] ]);
            }
        }
        if ( ! empty( $results_prev ) && ! empty( $results_now ) ) {

            $results_now_data = "'" . implode( "','", wp_list_pluck( $results_now, 'date' ) ) . "'" ;
            $previous_working_date = $this->addon_task->make_query( 'raw', '',[ 'sql' => function( $wpdb ) use ( $results_now_data ) {
                return "SELECT * FROM {$wpdb->prefix}erp_attendance_date_shift WHERE date IN ( {$results_now_data} ) GROUP BY user_id, shift_id";
            } ] );

            if ( ! empty( $results_prev ) ){
                foreach ( $results_prev as $rsltp ) {
                    foreach ( $previous_working_date as $pwd ) {
                        unset( $pwd->id );
                        $pwd->date = $rsltp->date;
                        $pwd->start_time = $rsltp->date. ' ' . date("H:i:s", strtotime($pwd->start_time));
                        $pwd->end_time    = date('Y-m-d', strtotime('+1 day', strtotime($rsltp->date))). ' ' . date("H:i:s", strtotime($pwd->start_time));
                        $this->addon_task->make_query( 'insert', 'erp_attendance_date_shift', [ 'data' => ( array ) $pwd ] );
                    }
                }
                $this->addon_task->make_query( 'raw', '', [ 'sql' => function ( $wpdb ) use ( $results_now_data ) {
                    return "DELETE FROM {$wpdb->prefix}erp_attendance_date_shift WHERE date IN ( {$results_now_data} )";
                } ] );
            }
        }
    }

    /**
     * Modify date shift for deleting holiday.
     *
     * @param integer  $id ID.
     * @param object  $result Current result.
     *
     * @since  1.0.2
     * @access public
     * @return mixed
     */
    public function modify_date_shift_for_holiday_delete ( $id, $result ) {

    }

    /**
     * Modify date shift for approve leave.
     *
     * @param object $result Result.
     * @param object $data Current Data.
     * @param integer $id ID.
     * @param object $request Current Request.
     *
     * @since  1.0.2
     * @access public
     * @return mixed
     */
    public function modify_date_shift_for_leave_approve ( $result, $data, $id, $request ) {
        foreach ( $data as $dt ) {
            $this->addon_task->make_query( 'delete', 'erp_attendance_date_shift', [ 'where' => [ 'user_id' => $data['user_id'], 'date' => $data['date'] ] ]);
        }
    }

    /**
     * Modify date shift for pending leave.
     *
     * @param object $result Result.
     * @param integer $id ID.
     * @param object $request Current Request.
     *
     * @since  1.0.2
     * @access public
     * @return mixed
     */
    public function modify_date_shift_for_leave_pending ( $results, $id, $request ) {
        //console_log($results);
        //console_log($id);
    }

    /**
     * Add shift leave to hr calender.
     *
     * @param object $holidays Holidays.
     * @param string $start_date Start date.
     * @param string $end_date End date.
     *
     * @since  1.0.2
     * @access public
     * @return mixed
     */
    public function add_shift_leave_to_hr_calender( $holidays, $start_date, $end_date ) {
        global $wpdb;

        $user_id = get_current_user_id();

        $result  = $wpdb->get_row( $wpdb->prepare( "SELECT attshift.name, attshift.holidays FROM {$wpdb->prefix}erp_attendance_shifts as attshift LEFT JOIN {$wpdb->prefix}erp_attendance_shift_user as attshiftuser ON attshift.id = attshiftuser.shift_id WHERE attshiftuser.user_id = %d",
            $user_id ), ARRAY_A );

        if ( ! isset( $result['holidays'] ) ) {
            return $holidays;
        }

        $shift_holidays = maybe_unserialize( $result['holidays'] );

        $match_holidays = array();

        $dates = new \DatePeriod(
            new \DateTime( $start_date ),
            new \DateInterval( 'P1D' ),
            new \DateTime( $end_date )
        );

        foreach ( $dates as $index => $date ) {
            $weekday = strtolower( $date->format( 'D' ) );
            if ( in_array( $weekday, $shift_holidays ) ) {
                $match_holidays[] = array(
                    'title'      => __( 'Weekly Holiday', 'erp-pro' ),
                    'start'      => erp_current_datetime()
                        ->modify( $date->format( 'Y-m-d' ) )
                        ->setTime( 0, 0, 0 )->format( 'Y-m-d' ),
                    'end'        => erp_current_datetime()
                        ->modify( $date->format( 'Y-m-d' ) )
                        ->setTime( 23, 59, 59 )->format( 'Y-m-d' ),
                    'id'         => $index,
                    'background' => true
                );
            }
        }

        return array_merge( $holidays, $match_holidays );
    }

    /**
     * Modify work days based on shift.
     *
     * @param array $days Result of available shift days.
     *
     * @since  1.0.2
     * @access public
     * @return mixed
     */
    public function add_shift_work_days( $days ) {
        global $wpdb;

        $user_id = get_current_user_id();

        $result  = $wpdb->get_row( $wpdb->prepare( "SELECT attshift.name, attshift.holidays FROM {$wpdb->prefix}erp_attendance_shifts as attshift LEFT JOIN {$wpdb->prefix}erp_attendance_shift_user as attshiftuser ON attshift.id = attshiftuser.shift_id WHERE attshiftuser.user_id = %d",
            $user_id ), ARRAY_A );

        if ( ! isset( $result['holidays'] ) ) {
            return $days;
        }

        $shift_holidays = maybe_unserialize( $result['holidays'] );

        foreach ( $days as $index => $day ) {
           $days[$index] = in_array( $index, $shift_holidays ) ? 0 : 8;
        }

        return $days;
    }

    /**
     * Remove from shift when an employee is not active
     *
     * @since 2.0.6
     *
     * @param int $emp_id
     * @param string $status
     * @param string $from_date
     *
     * @return void
     */
    public function remove_employee_from_shift( $emp_id, $status, $from_date = false ) {
        if ( 'active' !== $status ) {
            erp_attendance_remove_user_from_shift( $emp_id, $from_date );
        }
    }
}
