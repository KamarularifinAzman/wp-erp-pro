<?php
namespace WeDevs\AdvancedLeave\Unpaid;

use WeDevs\ERP\HRM\Models\FinancialYear;
use WP_List_Table;

if ( ! current_user_can( 'erp_leave_manage' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
}

/**
 * List table class
 */
class LeaveUnpaidListTable extends WP_List_Table {

    protected $page_status;
    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'leave_unpaid',
            'plural'   => 'leave_unpaids',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    /**
     * Message to show if no unpaid leaves found
     *
     * @return void
     */
    function no_items() {
        esc_html_e( 'No unpaid leaves found.', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $leave_unpaid, $column_name ) {
        switch ( $column_name ) {
            case 'amount':
                $html = [
                    '<input id="amount-' . $leave_unpaid->id . '" value="' . $leave_unpaid->amount . '" type="number">',
                    '<a class="unpaid-amount" href="#">&#x2713;</a>'
                ];

                return join($html);

            case 'total':
                return '<span id="total-' . $leave_unpaid->id . '">' . $leave_unpaid->total . '</span>';

            default:
                return isset( $leave_unpaid->$column_name ) ? $leave_unpaid->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'employee_name' => __( 'Employee Name', 'erp' ),
            'policy_name'   => __( 'Policy Name', 'erp' ),
            'days'          => __( 'Days', 'erp' ),
            'f_year'        => __( 'Year', 'erp' ),
            'start_date'    => __( 'Start Date', 'erp' ),
            'end_date'      => __( 'End Date', 'erp' ),
            'amount'        => __( 'Amount', 'erp' ),
            'total'         => __( 'Total', 'erp' )
        );

        return apply_filters( 'erp_hr_leave_unpaid_table_cols', $columns );
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
     * Render extra filtering option on
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
        $financial_years = wp_list_pluck( FinancialYear::orderBy( 'start_date', 'desc')->get(), 'fy_name', 'id' );
        if ( empty( $financial_years ) ) {
            return;
        }
        $f_year = erp_hr_get_financial_year_from_date();
        $f_year = ! empty( $f_year ) ? $f_year->id : '';

        $selected_year = ( isset( $_GET['export_f_year'] ) ) ? absint( wp_unslash( $_GET['export_f_year'] ) ) : $f_year;
        ?>

        <div class="alignleft actions">
            <label class="screen-reader-text" for="export_fy_name"><?php esc_html_e( 'Export', 'erp' ) ?></label>
            <select name="export_f_year" id="export_f_year">
                <?php
                foreach ( $financial_years as $f_id => $f_name ) {
                    echo sprintf( "<option value='%s'%s>%s</option>\n", esc_html( $f_id ), selected( $selected_year, $f_id, false ), esc_html( $f_name ) );
                }
                ?>
            </select>

            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_unpaid_leave', false );
            echo '&nbsp;';
            submit_button( __( 'Export' ), 'button', 'export_unpaid_leave', false );
        echo '</div>';
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {
        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=erp-hr&section=leave' );

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
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

        // get current year as default f_year
        $f_year = erp_hr_get_financial_year_from_date();
        $f_year = ! empty( $f_year ) ? $f_year->id : '';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'f_year'  => isset( $_GET['export_f_year'] ) ? sanitize_text_field( wp_unslash( $_GET['export_f_year'] ) ) : $f_year,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ;
        }

        $items = erp_pro_hr_leave_get_unpaid_leaves( $args );

        $this->items = $items['data'];

        $this->set_pagination_args( array(
            'total_items' => $items['total'],
            'per_page'    => $per_page
        ) );
    }

}

?>

<div class="wrap erp-hr-leave-unpaids">
    <h2><?php esc_html_e( 'Unpaid Leaves', 'erp' ); ?>
        <a href="#" id="calculate-unpaid-leave" class="add-new-h2">
            <?php esc_html_e( 'Calculate', 'erp-pro' ); ?>
        </a>
    </h2>

    <div class="erp-hr-leave-unpaid-inner">
        <div class="list-table-wrap">
            <div class="list-table-inner">

                <form method="get">
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="leave">
                    <input type="hidden" name="sub-section" value="unpaid-leave">
                    <?php
                    $requests_table = new LeaveUnpaidListTable();
                    $requests_table->prepare_items();
                    // $requests_table->search_box( __( 'Search Employee', 'erp' ), 'search_unpaid' );
                    $requests_table->views();

                    $requests_table->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    </div><!-- .erp-hr-leave-requests-inner -->
</div><!-- .wrap -->

