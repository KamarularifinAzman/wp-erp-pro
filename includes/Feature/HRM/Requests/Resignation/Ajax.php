<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Resignation;

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
        $this->action( 'wp_ajax_erp_hr_employee_resign', 'add_resign_request' );
        $this->action( 'wp_ajax_erp_hr_employee_cancel_resign', 'withdraw_resign_request' );
        $this->action( 'wp_ajax_erp_hr_employee_get_resign_request', 'get_resign_request' );
        $this->action( 'wp_ajax_erp_hr_send_resign_request_email', 'send_resign_email' );
    }

    /**
     * Handles the resign request
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function add_resign_request() {
        $this->verify_nonce( 'employee_resign_request' );

        if ( ! current_user_can( 'employee' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $user_id = ( isset( $_REQUEST['user_id'] ) ) ? absint( wp_unslash( $_REQUEST['user_id'] ) ) : null;

        if ( empty( $user_id ) ) {
            $this->send_error( __( 'Invalid request. User not found!', 'erp-pro' ) );
        }

        if ( erp_hr_employee_exists_resign_request( $user_id, 'pending' ) ) {
            $this->send_error( __( 'You already have a resign request pending!', 'erp-pro' ) );
        }

        $resign_details = ( isset( $_REQUEST['resign_details'] ) ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['resign_details'] ) ) : '';
        $resign_reason  = ( isset( $_REQUEST['resign_reason'] ) )  ? sanitize_text_field( wp_unslash( $_REQUEST['resign_reason'] ) )      : '';
        $request_type   = ( isset( $_REQUEST['request_type'] ) )   ? sanitize_text_field( wp_unslash( $_REQUEST['request_type'] ) )       : 'resigned';
        $resign_date    = ( isset( $_REQUEST['resign_date'] ) )    ? sanitize_text_field( wp_unslash( $_REQUEST['resign_date'] ) )        : erp_current_datetime()->format( 'Y-m-d' );

        $args = [
            'user_id'  => $user_id,
            'status'   => 'pending',
            'reason'   => $resign_reason,
            'date'     => $resign_date,
        ];

        $inserted = erp_hr_employee_insert_resign_request( $args );

        if ( is_wp_error( $inserted ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success( [
            'msg'  => __( 'Your request has been processed for awaiting approval.', 'erp-pro' ),
            'data' => $args + [
                'description' => $resign_details,
                'action'      => 'erp_hr_send_resign_request_email'
            ]
        ] );
    }

    /**
     * Handles the resignation email notification to HR
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function send_resign_email() {
        if ( ! current_user_can( 'employee' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $data = [
            'date'        => ! empty( $_REQUEST['date'] )        ? sanitize_text_field( wp_unslash( $_REQUEST['date'] ) )            : erp_current_datetime()->format( 'Y-m-d' ),
            'user_id'     => ! empty( $_REQUEST['user_id'] )     ? absint( wp_unslash( $_REQUEST['user_id'] ) )                      : 0,
            'reason'      => ! empty( $_REQUEST['reason'] )      ? sanitize_text_field( wp_unslash( $_REQUEST['reason'] ) )          : '',
            'description' => ! empty( $_REQUEST['description'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['description'] ) ) : '',
        ];

        $mail_status =  do_action( 'erp_hr_employee_after_resign_request', $data );

        if ( is_wp_error( $mail_status ) ) {
            $this->send_error( __( 'Something went wrong. Could not send email to HR!', 'erp-pro' ) );
        }

        $this->send_success( __( 'Resignation email has been sent to HR.', 'erp-pro' ) );
    }

    /**
     * Withdraws pending resign request for a specific employee
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function withdraw_resign_request() {
        $this->verify_nonce( 'employee_resign_request' );

        if ( ! current_user_can( 'employee' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $user_id = ( isset( $_REQUEST['user_id'] ) ) ? absint( wp_unslash( $_REQUEST['user_id'] ) ) : null;

        if ( empty( $user_id ) ) {
            $this->send_error( __( 'Invalid request. User not found!', 'erp-pro' ) );
        }

        if ( erp_hr_employee_exists_resign_request( $user_id, 'approved' ) ) {
            $this->send_error( __( 'Your resign request is already approved!', 'erp-pro' ) );
        }

        global $wpdb;

        $req_id  = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                FROM {$wpdb->prefix}erp_hr_employee_resign_requests
                WHERE `user_id` = %d
                AND `status` = 'pending'",
                $user_id
            )
        ); 

        $deleted = erp_hr_delete_resign_request_by( $req_id );

        if ( is_wp_error( $deleted ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success( __( 'Your resign request has been withdrawn successfully.', 'erp-pro' ) );
    }

    /**
     * Get a single resign request
     * 
     * @since 1.2.0
     *
     * @return mixed
     */
    public function get_resign_request() {
        $this->verify_nonce( 'wp-erp-hr-nonce' );

        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_hr_manager' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! empty( $_REQUEST['req_id'] ) ) {
            $id = intval( wp_unslash( $_REQUEST['req_id'] ) );
        } else {
            $this->send_error( __( 'Request not found!', 'erp-pro' ) );
        }

        $request = erp_hr_employee_get_resign_request( $id );

        $request['created'] = date( 'l, F jS, Y', strtotime( $request['created'] ) );

        if ( ! empty( $request['date'] ) ) {
            $request['date'] = date( 'l, F jS, Y', strtotime( $request['date'] ) );
        }

        if ( is_wp_error( $request ) ) {
            $this->send_error( __( 'Could not process the request. Try again later!', 'erp-pro' ) );
        }

        $this->send_success( $request );
    }
}