<?php
namespace WeDevs\ERP_PRO\HRM\HrTraining;

// don't call the file directly
use WeDevs\HrTraining\Emails\Emailer;
use WeDevs\HrTraining\Log;
use WeDevs\HrTraining\Ajax;
use WeDevs\HrTraining\TrainingEmployee;
use WeDevs\HrTraining\TrainingPostType;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * ERP_HR_Training class
 */
class Module {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.2.0';

    /**
     * Constructor for the ERP_HR_Training class
     */
    private function __construct() {

        $this->define_constants();

        add_action( 'admin_print_footer_scripts', array( $this, 'highlight_menu' ) );
        add_filter( 'parent_file', array( $this, 'highlight_submenu' ), 100 );

        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_hr_training', array( $this, 'activate' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_hr_training', array( $this, 'deactivate' ) );

        add_action( 'erp_hrm_loaded', array( $this, 'erp_hr_trainig_loaded' ) );
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPERP_TRAINING_VERSION', $this->version );
        define( 'WPERP_TRAINING_FILE', __FILE__ );
        define( 'WPERP_TRAINING_PATH', dirname( WPERP_TRAINING_FILE ) );
        define( 'WPERP_TRAINING_INCLUDES', WPERP_TRAINING_PATH . '/includes' );
        define( 'WPERP_TRAINING_URL', plugins_url( '', WPERP_TRAINING_FILE ) );
        define( 'WPERP_TRAINING_ASSETS', WPERP_TRAINING_URL . '/assets' );
    }

    /**
     * Initializes the ERP_HR_Training() class
     *
     * Checks for an existing ERP_HR_Training() instance
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
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {

        update_option( 'eht_version', WPERP_TRAINING_VERSION );

        $default_email_content_for_new_training_assign_to_employee = [
            'subject' => 'New training has been assigned to you',
            'heading' => 'New Training Assigned',
            'body'    => '
                        Hello {employee_name}
                        You are assigned to a training program named {training_name} at {date}
                        Thank you'
        ];

        if ( empty( get_option( 'erp_email_settings_after-assign-training' ) ) ) {
            update_option( 'erp_email_settings_after-assign-training', $default_email_content_for_new_training_assign_to_employee );
        }
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Load all require classes and files
     *
     * @return void
     */
    public function erp_hr_trainig_loaded() {
        $this->includes();
        $this->init_hooks();
        $this->init_classes();
    }

    /**
     * Include the required files
     *
     * @since 1.1.3 Added training log class
     *
     * @return void
     */
    public function includes() {
        if ( !class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
    }

    /**
     * Initializes classes
     *
     * @since 1.1.3 Added training log class
     *
     * @return void
     */
    public function init_classes() {
        new TrainingPostType();
        new TrainingEmployee();
        new Log();
        new Ajax();
        new Emailer();
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {
        // Loads frontend scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );


    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts( $hook ) {

        /**
         * All styles goes here
         */
        // wp_enqueue_style( 'multiselect', plugins_url( 'assets/css/multiselect.css', __FILE__ ), false, date( 'Ymd' ) );
        wp_enqueue_style( 'erp-select2' );
        wp_enqueue_style( 'erp-hr-training', plugins_url( 'assets/css/erp-training.css', __FILE__ ), false, date( 'Ymd' ) );

        /**
         * All scripts goes here
         */
        // wp_enqueue_script( 'multiselect', plugins_url( 'assets/js/multiselect.js', __FILE__ ), array( 'jquery'), false, true );
        wp_enqueue_script( 'erp-select2');
        wp_enqueue_script( 'erp-hr-training', plugins_url( 'assets/js/erp-training.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker', 'erp-select2' ), false, true );

        if ( 'edit.php' == $hook && isset( $_GET['post_type'] ) && 'erp_hr_training' == $_GET['post_type'] ) {
            wp_enqueue_script( 'quick-edit', plugins_url( 'assets/js/quick-edit.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker', 'erp-select2' ), false, true );
        }

        /**
         * Example for setting up text strings from Javascript files for localization
         *
         * Uncomment line below and replace with proper localization variables.
         */
        // $translation_array = array( 'some_string' => __( 'Some string to translate', 'baseplugin' ), 'a_value' => '10' );
        // wp_localize_script( 'base-plugin-scripts', 'baseplugin', $translation_array ) );

    }

    public function admin_menu() {
        $capability = 'erp_hr_manager';
        if ( version_compare( WPERP_VERSION , '1.4.0', '<' ) ) {
            add_submenu_page( 'erp-hr', __( 'Training', 'erp-pro' ), __( 'Training', 'erp-pro' ), 'erp_hr_manager', 'edit.php?post_type=erp_hr_training');
        } else {
            erp_add_menu( 'hr', array(
                'title'       =>  __( 'Training', 'erp-pro' ),
                'slug'        =>  '[fd[gp[rg',
                'callback'    =>  [],
                'capability'  =>  $capability,
                'direct_link' => admin_url( 'edit.php?post_type=erp_hr_training' ),
                'position'    => 35
            ) );
        }
    }

    /**
     * Highlight Menu for announcement
     */
    public function highlight_menu(){
        $screen = get_current_screen();
        if ( $screen->id != 'edit-erp_hr_training' ) {
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
        if ( 'erp_hr_training' == $post_type ) {
            $parent_file = 'admin.php?page=erp';
            $submenu_file = 'erp-hr';
        }
        return $parent_file;
    }
}
