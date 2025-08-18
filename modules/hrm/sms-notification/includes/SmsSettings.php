<?php
namespace WeDevs\ERP\SMS;

use WeDevs\ERP\Integration;

/**
 * General class
 */
class SmsSettings extends Integration {

    /**
     * Class constructor
     */
    public function __construct() {
        $this->id            = 'erp-sms';
        $this->title         = __( 'SMS', 'erp-pro' );
        $this->description   = __( 'SMS Add-on for WP-ERP.', 'wp-erp' );

        $this->sections      = [
            'twilio'       => __( 'Twilio', 'erp-sms' ),
            'clickatell'   => __( 'Clickatell', 'erp-sms' ),
            'smsglobal'    => __( 'SMSGlobal', 'erp-sms' ),
            'nexmo'        => __( 'Nexmo', 'erp-sms' ),
            'hoiio'        => __( 'Hoiio', 'erp-sms' ),
            'intellisms'   => __( 'Intellisms', 'erp-sms' ),
            'infobip'      => __( 'Infobip', 'erp-sms' ),
        ];

        $this->extra = [ 'selected_gateway' => erp_get_option( 'erp_integration_settings_erp-sms', 'erp_sms_selected_gateway', 'twilio' ) ];

        $this->init_settings();
        parent::__construct();
    }

     /**
     * Get the title of this setting.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Get the description of this setting.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Initializes settings page for sms
     *
     * @return void
     */
    public function init_settings() {
        $fields         = $this->get_fields();
        $this->sub_tab  = array_keys( $fields );
        $sub_section    = $this->sub_tab[0];

        if ( ! array_key_exists( $sub_section, $fields ) ) {
            $this->form_fields = [];
        }

        $this->form_fields = $fields[ $sub_section ];
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_fields() {
        $options = [
            'twilio'       => __( 'Twilio', 'erp-sms' ),
            'clickatell'   => __( 'Clickatell', 'erp-sms' ),
            'smsglobal'    => __( 'SMSGlobal', 'erp-sms' ),
            'nexmo'        => __( 'Nexmo', 'erp-sms' ),
            'hoiio'        => __( 'Hoiio', 'erp-sms' ),
            'intellisms'   => __( 'Intellisms', 'erp-sms' ),
            'infobip'      => __( 'Infobip', 'erp-sms' ),
        ];

        $fields['gateway']['twilio'] = [
            [
                'title' => __( 'Twilio', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'twilio',
            ],
            [
                'title' => __( 'Number From', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_twilio_number_from'
            ],
            [
                'title' => __( 'Account SID', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_twilio_account_sid'
            ],
            [
                'title' => __( 'Auth Token', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_twilio_auth_token'
            ],
        ];

        $fields['gateway']['clickatell'] = [
            [
                'title' => __( '', 'erp-sms' ),
                'type' => 'title',
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'clickatell',
            ],
            [
                'title' => __( 'Clickatell', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'title' => __( 'Username', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_clickatell_username'
            ],
            [
                'title' => __( 'Password', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_clickatell_password'
            ],
            [
                'title' => __( 'API ID', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_clickatell_api_id'
            ]
        ];

        $fields['gateway']['smsglobal'] = [
            [
                'title' => __( 'SMSGlobal', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'smsglobal',
            ],
            [
                'title' => __( 'Username', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_smsglobal_username'
            ],
            [
                'title' => __( 'Password', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_smsglobal_password'
            ],
            [
                'title' => __( 'From Number', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_smsglobal_from'
            ],
        ];

        $fields['gateway']['nexmo'] = [
            [
                'title' => __( 'Nexmo', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'nexmo',
            ],
            [
                'title' => __( 'API Key', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_nexmo_apikey'
            ],
            [
                'title' => __( 'API Secret', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_nexmo_apisecret'
            ],
            [
                'title' => __( 'Sender ID', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_nexmo_sender_id'
            ]
        ];

        $fields['gateway']['hoiio'] = [
            [
                'title' => __( 'Hoiio', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'hoiio',
            ],
            [
                'title' => __( 'App ID', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_hoiio_app_id'
            ],
            [
                'title' => __( 'Access Token', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_hoiio_access_token'
            ],
        ];

        $fields['gateway']['intellisms'] = [
            [
                'title' => __( 'Intellisms', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'intellisms',
            ],
            [
                'title' => __( 'Username', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_intellisms_username'
            ],
            [
                'title' => __( 'Password', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_intellisms_password'
            ],
            [
                'title' => __( 'Sender', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_intellisms_sender'
            ],
        ];

        $fields['gateway']['infobip'] = [
            [
                'title' => __( 'Infobip', 'erp-sms' ),
                'type' => 'title'
            ],
            [
                'type'    => 'hidden-fixed',
                'id'      => 'erp_sms_selected_gateway',
                'value'   => 'infobip',
            ],
            [
                'title' => __( 'Username', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_infobip_username'
            ],
            [
                'title' => __( 'Password', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_infobip_password'
            ],
            [
                'title' => __( 'Sender', 'erp-sms' ),
                'type'  => 'text',
                'id'    => 'erp_sms_infobip_sender'
            ],
        ];

        return $fields;
    }
}
