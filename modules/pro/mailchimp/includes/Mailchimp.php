<?php

namespace WeDevs\ERP\Mailchimp;

class Mailchimp {
    /**
     * HTTP Request Client.
     */
    protected $request;

    /**
     * Class Contructor.
     *
     * @param string $apikey
     */
    public function __construct( $api_key ) {
        $this->api_key = $api_key;
        $base_url = 'https://us1.api.mailchimp.com/3.0/';

        $dc = 'us1';

        if ( strstr( $api_key, '-' ) ) {
            list( $key, $dc ) = explode( '-', $api_key, 2 );
            if ( ! $dc ) {
                $dc = 'us1';
            }
        }

        $base_url = str_replace( 'us1', $dc, $base_url );

        $this->request = new Http_Client( $base_url );
    }

    /**
     * Ping Mailchimp if connected.
     *
     * @return boolean
     */
    public function is_connected() {
        $response = $this->request->get( '/', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key
            ],
        ] );

        $response_code = $response->response_code();
        if ( $response_code === 200 ) {
            return true;
        }

        return false;
    }

    /**
     * Get all lists.
     *
     * @return array
     */
    public function get_lists() {
        $response = $this->request->get( 'lists?fields=lists.id,lists.name,lists.stats.member_count', [
            'headers' => [
                'Authorization' => 'apikey ' . $this->api_key
            ],
        ] );

        return $response->to_array();
    }

    /**
     * Check if member exist or not in a list.
     *
     * @param  string $list_id
     * @param  string $email
     *
     * @return boolean
     */
    public function member_exists( $list_id, $email ) {
        $response = $this->request->get( 'lists/' . $list_id . '/members/' . md5( $email ), [
            'headers' => [
                'Authorization' => 'apikey ' . $this->api_key,
            ]
        ] );

        $response_code = $response->response_code();
        if ( $response_code === 200 ) {
            return true;
        }

        return false;
    }

    /**
     * Subscribe email to a list.
     *
     * @param  string $list_id
     * @param  string $email
     * @param  string $first_name
     * @param  string $last_name
     *
     * @return array
     */
    public function subscribe_to_list( $list_id, $email, $first_name, $last_name ) {
        $params = [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $first_name,
                'LNAME' => $last_name,
            ],
        ];

        $response = $this->request->post( 'lists/' . $list_id . '/members', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key,
            ],
            'body' => json_encode( $params ),
        ], false );

        return true;
    }

    /**
     * Batch Subscribe email to a list.
     *
     * @param  string $list_id
     * @param  string $data
     *
     * @return array
     */
    public function batch_subscribe_to_list( $list_id, $data ) {
        $operations = [];
        $x = 0;
        foreach ( $data as $item ) {
            $operations['operations'][$x] = [
                'method' => 'POST',
                'path' => 'lists/' . $list_id . '/members',
                'body' => json_encode([
                    'email_address' => $item['email'],
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'FNAME' => $item['first_name'],
                        'LNAME' => $item['last_name'],
                    ],
                ]),
            ];

            $x++;
        }

        $response = $this->request->post( 'batches', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key,
            ],
            'body' => json_encode( $operations ),
        ], false );

        return true;
    }

    /**
     * Get Subscribed members from a list.
     *
     * @param  string $list_id
     * @param  string $offset  (optional)
     *
     * @return array
     */
    public function get_subscribed_members( $list_id, $offset = 0 ) {
        $limit = 50;

        if ( $offset > 0 ) {
            $offset = '&offset=' . $offset;
        } else {
            $offset = '';
        }

        $response = $this->request->get( 'lists/' . $list_id . '/members?count=' . $limit . $offset, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key,
            ],
        ] );

        return $response->to_array();
    }

    /**
     * Check if webhook already created
     *
     * @since 1.2.0
     *
     * @param $list_id
     *
     * @return false|string false or webhook id
     */
    public function is_webhook_created($list_id ) {
        $response = $this->request->get(
            "lists/$list_id/webhooks",
            [
                'headers' => [
                    'Authorization' => 'apikey ' . $this->api_key,
                ],
            ]
        );

        if ( 200 === $response->response_code() ) {
            $response = $response->to_array();
            $webhooks = $response['webhooks'];

            foreach ( $webhooks as $webhook ) {
                if ( false !== stripos( $webhook['url'], Webhook_Controller::$webhook_path ) && false !== stripos( $webhook['url'], get_home_url() ) ) {
                    return $webhook['id'];
                }
            }
        }

        return false;
    }

    /**
     * Create webhook for a list
     *
     * @since 1.2.0
     *
     * @param $list_id
     *
     * @return array|false
     */
    public function create_webhook( $list_id, $params ) {
        $webhook_id = $this->is_webhook_created( $list_id );

        if ( false !== $webhook_id ) {
            return [ $webhook_id, false ]; // no new webhook is created, so second element is false
        }

        $response = $this->request->post(
            "lists/$list_id/webhooks",
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'apikey ' . $this->api_key,
                ],
                'body'    => json_encode( $params ),
            ]
        );

        if ( 200 === $response->response_code() ) {
            $response = $response->to_array();

            return [ $response['id'], true ]; // new webhook is created so second element is true
        }

        return false;
    }

    /**
     * Update webhook for a list
     *
     * @since 1.2.0
     *
     * @param $list_id
     *
     * @return false|array
     */
    public function update_webhook( $list_id, $params ) {
        $webhook_id = $this->is_webhook_created( $list_id );

        if ( false === $webhook_id ) {
            return $this->create_webhook( $list_id, $params );
        }

        $response = $this->request->patch(
            "lists/$list_id/webhooks/$webhook_id",
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'apikey ' . $this->api_key,
                ],
                'body'    => json_encode( $params ),
            ]
        );

        if ( 200 === $response->response_code() ) {
            $response = $response->to_array();

            return [ $response['id'], true ]; // webhook is updated, so second element is true
        }

        return false;
    }


    /**
     * Delete the webhook associated with $list_id
     *
     * @since 1.2.0
     *
     * @param $list_id
     *
     * @return bool|string
     */
    public function delete_webhook( $list_id ) {
        $webhook_id = $this->is_webhook_created( $list_id );

        if ( false === $webhook_id ) {
            return true;
        }

        $args = [
            'method'   => 'DELETE',
            'timeout'  => 10,
            'blocking' => true,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key,
            ],
        ];

        $url = "lists/$list_id/webhooks/$webhook_id";

        $base_url = 'https://us1.api.mailchimp.com/3.0/';
        $dc       = 'us1';

        if ( strstr( $this->api_key, '-' ) ) {
            list( $key, $dc ) = explode( '-', $this->api_key, 2 );

            if ( ! $dc ) {
                $dc = 'us1';
            }
        }

        $base_url = str_replace( 'us1', $dc, $base_url );
        $response = wp_remote_request( $base_url . $url, $args );

        if ( 204 === wp_remote_retrieve_response_code( $response ) ) {
            return $webhook_id;
        }

        return false;
    }

    /**
     * Unsubscribe a contact from mailchimp
     *
     * @since 1.2.0
     *
     * @param $list_id
     * @param $email
     * @param $first_name
     * @param $last_name
     *
     * @return bool
     */
    public function unsubscribe_from_list( $list_id, $email, $first_name, $last_name ) {
        $subscriber_hash = md5( strtolower( $email ) );

        $params = [
            'email_address' => $email,
            'status_if_new' => 'unsubscribed',
            'status'        => 'unsubscribed',
            'merge_fields'  => [
                'FNAME' => $first_name,
                'LNAME' => $last_name,
            ],
        ];

        $response = $this->request->put( 'lists/' . $list_id . '/members/' . $subscriber_hash, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key,
            ],
            'body' => json_encode( $params ),
        ], false );

        return true;
    }

    /**
     * Update contact
     *
     * @since 1.2.0
     *
     * @param $list_id
     * @param $email
     * @param $first_name
     * @param $last_name
     */
    public function update_contact( $list_id, $email, $first_name, $last_name, $status, $old_email = '' ) {
        $subscriber_hash = empty( $old_email ) ? md5( strtolower( $email ) ) : md5( strtolower( $old_email ) );

        if ( 'subscribe' === $status ) {
            $status = 'subscribed';
        } else {
            $status = 'unsubscribed';
        }

        $params = [
            'email_address' => $email,
            'status_if_new' => $status,
            'status'        => $status,
            'merge_fields'  => [
                'FNAME' => $first_name,
                'LNAME' => $last_name,
            ],
        ];

        $response = $this->request->put( 'lists/' . $list_id . '/members/' . $subscriber_hash, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'apikey ' . $this->api_key,
            ],
            'body' => json_encode( $params ),
        ], false );

        return true;
    }
}
