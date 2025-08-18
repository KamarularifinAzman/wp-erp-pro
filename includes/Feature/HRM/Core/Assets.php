<?php

namespace WeDevs\ERP_PRO\Feature\HRM\Core;

/**
 * Scripts and Styles Class
 */
class Assets {

    /**
     * Class constructor
     */
    function __construct() {

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 10 );
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {

       $this->register_scripts( $this->get_scripts() );

       wp_enqueue_script( 'erp_pro_hrm_core' );
    }

    /**
     * Register scripts
     *
     * @param array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] )      ? $script['deps']      : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] )   ? $script['version']   : ERP_PRO_PLUGIN_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'erp_pro_hrm_core' => [
                'src'       => ERP_HRM_FEATURE_URL . '/Core/assets/js/app.js',
                'version'   => filemtime( ERP_HRM_FEATURE_PATH . '/Core/assets/js/app.js' ),
                'in_footer' => true,
                'deps'      => [
                    "erp-settings" // Dependent on erp-settings JS
                ]
            ]
        ];
    }
}
