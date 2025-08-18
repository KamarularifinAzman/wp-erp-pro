<?php
namespace WeDevs\ERP_PRO\HRM\Attendance;

// don't call the file directly
use WeDevs\Attendance\ActionFilter;
use WeDevs\Attendance\Admin;
use WeDevs\Attendance\Ajax;
use WeDevs\Attendance\Assets;
use WeDevs\Attendance\AttendanceListTable;
use WeDevs\Attendance\Emails\Emailer;
use WeDevs\Attendance\FormHandler;
use WeDevs\Attendance\Frontend;
use WeDevs\Attendance\Install;
use WeDevs\Attendance\Log;
use WeDevs\Attendance\Notification;
use WeDevs\Attendance\REST_API;
use WeDevs\Attendance\Updates;
use WeDevs\Attendance\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class
 */
class Module {

	/**
	 * Add-on Version
	 *
	 * @var  string
	 */
	public $version = '2.2.0';

	/**
	 * Initializes the WeDevs_ERP_HR_Attendance class
	 *
	 * Checks for an existing WeDevs_ERP_HR_Attendance instance
	 * and if it doesn't find one, creates it.
	 */
	public static function init() {

		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Constructor for the WeDevs_ERP_HR_Attendance class
	 *
	 * Sets up all the appropriate hooks and actions
	 */
	private function __construct() {
		//Define constants
		$this->define_constants();

        new Install();
		add_action( 'erp_loaded', [ $this, 'plugin_init' ] );
	}

	/**
	 * Execute if ERP is installed
	 *
	 * @return null
	 */
	public function plugin_init() {
		// Include files
		$this->includes();

		// Instantiate classes
		$this->init_classes();

		// Init action hooks
		$this->init_actions();

		// Init filter hooks
		$this->init_filters();
	}

	/**
	 * Define Add-on constants
	 *
	 * @return void
	 */
	public function define_constants() {
		if ( defined( 'WPERP_ATTEND_VERSION' ) ) {
			return;
		}

		define( 'WPERP_ATTEND_VERSION', $this->version );
		define( 'WPERP_ATTEND_FILE', __FILE__ );
		define( 'WPERP_ATTEND_PATH', dirname( WPERP_ATTEND_FILE ) );
		define( 'WPERP_ATTEND_INCLUDES', WPERP_ATTEND_PATH . '/includes' );
		define( 'WPERP_ATTEND_URL', plugins_url( '', WPERP_ATTEND_FILE ) );
		define( 'WPERP_ATTEND_ASSETS', WPERP_ATTEND_URL . '/assets' );
		define( 'WPERP_ATTEND_VIEWS', WPERP_ATTEND_PATH . '/views' );
		define( 'WPERP_ATTEND_JS_TMPL', WPERP_ATTEND_VIEWS . '/js-templates' );
	}

	/**
	 * Include the required files
	 *
     * @since 2.0.6 Included attendance log file
     *
	 * @return void
	 */
	public function includes() {
        new Assets();

        if ( $this->is_request( 'admin' ) ) {
            new Admin();
        }

        if ( $this->is_request( 'frontend' ) ) {
            new Frontend();
        }

        if ( $this->is_request( 'rest' ) ) {
            new REST_API();
        }

//        new AttendanceShifts();
//        new AssignedShiftEmployees();
		include_once WPERP_ATTEND_INCLUDES . '/functions-attendance.php';
		include_once WPERP_ATTEND_INCLUDES . '/functions-shift.php';
        // queries

		//updater
		include_once WPERP_ATTEND_INCLUDES . '/wp-async-request.php';
		include_once WPERP_ATTEND_INCLUDES . '/wp-background-process.php';

        new Updates();
		//export import
		require_once WPERP_INCLUDES . '/Lib/parsecsv.lib.php';
	}

	/**
	 * Registers all the scripts to ERP init
	 *
	 * @since 1.1.1 Change the name from `register_scripts` to `attendance_scripts`.
	 *              Load scripts in specific pages
	 *
	 * @return void
	 */
	public function attendance_scripts( $hook ) {
		$hook = str_replace( sanitize_title( __( 'Attendance', 'erp-pro' ) ), 'attendance', $hook );

		$attendance_pages = [
			'toplevel_page_erp-hr-attendance',
			// 'admin_page_erp-new-attendance',
            // 'admin_page_erp-edit-attendance',
            // 'admin_page_erp-new-shift',
            // 'admin_page_erp-edit-shift',
			'hr-management_page_erp-hr-employee',
			'hr-management_page_erp-hr-reporting',
			// 'attendance_page_erp-hr-shifts',
			'attendance_page_erp-shfit-exim',
			'hr-management_page_erp-hr-my-profile'
		];

		if ( ! in_array( $hook, $attendance_pages ) ) {
			return;
		}

		wp_register_script( 'erp-att-sortablejs', WPERP_ATTEND_ASSETS . '/js/sortable.min.js', [], WPERP_ATTEND_VERSION, true );
        wp_register_script( 'erp-att-vuedraggable', WPERP_ATTEND_ASSETS . '/js/vuedragablefor.min.js', [], WPERP_ATTEND_VERSION, true );

		// Enqueue jQuery timepicker
		wp_enqueue_style( 'erp-timepicker' );

		// Enqueue main css style
		wp_enqueue_style( 'erp-attendance-main-style', WPERP_ATTEND_ASSETS . '/css/attendance.css' );

		if ( ! is_admin() ) {
			wp_enqueue_style( 'erp-attendance-frontend', WPERP_ATTEND_ASSETS . '/css/erp-attendance-frontend.css', [ 'erp-attendance-main-style' ], WPERP_ATTEND_VERSION );
		}

		// Register jQuery flot stack chart
		wp_register_script( 'erp-att-flot-stack', WPERP_ATTEND_ASSETS . '/js/jquery.flot.stack.js', [ 'erp-flotchart' ], WPERP_ATTEND_VERSION, true );

		// Register jQuery flot chart tick rotator
		wp_register_script( 'erp-att-flot-tickrotator', WPERP_ATTEND_ASSETS . '/js/jquery.flot.tickrotator.js', [ 'erp-flotchart' ], WPERP_ATTEND_VERSION, true );
		wp_enqueue_script( 'plot', '//cdn.jsdelivr.net/jquery.flot/0.8.3/jquery.flot.min.js', [ 'jquery' ], '', false );
		// Enqueue main js script
		wp_enqueue_script( 'erp-attendance-main-script', WPERP_ATTEND_ASSETS . '/js/attendance.js', [
			'jquery',
			'moment',
			'jquery-ui-datepicker',
			'erp-timepicker',
			'erp-flotchart',
			'erp-flotchart-pie',
			'erp-att-flot-stack',
			'erp-att-flot-tickrotator',
			'erp-flotchart-time',
			'erp-flotchart-tooltip',
			'erp-flotchart-axislables',
			'erp-vuejs'
		], WPERP_ATTEND_VERSION, true );

		wp_enqueue_style( 'erp-timepicker' );

		$localize_scripts = [
			'scriptDebug'          => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
			'att_main_url'         => admin_url( 'admin.php?page=erp-hr-attendance' ),
			'att_shifts_list'      => admin_url( 'admin.php?page=erp-hr-shifts' ),
			'current_date'         => current_time( 'Y-m-d' ),
			'utc_offset'           => get_option( 'gmt_offset' ),
			'nonce'                => wp_create_nonce( 'wp-erp-attendance' ),
			'hook'                 => $hook,
			'shift_delete_warning' => __( "This shift and related attendance record will be deleted permanently and can't be undone. Are you sure?", 'erp-pro' ),
			'popup'                => [
				'attendanceNew'          => __( 'New Attendance', 'erp-pro' ),
				'attendanceNewSubmit'    => __( 'Submit Attendance', 'erp-pro' ),
				'attendanceEdit'         => __( 'Edit Attendance', 'erp-pro' ),
				'attendanceEditSubmit'   => __( 'Save Changes', 'erp-pro' ),
				'attendanceImport'       => __( 'Import Attendance', 'erp-pro' ),
				'attendanceImportSubmit' => __( 'Import', 'erp-pro' ),
			],
			'alert'                => [
				'somethingWrong' => __( 'Something went wrong', 'erp-pro' )
			],
			'selfService'          => [
				'checkoutMsg' => __( 'Do you want to checkout?', 'erp-pro' )
			]
		];

		if ( 'hr-management_page_erp-hr-employee' == $hook ) {

			$localize_scripts['user_id'] = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
		}

		if ( isset( $_REQUEST['page'] ) && 'erp-edit-attendance' == $_REQUEST['page'] ) {
			$localize_scripts['current_date'] = esc_attr( $_REQUEST['edit_date'] );
		}

		// Localize scripts
		wp_localize_script( 'erp-attendance-main-script', 'wpErpAttendance', $localize_scripts );
	}

	/**
	 * Initialize the classes
	 *
	 * @since 1.0
	 * @since 2.0.6 Instanciated attendance log file
     *
	 * @return void
	 */
	public function init_classes() {
        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new Admin();
        }

        if ( $this->is_request( 'frontend' ) ) {
            $this->container['frontend'] = new Frontend();
        }

        if ( $this->is_request( 'ajax' ) ) {
            // $this->container['ajax'] =  new \App\Ajax();
        }

        if ( $this->is_request( 'rest' ) ) {
            $this->container['rest'] = new REST_API();
        }

        $this->container['assets'] = new Assets();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new Ajax();
		}

