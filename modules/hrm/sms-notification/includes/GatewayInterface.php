<?php
namespace WeDevs\ERP\SMS;

/**
 * Gateway Interface
 *
 * @since 1.0
 */
interface GatewayInterface {

    /**
     * Setup credentials for the gateway
     *
     * @since 1.0
     */
    public function get_credentials();

    /**
     * Prepare SMS for the gateway
     *
     * @since 1.0
     */
    public function prepare();

    /**
     * Send SMS
     *
     * @since 1.0
     */
    public function send( array $cell_no_all, $message );
}
