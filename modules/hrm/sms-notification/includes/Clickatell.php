<?php
namespace WeDevs\ERP\SMS;

/**
 * SMS Handler class for Clickatell
 *
 * @since 1.0
 */
class Clickatell implements GatewayInterface {

    /**
     * Username for Clickatel Account
     *
     * @since 1.0
     */
    private $username;

    /**
     * Password for Clickatel Account
     *
     * @since 1.0
     */
    private $password;

    /**
     * API ID for Clickatel Account
     *
     * @since 1.0
     */
    private $api_id;

    /**
     * URL
     *
     * @since 1.0
     */
    private $url;

    /**
     * SMS Body
     *
     * @since 1.0
     */
    private $sms_body;

    /**
     * Twilio Class constructor
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
        $this->username = erp_get_option( 'erp_sms_clickatell_username', 'erp_integration_settings_erp-sms' );
        $this->password = erp_get_option( 'erp_sms_clickatell_password', 'erp_integration_settings_erp-sms' );
        $this->api_id   = erp_get_option( 'erp_sms_clickatell_api_id', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {
        $this->url = sprintf(
            'http://api.clickatell.com/http/sendmsg?user=%s&password=%s&api_id=%s',
            $this->username,
            $this->password,
            $this->api_id
        );
    }

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no_all, $message ) {
        foreach ( $cell_no_all as $cell_no_single ) {
            $this->url = sprintf( '%s&to=%s&text=%s', $this->url, $cell_no_single, $message );
            $result    = wp_remote_get( $this->url );
        }
    }
}
