<?php

namespace WeDevs\Attendance;
/**
 * List table class
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AssignedShiftEmployees extends \WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'attendance',
			'plural'   => 'attendances',
			'ajax'     => false
		) );

		$this->table_css();

	}


	function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no items found
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
			'cb'          => '<input type="checkbox">',
			'name' 		  => __( 'Name', 'erp-pro' ),
			'designation' => __( 'Designation', 'erp-pro' ),
			'department'  => __( 'Department', 'erp-pro' )
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
	            return '<strong>' . $item->name . '</strong>';

	        case 'designation':
	            return $item->deg;

	        case 'department':
	            return $item->dep;
		}
	}

	/**
	 * Checkbox for every user
	 *
	 * @param $item
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="users_id[]" value="%s" />', $item->user_id
		);
	}

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = [];

        if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
            return $actions;
        }

        $actions = [
            'delete'  => __( 'Move to Trash', 'erp' ),
        ];

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            unset( $actions['delete'] );

            $actions['permanent_delete'] = __( 'Permanent Delete', 'erp' );
            $actions['restore'] = __( 'Restore', 'erp' );
        }

        return $actions;
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

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;
		$shift_id 	  = absint( $_REQUEST['shift'] );

		$total_items = erp_attendance_get_shift_users_count( $shift_id );
        $this->items = erp_attendance_get_shift_users( $shift_id, $per_page, $offset );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

	}

	public function process_bulk_action() {
		if ( ! current_user_can( erp_hr_get_manager_role() ) ) {
			return false;
		}

		if ( 'delete' === $this->current_action() ) {
			$shift_id = absint( $_REQUEST['shift'] );
			$users_id = implode(',', $_REQUEST['users_id']);

			erp_attendance_remove_users_from_shift($shift_id, $users_id);
		}

		// wp_redirect( esc_url( add_query_arg() ) );
		// exit;
	}

}
