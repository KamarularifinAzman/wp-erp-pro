<?php
namespace WeDevs\ERP\SMS;

/**
 * SMS Handler class for Infobip
 *
 * @since 1.0
 */
class Hoiio implements GatewayInterface {

    /**
     * Hoiio App ID
     *
     * @since 1.0
     */
    private $app_id;

    /**
     * Hoiio Access Token
     *
     * @since 1.0
     */
    private $access_token;

    /**
     * Hoiio Class Instance
     *
     * @since 1.0
     */
    public $hoiio_instance;

    /**
     * Infobip Class constructor
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
        $this->app_id       = erp_get_option( 'erp_sms_hoiio_app_id', 'erp_integration_settings_erp-sms' );
        $this->access_token = erp_get_option( 'erp_sms_hoiio_access_token', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {
        $this->hoiio_instance = new \HoiioService( $this->app_id, $this->access_token );
    }

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no_all, $message ) {
        foreach ( $cell_no_all as $cell_no_single ) {
            $result = $this->hoiio_instance( $cell_no_single, $message );
        }
    }
}
