<?php

namespace WeDevs\ERP\Salesforce;

class Salesforce {
    /**
     * HTTP Request Client.
     */
    protected $request;

    /**
     * Client ID.
     */
    protected $client_id = '3MVG9ZL0ppGP5UrC9yYekmd9g_AtHNaHPniiFz5Gt4J0ya5pVs3Axj3d_NxThajZQR2dIENEaKOyRbhyWmX59';

    /**
     * Client Secret.
     */
    protected $client_secret = '5395268455691654134';

    /**
     * Class Contructor.
     *
     * @param string $instance_url
     * @param string $access_token
     * @param string $refresh_token
     */
    public function __construct( $instance_url, $access_token, $refresh_token ) {
        $this->instance_url  = $instance_url;
        $this->access_token  = $access_token;
        $this->refresh_token = $refresh_token;

        $this->request = new HttpClient( $instance_url . '/' );

        $this->refresh();
    }

    /**
     * Get all lists.
     *
     * @return array
     */
    public function get_lists() {
        $response = $this->request->get( 'services/data/v36.0/sobjects/Contact/listviews', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'OAuth ' . $this->access_token,
            ],
        ] );

        return $response->to_array();
    }

    /**
     * Create a contact.
     *
     * @param  string $email
     * @param  string $last_name
     * @param  string $last_name
     *
     * @return void
     */
    public function create_contact( $email, $first_name, $last_name ) {
        $params = [
            'email'     => $email,
            'firstname' => $first_name,
            'lastname'  => $last_name,
        ];

        $response = $this->request->post( 'services/data/v36.0/sobjects/Contact/', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'OAuth ' . $this->access_token,
            ],
            'body' => json_encode( $params ),
        ] );

        return $response->to_array();
    }

    /**
     * Create bulk contacts.
     *
     * @param  array $contacts
     *
     * @return void
     */
    public function create_bulk_contacts( $contacts ) {
        $params = [
            'records' => []
        ];

        $x = 0;
        foreach ( $contacts as $contact ) {
            $params['records'][$x] = $contact;

            $params['records'][$x]['attributes'] = [
                'type' => 'Contact',
                'referenceId' => 'ref' . $x,
            ];

            $x++;
        }

        $response = $this->request->post( 'services/data/v36.0/composite/tree/Contact/', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'OAuth ' . $this->access_token,
            ],
            'body' => json_encode( $params ),
        ] );

        return $response->to_array();
    }

    /**
     * Get contacts.
     *
     * @return array
     */
    public function get_contacts( $list_id, $limit = 50, $offset = null ) {
        if ( isset( $offset ) && $offset > 0 ) {
            $offset = '&offset=' . $offset;
        } else {
            $offset = '';
        }

        $response = $this->request->get( 'services/data/v36.0/sobjects/Contact/listviews/' . $list_id . '/results?limit=' . $limit . $offset, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'OAuth ' . $this->access_token,
            ]
        ] );

        return $response->to_array();
    }

    // /**
    //  * Get contacts.
    //  *
    //  * @return array
    //  */
    // public function get_contacts( $list_id, $limit = 50, $offset = null ) {
    //     if ( isset( $offset ) && $offset > 0 ) {
    //         $offset = ' OFFSET ' . $offset;
    //     }

    //     $query = "SELECT FirstName, LastName, Email from Contact LIMIT " . $limit . $offset;

    //     $response = $this->request->get( 'services/data/v36.0/query?q=' . urlencode( $query ), [
    //         'headers' => [
    //             'Content-Type'  => 'application/json',
    //             'Authorization' => 'OAuth ' . $this->access_token,
    //         ]
    //     ] );

    //     return $response->to_array();
    // }

    /**
     * Get contacts.
     *
     * @return array
     */
    public function count_contacts() {
        $query = "SELECT COUNT(Id) from Contact";

        $response = $this->request->get( 'services/data/v36.0/query?q=' . urlencode($query), [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'OAuth ' . $this->access_token,
            ]
        ] );

        return $response->to_array();
    }

    /**
     * Get a new access token by refresh token.
     *
     * @return array
     */
    public function refresh() {
        $result = $this->count_contacts();

        if ( isset( $result[0]['errorCode'] ) && $result[0]['errorCode'] == 'INVALID_SESSION_ID' ) {
            $params = [
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
            ];

            $response = $this->request->post( '/services/oauth2/token', [
                'body' => $params,
            ], true, 'https://login.salesforce.com' );

            $access = $response->to_array();

            $this->access_token = $access['access_token'];

            $access_data = [ 'access_token' => $access['access_token'] ];

            erp_salesforce_update_options( $access_data );
        }
    }
}
