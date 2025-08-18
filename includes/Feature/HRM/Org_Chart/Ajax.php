<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Org_Chart;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\Traits\Ajax as Trait_Ajax;

/**
 * Ajax handler class
 * 
 * @since 1.2.0
 */
class Ajax {

    use Hooker;
    use Trait_Ajax;

    /**
     * The class constructor
     *
     * @since 1.2.1
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_hr_get_orgchart', 'get_orgchart' );
    }

    /**
     * Get the employee hierarchy for org chart
     * 
     * @since 1.2.1
     *
     * @return mixed
     */
    public function get_orgchart() {
        $this->verify_nonce( 'erp-hr-org-chart' );

        if ( ! current_user_can( 'erp_list_employee' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $dept_id = ! empty( $_POST['dept_id'] ) ? intval( wp_unslash( $_POST['dept_id'] ) ) : null;

        $data = Helpers::get_employee_hierarchy( $dept_id );

        if ( is_wp_error( $data ) ) {
            $this->send_error( __( 'Something went wrong. Try again later!', 'erp-pro' ) );
        }

        $this->send_success( $data );
    }
}