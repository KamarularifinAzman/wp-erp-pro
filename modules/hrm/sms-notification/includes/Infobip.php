<?php
namespace WeDevs\ERP\SMS;

use infobip\api\client;
use infobip\api\model\sms\mt\send\textual\SMSTextualRequest;
use infobip\api\AbstractApiClient;
use infobip\api\model\sms\mt\send\SMSResponse;

/**
 * SMS Handler class for Infobip
 *
 * @since 1.0
 */
class Infobip implements GatewayInterface{

    /**
     * Username for Infobip Account
     *
     * @since 1.0
     */
    private $username;

    /**
     * Password for Infobip Account
     *
     * @since 1.0
     */
    private $password;

    /**
     * Sender for Infobip Account
     *
     * @since 1.0
     */
    private $sender;

    /**
     * Client
     *
     * @since 1.0
     */
    private $client;

    /**
     * Requestbody
     *
     * @since 1.0
     */
    private $requestBody;

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
        $this->username = erp_get_option( 'erp_sms_infobip_username', 'erp_integration_settings_erp-sms' );
        $this->password = erp_get_option( 'erp_sms_infobip_password', 'erp_integration_settings_erp-sms' );
        $this->sender   = erp_get_option( 'erp_sms_infobip_sender', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {
        $this->client = new \infobip\api\client\SendSingleTextualSms(new \infobip\api\configuration\BasicAuthConfiguration( $this->username, $this->password ) );
        $this->requestBody = new \infobip\api\model\sms\mt\send\textual\SMSTextualRequest();
        $this->requestBody->setFrom( $this->sender );
    }

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no_all, $message ) {
        $this->requestBody->setText( $message );

        foreach ( $cell_no_all as $cell_no_single ) {
            $this->requestBody->setTo( $cell_no_single );
            $response = $this->client->execute( $this->requestBody );
        }
    }
}
