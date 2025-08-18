<?php

use WeDevs\Attendance\IpUtils;
use WeDevs\Attendance\Queries\AttendanceByDate;

/**
 * Get all attendance
 *
 * @since 2.0.0
 *
 * @param $args array
 *
 * @return array
 *
 */
function erp_att_get_all_attendance( $args = [] ) {
	global $wpdb;

	$defaults = [
        'limit'  => 20,
        'offset' => 0,
        'order'  => 'ASC',
		'count'  => false
	];

	$args = wp_parse_args( $args, $defaults );

    $last_changed = erp_cache_get_last_changed( 'hrm', 'attendance', 'erp-attendance' );
    $cache_key    = 'erp-all-attendance-' . md5( serialize( $args ) ).": $last_changed";
    $items        = wp_cache_get( $cache_key, 'erp-attendance' );

    $items_count_cache_key = 'erp-all-attendance-count-' . md5( serialize( $args ) ).": $last_changed";;
    $items_count           = wp_cache_get( $items_count_cache_key, 'erp-attendance' );

    if ( false === $items ) {
        $where = '';

        if ( isset( $args['start_date'] ) && isset( $args['end_date'] ) && ! empty( $args['start_date'] ) && ! empty( $args['end_date'] ) ) {
            $start_date = $args['start_date'];
            $end_date   = $args['end_date'];
            $where     .= "AND ds.date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        if ( $args['count'] ) {
            $countSql = "SELECT count(*) as total from (SELECT ds.date, ds2.absent AS absent, ds1.present1 AS present FROM {$wpdb->prefix}erp_attendance_date_shift ds LEFT JOIN (SELECT date as date1, COUNT(present) AS present1 FROM {$wpdb->prefix}erp_attendance_date_shift WHERE present = 1 GROUP BY date) AS ds1 ON ds.date = ds1.date1 LEFT JOIN (SELECT date as date2, COUNT(date) AS absent FROM {$wpdb->prefix}erp_attendance_date_shift WHERE present IS NULL GROUP BY date) as ds2 ON ds.date = ds2.date2 LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS shifts ON ds.shift_id = shifts.id WHERE shifts.status = 1 {$where} GROUP BY ds.date) countQuery";

            $items_count = $wpdb->get_var($countSql);

            wp_cache_set( $items_count_cache_key, $items_count, 'erp-attendance' );
        }

        $sql = "SELECT ds.date, ds2.absent AS absent, ds1.present1 AS present FROM {$wpdb->prefix}erp_attendance_date_shift ds LEFT JOIN (SELECT date as date1, COUNT(present) AS present1 FROM {$wpdb->prefix}erp_attendance_date_shift WHERE present = 1 GROUP BY date) AS ds1 ON ds.date = ds1.date1 LEFT JOIN (SELECT date as date2, COUNT(date) AS absent FROM {$wpdb->prefix}erp_attendance_date_shift WHERE present IS NULL GROUP BY date) as ds2 ON ds.date = ds2.date2 LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS shifts ON ds.shift_id = shifts.id WHERE shifts.status = 1 {$where} GROUP BY ds.date ORDER BY ds.date {$args['order']} LIMIT {$args['offset']}, {$args['limit']}";

        $att_records = $wpdb->get_results( $sql );

        $items = [];

        foreach ( $att_records as $record ) {
            $items[] = (object) [
                'date'    => $record->date,
                'present' => $record->present ? $record->present : 0,
                'absent'  => $record->absent
            ];
        }

        wp_cache_set( $cache_key, $items, 'erp-attendance' );
    }

    if ( $args['count'] ) {
        return $items_count;
    }

    return $items;
}

/**
 * Fetch attendance count from database
 *
 * @since  2.0.0
 *
 * @param  array $args
 *
 * @return array
 *
 */
function erp_att_get_attendance_count( $args = [] ) {
    global $wpdb;

    return $wpdb->get_var("SELECT COUNT(DISTINCT date) as total FROM {$wpdb->prefix}erp_attendance_date_shift");
}

/**
 * Get Lists of months for attendance filter
 *
 * @since 1.0.0
 *
 * @return array
 *
 */
function erp_att_get_filters() {

	$filters = array(
		'today'        => __( 'Today', 'erp-pro' ),
		'yesterday'    => __( 'Yesterday', 'erp-pro' ),
		'this_month'   => __( 'This Month', 'erp-pro' ),
		'last_month'   => __( 'Last Month', 'erp-pro' ),
		'this_quarter' => __( 'This Quarter', 'erp-pro' ),
		'last_quarter' => __( 'Last Quarter', 'erp-pro' ),
		'this_year'    => __( 'This Year', 'erp-pro' ),
		'last_year'    => __( 'Last Year', 'erp-pro' ),
		'custom'       => __( 'Custom', 'erp-pro' )
	);

	return $filters;
}

/**
 * Get start and end date from a specific time
 *
 * @since  1.0.0
 *
 * @param  string $time
 *
 * @return  array
 *
 */
function erp_att_get_start_end_date( $time = '' ) {

	$duration = [];

	$start_date = current_time( "Y-m-d" );
	$end_date   = current_time( "Y-m-d" );

	if ( $time ) {

		switch ( $time ) {

			case 'today':

				$start_date = current_time( "Y-m-d" );
				$end_date   = $start_date;
				break;

			case 'yesterday':

				$today      = strtotime( current_time( "Y-m-d" ) );
				$start_date = date( "Y-m-d", strtotime( "-1 days", $today ) );
				$end_date   = $start_date;
				break;

			case 'last_7_days':

				$end_date   = current_time( "Y-m-d" );
				$start_date = date( "Y-m-d", strtotime( "-6 days", strtotime( $end_date ) ) );
				break;

			case 'this_month':

				$start_date = date( "Y-m-d", strtotime( "first day of this month" ) );
				$end_date   = date( "Y-m-d", current_time( 'timestamp' ) );
				break;

			case 'last_month':

				$start_date = date( "Y-m-d", strtotime( "first day of previous month" ) );
				$end_date   = date( "Y-m-d", strtotime( "last day of previous month" ) );
				break;

			case 'this_quarter':

				$current_month = date( 'm' );
				$current_year  = date( 'Y' );

				if ( $current_month >= 1 && $current_month <= 3 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-January-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '31-March-' . $current_year ) );

				} else if ( $current_month >= 4 && $current_month <= 6 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-April-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '30-June-' . $current_year ) );

				} else if ( $current_month >= 7 && $current_month <= 9 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-July-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '30-September-' . $current_year ) );

				} else if ( $current_month >= 10 && $current_month <= 12 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-October-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '31-December-' . $current_year ) );
				}
				break;

			case 'last_quarter':

				$current_month = date( 'm' );
				$current_year  = date( 'Y' );

				if ( $current_month >= 1 && $current_month <= 3 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-October-' . ( $current_year - 1 ) ) );
					$end_date   = date( 'Y-m-d', strtotime( '31-December-' . ( $current_year - 1 ) ) );

				} else if ( $current_month >= 4 && $current_month <= 6 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-January-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '31-March-' . $current_year ) );

				} else if ( $current_month >= 7 && $current_month <= 9 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-April-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '30-June-' . $current_year ) );

				} else if ( $current_month >= 10 && $current_month <= 12 ) {

					$start_date = date( 'Y-m-d', strtotime( '1-July-' . $current_year ) );
					$end_date   = date( 'Y-m-d', strtotime( '30-September-' . $current_year ) );
				}
				break;

			case 'last_year':

				$start_date = date( "Y-01-01", strtotime( "-1 year" ) );
				$end_date   = date( "Y-12-31", strtotime( "-1 year" ) );
				break;

			case 'this_year':

				$start_date = date( "Y-01-01" );
				$end_date   = date( "Y-12-31" );
				break;

			case 'custom':

				$start_date = $_REQUEST['start'];
				$end_date   = $_REQUEST['end'];
				break;

			default:
				break;
		}
	}

	$duration = [
		'start' => $start_date,
		'end'   => $end_date
	];

	return $duration;
}

/**
 * Get attendance for a single date
 *
 * @since 1.0.0
 * @since 1.1.2 Refactor to fetch all active employees including absents
 *
 * @return array
 */
function erp_att_get_single_attendance( $args ) {
    global $wpdb;

    $att_by_date = new AttendanceByDate( $args['date'] );
    $att_records = $att_by_date->get_data();

	$attendance  = [];
	foreach ( $att_records as $record ) {
        if ( !empty( $record['name'] ) ) {
            $attendance[] = [
                'dshift_id'       => $record['dshift_id'],
                'user_id'         => $record['user_id'],
                'employee_id'     => $record['employee_id'],
                'employee_name'   => $record['name'],
                'department_name' => $record['dept'],
                'present'         => $record['status'],
                'shift'           => $record['shift_name'],
                'checkin_id'      => ! empty($record['checkin_id']) ? $record['checkin_id'] : 0,
                'checkout_id'     => ! empty($record['checkout_id']) ? $record['checkout_id'] : 0,
                'checkin'         => ! empty($record['checkin']) ? $record['checkin'] : '',
                'checkout'        => ! empty($record['checkout']) && $record['checkout'] !== '00:00' ? $record['checkout'] : '',
                'worktime'        => ! empty($record['worktime']) ? erp_att_second_to_hour_min($record['worktime']) : ''
            ];
        }
    }

	return erp_array_to_object( $attendance );
}

/**
 * Calculate work details like late, early left etc.
 *
 * @param  time $shift_start
 * @param  time $shift_end
 * @param  time $checkin
 * @param  time $checkout
 * @param  array $grace_times
 *
 * @return array
 */
