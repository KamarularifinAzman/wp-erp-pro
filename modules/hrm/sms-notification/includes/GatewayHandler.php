<?php
namespace WeDevs\ERP\SMS;

/**
 * SMS Gateway Controller Class
 *
 * @since 1.0
 */
class GatewayHandler {

    /**
     * Active Gateway
     *
     * @var string
     *
     * @since 1.0
     */
    protected $gateway;

    /**
     * Constructor for GatewayHandler class
     *
     * @since 1.0
     */
    public function __construct() {
        $selected_gateway = erp_get_option( 'erp_sms_selected_gateway', 'erp_integration_settings_erp-sms' );

        switch ( $selected_gateway ) {

            case 'clickatell':
                $this->gateway = new Clickatell();
            break;

            case 'twilio':
                $this->gateway = new Twilio();
            break;

            case 'smsglobal':
                $this->gateway = new Smsglobal();
            break;

            case 'nexmo':
                $this->gateway = new Nexmo();
            break;

            case 'hoiio':
                $this->gateway = new Hoiio();
            break;

            case 'intellisms':
                $this->gateway = new Intellisms();
            break;

            case 'infobip':
                $this->gateway = new Infobip();
            break;

            default:
                wp_send_json_error( esc_html__( 'Please select a gateway to send sms.', 'erp-pro' ) );
            break;
        }
    }

    /**
     * Ready to send SMS
     *
     * @since 1.0
     */
    public function send_sms( $cell_no_all, $message ) {
        $this->gateway->send( $cell_no_all, $message );
    }
}
