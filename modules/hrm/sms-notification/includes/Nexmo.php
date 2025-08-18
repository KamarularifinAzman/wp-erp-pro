<?php
namespace WeDevs\ERP\SMS;
/**
 * SMS Handler class for Nexmo
 *
 * @since 1.0
 */
class Nexmo implements GatewayInterface{

    /**
     * Username for Nexmo Account
     *
     * @since 1.0
     */
    private $apikey;

    /**
     * Password for Nexmo Account
     *
     * @since 1.0
     */
    private $apisecret;

    /**
     * Sender for Nexmo Account
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
     * Arguments
     *
     * @since 1.0
     */
    private $args;

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
        $this->apikey = erp_get_option( 'erp_sms_nexmo_apikey', 'erp_integration_settings_erp-sms' );
        $this->apisecret = erp_get_option( 'erp_sms_nexmo_apisecret', 'erp_integration_settings_erp-sms' );
        $this->from = erp_get_option( 'erp_sms_nexmo_sender_id', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {
        $this->url = 'http://rest.nexmo.com/sms/json';

        $this->args  = [
            'body' => [
                'api_key'    => $this->apikey,
                'api_secret' => $this->apisecret,
            ]
        ];
    }

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no_all, $message ) {
        if ( empty( $this->apikey ) || empty( $this->apisecret ) ) {
            return;
        }

        $from = ( $this->from ) ? $this->from : 'NEXMO';

        foreach ( $cell_no_all as $cell_no_single ) {
            $content = 'api_key=' . $this->apikey .
                '&api_secret=' . $this->apisecret .
                '&from=' . $from .
                '&to=' . $cell_no_single .
                '&text=' . urlencode( $message );

            $nexmo_response = file_get_contents( 'https://rest.nexmo.com/sms/json?' . $content );
        }
    }
}
