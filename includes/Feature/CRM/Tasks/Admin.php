<?php
namespace WeDevs\ERP_PRO\Feature\CRM\Tasks;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Class responsible for admin panel functionalities
 *
 * @since 1.0.1
 */
class Admin {

    use Hooker;

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function __construct() {

        //load ajax hooks
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Ajax();
        }

        // Admin hooks
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'erp_crm_tasks', 'admin_view_tasks' );
        $this->filter( 'erp_crm_tasks_menu_items', 'add_menu_items' );
    }

    /**
     * Load admin scripts
     *
     * @since 1.0.1
     *
     * @param $hook_suffix
     */
    public function admin_scripts( $hook_suffix ) {
        $section     = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : 'tasks';

        if ( 'wp-erp_page_erp-crm' !== $hook_suffix || 'task' !== $section || 'tasks' !== $sub_section ) {
            return;
        }

        $erp_tasks_global = [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'erp-crm-tasks' ),
            'i18n'    => $this->i18n()
        ];

        $style_deps = [ 'erp-styles', 'erp-timepicker', 'erp-fontawesome', 'erp-sweetalert', 'erp-nprogress', 'erp-trix-editor' ];

        $script_deps = [ 'jquery', 'erp-sweetalert', 'erp-nprogress', 'underscore' ];

        wp_enqueue_style( 'erp-daterangepicker' );
        wp_enqueue_script( 'erp-daterangepicker' );
        wp_enqueue_style( 'erp-crm-tasks', ERP_PRO_FEATURE_URL . '/CRM/Tasks/assets/css/crm-tasks.css', $style_deps, ERP_PRO_PLUGIN_VERSION );
        wp_enqueue_script( 'erp-crm-tasks', ERP_PRO_FEATURE_URL . '/CRM/Tasks/assets/js/crm-tasks.js', $script_deps, ERP_PRO_PLUGIN_VERSION, true );

        wp_localize_script( 'erp-crm-tasks', 'erpTasks', $erp_tasks_global );
    }

    /**
     * Add admin panel menu item
     *
     * @since 1.1.0
     *
     * @param array $items
     *
     * @return void
     */
    public function add_menu_items( $items ) {
        $dropdown = [ 'tasks' => esc_html__( 'Tasks', 'erp-pro' ) ];

        return $dropdown + $items;
    }

    /**
     * Tasks Admin Page
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function admin_view_tasks() {
        require_once ERP_PRO_FEATURE_DIR . '/CRM/Tasks/views/tasks-feed.php';
        require_once ERP_PRO_FEATURE_DIR . '/CRM/Tasks/views/task-single.php';
    }

    /**
     * i18n strings for main admin pages
     *
     * @since 1.0.1
     *
     * @return array
     */
    private function i18n() {
        return [
            'task'           => __( 'Task', 'erp-pro' ),
            'done'           => __( 'Done', 'erp-pro' ),
            'pending'        => __( 'Pending', 'erp-pro' ),
            'due'            => __( 'Due', 'erp-pro' ),
            'contact'        => __( 'Contact', 'erp-pro' ),
            'status'         => __( 'Status', 'erp-pro' ),
            'assignedTo'     => __( 'Assigned To', 'erp-pro' ),
            'assignedBy'     => __( 'Assigned By', 'erp-pro' ),
            'noTask'         => __( 'No task found.', 'erp-pro' ),
            'filterStatus'   => __( 'Filter by Status', 'erp-pro' ),
            'filterContact'  => __( 'Filter by Contact/Company', 'erp-pro' ),
            'filterUser'     => __( 'Filter by Manager/Agent', 'erp-pro' ),
            'markComplete'   => __( 'Mark Complete', 'erp-pro' ),
            'markIncomplete' => __( 'Mark Incomplete', 'erp-pro' ),
        ];
    }
}
