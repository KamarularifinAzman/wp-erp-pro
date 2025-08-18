<?php

namespace WeDevs\Attendance;

class Notification {
    public function __construct() {

        /**
         * There are so many inconsistencies here. For now iy is better to comment this out.
         * @todo: Need to clean all the inconsistencies
         */
        // add_action( 'erp_per_minute_scheduled_events', [ $this, 'send_email_notification' ] );
    }

    function send_email_notification() {

        $today                = date( 'Y-m-d', current_time( 'timestamp' ) );
        $is_working_day       = erp_hr_get_work_days_without_off_day( $today, $today );
        $is_notification_sent = get_option( 'erp_attendance_sent_notification' );
        $is_notification_on   = get_option( 'attendance_reminder' );
        $working_days         = erp_hr_get_work_days();
        $key                  = strtolower( date( 'D', strtotime( $today ) ) );

        // if is not working days
        if( $working_days[ $key ] == 0 ) {
            return;
        }

        // do not support when shift enabled or on a holiday
        if ( 'yes' != $is_notification_on || ! $is_working_day['total'] || $today == $is_notification_sent ) {
           return;
        }
        $office_time = ''; // erp_att_get_office_time();
        $grace_time  = ''; // erp_att_get_grace_times();
        $hours       = 0;
        $minutes     = 0;
        $seconds     = 0;
        $str_time    = preg_replace( "/^([\d]{1,2})\:([\d]{2})$/", "$1:$2:00", $office_time['starts'] );

        sscanf( $str_time, "%d:%d:%d", $hours, $minutes, $seconds );

        $remind_time = ( $hours * 3600 + $minutes * 60 + $seconds ) + ( $grace_time['after_checkin'] * 60 );
        list( $now_year, $now_month, $now_day, $now_hour, $now_minute, $now_second ) = preg_split( '([^0-9])', current_time( 'mysql' ) );

        $now_time = ( $now_hour * 3600 + $now_minute * 60 + $now_second );

        if ( $remind_time > $now_time ) {
            return;
        }

        //find who are in leave
        $attendances = \WeDevs\ERP\HRM\Models\Leave::whereDate( 'date', '=', $today )
                                                                   ->select( 'user_id' )
                                                                   ->get()
                                                                   ->toArray();
        $attendee    = wp_list_pluck( $attendances, 'user_id' );
        //find who have already checked in
        $leaves         = \WeDevs\ERP\HRM\Models\LeaveRequest::where( 'last_status', '1' )
                                                              ->whereDate( 'start_date', '>=', strtotime( $today ) )
                                                              ->whereDate( 'end_date', '<=', strtotime( $today ) )
                                                              ->select( 'user_id' )
                                                              ->get()
                                                              ->toArray();
        $users_on_leave = wp_list_pluck( $leaves, 'user_id' );
        //then send email expect those users
        $users_not_to_notify = array_values( array_merge( $attendee, $users_on_leave ) );

        $users = \WeDevs\ERP\HRM\Models\Employee::where( 'status', 'active' )
                                                           ->whereNotIn( 'user_id', $users_not_to_notify )
                                                           ->select( 'user_id' )
                                                           ->get()
                                                           ->toArray();
        $emailer = wperp()->emailer->get_email( 'AttendanceReminder' );

        if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
            foreach ( $users as $user ) {
                $emailer->trigger( new \WeDevs\ERP\HRM\Employee( $user['user_id'] ) );
            }
        }

        update_option( 'erp_attendance_sent_notification', $today );


    }
}
