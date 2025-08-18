<?php
namespace WeDevs\ERP_PRO\HRM\Recruitment;

// don't call the file directly
use WeDevs\Recruitment\AdminMenu;
use WeDevs\Recruitment\AjaxHandler;
use WeDevs\Recruitment\Api\RecruitmentController;
use WeDevs\Recruitment\Emails\Emailer;
use WeDevs\Recruitment\FormHandler;
use WeDevs\Recruitment\HrQuestionnaire;
use WeDevs\Recruitment\Installer;
use WeDevs\Recruitment\JobSchemaManager;
use WeDevs\Recruitment\Updates;

if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * Base_Plugin class
 */
class Module {

    /* plugin version
    *
    * @var string
    */
    public $version = '1.6.0';

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct() {
        $this->define_constants();

        new Installer();
        add_action( 'admin_init', [ $this, 'set_role' ] );
        add_action( 'erp_hrm_loaded', array( $this, 'erp_hrm_loaded_hook' ) );
        add_filter( 'erp_rest_api_controllers', [ $this, 'load_recruitment_api_controller' ] );



        // Add a section to HR Settings
        add_filter( 'erp_settings_hr_sections', [ $this, 'add_att_sections' ] );

        // Add fields to ERP Settings Recruitment section
        add_filter( 'erp_settings_hr_section_fields', [ $this, 'add_att_section_fields' ], 10, 2 );

        // Attendance tab in HR Settings
        add_filter( 'erp_hr_settings_tabs', [ $this, 'recruitment_settings_page' ] );
    }

    /**
     * Loaded after hrm module
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function erp_hrm_loaded_hook() {
        $this->includes();

        $this->instantiate();

        $this->actions();

        $this->filters();
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     *  Set role
     *
     * return void
     */
    public function set_role() {
        $users  =   get_users( [ 'role__in' => [ 'administrator', 'erp_hr_manager' ] ] );

        if ( $users ) {
            foreach ( $users as $user ) {
                $user->add_role( 'erp_recruiter' );
            }
        }
    }

    /**
     * check php version is supported
     *
     * @return bool
     */
    public function is_supported_php() {
        if ( version_compare( PHP_VERSION, '5.4.0', '<=' ) ) {
            return false;
        }

        return true;
    }

