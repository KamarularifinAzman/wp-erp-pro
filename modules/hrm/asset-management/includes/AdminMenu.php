<?php
namespace WeDevs\AssetManagement;

use WeDevs\AssetManagement\AssetsListTable;

/**
 * Class AdminMenu
 *
 * @namespace WeDevs\AssetManagement
 */
class AdminMenu {
    /**
     * Class contructor.
     */
    public function __construct() {
        global $current_screen;

        add_action( 'admin_menu', [ $this, 'admin_menu'] );

        if ( isset( $_GET['page'] ) && 'erp-hr-asset' == $_GET['page'] ) {
            add_filter( 'set-screen-option', [ $this, 'erp_asset_set_option' ], 10, 3 );
        }

        if ( isset( $_GET['page'] ) && 'erp-asset-allottment' == $_GET['page'] ) {
            add_filter( 'set-screen-option', [ $this, 'erp_asset_allott_set_option' ], 10, 3 );
        }

        if ( isset( $_GET['page'] ) && 'asset-request' == $_GET['page'] ) {
            add_filter( 'set-screen-option', [ $this, 'erp_asset_request_set_option' ], 10, 3 );
        }
    }

    /**
     * Register the admin menu.
     *
     * @return void
     */
    public function admin_menu() {

        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            $main_hook     = add_menu_page( __( 'Assets', 'erp-pro' ), __( 'Assets', 'erp-pro' ), 'erp_hr_manager', 'erp-hr-asset', [$this, 'assets_page'], 'dashicons-screenoptions' );
            $allott_hook   = add_submenu_page( 'erp-hr-asset', __( 'Allotments', 'erp-pro' ), __( 'Allotments', 'erp-pro' ), 'erp_hr_manager', 'erp-asset-allottment', [$this, 'allotment_page'] );
            $request_hook  = add_submenu_page( 'erp-hr-asset', __( 'Requests', 'erp-pro' ), __( 'Requests', 'erp-pro' ), 'erp_hr_manager', 'asset-request', [$this, 'request_page'] );

            add_action( "load-$main_hook", [ $this, 'asset_table_screen_options' ] );
            add_action( "load-$allott_hook", [ $this, 'allott_table_screen_options' ] );
            add_action( "load-$request_hook", [ $this, 'request_table_screen_options' ] );
        } else {
            erp_add_menu( 'hr', array(
                'title'         =>  __( 'Assets', 'erp-pro' ),
                'slug'          =>  'asset',
                'capability'    => 'erp_hr_manager',
                'callback'      =>  [ $this, 'assets_page' ],
                'position'      =>  35
            ) );

            erp_add_submenu( 'hr', 'asset', array(
                'title'         =>   __( 'Assets', 'erp-pro' ),
                'slug'          =>   'asset',
                'capability'    =>  'erp_hr_manager',
                'callback'      =>  [ $this, 'assets_page' ],
                'position'      =>  1
            ) );
            erp_add_submenu( 'hr', 'asset', array(
                'title'         =>  __( 'Allotments', 'erp-pro' ),
                'slug'          =>  'asset-allottment',
                'capability'    =>  'erp_hr_manager',
                'callback'      =>  [ $this, 'allotment_page' ],
                'position'      =>  5,
            ) );

            erp_add_submenu( 'hr', 'asset', array(
                'title'         =>  __( 'Requests', 'erp-pro' ),
                'slug'          =>  'asset-request',
                'capability'    =>  'erp_hr_manager',
                'callback'      =>  [ $this, 'request_page' ],
                'position'      =>  10,
            ) );

            erp_add_submenu( 'hr', 'report', array(
                'title'         =>  __( 'Assets', 'erp' ),
                'capability'    =>  'erp_hr_manager',
                'slug'          =>  'report&type=asset-report',
                'callback'      =>  '',
                'position'      =>  5,
            ) );

            $sub_section    =   isset( $_GET['sub-section'] ) ? $_GET['sub-section'] : '';
            switch ( $sub_section ) {
                case 'asset-allottment':
                    add_action( "load-wp-erp_page_erp-hr", [ $this, 'allott_table_screen_options' ] );
                    break;
                case 'asset-request':
                    add_action( "load-wp-erp_page_erp-hr", [ $this, 'request_table_screen_options' ] );
                    break;
                default:
                    add_action( "load-wp-erp_page_erp-hr", [ $this, 'asset_table_screen_options' ] );
                    break;
             }
            // add_action( "load-wp-erp_page_erp-hr", [ $this, 'asset_table_screen_options' ] );
            // add_action( "load-wp-erp_page_erp-hr", [ $this, 'allott_table_screen_options' ] );
            // add_action( "load-wp-erp_page_erp-hr", [ $this, 'request_table_screen_options' ] );
        }
    }

    /**
     * Display the Assets page.
     *
     * @return void
     */
    public function assets_page() {

        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
        $id     = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';

        switch ( $action ) {

            case 'view':
                include WPERP_ASSET_VIEWS . '/assets-single.php';
            break;

            default:
                include WPERP_ASSET_VIEWS . '/assets.php';
            break;
        }

    }

    /**
     * Allotment Page
     *
     * @return void
     */
    public function allotment_page() {
        include WPERP_ASSET_VIEWS . '/allotment.php';
    }

    /**
     * Request Page
     *
     * @return void
     */
    public function request_page() {
        include WPERP_ASSET_VIEWS . '/requests.php';
    }

    /**
     * Asset Table Screen Options
     *
     * @return void
     */
    public function asset_table_screen_options() {

        global $myListTable;

        $option = 'per_page';

        $args = [
            'label'   => __( 'Assets', 'erp-pro' ),
            'default' => 20,
            'option'  => 'erp_assets_per_page'
        ];

        add_screen_option( $option, $args );

        $myListTable = new AssetsListTable();
    }

    /**
     * Request Table Screen Options
     *
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function erp_asset_set_option( $status, $option, $value ) {
        if ( 'erp_assets_per_page' == $option ) {
            return $value;
        }

        return $status;
    }

    /**
     * Allottment Table Screen Options
     *
     * @return void
     */
    public function allott_table_screen_options() {

        global $myListTable;

        $option = 'per_page';

        $args = [
            'label'   => __( 'Allottment', 'erp-pro' ),
            'default' => 20,
            'option'  => 'erp_assets_allott_per_page'
        ];

        add_screen_option( $option, $args );

        $myListTable = new AllottmentListTable;
    }

    /**
     * Allott Table Screen Options
     *
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function erp_asset_allott_set_option( $status, $option, $value ) {
        if ( 'erp_assets_allott_per_page' == $option ) {
            return $value;
        }

        return $status;
    }

    /**
     * Request Table Screen Options
     *
     * @return void
     */
    public function request_table_screen_options() {

        global $myListTable;

        $option = 'per_page';

        $args = [
            'label'   => __( 'Requests', 'erp-pro' ),
            'default' => 20,
            'option'  => 'erp_assets_request_per_page'
        ];

        add_screen_option( $option, $args );

        $myListTable = new RequestListTable;
    }

    /**
     * Request Table Screen Options
     *
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function erp_asset_request_set_option( $status, $option, $value ) {
        if ( 'erp_assets_request_per_page' == $option ) {
            return $value;
        }

        return $status;
    }
}
