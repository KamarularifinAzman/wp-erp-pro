<?php
namespace WeDevs\Attendance;

/**
 * Admin Pages Handler
 */
class Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu() {
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            global $submenu;

            $capability = 'manage_options';
            $slug       = 'erp-hr-attendances';

            $hook = add_menu_page( __( 'Attendance', 'erp-pro' ), __( 'Attendance', 'erp-pro' ), $capability, $slug, [ $this, 'attendance_main_callback' ], 'dashicons-text' );

            if ( current_user_can( $capability ) ) {
                $submenu[ $slug ][] = array( __( 'Attendance', 'erp-pro' ), $capability, 'admin.php?page=' . $slug . '#/' );
                $submenu[ $slug ][] = array( __( 'Shifts', 'erp-pro' ), $capability, 'admin.php?page=' . $slug . '#/shifts' );
                $submenu[ $slug ][] = array( __( 'Import/Export', 'erp-pro' ), $capability, 'admin.php?page=' . $slug . '#/exim' );
            }

            add_action( 'load-' . $hook, [ $this, 'init_hooks' ] );
        } else {
            erp_add_menu( 'hr', array(
                'title'      => __( 'Attendance', 'erp-pro' ),
                'slug'       => 'attendance',
                'capability' => 'erp_hr_manager',
                'callback'   => [ $this, 'attendance_main_callback' ],
                'position'   => 34,
            ) );

            erp_add_submenu( 'hr', 'attendance', array(
                'title'       => __( 'Attendance', 'erp-pro' ),
                'slug'        => 'attendance',
                'direct_link' => admin_url('admin.php?page=erp-hr&section=attendance#/'),
                'capability'  => 'erp_hr_manager',
                'callback'    => [ $this, 'attendance_main_callback' ],
                'position'    => 36,
            ) );

            erp_add_submenu( 'hr', 'attendance', array(
                'title'       => __( 'Shifts', 'erp-pro' ),
                'slug'        => 'shifts',
                'direct_link' => admin_url('admin.php?page=erp-hr&section=attendance#/shifts'),
                'capability'  => 'erp_hr_manager',
                'callback'    => '',
                'position'    => 37,
            ) );

            erp_add_submenu( 'hr', 'attendance', array(
                'title'       => __( 'Tools', 'erp-pro' ),
                'slug'        => 'exim',
                'direct_link' => admin_url('admin.php?page=erp-hr&section=attendance#/exim'),
                'capability'  => 'erp_hr_manager',
                'callback'    => '',
                'position'    => 38,
            ) );

            erp_add_submenu( 'hr', 'attendance', array(
                'title'       => __( 'Assign Bulk Shift', 'erp-pro' ),
                'slug'        => 'assign-shift-bulk',
                'direct_link' => admin_url('admin.php?page=erp-hr&section=attendance#/assign-shift-bulk'),
                'capability'  => 'erp_hr_manager',
                'callback'    => '',
                'position'    => 39,
            ) );

            erp_add_submenu( 'hr', 'attendance', array(
                'title'       => __( 'Settings', 'erp-pro' ),
                'slug'        => 'settings',
                'direct_link' => admin_url('admin.php?page=erp-settings#/erp-hr/attendance'),
                'capability'  => 'erp_hr_manager',
                'callback'    => '',
                'position'    => 40,
            ) );

            erp_add_submenu( 'hr', 'report', array(
                'title'         =>  __( 'Attendance (Date Based)', 'erp' ),
                'capability'    =>  'erp_hr_manager',
                'slug'          =>  'report&type=attendance-report',
                'callback'      =>  '',
                'position'      =>  5,
            ) );

            erp_add_submenu( 'hr', 'report', array(
                'title'         =>  __( 'Attendance (Employee Based)', 'erp' ),
                'capability'    =>  'erp_hr_manager',
                'slug'          =>  'report&type=att-report-employee',
                'callback'      =>  '',
                'position'      =>  5,
            ) );

            add_action( 'load-wp-erp_page_erp-hr', [ $this, 'init_hooks' ] );
        }
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
    public function enqueue_scripts($hook) {
        $section = ! empty( $_GET['section'] ) ? $_GET['section'] : '';

        wp_enqueue_style( 'erp-sweetalert' );
        wp_enqueue_script( 'erp-sweetalert' );

        if ( $hook !== 'wp-erp_page_erp-hr' || $section !== 'attendance' ) {
            return;
        }

        global $current_user;

        wp_enqueue_style( 'att-admin' );
        wp_localize_script( 'att-admin', 'att', [
            'erp_user_id' => $current_user->ID,
            'erp_site_url' => site_url(),
            'erp_rest_nonce' => wp_create_nonce( 'wp_rest' ),
            'erp_attend_assets' => WPERP_ATTEND_ASSETS,
            'date_format'        => erp_get_date_format(),
        ] );
        wp_enqueue_script( 'att-admin' );
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function attendance_main_callback() {
        $erp_att_localized_data = [];
        $erp_i18n = new \WeDevs\ERP\ERP_i18n;
        $erp_att_localized_data['locale_data'] = $erp_i18n->get_jed_locale_data( 'erp-pro' );
        ?>
        <script>
            window.wpErpAttendance = JSON.parse('<?php echo addslashes(
                json_encode( $erp_att_localized_data )
            ); ?>');
        </script>
        <?php
        echo '<div class="wrap"><div id="vue-admin-app"></div></div>';
    }
}