function calculate_work_details_by_date( $shift_start, $shift_end, $checkin, $checkout, $grace_times = [] ) {
	$allowed_time = [
		'before_checkin'  => strtotime( '-' . $grace_times['before_checkin'] . ' minutes', $shift_start ),
		'after_checkin'   => strtotime( '+' . $grace_times['after_checkin'] . ' minutes', $shift_start ),
		'before_checkout' => strtotime( '-' . $grace_times['before_checkout'] . ' minutes', $shift_end ),
		'after_checkout'  => strtotime( '+' . $grace_times['after_checkout'] . ' minutes', $shift_end )
	];


	$office_time = $shift_end - $shift_start;
	$worked_time = strtotime( $checkout ) - strtotime( $checkin );
	$early_entry = 0;
	$late        = 0;
	$early_left  = 0;
	$extra_time  = 0;

	if ( ! empty( $checkin ) ) {
		$checkin = strtotime( $checkin );

		if ( $checkin < $allowed_time['before_checkin'] ) {
			$early_entry = $shift_start - $checkin;
		}

		if ( $checkin > $allowed_time['after_checkin'] ) {
			$late = $checkin - $shift_start;
		}
	}

	if ( ! empty( $checkout ) ) {

		if ( $worked_time >= $office_time ) {
			$extra_time = $worked_time - $office_time;
		} else {
			$early_left = abs( $office_time - $worked_time );
		}
	}

	$data = [
		'early_entry' => $early_entry,
		'late'        => $late,
		'early_left'  => $early_left,
		'extra_time'  => $extra_time
	];

	return $data;
}

/**
 * Get Department ID by User ID
 *
 * @param int $user_id
 *
 * @return int
 */
function erp_att_get_user_dept( $user_id ) {
	global $wpdb;

	$dept_id = $wpdb->get_var( "SELECT department FROM {$wpdb->prefix}erp_hr_employees WHERE user_id = $user_id" );

	return $dept_id;
}

/**
 * Gets attendance record for an user between dates
 *
 * @param  int $user_id
 * @param  date $date_start
 * @param  date $date_end
 *
 * @return object
 */
function erp_att_get_attendance_record_between_dates( $date_start, $date_end, $user_id = 0 ) {
	global $wpdb;

	$query = 'SELECT user_id, date, present, checkin, checkout FROM ' . $wpdb->prefix . 'erp_attendance WHERE date >= "' . $date_start . '" AND date <= "' . $date_end . '"';

	if ( $user_id ) {
		$query .= ' AND user_id = ' . $user_id;
	}

	$att_record = $wpdb->get_results( $query, ARRAY_A );

	return $att_record;
}

/**
 * Get query times
 *
 * @since 1.0
 *
 * @return array
 */
function erp_att_get_query_times() {
	return [
		'this_month'   => __( 'This Month', 'erp-pro' ),
		'last_month'   => __( 'Last Month', 'erp-pro' ),
		'this_quarter' => __( 'This Quarter', 'erp-pro' ),
		'last_quarter' => __( 'Last Quarter', 'erp-pro' ),
		'this_year'    => __( 'This Year', 'erp-pro' ),
		'last_year'    => __( 'Last Year', 'erp-pro' ),
		'custom'       => __( 'Custom', 'erp-pro' ),
	];
}

/**
 * Converts second to hour minute
 *
 * @return string
 */
function erp_att_second_to_hour_min( $sec, $format = "h" ) {

	$init    = $sec;
	$hours   = floor( $init / 3600 );
	$minutes = floor( ( $init / 60 ) % 60 );
	// $seconds = $init % 60;
	if ( 'h' == $format ) {
		return $hours . 'h ' . $minutes . 'm';
	}

	if ( ':' == $format ) {
		$time = date( 'h:i A', strtotime( '0000-00-00' ) + $sec );

		return $time;
	}

	return false;
}

/**
 * Checks if the string is valid date time
 *
 * @param $date
 * @param string $format
 *
 * @return bool
 */
function erp_att_validate_date_time( $date, $format = 'Y-m-d H:i:s' ) {

	$d = DateTime::createFromFormat( $format, $date );

	return $d && $d->format( $format ) == $date;
}

/**
 * Crates new Attendance if same entry not exists
 *
 * @param $shift_id
 * @param $checkin
 * @param $checkout
 *
 * @return object Eloquent Attendance model object
 */
function erp_att_insert_attendance( $shift_id, $present, $checkin, $checkout ) {
	$attendance           = \WeDevs\Attendance\Models\Attendance::find( absint( $shift_id ) );
	$attendance->present  = $present;
	$attendance->checkin  = $checkin;
	$attendance->checkout = $checkout;
	$attendance->save();

	return $attendance;
}

/**
 * Update single attendance
 *
 * @param $attendance_id
 * @param $user_id
 * @param $shift_id
 * @param $date
 * @param $checkin
 * @param $checkout
 */
function erp_att_update_attendance( $attendance_id, $user_id, $shift_id, $date, $checkin, $checkout ) {
	$attendance = new \WeDevs\Attendance\Models\Attendance();

	$single_attendance = $attendance::find( $attendance_id );

	$single_attendance->user_id  = $user_id;
	$single_attendance->shift_id = $shift_id;
	$single_attendance->date     = $date;
	$single_attendance->checkin  = $checkin;
	$single_attendance->checkout = $checkout;

	$single_attendance->save();
}

/**
 * Get attendance data within a date range
 *
 * @since 1.1.0
 * @since 1.1.1 Add checkin grace time offset
 *
 * @param string $start
 * @param string $end
 * @param integer $user_id
 * @param boolean $filtered
 *
 * @return array|object in case of not filtered Illuminate Collection object will be returned
 */
