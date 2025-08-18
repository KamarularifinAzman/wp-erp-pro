<?php

namespace WeDevs\ERP\Hubspot;

class Http_Client {
    /**
     * Instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * API Response.
     *
     * @var array
     */
    protected $response = array();

    /**
     * Server base url
     *
     * @var string
     */
    protected $base_url;

    /**
     * Access token of hubspot private App
     *
     * @var string
     */
    protected $access_token;

    /**
     * class constructor file
     */
    public function __construct( $base_url ) {
        $this->base_url = $base_url;
    }

    public function set_access_token( $access_token ) {
        $this->access_token = $access_token;
    }

    /**
     * Remote get request.
     *
     * @param  string  $url
     * @param  array   $data
     *
     * @return object
     */
    public function get( $url, $data = array() ) {
        $args = [
            'method' => 'GET',
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token,
            ]
        ];

        if ( isset( $data['headers'] ) ) {
            $args['headers'] = wp_parse_args( $data['headers'], $args['headers'] );
        }

        $this->response = wp_remote_request( $this->base_url . $url, $args );

        return $this;
    }

    /**
     * Remote post request.
     *
     * @param  string  $url
     * @param  array   $data
     * @param  boolean $blocking
     *
     * @return object
     */
    public function post( $url, $data = array(), $blocking = true ) {
        $args = [
            'method' => 'POST',
            'timeout' => 10,
            'blocking' => $blocking,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token,
            ]
        ];

        if ( isset( $data['headers'] ) ) {
            $args['headers'] = wp_parse_args( $data['headers'], $args['headers'] );
        }

        if ( isset( $data['body'] ) ) {
            $args['body'] = $data['body'];
        }

        $this->response = wp_remote_request( $this->base_url . $url, $args );

        return $this;
    }

    /**
     * Return response.
     *
     * @return array
     */
    public function response() {
        return $this->response;
    }

    /**
     * Return response body data as json.
     *
     * @return json
     */
    public function to_json() {
        return wp_remote_retrieve_body( $this->response );
    }

    /**
     * Return response body data as json.
     *
     * @return array
     */
    public function to_array() {
        return json_decode( wp_remote_retrieve_body( $this->response ), true );
    }

    /**
     * Return response status code.
     *
     * @return string
     */
    public function response_code() {
        return wp_remote_retrieve_response_code( $this->response );
    }
}
