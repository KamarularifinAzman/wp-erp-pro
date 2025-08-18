<?php
namespace WeDevs\ERP\Mailchimp;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax Class
 *
 * @package WP-ERP
 * @subpackage Mailchimp
 */
class AjaxHandler {

    use Ajax;
    use Hooker;

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_mailchimp_sync', 'erp_mailchimp_sync' );
        $this->action( 'wp_ajax_erp_mailchimp_refresh_email_lists', 'erp_mailchimp_refresh_email_lists' );
        $this->action( 'wp_ajax_erp_mailchimp_new_api_key_email_lists', 'get_email_lists_for_new_api_key' );
    }

    /**
     * Synchronize contacts with mailchimp.
     *
     * @return void
     */
    public function erp_mailchimp_sync() {
        $this->verify_nonce( 'erp-mailchimp-sync-nonce' );

        $mailchimp_api_key = erp_mailchimp_get_api_key();

        $mailchimp = new Mailchimp( $mailchimp_api_key );

        $group_id       = $_POST['group_id'];
        $mailchimp_list = $_POST['mailchimp_list'];
        $sync_type      = $_POST['sync_type'];
        $contact_owner  = $_POST['contact_owner'];
        $life_stage     = $_POST['life_stage'];

        $limit = 50; // Limit to sync per request

        $attempt = get_option( 'erp_mailchimp_sync_attempt', 1 );
        update_option( 'erp_mailchimp_sync_attempt', $attempt + 1 );

        $offset = ( $attempt - 1 ) * $limit;

        if ( $sync_type == 'contacts_to_mailchimp' ) {
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
                    $data[$x]['first_name'] = $contact->first_name;
                    $data[$x]['last_name'] = $contact->last_name;

                    $x++;
                }

                $mailchimp->batch_subscribe_to_list( $mailchimp_list, $data );
            }
        }

        if ( $sync_type == 'mailchimp_to_contacts' ) {
            $members = $mailchimp->get_subscribed_members( $mailchimp_list, $offset );
            $total_items = $members['total_items'];

            $inserted_ids = [];
            foreach ( $members['members'] as $member ) {
                $email_parts = explode("@", $member['email_address']);

                $data = [
                    'type'          => 'contact',
                    'first_name'    => ( $member['merge_fields']['FNAME'] != '' ) ? $member['merge_fields']['FNAME'] : $email_parts[0],
                    'last_name'     => ( $member['merge_fields']['LNAME'] != '' ) ? $member['merge_fields']['LNAME'] : ' ',
                    'email'         => $member['email_address'],
                    'contact_owner' => $contact_owner,
                    'life_stage'    => $life_stage,
                ];

                $contact_id = erp_mailchimp_create_contact( $data );

                if ( ! empty( $group_id ) && ! is_wp_error( $contact_id ) ) {
                    erp_crm_create_new_contact_subscriber( ['user_id' => (int) $contact_id, 'group_id' => (int) $group_id] );
                }
            }
        }

        // re-calculate stats
        if ( $total_items <= ( $attempt * $limit ) ) {
            $left = 0;
        } else {
            $left = $total_items - ( $attempt * $limit );
        }

        if ( $left === 0 ) {
            delete_option( 'erp_mailchimp_sync_attempt' );
        }

        $this->send_success( [ 'left' => $left, 'message' => sprintf( __( '%d left to sync.', 'erp-pro' ), $left ) ] );
    }

    /**
     * Refresh email lists from server.
     *
     * @return void
     */
    public function erp_mailchimp_refresh_email_lists() {
        $this->verify_nonce( 'erp-mailchimp-refresh-lists-nonce' );

        $lists = erp_mailchimp_refresh_email_lists();

        $options = get_option( 'erp_integration_settings_mailchimp-integration', [] );
        $options['email_lists'] = $lists;
        update_option( 'erp_integration_settings_mailchimp-integration', $options );

        $this->send_success( [ 'lists' => $lists ] );
    }

    /**
     * Get email list for a new Api key which is different from the current set api key
     *
     * @since 1.2.0
     */
    public function get_email_lists_for_new_api_key() {
        $this->verify_nonce( 'erp-settings-nonce' );

        if ( empty( $_POST['api_key'] ) ) {
            $this->send_error( [ 'message' => __( 'Please give api key', 'erp-pro' ) ] );
        }

        $api_key = sanitize_key( wp_unslash( $_POST['api_key'] ) );

        $mailchimp = new Mailchimp( $api_key );

        if ( ! $mailchimp->is_connected() ) {
            $this->send_error( [ 'message' => __( 'Invalid api key', 'erp-pro' ) ] );
        }

        $this->send_success( $mailchimp->get_lists() );
    }
}
