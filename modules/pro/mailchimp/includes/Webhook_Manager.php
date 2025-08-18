<?php

namespace WeDevs\ERP\Mailchimp;

/**
 * Class Webhook_Manager
 */
class Webhook_Manager {
    private static $webhook_path = '/erp-crm-mailchimp-webhook';

    /**
     * Constructor
     */
    public function __construct() {
        if ( $this->is_mailchimp_webhook() && ! is_admin() ) {
            add_action( 'plugins_loaded', [ $this, 'handle_mailchimp_event' ] );
        }
    }

    /**
     * Handle mailchimp event that is triggering this webhook
     *
     * @since 1.2.0
     */
    public function handle_mailchimp_event() {
        if ( ! $this->verify_request() ) {
            $this->send_success();
        }

        define( 'MAILCHIMP_TO_ERP_SYNCING', true ); //we will use this constant later to prevent call via api, which will prevent the callback cycle

        $event_type = empty( $_POST['type'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['type'] ) );

        $email   = ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['EMAIL'] ) ) ? '' : sanitize_email( wp_unslash( $_POST['data']['merges']['EMAIL'] ) );
        $list_id = ( empty( $_POST['data'] ) || empty( $_POST['data']['list_id'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['list_id'] ) );

        switch ( $event_type ) {
            case 'subscribe':
                $sync_data     = erp_mailchimp_get_sync_data_for_import( $list_id );
                $contact_owner = $sync_data['contact_owner']['id'];
                $life_stage    = $sync_data['life_stage']['id'];

                $data = $this->mailchimp_to_erp_contact_args();

                $data['life_stage']    = $life_stage;
                $data['contact_owner'] = $contact_owner;

                $contact_id = erp_mailchimp_create_contact( $data );

                if ( is_wp_error( $contact_id ) ) {
                    $this->send_success();
                }

                if ( ! is_wp_error( $contact_id ) ) {
                    foreach ( $sync_data['groups'] as $contact_group ) {
                        if ( \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $contact_id )->where( 'group_id', $contact_group['id'] )->exists() ) {
                            erp_crm_contact_resubscribe_subscriber( $contact_id, $contact_group['id'] );
                        } else {
                            erp_crm_create_new_contact_subscriber([
                                'user_id' => (int) $contact_id,
                                'group_id' => (int) $contact_group['id'],
                            ]);
                        }
                    }
                }
                break;

            case 'unsubscribe':
                $action = ( empty( $_POST['data'] ) || empty( $_POST['data']['action'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['action'] ) );

                if ( 'delete' === $action ) {
                    $contact = erp_get_people_by( 'email', $email );

                    if ( ! $contact || ( ! is_array( $contact->types ) && 'contact' !== $contact->types ) || ( is_array( $contact->types ) && ! in_array( 'contact', $contact->types, true ) ) ) {
                        $this->send_success();
                    }

                    $relations = erp_crm_check_company_contact_relations( $contact->id, 'contact' );

                    if ( 0 !== (int) $relations ) { // has company relation, can't delete
                        $this->send_success();
                    }

                    $sync_data = erp_mailchimp_get_sync_data_for_import( $list_id );

                    foreach ( $sync_data['groups'] as $group ) {
                        erp_crm_contact_subscriber_delete( $contact->id, $group['id'] );
                    }

                    erp_delete_people( [
                        'id' => $contact->id,
                        'hard' => true,
                        'type' => 'contact',
                    ] );
                } else {
                    //unsubscribe ,archive
                    $contact = erp_get_people_by( 'email', $email );

                    if ( ! $contact ) {
                        $this->send_success();
                    }

                    $sync_data = erp_mailchimp_get_sync_data_for_import( $list_id );

                    if ( 'unsub' === $action ) {
                        foreach ( $sync_data['groups'] as $group ) {
                            erp_crm_contact_unsubscribe_subscriber( $contact->id, $group['id'] );
                        }
                    } else { //archive action
                        foreach ( $sync_data['groups'] as $group ) {
                            erp_crm_contact_subscriber_delete( $contact->id, $group['id'] );
                        }
                    }
                }
                break;

            case 'profile':
                $contact = erp_get_people_by( 'email', $email );

                if ( ! $contact ) {
                    $this->send_success();
                }

                $data = $this->mailchimp_to_erp_contact_args();

                $data['contact_owner'] = $contact->contact_owner;
                $data['life_stage']    = $contact->life_stage;

                erp_insert_people( $data );
                break;

            case 'upemail':
                $old_email = ( empty( $_POST['data'] ) || empty( $_POST['data']['old_email'] ) ) ? '' : sanitize_email( wp_unslash( $_POST['data']['old_email'] ) );
                $new_email = ( empty( $_POST['data'] ) || empty( $_POST['data']['new_email'] ) ) ? '' : sanitize_email( wp_unslash( $_POST['data']['new_email'] ) );

                $contact = erp_get_people_by( 'email', $old_email );

                if ( ! $contact ) {
                    $this->send_success();
                }

                if ( ! is_email( $new_email ) ) {
                    $this->send_success();
                }

                erp_insert_people( [
                    'id'            => $contact->id,
                    'email'         => $new_email,
                    'contact_owner' => $contact->contact_owner,
                    'life_stage'    => $contact->life_stage,
                    'type'          => 'contact',
                ] );
                break;
        }

        $this->send_success();
    }

    /**
     * Check if the request from A mailchimp webhook
     *
     * @since 1.2.0
     *
     * @return bool
     */
    private function is_mailchimp_webhook() {
        if (
            'GET' === $_SERVER['REQUEST_METHOD']
            && 'mailchimp.com webhook validator' === strtolower( $_SERVER['HTTP_USER_AGENT'] )
            && false !== stripos( $_SERVER['REQUEST_URI'], self::$webhook_path )
        ) {
            $this->send_success();
        }

        return false !== stripos( $_SERVER['REQUEST_URI'], self::$webhook_path )
            && 'mailchimp' === strtolower( $_SERVER['HTTP_USER_AGENT'] );
    }

    /**
     * Prepare arguments for contact creation or update
     *
     * @since 1.2.0
     *
     * @return array
     */
    private function mailchimp_to_erp_contact_args() {
        $email = ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['EMAIL'] ) ) ? '' : sanitize_email( wp_unslash( $_POST['data']['merges']['EMAIL'] ) );

        return [
            'first_name'  => sanitize_text_field( ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['FNAME'] ) ) ? explode( '@', $email )[0] : wp_unslash( $_POST['data']['merges']['FNAME'] ) ),
            'last_name'   => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['LNAME'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['LNAME'] ) ),
            'email'       => $email,
            'phone'       => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['PHONE'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['PHONE'] ) ),
            'street_1'    => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['ADDRESS'] ) || empty( $_POST['data']['merges']['ADDRESS']['addr1'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['ADDRESS']['addr1'] ) ),
            'street_2'    => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['ADDRESS'] ) || empty( $_POST['data']['merges']['ADDRESS']['addr2'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['ADDRESS']['addr2'] ) ),
            'city'        => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['ADDRESS'] ) || empty( $_POST['data']['merges']['ADDRESS']['city'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['ADDRESS']['city'] ) ),
            'state'       => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['ADDRESS'] ) || empty( $_POST['data']['merges']['ADDRESS']['state'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['ADDRESS']['state'] ) ),
            'postal_code' => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['ADDRESS'] ) || empty( $_POST['data']['merges']['ADDRESS']['zip'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['ADDRESS']['zip'] ) ),
            'country'     => ( empty( $_POST['data'] ) || empty( $_POST['data']['merges'] ) || empty( $_POST['data']['merges']['ADDRESS'] ) || empty( $_POST['data']['merges']['ADDRESS']['country'] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST['data']['merges']['ADDRESS']['country'] ) ),
            'type'        => 'contact',
        ];
    }

    /**
     * Sending and exiting the system to prevent 404 error by WordPress
     * As a result Mailchimp will retry at a later time.
     *
     * @since 1.2.0
     */
    private function send_success() {
        http_response_code( 200 );
        exit( 0 );
    }

    /**
     * Check the token of the request
     *
     * @since 1.2.0
     *
     * @return false|int
     */
    private function verify_request() {
        if ( empty( $_GET['token'] ) || empty( $_POST['data']['list_id'] ) ) {
            return false;
        }

        $token = sanitize_key( wp_unslash( $_GET['token'] ) );

        $webhook_manager = new Webhook_Controller( sanitize_text_field( wp_unslash( $_POST['data']['list_id'] ) ), erp_mailchimp_get_api_key() );

        $valid = $webhook_manager->validate_token( $token );

        if ( $webhook_manager->should_update() ) {
            $webhook_manager->update_webhook();
        }

        return $valid;
    }
}
