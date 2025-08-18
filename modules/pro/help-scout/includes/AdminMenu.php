<?php

namespace WeDevs\HelpScout;

class AdminMenu {
    /**
     * Class contructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu'] );
        add_action( 'erp_crm_help_scout_page', [ $this, 'helpscout_page'] );
    }

    /**
     * Register the admin menu.
     *
     * @return void
     */
    public function admin_menu() {
        $capabilities = erp_crm_get_manager_role();
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            add_submenu_page( 'erp-sales', __( 'Helpscout', 'erp-pro' ), __( 'Helpscout', 'erp-pro' ), $capabilities, 'erp-sales-hubspot', array( $this, 'helpscout_page' ) );
        }
    }

    /**
     * Display the hubspot page.
     *
     * @return void
     */
    public function helpscout_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'dashboard';

        if ( $action == 'disconnect' ) {
            delete_option( 'erp_integration_settings_hubspot-integration' );
        }

        $helpscout_settings_url = admin_url( 'admin.php?page=erp-settings#/erp-integration' );

        $app_id     = helpscout_get_option( 'helpscout_app_id' );
        $app_secret = helpscout_get_option( 'helpscout_app_secret' );

        if ( ! $app_id && ! $app_secret ) {
            ?>
            <div class="wrap">
                <h2><?php esc_html_e( 'Integrations', 'erp-pro' ); ?></h2>
                <?php do_action( 'erp_crm_integration_menu', 'help_scout' ); ?>
                <p><?php _e( 'You\'re not connected with your Helpscout account yet. Click on below button to configure.', 'erp-pro' ); ?></p>
                <a href="<?php echo $helpscout_settings_url ?>"><button class="button-secondary">Configure</button></a>
            </div>
            <?php
        } else {
            include dirname( __FILE__ ) . '/views/dashboard.php';
        }
    }
}
