<?php

namespace WeDevs\ERP_PRO\Feature\Accounting\Core;

/**
 * Class for handling assets
 * 
 * @since 1.2.3
 */
class Assets {

    /**
     * Class constructor
     * 
     * @since 1.2.3
     */
    function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueues necessary app scripts and styles
     * 
     * @since 1.2.3
     *
     * @return void
     */
    public function enqueue_scripts() {
       $this->register_scripts( $this->get_scripts() );
       $this->register_styles( $this->get_styles() );

       wp_enqueue_style( 'erp_pro_acct_core' );
       wp_enqueue_script( 'erp_pro_acct_core' );

       wp_enqueue_script( 'erp_pro_acct_settings_core' );
    }

    /**
     * Register scripts
     * 
     * @since 1.2.3
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
     * Register all the styles
     *
     * @return void
     */
    private function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps      = isset( $style['deps'] )      ? $style['deps']      : false;
            $version   = isset( $style['version'] )   ? $style['version']   : ERP_PRO_PLUGIN_VERSION;

            wp_register_style( $handle, $style['src'], $deps, $version );
        }
    }

    /**
     * Get all scripts to register
     * 
     * @since 1.2.3
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'erp_pro_acct_core'         => [
                'src'       => ERP_ACCT_FEATURE_URL . '/Core/assets/js/app.js',
                'version'   => filemtime( ERP_ACCT_FEATURE_PATH . '/Core/assets/js/app.js' ),
                'in_footer' => true,
                'deps'      => [
                    'accounting-admin'
                ]
            ],

            'erp_pro_acct_settings_core' => [
                'src'       => ERP_ACCT_FEATURE_URL . '/Core/assets/js/settings.js',
                'version'   => filemtime( ERP_ACCT_FEATURE_PATH . '/Core/assets/js/settings.js' ),
                'in_footer' => true,
                'deps'      => [
                    'erp-settings'
                ]
            ]
        ];
    }

    /**
     * Get all stylesheets to register
     * 
     * @since 1.2.3
     *
     * @return array
     */
    private function get_styles() {
        return [
            'erp_pro_acct_core'         => [
                'src'       => ERP_ACCT_FEATURE_URL . '/Core/assets/js/app.css',
                'version'   => filemtime( ERP_ACCT_FEATURE_PATH . '/Core/assets/js/app.css' ),
                'deps'      => [
                    'accounting-admin'
                ]
            ],
        ];
    }
}
