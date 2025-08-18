<?php
namespace WeDevs\HelpScout;

use WeDevs\ERP\Integration;

/**
 * Settings class
 *
 * @since 1.0.0
 *
 * @package WPERP|HelpScout
 */
class Settings extends Integration {

    /**
     * Constructor function
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->id          = 'helpscout';
        $this->title       = __( 'Help Scout', 'erp-pro' );
        $this->description = __( 'Help Scout Add-on for WP-ERP.', 'erp-pro' );

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
     * Initializes settings page for Help Scout
     *
     * @return void
     */
    public function init_settings() {
        $this->form_fields = [
            [
                'title' => __( 'App ID', 'erp-pro' ),
                'type'  => 'text',
                'id'    => 'helpscout_app_id',
                'custom_attributes' => [
                    'disable' => 'disable'
                ],
            ],
            [
                'title' => __( 'App Secret', 'erp-pro' ),
                'type'  => 'text',
                'id'    => 'helpscout_app_secret',
            ],
            [
                'title' => __( 'Callback URI', 'erp-pro' ),
                'type'  => 'text',
                'id'    => 'helpscout_callback_uri',
                'value' => site_url( '/erp-helpscout/api', 'http' )
            ],
            [
                'type' => 'sectionend',
                'id'   => 'script_styling_options'
            ],
        ];

        return $this->form_fields;
    }
}
