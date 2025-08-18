<?php
namespace WeDevs\ERP\WooCommerce;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Plugin updater class
 *
 * @since 1.3.2
 */
class Updates {

    use Hooker;

    /**
     * Update version references
     *
     * @var array
     */
    private $update_files = [
        '1.3.2' => 'updates/update-1.3.2.php',
        '1.3.5' => 'updates/update-1.3.5.php',
    ];

    public function __construct() {
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            return;
        }

        $this->action( 'admin_notices', 'show_update_notice' );
        $this->action( 'admin_init', 'do_updates' );
    }

    /**
     * Check if any update is required
     *
     * @return boolean
     */
    public function is_update_required() {
        $installed_version = get_option( 'erp-woocommerce-version', null );

        if ( ! $installed_version ) {
            $installed_version = '0.0.0';
        }

        // we'll use this in future release
         if ( version_compare( $installed_version, WPERP_WOOCOMMERCE_VERSION, '<' ) ) {
             return true;
         }

        return false;
    }

    /**
     * Show update notice
     *
     * @return void
     */
    public function show_update_notice() {
        if ( ! current_user_can( 'update_plugins' ) || ! $this->is_update_required() ) {
            return;
        }

        ?>
            <div id="message" class="updated">
                <p><?php _e( '<strong>WP ERP - WooCommerce data update is required</strong> &#8211; We need to update your install to the latest version', 'erp-pro' ); ?></p>
                <p class="submit"><a href="<?php echo add_query_arg( [ 'erp_woocommerce_do_update' => true ], $_SERVER['REQUEST_URI'] ); ?>" class="erp-woo-update-btn button-primary"><?php _e( 'Run the updater', 'erp-pro' ); ?></a></p>
            </div>

            <script type="text/javascript">
                jQuery( '.erp-woo-update-btn' ).click( 'click', function(){
                    return confirm( '<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'erp-pro' ); ?>' );
                });
            </script>
        <?php
    }

    /**
     * If query found in url then start updates
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function do_updates() {
        if ( isset( $_GET['erp_woocommerce_do_update'] ) && $_GET['erp_woocommerce_do_update'] ) {
            $this->perform_updates();
        }
    }

    /**
     * Perform updates
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function perform_updates() {
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            return;
        }

        if ( ! $this->is_update_required() ) {
            return;
        }

        $installed_version = get_option( 'erp-woocommerce-version', null );

        // this will use in future release
        foreach ( $this->update_files as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                require_once $path;
                update_option( 'erp-woocommerce-version', $version );
            }
        }

        // update current version
        update_option( 'erp-woocommerce-version', WPERP_WOOCOMMERCE_VERSION );

        $location = remove_query_arg( ['erp_woocommerce_do_update'], $_SERVER['REQUEST_URI'] );
        wp_redirect( $location );
        exit();
    }

}
