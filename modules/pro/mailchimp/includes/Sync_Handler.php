<?php

namespace WeDevs\ERP\Mailchimp;

/**
 * Class ERP_To_Mailchimp_Handler
 */
class Sync_Handler {

    /**
     * Map Group Id to Mailchimp List Id
     * @var array
     */
    private $group_id_to_lists_id;

    /**
     * @var Mailchimp
     */
    private $mailchimp;

    /**
     * Constructor to initialize actions
     */
    public function __construct() {
        $this->get_mapping();

        $this->mailchimp = false;
        if ( erp_mailchimp_get_api_key() ) {
            $this->mailchimp = new Mailchimp( erp_mailchimp_get_api_key() );
        }

        add_action( 'erp_crm_create_contact_subscriber', [ $this, 'contact_create' ] );
        add_action( 'erp_crm_edit_contact_subscriber', [ $this, 'contact_create' ] );
        add_action( 'erp_people_created', [ $this, 'contact_update' ], 10, 3 );
        add_action( 'erp_people_email_updated', [ $this, 'contact_update' ], 10, 4 );
        add_action( 'erp_crm_pre_unsubscribed_contact', [ $this, 'contact_unsubscribe' ], 10, 2 );
        add_action( 'erp_crm_delete_contact_subscriber', [ $this, 'contact_unsubscribe' ], 10, 2 );
    }

    /**
     * Prepare data to send contact to mailchimp
     *
     * @since 1.2.0
     *
     * @param $subscriber
     */
    public function contact_create( $subscriber ) {
        if ( defined( 'MAILCHIMP_TO_ERP_SYNCING' ) && MAILCHIMP_TO_ERP_SYNCING ) {
            return;
        }

        if ( ! isset( $this->group_id_to_lists_id[ $subscriber->group_id ] ) ) {
            return;
        }

        $contact = new \WeDevs\ERP\CRM\Contact( $subscriber->user_id );

        if ( ! in_array( 'contact', $contact->types, true ) ) {
            return;
        }

        if ( ! $this->mailchimp ) {
            return;
        }

        foreach ( $this->group_id_to_lists_id[ $subscriber->group_id ] as $email_list_id ) {
            if ( $this->mailchimp->member_exists( $email_list_id, $contact->get_email() ) ) {
                $this->mailchimp->update_contact(
                    $email_list_id,
                    $contact->get_email(),
                    $contact->get_first_name(),
                    $contact->get_last_name(),
                    $subscriber->status
                );
            } else {
                $this->mailchimp->subscribe_to_list(
                    $email_list_id,
                    $contact->get_email(),
                    $contact->get_first_name(),
                    $contact->get_last_name()
                );
            }
        }
    }

    /**
     * Prepare data to send updated contact to mailchimp
     *
     * @since 1.2.0
     *
     * @param $user_id
     * @param $people
     * @param $people_type
     */
    public function contact_update( $user_id, $people, $people_type, $old_email = '' ) {
        if ( defined( 'MAILCHIMP_TO_ERP_SYNCING' ) && MAILCHIMP_TO_ERP_SYNCING ) {
            return;
        }

        if ( 'contact' !== $people_type ) {
            return;
        }

        $groups = \WeDevs\ERP\CRM\Models\ContactSubscriber::select( 'group_id', 'status' )
            ->where( 'user_id', $user_id )
            ->get()
            ->toArray();

        if ( empty( $groups ) ) {
            return;
        }

        $groups = array_filter( $groups, function ( $group ) {
            return ! empty( $this->group_id_to_lists_id[ $group['group_id'] ] );
        } );

        if ( empty( $groups ) ) {
            return;
        }

        $contact = new \WeDevs\ERP\CRM\Contact( $user_id );

        foreach ( $groups as $group ) {
            if ( ! $this->mailchimp ) {
                continue;
            }

            foreach ( $this->group_id_to_lists_id[ $group['group_id'] ] as $email_list_id ) {
                $this->mailchimp->update_contact(
                    $email_list_id,
                    $contact->get_email(),
                    $contact->get_first_name(),
                    $contact->get_last_name(),
                    $group['status'],
                    $old_email
                );
            }
        }
    }

    /**
     * Send request to mailchimp to unsubscribe a contact
     *
     * @since 1.2.0
     *
     * @param $user_id
     * @param $group_id
     */
    public function contact_unsubscribe( $user_id, $group_id ) {
        if ( defined( 'MAILCHIMP_TO_ERP_SYNCING' ) && MAILCHIMP_TO_ERP_SYNCING ) {
            return;
        }

        if ( ! isset( $this->group_id_to_lists_id[ $group_id ] ) ) {
            return;
        }

        $contact = new \WeDevs\ERP\CRM\Contact( $user_id );

        if ( ! in_array( 'contact', $contact->types, true ) ) {
            return;
        }

        if ( ! $this->mailchimp ) {
            return;
        }

        foreach ( $this->group_id_to_lists_id[ $group_id ] as $email_list_id ) {
            $this->mailchimp->unsubscribe_from_list(
                $email_list_id,
                $contact->get_email(),
                $contact->get_first_name(),
                $contact->get_last_name()
            );
        }
    }

    /**
     * Create a map to access email list ids by the key of group id
     *
     * @since 1.2.0
     */
    private function get_mapping() {
        $settings = get_option( 'erp_integration_settings_mailchimp-integration' );

        if ( ! $settings || ! isset( $settings['sync_data'] ) ) {
            return;
        }

        $sync_data = $settings['sync_data']['group_to_email_lists'];

        foreach ( $sync_data as $group_id => $sync_datum ) {
            $this->group_id_to_lists_id[ $group_id ] = [];

            if ( 'false' === $sync_datum['auto_sync'] || empty( $sync_datum['auto_sync'] ) ) {
                continue;
            }

            foreach ( $sync_datum['email_lists'] as $email_list ) {
                if ( isset( $email_list['id'] ) ) {
                    $this->group_id_to_lists_id[$group_id][] = $email_list['id'];
                }
            }
        }
    }
}
