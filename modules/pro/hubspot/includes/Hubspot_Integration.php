<?php
namespace WeDevs\ERP\Hubspot;

use WeDevs\ERP\Integration;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Hubspot Integration
 */
class Hubspot_Integration extends Integration {

    use Hooker;

    /**
     * Class constructor.
     */
    function __construct() {
        $this->id          = 'hubspot-integration';
        $this->title       = __( 'HubSpot', 'wp-erp' );
        $this->description = __( 'Hubspot Add-on for WP-ERP.', 'wp-erp' );
        $this->form_fields = $this->form_fields();
        
        $this->filter( $this->get_option_id() . '_filter', 'update_option_filter' );

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
     * Get the fields of this setting.
     *
     * @return array
     */
    public function form_fields() {
        $desc_text = '<a target="_blank" href="https://developers.hubspot.com/docs/api/migrate-an-api-key-integration-to-a-private-app#create-a-new-private-app">' . __( 'I need help getting my access token!', 'erp-pro' ) . '</a>';
        
        if ( ! empty( erp_hubspot_get_api_key() ) ) {
            $url       = admin_url( 'admin.php?page=erp-crm&section=integration&sub-section=hubspot&action=disconnect' );
            
            $desc_text = '<a class="wperp-btn btn--link" href="' . $url . '">'. __( 'Disconnect', 'erp-pro' ) . '</a>';
        }

        $fields = [
            [
                'title'             => __( 'Access Token', 'erp-pro' ),
                'id'                => 'api_key',
                'type'              => 'text',
                'custom_attributes' => ['placeholder' => __( 'Your Hubspot Access Token', 'erp-pro' ) ], //todo: this line is displaying formatting error on frontend
                'desc'              => $desc_text,
                'placeholder'       => __( 'Your Hubspot Access Token', 'erp-pro' ),
            ],
        ];

        return $fields;
    }

    /**
     * Add filter of this setting.
     *
     * @param  array $update_options
     *
     * @return array|WP_Error
     */
    public function update_option_filter( $update_options ) {
        if ( isset ( $update_options['api_key'] ) ) {
            $hubspot = new Hubspot( $update_options['api_key'] );
            
            if ( $hubspot->is_connected() ) {
                $update_options['email_lists'] = erp_hubspot_refresh_email_lists( $update_options['api_key'] );
            } else {
                return new \WP_Error( 'invalid-api-key', __( 'Invalid API key. Enter correct one!', 'erp' ) );
            }
        }

        return $update_options;
    }
}
