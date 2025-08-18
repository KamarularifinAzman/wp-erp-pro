<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Remote_Work;

/**
 * Remote work request handler class
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
        include_once ERP_PRO_FEATURE_DIR . '/HRM/Requests/Remote_Work/functions.php';
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
        new Settings();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Ajax();
        }
    }
}