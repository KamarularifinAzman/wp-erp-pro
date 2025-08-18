<?php

namespace WeDevs\Reimbursement\Classes;

/**
 * Scripts and Styles Class
 */
class Assets {

    function __construct() {

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 10 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        global $current_screen;

        $section = ! empty( $_GET['section'] ) ? wp_unslash( $_GET['section'] ) : '';

        if (
            is_admin() && 'wp-erp_page_erp-accounting' != $current_screen->base
            && $section !== 'reimbursement'
        ) {
            return;
        }

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_REIMBURSEMENT_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        // get people id for use in JS
        $people = erp_get_people_by( 'user_id', get_current_user_id() );

        wp_localize_script( 'erp-reimbursement-admin', 'erp_reimbursement_var', [
            'erp_reimbursement_module' => true,
            'page'       => isset( $_GET['page'] ) ? wp_unslash( $_GET['page'] ) : '',
            'section'    => isset( $_GET['section'] ) ? wp_unslash( $_GET['section'] ) : '',
            'assets_dir' => ERP_REIMBURSEMENT_ASSETS,
            'people_id'  => empty( $people ) || is_wp_error( $people ) ? null : $people->id
        ] );
    }

    /**
     * Register styles
     *
     * @param array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, WPERP_REIMBURSEMENT_VERSION );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

        $scripts = [
            'erp-reimbursement-admin' => [
                'src'       => ERP_REIMBURSEMENT_ASSETS . '/js/admin.js',
                'version'   => filemtime( ERP_REIMBURSEMENT_PATH . '/assets/js/admin.js' ),
                'in_footer' => true
            ]
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {

        $styles = [
            'erp-reimbursement-admin' => [
                'src' => ERP_REIMBURSEMENT_ASSETS . '/css/admin.css'
            ],
        ];

        return $styles;
    }

}
