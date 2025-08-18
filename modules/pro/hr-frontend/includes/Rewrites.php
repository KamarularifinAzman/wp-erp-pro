<?php
namespace WeDevs\HrFrontend;

/**
 * ERP Dashboard Rewrites Class
 */
class Rewrites {

    /**
     * ERP_Dashboard_Rewrites constructor.
     */
    function __construct() {
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'register_query_var' ] );
        add_action( 'template_redirect', [ $this, 'rewrite_templates' ] );
        add_action('erp_after_save_settings', [$this, 'flush_permalink']);
    }

    /**
     * Add the required rewrite rules
     *
     * @return void
     */
    function add_rewrite_rules() {
        $dashboard_slug = $this->get_dashboard_slug();
        add_rewrite_rule( '^' . $dashboard_slug . '/?$', 'index.php?erp_dashboard=true', 'top' );
    }

    /**
     * Register our query vars
     *
     * @param  array $vars
     *
     * @return array
     */
    function register_query_var( $vars ) {
        $vars[] = 'erp_dashboard';

        return $vars;
    }

    /**
     * Load our template on our rewrite rule
     *
     * @return void
     */
    public function rewrite_templates() {

        if ( 'true' == get_query_var( 'erp_dashboard' ) ) {

            //check if user is logged in otherwise redirect to login page
            if ( ! is_user_logged_in() ) {
                wp_redirect( wp_login_url( $this->get_dashboard_url() ) );
                exit();
            }

            new HrFrontendI18n();

            include_once ERP_DASHBOARD_PATH . '/templates/dashboard.php';
            exit;
        }


    }

    /**
     * Get the slug of erp dashboard page
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_dashboard_slug() {
        return get_erp_dashboard_slug();
    }

    /**
     * Get erp dashboard page url
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_dashboard_url() {
        return get_erp_dashboard_url();
    }

    /**
     * Flush permalink
     *
     * @since 1.0.0
     */
    public function flush_permalink(){
        flush_rewrite_rules();
    }
}
