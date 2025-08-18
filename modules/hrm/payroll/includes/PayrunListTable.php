<?php
namespace WeDevs\Payroll;

/**
 * List table class
 */
class PayrunListTable extends \WP_List_Table {

    protected $page_status;
    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'payrun',
            'plural'   => 'payruns',
            'ajax'     => false
        ));
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0.0
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {

        if ( $which != 'top' ) {
            return;
        }

        $selected_status = (isset($_REQUEST['filter_payrun_status'])) ? $_REQUEST['filter_payrun_status'] : -1;

        ?>
        <div class="alignleft actions">

        <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Status', 'erp-pro' );?></label>
        <select name="filter_payrun_status" id="filter_payrun_status">
            <option value="-1"><?php _e( '- Select All -', 'erp-pro' ); ?></option>
            <?php echo erp_hr_payroll_get_status_dropdown($selected_status); ?>
        </select>

        <?php
        submit_button( __( 'Filter' ), 'button', 'filter_status_button', false );

        echo '</div>';

    }

    /**
     * Message to show if no department found
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function no_items() {
        _e( 'No payrun found.', 'erp-pro' );
    }

    /**
     * Get the column names
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'pay_period'        => __( 'Pay Period', 'erp-pro' ),
            'pay_run'           => __( 'Pay Run', 'erp-pro' ),
            'payment_date'      => __( 'Payment Date', 'erp-pro' ),
            'employees'         => __( 'Employees', 'erp-pro' ),
            'employees_payment' => __( 'Net Pay + Tax', 'erp-pro' ),
            'status'            => __( 'Status', 'erp-pro' ),
            'action'            => __( 'Action', 'erp-pro' )
        );

        return apply_filters( 'erp_hr_payroll_list_table_cols', $columns );
    }

    /**
     * Show default column
     *
     * @since  1.0.0
     *
     * @param array $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        $payrun_approved_url = admin_url( 'admin.php?page=erp-hr-payroll-pay-run&tab=employees&prid=' . $item['id'] );
        $payrun_remove_url = admin_url( 'admin.php?page=erp-hr-payroll-pay-run&action=remove&payrunid=' . $item['id'] );

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            $payrun_approved_url = admin_url( 'admin.php?page=erp-hr&section=payroll&sub-section=payrun&tab=employees&prid=' . $item['id'] );
            $payrun_remove_url = admin_url( 'admin.php?page=erp-hr&section=payroll&sub-section=payrun&action=remove&payrunid=' . $item['id'] );
        }

        $payrun_id = $item['id'];

        switch ($column_name) {
            case 'pay_period':
                return date('Y.m.d', strtotime($item['from_date'])) . ' to ' . date('Y.m.d', strtotime($item['to_date']));
            case 'pay_run':
                return $item['Pay_Run'];
            case 'payment_date':
                return $item['payment_date'];
            case 'employees':
                return $item['effected_employees'];
            case 'employees_payment':
                if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
                    return erp_acct_get_price($item['employees_payment']);
                }

                return erp_ac_get_price($item['employees_payment']);
            case 'status':
                return ucwords(str_replace('_',' ',$item['status']));
            case 'action':
                $edit_delete_btn = sprintf(__("<a class='fa button' href='%s'><span><i class='fa fa-pencil'></i></span></a>"), $payrun_approved_url );
                if ( $item['status'] != 'Approved' ) {
                    $edit_delete_btn .= sprintf(__("&nbsp;<label class='fa button payrun-rp' v-on:click='removePayrun(\"%d\")'><span><i class='fa fa-trash'></i></span></label>"), $payrun_id );
                }
                if ( $item['status'] == 'Approved' ) {
                    $edit_delete_btn .= sprintf(__("&nbsp;<label title='Undo Approval' class='fa button payrun-rp' v-on:click='undoPayrun(\"%d\")'><span><i class='fa fa-rotate-left'></i></span></label>"), $payrun_id );
                }

                $edit_delete_btn .= sprintf(__("&nbsp;<label title='Copy Approval' class='fa button payrun-rp' v-on:click='copyPayrun(\"%d\")'><span><i class='fa fa-copy'></i></span></label>"), $payrun_id );

                return $edit_delete_btn;
            default:
        }
        return $item[$column_name];
    }

    /**
     * Get sortable columns
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'payment_date'  => array( 'payment_date', true ),
            'status'        => array( 'status', true )
        );

        return $sortable_columns;
    }

    /**
     * Render current trigger bulk action
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function current_action() {

        if ( isset( $_REQUEST['filter_status_button'] ) ) {
            return 'filter_payrun_status';
        }

        if ( isset( $_REQUEST['recruitment_search'] ) ) {
            return 'recruitment_search';
        }

        return parent::current_action();
    }

    /**
     * Search form for list table
     *
     * @since 1.0.0
     *
     * @param  string $text
     * @param  string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {

        if ( empty($_REQUEST['s']) && !$this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if ( !empty($_REQUEST['orderby']) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        }

        if ( !empty($_REQUEST['order']) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        }

        if ( !empty($_REQUEST['status']) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr($_REQUEST['status']) . '" />';
        }

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
            <?php submit_button( $text, 'button', 'payrun_search', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
    <?php
    }

    /**
     * Prepare the class items
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function prepare_items() {
        global $per_page;
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $this->page_status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '2';

        // only necessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page
        );

        if ( isset($_REQUEST['filter_payrun_status']) && $_REQUEST['filter_payrun_status'] ) {
            $args['status'] = $_REQUEST['filter_payrun_status'];
        }

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order'] = $_REQUEST['order'] ;
        }

        if ( isset($_REQUEST['s']) ) {
            $args['search_key'] = $_REQUEST['s'];
        }

        $this->items = get_payrun_rows($args);
        $total_rows  = get_total_payrun_rows($args);

        $this->set_pagination_args(array(
            'total_items' => $total_rows,
            'per_page'    => $per_page
        ));
    }
}
