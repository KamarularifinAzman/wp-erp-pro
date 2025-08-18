<?php
namespace WeDevs\ERP\Zendesk;

use WeDevs\ERP\Integration;

/**
 * Settings Class
 */
class Settings extends Integration {

    /**
     * Constructor function
     */
    function __construct() {
        $this->id          = 'zendesk';
        $this->title       = __( 'Zendesk', 'erp-pro' );
        $this->description = __( 'Zendesk Add-on for WP-ERP.', 'erp-pro' );

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
                'title' => __( 'Zendesk subdomain', 'erp-pro' ),
                'type'  => 'text',
                'id'    => 'zendesk_subdomain',
                'desc'    => __( 'e.g. <strong>mysub.zendesk.com</strong>', 'erp' ),
            ],
            [
                'title' => __( 'Zendesk Email', 'erp-pro' ),
                'type'  => 'email',
                'id'    =>  'zendesk_login_email'
            ],
            [
                'title' => __( 'Zendesk Password', 'erp-pro' ),
                'type'  => 'password',
                'id'    =>  'zendesk_password'
            ],
        ];

        return $this->form_fields;
    }
}