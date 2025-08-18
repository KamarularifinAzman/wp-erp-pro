<?php

namespace WeDevs\HelpScout;

/**
 * This class takes care of requests coming from HelpScout App Integrations
 */
class EndPoint {

    /**
     * @var array|mixed
     */
    private $data;

    /**
     * @var object|boolean
     */
    private $customer;

    /**
     * EndPoint constructor.
     */
    public function __construct() {
        // get request data
        $this->data = $this->parse_data();

        //validate request
        if ( ! $this->validate() ) {
            $this->respond( 'Invalid signature' );
            exit;
        }

        //get customer details
        if ( isset( $this->data['customer'] ) ) {
            $customer_data = $this->get_user_data( $this->data['customer'] );

            $customer       = new Customer( $customer_data );
            $this->customer = $customer;
        }

        //Widget request
        if ( ! isset( $this->data['type'] ) ) {
            $customer_details = $this->customer->get_details();
            if ( ! $customer_details ) {
                $this->respond( __( 'Could not retrieve customer details', 'erp-pro' ) );
            }
            $html = $this->build_response_html( $customer_details );
            $this->respond( $html );
        }
    }

    /**
     * Parse data
     *
     * @since 1.0.0
     * @return array|mixed|object
     */
    private function parse_data() {
        $data_string = file_get_contents( 'php://input' );
        $data        = json_decode( $data_string, true );

        return $data;
    }

    /**
     * Validate request
     *
     * @since 1.0.0
     * @return bool
     */
    private function validate() {
        // we need at least this
        if ( ! isset( $this->data['customer']['email'] ) && ! isset( $this->data['customer']['emails'] ) ) {
            return false;
        }

        // check request signature
        $request = new Request( $this->data );

        if ( isset( $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] ) && $request->signature_equals( $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] ) ) {
            return true;
        }

        return false;
    }


    /**
     * Get customer name
     *
     * @since 1.0.0
     * @return array
     */
    private function get_user_data( array $data ) {
        $first_name = '';
        $last_name  = '';

        if ( isset( $data['fname'] ) ) {
            $first_name = $data['fname'];
        }
        if ( isset( $data['firstName'] ) ) {
            $first_name = $data['firstName'];
        }

        if ( isset( $data['lname'] ) ) {
            $last_name = $data['lname'];
        }
        if ( isset( $data['lastName'] ) ) {
            $last_name = $data['lastName'];
        }

        $emails = array();
        if ( isset( $data['emails'] ) && is_array( $data['emails'] ) && count( $data['emails'] ) > 1 ) {
            $emails = array_values( $data['emails'] );
        } elseif ( isset( $data['email'] ) ) {
            $emails = array( $data['email'] );
        }

        if ( count( $emails ) === 0 ) {
            $this->respond( __( 'No customer email received.', 'erp-pro' ) );
        }

        $details = [
            'fname' => $first_name,
            'lname' => $last_name,
            'email' => @$emails[0],
        ];

        return $details;
    }

    /**
     * Build the markup for helpscout widget
     *
     * @since 1.0.0
     *
     * @param $customer_details
     *
     * @return string|void
     */
    private function build_response_html( $customer_details ) {
        if ( empty( $customer_details ) ) {
            return __( 'No customer found', 'erp-pro' );
        }

        ob_start();
        include ERP_HELPSCOUT_INCLUDES . '/widget.php';
        $html = ob_get_clean();

        return $html;
    }


    /**
     * Set JSON headers, return the given response string
     *
     * @since 1.0.0
     *
     * @param $html
     */
    private function respond( $html ) {
        $response = array( 'html' => $html );

        // clear output, some plugins might have thrown errors by now.
        if ( ob_get_level() > 0 ) {
            ob_end_clean();
        }

        header( 'Content-Type: application/json' );
        echo json_encode( $response );
        die();
    }

}
