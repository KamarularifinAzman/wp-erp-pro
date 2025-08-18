<?php
namespace WeDevs\AdvancedLeave\Forward;

use WP_List_Table;
use \WeDevs\ERP\ErpErrors;
use WeDevs\ERP\HRM\Models\LeaveEncashmentRequest;

if ( ! current_user_can( 'erp_leave_manage' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
}

/**
 * List table class
 */
class LeaveForwardListTable extends WP_List_Table {

    protected $prev_f_year = null;

    protected $exists = null;

    protected $page_status;

    function __construct() {

        global $status, $page;

        parent::__construct( array(
            'singular' => 'leave_forward',
            'plural'   => 'leave_forwards',
            'ajax'     => false
        ) );

        $this->prev_f_year = erp_pro_hr_leave_get_prev_financial_year();

        if ( null !== $this->prev_f_year ) {
            $this->exists = LeaveEncashmentRequest::where('f_year', $this->prev_f_year->id )->first();
        }

        $this->table_css();
    }

    /**
     * Render extra table nav
     */
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        if ( count( $this->items ) ) {
            $prev_f_year = erp_pro_hr_leave_get_prev_financial_year();

            if ( ! isset( $prev_f_year ) ) {
                return;
            }

            $exists = LeaveEncashmentRequest::where('f_year', $prev_f_year->id )->first();

            if ( $exists ) {
                return;
            }

            submit_button(
                sprintf('%s %s', esc_html__( 'Apply for', 'erp-pro' ), $prev_f_year->fy_name),
                'button button-primary', 'apply_forward_leaves', false
            );

            echo '<div class="forward-help">' . sprintf(
                '%s %s %s',
                esc_html__('Here bellow is the generated list of available carry forward and encashment report of', 'erp-pro'),
                '<em>' . $prev_f_year->fy_name  . '.</em><br>',
                esc_html__('Please click on `Apply` button to Actually make forwarding and encashment effective for employees.', 'erp-pro')
            ) . '</div>';
        }
    }

    /**
     * Message to show if no forward leaves found
     *
     * @return void
     */
    function no_items() {
        esc_html_e( 'No forward leaves found.', 'erp-pro' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $leave_forward, $column_name ) {
        switch ( $column_name ) {
            case 'available':
                return ! empty( $leave_forward->available ) ? erp_number_format_i18n( $leave_forward->available ) . __( ' days', 'erp-pro' ) : '';

            case 'amount':
                return ! empty( $leave_forward->amount ) ? erp_number_format_i18n( $leave_forward->amount ) : 0;

            case 'total':
                return ! empty( $leave_forward->total ) ? '<spna style="color:#00bfa5">' . erp_number_format_i18n( $leave_forward->total ) . '</span>' : 0;

            case 'max_encash_days':
            case 'max_carry_days':
            case 'encash_days':
            case 'forward_days':
                return ! empty( $leave_forward->$column_name ) ? erp_number_format_i18n( $leave_forward->$column_name ) : 0;

            default:
                return isset( $leave_forward->$column_name ) ? $leave_forward->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        if ( $this->exists ) {
            $columns = array(
                'employee_name'   => __( 'Employee Name', 'erp' ),
                'policy_name'     => __( 'Policy Name', 'erp' ),
                'forward_days'    => __( 'Forward Days', 'erp' ),
                'encash_days'     => __( 'Encash Days', 'erp' ),
                'amount'          => __( 'Amount', 'erp' ),
                'total'           => __( 'Total', 'erp' )
            );
        }
        else {
            $columns = array(
                'employee_name'   => __( 'Employee Name', 'erp' ),
                'policy_name'     => __( 'Policy Name', 'erp' ),
                'available'       => __( 'Available', 'erp' ),
                'max_encash_days' => __( 'Max Encash Days', 'erp' ),
                'max_carry_days'  => __( 'Max Carry Days', 'erp' ),
                'encash_days'     => __( 'Encash Days', 'erp' ),
                'forward_days'    => __( 'Forward Days', 'erp' ),
                'amount'          => __( 'Amount', 'erp' ),
                'total'           => __( 'Total', 'erp' )
            );
        }

        return apply_filters( 'erp_hr_leave_forward_table_cols', $columns );
    }

    /**
     * Render the employee name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_employee_name( $leave_unpaid ) {
        $employee_url = admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . absint($leave_unpaid->user_id) );
        return sprintf( '<a href="%s" class="link"><strong>%s</strong></a>', esc_url( $employee_url ), $leave_unpaid->employee_name );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {
        $status_links = array();
        $base_link    = admin_url( 'admin.php?page=erp-hr&section=leave' );

        foreach ($this->counts as $key => $value) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page          = 20;
        $current_page      = $this->get_pagenum();
        $offset            = ( $current_page -1 ) * $per_page;
        $this->page_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

        $args = array(
            'offset' => $offset,
            'number' => $per_page
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ;
        }

        if ( isset( $_REQUEST['forward_f_year'] ) ) {
            $args['f_year_id'] = absint( wp_unslash( $_REQUEST['forward_f_year'] ) );
        }
        else {
            $args['f_year_id'] = $this->prev_f_year !== null ? $this->prev_f_year->id : 0;
        }

        if ( $this->exists ) {

            $data = erp_pro_hr_leave_get_encash_requests( $args );
            $this->items = $data['data'];

            $this->set_pagination_args( array(
                'total_items' => $data['total'],
                'per_page'    => $per_page
            ) );
        }
        else {
            // Data initial load
            $items = erp_pro_hr_leave_get_users_available_leaves( $args );
            $this->items = erp_pro_hr_leave_generate_users_forward_leaves( $items );
        }
    }

}

/**
 * Show export button
 */
$prev_f_year = erp_pro_hr_leave_get_prev_financial_year();
$exists = null;

if ( isset( $prev_f_year ) ) {
    $exists = LeaveEncashmentRequest::where('f_year', $prev_f_year->id )->first();
}

$export_url = admin_url('admin.php?page=erp-hr&section=leave&sub-section=forward-leave&export-encash=true');

?>

<div class="wrap erp-hr-leave-forward">
    <h2><?php esc_html_e( 'Forward Leaves', 'erp' );
        if ( $exists ) : ?>
            <a href="<?php echo esc_url( $export_url ); ?>" id="export-forward-leave" class="add-new-h2">
                <?php esc_html_e( 'Export Encash Requests', 'erp-pro' ); ?>
            </a>
        <?php endif; ?>
    </h2>

    <?php

    if ( isset( $_GET['success'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Successfully applied for employees.', 'erp-pro'); ?></p>
        </div>

        <?php
    endif;

    echo (new ErpErrors( 'apply_forward_leaves' ))->display();

    ?>

    <div class="erp-hr-leave-forward-inner">
        <div class="list-table-wrap">
            <div class="list-table-inner">

                <form method="post">
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="leave">
                    <input type="hidden" name="sub-section" value="forward-leave">
                    <?php
                    $requests_table = new LeaveForwardListTable();
                    $requests_table->prepare_items();
                    $requests_table->views();

                    $requests_table->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    </div><!-- .erp-hr-leave-requests-inner -->
</div><!-- .wrap -->
