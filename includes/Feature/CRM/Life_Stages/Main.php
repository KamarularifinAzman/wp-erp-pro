<?php
namespace WeDevs\ERP_PRO\Feature\CRM\Life_Stages;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Deals plugin main class
 *
 * @since 1.0.1
 */
class Main {

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.0.1
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
     * @since 1.0.1
     *
     * @return void
     */
    private function __construct() {
        $this->init_classes();
    }

    /**
     * Include required files
     *
     * @since 1.0.1
     *
     * @return void
     */
    private function init_classes() {
        new Hooks();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Ajax();
        };
    }
}
