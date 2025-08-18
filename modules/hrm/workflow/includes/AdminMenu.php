<?php

namespace WeDevs\ERP\Workflow;

class AdminMenu {
    /**
     * Class constructor.
     */
    public function __construct() {
        add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 3 );
        add_action( 'erp_submenu_page', [ $this, 'admin_menu'] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        if ( version_compare( WPERP_VERSION , '1.4.0', '>=' ) ) {
            add_action( 'admin_print_footer_scripts', array( $this, 'highlight_menu' ) );
            add_filter( 'parent_file', array( $this, 'highlight_submenu' ), 100 );
        }
    }

    /**
     * Highlight Menu for announcement
     */
    public function highlight_menu(){
        $screen = get_current_screen();
        if ( $screen->id != 'wp-erp_page_erp-workflow-new' ) {
            return;
        }

        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                $('li.toplevel_page_erp').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
                $('li.toplevel_page_erp a:first').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
            });
        </script>
        <?php

    }

    /**
     * Highlight sunbmenu for announcement
     *
     * @param $parent_file
     *
     * @return string
     */
    public function highlight_submenu( $parent_file ) {
        global $parent_file, $submenu_file, $post_type;
        $screen = get_current_screen();

        if ( $screen->id == 'wp-erp_page_erp-workflow-new' ) {
            $parent_file = 'admin.php?page=erp';
            $submenu_file = 'erp-workflow';
        }
        return $parent_file;
    }

    /**
     * Setting screen option.
     *
     * @param  string $status, $option, $value
     *
     * @return string
     */
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    /**
     * Register the admin menu.
     *
     * @return void
     */
    public function admin_menu() {
        $capabilities = 'erp_workflow_menu_permission';
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            $menu_page = add_menu_page( __( 'Workflow', 'erp-pro' ), __( 'Workflow', 'erp-pro' ), $capabilities, 'erp-workflow', [ $this, 'workflow_page' ], 'dashicons-share-alt' );

            add_submenu_page( 'erp-workflow', __( 'All Workflows', 'erp-workflow' ), __( 'All Workflows', 'erp-workflow' ), $capabilities, 'erp-workflow', [ $this, 'workflow_page' ] );

            add_submenu_page( 'erp-workflow', __( 'Add New', 'erp-workflow' ), __( 'Add New', 'erp-workflow' ), $capabilities, 'erp-workflow-new', [ $this, 'workflow_new_page' ] );
        } else {
            $menu_page = add_submenu_page( 'erp', __( 'Workflow', 'erp-pro' ), __( 'Workflow', 'erp-pro' ), $capabilities, 'erp-workflow', [ $this, 'workflow_page'] );
            add_submenu_page( 'erp', __( 'Add New', 'erp-pro' ), '', $capabilities, 'erp-workflow-new', [ $this, 'workflow_new_page' ] );
        }

        add_action( "load-$menu_page", [ $this, 'screen_option' ] );
        add_action( "load-$menu_page", [ $this, 'handle_workflows_bulk_action' ] );
    }

    /**
     * Display the workflow page.
     *
     * @return void
     */
    public function workflow_page() {
        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id       = isset( $_GET['id'] ) ? $_GET['id'] : 0;
        $template = '';

        switch ( $action ) {
            case 'edit':
                $template = ERP_WORKFLOW_VIEWS . '/workflow-new.php';
                break;

            default:
                $template = ERP_WORKFLOW_VIEWS . '/workflows.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include( $template );
        }
    }

    /**
     * Display the new workflow page.
     *
     * @return void
     */
    public function workflow_new_page() {
        $template = ERP_WORKFLOW_VIEWS . '/workflow-new.php';

        include( $template );
    }

    /**
     * Enqueue scripts.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_erp-workflow' !== $hook  && 'wp-erp_page_erp-workflow-new' !== $hook && 'wp-erp_page_erp-workflow' !== $hook && 'workflow_page_erp-workflow-new' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'erp-wf-styles', ERP_WORKFLOW_ASSETS . '/css/style.css', ['erp-sweetalert'], false );

        if ( ( 'wp-erp_page_erp-workflow' === $hook || 'toplevel_page_erp-workflow' === $hook ) && ( ! isset( $_GET['action'] ) || $_GET['action'] != 'edit'  ) ) {
            return;
        }

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script( 'erp-wf-scripts', ERP_WORKFLOW_ASSETS . '/js/erp-workflow' . $suffix . '.js', ['erp-vuejs', 'moment', 'erp-sweetalert'], false, true );

        $events               = erp_wf_get_events_list();
        $events_list          = erp_wf_dropdown_list_formatter( $events, true );
        $conditions_auto      = erp_wf_get_conditions_list()['auto' ];
        $conditions_list_auto = erp_wf_dropdown_list_formatter( $conditions_auto, true );
        $actions              = erp_wf_get_actions_list()['auto'];
        $actions_list_auto    = erp_wf_dropdown_list_formatter( $actions, true );

        global $wp_roles;
        $roles = $wp_roles->get_names();

        $user_roles   = erp_wf_dropdown_list_formatter( $roles );

        $modules_list = [];
        $modules_list_keys = array_keys( $events_list );
        foreach ( $modules_list_keys as $item ) {
            switch ( $item ) {
                case 'crm':
                case 'hrm':
                case 'imap':
                    $label = __( strtoupper( $item ), 'erp' );
                    break;

                default:
                    $label = __( ucwords( $item ), 'erp' );
                    break;
            }

            $modules_list[] = [
                'key'   => $item,
                'label' => $label
            ];
        }

        $nonces = [
            'fetch_workflow' => wp_create_nonce( 'erp-wf-fetch-workflow' ),
            'fetch_users'    => wp_create_nonce( 'erp-wf-fetch-users' )
        ];

        $i18n = erp_wf_get_localize_strings();

        $erp_wf_localize_vars = [
            'i18n'                 => $i18n,
            'modules_list'         => $modules_list,
            'events_list'          => $events_list,
            'conditions_list_auto' => $conditions_list_auto,
            'actions_list_auto'    => $actions_list_auto,
            'pluginURL'            => ERP_WORKFLOW_URL,
            'site_url'             => site_url(),
            'user_roles'           => $user_roles,
            'nonces'               => $nonces
        ];
        wp_localize_script( 'erp-wf-scripts', 'erp_wf_localize_vars', $erp_wf_localize_vars );

        wp_enqueue_script( 'tiny-mce', site_url( '/wp-includes/js/tinymce/tinymce.min.js' ), [] );
        wp_enqueue_script( 'tiny-mce-code', ERP_WORKFLOW_ASSETS . '/js/tinymce/plugins/code/plugin.min.js', [ 'tiny-mce' ], ERP_WORKFLOW_VER, true );
        wp_enqueue_script( 'tiny-mce-hr', ERP_WORKFLOW_ASSETS . '/js/tinymce/plugins/hr/plugin.min.js', [ 'tiny-mce' ], ERP_WORKFLOW_VER, true );

        /**** For time picker  Start***/

        wp_enqueue_script( 'erp-asset-timepicker', WPERP_ASSETS . '/vendor/timepicker/jquery.timepicker.min.js', [ 'jquery' ], ERP_WORKFLOW_VER, true );

        /**** For time picker  End***/
    }

    /**
     * Handles bulk action and delete.
     *
     * @return void
     */
    public function handle_workflows_bulk_action() {
        $page_url = menu_page_url( 'erp-workflow', false );

        // Delete item
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'delete' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'erp-wf-delete-workflow' ) ) {
                return false;
            }

            $workflow_id = absint( esc_sql( $_REQUEST['id'] ) );

            $this->audit_log( $workflow_id, 'deleted', 'delete', false );

            erp_wf_delete_workflow( $workflow_id );

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Delete items
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-delete' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-delete' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-workflows' ) ) {
                return false;
            }

            $ids = esc_sql( $_REQUEST['bulk-items'] );

            foreach ( $ids as $id ) {
                $workflow_id = absint( $id );

                $this->audit_log( $workflow_id, 'deleted', 'delete', false );

                erp_wf_delete_workflow( $workflow_id );
            }

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Restore item
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'restore' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'restore' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'erp-wf-restore-workflow' ) ) {
                return false;
            }

            $workflow_id = absint( esc_sql( $_REQUEST['id'] ) );

            erp_wf_restore_workflow( $workflow_id );

            $this->audit_log( $workflow_id, 'restored', 'restore' );

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Restore items
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-restore' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-restore' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-workflows' ) ) {
                return false;
            }

            $ids = esc_sql( $_REQUEST['bulk-items'] );

            foreach ( $ids as $id ) {
                $id = absint( $id );

                erp_wf_restore_workflow( $id );

                $this->audit_log( $id, 'restored', 'restore' );
            }

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Parmanent delete item
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'parmanent-delete' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'parmanent-delete' )
        ) {

            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'erp-wf-delete-workflow' ) ) {
                return false;
            }

            $workflow_id = absint( esc_sql( $_REQUEST['id'] ) );

            erp_wf_delete_workflow( $workflow_id, true );

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Parmanent delete items
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-parmanent-delete' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-parmanent-delete' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-workflows' ) ) {
                return false;
            }

            $ids = esc_sql( $_REQUEST['bulk-items'] );

            foreach ( $ids as $id ) {
                $workflow_id = absint( $id );

                erp_wf_delete_workflow( $workflow_id, true );
            }

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Change status
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'status' ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'erp-wf-status-workflow' ) ) {
                return false;
            }

            $workflow_id = absint( $_REQUEST['id'] );
            $workflow    = erp_wf_get_workflow( $workflow_id );
            $status      = ( $workflow->status == 'active' ) ? 'paused' : 'active';

            erp_wf_update_workflow( $workflow_id, ['status' => $status] );

            $action = ( $status == 'active' ) ? 'activated' : 'paused';

            $this->audit_log( $workflow_id, $action, 'change' );

            wp_redirect( $page_url );
            exit;
        }
    }

    /**
     * Screen options.
     *
     * @return void
     */
    public function screen_option() {
        if ( isset( $_GET['action'] ) ) {
            return;
        }

        $option = 'per_page';
        $args   = [
            'label'   => __( 'Number of items per page:', 'erp-pro' ),
            'default' => 20,
            'option'  => 'workflows_per_page'
        ];

        add_screen_option( $option, $args );

        $this->workflows_list_table_obj = new WorkflowsListTable();
    }

    /**
     * Add audit log.
     *
     * @param  int     $workflow_id
     * @param  string  $action
     * @param  string  $type
     *
     * @return void
     */
    protected function audit_log( $workflow_id, $action, $type = 'add', $link = true ) {
        $workflow = erp_wf_get_workflow( intval( $workflow_id ) );

        $logger = erp_log();

        if ( $link ) {
            $message = sprintf(
                '<a href="%s">%s</a> has been %s.',
                admin_url( 'admin.php?page=erp-workflow&action=edit&id=' . $workflow->id ),
                $workflow->name,
                $action
            );
        } else {
            $message = sprintf(
                '%s has been %s.',
                $workflow->name,
                $action
            );
        }

        $log_data = [
            'component'     => 'General',
            'sub_component' => 'Workflow',
            'message'       => $message,
            'changetype'    => $type,
            'created_by'    => get_current_user_id(),
        ];

        $logger->add( $log_data );
    }
}
