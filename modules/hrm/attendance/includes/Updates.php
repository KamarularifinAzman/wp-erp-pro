<?php
namespace WeDevs\Attendance;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Plugin updater class
 *
 * @since 1.1.0
 */
class Updates {

    use Hooker;

    /**
     * Update version references
     *
     * @since 1.1.0
     *
     * @var array
     */
    private $update_files = [
        '1.1.0' => 'updates/update-1.1.0.php',
        '1.1.3' => 'updates/update-1.1.3.php',
        '2.0.0' => 'updates/update-2.0.0.php',
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
     * @since 1.1.0
     *
     * @return boolean
     */
    public function is_update_required() {
        $installed_version = get_option( 'erp-attendance-version', null );

        if ( ! $installed_version ) {
            return false;
        }

        // we'll use this in future release
         if ( version_compare( $installed_version, WPERP_ATTEND_VERSION, '<' ) ) {
             return true;
         }

        return false;
    }

    /**
     * Show update notice
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function show_update_notice() {
        if ( ! current_user_can( 'update_plugins' ) || ! $this->is_update_required() ) {
            return;
        }

        ?>
            <div id="message" class="updated">
                <p><?php _e( '<strong>WP ERP Attendance data update is required</strong> &#8211; We need to update your install to the latest version', 'erp-pro' ); ?></p>
                <p class="submit"><a href="<?php echo add_query_arg( [ 'erp_attendace_do_update' => true ], $_SERVER['REQUEST_URI'] ); ?>" class="erp-att-update-btn button-primary"><?php _e( 'Run the updater', 'erp-pro' ); ?></a></p>
            </div>

            <script type="text/javascript">
                jQuery( '.erp-att-update-btn' ).click( 'click', function(){
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
        if ( isset( $_GET['erp_attendace_do_update'] ) && $_GET['erp_attendace_do_update'] ) {
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

        $installed_version = get_option( 'erp-attendance-version', null );

        $this->set_db_prefix_and_collate();

        // this will use in future release
        foreach ( $this->update_files as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                require_once $path;
                update_option( 'erp-attendance-version', $version );
            }
        }

        // update current version
        update_option( 'erp-attendance-version', WPERP_ATTEND_VERSION );

        $this->perform_sql_query();

        $location = remove_query_arg( ['erp_attendace_do_update'], $_SERVER['REQUEST_URI'] );
        wp_redirect( $location );
        exit();
    }

    /**
     * Set db prefix and collate into two global constants
     *
     * @since 1.1.0
     *
     * @return void
     */
    private function set_db_prefix_and_collate() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        // we'll hook many sql statements and we don't want to
        // re-calculate collate again and again
        define( 'WPERP_ATTEND_DB_PREFIX', $wpdb->prefix );
        define( 'WPERP_ATTEND_DB_COLLATE', $collate );

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    /**
     * Execute all db queries during installation
     *
     * @since 1.1.0
     *
     * @return void
     */
    private function perform_sql_query() {
        global $wpdb;

        /**
         * Add mysql query for table schema during update
         *
         * @since 1.1.0
         *
         * @param array $schema query strings
         */
        $table_schema = apply_filters( 'erp-attendance-updates-table-schema', [] );

        if ( ! empty( $table_schema ) ) {
            foreach ( $table_schema as $schema ) {
                dbDelta( $schema );
            }
        }

        /**
         * Add mysql queries which are not table schema during update
         *
         * @since 1.1.0
         *
         * @param array $queries query strings
         */
        $queries = apply_filters( 'erp-attendance-updates-wpdb-query', [] );

        if ( ! empty( $queries ) ) {
            foreach ( $queries as $query ) {
                $wpdb->query( $query );
            }
        }

        /**
         * Action hook after perform db queries
         *
         * @since 1.1.0
         */
        do_action( 'erp-attendance-updates-end' );
    }

}
