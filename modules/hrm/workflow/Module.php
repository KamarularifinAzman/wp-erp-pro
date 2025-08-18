<?php
namespace WeDevs\ERP_PRO\HRM\Workflow;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

class Module {
    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.3.0';

    /**
     * Class constructor.
     */
    public function __construct() {
        // load the addon
        add_action( 'erp_loaded', array( $this, 'plugin_init' ) );

        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_workflow', array( $this, 'activate' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_workflow', array( $this, 'deactivate' ) );
    }

    /**
     * Initialize the class.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Activate the plugin.
     */
    public function activate() {
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

        $table_schema = [

            "CREATE TABLE `{$wpdb->prefix}erp_workflows` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              `type` varchar(10) NOT NULL DEFAULT 'auto',
              `object` varchar(20) NOT NULL DEFAULT '',
              `events_group` varchar(50) NOT NULL DEFAULT '',
              `event` varchar(50) NOT NULL DEFAULT '',
              `conditions_group` varchar(5) NOT NULL DEFAULT 'or',
              `status` varchar(10) NOT NULL DEFAULT 'active',
              `delay_time` bigint(20) NOT NULL,
              `delay_period` varchar(10) NOT NULL DEFAULT '',
              `run` int(11) NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              `deleted_at` datetime DEFAULT NULL,
              `created_by` bigint(20) NOT NULL,
              PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_workflow_conditions` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `condition_name` varchar(255) NOT NULL DEFAULT '',
              `operator` varchar(10) NOT NULL DEFAULT '',
              `value` varchar(255) NOT NULL DEFAULT '',
              `workflow_id` bigint(20) NOT NULL,
              `parent_id` bigint(20) NOT NULL,
              PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_workflow_actions` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              `params` text NOT NULL,
              `extra` text NOT NULL,
              `workflow_id` bigint(20) unsigned NOT NULL,
              PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE `{$wpdb->prefix}erp_workflow_logs` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `workflow_id` bigint(20) unsigned NOT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) $collate;",

        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

        // Create roles & permissions
        $this->create_roles_permissions();
    }

    /**
     * @since 1.2.2
     */
    public function deactivate() {
        // nothing added yet.
    }

    /**
     * Init the plugin.
     *
     * @return void
     */
    public function plugin_init() {
        require_once dirname( __FILE__ ) . '/includes/functions.php';

        // Define constants
        $this->define_constants();

        // Includes files
        $this->includes();

        // Instantiate classes
        $this->init_classes();
    }

    /**
     * Define the plugin constants.
     *
     * @return void
     */
    private function define_constants() {
        define( 'ERP_WORKFLOW_VER', $this->version );
        define( 'ERP_WORKFLOW_FILE', __FILE__ );
        define( 'ERP_WORKFLOW_PATH', dirname( ERP_WORKFLOW_FILE ) );
        define( 'ERP_WORKFLOW_INCLUDES', ERP_WORKFLOW_PATH . '/includes' );
        define( 'ERP_WORKFLOW_VIEWS', ERP_WORKFLOW_INCLUDES . '/views' );
        define( 'ERP_WORKFLOW_URL', plugins_url( '', ERP_WORKFLOW_FILE ) );
        define( 'ERP_WORKFLOW_ASSETS', ERP_WORKFLOW_URL . '/assets' );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        require_once ERP_WORKFLOW_INCLUDES . '/actions-filters.php';
    }

    /**
     * Init the plugin classes.
     *
     * @return void
     */
    private function init_classes() {
        new \WeDevs\ERP\Workflow\AdminMenu();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\ERP\Workflow\AjaxHandler();
        }
    }

    /**
     * Create & permissions
     *
     * @return  void
     */
    public function create_roles_permissions() {
        $hr_manager  = get_role( 'erp_hr_manager' );
        $crm_manager = get_role( 'erp_crm_manager' );
        $ac_manager  = get_role( 'erp_ac_manager' );

        $hr_manager->add_cap( 'erp_workflow_menu_permission' );
        $crm_manager->add_cap( 'erp_workflow_menu_permission' );
        $ac_manager->add_cap( 'erp_workflow_menu_permission' );
    }
}
