<?php

namespace WeDevs\HelpScout;

use WeDevs\ERP\People;

class Ajax {

    public function __construct() {
        add_action( 'wp_ajax_erp_helpscout_contact_hc_activity', [ $this, 'helpscout_activity' ] );
        add_action( 'wp_ajax_helpscout_send_message', [ $this, 'helpscout_send_message' ] );
        add_action( 'wp_ajax_helpscout_contact_sync', [ $this, 'helpscout_contact_sync' ] );
        add_action( 'wp_ajax_get_helpscout_user', [ $this, 'get_helpscout_user_by_mailbox' ] );
    }

    public function helpscout_activity(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'erp-hc-nonce' ) ) {
            wp_send_json_error(['msg' => __('Something went wrong try again', 'erp-pro')]);
        }

        if(!isset($_POST['contact_id'])){
            wp_send_json_error(['msg' => __('No contact id found for the user.', 'erp-pro')]);
        }

        $options = get_option( 'erp_integration_settings_helpscout' );
        if ( ! is_connected_helpscout() ) {
            $configure_link = admin_url( 'admin.php?page=erp-settings#/erp-integration' );
            wp_send_json_error(['msg' => __(
                "WP ERP HelpScout plugin is not configured correctly. Please configure the plugin from
                 the following <b><a href='{$configure_link}'>link</a></b>.", 'erp-pro')]);
        }

        $people = \WeDevs\ERP\Framework\Models\People::find(absint($_POST['contact_id']));
        if ( !$people ) {
            wp_send_json_error(['msg' => __('Could not find any user with the user id received.', 'erp-pro')]);
        }

        $email = $people->email;
        $access_token = get_helpscout_access_token();
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        );
        $mailbox = implode(',', (array) $options['helpscout_mailbox']);

        $request = wp_remote_get( 'https://api.helpscout.net/v2/conversations?mailbox='.$mailbox.'&status=all&query=(email:'.$email.')', $args );

        if ( is_wp_error( $request ) ) {
            wp_send_json_error(['msg' => __('Something went wrong please try again', 'erp-pro')]);
        }

        $body = wp_remote_retrieve_body( $request );
        $response = json_decode($body);

        if ( !isset($response->_embedded->conversations) || (count($response->_embedded->conversations)<1)){
            wp_send_json_error(['msg' => __('No activity found', 'erp-pro')]);
        } else{
            $lists = '';
            foreach ( $response->_embedded->conversations as $item ){
                $link = "https://secure.helpscout.net/conversation/{$item->id}/{$item->number}/";
                $message = explode(' ', $item->preview);
                $taken = array_slice($message, 0, 20);

                $lists .= '<li class="link-to-original">';
                $lists .= '<div class="ticket-meta">';
                $lists .= "<a href='{$link}' target='_blank'>#{$item->id} {$item->subject}</a><span class='ticket-status {$item->status}'>{$item->status}</span> ";
                $lists .= '</div>';
                $lists .= implode(' ', $taken);
                $lists .= '</li>';
            }
            $html = '<ul>'.$lists.'</ul>';

            wp_send_json_success(['msg' => $html]);
        }
    }

    /**
     * Helpscout send message
     *
     * @return object
     */
    public function helpscout_send_message() {
        $subject = isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : '';
        $message = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';
        $mailbox = isset( $_POST['mailbox'] ) ? intval( $_POST['mailbox'] ) : '';
        $user_id = isset( $_POST['user'] ) ? intval( $_POST['user'] ) : '';
        $customer_email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        if ( empty( $subject ) || empty( $message ) || empty( $mailbox ) || empty( $user_id ) || empty( $customer_email ) ) {
            return;
        }

        $data = json_encode( array(
            'subject' => $subject,
            'customer' => array(
                'email' => $customer_email
            ),
            'mailboxId' => $mailbox,
            'type'      =>  'email',
            'status'    =>  'active',
            'threads'   =>  array(
                array(
                    'type'  =>  'customer',
                    'customer'  =>  array(
                        'email' => $customer_email
                    ),
                    'text'  =>  $message
                )
            ),
            'user' => $user_id
        ) );

        $access_token = get_helpscout_access_token();
        $args = array(
            'body' => $data,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-type' => 'application/json; charset=UTF-8'
            )
        );

        $response = wp_remote_post( 'https://api.helpscout.net/v2/conversations', $args );
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array(
                'msg'   =>  __( 'Something went wrong please try again', 'erp-pro' ),
            ) );
        }

        wp_send_json_success( array(
            'msg'   =>  __( 'Your message has been sent successfully', 'erp-pro' ),
        ) );
    }

    /**
     * Helspcout contact sync
     *
     * @return object
     */
    public function helpscout_contact_sync() {
        $mailbox_ids = isset( $_POST['mailboxes'] ) ? $_POST['mailboxes'] : '';
        $life_stage = isset( $_POST['life_stage'] ) ? $_POST['life_stage'] : '';
        $group_id   = isset( $_POST['contact_group'] ) ? $_POST['contact_group'] : '';

        if ( ! $mailbox_ids && ! $life_stage ) {
            return;
        }
        $options = get_option( 'erp_integration_settings_helpscout' );
        $options['helpscout_customer_lifestage'] = $life_stage;
        $options['helpscout_mailbox'] = $mailbox_ids;
        $options['contact_group'] = $group_id;
        update_option( 'erp_integration_settings_helpscout', $options );

        new \WeDevs\HelpScout\User();
        wp_send_json_success( array(
            'message'   =>  'Successfully contact sync'
        ) );
    }

    /**
     * Get helpscout user by mailbox
     *
     * @return array Helpscout user
     */
    public function get_helpscout_user_by_mailbox() {
        $mailbox_id = isset( $_POST['mailbox_id'] ) ? $_POST['mailbox_id'] : '';
        if ( ! $mailbox_id ) {
            return;
        }
        $users = get_helscout_user_by_mailbox( $mailbox_id );

        if ( empty( $users ) ) {
            wp_send_json_error( array(
                'message'   =>  'No user found'
            ) );
        }

        wp_send_json_success( $users );
    }
}
