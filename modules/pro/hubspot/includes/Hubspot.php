<?php

namespace WeDevs\ERP\Hubspot;

/**
 * @see https://developers.hubspot.com/docs/api/migrate-an-api-key-integration-to-a-private-app
 */
class Hubspot {
    /**
     * HTTP Request Client.
     */
    protected $request;

    /**
     * Class Contructor.
     *
     * @param string $apikey
     */
    public function __construct( $api_key )
    {
        $this->request = new Http_Client( 'https://api.hubapi.com/' );

        $this->request->set_access_token( $api_key );
    }

    /**
     * Ping Hubspot if connected.
     *
     * @return boolean
     */
    public function is_connected() {
        $response = $this->request->get( 'contacts/v1/lists/all/contacts/all?count=1' );
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
        $response = $this->request->get( 'contacts/v1/lists?count=2');
        return $response->to_array();
    }

    /**
     * Subscribe bulk emails to a list.
     *
     * @param  string $list_id
     * @param  string $data
     *
     * @return array
     */
    public function bulk_subscribe_to_list( $list_id, $data = [] ) {
        $params = $data;

        $response = $this->request->post( 'contacts/v1/contact/batch', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode( $params ),
        ], false );

        $email_addresses = [];
        foreach ( $data as $email ) {
            $email_addresses[] = $email['email'];
        }

        $emails_query_string = implode( '&email=', $email_addresses );

        $response = $this->request->get( 'contacts/v1/contact/emails/batch/?email = ' . $emails_query_string );
        $contacts = $response->to_array();
        $vids     = [];
        $emails   = [];

        $x = 0;
        foreach ( $contacts as $contact ) {
            $vids[$x]   = $contact['vid'];
            $emails[$x] = '';

            foreach ( $contact['identity-profiles'][0]['identities'] as $identity ) {
                if ( $identity['type'] === 'EMAIL' ) {
                    $emails[$x] = $identity['value'] ? $identity['value'] : '';
                }
            }

            $x++;
        }

        $params = [
            'vids'   => $vids,
            'emails' => $emails,
        ];

        $response = $this->request->post( 'contacts/v1/lists/' . $list_id . '/add', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode( $params ),
        ], false );

        return true;
    }

    /**
     * Subscribe email to a list.
     *
     * @param  string $list_id
     * @param  string $first_name
     * @param  string $last_name
     *
     * @return array
     */
    public function subscribe_to_list( $list_id, $email, $first_name, $last_name ) {
        $response = $this->request->get( 'contacts/v1/contact/email/' .  $email . '/profile' );

        $response_code = $response->response_code();
        if ( $response_code !== 404 ) {
            $contact = $response->to_array();
            $vid     = $contact['vid'];
        } else {
            $params = [
                'properties' => [
                    [
                        'property' => 'email',
                        'value'    => $email,
                    ],
                    [
                        'property' => 'firstname',
                        'value'    => $first_name,
                    ],
                    [
                        'property' => 'lastname',
                        'value'    => $last_name
                    ],
                ]
            ];

            $response = $this->request->post( 'contacts/v1/contact', [
                'body' => json_encode( $params ),
            ] );

            $contact = $response->to_array();
            $vid     = $contact['vid'];
        }

        $params = [
            'vids'  => [
                $vid
            ],
            'emails' => [
                $email
            ],
        ];

        $response = $this->request->post( 'contacts/v1/lists/' . $list_id . '/add', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode( $params ),
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
    public function get_subscribed_members( $list_id, $offset = null ) {
        $limit = 50;

        if ( isset( $offset ) ) {
            $offset = '&vidOffset=' . $offset;
        } else {
            $offset = '';
        }

        $response = $this->request->get( 'contacts/v1/lists/all/contacts/all?count=' . $limit . $offset );

        return $response->to_array();
    }
}
