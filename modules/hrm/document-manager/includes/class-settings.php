<?php

namespace WeDevs\DocumentManager;

use WeDevs\ERP\Integration;

/**
 * General class
 */
class Settings extends Integration {

    function __construct() {
        $this->id            = 'erp-dm';
        $this->title         = __( 'Dropbox', 'erp-pro' );
        $this->description   = __( 'Dropbox Add-on for WP-ERP.', 'wp-erp' );

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
     * Initializes settings page for dropbox
     *
     * @return void
     */
    public function init_settings() {
        $this->form_fields = array(
            array(
                'title'   => __( 'Dropbox Access Token', 'erp-pro' ),
                'id'      => 'dropbox_access_token', // key for getting output from option table
                'type'    => 'text',
                'desc'    => __( 'This key will be used to access specific dropbox account. <a target="_blank" href="https://www.dropbox.com/developers/apps">Click Here</a> to create dropbox api access token.', 'erp-pro' ),
            ),

            array(
                'title'   => __( 'Enable Dropbox', 'erp-pro' ),
                'id'      => 'enable_dropbox', // key for getting output from option table
                'type'    => 'radio',
                'desc'    => __( 'This check will be used to enable OR disable dropbox functionality', 'erp-pro' ),
                'default' => 'yes'
            ),

            array(
                'title'   => __( 'Enable Local directory', 'erp-pro' ),
                'id'      => 'enable_local_directory', // key for getting output from option table
                'type'    => 'radio',
                'desc'    => __( 'This check will be used to enable OR disable local directory functionality', 'erp-pro' ),
                'default' => 'yes'
            ),
        );

        return $this->form_fields;
    }
}

return new Settings();
