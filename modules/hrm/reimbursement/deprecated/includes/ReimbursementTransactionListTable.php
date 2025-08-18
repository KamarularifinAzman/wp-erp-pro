<?php
namespace WeDevs\ERP\Accounting\Reimbursement;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class for Reimbursement Transactions
 *
 * @since 1.0.0
 */
class ReimbursementTransactionListTable extends \WeDevs\ERP\Accounting\Reimbursement\Transaction_List_Table {

    private $counts = array();
    private $page_status = '';

    /**
     * Load autometically
     *
     * @since 1.0.0
     */
    function __construct() {
        global $status, $page;

        $this->type = 'reimbur';

        if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
            $this->slug = 'erp-accounting-reimbursement';
        } else {
            $this->slug = 'erp-accounting&section=reimbursement';
            if ( current_user_can( 'employee' ) ) {
                $this->slug = 'erp-hr&section=reimbursement';
            }
        }

        \WP_List_Table::__construct([
            'singular' => 'reimbursement',
            'plural'   => 'reimbursements',
            'ajax'     => false
        ]);
    }

    /**
     * Generate the table navigation above or below the table
     *
     * @since 1.0.0
     *
     * @param string $which
     */
    protected function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        }
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
        <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
        ?>

        <br class="clear" />
        </div>
    <?php
    }

    /**
     * Get form types
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_form_types() {
        return erp_ac_reimbursement_register_form_types();
    }

    /**
     * Get the column names
     *
     * @since 1.0.0
     *
     * @return array
     */
    function get_columns() {
        $filter = isset( $_GET['filter'] ) ? $_GET['filter'] : false;
        $columns = array(
            'issue_date' => __( 'Date', 'erp-pro' ),
            'user_id'    => __( 'Receipt From', 'erp-pro' ),
            'ref'        => __( 'Ref', 'erp-pro' ),
            'due'        => __( 'Due', 'erp-pro' ),
            'total'      => __( 'Total', 'erp-pro' ),
            'status'     => __( 'Status', 'erp-pro' ),
        );

       if (  ( ! erp_ac_reimbur_is_employee() && ( $filter == 'awaiting-approval' || $filter == 'void' || $filter == 'closed' || $filter == 'awaiting-payment' ) || $filter == 'draft' ) ) {
            $action = [ 'cb' => '<input type="checkbox" />'];
            $columns = array_merge( $action, $columns );
       }

       if ( erp_ac_reimbur_is_employee() && $filter == 'draft' ) {
            $action = [ 'cb' => '<input type="checkbox" />'];
            $columns = array_merge( $action, $columns );
       }

        return $columns;
    }

    /**
     * Render the issue date column
     *
     * @since 1.0.0
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_issue_date( $item ) {
        $filter  = isset( $_GET['filter'] ) ? $_GET['filter'] : '';
        $url      = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=' . $item->form_type . '&transaction_id=' . $item->id .'&section=' . $filter );
        $paid_url = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=reimbur_payment&transaction_id=' . $item->id );
        $edit     = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Edit', 'erp-pro' ) );
        $delete   = sprintf( '<a href="#" class="erp-ac-reimbur-trns-row-status" data-status="delete" data-id="%d" title="%s">%s</a>', $item->id, __( 'Delete', 'erp-pro' ), __( 'Delete', 'erp-pro' ) );
        $void     = sprintf( '<a href="#" class="erp-ac-reimbur-trns-row-status" data-status="void" data-id="%d" title="%s">%s</a>', $item->id, __( 'Void', 'erp-pro' ), __( 'Void', 'erp-pro' ) );
        $approve  = sprintf( '<a href="#" class="erp-ac-reimbur-trns-row-status" data-status="awaiting_approval" data-id="%d" title="%s">%s</a>', $item->id, __( 'Submit for Approve', 'erp-pro' ), __( 'Submit for Approve', 'erp-pro' ) );
        $payment  = sprintf( '<a href="#" class="erp-ac-reimbur-trns-row-status" data-status="awaiting_payment" data-id="%d" title="%s">%s</a>', $item->id, __( 'Submit for Payment', 'erp-pro' ), __( 'Submit for Payment', 'erp-pro' ) );
        $paid     = sprintf( '<a href="%s"  title="%s">%s</a>', $paid_url, __( 'Paid', 'erp-pro' ), __( 'Paid', 'erp-pro' ) );

        if ( $item->status == 'draft' ) {
            $actions['approval'] =  $approve;
        }

        if ( $item->status == 'awaiting_approval' && ! erp_ac_reimbur_is_employee() ) {
             $actions['payment'] =  $payment;
        }

        if ( ( $item->status == 'awaiting_payment' ||  $item->status == 'partial' ) && $item->form_type == 'reimbur_invoice' && ! erp_ac_reimbur_is_employee() ) {
            $actions['paid'] = $paid;
        }

        if ( ( $item->status == 'awaiting_approval' || $item->status == 'awaiting_payment' || $item->status == 'closed' || $item->status == 'partial' || $item->status == 'paid' ) && ! erp_ac_reimbur_is_employee() ) {
            $actions['void'] = $void;
        }

        if ( ($item->status == 'draft' || ( $item->status == 'awaiting_payment' || $item->status == 'awaiting_approval' ) && ! erp_ac_reimbur_is_employee() ) ) {
            $actions['edit'] = $edit;
        }

        if ( $item->status == 'draft' || ( $item->status == 'void' && ! erp_ac_reimbur_is_employee() ) ) {
            $actions['delete'] = $delete;
        }

        if ( isset( $actions ) && count( $actions ) ) {
            return sprintf( '<a href="%1$s">%2$s</a> %3$s', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ), $this->row_actions( $actions ) );
        } else {
            return sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ) );
        }
    }

    /**
     * Manage column status
     *
     * @since 1.0.0
     *
     * @param  array $item
     *
     * @return string
     */
    public function column_status( $item ) {
        switch ( $item->status ) {
            case 'awaiting_approval':
                if ( erp_ac_reimbur_is_employee() ) {
                    $status = __( 'Awaiting for approval', 'erp-pro' );
                    break;
                }
                $url   = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=' . $item->form_type . '&transaction_id=' . $item->id );
                $status = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Awaiting for approval', 'erp-pro' ) );
                break;

            case 'draft':
                $url   = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=' . $item->form_type . '&transaction_id=' . $item->id );
                $status = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Draft', 'erp-pro' ) );
                break;

            case 'awaiting_payment':
                if (  erp_ac_reimbur_is_employee() ) {
                    $status = __( 'Awaiting for payment', 'erp-pro' );
                    break;
                }

                $url    = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=reimbur_payment&transaction_id=' . $item->id );
                $status = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Awaiting for payment', 'erp-pro' ) );
                break;

            case 'paid':
                $url    = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=reimbur_payment&transaction_id=' . $item->id );
                $status = __( 'Paid', 'erp-pro' );
                break;

            case 'closed':
                $url    = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=reimbur_payment&transaction_id=' . $item->id );
                $status = __( 'Closed', 'erp-pro' );
                break;

            case 'void':
                $url    = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=' . $item->form_type . '&transaction_id=' . $item->id );
                $status = sprintf( '<a href="%1s">%2s</a>', $url, __( 'void', 'erp-pro' ) );
                break;

            case 'partial':
                if ( erp_ac_reimbur_is_employee() ) {
                    $status = __( 'Partially Paid', 'erp-pro' );
                } else {
                    $url    = admin_url( 'admin.php?page=' . $this->slug . '&action=new&type=reimbur_payment&transaction_id=' . $item->id );
                    $status = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Partially Paid', 'erp-pro' ) );
                }
                break;
        }

        return isset( $status ) ? $status : '';
    }

    /**
     * Column form type
     *
     * @since  1.0.0
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_form_type( $item ) {
        if ( $item->form_type == 'reimbur_payment' ) {
            return __( 'Payment', 'erp-pro' );
        }

        return __( 'Invoice', 'erp-pro' );
    }

    /**
     * Added column user id
     *
     * @since  1.0.0
     *
     * @param  object $item
     *
     * @return string
     */
    public function column_user_id( $item ) {
        $user_display_name = '';
        $actions           = array();
        $transaction       = \WeDevs\ERP\Accounting\Model\Transaction::find( $item->id );

        $employee = get_user_by( 'id', intval( $transaction->user_id ) );
        if ( erp_ac_reimbursement_is_hrm_active() && user_can( $transaction->user_id, 'employee' ) ) {
            $employee          = new \WeDevs\ERP\HRM\Employee( intval( $transaction->user_id ) );
            $user_display_name = $employee->get_full_name();
            $profile           = $employee->get_details_url();
        } else {
            $user_display_name = $employee->display_name;
            $profile           = admin_url( 'user-edit.php?user_id=' . $transaction->user_id );
        }

        return sprintf( '<a href="%1$s">%2$s</a> %3$s', $profile, $user_display_name, $this->row_actions( $actions ) );
    }

    /**
     * Add extra table nav Filters
     *
     * @since 1.0.0
     *
     * @param  string  $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        $filter = isset( $_GET['filter'] ) ? $_GET['filter'] : false;
        if ( 'top' == $which ) {
            echo '<div class="alignleft actions">';

            $types     = [];
            $all_types = $this->get_form_types();

            foreach ($all_types as $key => $type) {
                $types[ $key ] = $type['label'];
            }

            $type = [];

            if ( $filter == 'void' ) {
                $type = [ 'delete' => __( 'Delete', 'erp-pro' ) ];
            } else if ( $filter == 'awaiting-approval' ) {
                $type = [ 'void' => __( 'Void', 'erp-reimbursment' ), 'awaiting_payment' => __( 'Payment', 'erp-reimbursment' ) ];
            } else if ( $filter == 'awaiting-payment' || $filter == 'closed' ) {
                $type = [ 'void' => __( 'Void', 'erp-reimbursment' ) ];
            } else if ( $filter == 'draft' ) {
                $type = [ 'delete' => __( 'Delete', 'erp-pro' ), 'awaiting_approval' => __( 'Approve', 'erp-pro' ) ];
            }

            if ( $filter ) {
                erp_html_form_input([
                    'name'    => 'action',
                    'type'    => 'select',
                    'options' => [ '-1' => __( 'Bulk Actions', 'erp-pro' ) ] + $type
                ]);

                submit_button( __( 'Apply', 'erp-pro' ), 'button', 'submit_action_delete', false, ['data-status' => $filter] );
            }

            erp_html_form_input([
                'name'        => 'user_id',
                'type'        => 'hidden',
                'class'       => 'erp-ac-customer-search',
                'placeholder' => __( 'Search for Customer', 'erp-pro' ),
            ]);

            erp_html_form_input([
                'name'        => 'start_date',
                'class'       => 'erp-date-field',
                'value'       => isset( $_REQUEST['start_date'] ) && !empty( $_REQUEST['start_date'] ) ? $_REQUEST['start_date'] : '',
                'placeholder' => __( 'Start Date', 'erp-pro' )
            ]);

            erp_html_form_input([
                'name'        => 'end_date',
                'class'       => 'erp-date-field',
                'value'       => isset( $_REQUEST['end_date'] ) && !empty( $_REQUEST['end_date'] ) ? $_REQUEST['end_date'] : '',
                'placeholder' => __( 'End Date', 'erp-pro' )
            ]);

            erp_html_form_input([
                'name'        => 'ref',
                'value'       => isset( $_REQUEST['ref'] ) && ! empty( $_REQUEST['ref'] ) ? $_REQUEST['ref'] : '',
                'placeholder' => __( 'Ref No.', 'erp-pro' )
            ]);

            submit_button( __( 'Filter', 'erp-pro' ), 'button', 'submit_filter_sales', false );

            echo '</div>';
        }
    }

    /**
     * Set the views
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_views() {
        $status_links = array();
        $base_link    = admin_url( 'admin.php?page=erp-hr-employee' );

        foreach ( $this->counts as $key => $value ) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', erp_ac_reimbur_section_url( $key ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Get section transaction total for pagination
     *
     * @since  1.0.0
     *
     * @param  array $count
     *
     * @return int
     */
    function get_transaction_count( $count ) {
        $filter = isset( $_REQUEST['filter'] ) ? $_REQUEST['filter'] : '';

        switch ( $filter ) {
            case 'draft':
                return isset( $count['draft']['count'] ) ? intval( $count['draft']['count'] ) : 0;
                break;

            case 'awaiting-approval':
                return isset( $count['awaiting_approval']['count'] ) ? intval( $count['awaiting_approval']['count'] ) : 0;
                break;

            case 'awaiting-payment':
                return isset( $count['awaiting_payment']['count'] ) ? intval( $count['awaiting_payment']['count'] ) : 0;
                break;

            case 'paid':
                return isset( $count['paid']['count'] ) ? intval( $count['paid']['count'] ) : 0;
                break;

            case 'void':
                return isset( $count['void']['count'] ) ? intval( $count['void']['count'] ) : 0;
                break;

            case 'closed':
                return isset( $count['closed']['count'] ) ? intval( $count['closed']['count'] ) : 0;
                break;

            case 'partial':
                return isset( $count['partial']['count'] ) ? intval( $count['partial']['count'] ) : 0;
                break;

            default:
                return isset( $count['all']['count'] ) ? intval( $count['all']['count'] ) : 0;
                break;
        }
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
        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['filter'] ) ? erp_ac_reimbur_get_status_from_url( sanitize_text_field( $_GET['filter'] ) ) : 'all';
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // only ncessary because we have sample data
        $args = array(
            'type'   => $this->type,
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        // search params
        if ( isset( $_REQUEST['start_date'] ) && !empty( $_REQUEST['start_date'] ) ) {
            $args['start_date'] = $_REQUEST['start_date'];
        }

        if ( isset( $_REQUEST['end_date'] ) && !empty( $_REQUEST['end_date'] ) ) {
            $args['end_date'] = $_REQUEST['end_date'];
        }

        if ( isset( $_REQUEST['form_type'] ) && ! empty( $_REQUEST['form_type'] ) ) {
           $args['form_type'] = $_REQUEST['form_type'];
        }

        if ( isset( $_REQUEST['filter'] ) ) {
            $args['status'] = erp_ac_reimbur_get_status_from_url( $_REQUEST['filter'] );
        }

        if ( isset( $_REQUEST['ref'] ) && ! empty( $_REQUEST['ref'] ) ) {
            $args['ref'] = $_REQUEST['ref'];
        }

        if ( erp_ac_reimbur_is_employee() ) {
            $args['user_id'] = get_current_user_id();
        }

        $this->counts = erp_ac_reimbur_transaction_count();
        $this->items  = $this->get_transactions( $args );

        $this->set_pagination_args( array(
            'total_items' => $this->get_transaction_count( $this->counts ),
            'per_page'    => $per_page
        ) );
    }
}
