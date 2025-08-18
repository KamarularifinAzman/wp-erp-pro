<?php
namespace WeDevs\ERP\Accounting\Reimbursement;

class Admin {

	/**
     * Itializes the class
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.0.0
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor for the class
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->actions();
        $this->filters();
    }

    /**
     * All actions are doing here
     *
     * @since  1.0.0
     *
     * @return  void
     */
    function actions() {
        add_action( 'admin_menu', [ $this, 'menu' ], 12 );
        add_action( 'admin_init', 'erp_ac_reimbur_bulk_action' );
        add_action( 'admin_init', 'erp_ac_reimbur_bulk_action' );
        add_action( 'wp_ajax_erp-ac-reimbur-trns-row-status', 'erp_ac_reimbur_ajax_handel_trn_update' );
        add_action( 'erp_ac_new_transaction_reimbur', 'erp_ac_reibur_after_new_trans', 10, 3 );
    }

    /**
     * All filters are doing here
     *
     * @since  1.0.0
     *
     * @return  void
     */
    function filters() {
        add_filter( 'erp_ac_redirect_after_transaction', 'erp_reimburs_redirect', 10, 3 );
        add_filter( 'erp_ac_trans_status', 'erp_ac_reimbur_trans_status', 10, 2 );
        add_filter( 'erp_ac_register_type', 'erp_ac_reimbur_register_type' );
        add_filter( 'erp_ac_partial_types', 'erp_ac_reimbur_partial_types', 10, 2 );
        add_filter( 'erp_ac_is_due_trans', 'erp_ac_reimbur_is_due_trans', 10, 2 );
        add_filter( 'erp_ac_form_types', 'erp_ac_reimbursement_form_types', 10, 2 );
        add_filter( 'erp_ac_single_partial_payment_url', 'erp_ac_reimbur_single_partial_payment_url', 10, 2 );
        //add_filter( 'erp_ac_trial_balance_where', 'erp_ac_reimbur_trial_balance_where' );
        //add_filter( 'erp_ac_trial_balance_join', 'erp_ac_reimbure_trial_balance_join' );
        add_filter( 'erp_ac_dashboard_expense_args', 'erp_ac_dashboard_expense_args' );
        add_filter( 'erp_ac_expense_pie_chart', 'erp_ac_expense_pie_chart' );
        add_filter( 'erp_ac_net_expense', 'erp_ac_net_expense', 10, 2 );
        add_filter( 'erp_ac_net_income_args', 'erp_ac_net_income_args' );
        add_filter( 'erp_ac_bill_payable_arags', 'erp_ac_bill_payable_arags' );
    }

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     */
    function menu() {
        $capability = 'erp_ac_manager';

        if ( self::need_backward_compatible() ) {
            $this->get_old_menus( $capability );
        } else {
            $this->get_new_menus( $capability );
        }

        add_action( 'admin_footer', array( $this, 'reimbursement_script' ) );
    }

    /**
     * Load reimbursement scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function reimbursement_script() {
        $chart_script = new \WeDevs\ERP\Accounting\AdminMenu();
        $chart_script->chart_script();
        $chart_script->common_scripts();
        wp_enqueue_script( 'erp-ac-reimur', WPERP_REIMBURSEMENT_ASSETS . '/reimbursement.js', array('jquery'), WPERP_REIMBURSEMENT_VERSION, true );
        wp_localize_script( 'erp-ac-reimur', 'erp_ac_tax', [ 'rate' => erp_ac_get_tax_info() ] );
    }

    /**
     * Load reimbursement view page
     *
     * @since 1.0.0
     *
     * @return void
     */
    function reimbursement() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';

        $type     = isset( $_GET['type'] ) ? $_GET['type'] : 'pv';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $draft    = isset( $_GET['transaction_id'] ) ? intval( $_GET['transaction_id'] ) : 0;
        $id       = $id ? $id : $draft;

        $template = '';

        switch ( $action ) {
            case 'new':
                if ( $type == 'reimbur_invoice' ) {
                    $template = WPERP_REIMBURSEMENT_VIEWS . '/invoice-new.php';
                } else if ( $type == 'reimbur_payment' && ! erp_ac_reimbur_is_employee() ) {
                   $template = WPERP_REIMBURSEMENT_VIEWS . '/payment-new.php';
                }else {
                    $template = apply_filters( 'erp_ac_reimbursement_invoice_transaction_template', $template );
                }

                break;

            case 'view':
                $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $id );

                if ( $transaction->form_type == 'reimbur_invoice' ) {
                    $template = WPERP_REIMBURSEMENT_VIEWS . '/invoice-single.php';
                } else {
                    $template = WPERP_REIMBURSEMENT_VIEWS . '/payment-single.php';
                }

                break;

            default:
                $template = WPERP_REIMBURSEMENT_VIEWS . '/reimbursement-list.php';
                break;
        }

        include_once $template;
    }

    /**
     * check if backward compatibility is needed
     * para
     * @return boolean
     **/
    public static function need_backward_compatible() {
        $installed_version  = get_option( 'wp_erp_version' );
        $new_version = '1.4.0';

        if ( ! is_null( $installed_version ) && version_compare( $installed_version, $new_version, '<' ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get old menus
     * para
     * @return void
     **/
    public function get_old_menus( $capability ) {

        if ( current_user_can( 'erp_ac_manager' ) ) {
            add_submenu_page( 'erp-accounting', __( 'Reimbursement', 'reimbursement' ), __( 'Reimbursement', 'erp-reimburs' ), 'erp_ac_manager', 'erp-accounting-reimbursement', array( $this, 'reimbursement' ) );
        } else {
            add_submenu_page( 'erp-hr', __( 'Reimbursement', 'erp' ), __( 'Reimbursement', 'erp' ), 'read', 'erp-accounting-reimbursement', array( $this, 'reimbursement' ) );

            // add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' )

        }
    }

    /**
     * get new menus
     * para
     * @return mixed
     **/
    public function get_new_menus( $capability ) {
        if ( current_user_can( 'erp_ac_manager' ) ) {
            erp_add_menu('accounting', [
                'title'      => __('Reimbursement', 'erp'),
                'capability' => $capability,
                'slug'       => 'reimbursement',
                'callback'   => array($this, 'reimbursement'),
                'position'   => 102
            ]);
        } else {
            erp_add_menu( 'hr', [
                'title'      =>  __( 'Reimbursement', 'erp' ),
                'capability' => 'read',
                'slug'       => 'reimbursement',
                'callback'   => array( $this, 'reimbursement' ),
                'position'   => 102
            ] );
        }
    }
}

\WeDevs\ERP\Accounting\Reimbursement\Admin::init();
