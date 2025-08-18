<?php
namespace WeDevs\ERP_PRO\Updates;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Plugin updater class
 *
 * @since 1.0.1
 */
class Updater {

    use Hooker;

    /**
     * Update version references
     *
     * @var array
     */
    private $update_files = [
        '1.0.1' => 'Update-1.0.1.php',
        '1.1.0' => 'Update-1.1.0.php',
        '1.2.0' => 'Update-1.2.0.php',
        '1.2.8' => 'Update-1.2.8.php',
    ];

    /**
     * Update constructor.
     */
    public function __construct() {
        $this->load_background_process_files();

        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            return;
        }

        $this->action( 'admin_notices', 'show_update_notice' );
        $this->action( 'admin_init', 'do_updates' );
    }

    /**
     * Instantiate all background process files related to update process here.
     *
     * @since 1.0.1
     * @return void
     */
    private function load_background_process_files() {
        if ( ! class_exists( 'Recruitment_Experience_Type_Migrator', false ) ) {
            require_once ERP_PRO_INC . '/Updates/BP/Recruitment_Experience_Type_Migrator.php';
        }
    }

    /**
     * Check if any update is required
     *
     * @since 1.0.1
     * @return boolean
     */
    private function is_update_required() {
        $installed_version = get_option( 'erp-pro-plugin-version', null );

        if ( ! $installed_version ) {
            $installed_version = '1.0.0';
        }

        // we'll use this in future release
        if ( version_compare( $installed_version, ERP_PRO_PLUGIN_VERSION, '<' ) && array_key_exists( ERP_PRO_PLUGIN_VERSION, $this->update_files ) ) {
            return true;
        }

        // check we need to update to current version
        if ( version_compare( $installed_version, ERP_PRO_PLUGIN_VERSION, '<' ) ) {
            // update current version, coz no update script found
            update_option( 'erp-pro-plugin-version', ERP_PRO_PLUGIN_VERSION );
        }

        return false;
    }

    /**
     * Show update notice
     *
     * @since 1.0.1
     * @return void
     */
    public function show_update_notice() {
        if ( ! current_user_can( 'update_plugins' ) || ! $this->is_update_required() ) {
            return;
        }

        ?>
        <div id="message" class="updated">
            <p><?php _e( '<strong>WP ERP PRO data update is required</strong> &#8211; We need to update your install to the latest version', 'erp-pro' ); ?></p>
            <p class="submit"><a href="<?php echo add_query_arg( [ 'wp_erp_pro_do_update' => true ], $_SERVER['REQUEST_URI'] ); ?>" class="wp-erp-pro-update-btn button-primary"><?php _e( 'Run the updater', 'erp-pro' ); ?></a></p>
        </div>

        <script type="text/javascript">
            jQuery( '.wp-erp-pro-update-btn' ).click( 'click', function() {
                return confirm( '<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'erp-pro' ); ?>' );
            });
        </script>
        <?php
    }

    /**
     * If query found in url then start updates
     *
     * @since 1.1.0
     * @return void
     */
    public function do_updates() {
        if ( isset( $_GET['wp_erp_pro_do_update'] ) && $_GET['wp_erp_pro_do_update'] ) {
            $this->perform_updates();
        }
    }

    /**
     * Perform updates
     *
     * @since 1.0.1
     * @return void
     */
    private function perform_updates() {
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            return;
        }

        if ( ! $this->is_update_required() ) {
            return;
        }

        $installed_version = get_option( 'erp-pro-plugin-version', null );

        // this will use in future release
        foreach ( $this->update_files as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                require_once $path;
                update_option( 'erp-pro-plugin-version', $version );
            }
        }

        // update current version
        update_option( 'erp-pro-plugin-version', ERP_PRO_PLUGIN_VERSION );

        $location = remove_query_arg( ['wp_erp_pro_do_update'], $_SERVER['REQUEST_URI'] );
        wp_redirect( $location );
        exit();
    }
}