function erp_att_get_attendance_data( $start, $end, $user_id = 0, $filtered = false ) {
    global $wpdb;

    $sql = sprintf("SELECT SUM( IF (present = 1, 1, 0) ) as presents,
        SUM( IF (present IS NULL, 1, 0) ) as absents,
        SUM( IF (late IS NULL OR late = 0, 0, 1) ) as lates
        FROM {$wpdb->prefix}erp_attendance_date_shift
        WHERE user_id = %d AND
        date BETWEEN '%s' AND '%s'", intval( $user_id ), $start, $end );

    $result = $wpdb->get_row( $sql );

    $absents         = isset($result->absents) ? $result->absents : 0;
    $late_checkins   = isset($result->lates) ? $result->lates : 0;
    $proper_checkins = isset($result->presents, $result->lates) ? $result->presents - $result->lates : 0;

	return [
		'absents'         => $absents,
		'late_checkins'   => $late_checkins,
		'proper_checkins' => $proper_checkins
	];
}

/**
 * Check if IP checkin/out restriction is enabled
 *
 * @return boolean
 */
function erp_att_has_restriction() {
	return erp_get_option( 'erp_at_enable_ip_restriction', false, 'no' ) == 'yes';
}

/**
 * Get all allowed IP's for checkin/out
 *
 * @return array
 */
function erp_att_get_allowed_ips() {
	$allowed_ips = explode( "\n", erp_get_option( 'erp_at_whitelisted_ips' ) );
	$allowed_ips = array_map( 'trim', $allowed_ips );

	return $allowed_ips;
}

/**
 * Check if a IP is allowed in the whitelist
 *
 * @param  string $ip_address
 *
 * @return boolean
 */
function erp_att_is_ip_allowed( $ip_address ) {
	$allowed_ips = erp_att_get_allowed_ips();

	// if no IP found, we assume every IP is allowed
	if ( ! $allowed_ips ) {
		return true;
	}

	if ( IpUtils::checkIp( $ip_address, $allowed_ips ) ) {
		return true;
	}

	return false;
}


// Export

function export_to_csv() {
	if ( isset( $_REQUEST['submit_export'] ) ) {
        global $wpdb;

        if ( empty( $_REQUEST['erp_att_db_table'] ) ) {
            exit;
        }

        $table_slug = sanitize_text_field( $_REQUEST['erp_att_db_table'] );
        $table      = $wpdb->prefix . 'erp_attendance_' . $table_slug;

        $items = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        $file  = $table . '_' . date( 'd_m_Y' ) . '.csv';
        erp_make_csv_file( $items, $file );
	}
}

add_action( 'init', 'export_to_csv' );

/**
 * Get attendance summary
 *
 * @since 1.0.0
 *
 * @param $employee_ids
 * @param $start_date
 * @param null $end_date
 * @param string $type
 *
 * @return array
 *
 */
function erp_get_attendance_summary( $employee_ids, $start_date, $end_date = null, $type = 'employee_based', $per_page = null, $offset = null, $total = false ) {
    global $wpdb;

    $reports     = [];

    if ( $end_date == null ) {
		$end_date = date( 'Y-m-d', current_time( 'timestamp' ) );
	}

    $end_date = $end_date . ' 23:59:59';

    $leave_start_date = erp_current_datetime()->modify( $start_date )->setTime( 0, 0, 0 )->getTimestamp();
    $leave_end_date = erp_current_datetime()->modify( $end_date )->setTime( 23, 59, 59)->getTimestamp();

	if ( $type == 'employee_based' ) {
        $employee_ids_string = implode( ',', $employee_ids );

        $sql = "SELECT SQL_CALC_FOUND_ROWS
                ds.user_id as user_id,
                SUM( IF (ds.present = 1, 1, 0) ) as presents,
                SUM( IF (ds.present IS NULL, 1, 0) ) as absents,
                ( SELECT SUM(time) FROM {$wpdb->prefix}erp_attendance_log WHERE user_id = ds.user_id AND checkin BETWEEN '{$start_date}' AND '{$end_date}' ) as avg_worktime,
                (SELECT AVG(tal.checkin) FROM ( SELECT MIN(TIME_TO_SEC(checkin)) as checkin, user_id FROM {$wpdb->prefix}erp_attendance_log WHERE checkin BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY date_shift_id) as tal WHERE tal.user_id = ds.user_id) as avg_checkin,
                (SELECT AVG(tal.checkout) FROM ( SELECT MAX(TIME_TO_SEC(checkout)) as checkout, user_id FROM {$wpdb->prefix}erp_attendance_log WHERE checkin BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY date_shift_id) as tal WHERE tal.user_id = ds.user_id) as avg_checkout,
                ( SELECT COUNT(leave_date) FROM {$wpdb->prefix}erp_hr_leave_request_details WHERE user_id = ds.user_id AND leave_date between $leave_start_date and $leave_end_date ) as leaves
                FROM `{$wpdb->prefix}erp_attendance_date_shift` as ds
                WHERE ds.user_id IN ( {$employee_ids_string} ) AND
                ds.date BETWEEN '{$start_date}' AND '{$end_date}'
                GROUP BY ds.user_id";

        if ( $per_page !== NULL && $offset !== NULL ) {
            $sql .= " LIMIT {$offset}, {$per_page}";
        }

        $results  = $wpdb->get_results( $sql, ARRAY_A );
        $total_row_found = absint( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );

        if ( $total == true ) {
            return $total_row_found;
        }


		foreach ( $results as $result ) {
			$att_report = [
                'user_id'                => $result['user_id'],
				'total_present'          => $result['presents'],
				'total_absent'           => $result['absents'],
				'total_leaves'           => $result['leaves'],
				'avg_checkin'            => $result['avg_checkin'],
				'avg_checkout'           => $result['avg_checkout'],
				'avg_worktime'           => $result['presents'] == 0 ? 0 : ( $result['avg_worktime'] / $result['presents'] )
			];

			$reports[] = $att_report;
		}
	}

	if ( $type == 'date_based' ) {

        // $dates = erp_extract_dates( $start_date, $end_date );

        $employee_ids_string = implode( ',', $employee_ids );

        $sql = "SELECT date,
                SUM(IF(present IS NULL, NULL, 1)) AS presents,
                SUM(IF(present IS NULL, 1, NULL)) AS absents
                FROM {$wpdb->prefix}erp_attendance_date_shift as ds
                WHERE
                user_id IN ( {$employee_ids_string} ) AND
                date BETWEEN '{$start_date}' AND '{$end_date}' GROUP BY date";


        $results = $wpdb->get_results( $sql, ARRAY_A );

        $leaves = array();
        if ( ! empty( $results ) ) {
            $sql = "SELECT leave_date, COUNT(leave_date) as cnt FROM {$wpdb->prefix}erp_hr_leave_request_details
                    WHERE user_id IN ( {$employee_ids_string} ) AND leave_date between $leave_start_date and $leave_end_date
                    GROUP BY leave_date ";
            $leaves = $wpdb->get_results( $sql );
            $leaves = wp_list_pluck( $leaves, 'cnt', 'leave_date' );
        }

		foreach ( $results as $result ) {
            $date_ts = erp_current_datetime()->modify( $result['date'] . ' 00:00:00' )->getTimestamp();
            $leave_count = array_key_exists( $date_ts, $leaves ) ? $leaves[ $date_ts ] : 0;
			$att_report = [
				'date'          => $result['date'],
				'total'         => intval( $result['presents'] ) + intval( $result['absents'] ),
				'total_present' => $result['presents'],
				'total_leaves'  => $leave_count,
				'total_absent'  => intval( $result['absents'] ),
				'comment'       => ''
            ];

			$reports[] = $att_report;
		}
	}

	return $reports;
}

/**
 * Employee attendance report
 *
 * @since  2.0.0
 *
 * @param  ini $user_id
 * @param  string $start_date
 * @param  string $end_date
 *
 * @return array
 *
 */
function erp_get_emp_attendance_report( $user_id, $start_date, $end_date = null ) {
    global $wpdb;

    if ( $end_date == null ) {
		$end_date = date( 'Y-m-d', current_time( 'timestamp' ) );
	}

	if ( ! erp_is_valid_date( $start_date ) || ! erp_is_valid_date( $end_date ) ) {
		return new WP_Error( 'invalid-date', __( 'Invalid date supplied', 'erp' ) );
	}

	$start_date = date( 'Y-m-d', strtotime( $start_date ) );

    $results = [
		'summary' => [
			'dates'            => '',
			'working_days'     => '',
			'present'          => '',
			'leaves'           => '',
			'absent'           => '',
			'holidays'         => '',
			'missing_checkout' => '',
			'late'             => '',
			'early_left'       => '',
			'avg_checkin'      => '',
			'avg_checkout'     => '',
            'avg_worktime'     => '',
            'worktime'         => '',
			'overtime'         => '',
			'percentage'       => ''
		],
		'attendances' => []
	];

    $sql1 = "SELECT COUNT(date) AS total,
            COUNT( IF(day_type = 'working_day', 1, NULL ) ) AS working_day,
            COUNT( IF(day_type = 'leave', 1, NULL ) ) AS leaves,
            COUNT( IF (late IS NULL, NULL, 1 ) ) as late,
            COUNT( IF (present IS NULL, NULL, 1) ) AS present,
            COUNT( IF (early_left IS NULL, NULL, 1) ) AS early_left
            FROM {$wpdb->prefix}erp_attendance_date_shift
            WHERE user_id = {$user_id} AND date BETWEEN '{$start_date}' AND '{$end_date}'";

    $sql2 = "SELECT SUM( TIME_TO_SEC(timediff( ds.end_time, ds.start_time )) ) AS total_shift_time,
            SUM(al.time) AS worked_time
            FROM {$wpdb->prefix}erp_attendance_log AS al
            INNER JOIN {$wpdb->prefix}erp_attendance_date_shift AS ds
            ON al.date_shift_id = ds.id
            WHERE ds.user_id = {$user_id} AND ds.date BETWEEN '{$start_date}' AND '{$end_date}'";

    $sql3 = "SELECT SUM(mc) AS missing_checkout,
            AVG(TIME_TO_SEC(avg_ckin)) AS avg_checkin,
            AVG(TIME_TO_SEC(avg_ckout)) AS avg_checkout
            FROM ( SELECT IF( MAX(checkout ) IS NULL, 1, NULL ) AS mc,
            min(checkin) AS avg_ckin,
            max(checkout) AS avg_ckout,
            date_shift_id FROM {$wpdb->prefix}erp_attendance_log AS al
            INNER JOIN {$wpdb->prefix}erp_attendance_date_shift AS ds ON al.date_shift_id = ds.id
            WHERE ds.date BETWEEN '{$start_date}' AND '{$end_date}' AND ds.user_id = {$user_id}
            GROUP BY date_shift_id) dshift";

    $sql4 = "SELECT shifts.name AS shift_title, ds.date, ds.present, ds.day_type, al.worktime, ds.late, ds.early_left,
            TIME_TO_SEC( TIME_FORMAT( TIME( ds.start_time ), '%H:%i:%s' ) ) start_time,
            TIME_TO_SEC( TIME_FORMAT( TIME( ds.end_time ), '%H:%i:%s' ) ) end_time,
            TIME_TO_SEC( TIME_FORMAT( TIME( al.checkin ), '%H:%i:%s' ) ) checkin,
            TIME_TO_SEC( TIME_FORMAT( TIME( al.checkout ), '%H:%i:%s' ) ) checkout,
            TIME_TO_SEC( TIMEDIFF( ds.end_time, ds.start_time ) ) as shift_time
            FROM {$wpdb->prefix}erp_attendance_date_shift AS ds LEFT JOIN
            (SELECT date_shift_id, min(checkin) checkin, max(checkout) checkout, sum(TIME) worktime
            FROM {$wpdb->prefix}erp_attendance_log GROUP BY date_shift_id) as al ON ds.id = al.date_shift_id
            INNER JOIN  {$wpdb->prefix}erp_attendance_shifts as shifts ON ds.shift_id = shifts.id
            WHERE ds.user_id = {$user_id} AND ds.date BETWEEN '{$start_date}' AND '{$end_date}'";


    $data1 = $wpdb->get_row( $sql1 );
    $data2 = $wpdb->get_row( $sql2 );
    $data3 = $wpdb->get_row( $sql3 );
    $data4 = $wpdb->get_results( $sql4 );


    if ( ! empty( $data1->total ) ) {
        $results['summary']['dates'] = $data1->total;
    }

    if ( ! empty( $data1->working_day ) ) {
        $results['summary']['working_days'] = $data1->working_day;
    }

    if ( ! empty( $data1->present ) ) {
        $results['summary']['present'] = $data1->present;
    }

    if ( ! empty( $data1->leaves ) ) {
        $results['summary']['leaves'] = $data1->leaves;
    }

    if ( ! empty( $data1->late ) ) {
        $results['summary']['late'] = $data1->late;
    }

    if ( ! empty( $data1->early_left ) ) {
        $results['summary']['early_left'] = $data1->early_left;
    }

    if ( ! empty( $data1->total ) && ! empty( $data1->working_day ) ) {
        $results['summary']['holidays'] = absint( $data1->total ) - absint( $data1->working_day );
    }

    if ( ! empty( $data1->working_day ) && ! empty( $data1->present ) ) {
        $results['summary']['absent']     = absint( $data1->working_day ) - absint( $data1->present );
        $results['summary']['percentage'] = number_format( $data1->present / $data1->working_day, 2 );
    }

    if ( ! empty( $data2->worked_time ) ) {
        $results['summary']['worktime'] = $data2->worked_time;
    }

    if ( ! empty( $data2->total_shift_time ) && ! empty( $data2->worked_time )  ) {
        $overtime = 0;

        if ( absint( $data2->worked_time ) > absint( $data2->total_shift_time ) ) {
            $overtime = absint( $data2->worked_time ) - absint( $data2->total_shift_time );
        }

        $results['summary']['overtime'] = $overtime;
    }

    if ( ! empty( $data3->avg_checkin ) ) {
        $results['summary']['avg_checkin'] = $data3->avg_checkin;
    }

    if ( ! empty( $data3->avg_checkout ) ) {
        $results['summary']['avg_checkout'] = $data3->avg_checkout;
    }

    if ( ! empty( $data1->working_day ) && ! empty( $data2->worked_time )  ) {
        $results['summary']['avg_worktime'] = absint( $data2->worked_time ) / absint( $data1->working_day );
    }

    if ( ! empty( $data3->missing_checkout ) ) {
        $results['summary']['missing_checkout'] = $data3->missing_checkout;
    }

    foreach ( $data4 as $d4 ) {
        $attendances = [
            'date'           => $d4->date,
			'checkin'        => $d4->checkin,
			'checkout'       => $d4->checkout,
            'worktime'       => $d4->worktime,
            'late_time'      => $d4->late,
            'earlyleft_time' => $d4->early_left,
            'shift'          => '-',
            'start'          => $d4->start_time,
            'end'            => $d4->end_time,
            'status'         => '',
            'overtime'       => '',
			'comments'       => []
        ];

        if ( $d4->day_type === 'working_day' ) {
            $attendances['status'] = 'present';
        }
        if ( empty( $d4->present ) ) {
            $attendances['status'] = 'absent';
        }
        if ( $d4->day_type === 'weekend' ) {
            $attendances['status'] = 'holiday';
        }
        if ( $d4->day_type === 'leave' ) {
            $attendances['status'] = 'leave';
        }

        if ( $d4->shift_time < $d4->worktime ) {
            $attendances['overtime'] = $d4->worktime - $d4->shift_time;
        }

        $results['attendances'][] = $attendances;
    }

    return $results;
}

/**
 *
 * Insert shift
 *
 * @since 1.2.0
 *
 * @param $start_time
 * @param $end_time
 * @param null $shift_name
 * @param array $holidays
 * @param int $status
 *
 * @return false|int|\WP_Error
 *
 */
function erp_attendance_insert_shift( $start_time, $end_time, $shift_name = null, $holidays = [], $status = 1 ) {
	global $wpdb;
	$start_time_timestamp  = strtotime( $start_time );
	$end_time_timestamp    = strtotime( $end_time );
	$date_period_timestamp = $start_time_timestamp + 86400;

	if ( $start_time_timestamp >= $end_time_timestamp ) {
		$end_time_timestamp += 86400;
	}

	$time_diff = $end_time_timestamp - $start_time_timestamp;

	if ( $end_time_timestamp >= $date_period_timestamp ) {
		return new WP_Error( 'invalid-shift-range', __( 'Time range for the shift is invalid. Please choose shift length less than 24hrs', 'erp-pro' ) );
	}

	if ( empty( $shift_name ) ) {
		$unique_number = rand( 1, 1000 );
		$shift_name    = "Random shift {$unique_number}";
	}

	$exist = erp_atts_is_duplicate_shift( $shift_name, $start_time, $end_time, $holidays );

	if ( $exist ) {
		return new WP_Error( 'duplicate-shift', __( 'A shift with the same name/time range already exists. Duplicate shift is not allowed.', 'erp-pro' ) );
	}

	$duration = $time_diff;

	$inserted = $wpdb->insert(
		"{$wpdb->prefix}erp_attendance_shifts",
		array(
			'name'       => $shift_name,
			'start_time' => $start_time,
			'end_time'   => $end_time,
			'duration'   => $duration,
			'holidays'   => serialize( $holidays ),
			'status'     => $status,
		),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
		)
	);


	if ( ! $inserted ) {
		return new WP_Error( 'shift-insert-failed', __( 'Could not create shift.', 'erp-pro' ) );
	}

	$shift_id = $wpdb->insert_id;

    erp_attendance_purge_cache( [ 'list' => 'shifts', 'shift_id' => $shift_id ] );

	do_action( 'erp_attendance_after_insert_shift', $shift_id, $start_time_timestamp, $end_time_timestamp, $duration );

	return $shift_id;
}

