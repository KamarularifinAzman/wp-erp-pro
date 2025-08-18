<?php

namespace WeDevs\Reimbursement\Classes;

/**
 * Admin Pages Handler
 */
class Admin {

    /**
     * Class constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_head', [ $this, 'admin_head' ] );
        add_action( 'erp_acct_locale_script', [ $this, 'locale_script' ] );
        add_action( 'erp_acct_js_hook_loaded', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu() {
        if ( function_exists( 'erp_add_menu' ) ) {
            if ( current_user_can( 'employee' ) ) {
                erp_add_menu( 'hr', array(
                    'title'      => __( 'Reimbursement', 'erp' ),
                    'capability' => 'employee',
                    'slug'       => 'reimbursement',
                    'callback'   => [ $this, 'reimbursement_page' ],
                    'position'   => 190
                ) );
            }
        }
    }

    /**
     * Registers locale script in admin head
     *
     * @since 1.2.6
     *
     * @return void
     */
    public function admin_head() {
        $page    = isset( $_GET['page'] )    ? wp_unslash( $_GET['page'] ) : '';
        $section = isset( $_GET['section'] ) ? wp_unslash( $_GET['section'] ) : '';

        if ( $page === 'erp-hr' ) :
            if ( $section === 'reimbursement' ) : ?>
                <script>
                    window.erpReimbursement = JSON.parse('<?php echo json_encode(
                        apply_filters( 'erp_reimbursement_localized_data', [] )
                    ); ?>');
                </script>
            <?php endif;
        endif;
    }

    /**
     * Script to localize data
     *
     * @since 1.2.7
     *
     * @return void
     */
    public function locale_script() {
        ?>
            <script>
                window.erpReimbursement = JSON.parse('<?php echo json_encode(
                    apply_filters( 'erp_reimbursement_localized_data', [] )
                ); ?>');
            </script>
        <?php
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'erp-reimbursement-admin' );
        wp_enqueue_script( 'erp-reimbursement-admin' );
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function reimbursement_page() {
        ?>
        <script>
            window.erpAcct = {};

            function acct_get_lib(arg) {
                return null;
            }
        </script>
        <?php

        // $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        echo '<div class="wrap"><div id="erp-reimbursement"></div></div>';
    }
}
