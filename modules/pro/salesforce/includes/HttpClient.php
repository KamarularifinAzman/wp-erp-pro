<?php

namespace WeDevs\ERP\Salesforce;

class HttpClient {
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
     * class constructor file
     */
    public function __construct( $base_url ) {
        $this->base_url = $base_url;
    }

    /**
     * Remote get request.
     *
     * @param  string  $url
     * @param  array   $data
     * @param  string  $base_url
     *
     * @return object
     */
    public function get( $url, $data = [], $base_url = null ) {
        if ( isset( $base_url ) ) {
            $base_url = $base_url;
        } else {
            $base_url = $this->base_url;
        }

        $args = [
            'method' => 'GET',
            'timeout' => 10,
        ];

        if ( isset( $data['headers'] ) ) {
            $args['headers'] = $data['headers'];
        }

        $this->response = wp_remote_request( $base_url . $url, $args );

        return $this;
    }

    /**
     * Remote post request.
     *
     * @param  string  $url
     * @param  array   $data
     * @param  boolean $blocking
     * @param  boolean $base_url
     *
     * @return object
     */
    public function post( $url, $data = [], $blocking = true, $base_url = null ) {
        if ( isset( $base_url ) ) {
            $base_url = $base_url;
        } else {
            $base_url = $this->base_url;
        }

        $args = [
            'method' => 'POST',
            'timeout' => 10,
            'blocking' => $blocking,
        ];

        if ( isset( $data['headers'] ) ) {
            $args['headers'] = $data['headers'];
        }

        if ( isset( $data['body'] ) ) {
            $args['body'] = $data['body'];
        }

        $this->response = wp_remote_request( $base_url . $url, $args );

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
