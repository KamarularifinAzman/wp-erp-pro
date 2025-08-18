<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Remote_Work;

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
     * @since 1.2.0
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_hr_employee_get_remote_work_request', 'get_remote_work_request' );
        $this->action( 'wp_ajax_erp_hr_employee_get_single_remote_work_history', 'get_single_remote_work_history' );
        $this->action( 'wp_ajax_erp_hr_employee_filter_remote_work_history', 'filter_remote_work_history' );
        $this->action( 'wp_ajax_erp_hr_employee_remote_work_request', 'add_remote_work_request' );
        $this->action( 'wp_ajax_erp_hr_employee_edit_remote_work_req', 'update_remote_work_request' );
        $this->action( 'wp_ajax_erp_hr_employee_delete_remote_work_req', 'delete_remote_work_request' );
    }

    /**
     * Get a single remote work request
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function get_remote_work_request() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_hr_manager' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( ! empty( $_REQUEST['req_id'] ) ) {
            $id = intval( wp_unslash( $_REQUEST['req_id'] ) );
        } else {
            $this->send_error( __( 'Request not found!', 'erp-pro' ) );
        }

        $request = erp_hr_employee_get_remote_work_request( $id );

        $request['created'] = date( 'l, F jS, Y', strtotime( $request['created'] ) );

        $request['start_date'] = date( 'l, F jS, Y', strtotime( $request['start_date'] ) );
        $request['end_date'] = date( 'l, F jS, Y', strtotime( $request['end_date'] ) );

        if ( is_wp_error( $request ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success( $request );
    }

    /**
     * Gets remote request from employee end
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function get_single_remote_work_history() {
        $this->verify_nonce( 'employee_remote_work_request' );

        if ( ! current_user_can( 'employee' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $id = ! empty( $_REQUEST['req_id'] ) ? intval( wp_unslash( $_REQUEST['req_id'] ) ) : 0;

        $request = erp_hr_employee_get_remote_work_request( $id );

        if ( is_wp_error( $request ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success( $request );
    }

    /**
     * Filters remote work requests on employee history
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function filter_remote_work_history() {
        $this->verify_nonce( 'employee_remote_work_request' );

        if ( ! current_user_can( 'employee' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $args = [
            'user_id' => get_current_user_id(),
            'date'    => [
                'start' => erp_current_datetime()->format( 'Y-01-01 00:00:00' ),
                'end'   => erp_current_datetime()->format( 'Y-12-31 23:59:59' )
            ],
            'number'  => null
        ];

        if ( ! empty( $_REQUEST['year'] ) ) {
            $date = sanitize_text_field( wp_unslash( $_REQUEST['year'] ) ) . '-01-01';
            
            $args['date']['start'] = erp_current_datetime()->modify( $date )->format( 'Y-01-01 00:00:00' );
            $args['date']['end']   = erp_current_datetime()->modify( $date )->format( 'Y-12-31 23:59:59' );
        }

        if ( ! empty( $_REQUEST['status'] ) && '-1' != $_REQUEST['status'] ) {
            $args['status'] = sanitize_text_field( wp_unslash( $_REQUEST['status'] ) );
        }
        
        $result = erp_hr_employee_get_remote_work_requests( $args );

        if ( is_wp_error( $result ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        ob_start();

        $requests = $result['data'];

        include_once ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/remote-work-history-data.php';

        $this->send_success( ob_get_clean() );
    }

    /**
     * Adds a remote work request
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function add_remote_work_request() {
        $this->verify_nonce( 'employee_remote_work_request' );

        if ( ! erp_hr_is_employee_active() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $user_id        = ! ( empty( $_REQUEST['user_id'] ) )      ? intval( wp_unslash( $_REQUEST['user_id'] ) )                       : get_current_user_id();
        $start_date     = ! ( empty( $_REQUEST['start_date'] ) )   ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) )       : '';
        $end_date       = ! ( empty( $_REQUEST['end_date'] ) )     ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) )         : '';
        $reason         = ! ( empty( $_REQUEST['reason'] ) )       ? sanitize_text_field( wp_unslash( $_REQUEST['reason'] ) )           : '';
        $other_reason   = ! ( empty( $_REQUEST['other_reason'] ) ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['other_reason'] ) ) : '';

        $insert_id      = erp_hr_employee_insert_remote_work_request( [
            'user_id'      => $user_id,
            'reason'       => $reason,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
            'other_reason' => $other_reason
        ] );

        if ( is_wp_error( $insert_id ) ) {
            $this->send_error( $insert_id->get_error_message() );
        }

        $this->send_success(  __( 'Request for remote work has been created successfully.', 'erp-pro' ) );
    }

    /**
     * Updates a remote work request
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function update_remote_work_request() {
        $this->verify_nonce( 'employee_remote_work_request' );

        if ( ! erp_hr_is_employee_active() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $id             = ! ( empty( $_REQUEST['req_id'] ) )       ? intval( wp_unslash( $_REQUEST['req_id'] ) )                        : 0;
        $start_date     = ! ( empty( $_REQUEST['start_date'] ) )   ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) )       : '';
        $end_date       = ! ( empty( $_REQUEST['end_date'] ) )     ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) )         : '';
        $reason         = ! ( empty( $_REQUEST['reason'] ) )       ? sanitize_text_field( wp_unslash( $_REQUEST['reason'] ) )           : '';
        $other_reason   = ! ( empty( $_REQUEST['other_reason'] ) ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['other_reason'] ) ) : '';

        $exist_request  = erp_hr_exists_remote_work_date_range( get_current_user_id(), $start_date, $end_date, $id );

        if ( $exist_request ) {
            $this->send_error( __( 'You already have a request within the date range. Please select different dates.', 'erp-pro' ) );
        }

        $update_id      = erp_hr_employee_update_remote_work_request( $id, [
            'reason'       => $reason,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
            'other_reason' => $other_reason
        ] );

        if ( is_wp_error( $update_id ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success(  __( 'Request for remote work has been updated successfully.', 'erp-pro' ) );
    }

    /**
     * Deletes remote work request
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function delete_remote_work_request() {
        $this->verify_nonce( 'employee_remote_work_request' );

        if ( ! erp_hr_is_employee_active() ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $id = ! empty( $_REQUEST['req_id'] ) ? intval( wp_unslash( $_REQUEST['req_id'] ) ) : 0;

        $deleted = erp_hr_delete_remote_work_request_by( $id );

        if ( is_wp_error( $deleted ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success(  __( 'Request for remote work has been deleted successfully.', 'erp-pro' ) );
    }
}