    /**
     * define the plugin constant
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPERP_REC_VERSION', $this->version );
        define( 'WPERP_REC_FILE', __FILE__ );
        define( 'WPERP_REC_PATH', dirname( WPERP_REC_FILE ) );
        define( 'WPERP_REC_INCLUDES', WPERP_REC_PATH . '/includes' );
        define( 'WPERP_REC_MODULES', WPERP_REC_PATH . '/modules' );
        define( 'WPERP_REC_URL', plugins_url( '', WPERP_REC_FILE ) );
        define( 'WPERP_REC_ASSETS', WPERP_REC_URL . '/assets' );
        define( 'WPERP_REC_VIEWS', WPERP_REC_INCLUDES . '/Admin/views' );
        define( 'WPERP_REC_JS_TMPL', WPERP_REC_VIEWS . '/js-templates' );
    }

    /**
     * function objective
     *
     * @return void
     */
    public function includes() {
        if ( !class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        require_once WPERP_REC_INCLUDES . '/functions-recruitment.php';
        require_once WPERP_REC_INCLUDES . '/rec-actions-filters.php';

        if ( !$this->is_supported_php() ) {
            return;
        }
	    // Setup/welcome
	    // if ( !empty( $_GET['page'] ) ) {

		//     if ( 'erp-recruitment-setup' == $_GET['page'] ) {
		// 	    require_once WPERP_REC_INCLUDES . '/class-setup-wizard.php';
		//     }
	    // }
	    if ( !class_exists( 'WP_List_Table' ) ) {
		    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	    }

	    if ( ! $this->is_supported_php() ) {
		    return;
	    }

    }

    /**
     * function objective
     *
     * @return
     */
    public function instantiate() {
        new HrQuestionnaire();
        new FormHandler();

        new AdminMenu();
        new AjaxHandler();
        new Updates();
        new JobSchemaManager();
        new Emailer();
    }

    /**
     * function objective
     *
     * @return
     */
    public function actions() {
        add_action( 'init', [ $this, 'erp_rec_register_post_types' ] );
        add_action( 'admin_footer', [ $this, 'admin_rec_js_templates' ] );
        add_action( 'erp_daily_scheduled_events', [ $this, 'erp_rec_email_candidate_list' ] );
        add_action( 'erp_hr_employee_single_tabs', [ $this, 'create_todo_tab' ]);
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
        if ( version_compare( WPERP_VERSION, "1.4.0", '>=' ) ) {
            $links[] = '<a href="' . admin_url( 'admin.php?page=erp-hr&section=recruitment&sub-section=job-opening' ) . '">' . __( 'Manage Openings', 'erp-recruitment' ) . '</a>';
        }
        return $links;
    }

    /**
     * Tab of to-do setup
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function create_todo_tab($tabs) {
        if ( current_user_can( 'erp_list_employee' ) ) {
            $tabs['todo'] = [
                'title' => __( 'To-Do', 'erp-pro' ),
                'callback' => [ $this, 'erp_hr_employee_todo_tab' ]
            ];
            return $tabs;
        }
        return $tabs;
    }

    /**
     * To-do view
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function erp_hr_employee_todo_tab() {
        require_once WPERP_REC_VIEWS . '/view-employee-todo.php';
    }

    /**
     * function objective
     *
     * @return void
     */
    public function filters() {
        $this->job_description_filter();
    }

    /**
     * Email candidate list to hiring lead
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function erp_rec_email_candidate_list() {
        //get today's candidate list
        $candidate_list = erp_hr_rec_get_candidate_list();
        if ( count( $candidate_list ) > 0 ) {
            foreach ( $candidate_list as $candidate ) {
                $jobid              = $candidate['job_id'];
                $applicantid        = $candidate['applicant_id'];
                // $hiring_manager_ids = get_post_meta( $jobid, '_hiring_lead', true );
                $job_title          = get_the_title( $jobid );
                $people_info        = erp_get_people( $applicantid );
                $first_name         = $people_info->first_name;
                $last_name          = $people_info->last_name;
                $email              = $people_info->email;
                $subject            = __( 'A new applicant has been applied', 'erp-pro' );
                $message            = sprintf( __( 'Job title : ' . "%s" . '<br />Applicant details:<br />' . 'Name : ' . "%s" . " " . "%s" . '<br />Email : ' . "%s", 'erp-pro' ), $job_title, $first_name, $last_name, $email );
                $headers[]          = "Content-type: text/html";
                // foreach ( $hiring_manager_ids as $hid ) {
                //     $employee_object = new \WeDevs\ERP\HRM\Employee( intval( $hid ) );
                //     wp_mail( $employee_object->user->user_email, $subject, $message, $headers );
                // }
            }
        }
    }

    /**
     * Apply standard WordPress filters on the text
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function job_description_filter() {
        add_filter( 'erp_rec_job_description', 'wptexturize'        );
        add_filter( 'erp_rec_job_description', 'convert_smilies'    );
        add_filter( 'erp_rec_job_description', 'convert_chars'      );
        add_filter( 'erp_rec_job_description', 'wpautop'            );
        add_filter( 'erp_rec_job_description', 'shortcode_unautop'  );
        add_filter( 'erp_rec_job_description', 'prepend_attachment' );

        if ( ! empty( $GLOBALS['wp_embed'] ) ) {
            add_filter( 'erp_rec_job_description', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
            add_filter( 'erp_rec_job_description', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
        }
    }

    /**
     * Register Recruitment post type
     *
     * @since 0.1
     *
     * @return void
     */
    public function erp_rec_register_post_types() {
        $post_type = 'erp_hr_recruitment';
        $capability = 'manage_recruitment';

        register_post_type( $post_type, array(
                'label'           => __( 'Job Openings', 'erp-pro' ),
                'description'     => '',
                'public'          => true,
                'show_ui'         => true,
                'show_in_menu'    => false,
                'capability_type' => 'post',
                'hierarchical'    => false,
                'rewrite'         => array('slug' => 'job'),
                'query_var'       => false,
                'supports'        => array(
                    'title',
                    'editor'
                ),
                'menu_icon'       => 'dashicons-businessman',
                'capabilities'    => array(
                    'edit_post'          => $capability,
                    'read_post'          => $capability,
                    'delete_posts'       => $capability,
                    'edit_posts'         => $capability,
                    'edit_others_posts'  => $capability,
                    'publish_posts'      => $capability,
                    'read_private_posts' => $capability,
                    'create_posts'       => $capability,
                    'delete_post'        => $capability
                ),
                'labels'          => array(
                    'name'               => __( 'Job Openings', 'erp-pro' ),
                    'singular_name'      => __( 'Recruitment', 'erp-pro' ),
                    'menu_name'          => __( 'Recruitment', 'erp-pro' ),
                    'add_new'            => __( 'Create Openings', 'erp-pro' ),
                    'add_new_item'       => __( 'Add New Recruitment', 'erp-pro' ),
                    'edit'               => __( 'Edit', 'erp-pro' ),
                    'edit_item'          => __( 'Edit Openings', 'erp-pro' ),
                    'new_item'           => __( 'New Job Openings', 'erp-pro' ),
                    'view'               => __( 'View Job Openings', 'erp-pro' ),
                    'view_item'          => __( 'View Job Openings', 'erp-pro' ),
                    'search_items'       => __( 'Search Openings', 'erp-pro' ),
                    'not_found'          => __( 'No Job Openings Found', 'erp-pro' ),
                    'not_found_in_trash' => __( 'No Job Openings found in trash', 'erp-pro' ),
                    'parent'             => __( 'Parent Openings', 'erp-pro' )
                )
            )
        );
    }

    /**
     * Print JS templates in footer
     * @since 1.0.0
     * @return void
     */
    public function admin_rec_js_templates() {
        global $current_screen;
        $sub_section    =   isset( $_GET['sub-section'] ) ? $_GET['sub-section'] : '';
        switch ( $sub_section ) {
            case 'todo-calendar':
                erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
                erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template-detail.php', 'erp-rec-todo-description-template' );
                break;
            case 'applicant_detail':
                erp_get_js_template( WPERP_REC_JS_TMPL . '/interview-template.php', 'erp-rec-interview-template' );
                erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
                break;
            default:
                # code...
                break;
        }
        if ( version_compare( WPERP_VERSION , '1.4.0', '<' ) ) {
            switch ( $current_screen->base ) {
                //case 'admin_page_applicant_detail':
                case 'recruitment_page_applicant_detail':
                    erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
                    erp_get_js_template( WPERP_REC_JS_TMPL . '/interview-template.php', 'erp-rec-interview-template' );
                    break;
                case 'job-openings_page_todo-calendar':
                    erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
                    break;
                case 'recruitment_page_todo-calendar':
                    erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template.php', 'erp-rec-todo-template' );
                    erp_get_js_template( WPERP_REC_JS_TMPL . '/todo-template-detail.php', 'erp-rec-todo-description-template' );
                    break;
                case 'recruitment_page_candidate-filter-list':
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    public function load_recruitment_api_controller( $controllers ){
        $controllers[] = 'WeDevs\Recruitment\Api\RecruitmentController';
        return $controllers;
    }

    public function add_att_sections( $sections ) {

        $sections ['recruitment'] = __( 'Recruitment', 'erp-pro' );

        return $sections;
    }

    public function add_att_section_fields( $fields, $section ) {

        if ( 'recruitment' == $section ) {

            $fields['recruitment'] = [
                [
                    'title' => __( 'Recruitment Settings', 'erp-pro' ),
                    'type'  => 'title',
                    'id'    => 'erp_att_grace',
                    'desc'  => 'HRM Recruitment settings'
                ],
                [
                    'title'     => __( 'Global Api', 'erp-pro' ),
                    'type'      => 'text',
                    'id'        => 'recruitment_api_url',
                    'desc'      => __( 'Third party API', 'erp-pro' ),
                    'default'   => get_rest_url() . 'erp/v1/hrm/recruitment/jobs'
                ]
            ];

            $fields['recruitment'][] = [
                'type' => 'sectionend',
                'id'   => 'script_styling_options'
            ];

        }

        return $fields;
    }

    public function recruitment_settings_page( $tabs ) {

        $tabs['recruitment'] = [
            'title'    => __( 'Recruitment', 'erp-pro' ),
            'callback' => array( $this, 'recruitment_tab' )
        ];

        return $tabs;
    }

    public function recruitment_tab() {
        //include WPERP_REC_VIEWS . '/recruitment-settings.php';
    }

} // Base_Plugin
