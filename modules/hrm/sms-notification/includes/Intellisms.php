<?php
namespace WeDevs\ERP\SMS;

/**
 * SMS Handler class for Infobip
 *
 * @since 1.0
 */
class Intellisms implements GatewayInterface{

    /**
     * Username for Intellisms Account
     *
     * @since 1.0
     */
    private $username;

    /**
     * Password for Intellisms Account
     *
     * @since 1.0
     */
    private $password;

    /**
     * SMS Sender
     *
     * @since 1.0
     */
    private $sender;

    /**
     * IntelliSMS class object
     *
     * @since 1.0
     */
    private $objIntelliSMS;

    /**
     * Intellisms Class constructor
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
        $this->username = erp_get_option( 'erp_sms_intellisms_username', 'erp_integration_settings_erp-sms' );
        $this->password = erp_get_option( 'erp_sms_intellisms_password', 'erp_integration_settings_erp-sms' );
        $this->sender   = erp_get_option( 'erp_sms_intellisms_sender', 'erp_integration_settings_erp-sms' );
    }

    /**
     * Prepare SMS
     *
     * @since 1.0
     */
    public function prepare() {

        include  WPERP_SMS_LIB . '/intelliSMS/SendScripts/intelliSMS.php';

        $this->objIntelliSMS = new IntelliSMS();
        $this->objIntelliSMS->Username = $this->username;
        $this->objIntelliSMS->Password = $this->password;
    }

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no_all, $message ) {
        $numbers = implode( ',', $cell_no_all );
        $this->objIntelliSMS->SendMessage( $numbers, $message, $this->sender );
    }
}