        new FormHandler();

        // Widget instance
		new Widgets();
		// Init mailer
		new Emailer();
		// Init notification
		new Notification();
		// Updater class
		new Updates();

		//migration v2
		//new \WeDevs\Attendance\Updates\ERP_Att_Migrate_Attendance();

        new ActionFilter();

        // Audit Logger Class
        new Log();
	}

	/**
	 * Initializes action hooks to ERP
	 *
	 * @return void
	 */
	public function init_actions() {
		// Enqueue script files
		add_action( 'admin_enqueue_scripts', [ $this, 'attendance_scripts' ] );

		// Enqueue script files
		add_action( 'wp_enqueue_scripts', [ $this, 'attendance_scripts' ], 10 );

		// Adds a tab in a single employee page
		add_action( 'erp_hr_employee_single_tabs', [ $this, 'erp_hr_employee_single_attendance_callback' ], 12, 2 );

		// Attendance table bulk actions
		add_action( 'load-hr-management_page_erp-hr-attendance', [ $this, 'attendance_bulk_action' ] );

		// add frontend script
        add_action( 'erp_hr_frontend_load_script', [ $this, 'load_frontend_script' ] );
	}

	/**
	 * Initializes action hooks to ERP
	 *
	 * @return void
	 */
	public function init_filters() {
		// Add a section to HR Settings
		add_filter( 'erp_settings_hr_sections', [ $this, 'add_att_sections' ] );

		// Add fields to ERP Settings Attendance section
		add_filter( 'erp_settings_hr_section_fields', [ $this, 'add_att_section_fields' ], 10, 2 );

		// Attendance tab in HR Settings
		add_filter( 'erp_hr_settings_tabs', [ $this, 'attendance_settings_page' ] );

		// Adds an option for Attendance report in HR reporting page
		add_filter( 'erp_hr_reports', [ $this, 'attendance_report' ] );

		// Creates a separate report page for attendance report
		add_filter( 'erp_hr_reporting_pages', [ $this, 'attendance_report_page' ], 10, 2 );

		// Add api support
		add_filter( 'erp_rest_api_controllers', [ $this, 'load_attendance_api_controller' ] );

        // Plugin action links
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [ $this, 'plugin_action_links' ] );
	}

    /**
     * Add action links
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            $links[] = '<a href="' . admin_url( 'admin.php?page=erp-hr&section=attendance' ) . '">' . __( 'Manage Attendance', 'erp-pro' ) . '</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=erp-settings#/erp-hr/attendance' ) . '">' . __( 'Settings', 'erp-pro' ) . '</a>';
        return $links;
    }

	/**
	 * fucntion for Attendage Setting Tab
	 *
	 * @since 1.0
	 *
	 * @return mixed
	 */
	public function attendance_settings_page( $tabs ) {

		$tabs['attendance'] = [
			'title'    => __( 'Attendance', 'erp-pro' ),
			'callback' => array( $this, 'attendance_tab' )
		];

		return $tabs;
	}

	/**
	 * Attendance Tab in HR Settings
	 */
	public function attendance_tab() {
		include WPERP_ATTEND_VIEWS . '/tab-hr-settings-attendance.php';
	}

	/**
	 * Register Attendance Tab in Employee profile
	 */
	public function erp_hr_employee_single_attendance_callback( $tabs, $employee ) {

		// only show if HR manager or Valid employee
		if ( get_current_user_id() == $employee->id || current_user_can( 'erp_hr_manager' ) ) {
			$tabs['attendance'] = [
				'title'    => __( 'Attendance', 'erp-pro' ),
				'callback' => [ $this, 'erp_hr_employee_single_attendance_tab' ]
			];
		}

		return $tabs;
	}

	/**
	 *
	 */
	public function erp_hr_employee_single_attendance_tab() {
		include WPERP_ATTEND_VIEWS . '/tab-employee-single-status.php';
	}

	/**
	 * Check is current page actions
	 *
	 * @since 0.1
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
	 *
	 */
	public function attendance_bulk_action() {

		if ( ! $this->verify_current_page_screen( 'erp-hr-attendance', 'bulk-attendances' ) ) {
			return;
		}

		$attendance_table = new AttendanceListTable();
		$action           = $attendance_table->current_action();

		if ( $action ) {

			$redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

			switch ( $action ) {

				case 'filter_attendance':
					$redirect = remove_query_arg( array( 'filter_attendance', 'action', 'action2' ), $redirect );
					wp_redirect( $redirect );
					exit;

				default:
					exit;
			}
		}
	}

	/**
	 * Add attendance report to HR reporting page
	 *
	 * @param  $reports  array
	 *
	 * @return array
	 */
	public function attendance_report( $reports ) {
		$reports['attendance-report'] = [
			'title'       => __( 'Attendance (Date Based)', 'erp-pro' ),
			'description' => __( 'Reporting on employee attendance', 'erp-pro' )
		];

		$reports['att-report-employee'] = [
			'title'       => __( 'Attendance (Employee Based)', 'erp-pro' ),
			'description' => __( 'Reporting on employee attendance', 'erp-pro' )
		];

		return $reports;
	}

	/**
	 * Creates a separate page for attendance report
	 *
	 * @return mixed
	 */
	public function attendance_report_page( $template, $action ) {

		if ( 'attendance-report' == $action ) {
			$template = WPERP_ATTEND_VIEWS . '/attendance-reporting.php';
		} elseif ( 'att-report-employee' == $action ) {
			$template = WPERP_ATTEND_VIEWS . '/att-report-employee.php';
		}


		return $template;
	}

	/**
	 * Add Attendance Sections to ERP Settings Page
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function add_att_sections( $sections ) {

		$sections ['attendance'] = __( 'Attendance', 'erp-pro' );

		return $sections;
	}

	/**
	 * Add fields to Attendance Section in ERP Fields
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function add_att_section_fields( $fields, $section ) {

		if ( 'attendance' == $section ) {

			$fields['attendance'] = [
				[
					'title' => __( 'Grace Time', 'erp-pro' ),
					'type'  => 'title',
					'id'    => 'erp_att_grace'
				],
				[
					'title'   => __( 'Grace Before Checkin', 'erp-pro' ),
					'type'    => 'text',
					'id'      => 'grace_before_checkin',
					'desc'    => __( '(in minute) this time will not counted as overtime', 'erp-pro' ),
					'default' => 15
				],
				[
					'title'   => __( 'Grace After Checkin', 'erp-pro' ),
					'type'    => 'text',
					'id'      => 'grace_after_checkin',
					'desc'    => __( '(in minute) this time will not counted as late', 'erp-pro' ),
					'default' => 15
				],
                [
                    'title'   => __( 'Threshhold between checkout & checkin', 'erp-pro' ),
                    'type'    => 'text',
                    'id'      => 'erp_att_diff_threshhold',
                    'desc'    => __( '(in second) this time will prevent quick checkin after making a checkout', 'erp-pro' ),
                    'default' => 60
                ],
				[
					'title'   => __( 'Grace Before Checkout', 'erp-pro' ),
					'type'    => 'text',
					'id'      => 'grace_before_checkout',
					'desc'    => __( '(in minute) this time will not counted as early left', 'erp-pro' ),
					'default' => 15
				],
				[
					'title'   => __( 'Grace After Checkout', 'erp-pro' ),
					'type'    => 'text',
					'id'      => 'grace_after_checkout',
					'desc'    => __( '(in minute) this time will not counted as overtime', 'erp-pro' ),
					'default' => 15
				],
				[
					'title' => __( 'Self Attendance', 'erp-pro' ),
					'type'  => 'checkbox',
					'id'    => 'enable_self_att',
					'desc'  => __( 'Enable self attendance service for employees?', 'erp-pro' )
				],
				[
					'title' => __( 'IP Restriction', 'erp-pro' ),
					'type'  => 'checkbox',
					'id'    => 'erp_at_enable_ip_restriction',
					'desc'  => __( 'Enable IP restriction for checkin/checkout', 'erp-pro' )
				],
				[
					'title'             => __( 'Whitelisted IP\'s', 'erp-pro' ),
					'type'              => 'textarea',
					'id'                => 'erp_at_whitelisted_ips',
					'desc'              => __( 'Employees from this IP addresss will be able to self check-in. Put one IP in each line', 'erp-pro' ),
					'custom_attributes' => [
						'rows' => 4,
						'cols' => 45
					]
				],
				[
					'title' => __( 'Attendance Reminder', 'erp-pro' ),
					'type'  => 'checkbox',
					'id'    => 'attendance_reminder',
					'desc'  => __( 'Send email notification to remind Checking-in', 'erp-pro' )
				],
			];

			$fields['attendance'][] = [
				'type' => 'sectionend',
				'id'   => 'script_styling_options'
			];

		}

		return $fields;
	}

	/**
	 * Load scripts in frontend
	 *
	 * @since 1.1.1
	 *
	 * @return void
	 */
	public function load_frontend_script() {
		$this->attendance_scripts( 'toplevel_page_erp-hr-attendance' );
    }

    /**
     * Update day_type for user in date_shift_table
     *
     * @return void
     */
    public function update_day_type_for_user( $request_id, $request ) {
        global $wpdb;

        $sql = sprintf( "UPDATE {$wpdb->prefix}erp_attendance_date_shift
                SET day_type = 'leave'
                WHERE user_id = %d
                AND date BETWEEN '%s' AND '%s'
                AND day_type = 'working_day'",
                absint( $request['user_id'] ),
                date( 'Y-m-d', strtotime( $request['start_date'] ) ),
                date( 'Y-m-d', strtotime( $request['end_date'] ) )
            );

        $wpdb->query( $sql );
    }

	/**
	 * Register api files
	 * @since 1.1.3
	 *
	 * @param $controllers
	 *
	 * @return array
	 */
	public function load_attendance_api_controller( $controllers ) {
		$controllers[] = 'WeDevs\Attendance\Api\AttendanceController';

		return $controllers;
    }

    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'rest' :
                return defined( 'REST_REQUEST' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    public function update_employee_shift_by_leave_pending ( $request_id, $request ) {
        global $wpdb;

        $sql = sprintf( "UPDATE {$wpdb->prefix}erp_attendance_date_shift
                SET day_type = 'working_day'
                WHERE user_id = %d
                AND date BETWEEN '%s' AND '%s'
                AND day_type = 'leave'",
            absint( $request['user_id'] ),
            date( 'Y-m-d', strtotime( $request['start_date'] ) ),
            date( 'Y-m-d', strtotime( $request['end_date'] ) )
        );

        $wpdb->query( $sql );
    }

}
