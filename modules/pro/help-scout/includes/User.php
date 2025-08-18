<?php
namespace WeDevs\HelpScout;

use WeDevs\ERP\Framework\Models\People;
use WeDevs\ERP\CRM\Contact;

class User {

    /**
     * @since 1.0.0
     * @var Contact
     */
    protected $contact;

    /**
     * User constructor.
     *
     * @param array $args
     */
    public function __construct() {
        $this->get_contact();
    }

    /**
     * Returns Contact object
     *
     * @since 1.0.0
     * @return Contact
     */
    public function contact(){
        return $this->contact;
    }

    /**
     * Get the contact
     *
     * @since 1.0.0
     * @return Contact
     */
    protected function get_contact() {
        $customers = $this->get_helpscout_customer();
        if ( ! $customers ) {
            return;
        }

        foreach ( $customers as $customer ) {
            $email = get_helpscout_customer_email( $customer->_links->emails->href );
            $contact = People::where( 'email', $email )->first();

            if ( ! $contact && function_exists( 'erp_insert_people' ) ) {
                $args  = [
                    'first_name' => $customer->firstName,
                    'last_name'  => $customer->lastName,
                    'email'      => $email
                ];

                $contact = $this->insert_user( $args );
            }

            if ( ! $contact instanceof Contact ) {
                $contact = new Contact($contact->id );
            }
        }
    }


    /**
     * Insert contact if its not there already
     *
     * @since 1.0.0
     *
     * @param array $args
     *
     * @return bool|Contact
     */
    public function insert_user( array $args ) {
        $args = wp_parse_args( $args,
            [
                'first_name' => '',
                'last_name'  => '',
                'email'      => '',
                'company'    => '',
                'phone'      => '',
                'country'    => '',
                'website'    => '',
                'type'       => 'contact',
                'life_stage' => get_customer_life_stage(),
                'contact_owner' => erp_crm_get_default_contact_owner(),
            ] );

        $people = erp_insert_people( $args );
        if ( is_wp_error( $people ) ) {
            return false;
        }

        $contact_group = helpscout_get_option( 'contact_group' );
        if ( $contact_group ) {
            erp_crm_edit_contact_subscriber( $contact_group, $people );
        }

        $contact = new Contact( absint( $people ), 'contact' );
        $contact->update_meta( 'source', 'optin_form' );

        return $contact;
    }

    /**
     *  Get helpscout customers
     *
     * @return array helpscout customers
     */
    public function  get_helpscout_customer() {
        $app_id         = helpscout_get_option( 'helpscout_app_id' );
        $app_secret     = helpscout_get_option( 'helpscout_app_secret' );
        $mail_box_ids    = helpscout_get_option( 'helpscout_mailbox' );

        if ( empty( $app_id ) || empty( $app_secret ) || empty( $mail_box_ids ) ) {
            return;
        }
        $access_token = get_helpscout_access_token();
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        );

        $customers = array();
        foreach ( $mail_box_ids as $mail_box_id ) {
            $request    = wp_remote_get( 'https://api.helpscout.net/v2/customers?mailbox='.$mail_box_id, $args );
            $body       = wp_remote_retrieve_body( $request );
            $response   = json_decode($body);
            $customers  = array_merge( $customers, $response->_embedded->customers );
        }


        if ( is_wp_error( $customers ) ) {
            wp_send_json_error(['msg' => __('Something went wrong please try again', 'erp-pro')]);
        }
        return $customers;
    }
}
