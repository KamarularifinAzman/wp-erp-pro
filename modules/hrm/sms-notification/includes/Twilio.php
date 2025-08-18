<?php
namespace WeDevs\ERP\SMS;

use Twilio\Rest\Client;
/**
 * SMS Handler class for twilio
 *
 * @since 1.0
 */
class Twilio implements GatewayInterface{

    /**
     * The number SMS will be sent from
     *
     * @since 1.0
     */
    private $number_from;

    /**
     * Twilio account SID
     *
     * @since 1.0
     */
    private $account_sid;

    /**
     * Twilio Auth Token
     *
     * @since 1.0
     */
    private $auth_token;

    /**
     * Twilio instance class
     *
     * @since 1.0
     */
    private $instance;

    /**
     * The constructor function
     *
     * @since 1.0
     */
    public function __construct() {
        $this->get_credentials();
        $this->prepare();
    }

    /**
     * Setup Credentials
     *
     * @since 1.0
     */
    public function get_credentials() {
        $this->number_from = erp_get_option( 'erp_sms_twilio_number_from', 'erp_integration_settings_erp-sms' );
        $this->account_sid = erp_get_option( 'erp_sms_twilio_account_sid', 'erp_integration_settings_erp-sms' );
        $this->auth_token  = erp_get_option( 'erp_sms_twilio_auth_token', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {
        $this->instance = new Client( $this->account_sid, $this->auth_token );
    }

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no, $message ) {
        if ( 0 !== strpos( $cell_no_single, '+' ) ) {
            $cell_no_single = '+' . $cell_no_single;
        }

        foreach ( $cell_no as $cell_no_single ) {
            try {
                return $this->instance->messages->create( $cell_no_single, [
                    'body' => $message,
                    'from' => $this->number_from
                ] );
            } catch ( Exception $e ) {
                return $e->getMessage();
            }
        }
    }
}
