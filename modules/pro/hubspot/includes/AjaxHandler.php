<?php
namespace WeDevs\ERP\Hubspot;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax Class
 *
 * @package WP-ERP
 * @subpackage Hubspot
 */
class AjaxHandler {

    use Ajax;
    use Hooker;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_hubspot_sync', 'erp_hubspot_sync' );
        $this->action( 'wp_ajax_erp_hubspot_refresh_email_lists', 'erp_hubspot_refresh_email_lists' );
    }

    /**
     * Synchronize contacts with hubspot.
     *
     * @return void
     */
    public function erp_hubspot_sync() {
        global $wpdb;

        $this->verify_nonce( 'erp-hubspot-sync-nonce' );

        $hubspot_api_key = erp_hubspot_get_api_key();

        $hubspot = new Hubspot( $hubspot_api_key );

        $group_id      = intval( $_POST['group_id'] );
        $contact_owner = intval( $_POST['contact_owner'] );
        $hubspot_list  = sanitize_text_field( wp_unslash( $_POST['hubspot_list'] ) );
        $sync_type     = sanitize_text_field( wp_unslash( $_POST['sync_type'] ) );
        $life_stage    = sanitize_text_field( wp_unslash( $_POST['life_stage'] ) );

        $limit = 50; // Limit to sync per request

        $attempt = get_option( 'erp_hubspot_sync_attempt', 1 );
        update_option( 'erp_hubspot_sync_attempt', $attempt + 1 );
        $vid_offset = get_option( 'erp_hubspot_vid_offset', null );

        $offset = ( $attempt - 1 ) * $limit;

        if ( $sync_type == 'contacts_to_hubspot' ) {
            if ( ! empty( $group_id ) ) {
                $contact_contact_group = erp_crm_get_subscriber_contact( ['number' => $limit, 'group_id' => $group_id, 'offset' => $offset] );
                $total_items = erp_crm_get_subscriber_contact( ['group_id' => $group_id, 'count' => true] );

                $contact_ids = [];
                foreach ( $contact_contact_group as $item ) {
                    $contact_ids[] = $item->user_id;
                }

                $contacts = erp_get_people_by( 'id', $contact_ids );
            } else {
                $contacts = erp_get_peoples( ['type' => 'contact', 'number' => $limit, 'offset' => $offset] );

                $total_items = erp_get_peoples_count( 'contact' );
            }

            if ( $contacts ) {
                $data = [];
                foreach ( $contacts as $contact ) {
                    $data[] = [
                        'email' => $contact->email,
                        'properties' => [
                            [
                                'property' => 'firstname',
                                'value'    => $contact->first_name,
                            ],
                            [
                                'property' => 'lastname',
                                'value'    => $contact->last_name
                            ],
                        ]
                    ];
                }

                $hubspot->bulk_subscribe_to_list( $hubspot_list, $data );
            }
        }

        if ( $sync_type == 'hubspot_to_contacts' ) {
            $members = $hubspot->get_subscribed_members( $hubspot_list, $vid_offset );

            update_option( 'erp_hubspot_vid_offset', $members['vid-offset'] );
            $has_more = $members['has-more'];

            foreach ( $members['contacts'] as $member ) {

                // Process data after getting from hubspot
                $member_first_name  = ! empty( $member['properties']['firstname'] ) ? $member['properties']['firstname']['value']: '';
                $member_last_name   = ! empty( $member['properties']['lastname'] ) ? $member['properties']['lastname']['value']  : '';
                $hubspot_identities = ! empty( $member['identity-profiles'][0]['identities'] ) ? $member['identity-profiles'][0]['identities'] : [];
                $member_email       = '';

                foreach ( $hubspot_identities as $identity ) {
                    if ( $identity['type'] === 'EMAIL' ) {
                        $member_email = $identity['value'] ? $identity['value'] : '';
                    }
                }

                $data = [
                    'type'          => 'contact',
                    'first_name'    => $member_first_name,
                    'last_name'     => $member_last_name,
                    'email'         => $member_email,
                    'contact_owner' => (int) empty ( $contact_owner ) ? get_current_user_id() : $contact_owner,
                    'life_stage'    => $life_stage,
                ];

                $contact_id = erp_hubspot_create_contact( $data );

                if ( ! is_wp_error( $contact_id ) && ! empty( $group_id ) ) {
                    $contact_id = (int) $contact_id;
                    $group_id   = (int) $group_id;

                    // Check if contact is already subscribed or not
                    $exists = (int) $wpdb->get_var( $wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}erp_crm_contact_subscriber WHERE user_id = %d AND group_id = %d",
                        [ $contact_id, $group_id ]
                    ) );

                    if ( ! $exists ) {
                        erp_crm_create_new_contact_subscriber( ['user_id' => $contact_id, 'group_id' => $group_id] );
                    }
                }
            }
        }

        // re-calculate stats
        $synced = $attempt * $limit;
        if ( $sync_type == 'contacts_to_hubspot' ) {
            if ( $total_items <= $synced ) {
                $has_more = false;
            } else {
                $has_more = true;
            }
        }

        if ( ! $has_more ) {
            delete_option( 'erp_hubspot_vid_offset' );
            delete_option( 'erp_hubspot_sync_attempt' );
        }

        $this->send_success( [ 'synced' => $synced, 'has_more' => $has_more, 'message' => sprintf( __( 'Synced %d contacts.', 'erp-pro' ), $synced ) ] );
    }

    /**
     * Refresh email lists from server.
     *
     * @return void
     */
    public function erp_hubspot_refresh_email_lists() {
        $this->verify_nonce( 'erp-hubspot-refresh-lists-nonce' );

        $lists = erp_hubspot_refresh_email_lists();

        $options = get_option( 'erp_integration_settings_hubspot-integration', [] );
        $options['email_lists'] = $lists;
        update_option( 'erp_integration_settings_hubspot-integration', $options );

        $this->send_success( [ 'lists' => $lists ] );
    }
}
