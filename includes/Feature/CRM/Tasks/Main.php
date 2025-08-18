<?php
namespace WeDevs\ERP_PRO\Feature\CRM\Tasks;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Tasks main class
 *
 * @since 1.2.3
 */
class Main {

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.2.3
     *
     * @return object Class instance
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

	/**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.2.3
     *
     * @return void
     */
    private function __construct() {
        $this->init_classes();
    }

    /**
     * Instantiates classes
     *
     * @since 1.2.3
     *
     * @return void
     */
    private function init_classes() {
        new Admin();
    }
}
