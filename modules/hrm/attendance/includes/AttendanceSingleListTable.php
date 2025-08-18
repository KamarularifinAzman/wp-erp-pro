<?php
namespace WeDevs\Attendance;
/**
 * List table class
 */
if ( ! class_exists ( 'WP_List_Table' ) ) {

    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class AttendanceSingleListTable extends \WP_List_Table {

    function __construct() {

        parent::__construct( array(
            'singular' => 'attendance_single',
            'plural'   => 'attendances_singles',
            'ajax'     => false
        ) );

        $this->table_css();
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
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'employee_id':
                return $item->employee_id;

            case 'employee_name':
                return $item->employee_name;

            case 'department':
                return $item->department_name;

            case 'shift':
                return $item->shift;

            case 'present':
                return $item->present;

            case 'checkin':
                return $item->checkin;

            case 'checkout':
                return $item->checkout;

            case 'worktime':
                return erp_att_second_to_hour_min( $item->worktime );

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }
    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'employee_id'   => __( 'Emp. ID', 'erp-pro' ),
            'employee_name' => __( 'Employee Name', 'erp-pro' ),
            'department'    => __( 'Department', 'erp-pro' ),
            'shift'         => __( 'Shift', 'erp-pro' ),
            'present'       => __( 'Status', 'erp-pro' ),
            'checkin'       => __( 'Checkin', 'erp-pro' ),
            'checkout'      => __( 'Checkout', 'erp-pro' ),
            'worktime'      => __( 'Worktime', 'erp-pro' )
        );

        return $columns;
    }

    /**
     * Return mdash if no shift
     * @param $item
     *
     * @return mixed
     */
    function column_shift( $item ) {

        if( ! $item->shift ) {
            return '&mdash;';
        }
        return $item->shift;
    }

    /**
     * Return mdash if no checkin
     * @param $item
     *
     * @return mixed
     */
    function column_checkin( $item ) {

        if( ! $item->checkin ) {
            return '&mdash;';
        }
        return date( 'H:i:s', strtotime($item->checkin) );
    }

    /**
     * Return mdash if no checkout
     * @param $item
     *
     * @return mixed
     */
    function column_checkout( $item ) {

        if( ! $item->checkout ) {
            return '&mdash;';
        }
        return date( 'H:i:s', strtotime($item->checkout) );
    }

    /**
     * Manages the circle color when present/absent
     *
     * @return array
     */
    function column_present( $item ) {
        $circle = 'yes' == $item->present ? '<div class="green-circle"></div>' : '<div class="red-circle"></div>';

        return $circle;
    }

    public function column_employee_name( $item ) {
        $employee_url = '<a href="' . admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $item->user_id ) . '">' . $item->employee_name . '</a>';

        return $employee_url;
    }

    /**
     * Render the checkbox column
     * @param  object  $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="record_id[]" value="%s" />', $item->dshift_id );
    }

    /**
     * Attendance Single bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'attendance_delete' => __( 'Delete', 'erp-asset' )
        ];

        return $actions;
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {

        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=sample-page' );

        foreach ($this->counts as $key => $value) {

            $class                = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 50;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'];
        }

        if ( isset( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
            $args['date'] = $_REQUEST['id'];
        }

        $this->items  = erp_att_get_single_attendance( $args );

        $this->set_pagination_args( array(
            'total_items' => count( $this->items ),
            'per_page'    => $per_page
        ) );
    }

    /**
     * Get sortable columns
     *
     * @since 1.1.2
     *
     * @return array
     */
    function get_sortable_columns() {

        $sortable_columns = array(
            'employee_id' => array( 'employee_id', true ),
            'checkin' => array( 'checkin', true ),
        );

        return $sortable_columns;
    }
}

