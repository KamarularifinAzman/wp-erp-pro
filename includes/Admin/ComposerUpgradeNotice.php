<?php

namespace WeDevs\ERP_PRO\Admin;

class ComposerUpgradeNotice {

    // From these versions we introduced composer v2 both core and pro.
    private $composer_update_in_core_version = '1.12.0';

    /**
     * Class constructor
     */
    public function __construct() {
        // Version check
        if ( $this->need_to_upgrade() ) {
            add_action( 'admin_notices', [ $this, 'activation_notice' ] );
        }
    }


    /**
     * Check if the PHP Composer version needs to be updated.
     *
     * @return bool
     */
    public function need_to_upgrade() {
        if ( class_exists( 'WeDevs_ERP' ) && version_compare( WPERP_VERSION, $this->composer_update_in_core_version, '<' ) ) {
            return true;
        }

        return false;
    }

    /**
     * ERP main plugin upgrade notice
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function activation_notice() {
        if ( ! empty( $_GET['page'] ) && 'erp-license' === $_GET['page'] ) { // phpcs:ignore
            return;
        }

        $screen = get_current_screen();
        if ( 'erp' === $screen->parent_base && current_user_can( 'activate_plugins' ) ) {
            include_once ERP_PRO_TEMPLATE_DIR . '/upgrade-notice.php';
            exit();
        }
    }
}
