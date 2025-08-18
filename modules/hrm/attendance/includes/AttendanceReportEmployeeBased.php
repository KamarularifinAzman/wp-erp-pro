<?php

namespace WeDevs\Attendance;

/**
 * List table class
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AttendanceReportEmployeeBased extends \WP_List_Table {

	protected $duration;

	function __construct() {
		parent::__construct( array(
			'singular' => 'attendance',
			'plural'   => 'attendances',
			'ajax'     => false
		) );

		$this->table_css();

		$this->duration = erp_att_get_start_end_date( 'this_month' );
	}


	function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	function no_items() {

		_e( 'No Record Found', 'erp-pro' );
	}

	/**
	 * Get the column names
	 *
	 * @return array
	 */
	function get_columns() {

		$columns = array(
			'name'     => __( 'Name', 'erp-pro' ),
			'present'  => __( 'Present', 'erp-pro' ),
			'leave'    => __( 'Leave', 'erp-pro' ),
			'absent'   => __( 'Absent', 'erp-pro' ),
			'avg_work' => __( 'Avg Work', 'erp-pro' ),
			'checkin'  => __( 'Avg Checkin', 'erp-pro' ),
			'checkout' => __( 'Avg Checkout', 'erp-pro' ),
		);

		return $columns;
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param  object $item
	 * @param  string $column_name
	 *s
	 *
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
            case 'name':
				return ! empty( $item['user_id'] ) ? erp_hr_get_single_link($item['user_id']) : ' - ';
				break;

			case 'present':
				return ! empty( $item['total_present'] ) ? $item['total_present'] : ' - ';
				break;
			case 'absent':
				return ! empty( $item['total_absent'] ) ? $item['total_absent'] : ' - ';
				break;
			case 'leave':
				return ! empty( $item['total_leaves'] ) ? $item['total_leaves'] : ' - ';
				break;
			case 'avg_work':
				return erp_att_second_to_hour_min( $item['avg_worktime'] );
				break;
            case 'checkin':
				return ! empty( $item['avg_checkin'] ) ? date( "g:i a", $item['avg_checkin'] ) : ' - ';
				break;
			case 'checkout':
				return ! empty( $item['avg_checkout'] ) ? date( "g:i a", $item['avg_checkout'] ) : ' - ';
				break;
		}
	}

	/**
	 * Prepare the class items
	 *
	 * @return void
	 */
	function prepare_items() {
		global $wpdb;
		$selected_query_time = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : 'this_month';
		$selected_location   = isset( $_REQUEST['location'] ) ? $_REQUEST['location'] : '';
		$selected_department = isset( $_REQUEST['department'] ) ? $_REQUEST['department'] : '';
		$start_date          = isset( $_REQUEST['start'] ) ? $_REQUEST['start'] : '';
		$end_date            = isset( $_REQUEST['end'] ) ? $_REQUEST['end'] : '';


        $sql = "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status='active' AND user_id != '0' ";

//if filter by location
        if ( $selected_location && '-1' != $selected_location ) {
            $sql .= " AND location = '{$selected_location}' ";
        }

        if ( $selected_department && '-1' != $selected_department ) {
            $sql .= " AND department = '{$selected_department}' ";
        }

        $employees = $wpdb->get_col( "{$sql} order by user_id" );

        // If there are no employees found based on current query, then go back.
        if ( empty( $employees ) ){
            return;
        }

		if ( 'custom' != $selected_query_time ) {
			$duration   = erp_att_get_start_end_date( $selected_query_time );
			$start_date = $duration['start'];
			$end_date   = $duration['end'];
		}

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = 30;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

        $this->items = erp_get_attendance_summary( $employees, $start_date, $end_date, 'employee_based', $per_page, $offset );
		$total_items = erp_get_attendance_summary( $employees, $start_date, $end_date, 'employee_based', null, null, true );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

	}
}

