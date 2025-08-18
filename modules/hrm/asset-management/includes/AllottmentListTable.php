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
class AllottmentListTable extends \WP_List_Table {

    // Status Count
    private $counts = [];
    private $page_status = '';

    function __construct() {

        parent::__construct( array(
            'singular' => 'allottment',
            'plural'   => 'allottments',
            'ajax'     => true
        ) );
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
            case 'item_code':
                return $item->item_code;
                break;

            case 'model_no':
                return $item->model_no;
                break;

            case 'date_given':
                return $item->date_given;
                break;

            case 'alloted_to':
                return $item->employee_name;
                break;

            case 'date_return':
                return $item->date_return;
                break;

            case 'status':
                return $item->status;
                break;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Allottment bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'allottment_delete' => __( 'Delete', 'erp-pro' )
        ];

        return $actions;
    }

    /**
     * Current Action
     *
     * @since 1.0
     */
    public function current_action() {

        if ( isset( $_REQUEST['allott_status'] ) ) {
            return 'allott_status';
        }

        return parent::current_action();
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        $columns = [
            'cb'          => '<input type="checkbox" />',
            'item_group'  => __( 'Item Name', 'erp-pro' ),
            'model_no'    => __( 'Model No', 'erp-pro' ),
            'item_code'   => __( 'Asset Code', 'erp-pro' ),
            'alloted_to'  => __( 'Given To', 'erp-pro' ),
            'date_given'  => __( 'Given Date', 'erp-pro' ),
            'date_return' => __( 'Return Date', 'erp-pro' ),
            'status'      => __( 'Status', 'erp-pro' ),

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
    function column_item_group( $item ) {

        $actions         = [ ];
        $url             = '';

        if ( 'requested_return' == $item->status ) {
            $actions['edit']   = sprintf( '<a href="%s" class="accept-return-request" data-date="%s" data-id="%d" title="%s">%s</a>', $url, $item->date_request_return, $item->id, __( 'Accept Return Request', 'erp-pro' ), __( 'Accept Request', 'erp-pro' ) );
            $actions['return'] = sprintf( '<a href="%s" class="reject-return-request" data-id="%d" data-item-id="%d" title="%s">%s</a>', $url, $item->id, $item->item_id, __( 'Reject Return Request', 'erp-pro' ), __( 'Reject Request', 'erp-pro' ) );
        }

        if ( 'allotted' == $item->status ) {
            $actions['edit']   = sprintf( '<a href="%s" class="allottment-edit" data-id="%d" title="%s">%s</a>', $url, $item->id, __( 'Edit this item', 'erp-pro' ), __( 'Edit', 'erp-pro' ) );

            if ( 'yes' == $item->is_returnable ) {
                $actions['return'] = sprintf( '<a href="%s" class="asset-return" data-id="%d" data-item-id="%d" title="%s">%s</a>', $url, $item->id, $item->item_id, __( 'Return this item', 'erp-pro' ), __( 'Return', 'erp-pro' ) );
            }
        }

        $actions['delete'] = sprintf( '<a href="%s" class="allott-remove" data-id="%d" data-item-id="%d" title="%s">%s</a>', $url, $item->id, $item->item_id, __( 'Delete this allottment', 'erp-pro' ), __( 'Delete', 'erp-pro' ) );

        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', erp_asset_url( 'asset&action=view&id=' . $item->item_group_id ), $item->item_group, $this->row_actions( $actions ) );
    }

    /**
     * Render Status column
     *
     * @return string
     */
    function column_status( $item ) {

        $status = '';

        switch ( $item->status ) {

            case 'allotted':
                $status = sprintf( '<span class="asset-allotted"><b>%s</b></span>', ucfirst( $item->status ));
                break;

            case 'returned':
                $status = sprintf( '<span class="asset-returned"><b>%s</b></span>', ucfirst( $item->status ));
                break;

            case 'dissmissed':
                $status = sprintf( '<span class="asset-dissmissed"><b>%s</b></span>', ucfirst( $item->status ));
                break;

            case 'requested_return':
                $status = sprintf( '<span class="asset-allotted"><b>%s</b></span>', ucwords( str_replace( '_', ' ', $item->status ) ) );
                break;

            default:
                break;
        }

        return $status;
    }

    /**
     * Column Employee Name Output
     *
     * @param $item
     *
     * @return string
     */
    function column_alloted_to( $item ) {
        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a>', admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $item->employee_id ), $item->employee_name );
    }

    /**
     * Render date return column
     *
     * @param item
     *
     * @return string
     */
    function column_date_return( $item ) {

        $today = current_time( 'Y-m-d' );

        if ( '&mdash;' != $item->date_return_proposed ) {

            if ( 'allotted' == $item->status ) {

                $date1 = new \DateTime( $item->date_return_proposed );
                $date2 = new \DateTime( $today );

                if ( strtotime( $item->date_return_proposed ) > strtotime( $today ) || strtotime( $item->date_return_proposed ) == strtotime( $today )  ) {
                    printf( '%s<br><span class="description alert-green">%s</span>', erp_format_date( $item->date_return_proposed ), sprintf( _n( '%s day left', '%s days left', $date1->diff($date2)->days, 'erp-pro' ), $date1->diff($date2)->days ) );
                } else {
                    printf( '%s<br><span class="description alert-red">%s</span>', erp_format_date( $item->date_return_proposed ), sprintf( _n( '%s day over', '%s days over', $date2->diff($date1)->days, 'erp-pro' ), $date2->diff($date1)->days ) );
                }
            } else if ( 'returned' == $item->status ) {

                $date1 = new \DateTime( $item->date_return_proposed );
                $date2 = new \DateTime( $item->date_return_real );

                if ( strtotime( $item->date_return_proposed ) > strtotime( $item->date_return_real ) || strtotime( $item->date_return_proposed ) == strtotime( $item->date_return_real )  ) {
                    printf( '%s<br><span class="description alert-green">%s</span>', erp_format_date( $item->date_return_real ), __( 'Returned in time', 'erp-pro' ) );
                } else {
                    printf( '%s<br><span class="description alert-red">%s</span>', erp_format_date( $item->date_return_real ),sprintf( _n( '%s day delayed', '%s days delayed', $date2->diff($date1)->days, 'erp-pro' ), $date2->diff($date1)->days ) );
                }
            } else if ( 'dissmissed' == $item->status ) {

                $date1 = new \DateTime( $item->date_return_proposed );
                $date2 = new \DateTime( $item->date_return_real );

                if ( strtotime( $item->date_return_proposed ) > strtotime( $item->date_return_real ) || strtotime( $item->date_return_proposed ) == strtotime( $item->date_return_real )  ) {
                    printf( '%s<br><span class="description alert-green">%s</span>', erp_format_date( $item->date_return_real ), __( '', 'erp-pro' ) );
                } else {
                    printf( '%s<br><span class="description alert-red">%s</span>', erp_format_date( $item->date_return_real ),sprintf( _n( '%s day delayed', '%s days delayed', $date2->diff($date1)->days, 'erp-pro' ), $date2->diff($date1)->days ) );
                }
            } else if ( 'requested_return' == $item->status ) {

                $date1 = new \DateTime( $item->date_return_proposed );
                $date2 = new \DateTime( $item->date_request_return );

                if ( strtotime( $item->date_return_proposed ) > strtotime( $item->date_request_return ) || strtotime( $item->date_return_proposed ) == strtotime( $item->date_request_return )  ) {
                    printf( '%s<br><span class="description alert-green">%s</span>', erp_format_date( $item->date_request_return ), __( '', 'erp-pro' ) );
                } else {
                    printf( '%s<br><span class="description alert-red">%s</span>', erp_format_date( $item->date_request_return ),sprintf( _n( '%s day delayed', '%s days delayed', $date2->diff($date1)->days, 'erp-pro' ), $date2->diff($date1)->days ) );
                }
            }
        } else {
            return $item->date_return_proposed;
        }
    }

    /**
     * Render the checkbox column
     * @param  object  $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="allottment_id[]" value="%s" />', $item->id );
    }

    /**
     * Render Status Column
     *
     * @return string
     */
    //    function column_status( $item ) {
    //        return $item;
    //    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {

        $sortable_columns = [
            'date_given'  => [ 'date_given', true ]
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
        $base_link      = erp_asset_url( 'asset-allottment' );

        foreach ( $this->counts as $key => $value ) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
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

        $per_page              = $this->get_items_per_page('erp_assets_allott_per_page');
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

        $allott_status = isset( $_REQUEST['allott_status'] ) ? $_REQUEST['allott_status'] : '';

        if ( '-1' != $allott_status ) {
            $args['status'] = $allott_status;
        }

        if ( isset( $_REQUEST['status'] ) && !empty( $_REQUEST['status'] ) ) {
            $args['status'] = $_REQUEST['status'];
        }

        $this->counts = erp_asset_get_allotment_status_count();
        $this->items  = erp_hr_allottment_get_all( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_allottment_get_count( $args ),
            'per_page'    => $per_page
        ) );
    }
}
