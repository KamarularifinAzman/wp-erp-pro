<?php
namespace WeDevs\AssetManagement;
/**
 * List table class
 */
if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class RequestListTable extends \WP_List_Table {

    // Status count
    private $counts = [];
    private $page_status = '';

    function __construct() {

        parent::__construct( array(
            'singular' => 'request',
            'plural'   => 'requests',
            'ajax'     => true
        ) );
    }

    /**
     * Current Action
     *
     * @since 1.0
     */
    public function current_action() {

        if ( isset( $_REQUEST['request_status'] ) ) {
            return 'request_status';
        }

        return parent::current_action();
    }

    /**
     * Apply default table classes
     * @return array
     */
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
            case 'employee_name':
                return $item->employee_name;
                break;

            case 'alloted_to':
                return $item->employee_name;
                break;

            case 'item_description':
                return $item->item_description;
                break;

            case 'req_item_category':
                return $item->cat_name ? $item->cat_name : '&mdash;';
                break;

            case 'req_item_name':
                return $item->item;
                break;

            case 'available':
                return $item->available;
                break;

            case 'date_requested':
                return $item->date_requested;
                break;

            case 'date_replied':
                return $item->date_replied;
                break;

            case 'status':
                return $item->status;
                break;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    public function get_bulk_actions() {
        $actions = [
            'request_reject' => __( 'Reject', 'erp-pro' )
        ];

        return $actions;
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        $columns = [
            'cb'                => '<input type="checkbox" />',
            'employee_name'     => __( 'Employee Name', 'erp-pro' ),
            'req_item_category' => __( 'Requested Category', 'erp-pro' ),
            'req_item_name'     => __( 'Requested Item', 'erp-pro' ),
            'date_requested'    => __( 'Request Date', 'erp-pro' ),
            'given_item'        => __( 'Given Item', 'erp-pro' ),
//            'available'         => __( 'Available', 'erp-pro' ),
            'date_replied'      => __( 'Given Date', 'erp-pro' ),
            'status'            => __( 'Status', 'erp-pro' )
        ];

        return $columns;
    }

    /**
     * Render the Item Name Column
     *
     * @since.10
     *
     * @return string
     */
    function column_employee_name( $item ) {

        $actions           = [];
        $url               = '';
        $employee_url      = admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $item->user_id );
        if ( 'pending' == $item->status ) {

            $actions['edit']   = sprintf( '<a href="%s" class="request-approve" data-id="%d" data-item-id="%d" data-category="%d" data-item-name="%d" title="%s">%s</a>', $url, $item->id, 'yes' == $item->available ? $item->req_item_id : 0, $item->category, $item->item_name, __( 'Approve this item', 'erp-pro' ), __( 'Approve', 'erp-pro' ) );
            $actions['reject'] = sprintf( '<a href="%s" class="request-reject deletesubmit" data-id="%d" data-employee-name="%s" data-description="%s" title="%s">%s</a>', $url, $item->id, $item->employee_name, $item->item_description, __( 'Reject the request', 'erp-pro' ), __( 'Reject', 'erp-pro' ) );
        } else {
            if ( 'rejected' == $item->status ) {
                $actions['undo']   = sprintf( '<a href="%s" class="request-undo" data-id="%d" title="%s">%s</a>', $url, $item->id, __( 'Undo Action', 'erp-pro' ), __( 'Undo', 'erp-pro' ) );
            }
            if ( 'approved' == $item->status ) {
                $actions['disapprove']   = sprintf( '<a href="%s" class="request-disapprove" data-id="%d" title="%s">%s</a>', $url, $item->id, __( 'Disapprove Item', 'erp-pro' ), __( 'Disapprove', 'erp-pro' ) );
            }
        }
//            $actions['delete']   = sprintf( '<a href="%s" class="request-delete" data-id="%d" data-item-id="%d" data-employee-name="%s" data-description="%s" title="%s">%s</a>', $url, $item->id, 'yes' == $item->available ? $item->req_item_id : 0, $item->employee_name, $item->item_description, __( 'Delete Request', 'erp-pro' ), __( 'Delete', 'erp-pro' ) );


        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', $employee_url, $item->employee_name, $this->row_actions( $actions ) );
    }

    /**
     * Column Employee Name Output
     * @param $item
     * @return string
     */
    function column_alloted_to( $item ) {
        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a>', admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $item->employee_id ), $item->employee_name );
    }

    /**
     * Column Status Output
     * @param $item
     * @return string
     */
    function column_status( $item ) {

        $status = '';

        switch ( $item->status ) {

            case 'pending':
                $status = sprintf( '<span class="asset-allotted"><b>%s</b></span>', ucfirst( $item->status ));
            break;

            case 'approved':
                $status = sprintf( '<span class="asset-returned"><b>%s</b></span>', ucfirst( $item->status ));
            break;

            case 'rejected':
                $status = sprintf( '<span class="asset-dissmissed"><b>%s</b></span>', ucfirst( $item->status ));
            break;

            default:
            break;
        }

        return $status;
    }

    /**
     * Render column given item
     *
     * @return array
     */
    function column_given_item( $item ) {

        if ( 'approved' == $item->status ) {
            return $item->item_given;
        } else {
            return '&mdash;';
        }
    }

    /**
     * Column Available Output
     * @param $item
     * @return string
     */
    function column_available( $item ) {

        $available = '';

        switch ( $item->available ) {

            case 'yes':
                $available = __( 'Yes', 'erp-pro' );
                break;

            case 'no':
                $available = __( 'No', 'erp-pro' );
                break;

            default:
                break;
        }

        return $available;
    }

    /** Renders item Name Column
     *
     * @return String
     */
    function column_req_item_name( $item ) {
        return sprintf( '<a href="%s">%s</a>', erp_asset_url( 'asset&action=view&id=' . $item->item_name ), $item->item );
    }

    /**
     * Render the checkbox column
     * @param  object  $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="request_id[]" value="%s" />', $item->id );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {

        $sortable_columns = [
            'date_requested' => [ 'date_requested', true ],
            'date_replied'   => [ 'date_replied', true ],
            'status'         => [ 'status', true ]

        ];

        return $sortable_columns;
    }

    /**
     * Set the views
     *
     * @return array
     */
    function get_views() {

        $status_links   = array();
        $base_link      = erp_asset_url( 'asset-request' );

        foreach ( $this->counts as $key => $value ) {

            $class                = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;

            if ( 'all' == $key ) {
                $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array(), $base_link ), $class, $value['label'], $value['count'] );
                continue;
            }

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
        $this->_column_headers = $this->get_column_info();

        $per_page              = $this->get_items_per_page('erp_assets_request_per_page');
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {

            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';

        if ( '-1' != $status ) {
            $args['status'] = $status;
        }

        $this->items  = erp_hr_asset_request_all( $args );
        $this->counts = erp_asset_get_request_status_count();

        $this->set_pagination_args( array(
            'total_items' => erp_hr_asset_request_count( $args ),
            'per_page'    => $per_page
        ) );
    }
}