/**
 * Update shift
 *
 * @since  2.0.0
 *
 * @param  int     $shift_id
 * @param  string  $start_time
 * @param  string  $end_time
 * @param  string  $shift_name
 * @param  array   $holidays
 * @param  integer $status
 *
 * @return array
 *
 */
function erp_attendance_update_shift( $shift_id, $start_time, $end_time, $shift_name = null, $holidays = [], $status = 1 ) {
	global $wpdb;
	$start_time_timestamp  = strtotime( $start_time );
	$end_time_timestamp    = strtotime( $end_time );
	$date_period_timestamp = $start_time_timestamp + 86400;

	if ( $start_time_timestamp >= $end_time_timestamp ) {
		$end_time_timestamp += 86400;
	}

	$time_diff = $end_time_timestamp - $start_time_timestamp;

	if ( $end_time_timestamp >= $date_period_timestamp ) {
		return new WP_Error( 'invalid-shift-range', __( 'Time range for the shift is invalid. Please choose shift length less than 24hrs', 'erp-pro' ) );
	}

	if ( empty( $shift_name ) ) {
		$unique_number = rand( 1, 1000 );
		$shift_name    = "Random shift {$unique_number}";
	}

	$exist = erp_atts_is_duplicate_shift( $shift_name, $start_time, $end_time, $holidays, $shift_id );

	if ( $exist ) {
		return new WP_Error( 'duplicate-shift', __( 'A shift with the same name/time range already exists. Duplicate shift is not allowed.', 'erp-pro' ) );
	}

	$duration = $time_diff;

	$updated = $wpdb->update(
		"{$wpdb->prefix}erp_attendance_shifts",
		[
			'name'       => $shift_name,
			'start_time' => $start_time,
			'end_time'   => $end_time,
			'duration'   => $duration,
			'holidays'   => serialize( $holidays ),
			'status'     => $status,
        ],
        [ 'id' => $shift_id ],
        [ '%s', '%s', '%s', '%s', '%s', '%d' ],
        [ '%d' ]
	);

    erp_attendance_purge_cache( [ 'list' => 'shifts', 'shift_id' => $shift_id ] );

	if ( ! $updated ) {
		return new WP_Error( 'shift-update-failed', __( 'Could not update shift.', 'erp-pro' ) );
	}

	do_action( 'erp_attendance_after_update_shift', $shift_id, $start_time_timestamp, $end_time_timestamp, $duration );

	return [
        'id'         => $shift_id,
        'name'       => $shift_name,
        'start_time' => $start_time,
        'end_time'   => $end_time,
        'duration'   => $duration,
        'holidays'   => $holidays,
        'status'     => $status
    ];
}

/**
 * Remove a shift
 *
 * @since  2.0.0
 *
 * @param  int $shift_id
 *
 * @return void
 *
 */
function erp_att_delete_shift( $shift_id ) {
    global $wpdb;

    $wpdb->update(
        "{$wpdb->prefix}erp_attendance_shifts",
        array( 'status' => 0 ),
        array( 'id' => $shift_id ),
        array( '%d' ),
        array( '%d' )
    );

    erp_attendance_purge_cache( [ 'list' => 'shifts', 'shift_id' => $shift_id ] );

    // $wpdb->delete( "{$wpdb->prefix}erp_attendance_shifts", array( 'id' => $shift_id ) );
}

/**
 * Remove multiple shift
 *
 * @since  2.0.0
 *
 * @param  array $shift_ids
 *
 * @return void
 *
 */
function erp_att_delete_shifts( $shift_ids ) {
	global $wpdb;

	$wpdb->query("UPDATE {$wpdb->prefix}erp_attendance_shifts SET status = 0 WHERE id IN ({$shift_ids})");

    erp_attendance_purge_cache( [ 'list' => 'shifts' ] );

    foreach ( $shift_ids as $shift_id ) {
        erp_attendance_purge_cache( [ 'shift_id' => $shift_id ] );
    }

	// $wpdb->query("DELETE FROM {$wpdb->prefix}erp_attendance_shifts WHERE id IN ({$shift_ids})");
}

/**
 * Insert date and shifting record
 * @since 1.2.0
 *
 * @param string $start_time hours 9:00:00
 * @param int $duration int seconds
 * @param int $shift_id
 * @param string $date format 'Y-m-d'
 *
 * @return boolean
 */
function erp_attendance_insert_date_shift( $start_time, $duration, $shift_id, $date ) {
	global $wpdb;

	//either timestamp or 9:00:00
	if ( is_string( $start_time ) && strpos( $start_time, ':' ) !== false ) {
		$start_time = strtotime( "$date $start_time" );
	}

	$end_time       = strtotime( "+ $duration seconds", $start_time );
	$start_time_sql = date( 'Y-m-d H:i:s', $start_time );
	$end_time_sql   = date( 'Y-m-d H:i:s', $end_time );

    erp_attendance_purge_cache( [ 'list' => 'shifts' ] );

	return $wpdb->query( "INSERT INTO {$wpdb->prefix}erp_attendance_date_shift (date, shift_id, start_time, end_time) VALUES ('$date', '$shift_id', '$start_time_sql', '$end_time_sql')" );
}

/**
 * Insert shipping details for an user
 *
 * @since 1.0.
 *
 * @param $start_date
 * @param $end_date
 * @param $shift_id
 * @param $user_id
 * @param bool $overwrite
 *
 * @return false|int
 *
 */
