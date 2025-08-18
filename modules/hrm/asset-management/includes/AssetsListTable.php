<?php
namespace WeDevs\AssetManagement;
/**
 * List table class
 */
if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Assets List table class
 */
class AssetsListTable extends \WP_List_Table {

    protected $page_status;

    function __construct() {

        parent::__construct( array(
            'singular' => 'asset',
            'plural'   => 'assets',
            'ajax'     => true
        ) );
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {

        if ( $which != 'top' ) {

            return;
        }

        $selected_category = ( isset( $_GET['category_id'] ) ) ? $_GET['category_id'] : '';
        $types             = erp_hr_assets_get_categories_dropdown();

        unset( $types['-1'] );
        ?>

        <div class="alignleft actions">

            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Category', 'erp-asset' ) ?></label>
        <select name="category_id" id="category">

                <option value="-1"><?php _e( "&mdash; All Category &mdash;"); ?></option>
                <?php
                    foreach ( $types as $key => $title ) {
                        echo sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected_category, $key, false ), $title );
                    }
                ?>
            </select>

            <?php
            submit_button( __( 'Filter', 'erp-pro' ), 'button', 'filter_category', false );
        echo '</div>';
    }

    /**
     * Table CSS classes to apply
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

            case 'item_group':
                return $item->item_group;
            break;

            case 'category_id':
                return $item->category;
            break;

            case 'type':
                return $item->type;
                break;

            case 'model':
                return $item->model_no;
            break;

            case 'date_reg':
                return $item->date_reg;
            break;

            case 'date_expiry':
                return $item->date_expiry;
            break;

            case 'date_warranty':
                return $item->date_warranty;
            break;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Asset bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'asset_delete' => __( 'Delete', 'erp-asset' )
        ];

        return $actions;
    }

    /**
     * Current Action
     *
     * @since 1.0
     */
    public function current_action() {

        if ( isset( $_REQUEST['filter_category'] ) ) {
            return 'filter_category';
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
            'cb'            => '<input type="checkbox" />',
            'item_group'    => __( 'Item Name', 'erp-pro' ),
            'type'          => __( 'Type', 'erp-pro' ),
            'category_id'   => __( 'Category', 'erp-pro' ),
        //    'model'         => __( 'Model', 'erp-pro' ),
            'date_reg'      => __( 'Reg Date', 'erp-pro' ),
            'date_expiry'   => __( 'Expiry Date', 'erp-pro' ),
            'date_warranty' => __( 'Warranty Till', 'erp-pro' ),
            'status'        => __( 'Available/Total', 'erp-pro' ),

        ];

        return $columns;
    }

    /**
     * Render Column Type
     *
     * @since 1.0
     *
     * @return string
     */
    function column_type( $item ) {
        if ( 'single' == $item->type ) {
            printf( '<i style="cursor:help" title="%s" class="dashicons dashicons-admin-home erp-tips"></i>', __( 'Single', 'erp-pro' ) );
        } else {
            printf( '<i style="cursor:help" title="%s" class="dashicons dashicons-admin-multisite erp-tips"></i>', __( 'Multiple', 'erp-pro' ) );
        }
    }

    /**
     * Render Category Column
     *
     * @since 1.0
     *
     * @return string
     */
    function column_category_id( $item ) {

        $url = erp_asset_url( 'asset&category_id=' . $item->category_id );
        return '<a href="' . $url . '">' . $item->category . '</a>';
    }

    /**
     * Render the Item Name Column
     *
     * @since.10
     *
     * @return string
     */
    function column_item_group( $item ) {

        $actions           = [];
        $url               = '';
        $actions['edit']   = sprintf( '<a href="%s" class="asset-edit" data-id="%d" title="%s">%s</a>', $url, $item->id, __( 'Edit this item', 'erp-pro' ), __( 'Edit', 'erp-pro' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="asset-delete" data-id="%d" title="%s">%s</a>', $url, $item->id, __( 'Delete this item', 'erp-pro' ), __( 'Delete', 'erp-pro' ) );

        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', erp_asset_url( 'asset&action=view&id=' . $item->id ), $item->item_group, $this->row_actions( $actions ) );
    }

    /**
     * Render the checkbox column
     * @param  object  $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="asset_id[]" value="%s" />', $item->id );
    }

    /**
     * Render Status Column
     *
     * @return string
     */
    function column_status( $item ) {
        return $item->available_item_total . '/' . $item->multiple_item_total;
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {

        $sortable_columns = [
            'category_id'   => [ 'category_id', true ],
            'date_reg'      => [ 'date_reg', true ],
            'date_expiry'   => [ 'date_expiry', true ],
            'date_warranty' => [ 'date_warranty', true ],
        ];

        return $sortable_columns;
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
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = $this->get_column_info();

        $per_page              = $this->get_items_per_page('erp_assets_per_page');
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {

            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $category = isset( $_REQUEST['category_id'] ) ? $_REQUEST['category_id'] : '';

        if ( '-1' != $category ) {
            $args['category_id'] = $category;
        }

        $this->items  = erp_hr_assets_get_all( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_hr_assets_get_count( $args ),
            'per_page'    => $per_page
        ) );
    }
}
