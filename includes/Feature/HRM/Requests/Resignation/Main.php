<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Resignation;

/**
 * Resignation handler class
 * 
 * @since 1.2.0
 */
class Main {

    /**
     * Class constructor
     * 
     * @since 1.2.0
     */
    function __construct() {
        $this->includes();
        $this->init_classes();
    }

    /**
     * Includes required files
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function includes() {
        include_once ERP_PRO_FEATURE_DIR . '/HRM/Requests/Resignation/functions.php';
    }

    /**
     * Instantiates required classes
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function init_classes() {
        new Hooks();
        new Email();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Ajax();
        }
    }
}