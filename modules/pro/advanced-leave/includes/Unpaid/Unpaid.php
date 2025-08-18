<?php
namespace WeDevs\AdvancedLeave\Unpaid;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

use WeDevs\ERP\HRM\Models\LeavesUnpaid;

class Unpaid {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        if ( get_option('enable_extra_leave') !== 'yes' ) {
            return;
        }

        $this->include_files();

        add_action( 'admin_init', array( $this, 'unpaid_admin_init' ) );
        add_action( 'admin_menu', array( $this, 'unpaid_leave_admin_menu' ) );
        add_action( 'admin_footer', array( $this, 'include_calculate_modal' ) );
        add_action( 'wp_ajax_erp_pro_hr_unpaid_leave_calc', array( $this, 'unpaid_amount_calc' ) );
        add_action( 'wp_ajax_erp_pro_hr_unpaid_leave_calc_single', array( $this, 'unpaid_single_amount_calc' ) );
        add_action( 'wp_ajax_erp_pro_hr_unpaid_leave_export', array( $this, 'unpaid_leaves_export' ) );
    }

    /**
     * Admin init
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function unpaid_admin_init() {
        $export = ( isset( $_GET['export_unpaid_leave'] ) ) ? true : false;

        if ( $export  ) {
            if ( ! check_ajax_referer( 'bulk-leave_unpaids' ) ) {
                die( __( 'Error: Nonce verification failed', 'erp-pro' ) );
            }

            if ( ! current_user_can( 'erp_leave_manage' ) ) {
                wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
            }

            $fyear_id = ( isset( $_GET['export_f_year'] ) ) ? absint( wp_unslash( $_GET['export_f_year'] ) ) : 0;

            erp_pro_hr_leave_unpaid_leaves_export( $fyear_id );
        }
    }

    /**
     * Include files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function include_files() {
        require_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Unpaid/functions.php';
    }

    /**
     * Include menu
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function unpaid_leave_admin_menu() {
        erp_add_submenu( 'hr', 'leave', array(
            'title'         =>  __( 'Unpaid Leaves', 'erp' ),
            'capability'    =>  'erp_leave_manage',
            'slug'          =>  'unpaid-leave',
            'callback'      =>  [ $this, 'leave_unpaid_page' ],
            'position'      =>  36,
        ) );
    }

    /**
     * Include view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function leave_unpaid_page() {
        new LeaveUnpaidListTable();
    }

    /**
     * Include modal view file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function include_calculate_modal() {
        erp_get_js_template( ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Unpaid/modal.php', 'unpaid-leave-modal' );
    }

    /**
     * Include amount calculation
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function unpaid_amount_calc() {
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! check_ajax_referer( 'erp-pro-hr-leave' )) {
            die( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $f_year      = ! empty( $_POST['f-year'] ) ? absint( wp_unslash( $_POST['f-year'] ) ) : 0;
        $salary_type = ! empty( $_POST['salary-type'] ) ? sanitize_text_field( wp_unslash( $_POST['salary-type'] ) ) : '';

        erp_pro_hr_leave_unpaid_update_amount( $f_year, $salary_type );

        wp_send_json_success( true );
    }

    /**
     * Include single amount calculation
     *
     * @since 1.0.0
     *
     * @return float
     */
    public function unpaid_single_amount_calc() {
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'erp-pro-nonce' ) ) {
            die( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $id     = ! empty( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
        $amount = ! empty( $_POST['amount'] ) ? sprintf('%.2f', wp_unslash( $_POST['amount'] )) : 0;

        $total = erp_pro_hr_leave_unpaid_update_single_amount($id, $amount);

        wp_send_json_success( $total );
    }

    /**
     * Unpaid leaves export
     *
     * @since 1.0.0
     *
     * @return float
     */
    public function unpaid_leaves_export() {
        if ( ! current_user_can( 'erp_leave_manage' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! check_ajax_referer( 'erp-pro-nonce' )) {
            die( __( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        $f_year = ! empty( $_GET['f_year'] ) ? absint( wp_unslash( $_GET['f_year'] ) ) : 0;

        $data = LeavesUnpaid::where('f_year', $f_year)->get()->toArray();

        wp_send_json_success( $data );
    }

}
