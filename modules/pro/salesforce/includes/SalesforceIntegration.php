<?php
namespace WeDevs\ERP\Salesforce;

use WeDevs\ERP\Integration;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Salesforce Integration
 */
class SalesforceIntegration extends Integration {

    use Hooker;

    /**
     * Class constructor.
     */
    function __construct() {
        $this->id          = 'salesforce-integration';
        $this->title       = __( 'Salesforce', 'wp-erp' );
        $this->description = __( 'Salesforce Add-on for WP-ERP.', 'wp-erp' );

        $this->init_settings();

        add_action( 'erp_admin_field_api_status', array( $this, 'api_status' ) );

        parent::__construct();
    }

    /**
     * Get the title of this setting.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Get the description of this setting.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Get the fields of this setting.
     *
     * @return array
     */
    public function init_settings() {

        $this->form_fields = [
            [
                'type'  => 'html',
                'id'    => 'erp-saleforce-integration-status',
                'value' => $this-> api_status(),
            ],
        ];

        return $this->form_fields;
    }

    /**
     * Display API settings view.
     *
     * @return void
     */
    public function api_status() {
        $access_token = erp_salesforce_get_access_token();

        ob_start(); ?>

        <div class="wperp-form-group">
            <label for="erp-salesforce-integration"><?php _e( 'Status', 'erp-pro' ) ?></label>

            <span class="forminp forminp-text" id="erp-salesforce-integration">
                <?php
                if ( $access_token ) {
                    $url = admin_url( 'admin.php?page=erp-crm&section=integration&sub-section=salesforce&action=disconnect' );
                ?>
                    <p><strong><?php _e( 'Connected', 'erp-pro' ); ?></strong></p>
                    <a href="<?php echo $url; ?>" class="wperp-btn wperp-btn-danger"><?php _e( 'Disconnect' ); ?></a>
                <?php
                } else {
                    $url = 'https://login.salesforce.com/services/oauth2/authorize?grant_type=authorization_code&response_type=code&display=page&state=' . urlencode( admin_url( 'admin.php?page=erp-crm&section=integration&sub-section=salesforce&action' ) ) . '&client_id=3MVG9ZL0ppGP5UrC9yYekmd9g_AtHNaHPniiFz5Gt4J0ya5pVs3Axj3d_NxThajZQR2dIENEaKOyRbhyWmX59&redirect_uri=' . urlencode( 'https://api.wperp.com/oauth/salesforce' );
                ?>
                    <p><strong><?php _e( 'Not Connected', 'erp-pro' ); ?></strong></p>
                    <a href="<?php echo $url; ?>" class="wperp-btn btn--outline"><?php _e( 'Connect' ); ?></a>
                <?php
                }
                ?>
            </span>
        </div>

        <?php return ob_get_clean();
    }
}
