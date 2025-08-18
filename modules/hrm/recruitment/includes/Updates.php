<?php
namespace WeDevs\Recruitment;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Installer Class
 *
 * @package ERP
 */
class Updates {

    use Hooker;

    /** @var array DB updates that need to be run */
    private $update_files = [
        '1.1.0' => 'updates/update-1.1.0.php',
        '1.4.1' => 'updates/update-1.4.1.php',
    ];

    public function __construct() {
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            return;
        }

        $this->action( 'admin_notices', 'show_update_notice' );
        $this->action( 'admin_init', 'do_updates' );
    }

    /**
     * Current active erp modules
     *
     * @since 1.1.9
     *
     * @var array
     */
    private $active_modules = [];

    /**
     * Check if any update is required
     *
     * @since 1.1.0
     *
     * @return boolean
     */
    public function is_update_required() {
        $installed_version = get_option( 'erp-recruitment-version', null );

        // may be it's the first install
        if ( ! $installed_version ) {
            /**
             * Updater was introduced in v1.1.0. Before that there was no 'erp-recruitment-version'
             * in options table. So we don't know if it's fresh install or an update to v1.1.0 or later.
             * first check to an candidate has the status row, if has row then return false else
             * return true
             */
            global $wpdb;
            //get job seeker id
            $query = "SELECT applicant_id
                      FROM {$wpdb->prefix}erp_application
                      WHERE status=0
                      LIMIT 1";
            $jobseekerid = $wpdb->get_var( $query );
            $st  = erp_people_get_meta( $jobseekerid, 'status' );

            if ( is_array($st) && count( $st ) === 0 ) {
                return true;
            }

        } else if ( version_compare( $installed_version, WPERP_REC_VERSION, '<' ) ) {
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
            <p><?php _e( '<strong>WP ERP Recruitment data update is required</strong> &#8211; We need to update your install to the latest version', 'erp-recruitment' ); ?></p>
            <p class="submit"><a href="<?php echo add_query_arg( [ 'erp_recruitment_do_update' => true ], $_SERVER['REQUEST_URI'] ); ?>" class="erp-att-update-btn button-primary"><?php _e( 'Run the updater', 'erp-recruitment' ); ?></a></p>
        </div>

        <script type="text/javascript">
            jQuery( '.erp-att-update-btn' ).click( 'click', function(){
                return confirm( '<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'erp-recruitment' ); ?>' );
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
        if ( isset( $_GET['erp_recruitment_do_update'] ) && $_GET['erp_recruitment_do_update'] ) {
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

        $installed_version = get_option( 'erp-recruitment-version', null );

        $this->set_db_prefix_and_collate();

        // this will use in future release
        foreach ( $this->update_files as $version => $path ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                require_once $path;
                update_option( 'erp-recruitment-version', $version );
            }
        }

        update_option( 'erp-recruitment-version', WPERP_REC_VERSION );

        $this->perform_sql_query();

        $location = remove_query_arg( ['erp_recruitment_do_update'], $_SERVER['REQUEST_URI'] );
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
        define( 'WPERP_RECRUITMENT_DB_PREFIX', $wpdb->prefix );
        define( 'WPERP_RECRUITMENT_DB_COLLATE', $collate );

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
        $table_schema = apply_filters( 'erp-recruitment-updates-table-schema', [] );

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
        $queries = apply_filters( 'erp-recruitment-updates-wpdb-query', [] );

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
        do_action( 'erp-recruitment-updates-end' );
    }
}
