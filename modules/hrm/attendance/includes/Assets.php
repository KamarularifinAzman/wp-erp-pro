<?php
namespace WeDevs\Attendance;

/**
 * Scripts and Styles Class
 */
class Assets {

    function __construct() {

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
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
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_ATTEND_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
            //  wp_enqueue_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        // Enqueue settings page scripts
        if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'erp-settings' ) {
            wp_enqueue_script( 'att-settings' );
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, WPERP_ATTEND_VERSION );
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
            'att-vendor' => [
                'src'       => WPERP_ATTEND_ASSETS . '/js/vendor.js',
                'version'   => filemtime( WPERP_ATTEND_PATH . '/assets/js/vendor.js' ),
                'in_footer' => true
            ],
            'att-frontend' => [
                'src'       => WPERP_ATTEND_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'att-vendor' ],
                'version'   => filemtime( WPERP_ATTEND_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true
            ],
            'att-admin' => [
                'src'       => WPERP_ATTEND_ASSETS . '/js/admin.js',
                'deps'      => [ 'jquery', 'att-vendor' ],
                'version'   => filemtime( WPERP_ATTEND_PATH . '/assets/js/admin.js' ),
                'in_footer' => true
            ],
            'att-settings' => [
                'src'       => WPERP_ATTEND_ASSETS . '/js/settings.js',
                'deps'      => [ 'erp-settings' ],
                'version'   => filemtime( WPERP_ATTEND_PATH . '/assets/js/settings.js' ),
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
            'att-style' => [
                'src' =>  WPERP_ATTEND_ASSETS . '/css/style.css'
            ],
            'att-frontend' => [
                'src' =>  WPERP_ATTEND_ASSETS . '/css/frontend.css'
            ],
            'att-admin' => [
                'src' =>  WPERP_ATTEND_ASSETS . '/css/admin.css'
            ],
        ];

        return $styles;
    }

}
