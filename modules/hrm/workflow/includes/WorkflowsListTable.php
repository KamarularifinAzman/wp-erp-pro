<?php
namespace WeDevs\ERP\Workflow;

class WorkflowsListTable extends \WP_List_Table {
    /**
     * Constructor.
     */
    function __construct() {
        parent::__construct( array(
            'singular' => __( 'workflow', 'erp-pro' ),
            'plural'   => __( 'workflows', 'erp-pro' ),
            'ajax'     => false
        ) );
    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-items[]" value="%s" />', $item->id
        );
    }

    /**
     * Method for name column.
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name( $item ) {
        $delete_nonce = wp_create_nonce( 'erp-wf-delete-workflow' );
        $title        = '<strong>' . $item->name . '</strong>';

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            $restore_nonce = wp_create_nonce( 'erp-wf-restore-workflow' );

            $actions = [
                'restore'          => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'restore', absint( $item->id ), $restore_nonce, __( 'Restore', 'erp-pro' ) ),
                'parmanent-delete' => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'parmanent-delete', absint( $item->id ), $delete_nonce, __( 'Parmanent Delete', 'erp-pro' ) )
            ];

            return $title . $this->row_actions( $actions );
        }

        $status_nonce = wp_create_nonce( 'erp-wf-status-workflow' );

        $status = ( $item->status == 'paused' ) ? __( 'Activate', 'erp-pro' ) : __( 'Pause', 'erp-pro' );

        $actions = [
            'status' => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'status', absint( $item->id ), $status_nonce, $status ),
            'edit'   => sprintf( '<a href="?page=%s&action=%s&id=%d">%s</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item->id ), __( 'Edit', 'erp-pro' ) ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item->id ), $delete_nonce )
        ];

        return sprintf( '<a href="?page=%s&action=%s&id=%d">%s</a>',  esc_attr( $_REQUEST['page'] ), 'edit', absint( $item->id ), $title ) . $this->row_actions( $actions );
    }

    /**
     * Get a list of columns.
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'name'         => __( 'Name', 'erp-pro' ),
            'status'       => __( 'Status', 'erp-pro' ),
            'events_group' => __( 'Module', 'erp-pro' ),
            'event'        => __( 'Event', 'erp-pro' ),
            'run'          => __( 'Total Runs', 'erp-pro' ),
            'created_by'   => __( 'Created By', 'erp-pro' ),
            'created_at'   => __( 'Date', 'erp-pro' ),
        );

        return $columns;
    }

    /**
     * Get a list of sortable columns.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'name'       => array( 'name', true ),
            'status'     => array( 'status', false ),
            'created_at' => array( 'created_at', false ),
        );

        return $sortable_columns;
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links = [];
        $base_link    = admin_url( 'admin.php?page=erp-workflow' );

        $workflows_count        = erp_wf_get_workflows( ['count' => true] );
        $workflows_trash_count  = erp_wf_get_workflows( ['trashed' => true, 'count' => true] );
        $workflows_active_count = erp_wf_get_workflows( ['status' => 'active', 'count' => true] );
        $workflows_paused_count = erp_wf_get_workflows( ['status' => 'paused', 'count' => true] );

        $status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';

        $status_links['all']    = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'all' ), $base_link ), ( $status == 'all' ) ? 'current' : '', __( 'All', 'erp-pro' ), $workflows_count );
        $status_links['active'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'active' ), $base_link ), ( $status == 'active' ) ? 'current' : '', __( 'Active', 'erp-pro' ), $workflows_active_count );
        $status_links['paused'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'paused' ), $base_link ), ( $status == 'paused' ) ? 'current' : '', __( 'Paused', 'erp-pro' ), $workflows_paused_count );
        $status_links['trash']  = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), ( $status == 'trash' ) ? 'current' : '', __( 'Trash', 'erp-pro' ), $workflows_trash_count );

        return $status_links;
    }

    /**
     * Define each column of the table.
     *
     * @param  array  $item
     * @param  string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'name':
            case 'run':
                return $item->{$column_name};
            case 'status':
                return ( $item->status == 'paused' ) ? '<span class="status-paused"></span>' : '<span class="status-active"></span>';
            case 'events_group':
                return in_array( $item->events_group, ['crm', 'hrm', 'imap'] ) ? strtoupper( $item->events_group ) : ucwords( $item->events_group );
            case 'event':
                return ucwords( str_replace( '_', ' ', $item->event ) );
            case 'created_by':
                $user = get_user_by( 'id', $item->{$column_name} );
                return $user->display_name;
            case 'created_at':
                return ! empty( $item->created_at ) ? erp_format_date( $item->created_at ) : '-';
        }
    }

    /**
     * Message to be displayed when there are no items.
     *
     * @return void
     */
    public function no_items() {
        _e( 'No workflows found.', 'erp-pro' );
    }

    /**
     * Set the bulk actions.
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'bulk-delete' => __( 'Delete', 'erp-pro' ),
        );

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            $actions = array(
                'bulk-restore'          => __( 'Restore', 'erp-pro' ),
                'bulk-parmanent-delete' => __( 'Permanent Delete', 'erp-pro' ),
            );
        }

        return $actions;
    }

    /**
     * Prepares the list of items for displaying.
     *
     * @return void
     */
    public function prepare_items() {
        $per_page     = $this->get_items_per_page( 'workflows_per_page', 20 );
        $current_page = $this->get_pagenum();

        $status       = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';
        $search       = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : false;

        if ( $status == 'trash' ) {
            $total_items = erp_wf_get_workflows( ['count' => true, 'trashed' => true, 's' => $search] );
        } else {
            $total_items = erp_wf_get_workflows( ['count' => true, 'status' => $status, 's' => $search] );
        }

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ) );

        $this->_column_headers = $this->get_column_info();

        $offset = ( $current_page - 1 ) * $per_page;

        $args = [
            'offset' => $offset,
            'number' => $per_page,
            // 'type'   => $this->type, // To avoid Getting a dynamic property is deprecated since version 6.4.0! hence can't find any use of this property.
            's'      => isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : false,
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        if ( $status == 'trash' ) {
            $args['trashed'] = true;
        }

        if ( in_array( $status, ['active', 'paused'] ) ) {
            $args['status'] = $status;
        }

        $this->items = erp_wf_get_workflows( $args );
    }
}
