<?php
namespace WeDevs\ERP\Zendesk;

use WeDevs\ERP\People;

class Ajax {

    /**
     * Constructor function
     */
    function __construct() {
        add_action( 'wp_ajax_erp_zendesk_activity', array( $this, 'zendesk_activity' ) );
    }

    /**
     * Zendesk Custotomer Activity
     *
     * @return void
     */
    public function zendesk_activity() {
        if ( ! wp_verify_nonce( $_POST['nonce'] ) ) {
            wp_send_json_error( ['message'  => __( 'Something went wrong try again', 'erp-pro' )] );
        }

        if ( ! isset( $_POST['contact_id'] ) ) {
            wp_send_json_error( ['message' => __('No contact id found for the uyser.', 'erp-pro')] );
        }

        $options    = get_option( 'erp_integration_settings_zendesk' );
        if ( empty( $options['zendesk_subdomain'] ) || empty( $options['zendesk_login_email'] ) || empty( $options['zendesk_password'] ) ) {
            $configure_link = admin_url( 'admin.php?page=erp-settings#/erp-integration' );
            wp_send_json_error(['message' => __( "WP ERP Zendesk plugin is not configured correctly. Please configure the plugin from the following <b><a href='{$configure_link}'>Link</a></b>", 'erp-pro' )]);
        }

        $people     = \WeDevs\ERP\Framework\Models\People::find( absint( $_POST['contact_id'] ) )->first();
        if ( ! $people ) {
            wp_send_json_error( ['message' => __('Could not find any user with the id.', 'erp-pro')] );
        }

        $zendesk_user_id = erp_people_get_meta( $_POST['contact_id'], 'zendesk_user_id', true );
        if ( ! $zendesk_user_id ) {
            wp_send_json_error( ['message' => __('No activity found', 'erp-pro')] );
        }

        $url    ='https://' . $options['zendesk_subdomain'] . '/api/v2/users/'. $zendesk_user_id .'/tickets/requested.json';
        $args   = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $options['zendesk_login_email'] . ':' . $options['zendesk_password'] )
            )
        );

        $response =  wp_remote_get( $url, $args );
        if ( $response['response']['code'] == 404 ) {
            $configure_link = admin_url( 'admin.php?page=erp-settings#/erp-integration' );
            wp_send_json_error(['message' => __( "WP ERP Zendesk plugin is not configured correctly. Please configure the plugin from the following <b><a href='{$configure_link}'>Link</a></b>", 'erp-pro' )] );
        }

        if ( $response ) {
            $data = json_decode( $response['body'] );
        }

        if ( empty( $data->tickets ) ) {
            wp_send_json_error( ['message' => __( 'No activity found!', 'erp-pro' )] );
        }else{
            $lists      = '';
            $tickets    = array_slice( $data->tickets, 0, 5 );
            foreach ( $tickets as $ticket ) {
                $link   = 'https://'.$options['zendesk_subdomain'].'/agent/tickets/'.$ticket->id;
                $lists .= '<li class="link-to-original">';
                $lists .= '<div class="ticket-meta">';
                $lists .= '<a href="'.$link.'" target="_blank">#'. $ticket->id .' '. $ticket->subject .'</a> <span class="ticket-status '.$ticket->status.'">'. $ticket->status .'</span>';
                $lists .= '</div>';
                $lists .= '</li>';
            }

            if ( count( $data->tickets ) > 5 ) {
                $more_link  = 'https://' . $options['zendesk_subdomain'] . '/agent/users/' . $zendesk_user_id . '/requested_tickets';
                $lists  .=  '<a style="text-align:center" href="' . $more_link . '" target="_blank">View all tickets by the user.</a>';
            }

            $html = '<ul>' . $lists . '</ul>';
            wp_send_json_success( [ 'message' => $html ] );
        }
    }
}
