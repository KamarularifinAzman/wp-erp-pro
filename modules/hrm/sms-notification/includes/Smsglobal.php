<?php
namespace WeDevs\ERP\SMS;
/**
 * SMS Handler class for Clickatell
 *
 * @since 1.0
 */
class Smsglobal implements GatewayInterface{

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
     * Sender for Clickatel Account
     *
     * @since 1.0
     */
    private $from;

    /**
     * URL
     *
     * @since 1.0
     */
    private $url;

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
        $this->username = erp_get_option( 'erp_sms_smsglobal_username', 'erp_integration_settings_erp-sms' );
        $this->password = erp_get_option( 'erp_sms_smsglobal_password', 'erp_integration_settings_erp-sms' );
        $this->from     = erp_get_option( 'erp_sms_smsglobal_from', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {
        $this->url = sprintf(
            'http://www.smsglobal.com.au/http-api.php?action=sendsms&user=%s&password=%s&from=%s',
            rawurlencode( $this->username ),
            rawurlencode( $this->password ),
            rawurlencode( $this->from )
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
