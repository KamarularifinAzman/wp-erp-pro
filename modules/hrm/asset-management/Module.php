<?php
namespace WeDevs\ERP_PRO\HRM\AssetManagement;

// don't call the file directly
use WeDevs\AssetManagement\Log;
use WeDevs\AssetManagement\AdminMenu;
use WeDevs\AssetManagement\FormHandler;
use WeDevs\AssetManagement\AjaxHandler;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs ERP Asset Main Class
 */
class Module {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.2.0';

    /**
     * Initializes the WeDevs_ERP_Asset class
     *
     * Checks for an existing WeDevs_ERP_Asset instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.0
     * @return mixed | bool
     */
    public static function init() {

        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor for the WeDevs_ERP_Asset class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0
     * @return void
     */
    private function __construct() {

        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_asset_management', array( $this, 'activate' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_asset_management', array( $this, 'deactivate' ) );

        add_action( 'erp_hrm_loaded', [$this, 'init_plugin'] );
    }

    /**
     * Execute if ERP main is installed
     *
     * @since 1.0
     * @return void
     */
    public function init_plugin() {

        /* Define constats */
        $this->define_constants();

        /* Include files */
        $this->includes();

        /* Instantiate classes */
        $this->init_classes();

        /* Initialize the action hooks */
        $this->init_actions();

        /* Initialize the filter hooks */
        $this->init_filters();
    }

    /**
     * Placeholder for activation function
     *
     * @since 1.0
     * @return void
     */
    public function activate() {
        $this->create_table();          // Create default tables when plugin activates
        $this->setup_default_email();   // Sets up default email templates when plugin activates
    }

