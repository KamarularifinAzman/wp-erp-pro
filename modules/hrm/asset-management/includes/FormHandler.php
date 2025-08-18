<?php
namespace WeDevs\AssetManagement;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 */
class FormHandler {

    public function __construct() {

        add_action( 'load-toplevel_page_erp-hr-asset', [ $this, 'asset_bulk_action' ] );
        add_action( 'load-assets_page_erp-asset-allottment', [ $this, 'asset_allottment_page' ] );
        add_action( 'load-assets_page_asset-request', [ $this, 'asset_request_page' ] );
        add_action( 'load-hr-management_page_erp-hr-reporting', [ $this, 'asset_hr_reporting_page' ] );

        if ( version_compare( WPERP_VERSION , '1.4.0', '>='  ) ) {
            $this->erp_asset_bulk_action();
        }
    }

    /**
     * Bulk action
     *
     * @return void
     */
    public function erp_asset_bulk_action() {
        $sub_section    =   isset( $_GET['sub-section'] ) ? $_GET['sub-section'] : '';

        switch ( $sub_section ) {
            case 'asset-allottment':
                add_action( "load-wp-erp_page_erp-hr", [ $this, 'asset_allottment_page' ] );
                break;
            case 'asset-request':
                add_action( "load-wp-erp_page_erp-hr", [ $this, 'asset_request_page' ] );
                break;
            default:
                add_action( "load-wp-erp_page_erp-hr", [ $this, 'asset_bulk_action' ] );
                break;
        }
    }
    /**
     * Check is current page actions
     *
     * @since 1.0
     *
     * @param  integer $page_id
     * @param  integer $bulk_action
     *
     * @return boolean
     */
    public function verify_current_page_screen( $page_id, $bulk_action ) {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return false;
        }

        if ( $_GET['page'] != $page_id ) {
            return false;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $bulk_action ) ) {
            return false;
        }

        return true;
    }

    /**
     * Asset Bulk Action
     *
     * @since 1.0
     */
    public function asset_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-hr-asset', 'bulk-assets' ) && ! $this->verify_current_page_screen( 'erp-hr', 'bulk-assets' ) ) {
            return;
        }

        $assets_table = new \WeDevs\AssetManagement\AssetsListTable();
        $action       = $assets_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( ['_wp_http_referer', '_wpnonce'], wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'filter_category':

                    $redirect = remove_query_arg( ['filter_category', 'action', 'action2'], $redirect );

                    wp_redirect( $redirect );
                exit;

                case 'asset_delete':

                    if ( isset( $_GET['asset_id'] ) && !empty( $_GET['asset_id'] ) ) {
                        foreach ( $_GET['asset_id'] as $id ) {
                            erp_hr_asset_remove( $id );
                        }
                    }

                    $redirect = remove_query_arg( ['asset_id'], $redirect );

                    wp_redirect( $redirect );
                exit;

                default:
                exit;
            }
        }
    }

    /**
     * Form handler for HR Reporting page
     *
     * @return void
     */
    public function asset_hr_reporting_page() {

        if ( !isset( $_REQUEST['submit-asset-category'] ) ) {
            return;
        }

        if ( !isset( $_REQUEST['asset-report-nonce'] ) || !wp_verify_nonce( $_REQUEST['asset-report-nonce'], 'asset-hr-reporting' ) ) {
            die( __( 'Not allowed!', 'erp-pro' ) );
        }

        $redirect = remove_query_arg( ['_wp_http_referer', 'asset-report-nonce', 'submit-asset-category'], wp_unslash( $_SERVER['REQUEST_URI'] ) );

        wp_redirect( $redirect );
    }

    /**
     * Manage bulk actions on allottment list table
     *
     * @return bool
     */
    public function asset_allottment_page() {

        if ( ! $this->verify_current_page_screen( 'erp-asset-allottment', 'bulk-allottments' ) && ! $this->verify_current_page_screen( 'erp-hr', 'bulk-allottments' ) ) {
            return;
        }
        $assets_table = new \WeDevs\AssetManagement\AllottmentListTable();
        $action       = $assets_table->current_action();
        if ( $action ) {

            $redirect = remove_query_arg( ['_wp_http_referer', '_wpnonce'], wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {
                case 'allottment_delete':

                    if ( isset( $_GET['allottment_id'] ) && !empty( $_GET['allottment_id'] ) ) {
                        foreach ( $_GET['allottment_id'] as $id ) {
                            $return = erp_hr_asset_allottment_remove( intval( $id ) );
                        }
                    }

                    $redirect = remove_query_arg( ['allottment_id', 'action', 'action2'], $redirect );

                    wp_redirect( $redirect );
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Manage bulk actions on Request list table
     *
     * @return bool
     */
    public function asset_request_page() {

        if ( ! $this->verify_current_page_screen( 'asset-request', 'bulk-requests' )  && ! $this->verify_current_page_screen( 'erp-hr', 'bulk-requests' ) ) {
            return;
        }

        $request_table = new \WeDevs\AssetManagement\RequestListTable();
        $action       = $request_table->current_action();
        if ( $action ) {

            $redirect = remove_query_arg( ['_wp_http_referer', '_wpnonce'], wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'request_reject':

                    if ( isset( $_GET['request_id'] ) && !empty( $_GET['request_id'] ) ) {
                        foreach ( $_GET['request_id'] as $id ) {
                            $return = erp_asset_request_reject( intval( $id ) );
                        }
                    }

                    $redirect = remove_query_arg( ['request_id', 'action', 'action2'], $redirect );

                    wp_redirect( $redirect );
                    break;

                default:
                    break;
            }
        }
    }
}
