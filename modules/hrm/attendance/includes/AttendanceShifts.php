<?php

namespace WeDevs\Attendance;
/**
 * List table class
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AttendanceShifts extends \WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'shift',
			'plural'   => 'shifts',
			'ajax'     => false
		) );

		$this->table_css();
	}


	function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no record found
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
			'shift_name' => __( 'Shift Name', 'erp-pro' ),
			'start'  	 => __( 'Start Time', 'erp-pro' ),
			'end'    	 => __( 'End Time', 'erp-pro' ),
			'duration'   => __( 'Duration', 'erp-pro' ),
			'holidays'   => __( 'Holidays', 'erp-pro' )
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
            case 'shift_name':
                $shift_nonce = wp_create_nonce( 'wp-erp-attendance' );
                $edit_url    = add_query_arg( 'shift', $item->id, admin_url( 'admin.php?page=erp-edit-shift' ) );
                $shift_name  = '<strong>' . $item->name . '</strong>';

                $actions = [
                    'edit' => sprintf(
                        '<a href="?page=%s&action=%s&shift=%s&_wpnonce=%s">Assign</a>',
                        esc_attr( 'erp-edit-shift' ),
                        'edit',
                        absint( $item->id ), $shift_nonce ),

                    'delete' => sprintf(
                        '<a href="?page=%s&action=%s&shift=%s&_wpnonce=%s">Delete</a>',
                        esc_attr( $_REQUEST['page'] ),
                        'delete',
                        absint( $item->id ), $shift_nonce )
                ];

                return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', $shift_name, $this->row_actions( $actions ), $edit_url );

			case 'start':
				return ! empty( $item->start_time ) ? $item->start_time : ' - ';

			case 'end':
				return ! empty( $item->end_time ) ? $item->end_time : ' - ';

			case 'duration':
				return floor($item->duration / 3600) . ' hour(s) ' . floor(($item->duration / 60) % 60) . ' minutes ';

            case 'holidays':
                $days = [
                    'Sun' => 'Sunday',
                    'Mon' => 'Monday',
                    'Tue' => 'Tuesday',
                    'Wed' => 'Wednesday',
                    'Thu' => 'Thursday',
                    'Fri' => 'Friday',
                    'Sat' => 'Saturday'
                ];

                $holidays = unserialize( $item->holidays );
                $offdays  = '';

                foreach ( $holidays as $holiday ) {
                    $offdays .= $days[ trim($holiday) ] . ', ';
                }

                echo rtrim( $offdays, ', ' );
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

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->process_bulk_action();

		$per_page     = 50;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$sql = "SELECT * FROM {$wpdb->prefix}erp_attendance_shifts";

		$result      = $wpdb->get_results( "{$sql} limit $per_page offset $offset" );
		$total_items = count( $wpdb->get_results( "{$sql}" ) );

		$this->items = $result;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
        ) );
    }

    /**
     * Process edit or delete action
     *
     * @return void
     */
    function process_bulk_action() {

        if ( 'delete' === $this->current_action() ) {
			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp-erp-attendance' ) ) {
				die( 'You are no allowed' );
			}

			erp_attendance_remove_shift( absint( $_REQUEST['shift'] ) );
        }
    }

}
