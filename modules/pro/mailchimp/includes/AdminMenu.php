<?php

namespace WeDevs\ERP\Mailchimp;

class AdminMenu {
    /**
     * Class contructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu'] );
        add_action( 'erp_crm_mailchimp_page', [ $this, 'mailchimp_page'] );
    }

    /**
     * Register the admin menu.
     *
     * @return void
     */
    public function admin_menu() {
        $capabilities = erp_crm_get_manager_role();
        if( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            add_submenu_page( 'erp-sales', __( 'Mailchimp', 'erp-pro' ), __( 'Mailchimp', 'erp-pro' ), $capabilities, 'erp-sales-mailchimp', array( $this, 'mailchimp_page' ) );
        }
    }

    /**
     * Display the mailchimp page.
     *
     * @return void
     */
    public function mailchimp_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'dashboard';

        if ( $action == 'disconnect' ) {
            delete_option( 'erp_integration_settings_mailchimp-integration' );
        }

        $mailchimp_settings_url = admin_url( 'admin.php?page=erp-settings#/erp-integration' );

        $api_key = erp_mailchimp_get_api_key();

        if ( ! $api_key ) {
            ?>
            <div class="wrap">
                <h2><?php esc_html_e( 'Integrations', 'erp-pro' ); ?></h2>
                <?php do_action( 'erp_crm_integration_menu', 'mailchimp' ); ?>
                <p><?php _e( 'You\'re not connected with your Mailchimp account yet. Click on below button to configure.', 'erp-pro' ); ?></p>
                <a href="<?php echo $mailchimp_settings_url ?>"><button class="button-secondary">Configure</button></a>
            </div>
            <?php
        } else {
            include dirname( __FILE__ ) . '/views/dashboard.php';
        }
    }
}
