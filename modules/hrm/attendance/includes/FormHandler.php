<?php

namespace WeDevs\Attendance;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 */
class FormHandler {

	public function __construct() {
		add_action( 'load-toplevel_page_erp-hr-attendance', array( $this, 'handle_attendance_main' ) );
		add_action( 'load-hr-management_page_erp-hr-employee', array( $this, 'employee_att_report' ) );
		add_action( 'load-hr-management_page_erp-hr-reporting', array( $this, 'hr_reporting_att_report' ) );
		add_action( 'load-toplevel_page_erp-hr-attendance', array( $this, 'sinlge_employee_record_page' ) );

		add_action( 'admin_post_erp_att_export_attendance_csv', array( $this, 'export_att_csv' ) );

        add_action( 'erp-hr-employee-form-work', array( $this, 'add_shift_field_to_employee_form' ) );

		if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
			$this->bulk_actions();
		}
	}

	/**
	 * Bulk action
	 *
	 * @return void
	 */
	public function bulk_actions() {
		$section = isset( $_GET['sub-section'] ) ? $_GET['sub-section'] : '';

		switch ( $section ) {
			case 'attendance':
				add_action( 'load-wp-erp_page_erp-hr', [ $this, 'handle_attendance_main' ] );
				break;
			case 'erp-shfit-exim':
				// add_action( 'load-wp-erp_page_erp-hr', [ $this, 'handle_attendance_main' ] );
				break;
			default:
				# code...
				break;
		}
	}
	/**
	 * Check is current page actions
	 *
	 * @since 0.1
	 *
	 * @param  integer $page_id
	 * @param  integer $bulk_action
	 *
	 * @return boolean
	 */
	public function verify_current_page_screen( $page_id, $bulk_action ) {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
			return false;
		}

		if ( $_GET['page'] != $page_id ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $bulk_action ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Form handler for employee single page report
	 *
	 * @since 1.0
	 */
	public function employee_att_report() {
		if ( ! $this->verify_current_page_screen( 'erp-hr-employee', 'epr-attendance-filter' ) && ! $this->verify_current_page_screen( 'erp-hr', 'epr-attendance-filter' ) ) {
			return;
		}

		$query = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : '';

		if ( isset( $_REQUEST['filter_attendance'] ) ) {
			$redirect = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$redirect = remove_query_arg( [ 'filter_attendance' ], $redirect );

			wp_redirect( $redirect );
		}
	}

	/**
	 * Attendance report on HR reporting form handler
	 * @return void
	 *
	 * @since 1.0
	 */
	public function hr_reporting_att_report() {
		if ( ! $this->verify_current_page_screen( 'erp-hr-reporting', 'epr-att-hr-reporting-filter' ) && ! $this->verify_current_page_screen( 'erp-hr', 'epr-att-hr-reporting-filter' ) ) {
			return;
		}

		$department_id = isset( $_REQUEST['department'] ) ? $_REQUEST['department'] : '';
		$query_time    = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : '';

		if ( isset( $_REQUEST['filter_att_hr_reporting'] ) ) {
			$redirect = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$redirect = remove_query_arg( [ 'filter_att_hr_reporting' ], $redirect );

			wp_redirect( $redirect );
		}
	}

	/**
	 * Handles for submission from Single Employee Attendance Record Page
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function sinlge_employee_record_page() {
		if ( ! $this->verify_current_page_screen( 'erp-hr-attendance', 'bulk-attendances_singles' ) && ! $this->verify_current_page_screen( 'erp-hr', 'bulk-attendances_singles' ) ) {
			return;
		}

		$record_table = new AttendanceSingleListTable();
		$action       = $record_table->current_action();

		if ( $action ) {
			$redirect = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );

			switch ( $action ) {
				case 'attendance_delete':
					if ( isset( $_GET['record_id'] ) && ! empty( $_GET['record_id'] ) ) {
						foreach ( $_GET['record_id'] as $id ) {
							erp_att_delete_attendance( intval( $id ) );
						}
					}

					$redirect = remove_query_arg( [ 'record_id' ], $redirect );

					wp_redirect( $redirect );

					break;

				default:
					break;
			}
		}
	}

	/**
	 * Handle form submission from attendance main table
	 *
	 * @since 1.2
	 *
	 * @return void
	 */
	public function handle_attendance_main() {
		if ( ! $this->verify_current_page_screen( 'erp-hr-attendance', 'bulk-attendances' ) && ! $this->verify_current_page_screen( 'erp-hr', 'bulk-attendances' ) ) {
			return;
		}

		$attendance_table = new AttendanceListTable();
		$action           = $attendance_table->current_action();

		if ( $action ) {
			$redirect   = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$attendance = new \WeDevs\Attendance\Models\Attendance();

			switch ( $action ) {
				case 'delete':
					if ( isset( $_REQUEST['attendance_date'] ) && ! empty( $_REQUEST['attendance_date'] ) ) {
						foreach ( $_REQUEST['attendance_date'] as $date ) {
							$attendance->where( 'date', $date )->delete();
						}
					}
					$redirect = remove_query_arg( [ 'attendance_date' ], $redirect );
					wp_redirect( $redirect );
					exit;

				default:
					$redirect = remove_query_arg( [ 'filter_attendance' ], $redirect );
					wp_redirect( $redirect );
					exit;
			}
		}
	}


	public function export_att_csv() {
		if ( ! current_user_can( 'erp_hr_manager' ) ) {
			wp_die( 'huh, no cheating!' );
		}

		if ( ! wp_verify_nonce( $_GET['nonce'], 'export_attendance_csv' ) ) {
			wp_die( __( 'session expired try again', 'erp_attendance' ) );
		}

		global $wpdb;

		$sql = "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status='active' AND user_id != '0' ";

		if ( isset( $_GET['location'] ) && '-1' != $_GET['location'] ) {
			$location = intval( $_GET['location'] );
			$sql      .= " AND location = '{$location}' ";
		}

		if ( $_GET['department'] && '-1' != $_GET['department'] ) {
			$department = intval( $_GET['department'] );
			$sql        .= " AND department = '{$department}' ";
		}

		$employees = $wpdb->get_col( $sql );

		$start_date          = isset( $_REQUEST['start'] ) ? $_REQUEST['start'] : '';
		$end_date            = isset( $_REQUEST['end'] ) ? $_REQUEST['end'] : '';
		$selected_query_time = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : '';

		if ( 'custom' != $selected_query_time ) {
			$duration   = erp_att_get_start_end_date( $selected_query_time );
			$start_date = $duration['start'];
			$end_date   = $duration['end'];
		}

		if ( isset( $_GET['type'] ) == 'date_based' ) {
			$items    = erp_get_attendance_summary( $employees, $start_date, $end_date, 'date_based' );
			$filename = "attendance-report-$start_date-to-$end_date.csv";
			$headers  = [ 'Date', 'Total', 'Present', 'Absent', 'Leave', 'Comment' ];
			$lines    = [];
			foreach ( $items as $item ) {
				$line   = [];
				$line[] = ! empty( $item['date'] ) ? $item['date'] : '-';
				$line[] = ! empty( $item['total'] ) ? $item['total'] : '-';
				//working day
				$line[] = ! empty( $item['total_present'] ) ? $item['total_present'] : '-';
				$line[] = ! empty( $item['total_absent'] ) ? $item['total_absent'] : '-';
				$line[] = ! empty( $item['total_leaves'] ) ? $item['total_leaves'] : '-';
				$line[] = ! empty( $item['comment'] ) ? $item['comment'] : '-';

				$lines[] = $line;
			}

			$file = fopen( 'php://output', 'w' );
			fputcsv( $file, $headers );
			foreach ( $lines as $content ) {
				fputcsv( $file, $content );
			}
			fclose( $file );
		} else {
			$items = erp_get_attendance_summary( $employees, $start_date, $end_date );

			$lines = [];
			foreach ( $items as $item ) {
				$line    = [];
				$line[]  = ! empty( $item['user_id'] ) ? erp_hr_get_employee_name( $item['user_id'] ) : '-';
				$line[]  = ! empty( $item['total_present'] ) ? $item['total_present'] : '-';
				$line[]  = ! empty( $item['total_absent'] ) ? $item['total_absent'] : '-';
				$line[]  = ! empty( $item['total_leaves'] ) ? $item['total_leaves'] : '-';
				$line[]  = ! empty( $item['avg_worktime'] ) ? erp_att_second_to_hour_min( $item['avg_worktime'] ) : '-';
				$line[]  = ! empty( $item['avg_checkin'] ) ? date( 'g:i a', $item['avg_checkin'] ) : '-';
				$line[]  = ! empty( $item['avg_checkout'] ) ? date( 'g:i a', $item['avg_checkout'] ) : '-';
				$lines[] = $line;
			}

			$filename = "attendance-report-$start_date-to-$end_date.csv";
			$headers  = [ 'Name', 'Present', 'Absent', 'Leave', 'Average Work', 'Average Checkin', 'Average Checkout' ];
			$file     = fopen( 'php://output', 'w' );
			fputcsv( $file, $headers );
			foreach ( $lines as $content ) {
				fputcsv( $file, $content );
			}
			fclose( $file );
		}

		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
	}


	public function add_shift_field_to_employee_form() {
        $args = [
            'limit' => 1000,
            'offset' => 0,
        ];

        $results            = erp_attendance_get_shifts( $args );
        $shift_arr          = [];
        $shift_arr['-1']    = __( '- Select -', 'erp' );

        foreach ( $results as $rslt ) {
            $shift_arr[ $rslt->id ] = __( $rslt->name, 'erp' );
        }

	    echo '<div class="col-3" data-selected="{{ data.work.shift }}">';
        erp_html_form_input( array(
            'label' => __( 'Shift', 'erp' ),
            'name'  => 'work[shift]',
            'value' => '{{ data.work.shift }}',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'options' => $shift_arr,
        ) );
	    echo '</div>';
    }

}