function erp_attendance_insert_shifting_for_user( $shift_id, $user_id, $start_date = null, $end_date = null, $overwrite = false ) {
	global $wpdb;

	if ( empty( $start_date ) ) {
		$start_date = date( 'Y-m-01', current_time( 'timestamp' ) );
	}

	if ( empty( $end_date ) ) {
		$end_date = date( 'Y-m-t', current_time( 'timestamp' ) );
	}

    $values      = [];
	$dates       = erp_extract_dates( $start_date, $end_date );
	$holidays    = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );
	$leaves      = (array) erp_hrm_is_leave_recored_exist_between_date_for_attendence( $start_date, $end_date, $user_id );
	$shift       = erp_attendance_get_shift( $shift_id );
    $valid_dates = [];


    $wpdb->insert( $wpdb->prefix . 'erp_attendence_shift_generated_to', [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'generated_by' => get_current_user_id(),
        'shift_id' => $shift->id,
    ] );


	foreach ( $dates as $date ) {
		//if set past dates then skip
		if ( !$overwrite && ( strtotime( $date ) <= current_time( 'timestamp' ) ) ) {
			continue;
        }

		$start_time_sql = date( 'Y-m-d H:i:s', strtotime( "$date $shift->start_time" ) );
		$end_time       = strtotime( "+ 86400 seconds", strtotime( $start_time_sql ) );
		$end_time_sql   = date( 'Y-m-d H:i:s', $end_time );
		$day_name       = strtolower( date( 'D', strtotime( $date ) ) );

        $date_type      = 'working_day';


        if ( is_array( $holidays ) && in_array( $date, $holidays ) ) {
            $is_off         = true;
        } elseif ( in_array( $day_name, $shift->holidays ) ) {
            $is_off         = true;
        } elseif ( is_array( $leaves ) &&  in_array( $date, $leaves ) ) {
            $is_off         = true;
        } else {
            $is_off         = false;
        }
        if ( $is_off == false ) {
            $valid_dates[] = $date;
            $values[] = "( '$date', '$user_id', '$shift_id', '$start_time_sql', '$end_time_sql', '$date_type')";
        }
	}

	if ( $overwrite ) {
        /**
         * We are going to remove all the date shifts
         * that exist from the start of new date range.
         * Because we need to make sure there exists
         * no previous date shift in the new date range
         * and no backdated shift can still includes
         * the employees indirectly by those date shifts.
         */
		$wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}erp_attendance_date_shift
                WHERE `date` >= %s
                AND `user_id` = %d",
                [ $start_date, $user_id ]
            )
        );
    }

	if ( empty( $values ) ) {
		return false;
	}

	$values = implode( ', ', $values );
	$sql    = "INSERT INTO {$wpdb->prefix}erp_attendance_date_shift (date,user_id,shift_id,start_time,end_time, day_type) VALUES $values ;";
    $result = $wpdb->query( $sql );

	return $result;
}

/**
 * Get shift
 *
 * @since  2.0.0
 *
 * @param  int $shift_id
 *
 * @return object shift object
 *
 */