    /**
     * Placeholder for creating tables while activationg plugin
     *
     * @since 1.0
     * @return void
     */
    private function create_table() {

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

        /* Table Schemas */
        $table_schema = [

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_hr_assets (
              `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
              `parent` BIGINT(20) NOT NULL,
              `category_id` BIGINT(20) DEFAULT NULL,
              `item_group` TEXT DEFAULT NULL,
              `asset_type` TEXT NOT NULL,
              `item_code` VARCHAR(255) DEFAULT NULL,
              `manufacturer` TEXT DEFAULT NULL,
              `model_no` TEXT DEFAULT NULL,
              `price` DECIMAL(10,2) DEFAULT NULL,
              `date_reg` DATE NOT NULL,
              `date_expiry` DATE DEFAULT NULL,
              `date_warranty` DATE DEFAULT NULL,
              `allottable` VARCHAR(2) DEFAULT NULL,
              `item_serial` TEXT DEFAULT NULL,
              `item_desc` TEXT NOT NULL,
              `status` VARCHAR(255) NOT NULL,
              `date_dissmissed` DATE DEFAULT NULL,
              `dissmiss_reason` VARCHAR(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `id` (`id`)
            ) $collate;",
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_hr_assets_history (
              `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
              `category_id` BIGINT(20) NOT NULL,
              `item_group` BIGINT(20) NOT NULL,
              `item_id` BIGINT(20) NOT NULL,
              `allotted_to` BIGINT(20) NOT NULL,
              `is_returnable` VARCHAR (3) NOT NULL,
              `date_given` DATE DEFAULT NULL,
              `date_return_proposed` DATE DEFAULT NULL,
              `date_return_real` DATE DEFAULT NULL,
              `date_request_return` DATE DEFAULT NULL,
              `status` VARCHAR(255) NOT NULL,
              `return_note` TEXT DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `id` (`id`)
            ) $collate;",
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_hr_assets_request (
              `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
              `user_id` BIGINT(20) NOT NULL,
              `item_group` BIGINT(20) DEFAULT NULL,
              `item_id` BIGINT(20) DEFAULT NULL,
              `given_item_id` BIGINT(20) DEFAULT NULL,
              `allott_id` BIGINT(20) DEFAULT NULL,
              `request_desc` TEXT NOT NULL,
              `not_in_list` VARCHAR (3) NOT NULL,
              `date_requested` DATE DEFAULT NULL,
              `date_replied` DATE DEFAULT NULL,
              `reply_msg` TEXT DEFAULT NULL,
              `status` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `id` (`id`)
            ) $collate;",
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_hr_assets_category (
              id BIGINT(20) NOT NULL AUTO_INCREMENT,
              `cat_name` TEXT NOT NULL,
              PRIMARY KEY (`id`),
              KEY `id` (`id`)
            ) $collate;"
        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }

    /**
     * Setup default email templates
     *
     * @since 1.0
     * @return void
     */
    public function setup_default_email() {

        // Asset Request
        $request = [
            'subject' => 'New Asset Request',
            'heading' => 'New Asset Request',
            'body'    => 'Dear {employee_name},
You have requested a new assset.'
        ];

        update_option( 'erp_email_settings_employee-asset-request', $request );

        // Asset Request Approve
        $approve = [
            'subject' => 'Asset Request Approved',
            'heading' => 'Asset Request Approved',
            'body'    => 'Dear {employee_name},
This is a remainder that you delayed to return the asset. It was supposed to return on {date_return_proposed}.'
        ];

        update_option( 'erp_email_settings_employee-asset-approve', $approve );

        // Asset Request Approve
        $reject = [
            'subject' => 'Asset Request Rejected',
            'heading' => 'Asset Request Rejected',
            'body'    => 'Dear {employee_name},
Your asset request was rejected.'
        ];

        update_option( 'erp_email_settings_employee-asset-reject', $reject );

        // Asset Request Overdue
        $overdue = [
            'subject' => 'Asset Overdue Notice',
            'heading' => 'Asset Overdue Notice',
            'body'    => 'Dear {employee_name},
This is a remainder that your asset return date has passed.'
        ];

        update_option( 'erp_email_settings_employee-asset-overdue', $overdue );
    }

    /**
     * Placeholder for deactivation function
     *
     * @since 1.0
     * @return void
     */
    public function deactivate() {

    }

    /**
     * Define Add-on constants
     *
     * @since 1.0
     * @return void
     */
    public function define_constants() {

        define( 'WPERP_ASSET_VERSION', $this->version );                       // Plugin Version
        define( 'WPERP_ASSET_FILE', __FILE__ );                                // Plugin Main Folder Path
        define( 'WPERP_ASSET_PATH', dirname( WPERP_ASSET_FILE ) );             // Parent Directory Path
        define( 'WPERP_ASSET_INCLUDES', WPERP_ASSET_PATH . '/includes' );      // Include Folder Path
        define( 'WPERP_ASSET_URL', plugins_url( '', WPERP_ASSET_FILE ) );      // URL Path
        define( 'WPERP_ASSET_ASSETS', WPERP_ASSET_URL . '/assets' );           // Asset Folder Path
        define( 'WPERP_ASSET_VIEWS', WPERP_ASSET_PATH . '/views' );            // View Folder Path
        define( 'WPERP_ASSET_JS_TMPL', WPERP_ASSET_VIEWS . '/js-templates' );  // JS Template Folder Path
    }

    /**
     * Include the required files
     *
     * @since 1.0
     * @since 1.1.3 Included assets log class
     *
     * @return void
     */
    public function includes() {
        include WPERP_ASSET_INCLUDES . '/functions-assets.php';
    }

    /**
     * Instantiate classes
     *
     * @since 1.0
     * @since 1.1.3 Instanciated assets log class
     *
     * @return void
     */
    public function init_classes() {
        // Admin Menu Class
        new AdminMenu();

        // Form Handler Class
        new FormHandler();

        // Ajax Handler Class
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new AjaxHandler();
        }

        // Audit Logger Class
        new Log();
    }

    /**
     * Initializes action hooks
     *
     * @since 1.0
     *
     * @return  void
     */
    public function init_actions() {
        add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
        add_action( 'admin_footer', [ $this, 'admin_js_templates' ] );
        add_action( 'erp_hr_employee_single_tabs', [ $this, 'get_employee_single_tab' ] );
        add_action( 'erp_daily_scheduled_events', [ $this, 'erp_asset_notify_overdue' ] );
    }

    /**
     * Initializes action filters
     *
     * @since 1.0
     *
     * @return  void
     */
    public function init_filters() {
        add_filter( 'erp_hr_reports', [ $this, 'asset_report' ] );
        add_filter( 'erp_hr_reporting_pages', [ $this, 'asset_reporting_page' ], 10, 2 );
        add_filter( 'erp_hr_email_classes', [ $this, 'asset_email_classes' ] );
        add_filter( 'erp_hr_employee_request_types', [ $this, 'add_request_type' ] );
        add_filter( 'erp_hr_employee_pending_request_count', [ $this, 'get_pending_request_count' ] );
        add_filter( 'erp_hr_get_employee_asset_requests', [ $this, 'get_asset_requests' ] );
    }

    /**
     * Register Script
     *
     * @since 1.0
     * @return mixed | void
     */
    public function register_scripts( $hook ) {

        wp_enqueue_script( 'asset-main-script', WPERP_ASSET_ASSETS . '/js/assets.js', ['jquery', 'jquery-ui-datepicker'] );
        wp_enqueue_style( 'asset-main-style', WPERP_ASSET_ASSETS . '/css/assets.css' );
        if ( isset( $_GET['page'] ) ) {
            $cur_page = sanitize_text_field( $_GET['page'] );
            if ( strpos( $cur_page, 'erp' ) !== false ) {
                wp_enqueue_script( 'erp-sweetalert' );
                wp_enqueue_style( 'erp-sweetalert' );
            }
        }

        $localize_scripts = [
            'nonce'                 => wp_create_nonce( 'wp-erp-asset' ),
            'confirm'               => __( 'Are you sure?', 'erp-pro' ),
            'assetDeleteConfirm'    => __( 'Your all items and their history in this group will be deleted.', 'erp-pro' ),
            'requestDeleteConfirm'  => __( 'The request will be deleted.', 'erp-pro' ),
            'catCantDelete'         => __( 'Items exist under this category', 'erp-pro' ),
            'assetReturn'           => __( 'Are you sure to return?', 'erp-pro' ),
            'singleDeleteConfirm'   => __( 'This item and all its history will be deleted.', 'erp-pro' ),
            'singleDismissConfirm'  => __( 'Dismissed means your item is lost, or stolen or in irreparable condition etc. and can\'t be used anymore.', 'erp-pro' ),
            'delete'                => __( 'Delete', 'erp-pro' ),
            'deleted'               => __( 'Deleted!', 'erp-pro' ),
            'dismissed'             => __( 'Dismissed!', 'erp-pro' ),
            'restored'              => __( 'Restored!', 'erp-pro' ),
            'dismissConfirmBtn'     => __( 'Yes, dissmiss it!', 'erp-pro' ),
            'deleteConfirmBtn'      => __( 'Yes, delete', 'erp-pro' ),
            'dismissCancelBtn'      => __( 'No, cancel!', 'erp-pro' ),
            'deleteConfirmMsg'      => __( 'Your item has been deleted.', 'erp-pro' ),
            'dismissConfirmMsg'     => __( 'Your item has been dissmissed.', 'erp-pro' ),
            'restoreConfirmMsg'     => __( 'The request has been restored.', 'erp-pro' ),
            'returnReqOkBtn'        => __( 'Yes, Return', 'erp-pro' ),
            'returnReqCancelBtn'    => __( 'No, Keep', 'erp-pro' ),
            'returnReqTitle'        => __( 'Request Return', 'erp-pro' ),
            'returnReqConf'         => __( 'Requested', 'erp-pro' ),
            'returnReqConfMsg'      => __( 'Return of the item requested successfully', 'erp-pro' ),
            'returnReqMsg'          => __( 'Are you sure to return the item?', 'erp-pro' ),
            'error'                 => __( 'Error', 'erp-pro' ),
            'empty_asset'           => erp_hr_get_empty_asset(),
            'emptyAllot'            => erp_asset_get_allottment_empty_data(),
            'popup'                 => [
                        'titleNew'       => __( 'New Asset', 'erp-pro' ),
                        'submitNew'      => __( 'Save Asset', 'erp-pro' ),
                        'titleEdit'      => __( 'Edit Asset', 'erp-pro' ),
                        'submitEdit'     => __( 'Save Changes', 'erp-pro' ),
                        'titleCategory'  => __( 'New Category', 'erp-pro' ),
                        'submitCategory' => __( 'Save Category', 'erp-pro' ),
                        'editCategory'   => __( 'Edit Category', 'erp-pro' ),
                        'addAnoterItem'  => __( 'Add Another Item', 'erp-pro' ),
                        'deleteItem'     => __( 'Delete', 'erp-pro' ),
                'allot'            => [
                            'titleNew'    => __( 'New Allotment', 'erp-pro' ),
                            'submitNew'   => __( 'Allot', 'erp-pro' ),
                            'titleEdit'   => __( 'Edit Allotment', 'erp-pro' ),
                            'submitEdit'  => __( 'Save Changes', 'erp-pro' ),
                ],
                'approve'        => [
                    'title'  => __( 'Approve Request', 'erp-pro' ),
                    'submit' => __( 'Approve', 'erp-pro' )
                ],
                'reject'         => [
                    'title'  => __( 'Reject Request', 'erp-pro' ),
                    'submit' => __( 'Reject', 'erp-pro' )
                ],
                'undo'           => [
                    'message' => __( 'Doing this will remove all previous actions for this request', 'erp-pro' ),
                    'confirmBtn'  => __( 'Yes, Undo!', 'erp-pro' )
                ],
                'disapprove' => [
                    'confirmBtn' => __( 'Yes, disapprove!', 'erp-pro' )
                ],
                'request'    => [
                    'request'           => __( 'Request', 'erp-pro' ),
                    'requestConfirmMsg' => __( 'Item requested successfully', 'erp-pro' )
                ],
                'asset'      => [
                    'added' => __( 'Added', 'erp-pro' ),
                    'addedMsg' => __( 'Asset added successfully', 'erp-pro' )
                ],
                'rejected'   => [
                    'rejected'    => __( 'Rejected', 'erp-pro' ),
                    'rejectedMsg' => __( 'The request has been rejected', 'erp-pro')
                ],
                'approved'   => [
                    'approved'    => __( 'Approved', 'erp-pro' ),
                    'approvedMsg' => __( 'The request has been approved', 'erp-pro')
                ],
                'return'   => [
                    'title'    => __( 'Asset Return', 'erp-pro' ),
                    'button' => __( 'Return', 'erp-pro')
                ]
            ],
        ];

        wp_localize_script( 'asset-main-script', 'wpErpAsset', $localize_scripts );
    }

    /**
     * Load JS Templates to Appropriate Page
     *
     * @since 1.0
     * @return void
     */
    public function admin_js_templates() {
        global $current_screen;
        $section     = isset( $_GET['section'] ) ? $_GET['section'] : '';
        $sub_section = isset( $_GET['sub-section'] ) ? $_GET['sub-section'] : '';

        if ( version_compare( WPERP_VERSION,  '1.4.0', '<' ) ) {
            switch ( $current_screen->base ) {

                case 'toplevel_page_erp-hr-asset':
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/asset-new.php', 'erp-asset-new' );
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/asset-edit.php', 'erp-asset-edit' );
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/category-new.php', 'asset-category-new' );
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/category-edit.php', 'asset-category-edit' );
                    break;

                case 'hr-management_page_erp-hr-employee':
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/employee-asset-new.php', 'erp-hr-emp-add-asset' );

                case 'hr-management_page_erp-hr-employee':
                case 'hr-management_page_erp-hr-my-profile':
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/employee-request-new.php', 'erp-hr-emp-request-asset' );

                case 'assets_page_erp-asset-allottment':
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/allot-new.php', 'erp-allotment-new' );
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/asset-return.php', 'erp-asset-return' );

                case 'assets_page_asset-request':
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/request-reply.php', 'erp-asset-request-reply' );
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/request-reject.php', 'erp-asset-request-reject' );

                default:
                    break;
            }
        }

        switch ( $section ) {
            case 'asset':
                erp_get_js_template( WPERP_ASSET_JS_TMPL . '/asset-new.php', 'erp-asset-new' );
                erp_get_js_template( WPERP_ASSET_JS_TMPL . '/asset-edit.php', 'erp-asset-edit' );
                erp_get_js_template( WPERP_ASSET_JS_TMPL . '/category-new.php', 'asset-category-new' );
                erp_get_js_template( WPERP_ASSET_JS_TMPL . '/category-edit.php', 'asset-category-edit' );
                break;

            case 'my-profile':
                erp_get_js_template( WPERP_ASSET_JS_TMPL . '/employee-asset-new.php', 'erp-hr-emp-add-asset' );
                erp_get_js_template( WPERP_ASSET_JS_TMPL . '/employee-request-new.php', 'erp-hr-emp-request-asset' );
                break;

            case 'people':
                if ( 'employee' === $sub_section ) {
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/employee-asset-new.php', 'erp-hr-emp-add-asset' );
                    erp_get_js_template( WPERP_ASSET_JS_TMPL . '/employee-request-new.php', 'erp-hr-emp-request-asset' );
                }
                break;

            default:
                # code...
                break;
        }

        if ( isset( $_GET['sub-section'] ) ) {
            erp_get_js_template( WPERP_ASSET_JS_TMPL . '/allot-new.php', 'erp-allotment-new' );
            erp_get_js_template( WPERP_ASSET_JS_TMPL . '/asset-return.php', 'erp-asset-return' );
            erp_get_js_template( WPERP_ASSET_JS_TMPL . '/request-reply.php', 'erp-asset-request-reply' );
            erp_get_js_template( WPERP_ASSET_JS_TMPL . '/request-reject.php', 'erp-asset-request-reject' );
        }
    }

    /**
     * Employee Single Tab Header
     *
     * @since 1.0
     * @return array
     */
    public function get_employee_single_tab( $tabs ) {

        $user_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : get_current_user_id();

        if ( !current_user_can ( 'erp_edit_employee', $user_id ) ) {
            return $tabs;
        }

        $tabs['assets'] = [
            'title' => __( 'Assets', 'erp-pro' ),
            'callback' => [$this, 'get_employee_single_tab_content']
        ];

        return $tabs;
    }

    /**
     * Employee Single Tab Content
     *
     * @since 1.0
     * @return mixed
     */
    public function get_employee_single_tab_content( $employee ) {
        include WPERP_ASSET_VIEWS . '/asset-employee-tab.php';
    }

    /**
     * Adds a section to hr report listing page
     *
     * @since 1.0
     * @return array
     */
    public function asset_report( $reports ) {

        $reports['asset-report'] = [
            'title'       => __( 'Assets', 'erp-pro' ),
            'description' => __( 'Detailed report on Assets', 'erp-pro' )
        ];

        return $reports;
    }

    /**
     * Asset report page for hr reporting
     *
     * @since 1.0
     * @return mixed
     */
    public function asset_reporting_page( $template, $action ) {

        if( 'asset-report' == $action ) {
            $template = WPERP_ASSET_VIEWS . '/asset-reporting.php';
        }

        return $template;
    }

    /**
     * Add asset email classes to HR email
     *
     * @since 1.0
     * @return array
     */
    public function asset_email_classes( $emails ) {

        $emails['New_Asset_Request'] = new \WeDevs\AssetManagement\EmployeeAssetRequest();
        $emails['New_Asset_Approve'] = new \WeDevs\AssetManagement\EmployeeAssetApprove();
        $emails['Asset_Request_Reject'] = new \WeDevs\AssetManagement\EmployeeAssetRequestReject();
        $emails['EmployeeAssetOverdue'] = new \WeDevs\AssetManagement\EmployeeAssetOverdue();

        return $emails;
    }

    /**
     * Send email to employee if item overdue
     *
     * @since 1.0
     * @return bool
     */
    public function erp_asset_notify_overdue() {
        global $wpdb;
        $today = current_time( 'Y-m-d' );

        $sql = "SELECT his.*, u.display_name, u.user_email, ass.item_group, ass.model_no, ass.item_code
                FROM {$wpdb->prefix}erp_hr_assets_history AS his
                LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass
                ON his.item_id = ass.id
                LEFT JOIN $wpdb->users as u
                ON his.allotted_to = u.id
                WHERE his.status = 'allotted' AND his.date_return_proposed != '0000-00-00' AND his.date_return_proposed < %s";

        $data    = $wpdb->get_results( $wpdb->prepare( $sql, $today ), ARRAY_A );
        $emailer = wperp()->emailer->get_email( 'EmployeeAssetOverdue' );

        if ( is_a( $emailer, '\WeDevs\ERP\Email') ) {
            $emailer->trigger( $data );

            wp_send_json_success();
        }
    }

    /**
     * Adds necessary request type
     *
     * @since 1.1.6
     *
     * @param array $types
     *
     * @return void
     */
    public function add_request_type( $types ) {
        $requests = erp_hr_asset_request_all();

        $type     = [
            'asset'  => [
                'count'   => count( (array) $requests ),
                'label'   => __( 'Asset', 'erp-pro' )
            ]
        ];

        return $types + $type;
    }

    /**
     * Gets pending request count
     *
     * @since 1.1.6
     *
     * @param array $requests
     *
     * @return array
     */
    public function get_pending_request_count( $requests ) {
        $pending  = erp_hr_asset_request_all( [ 'status' => 'pending', 'number' => -1 ] );

        $requests['asset'] = count( (array) $pending );

        return $requests;
    }

    /**
     * Retrieves asset requests
     *
     * @since 1.1.6
     *
     * @param array $args
     *
     * @return array
     */
    public function get_asset_requests( $args ) {
        $data     = [];
        $requests = erp_hr_asset_request_all( $args );

        if ( is_wp_error( $requests ) ) {
            return $requests;
        }

        foreach ( $requests as $key => $request ) {
            $data[] = [
                'id'           => $request->id,
                'employee'     => [
                    'id'       => $request->user_id,
                    'name'     => $request->employee_name,
                    'url'      => add_query_arg(
                        [ 'id' => $request->user_id ],
                        admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view' )
                    )
                ],
                'status'       => [
                    'id'       => $request->status,
                    'title'    => sprintf( __( '%s', 'erp-pro' ), ucfirst( $request->status ) )
                ],
                'type'         => [
                    'id'       => 'asset',
                    'title'    => __( 'Asset', 'erp' )
                ],
                'item'         => [
                    'id'       => $request->item_name,
                    'name'     => $request->item
                ],
                'category'     => [
                    'id'       => $request->category,
                    'name'     => $request->cat_name
                ],
                'created'      => $request->date_requested
            ];
        }

        return [
            'data'        => $data,
            'total_items' => count( $data )
        ];
    }
}
