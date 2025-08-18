<?php

namespace WeDevs\ERP\Salesforce;

class AdminMenu {
    /**
     * Class contructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu'] );
        add_action( 'erp_crm_salesforce_page', [ $this, 'salesforce_page'] );

        $this->admin_page = admin_url( 'admin.php?page=erp-crm&section=integration&sub-section=salesforce' );
    }

    /**
     * Register the admin menu.
     *
     * @return void
     */
    public function admin_menu() {
        $capabilities = erp_crm_get_manager_role();

        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            $page = add_submenu_page( 'erp-sales', __( 'Salesforce', 'erp-pro' ), __( 'Salesforce', 'erp-pro' ), $capabilities, 'erp-sales-salesforce', array( $this, 'salesforce_page' ) );

            add_action( 'load-' . $page, [ $this, 'salesforce_store_ouath_access' ] );
        } else {
            add_action( 'load-wp-erp_page_erp-crm', [ $this, 'salesforce_store_ouath_access' ] );
        }
    }

    /**
     * Display the salesforce page.
     *
     * @return void
     */
    public function salesforce_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'dashboard';

        if ( $action == 'disconnect' ) {
            delete_option( 'erp_integration_settings_salesforce-integration' );
        }

        $access_token = erp_salesforce_get_access_token();

        if ( ! $access_token ) {
            $salesforce_connect_url = 'https://login.salesforce.com/services/oauth2/authorize?grant_type=authorization_code&response_type=code&display=page&state=' . urlencode( $this->admin_page ) . '&client_id=3MVG9ZL0ppGP5UrC9yYekmd9g_AtHNaHPniiFz5Gt4J0ya5pVs3Axj3d_NxThajZQR2dIENEaKOyRbhyWmX59&redirect_uri=' . urlencode( 'https://api.wperp.com/oauth/salesforce' );
            ?>
            <div class="wrap">
                <h2><?php esc_html_e( 'Integrations', 'erp-pro' ); ?></h2>
                <?php do_action( 'erp_crm_integration_menu', 'salesforce' ); ?>
                <p><?php _e( 'You\'re not connected with your Salesforce account yet. Click on below button to connect.', 'erp-pro' ); ?></p>
                <a href="<?php echo $salesforce_connect_url ?>"><button class="button-secondary"><?php _e( 'Connect Now', 'erp-pro' ); ?></button></a>
            </div>
            <?php
        } else {
            include dirname( __FILE__ ) . '/views/dashboard.php';
        }
    }

    /**
     * Store salesforce oauth access.
     *
     * @return void
     */
    public function salesforce_store_ouath_access() {
        if ( ! isset( $_GET['access_token'] ) ) {
            return;
        }

        $data['instance_url']  = esc_html( $_GET['instance_url'] );
        $data['access_token']  = esc_html( $_GET['access_token'] );
        $data['refresh_token'] = esc_html( $_GET['refresh_token'] );

        $salesforce = new \WeDevs\ERP\Salesforce\Salesforce( $data['instance_url'], $data['access_token'],  $data['refresh_token'] );
        $lists = $salesforce->get_lists();

        foreach ( $lists['listviews'] as $list ) {
            $data['contact_lists'][] = [
                'id' => $list['id'],
                'name' => $list['label'],
            ];
        }

        erp_salesforce_update_options( $data );
        wp_safe_redirect( $this->admin_page );
        exit;
    }
}
