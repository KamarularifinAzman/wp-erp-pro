<?php
namespace WeDevs\ERP\Zendesk;

use WeDevs\ERP\Framework\Models\People;
use WeDevs\ERP\CRM\Contact;

class User {

    /**
     * Constructor function
     */

    function __construct() {
        $this->get_contact();
    }

    /**
     * Get contact
     *
     * @return void
     */
    public function get_contact() {
        $customers = $this->get_zendesk_customer();

        if ( is_wp_error( $customers ) ) {
            return;
        }

        if ( ! $customers ) {
            return;
        }

        $zendesk_customer_ids = array();
        $erp_peoples = erp_get_peoples( array(
            'number'    =>  -1,
        ) );


        foreach( $erp_peoples as $people ) {
            $zendesk_customer_ids[] = erp_people_get_meta( $people->id, 'zendesk_user_id', true );
        }

        foreach ( $customers->users as $customer ) {
            $contact    = People::where( 'email', $customer->email )->first();
            if ( $contact ) {
                erp_people_update_meta( $contact->id, 'zendesk_user_id', $customer->id );
            }

            if ( in_array( $customer->id, $zendesk_customer_ids ) ) {
                continue;
            }

            if ( function_exists( 'erp_insert_people' ) ) {
                $args   =   array(
                    'first_name'    =>   $customer->name,
                    'email'         =>   $customer->email,
                    'phone'         =>   $customer->phone
                );

                $id = $customer->id;
                $contact = $this->insert_user( $args, $id );

                if ( ! $contact instanceof Contact ) {
                    $contact = new Contact( $contact->id );
                }
            }
        }
    }

    /**
     * Insert contact
     *
     * @param  array $args
     * @param  integer $id
     * @return void
     */
    public function insert_user( $args, $id ) {
        $contact_options = get_option( 'erp_settings_erp-crm_contacts' );
        $args   = wp_parse_args( $args, [
            'first_name'    => '',
            'last_name'     => '',
            'email'         => '',
            'phone'         => '',
            'country'       => '',
            'website'       => '',
            'type'          => 'contact',
            'life_stage'    =>  ! empty( $contact_options['life_stage'] ) ? $contact_options['life_stage'] : 'customer',
            'contact_owner' => erp_crm_get_default_contact_owner()
        ] );

        $people     = erp_insert_people( $args, true );

        if ( is_wp_error( $people ) ) {
            return fasle;
        }

        $contact        = new Contact( absint( $people->id ), 'contact' );
        $life_stage     = 'customer';
        $contact->update_meta( 'zendesk_user_id', $id );

        return $contact;
    }


    /**
     * Get zendesk customer
     *
     * @return array
     */
    public function get_zendesk_customer() {
        $subdomain          = erp_get_option( 'zendesk_subdomain', 'erp_integration_settings_zendesk' );
        $zendesk_email      = erp_get_option( 'zendesk_login_email', 'erp_integration_settings_zendesk' );
        $zendesk_password   = erp_get_option( 'zendesk_password', 'erp_integration_settings_zendesk' );
        $url                = 'https://' . $subdomain . '/api/v2/users.json?role[]=admin&role[]=end-user';

        if ( empty( $subdomain ) || empty( $zendesk_email ) || empty( $zendesk_password ) ) {
            return;
        }

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $zendesk_email . ':' . $zendesk_password )
            )
        );

        $response = wp_remote_get( $url, $args );
        $customers = array();

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response  = json_decode( $response['body'] );
        if ( ! empty( $response->users ) ) {
            $customers = $response;
        }
        return $customers;
    }
}
