<?php

namespace WeDevs\ERP\Mailchimp;

/**
 * Class Webhook_Controller
 */
class Webhook_Controller {

    /**
     * @var string
     */
    public static $webhook_path = '/erp-crm-mailchimp-webhook';

    /**
     * @var string
     */
    private static $option_name = 'erp_crm_integration-mailchimp_webhook_secrets';

    /**
     * @var int
     */
    private static $expire_period = 12 * 60 * 60; //12 hour

    /**
     * @var string
     */
    private $list_id;

    /**
     * @var Mailchimp
     */
    private $mailchimp;

    /**
     * Constructor
     *
     * @param $email_list_id
     * @param $api_key
     */
    public function __construct( $email_list_id, $api_key ) {
        $this->list_id = $email_list_id;
        $this->mailchimp = new Mailchimp( $api_key );
    }

    /**
     * Create webhook
     *
     * @since 1.2.0
     *
     * @return false|string
     */
    public function create_webhook() {
        list( $webhook_callback_url, $token ) = $this->get_webhook_callback_url();

        $params = $this->get_parameters( $webhook_callback_url );
        $result = $this->mailchimp->create_webhook( $this->list_id, $params );

        if ( is_array( $result ) ) {
            list( $webhook_id, $is_newly_created ) = $result;

            if ( $is_newly_created ) { // if new webhook is created
                $this->update_secret_store_action( $token );
            }

            return $webhook_id;
        }

        return false;
    }

    /**
     * Update a webhook with an updated nonce verifier
     *
     * @since 1.2.0
     *
     * @return false|string
     */
    public function update_webhook() {
        list( $webhook_callback_url, $token ) = $this->get_webhook_callback_url();

        $params = $this->get_parameters( $webhook_callback_url );
        $result = $this->mailchimp->update_webhook( $this->list_id, $params );

        if ( is_array( $result ) ) {
            list( $webhook_id, $is_updated ) = $result;

            if ( $is_updated ) { // if new webhook is created or existing webhook is updated
                $this->update_secret_store_action( $token );
            }

            return $webhook_id;
        }

        return false;
    }

    /**
     * Delete a webhook
     *
     * @since 1.2.0
     *
     * @return bool|string
     */
    public function delete_webhook() {
        $this->delete_option();
        return $this->mailchimp->delete_webhook( $this->list_id );
    }

    /**
     * Check if a webhook already exists
     *
     * @since 1.2.0
     *
     * @return false|string
     */
    public function is_webhook_exists() {
        return $this->mailchimp->is_webhook_created( $this->list_id );
    }

    /**
     * Create a secret action and store it and return a nonce for that action
     *
     * @since 1.2.0
     *
     * @return false|string
     */
    private function create_and_get_secret() {
        //random string
        $secret = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));

        return $secret;
    }

    /**
     * Verify the nonce with the secret action
     *
     * @since 1.2.0
     *
     * @param $secret
     *
     * @return false|int
     */
    public function validate_token($secret ) {
        $option = get_option( self::$option_name, false );

        $secret_saved = '';

        if ( $option ) {
            $secret_saved = $option[ $this->list_id ];
        }

        return $secret === $secret_saved;
    }

    /**
     * Get the parameters to create a webhook
     *
     * @since 1.2.0
     *
     * @param $webhook_callback_url
     *
     * @return array
     */
    private function get_parameters( $webhook_callback_url ) {
        return [
            'url' => $webhook_callback_url,
            'events' => [
                'subscribe' => true, //subscribed to list
                'unsubscribe' => true, //unsubscribed from list
                'profile' => true, //update profile
                'cleaned' => true, //email cleared from list
                'upemail' => true, //email address changed
                'campaign' => false,
            ],
            'sources' => [
                'user' => true,
                'admin' => true,
                'api' => true,
            ],
        ];
    }

    /**
     * Create and get the callback url for webhook
     *
     * @since 1.2.0
     *
     * @return array
     */
    private function get_webhook_callback_url() {
        $webhook_callback_url = get_home_url() . self::$webhook_path;
        $token = $this->create_and_get_secret();

        return [ add_query_arg( [ 'token' => $token ], $webhook_callback_url ), $token ];
    }

    /**
     * Check if we need to update a webhook
     *
     * @return bool
     */
    public function should_update() {
        $option = get_option( self::$option_name, false );

        if ( ! $option ) {
            return true;
        }

        $last_update = $option[ 'last_update' . $this->list_id ];

        return $last_update + self::$expire_period < erp_current_datetime()->getTimestamp();
    }

    /**
     * Delete option for a list id
     *
     * @since 1.2.0
     *
     * @return bool
     */
    public function delete_option() {
        $option = get_option( self::$option_name, false );

        if ( ! $option ) {
            return false;
        }

        unset( $option[ $this->list_id ] );
        unset( $option[ 'last_update' . $this->list_id ] );

        return update_option( self::$option_name, $option );
    }

    /**
     * Update Secret Action storage
     *
     * @since 1.2.0
     *
     * @param $secret
     */
    private function update_secret_store_action( $secret) {
        $option = get_option( self::$option_name, false );

        if ( ! $option ) {
            $option = [];
        }

        $option[ $this->list_id ] = $secret;
        $option[ 'last_update' . $this->list_id ] = erp_current_datetime()->getTimestamp();

        update_option( self::$option_name, $option );
    }
}