function erp_attendance_get_shift( $shift_id ) {
	global $wpdb;
	$shift_id = intval( $shift_id );
	$result   = $wpdb->get_row( "SELECT
			shift.*,
			min(date_shift.date) min_date,
			max(date_shift.date) max_date
			FROM {$wpdb->prefix}erp_attendance_shifts AS shift
		LEFT JOIN {$wpdb->prefix}erp_attendance_date_shift AS date_shift ON date_shift.shift_id = shift.id
		WHERE shift.id = {$shift_id}", ARRAY_A );

	if ( $result ) {
		$result = (object) array_map( 'maybe_unserialize', $result );
	}

	return $result;
}

/**
 * Attendance report
 *
 * @since  2.0.0
 *
 * @param  int $user_id
 * @param  string $start_date
 * @param  string $end_date
 * @param  int $shift_id
 *
 * @return object
 *
 */
function erp_get_attendance_report( $user_id, $start_date, $end_date = null, $shift_id = null ) {
	global $wpdb;
	$sql   = "SELECT * FROM {$wpdb->prefix}erp_attendance_date_shift WHERE shift_id = '$shift_id' AND (DATE(start_time) BETWEEN '$start_date' AND '$end_date')";
	$dates = $wpdb->get_results( $sql );
}

/**
 * Assign shift to an user
 * @since 1.0.
 *
 * @param int $user_id
 * @param int $shift_id
 * @param bool $overwrite
 *
 * @return int|\WP_Error
 *
 */
function erp_attendance_assign_shift( $user_id, $shift_id, $overwrite = false ) {
	global $wpdb;
	$user_shift = erp_attendance_get_user_shift( $user_id );

	if ( ! $overwrite && $user_shift ) {

        $sql = sprintf( "UPDATE {$wpdb->prefix}erp_attendance_shift_user SET status='%s'  WHERE shift_id='%s' AND user_id='%s'",intval(1), intval( $shift_id ), intval( $user_id ) );
        $result = $wpdb->query( $sql );

		return new WP_Error( 'invalid-action', __( 'User already assigned to a shift. Do you want to overwrite?', 'erp-pro' ) );
	}

	if ( $user_shift && ! empty( $user_shift->id ) && $user_shift->id == $shift_id ) {
        $sql = sprintf( "UPDATE {$wpdb->prefix}erp_attendance_shift_user SET status='%s'  WHERE shift_id='%s' AND user_id='%s'",intval(1), intval( $shift_id ), intval( $user_id ) );
        $result = $wpdb->query( $sql );
		return new WP_Error( 'invalid-action', __( 'User already assigned to the shift before.', 'erp-pro' ) );
	}

	if ( $user_shift && $user_shift->id != $shift_id ) {
		$sql = sprintf( "UPDATE {$wpdb->prefix}erp_attendance_shift_user SET shift_id='%s', status='%s'  WHERE user_id='%s'", intval( $shift_id ), intval( $user_id ), intval(1) );

		return $wpdb->query( $sql );
	}

	$sql = sprintf( "INSERT INTO {$wpdb->prefix}erp_attendance_shift_user (user_id, shift_id, status) VALUES('%s', '%s')", intval( $user_id ), intval( $shift_id ), intval( 1 ) );

	return $wpdb->query( $sql );
}

/**
 *
 * @since 1.0.0
 *
 * @param int $user_id
 *
 * @return null|object
 *
 */
function erp_attendance_get_user_shift( $user_id ) {
	global $wpdb;

	$sql   = sprintf( "SELECT shifts.* FROM {$wpdb->prefix}erp_attendance_shift_user as shift_user INNER JOIN {$wpdb->prefix}erp_attendance_shifts as shifts ON shifts.id=shift_user.shift_id WHERE shifts.status = 1 AND shift_user.user_id = '%s'", intval( $user_id ) );
	$shift = $wpdb->get_row( $sql, ARRAY_A );

	if ( $shift ) {
		$shift = (object) array_map( 'maybe_unserialize', $shift );
	}

	if ( ! $shift && defined( 'WPERP_ATTENDANCE_USE_DEFAULT_SHIFT' ) && WPERP_ATTENDANCE_USE_DEFAULT_SHIFT === true ) {
		$shift = erp_attendance_get_default_shift();
	}

	return $shift;
}

/**
 *
 * Default shift
 *
 * @since 2.0.0
 *
 * @return object
 *
 */
function erp_attendance_get_default_shift() {
    global $wpdb;

	$sql    = "SELECT * FROM {$wpdb->prefix}erp_attendance_shifts ORDER BY id ASC LIMIT 1";
	$result = $wpdb->get_row( $sql, ARRAY_A );
	if ( $result ) {
		$result = (object) array_map( 'maybe_unserialize', $result );
	}

	return $result;
}

/**
 *
 * Get only the users id from shift
 *
 * @since 2.0.0
 *
 * @param int $shift_id
 *
 * @return int
 *
 */
function erp_attendance_get_shift_users_id( $shift_id ) {
	global $wpdb;

    $sql = "SELECT user_id FROM {$wpdb->prefix}erp_attendance_shift_user WHERE shift_id = $shift_id";

	return $wpdb->get_col( $sql );
}

/**
 *
 * Get employees from shift along with thier department and designation
 *
 * @since 2.0.0
 *
 * @param int $shift_id
 * @param int $limit
 * @param int $offset
 *
 * @return array
 *
 */
function erp_attendance_get_shift_users( $args = [] ) {
	global $wpdb;

	$defaults = [
        'limit'  => 20,
        'offset' => 0,
        'order'  => 'ASC',
		'count'  => false
	];

	$args = wp_parse_args( $args, $defaults );

	if ( $args['count'] ) {
		return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_attendance_shift_user WHERE shift_id = {$args['shift_id']}" );
	}

	$sql = "SELECT CONCAT_WS(' ', umeta1.meta_value, umeta2.meta_value) as name,
		hr_depts.title as dep,
		hr_degns.title as deg,
		hr_employees.user_id,
		min(date_shift.date) min_date,
		max(date_shift.date) max_date
		FROM {$wpdb->prefix}erp_attendance_shift_user as shift_user
		LEFT JOIN {$wpdb->prefix}erp_attendance_date_shift AS date_shift ON date_shift.shift_id = shift_user.shift_id
		INNER JOIN {$wpdb->prefix}erp_hr_employees as hr_employees ON shift_user.user_id = hr_employees.user_id
		LEFT JOIN {$wpdb->prefix}erp_hr_depts as hr_depts ON hr_depts.id = hr_employees.department
		LEFT JOIN {$wpdb->prefix}erp_hr_designations as hr_degns ON hr_degns.id = hr_employees.designation
		INNER JOIN {$wpdb->prefix}usermeta as umeta1 ON umeta1.user_id = hr_employees.user_id
		INNER JOIN {$wpdb->prefix}usermeta as umeta2 ON umeta2.user_id = hr_employees.user_id
		WHERE shift_user.shift_id = {$args['shift_id']} AND umeta1.meta_key = 'first_name' AND umeta2.meta_key = 'last_name' AND shift_user.status = 1
		GROUP BY shift_user.user_id
		ORDER BY name {$args['order']}";

	if ( !empty( $args['limit'] ) ) {
		$sql .= " LIMIT {$args['offset']}, {$args['limit']}";
	}

	return $wpdb->get_results( $sql );
}

/**
 * Total numbers of employees in a shift
 *
 * @since 2.0.0
 *
 * @param int $shift_id
 *
 * @return null|object
 *
 */
function erp_attendance_get_shift_users_count( $shift_id ) {
    global $wpdb;

    return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_attendance_shift_user WHERE shift_id = $shift_id" );
}

/**
 *
 * Remove users from shift
 *
 * @since 2.0.0
 *
 * @param int $shift_id
 * @param array $users_id
 *
 * @return void
 *
 */
function erp_attendance_remove_users_from_shift( $shift_id, $users_id ) {
    global $wpdb;

	$wpdb->query( "DELETE FROM {$wpdb->prefix}erp_attendance_shift_user WHERE user_id IN ($users_id) AND shift_id = $shift_id" );
}

/**
 *
 * Remove shift
 *
 * @since 2.0.0
 *
 * @param int $shift_id
 *
 * @return void
 *
 */
function erp_attendance_remove_shift( $shift_id ) {
    global $wpdb;

	$wpdb->query( "DELETE FROM {$wpdb->prefix}erp_attendance_shifts WHERE id = $shift_id" );
}

/**
 * Get user late and early left time
 *
 * @param integer $user_id
 *
 * @return array
 */
function get_user_late_and_ealry_left_time( $user_id, $timestamp = null ) {
    $timestamp  = $timestamp ? $timestamp : current_time( 'timestamp' );
    $date_shift = erp_attendance_get_punching_shift( $user_id, $timestamp );
    $error      = null;
    $early_left = null;
    $late       = 0;

	if ( is_wp_error( $date_shift ) ) {
		$error = $date_shift;
    }

    // Shift start and end time
    $start_time_timestamp = strtotime($date_shift->start_time);
    $end_time_timestamp   = strtotime($date_shift->end_time);

    // late check
    if ( $start_time_timestamp < $timestamp ) {
        $late = $timestamp - $start_time_timestamp;
    }

    // early left check
    if ( $end_time_timestamp > $timestamp ) {
        $early_left = $end_time_timestamp - $timestamp;
    }

    return [
        'late'       => $late,
        'early_left' => $early_left,
        'error'      => $error
    ];
}

/**
 * Save attendance entry by `HR Manager`
 *
 * @param string  $date
 * @param integer $user_id
 * @param integer $dshift_id
 * @param string $checkin
 * @param string $checkout
 *
 * @return void
 */
function save_new_attendance_entry_by_hr( $date, $user_id, $dshift_id, $checkin, $checkout ) {
	global $wpdb;

    /*$late_and_early_left = get_user_late_and_ealry_left_time( $user_id, strtotime($date) );
    if ( $late_and_early_left['error'] ) {
        return $late_and_early_left['error'];
	}*/

	$worktime = strtotime( $checkout ) - strtotime( $checkin );

    $wpdb->insert(
        "{$wpdb->prefix}erp_attendance_log",
        array(
            'user_id'       => $user_id,
            'date_shift_id' => $dshift_id,
            'checkin'       => $checkin,
			'checkout'      => $checkout,
			'time'			=> $worktime
        ),
        array( '%d', '%d', '%s', '%s', '%s' )
    );

    $wpdb->update(
        "{$wpdb->prefix}erp_attendance_date_shift",
        array(
            'present'    => 1,
            'late'       => $late_and_early_left['late'],
            'early_left' => $late_and_early_left['early_left']
        ),
        array( 'id' => $dshift_id ),
        array( '%d', '%s', '%s' ),
        array( '%d' )
    );
}

/**
 * Edit / Update attendance entry by `HR Manager`
 *
 * @return void
 */
function update_new_attendance_entry_by_hr( $date, $user_id, $dshift_id, $checkin_id, $checkout_id, $checkin, $checkout ) {
    global $wpdb;

    /*$late_and_early_left = get_user_late_and_ealry_left_time( $user_id, strtotime($date) );
    if ( $late_and_early_left['error'] ) {
        return $late_and_early_left['error'];
	}*/

    // first checkin time
    $wpdb->update(
        "{$wpdb->prefix}erp_attendance_log",
        ['checkin' => $checkin],
        ['id' => $checkin_id],
        ['%s'],
        ['%d']
	);

	// for worktime we need to get the last checkin
	// and substract it from the last checkout
	$last_checkin = $wpdb->get_row( "SELECT checkin FROM {$wpdb->prefix}erp_attendance_log WHERE id = {$checkout_id}" );

	$worktime = strtotime( $checkout ) - strtotime( $last_checkin->checkin );

    // last checkout time
    $wpdb->update(
        "{$wpdb->prefix}erp_attendance_log",
        ['checkout' => $checkout, 'time' => $worktime],
        ['id' => $checkout_id],
        ['%s', '%s'],
        ['%d']
    );

    $wpdb->update(
        "{$wpdb->prefix}erp_attendance_date_shift",
        ['early_left' => $late_and_early_left['early_left']],
        ['id' => $dshift_id],
        ['%s'],
        ['%d']
    );
}

/**
 * Get single user attendance checkin checkout log
 *
 * @since  2.0.0
 *
 * @param  int $user_id
 * @param  int $date
 *
 * @return object
 *
 */
function erp_attendance_get_single_user_log( $user_id = null, $date = null ) {
    global $wpdb;

    $grace_checking_time = erp_attendance_get_grace_checkin_time();

    $user_id    = ! empty( $user_id ) ? absint( $user_id ) : get_current_user_id();
    $date       = ! empty( $date )    ? $date : current_time( 'Y-m-d H:i:s' );
    $grace_date = date('Y-m-d H:i:s',strtotime('+'. $grace_checking_time .' seconds',strtotime($date)));

    /*$sql = sprintf("SELECT
        ds.id AS ds_id,
        log_checkout.id AS log_id,
        SUM(log.time) AS log_time,
        shift.name AS shift_title,
        shift.start_time,
        ds.start_time AS ds_start_time,
        shift.end_time,
        ds.end_time AS ds_end_time,
        min(log.checkin) AS min_checkin,
        UNIX_TIMESTAMP(max(log.checkin)) AS max_checkin,
        UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS curnt_timestamp,
        log_checkout.checkout AS max_checkout
        FROM {$wpdb->prefix}erp_attendance_date_shift AS ds
        LEFT JOIN {$wpdb->prefix}erp_attendance_log AS log ON ds.id = log.date_shift_id
        LEFT JOIN(
            SELECT id, date_shift_id, checkout
            FROM {$wpdb->prefix}erp_attendance_log ORDER BY id DESC LIMIT 1
        ) AS log_checkout ON log_checkout.date_shift_id = ds.id
        LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS shift ON ds.shift_id = shift.id
        WHERE shift.status = 1 AND ds.user_id = %d AND ds.start_time < '%s' AND ds.end_time > '%s'", $user_id, $grace_date, $grace_date );*/

    $sql = sprintf("SELECT
        ds.id AS ds_id,
        max(log.id) AS log_id,
        SUM(log.time) AS log_time,
        shift.name AS shift_title,
        shift.start_time,
        ds.start_time AS ds_start_time,
        shift.end_time,
        ds.end_time AS ds_end_time,
        min(log.checkin) AS min_checkin,
        UNIX_TIMESTAMP(max(log.checkin)) AS max_checkin_unix,
        UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS curnt_timestamp_unx,
        max(log.checkin) AS max_checkin_dt,
        ( SELECT checkout FROM {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id ORDER BY id DESC LIMIT 1) AS max_checkout
        FROM {$wpdb->prefix}erp_attendance_date_shift AS ds
        LEFT JOIN {$wpdb->prefix}erp_attendance_log AS log ON ds.id = log.date_shift_id
        LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS shift ON ds.shift_id = shift.id
        WHERE shift.status = 1 AND ds.user_id = %d AND ds.start_time < '%s' AND ds.end_time > '%s'", $user_id, $grace_date, $grace_date );

    $result = $wpdb->get_row( $sql );
    $result->curnt_timestamp = current_time( 'timestamp' ) ;
    $result->max_checkin     = strtotime( $result->max_checkin_dt ) ;

    return $result;
}

/**
 * Attendance assigned details
 *
 * @since  2.0.0
 *
 * @param  int $shift_id
 *
 * @return object
 *
 */
function get_att_shift_assigned_details( $shift_id ) {
    global $wpdb;

    $sql = sprintf("SELECT id as date_shift_id, date, user_id, start_time, end_time, present, late, early_left, day_type FROM {$wpdb->prefix}erp_attendance_date_shift WHERE shift_id = %d", $shift_id);

    return $wpdb->get_results( $sql );
}

/**
 * New attendance URL
 *
 * @since  2.0.0
 *
 * @param  string $slug
 *
 * @return string
 *
 */
function erp_attendance_url( $slug = '' ) {
    if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
        return admin_url( 'admin.php?page=' . $slug );
	}

    $section =  explode( '-', $slug );
	$section =  end( $section );

    if ( $slug == 'erp-hr-'.$section ) {
        return  admin_url( 'admin.php?page=erp-hr&section=attendance&sub-section='. $section );
    }

    return admin_url( 'admin.php?page=erp-hr&section=attendance&sub-section='. $slug );
}

/**
 * Remove entry by hr
 *
 * @since  2.0.0
 *
 * @param  array $dates
 *
 * @return void
 *
 */
function erp_att_remove_hr_entry_attendances( $dates ) {
    global $wpdb;

    $dates = explode( ',', $dates );

    erp_attendance_purge_cache( ['list' => 'attendance'] );

    foreach ( $dates as $date ) {
        $deleted[] = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}erp_attendance_date_shift WHERE `date` = %s", $date ) );
    }

    return $deleted;

	/**
	 * May be we should remove associte data from `log` table
	 *
	 * `DELETE T1, T2 FROM T1 INNER JOIN T2 ON T1.key = T2.key WHERE condition;`
	 *
	 * Notice that we put table names T1 and T2 between the DELETE and FROM keywords. If you omit T1 table, the DELETE statement
	 * only deletes rows in T2 table. Similarly, if you omitT2 table, the DELETE statement will delete only rows in T1 table.
	 */
}



function get_employee_att_report( $user_id, $duration) {
    global $wpdb;

    $start_date = $duration['start'];
    $end_date   = $duration['end'];

    $sql        = "SELECT
                        ds.date AS ds_date,
                        ds.user_id AS ds_user_id,
                        ds.present AS ds_present,
                        ds.late AS ds_late,
                        ds.early_left AS ds_early_left,
                        ds.day_type AS ds_day_type,
                        MIN(al.checkin) AS al_min_checkin,
                        MIN(al.checkout) AS al_min_checkout,
                        MAX(al.checkout) AS al_max_checkout,
                        SUM(al.time) AS al_time,
                        ash.name AS ash_name,
                        ash.start_time AS ash_start_time,
                        ash.end_time AS ash_end_time,
                        ash.duration AS ash_duration,
                        (SUM(al.time) - ash.duration) AS al_overtime,
                        ash.holidays AS ash_holidays
                      FROM {$wpdb->prefix}erp_attendance_date_shift AS ds
                        LEFT JOIN {$wpdb->prefix}erp_attendance_log AS al
                          ON ds.user_id = al.user_id AND
                              ds.id = al.date_shift_id
                        LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS ash
                          ON ds.shift_id = ash.id
                        WHERE ds.user_id = {$user_id} AND date BETWEEN '{$start_date}' AND '{$end_date}'
                        GROUP BY ds.date";

    $results     = $wpdb->get_results( $sql );

    if ( empty( $results ) ) {
        return [];
    }

    $total_days             = count($results);

    $total_working_days     = array_sum(array_map(function($value){
        return ( $value == 'working_day' ) ? 1 : 0;
    },wp_list_pluck($results, 'ds_day_type' )));

    $total_present          = array_sum(wp_list_pluck($results, 'ds_present' ));
    $total_early_left       = array_values( wp_list_pluck( $results, 'ds_early_left' ) );

    $total_early_left       = array_filter( $total_early_left, function( $x ) {
        if ( !is_null( $x ) ) {
            return $x;
        }
    } );

    $total_late             = array_sum(array_map(function($value){
        return ( $value > 0 ) ? 1 : 0;
    },wp_list_pluck($results, 'ds_late' )));


    $al_time                = wp_list_pluck($results, 'al_time' );
    $al_time_map            = array_map( function ( $value ) {
        return ( $value > 0 ) ? $value : 0 ;
    }, $al_time );


    //$total_work             = gmdate("H:i:s",array_sum($al_time_map)); //sec_to_hours
    $total_work             = sec_to_hours( array_sum($al_time_map) ); //sec_to_hours

    $al_overtime            = array_sum(wp_list_pluck($results, 'al_overtime' ));
    $al_overtime            = ( $al_overtime > 0 ) ? $al_overtime : 0;
    $total_over_time        = gmdate("H:i:s", $al_overtime);


    $missing_checkout       = array_sum(array_map(function($value){
        return ( $value == '0000-00-00 00:00:00' ) ? 1 : 0;
    }, wp_list_pluck($results, 'al_min_checkout' )));

    $total_avg_work         = gmdate("H:i:s",(array_sum($al_time_map) / $total_present));

    $avg_checkin            = array_map(function($value){
        return date("H:i:s",strtotime($value));

    },wp_list_pluck($results, 'al_min_checkin' ));

    $avg_checkin            = array_filter($avg_checkin, function($value){
        return $value != '00:00:00';
    });

    $avg_checkin            = date('H:i:s', array_sum(array_map('strtotime', $avg_checkin)) / count($avg_checkin));

    $avg_checkout           = array_map(function($value){
        return date("H:i:s",strtotime($value));

    },wp_list_pluck($results, 'al_max_checkout' ));

    $avg_checkout           = array_filter($avg_checkout, function( $value ){
        return $value != '00:00:00';
    });

    $avg_checkout           = date('H:i:s', array_sum(array_map('strtotime', $avg_checkout)) / count($avg_checkout));

    $ds_date                = wp_list_pluck( $results, 'ds_date' );

    $weekly_holidays        = unserialize( array_unique( wp_list_pluck( $results, 'ash_holidays' ) )[0] );

    $weekly_holidays        = array_map( function( $value ) use ($weekly_holidays) {
        $cur_value = strtolower(date('D', strtotime( $value )));
        if ( in_array( $cur_value, $weekly_holidays) ) {
              return $value;
        }
    }, $ds_date );

    $weekly_holidays        = array_values( array_filter( $weekly_holidays, function ( $value ) {
        return $value != null ;
    } ) );

    $data = [
        'dates'              =>  $total_days,
        'working_days'       =>  $total_working_days,
        'present'            =>  $total_present,
        'early_left'         =>  $total_early_left,
        'late'               =>  $total_late,
        'worktime'           =>  $total_work,
        'overtime'           =>  $total_over_time,
        'missing_checkout'   =>  $missing_checkout,
        'avg_worktime'       =>  $total_avg_work,
        'leaves'             =>  '',
        'holidays'           =>  '',
        'avg_checkin'        =>  $avg_checkin,
        'avg_checkout'       =>  $avg_checkout,
    ];

    $start_date_for_leave = erp_current_datetime()->modify( $start_date )->setTime( 0, 0, 0 )->getTimestamp();
    $end_date_for_leave = erp_current_datetime()->modify( $end_date )->setTime( 23, 59, 59)->getTimestamp();

    $user_leave_sql         = "SELECT leave_date, workingday_status FROM {$wpdb->prefix}erp_hr_leave_request_details WHERE user_id = {$user_id}
                                AND leave_date BETWEEN {$start_date_for_leave} AND {$end_date_for_leave}";


    $user_leave             = $wpdb->get_results( $user_leave_sql );
    $user_leave_all_array   = [];

    if ( ! empty( $user_leave ) ) {
        foreach ( $user_leave as $leave ) {
            $leave_date = erp_current_datetime()->setTimestamp( $leave->leave_date );
            $user_leave_all_array[] = $leave_date->format( 'Y-m-d' );
        }
    }


    $holidays_sql         = "SELECT * FROM {$wpdb->prefix}erp_hr_holiday
                                   WHERE start BETWEEN '{$start_date}' AND '{$end_date}' AND
                                         end BETWEEN '{$start_date}' AND '{$end_date}'";
    $holidays             = $wpdb->get_results( $holidays_sql );
    $all_holidays_array   = [];

    if ( ! empty( $holidays ) ) {
        foreach ( $holidays as $ul ) {
            $period = new DatePeriod(
                new DateTime($ul->start),
                new DateInterval('P1D'),
                new DateTime($ul->end)
            );
            foreach ($period as $key => $value) {
               /* if ( in_array( $value->format('Y-m-d'), $ds_date ) ) {
                    $all_holidays_array[] = $value->format('Y-m-d');
                }*/
                $all_holidays_array[] = $value->format('Y-m-d');
            }
        }
    }

    $all_holidays_array   = array_values( array_unique( array_merge( $all_holidays_array, $weekly_holidays ) ) );

    return [
        'attendance_summary' => $data,
        'attendance_report'  => $results,
        'user_leave'         => $user_leave_all_array,
        'holidays'           => $all_holidays_array,
    ];

}


function sec_to_hours ($init) {
    $hours = floor($init / 3600);
    $minutes = floor(($init / 60) % 60);
    $seconds = $init % 60;
    $seconds = ( $seconds == 0 ) ? '00' : $seconds;
    return "$hours:$minutes:$seconds";
}


function if_check_in_out_valid() {
    global $wpdb;

    $current_user = get_current_user_id();
    $current_time_start = erp_current_datetime()->setTime( 0, 0, 0 )->getTimestamp();
    $current_time_end = erp_current_datetime()->setTime( 23, 59, 59 )->getTimestamp();

    $sql          = "SELECT count(*) from {$wpdb->prefix}erp_hr_leave_request_details
                        WHERE user_id = {$current_user} AND
                              leave_date between $current_time_start AND $current_time_end";

    $result       = $wpdb->get_var( $sql );

    if ( $result > 0 ) {
        return false;
    }

    return true;
}


function erp_hrm_is_leave_recored_exist_between_date_for_attendence( $start_date, $end_date, $user_id ) {

    $given_date_extrat  = erp_extract_dates( $start_date, $end_date );
    $start_date         = is_numeric( $start_date ) ? $start_date :  strtotime( $start_date );
    $end_date           = is_numeric( $end_date ) ? $end_date : strtotime( $end_date );

    $holiday = new \WeDevs\ERP\HRM\Models\LeaveRequest();

    $holiday->where( 'user_id', '=', $user_id );

    $holiday = $holiday->where( function ( $condition ) use ( $start_date, $user_id ) {
        $condition->where( 'start_date', '<=', $start_date );
        $condition->where( 'end_date', '>=', $start_date );
        $condition->whereIn( 'last_status', [ 1 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $end_date, $user_id ) {
        $condition->where( 'start_date', '<=', $end_date );
        $condition->where( 'end_date', '>=', $end_date );
        $condition->whereIn( 'last_status', [ 1 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $start_date, $end_date, $user_id ) {
        $condition->where( 'start_date', '>=', $start_date );
        $condition->where( 'start_date', '<=', $end_date );
        $condition->whereIn( 'last_status', [ 1 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $start_date, $end_date, $user_id ) {
        $condition->where( 'end_date', '>=', $start_date );
        $condition->where( 'end_date', '<=', $end_date );
        $condition->whereIn( 'last_status', [ 1 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $results = $holiday->get()->toArray();

    $holiday_extrat    = [];

    foreach ( $results as $result ) {
        $start_date     = erp_current_datetime()->setTimestamp( $result['start_date'] )->format( 'Y-m-d' );
        $end_date       = erp_current_datetime()->setTimestamp( $result['end_date'] )->format( 'Y-m-d' );
        $date_extrat    = erp_extract_dates( $start_date, $end_date );
        $holiday_extrat = array_merge( $holiday_extrat, $date_extrat );
    }

    $extract = array_intersect( $given_date_extrat, $holiday_extrat );

    return $extract;
}


function erp_att_get_single_day_attendance ( $request ) {
    //return $request->get_params();
    global $wpdb;

    $date = erp_current_datetime()->modify( $request['date'] )->format( 'Y-m-d' );
    $args = [
        'limit'  => $request['per_page'],
        'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
    ];

    if( isset( $request['count'] ) && $request['count'] == true ) {
        $sql = "SELECT
            ds.id as dshift_id
            FROM {$wpdb->prefix}erp_attendance_date_shift AS ds
            LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS shift ON ds.shift_id = shift.id
            WHERE shift.status = 1 AND ds.date = '{$date}'";
        return $wpdb->get_results( $sql, ARRAY_A );
    }

    $where = '';

    if ( isset( $request['s'] ) && !empty( $request['s'] ) ) {
        $search_str = $request['s'];
        $searched_users_by_first_name       = ( array ) get_users( array( 'meta_key' => 'first_name', 'meta_value' => $search_str, 'meta_compare'=>'LIKE' ) );
        $searched_users_by_last_name        = ( array ) get_users( array( 'meta_key' => 'last_name', 'meta_value' => $search_str, 'meta_compare'=>'LIKE' ) );
        $search_result                      = $searched_users_by_first_name + $searched_users_by_last_name;
        $searched_users                     = wp_list_pluck( $search_result, 'data' );
        $searched_users_id                  = array_unique( wp_list_pluck( $searched_users, 'ID' ) );
        $searched_users_id_implode_by_coma  = implode( ',', $searched_users_id);
        $where.= " AND ds.user_id IN ({$searched_users_id_implode_by_coma}) ";
    }

    $sql = "SELECT
            ds.id as dshift_id,
            ds.user_id,
            shift.name AS shift,
            IF(ds.present is null, 'no', 'yes') AS present,
            ( SELECT MIN(checkin) from {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id) as checkin,
            /*( SELECT MAX(checkout) from {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id) as checkout,*/
            ( SELECT checkout from {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id ORDER BY id DESC LIMIT 1) as checkout,
            ( SELECT MIN(id) from {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id) as checkin_id,
            ( SELECT MAX(id) from {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id) as checkout_id,
            ( SELECT SUM(time) from {$wpdb->prefix}erp_attendance_log WHERE date_shift_id = ds.id AND user_id = ds.user_id ) as worktime,
            ( SELECT employee_id from {$wpdb->prefix}erp_hr_employees WHERE user_id = ds.user_id) as employee_id
            FROM {$wpdb->prefix}erp_attendance_date_shift AS ds
            LEFT JOIN {$wpdb->prefix}erp_attendance_shifts AS shift ON ds.shift_id = shift.id
            WHERE shift.status = 1 {$where} AND ds.date = '{$date}' LIMIT {$args['offset']}, {$args['limit']}";

    return $wpdb->get_results( $sql, ARRAY_A );

}

function save_attendance_by_hr_mnanager( $data, $date ) {
    global $wpdb;

    erp_attendance_purge_cache( ['list' => 'attendance'] );

    foreach ( $data as $dt ) {
        if( $dt['hr_should_change'] == true ) {
            $dshift_id              = $dt['dshift_id'];
            $user_id                = $dt['user_id'];
            $hr_checkin             = $dt['checkin'];
            $hr_checkin_datetime    = $date . ' ' . $dt['checkin'];


            $hr_checkout            = $dt['checkout'];
            $hr_checkout_datetime   = $date . ' ' . $dt['checkout'];

            $get_current_log = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}erp_attendance_log` WHERE date_shift_id = {$dshift_id} AND user_id = {$user_id}" );

            $get_current_log_count = count( $get_current_log );
            if ( $get_current_log_count > 0 ){
                $first_row = $get_current_log[0];
                $last_row  = $get_current_log[ $get_current_log_count - 1 ];
                if ( ! empty( $hr_checkin ) ) {
                    if ( $first_row->checkout != '0000-00-00 00:00:00' ) {
                        $time = strtotime( $first_row->checkout ) - strtotime( $hr_checkin_datetime );
                    } else {
                        $time = /*strtotime( $first_row->checkin ) - strtotime( $hr_checkin_datetime )*/ null;
                    }
                    $wpdb->update(
                        "{$wpdb->prefix}erp_attendance_log",
                        array(
                            'checkin'    => $hr_checkin_datetime,
                            'time'       => $time
                        ),
                        array(
                            'id'            => $first_row->id,
                            'date_shift_id' => $dshift_id,
                            'user_id'       => $user_id
                        )
                    );
                }

                if( ! empty( $hr_checkout ) ) {

                    $updated_chkout = ( $get_current_log_count === 1 ) ? $hr_checkin_datetime : $last_row->checkin;

                    $time_c = strtotime( $hr_checkout_datetime ) - strtotime( $updated_chkout );
                    $wpdb->update(
                        "{$wpdb->prefix}erp_attendance_log",
                        array(
                            'checkout'    => $hr_checkout_datetime,
                            'time'        => $time_c
                        ),
                        array(
                            'id'            => $last_row->id,
                            'date_shift_id' => $dshift_id,
                            'user_id'       => $user_id
                        )
                    );
                }
            } else {
                $wpdb->insert(
                    "{$wpdb->prefix}erp_attendance_log",
                    array(
                        'date_shift_id'     => $dshift_id,
                        'user_id'           => $user_id,
                        'checkin'           => $hr_checkin_datetime,
                        'checkout'          => $hr_checkout_datetime,
                        'time'              => strtotime( $hr_checkout_datetime ) - strtotime( $hr_checkin_datetime )
                    )
                );
            }


            /**** Check if late and early left ******/

            $get_date_shift = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}erp_attendance_date_shift as ds LEFT JOIN {$wpdb->prefix}erp_attendance_shifts as ats ON ds.shift_id = ats.id WHERE ds.id={$dshift_id} AND ds.user_id={$user_id}" );

            $late           = null;
            $early_left     = null;

            if ( ! empty( $hr_checkin ) ) {
                $checkin_timestamp  = strtotime( $hr_checkin_datetime );
                $entry_timestamp    = ( get_option( 'grace_after_checkin', 15 ) * 60 ) + strtotime( $date . " " . $get_date_shift->start_time );
                if ( $checkin_timestamp > $entry_timestamp ) {
                    $late = $checkin_timestamp - $entry_timestamp;
                } else {
                    $late = null;
                }
            }

            if ( ! empty( $hr_checkout ) ) {
                $checkout_timestamp = strtotime( $hr_checkout_datetime );
                $exit_timestamp     = strtotime( $date . " " . $get_date_shift->end_time );
                if ( $exit_timestamp > $checkout_timestamp ) {
                    $early_left = $exit_timestamp - $checkout_timestamp;
                } else {
                    $early_left = null;
                }
            }


            /**********/



            $wpdb->update(
                "{$wpdb->prefix}erp_attendance_date_shift",
                array(
                    'present'    => ( $dt['status'] == 'yes' ) ? 1 : null,
                    'late'       => $late,
                    'early_left' => $early_left
                ),
                array( 'id' => $dshift_id ),
                array( '%d', '%s', '%s' ),
                array( '%d' )
            );

            do_action( 'erp_hr_log_att_by_hr', $dt['shift'], $dt['employee_name'] );
        }
    }
}

/**
 * Remove user from assigned shift and generated date shift
 *
 * @since 2.0.6
 *
 * @param int $user_id
 * @param string $from_date
 *
 * @return void
 */
function erp_attendance_remove_user_from_shift( $user_id, $from_date = false ) {
    global $wpdb;

    erp_attendance_purge_cache( ['list' => 'attendance,shifts'] );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}erp_attendance_shift_user
            WHERE `user_id` = %d",
            (int) $user_id
        )
    );

    $from_date = $from_date ? erp_current_datetime()->modify( $from_date )->format( 'Y-m-d' ) : erp_current_datetime()->format( 'Y-m-d' );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}erp_attendance_date_shift
            WHERE `user_id` = %d
            AND `date` >= %s",
            [ (int) $user_id, $from_date ]
        )
    );
}

/**
 * Purge cache data for Attendance addon
 *
 * Remove all cache for Attendance addon
 *
 * @since 2.0.7
 *
 * @param array $args
 *
 * @return void
 */
function erp_attendance_purge_cache( $args = [] ) {

    $group = 'erp-attendance';

    if ( isset( $args['shift_id'] ) ) {
        wp_cache_delete( "erp-attendance-shift-by-" . $args['shift_id'], $group );
    }

    if ( isset( $args['list'] ) ) {
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => $args['list'] ] );
    }
}

/**
 * Checks if shift already exists
 *
 * @since 2.0.8
 *
 * @param string $shift_name
 * @param string $start_time
 * @param string $end_time
 * @param array $holidays
 * @param int $shift_id
 *
 * @return bool|string
 */
function erp_atts_is_duplicate_shift( $shift_name, $start_time, $end_time, $holidays = [], $shift_id = false ) {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_attendance_shifts';

    $shift_name_sql  = "SELECT shifts.id FROM {$table} AS shifts where shifts.name = %s AND shifts.status = 1";
    $shift_name_args = [ $shift_name ];

    if ( $shift_id ) {
        $shift_name_sql   .=  " AND shifts.id != %d";
        $shift_name_args[] = (int) $shift_id;
    }

    $name_exists_id = $wpdb->get_var( $wpdb->prepare( $shift_name_sql, $shift_name_args ) );

    if ( ! empty( $name_exists_id ) ) {
        return $name_exists_id;
    }

    $shift_time_sql  = "SELECT shifts.id FROM {$table} AS shifts where shifts.start_time = %s AND shifts.end_time = %s AND shifts.status = 1";
    $shift_time_args = [ $start_time, $end_time ];

    if ( $shift_id ) {
        $shift_time_sql   .= " AND shifts.id != %d";
        $shift_time_args[] = (int) $shift_id;
    }

    $time_exists_id = $wpdb->get_col( $wpdb->prepare( $shift_time_sql, $shift_time_args ) );

    if ( ! empty( $time_exists_id ) ) {
        foreach( $time_exists_id as $shift_id ) {
            $existing_holidays = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT shifts.holidays FROM {$table} AS shifts WHERE shifts.id = %d AND shifts.status = 1",
                    [ (int) $shift_id ]
                )
            );

            $existing_holidays = ! empty( $existing_holidays ) ? maybe_unserialize( $existing_holidays ) : [];

            if ( count( $holidays ) == count( $existing_holidays ) ) {
                $matched_shift = true;

                foreach ( $holidays as $key => $holiday ) {
                    if ( ! in_array( $holiday, $existing_holidays ) ) {
                        $matched_shift = false;
                        break;
                    }
                }

                if ( $matched_shift ) {
                    return $shift_id;
                }
            }
        }
    }

    return false;
}
