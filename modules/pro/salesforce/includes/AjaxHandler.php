<?php
namespace WeDevs\ERP\Salesforce;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax Class
 *
 * @package WP-ERP
 * @subpackage Salesforce
 */
class AjaxHandler {

    use Ajax;
    use Hooker;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_salesforce_sync', 'erp_salesforce_sync' );
        $this->action( 'wp_ajax_erp_salesforce_refresh_contact_lists', 'erp_salesforce_refresh_contact_lists' );
    }

    /**
     * Synchronize contacts with Salesforce.
     *
     * @return void
     */
    public function erp_salesforce_sync() {
        $this->verify_nonce( 'erp-salesforce-sync-nonce' );

        $instance_url    = erp_salesforce_get_instance_url();
        $access_token    = erp_salesforce_get_access_token();
        $refresh_token   = erp_salesforce_get_refresh_token();

        $salesforce      = new Salesforce( $instance_url, $access_token, $refresh_token );

        $group_id        = intval( wp_unslash( $_POST['group_id'] ) );
        $sync_type       = sanitize_text_field( wp_unslash( $_POST['sync_type'] ) );
        $contact_owner   = intval( wp_unslash( $_POST['contact_owner'] ) );
        $life_stage      = sanitize_text_field( wp_unslash( $_POST['life_stage'] ) );
        $salesforce_list = sanitize_text_field( wp_unslash( $_POST['salesforce_list'] ) );

        $limit = 50; // Limit to sync per request

        $attempt = get_option( 'erp_salesforce_sync_attempt', 1 );
        update_option( 'erp_salesforce_sync_attempt', $attempt + 1 );

        $offset = ( $attempt - 1 ) * $limit;

        if ( $sync_type == 'contacts_to_salesforce' ) {
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
                $x = 0;
                foreach ( $contacts as $contact ) {
                    $data[$x]['email'] = $contact->email;
                    $data[$x]['firstname'] = $contact->first_name;
                    $data[$x]['lastname'] = $contact->last_name;

                    $x++;
                }

                $response = $salesforce->create_bulk_contacts( $data );
            }
        }

        if ( $sync_type == 'salesforce_to_contacts' ) {
            // $total_items = $salesforce->count_contacts()['records'][0]['expr0'];
            $total_items = $salesforce->get_contacts( $salesforce_list, 10000 )['size'];
            $contacts    = $salesforce->get_contacts( $salesforce_list, $limit, $offset );

            foreach ( $contacts['records'] as $contact ) {

                foreach ( $contact['columns'] as $column ) {
                    switch ( $column['fieldNameOrPath'] ) {
                        case 'Name':
                            $nameParts = explode( ' ', $column['value'] );

                            break;
                        case 'Email':
                            $email = $column['value'];

                            break;
                    }
                }

                $data = [
                    'type'          => 'contact',
                    'first_name'    => $nameParts[0],
                    'last_name'     => $nameParts[1],
                    'email'         => $email,
                    'contact_owner' => $contact_owner,
                    'life_stage'    => $life_stage,
                ];

                $contact_id = erp_salesforce_create_contact( $data );

                if ( ! empty( $group_id ) && ! is_wp_error( $contact_id ) ) {
                    erp_crm_create_new_contact_subscriber( ['user_id' => (int) $contact_id, 'group_id' => (int) $group_id] );
                }
            }
        }

        if ( ! empty( $response[0]['errorCode'] ) ) {
            $this->send_error( [ 'message' => $response[0]['message'] ] );
        }

        // re-calculate stats
        if ( $total_items <= ( $attempt * $limit ) ) {
            $left = 0;
        } else {
            $left = $total_items - ( $attempt * $limit );
        }

        if ( $left === 0 ) {
            delete_option( 'erp_salesforce_sync_attempt' );
        }

        $this->send_success( [ 'left' => $left, 'message' => sprintf( __( '%d left to sync.', 'erp-pro' ), $left ) ] );
    }

    /**
     * Refresh contact lists from server.
     *
     * @return void
     */
    public function erp_salesforce_refresh_contact_lists() {
        $this->verify_nonce( 'erp-salesforce-refresh-lists-nonce' );

        $lists = erp_salesforce_refresh_contact_lists();
        $options['contact_lists'] = $lists;
        erp_salesforce_update_options( $options );

        $this->send_success( [ 'lists' => $lists ] );
    }
}
