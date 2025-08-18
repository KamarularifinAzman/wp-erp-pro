<?php
namespace WeDevs\ERP_PRO\HRM\CustomFieldBuilder;

// don't call the file directly
use WeDevs\CustomFieldBuilder\API\REST_API;
use WeDevs\CustomFieldBuilder\Classes\Admin;
use WeDevs\CustomFieldBuilder\Classes\Assets;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs ERP Peoples Add-on Main class
 */
class Module {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.5.0';

    /**
     * Initializes the class
     */
    public static function init() {

        static $instance = false;

        if ( ! $instance ) {

            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor for the WeDevs_ERP_People class
     *
     * Sets up all the appropriate hooks and actions
     */
    private function __construct() {
        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_custom_field_builder', array( $this, 'activate' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_custom_field_builder', array( $this, 'deactivate' ) );

        add_action( 'erp_loaded', [$this, 'init_plugin'] );
    }

    /**
     * Execute if ERP main is installed
     *
     * @return null
     */
    public function init_plugin() {

        $this->define_constants();

        $this->includes();

        $this->init_classes();

        $this->init_actions();

        $this->init_filters();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {

    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Define Add-on constants
     *
     * @return void
     */
    public function define_constants() {

        define( 'WPERP_PPL_VERSION', $this->version );
        define( 'WPERP_PPL_FILE', __FILE__ );
        define( 'WPERP_PPL_PATH', dirname( WPERP_PPL_FILE ) );
        define( 'WPERP_PPL_INCLUDES', WPERP_PPL_PATH . '/includes' );
        define( 'WPERP_PPL_URL', plugins_url( '', WPERP_PPL_FILE ) );
        define( 'WPERP_PPL_ASSETS', WPERP_PPL_URL . '/assets' );
        define( 'WPERP_PPL_VIEWS', WPERP_PPL_PATH . '/views' );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            require_once WPERP_PPL_INCLUDES . '/functions.php';
        }
    }

    /**
     * Initialize required classes
     *
     * @return void
     */
    public function init_classes() {
        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            new REST_API();
            new Admin();
            new Assets();
        }
    }

    /**
     * Initializes action hooks to ERP
     *
     * @return  void
     */
    public function init_actions() {
        if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) {
            add_action( 'admin_menu', [$this, 'add_menu'], 100 );
        }
        add_action( 'erp_submenu_page', [$this, 'add_menu'], 100 );

        add_action( 'wp_ajax_erp_form_builder', [$this, 'erp_field_builder_handler'] );

        // Employee Entry form
        add_action( 'erp-hr-employee-form-top', [$this, 'export_employee_top'] );
        add_action( 'erp-hr-employee-form-basic', [$this, 'export_employee_basic'] );
        add_action( 'erp-hr-employee-form-work', [$this, 'export_employee_work'] );
        add_action( 'erp-hr-employee-form-personal', [$this, 'export_employee_personal'] );
        add_action( 'erp-hr-employee-form-bottom', [$this, 'export_employee_bottom'] );
        add_action( 'erp-hr-employee-form', [$this, 'export_employee_form'] );

        // Employee Add
        add_action( 'erp_hr_employee_new', [$this, 'save_employee_all_section'], 10, 2 );

        // Employee Update
        add_action( 'erp_hr_employee_update', [$this, 'update_employee'], 10, 2 );

        // Employee Single Page
        add_action( 'erp-hr-employee-single-basic', [$this, 'employee_single_page_basic'], 10, 1 );
        add_action( 'erp-hr-employee-single-work', [$this, 'employee_single_page_work'], 10, 1 );
        add_action( 'erp-hr-employee-single-personal', [$this, 'employee_single_page_personal'], 10, 1 );
        add_action( 'erp-hr-employee-single-after-personal', [$this, 'employee_single_page_after_personal'], 10, 1 );
        add_action( 'erp-hr-employee-single-bottom', [$this, 'employee_single_page_bottom'], 10, 1 );

        // Contact Entry / Edit Form
        add_action( 'erp_crm_contact_form_top', [$this, 'export_contact_top'] );
        add_action( 'erp_crm_contact_form_basic', [$this, 'export_contact_basic'] );
        add_action( 'erp_crm_contact_form_other', [$this, 'export_contact_other'] );
        add_action( 'erp_crm_contact_form_contact_group', [$this, 'export_contact_contact_group'] );
        add_action( 'erp_crm_contact_form_additional', [$this, 'export_contact_additional'] );
        add_action( 'erp_crm_contact_form_social', [$this, 'export_contact_social'] );
        add_action( 'erp_crm_contact_form_bottom', [$this, 'export_contact_bottom'] );

        // Customer Entry / Edit Page
        add_action( 'erp_crm_company_form_top', [$this, 'export_company_top'] );
        add_action( 'erp_crm_company_form_basic', [$this, 'export_company_basic'] );
        add_action( 'erp_crm_company_form_other', [$this, 'export_company_other'] );
        add_action( 'erp_crm_company_form_contact_group', [$this, 'export_company_contact_group'] );
        add_action( 'erp_crm_company_form_additional', [$this, 'export_company_additional'] );
        add_action( 'erp_crm_company_form_social', [$this, 'export_company_social'] );
        add_action( 'erp_crm_company_form_bottom', [$this, 'export_company_bottom'] );

        // Contact Single Page
        add_action( 'erp_crm_single_contact_basic_info', [$this, 'contact_single_page_basic'] );
        add_action( 'erp_crm_contact_social_fields', [$this, 'show_contact_social_fields'] );

        // Company
        add_action( 'erp_crm_single_company_basic_info', [$this, 'company_single_page_basic'] );
        add_action( 'erp_crm_company_social_fields', [$this, 'show_company_social_fields'] );

        // Customer
        add_action( 'erp_accounting_customer_form_top', [$this, 'export_customer_top'] );
        add_action( 'erp_accounting_customer_form_bottom', [$this, 'export_customer_bottom'] );
        add_action( 'erp_accounting_customer_form_middle', [$this, 'export_customer_middle'] );

        // Vendor
        add_action( 'erp_accounting_vendor_form_top', [$this, 'export_vendor_top'] );
        add_action( 'erp_accounting_vendor_form_bottom', [$this, 'export_vendor_bottom'] );
        add_action( 'erp_accounting_vendor_form_middle', [$this, 'export_vendor_middle'] );

        // Save customer data
        add_filter( 'erp_ac_customer_new_save_field', [$this, 'save_customer_data'], 10, 2 );

        // with new accounting
        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            add_filter( 'erp_create_new_people', [$this, 'save_acct_customer_data'], 10, 3 );
            add_filter( 'erp_update_people', [$this, 'save_acct_customer_data'], 10, 3 );
        }

        // Add key to CRM search segment
        add_filter( 'erp_crm_global_serach_fields', [ $this, 'add_key_to_segment_search' ], 10, 2 );
        add_filter( 'erp_crm_contact_meta_fields', [ $this, 'add_key_to_segment_query_search' ] );
    }

    /**
     * Add submenu to ERP settings page
     *
     * @return  void
     */
    public function add_menu() {
        $page_hook = add_submenu_page( 'erp-company', __( 'Custom Field Builder', 'erp-pro' ), __( 'Custom Field Builder', 'erp-pro' ), 'manage_options', 'custom-field-builder', array( $this, 'field_builder_callback' ) );

        add_action( "admin_print_styles-{$page_hook}", [ $this, 'enqueue_scripts' ] );

        if ( version_compare( WPERP_VERSION, '1.4.0', '>=' ) ) {
            $page_hook = add_submenu_page( 'erp', __( 'Custom Field Builder', 'erp-pro' ), __( 'Custom Field Builder', 'erp-pro' ), 'manage_options', 'custom-field-builder', array( $this, 'field_builder_callback' ) );

            add_action( "admin_print_styles-{$page_hook}", [ $this, 'enqueue_scripts' ] );
        }
    }

    /**
     * Registers all the scripts to ERP init
     *
     * @return void
     */
    public function enqueue_scripts( $hook ) {
        wp_enqueue_style( 'erp-people-field-css', WPERP_PPL_ASSETS . '/css/style.css' );
        wp_enqueue_script( 'erp-people-field-js', WPERP_PPL_ASSETS . '/js/script.js', ['erp-vuejs', 'jquery'], false, true );

        $localize_scripts = [
            'nonce'     => wp_create_nonce('erp-form-builder'),
            'people'    => $this->get_current_people(),
            'collection'=> get_option( 'erp-' . $this->get_current_people() . '-fields', [] ),
            'sections'  => $this->get_current_people_sections(),
            'duplicateAlert' => __( 'There are duplicate fields, Please check.', 'erp-pro' ),
            'icons'     => [
                'behance'   => __( 'Behance', 'erp-pro' ),
                'dribbble'  => __( 'Dribbble', 'erp-pro' ),
                'flickr'    => __( 'Flickr', 'erp-pro' ),
                'github'    => __( 'Github', 'erp-pro' ),
                'instagram' => __( 'Instagram', 'erp-pro' ),
                'medium'    => __( 'Medium', 'erp-pro' ),
                'pinterest' => __( 'Pinterest', 'erp-pro' ),
                'reddit'    => __( 'Reddit', 'erp-pro' ),
                'snapchat'  => __( 'Snapchat', 'erp-pro' ),
                'tumblr'    => __( 'Tumblr', 'erp-pro' ),
                'vine'      => __( 'Vine', 'erp-pro' ),
                'vk'        => __( 'VK', 'erp-pro' ),
                'whatsapp'  => __( 'Whatsapp', 'erp-pro' )
            ]
        ];

        wp_localize_script( 'erp-people-field-js', 'wpErpForm', $localize_scripts );
    }

    /**
     * Initializes action filters to ERP
     *
     * @return  void
     */
    public function init_filters() {

        //Employee Update
        add_filter( 'erp_hr_get_employee_fields', [$this, 'get_employee_fields'], 10, 3 );
        add_filter( 'erp_people_types', [$this, 'erp_field_builder_people_types'] );
        add_filter( 'erp_crm_get_contacts_fields', [$this, 'get_contact_fields'], 10, 4 );
        // Recruitment
        add_filter( 'erp_personal_fields', [$this, 'export_recruitment_fields'] );

        // ERP Emport/Export CSV Fields
        add_filter( 'erp_import_export_csv_fields', [$this, 'add_erp_import_export_csv_fields'] );
    }

    /**
     * Get the current active people
     *
     * @return string
     */
    public function get_current_people() {

        return ( isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) ? $_GET['tab'] : $this->get_first_poeple();

    }

    /**
     * Get the current active modules
     * @return array
     */
    public function get_active_modules() {

        return wperp()->modules->get_active_modules();
    }

    /**
     * Get the current people type
     * @return array
     */
    public function get_people_types() {

        return erp_get_people_types();
    }

    /**
     * Get the sections of a currently active people
     * @return array
     */
    public function get_current_people_sections() {

        $peoples = $this->get_people_types();
        $current = $this->get_current_people();

        if ( is_array( $peoples ) ) {

            foreach ($peoples as $people) {

                if ( array_key_exists( $current, $people ) ) {

                    $sections = $people[$current]['sections'];
                }

                if ( isset( $sections ) ) {
                    return $sections;
                }
            }
        }

    }

    /**
     * Generates the first people type when no tab selected
     *
     * @return string
     */
    public function get_first_poeple() {

        $peoples = $this->get_people_types();

        if ( $peoples ) {

            return array_keys( array_values( $peoples )[0] )[0];
        }
    }

    /**
     * Profile builder Main Page
     */
    public function field_builder_callback( ) {

        $active_modules = $this->get_active_modules();
        $current        = $this->get_current_people();
        $peoples        = $this->get_people_types();

        // Returns if no module active
        if ( empty( $active_modules ) ) {
            wp_die( 'No Module Activated' );
        }

        // Profile View
        include WPERP_PPL_VIEWS . '/view.php';

    }

    /**
     * [erp_field_builder_handler description]
     * @return [type] [description]
     */
    public function erp_field_builder_handler() {

        if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'erp-form-builder' ) ) {
            die( 'You are no allowed' );
        }


        $collection = isset( $_REQUEST['collection']) ? wp_unslash( $_REQUEST['collection'] ) : '';
        $people     = isset( $_REQUEST['people']) ? $_REQUEST['people'] : '';
        $option_id  = 'erp-' . $people . '-fields';

        update_option( $option_id, $collection );
    }


    /**
     * Exports extra fields to recruitment addon
     *
     * @param  array $fields
     *
     * @return array
     */
    public function export_recruitment_fields( $fields ) {

        $extra_fields = get_option( 'erp-employee-fields' );

        $new_fields = [ ];
        $count = 0;
        if ( is_array($extra_fields) ) {
            foreach ( $extra_fields as $single ) {

                $new_fields[$count] = [
                    'label'       => $single['label'],
                    'name'        => $single['name'],
                    'section'     => $single['section'],
                    'icon'        => $single['icon'],
                    'required'    => $single['required'],
                    'type'        => $single['type'],
                    'placeholder' => $single['placeholder'],
                    'helptext'    => $single['helptext'],
                ];

                if ( is_array( $single['options'] ) && !empty( $single['options'] ) ) {
                    foreach ( $single['options'] as $opt ) {
                        $new_fields[$count]['options'][$opt['value']] = $opt['text'];
                    }
                }

                $count++;
            }
        }

        if ( is_array( $new_fields ) ) {
            foreach ( $new_fields as $single_field ) {
                $fields[$single_field['name']] = $single_field;
            }
        }

        return $fields;
    }

    /**
     * Exports field to Employee for Top Section
     *
     * @return null
     */
    public function export_employee_top() {

        $top_fields = $this->get_employee_fields_by_section( 'top' );

        $this->generate_hrm_html_field( $top_fields );
    }

    /**
     * Exports field to Employee for basic section
     *
     * @return null
     */
    public function export_employee_basic() {

        $basic_fields = $this->get_employee_fields_by_section( 'basic' );

        $this->generate_hrm_html_field( $basic_fields );
    }

    /**
     * Exports field to Employee for Work Section
     *
     * @return null
     */
    public function export_employee_work() {

        $work_fields = $this->get_employee_fields_by_section( 'work' );

        $this->generate_hrm_html_field( $work_fields );
    }

    /**
     * Exports field to Employee for Personal Section
     *
     * @return null
     */
    public function export_employee_personal() {

        $personal_fields = $this->get_employee_fields_by_section( 'personal' );

        $this->generate_hrm_html_field( $personal_fields );
    }

    /**
     * Exports field to Employee for Employee form Settings
     *
     * @return null
     */
    public function export_employee_bottom() {

        $bottom_fields = $this->get_employee_fields_by_section( 'bottom' );

        $this->generate_hrm_html_field( $bottom_fields );
    }

    /**
     * Get Employee Fields by section
     *
     * @return array
     */
    public function get_employee_fields_by_section( $section ) {

        $employee_fields = get_option( 'erp-employee-fields' );
        $filtered_fields = [];

        if ( is_array( $employee_fields ) ) {

            foreach( $employee_fields as $field ) {

                if ( $section == $field['section'] ) {

                    $filtered_fields[] = $field;
                }
            }
        }

        return $filtered_fields;
    }

    /**
     * Generate HRM html field
     *
     * @return mixed
     */
    public function generate_hrm_html_field( $fields ) {

        if ( is_array( $fields ) ) {

            foreach ( $fields as $field ) {

                $options = [];

                foreach ( $field['options'] as $option ) {

                    $options[$option['value']] = $option['text'];
                }

                if( 'checkbox' == $field['type'] ) {
                    echo '<div class="col-3" data-selected="{{ data.additional.'. $field['name'] .' }}">';
                    erp_html_form_input( array(
                        'label'       => $field['label'],
                        'name'        => 'additional['. $field['name'].'][]',
                        'required'    => 'true' == $field['required'] ? true : false,
                        'type'        => 'multicheckbox',
                        'options'     => $options,
                        'class'       => 'erp-field-builder-clear'
                    ) );
                    echo '<input type="hidden" value="" name="additional[' . $field['name'] . '][]">';
                    echo '</div>';

                } else if ( 'date' == $field['type'] ) {
                    echo '<div class="col-3">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => 'additional['. $field['name'].']',
                        'value'    => '{{ data.additional.'.$field['name'].' }}',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'     => 'text',
                        'class'    => 'erp-date-field '
                    ) );
                    echo '</div>';
                } else if ( 'select' == $field['type'] ) {
                    echo '<div class="col-3" data-selected="{{ data.additional.'. $field['name'] .' }}">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => 'additional['. $field['name'].']',
                        'value'    => '{{ data.additional.'.$field['name'].' }}',
                        'class'    => 'erp-hrm-select2',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'     => 'select',
                        'options'  => $options
                    ) );
                    echo '</div>';
                } else if ( 'radio' == $field['type'] ) {
                    echo '<div class="col-3" data-selected="{{ data.additional.'. $field['name'] .' }}">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => 'additional['. $field['name'].']',
                        'value'    => '{{ data.additional.'.$field['name'].' }}',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'     => 'radio',
                        'options'  => $options,
                    ) );
                    echo '</div>';
                } else {
                    echo '<div class="col-3">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => 'additional['. $field['name'].']',
                        'placeholder' => $field['placeholder'],
                        'value'    => '{{ data.additional.'.$field['name'].' }}',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'     => $field['type'],
                        'options'  => $options,
                    ) );
                    echo '</div>';
                }
            }
        }
    }

    /**
     * Generate CRM html field
     *
     * @return mixed
     */
    public function generate_crm_html_field( $fields ) {

        if ( is_array( $fields ) ) {

            foreach ( $fields as $field ) {

                $options = [];

                foreach ( $field['options'] as $option ) {

                    $options[$option['value']] = $option['text'];
                }

                if( 'checkbox' == $field['type'] ) {

                    echo '<div class="col-3" data-selected="{{ data.'. $field['name'] .' }}">';
                    erp_html_form_input( array(
                        'label'       => $field['label'],
                        'name'        => 'contact[meta]['. $field['name'].'][]',
                        //                        'value'    => '{{ data.contact[meta].'.$field['name'].' }}',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'        => 'multicheckbox',
                        'options'     => $options
                    ) );
                    echo '</div>';

                } else if ( 'date' == $field['type'] ) {

                    echo '<div class="col-3" data-selected="{{ data.'. $field['name'] .' }}">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => 'contact[meta]['. $field['name'].']',
                        'value'    => '{{ data.'.$field['name'].' }}',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'     => 'text',
                        'class' => 'erp-date-field'
                    ) );
                    echo '</div>';
                } else {
                    echo '<div class="col-3" data-selected="{{ data.'. $field['name'] .' }}">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => 'contact[meta]['. $field['name'].']',
                        'placeholder' => $field['placeholder'],
                        'value'    => '{{ data.'.$field['name'].' }}',
                        'required' => 'true' == $field['required'] ? true : false,
                        'type'     => $field['type'],
                        'options'  => $options,
                    ) );
                    echo '</div>';
                }
            }
        }
    }

    /**
     * Save Employee Fields
     *
     * @return null
     */
    public function save_employee_all_section( $user_id, $data ) {

        if ( isset( $data['additional'] ) && is_array( $data['additional'] ) ) {

            foreach ( $data['additional'] as $key => $value ) {

                update_user_meta( $user_id, $key, $value );
            }
        }
    }

    /**
     * Get All Employee Fields
     *
     * @return array
     */
    public function get_employee_fields( $fields, $id, $user ){

        $saved_fields = get_option( 'erp-employee-fields' );

        if ( is_array( $saved_fields ) ) {

            foreach ( $saved_fields as $field ) {

                $data                                 =  get_user_meta( $id, $field['name'], true );
                $fields['additional'][$field['name']] = ( false != $data ) ? $data : '';
            }
        }

        return $fields;
    }

    /**
     * Employee Additional Fields Update
     *
     * @return null
     */
    public function update_employee( $user_id, $data ) {

        if ( is_array( $data['additional'] ) ) {

            foreach ( $data['additional'] as $key => $value ) {

                update_user_meta( $user_id, $key, $value );
            }
        }
    }

    /**
     * Basic Infos to show Employee single page
     */
    public function employee_single_page_basic( $employee ) {

        $this->get_employee_single_html_by_section( $employee->id, 'basic');
        $this->get_employee_single_html_by_section( $employee->id, 'top');
        $this->get_employee_single_html_by_section( $employee->id, 'bottom');
    }

    /**
     * Basic Infos to show Employee single page
     */
    public function employee_single_page_work( $employee ) {

        $this->get_employee_single_html_by_section( $employee->id, 'work' );
    }

    /**
     * Basic Infos to show Employee single page
     */
    public function employee_single_page_personal( $employee ) {

        $this->get_employee_single_html_by_section( $employee->id, 'personal' );
    }

    /**
     * Basic Infos to show Employee single page
     */
    public function employee_single_page_after_personal( $employee ) {

    }


    /**
     * Basic Infos to show Employee single page
     */
    public function employee_single_page_bottom( $employee ) {

        //$this->get_employee_single_html_by_section( $employee->id, 'bottom' );
    }

    /**
     * Generates HTML for Employee single field by section
     *
     * @since 1.0.0
     * @since 1.1.1 Use `print_custom_field` method to print fields
     *
     * @param  int $employee_id
     * @param  str $section
     *
     * @return void
     */
    public function get_employee_single_html_by_section( $employee_id, $section ) {

        $data   = $this->get_employee_fields_by_section( $section );

        foreach ( $data as $field ) {
            $value =  get_user_meta( $employee_id, $field['name'], true );
            $this->print_custom_field( $field, $value );
        }

    }

    /**
     * CRM related people types
     *
     * @since 1.0
     *
     * @param  array $types
     *
     * @return array
     */
    public function erp_field_builder_people_types( $types ) {
        $types = [
            'hrm' => [
                'employee' => [
                    'label'    => __( 'Employee', 'erp-pro' ),
                    'sections' => [
                        'top'      => __( 'Top Area', 'erp-pro' ),
                        'basic'    => __( 'Basic Information', 'erp-pro' ),
                        'work'     => __( 'Work Information', 'erp-pro' ),
                        'personal' => __( 'Personal Information', 'erp-pro' ),
                        'bottom'   => __( 'Bottom Area', 'erp-pro' ),
                    ]
                ]
            ],

            'crm' => [
                'contact' => [
                    'label'    => __( 'Contact', 'erp-pro' ),
                    'sections' => [
                        'top'        => __( 'Top Area', 'erp-pro' ),
                        'basic'      => __( 'Basic Information', 'erp-pro' ),
                        'other'      => __( 'Others Information', 'erp-pro' ),
                        'group'      => __( 'Contact Group', 'erp-pro' ),
                        'additional' => __( 'Additional Information', 'erp-pro' ),
                        'social'     => __( 'Social Profile', 'erp-pro' ),
                        'bottom'     => __( 'Bottom Area', 'erp-pro' ),
                    ]
                ],
                'company' => [
                    'label'    => __( 'Company', 'erp-pro' ),
                    'sections' => [
                        'top'        => __( 'Top Area', 'erp-pro' ),
                        'basic'      => __( 'Basic Information', 'erp-pro' ),
                        'other'      => __( 'Others Information', 'erp-pro' ),
                        'group'      => __( 'Contact Group', 'erp-pro' ),
                        'additional' => __( 'Additional Information', 'erp-pro' ),
                        'social'     => __( 'Social Profile', 'erp-pro' ),
                        'bottom'     => __( 'Bottom Area', 'erp-pro' ),
                    ]

                ]
            ],

            'accounting' => [
                'customer' => [
                    'label'    => __( 'Customer', 'erp-pro' ),
                    'sections' => [
                        'top'        => __( 'Top Area', 'erp-pro' ),
                        'middle'     => __( 'Middle Area', 'erp-pro' ),
                        'bottom'     => __( 'Bottom Area', 'erp-pro' ),
                    ]
                ],
                'vendor' => [
                    'label'    => __( 'Vendor', 'erp-pro' ),
                    'sections' => [
                        'top'        => __( 'Top Area', 'erp-pro' ),
                        'middle'     => __( 'Middle Area', 'erp-pro' ),
                        'bottom'     => __( 'Bottom Area', 'erp-pro' ),
                    ]
                ]
            ],
        ];

        return apply_filters( 'erp_field_builder_people_types', $types );
    }

    /**
     * Exports field to Customer for Top Section
     *
     * @return null
     */
    public function export_contact_top() {

        $top_fields = $this->get_contact_fields_by_section( 'top' );

        $this->generate_crm_html_field( $top_fields );
    }

    /**
     * Exports field to Customer for basic section
     *
     * @return null
     */
    public function export_contact_basic() {

        $basic_fields = $this->get_contact_fields_by_section( 'basic' );

        $this->generate_crm_html_field( $basic_fields );
    }

    /**
     * Exports field to Customer for Work Section
     *
     * @return null
     */
    public function export_contact_other() {

        $other_fields = $this->get_contact_fields_by_section( 'other' );

        $this->generate_crm_html_field( $other_fields );
    }

    /**
     * Exports field to Customer for Contact Group
     *
     * @return null
     */
    public function export_contact_contact_group() {

        $group_fields = $this->get_contact_fields_by_section( 'group' );

        $this->generate_crm_html_field( $group_fields );
    }

    /**
     * Exports field to Customer for Personal Section
     *
     * @return null
     */
    public function export_contact_additional() {

        $additional_fields = $this->get_contact_fields_by_section( 'additional' );

        $this->generate_crm_html_field( $additional_fields );
    }

    /**
     * Exports field to Customer for Social Section
     *
     * @return null
     */
    public function export_contact_social() {

        $social_fields = $this->get_contact_fields_by_section( 'social' );

        $this->generate_crm_html_field( $social_fields );
    }

    /**
     * Exports field to Customer form Settings
     *
     * @return null
     */
    public function export_contact_bottom() {

        $bottom_fields = $this->get_contact_fields_by_section( 'bottom' );

        $this->generate_crm_html_field( $bottom_fields );
    }

    /**
     * Get Customer Fields by section
     *
     * @return array
     */
    public function get_contact_fields_by_section( $section ) {

        $contact_fields = get_option( 'erp-contact-fields' );
        $filtered_fields = [];

        if ( is_array( $contact_fields ) ) {

            foreach( $contact_fields as $field ) {

                if ( $section == $field['section'] ) {

                    $filtered_fields[] = $field;
                }
            }
        }

        return $filtered_fields;
    }

    /**
     * Get All Contact Fields
     *
     * @return array
     */
    public function get_contact_fields( $fields, $data, $id, $types ){

        if ( is_array( $types ) && in_array( 'contact', $types ) ) {
            $saved_fields = get_option( 'erp-contact-fields' );
        }

        if ( is_array( $types ) && in_array( 'company', $types ) ) {
            $saved_fields = get_option( 'erp-company-fields' );
        }

        if ( isset( $saved_fields ) && is_array( $saved_fields ) ) {
            foreach ( $saved_fields as $field ) {
                $data                                 = ( $fields['user_id'] ) ? get_user_meta( $fields['user_id'], $field['name'], true ) : erp_people_get_meta( $id, $field['name'], true );
                $fields[$field['name']] = $data ? $data : '';
            }
        }

        return $fields;
    }

    /**
     * Show info in Contact single page basic section
     *
     * @since 1.0
     */
    public function contact_single_page_basic( $contact ) {

        $this->get_contact_single_html_by_section( $contact, 'top' );
        $this->get_contact_single_html_by_section( $contact, 'basic' );
        $this->get_contact_single_html_by_section( $contact, 'work' );
        $this->get_contact_single_html_by_section( $contact, 'personal' );
        $this->get_contact_single_html_by_section( $contact, 'bottom' );
    }

    /**
     * Generates HTML for Contact single field by section
     *
     * @since 1.0.0
     * @since 1.1.1 Use `print_custom_field` method to print fields
     *
     * @param  int $employee_id
     * @param  str $section
     * @return mix
     */
    public function get_contact_single_html_by_section( $contact, $section ) {

        $data   = $this->get_contact_fields_by_section( $section );

        foreach ( $data as $field ) {
            $value =  $contact->get_meta( $field['name'] );
            $this->print_custom_field( $field, $value );
        }

    }

    /**
     * Exports field to Customer for Top Section
     *
     * @return null
     */
    public function export_company_top() {

        $top_fields = $this->get_company_fields_by_section( 'top' );

        $this->generate_crm_html_field( $top_fields );
    }

    /**
     * Exports field to Customer for basic section
     *
     * @return null
     */
    public function export_company_basic() {

        $basic_fields = $this->get_company_fields_by_section( 'basic' );

        $this->generate_crm_html_field( $basic_fields );
    }

    /**
     * Exports field to Customer for Work Section
     *
     * @return null
     */
    public function export_company_other() {

        $other_fields = $this->get_company_fields_by_section( 'other' );

        $this->generate_crm_html_field( $other_fields );
    }

    /**
     * Exports field to Customer for Contact Group
     *
     * @return null
     */
    public function export_company_contact_group() {

        $group_fields = $this->get_company_fields_by_section( 'group' );

        $this->generate_crm_html_field( $group_fields );
    }

    /**
     * Exports field to Customer for Personal Section
     *
     * @return null
     */
    public function export_company_additional() {

        $additional_fields = $this->get_company_fields_by_section( 'additional' );

        $this->generate_crm_html_field( $additional_fields );
    }

    /**
     * Exports field to Customer for Social Section
     *
     * @return null
     */
    public function export_company_social() {

        $social_fields = $this->get_company_fields_by_section( 'social' );

        $this->generate_crm_html_field( $social_fields );
    }

    /**
     * Exports field to Customer form Settings
     *
     * @return null
     */
    public function export_company_bottom() {

        $bottom_fields = $this->get_company_fields_by_section( 'bottom' );

        $this->generate_crm_html_field( $bottom_fields );
    }

    /**
     * Get Customer Fields by section
     *
     * @return array
     */
    public function get_company_fields_by_section( $section ) {

        $company_fields = get_option( 'erp-company-fields' );
        $filtered_fields = [];

        if ( is_array( $company_fields ) ) {

            foreach( $company_fields as $field ) {

                if ( $section == $field['section'] ) {

                    $filtered_fields[] = $field;
                }
            }
        }

        return $filtered_fields;
    }

    /**
     * Show info in Company single page basic section
     *
     * @since 1.0
     */
    public function company_single_page_basic( $contact ) {

        $this->get_company_single_html_by_section( $contact, 'top' );
        $this->get_company_single_html_by_section( $contact, 'basic' );
        $this->get_company_single_html_by_section( $contact, 'work' );
        $this->get_company_single_html_by_section( $contact, 'personal' );
        $this->get_company_single_html_by_section( $contact, 'bottom' );
    }

    /**
     * Generates HTML for Company single field by section
     *
     * @since 1.0.0
     * @since 1.1.1 Use `print_custom_field` method to print fields
     *
     * @param  int $employee_id
     * @param  str $section
     * @return mix
     */
    public function get_company_single_html_by_section( $company, $section ) {

        $data   = $this->get_company_fields_by_section( $section );

        foreach ( $data as $single ) {
            $value =  $company->get_meta( $single['name'] );
            $this->print_custom_field( $single, $value );
        }

    }

    /**
     * Show contact social fields description
     * @return array
     *
     * @since 1.0
     */
    public function show_contact_social_fields( $customer ) {

        $saved_fields = get_option( 'erp-contact-fields' );
        if ( $saved_fields ) {
            foreach( $saved_fields as $field ) {
                if ( 'social' == $field['section'] ) {
                    $icon_value = $customer->get_meta($field['name']);
                    if ( $icon_value ) {
                        ?>
                        <li>
                            <a href="<?php echo $icon_value; ?>"><i class="fa fa-<?php echo $field['icon']; ?>"></i></a>
                        </li>
                        <?php
                    }
                }
            }
        }
    }

    /**
     * Show company social fields description
     * @return array
     *
     * @since 1.0
     */
    public function show_company_social_fields( $customer ) {

        $saved_fields = get_option( 'erp-company-fields' );
        if ( $saved_fields ) {
            foreach( $saved_fields as $field ) {
                if ( 'social' == $field['section'] ) {
                    $icon_value = $customer->get_meta($field['name']);
                    if ( $icon_value ) {
                        ?>
                        <li>
                            <a href="<?php echo $icon_value; ?>"><i class="fa fa-<?php echo $field['icon']; ?>"></i></a>
                        </li>
                        <?php
                    }
                }
            }
        }
    }

    /**
     * Add field builder fields to import export
     *
     * @param  array $fields
     *
     * @return array
     */
    public function add_erp_import_export_csv_fields( $fields ) {
        $contact_fields  = get_option( 'erp-contact-fields', [] );
        $company_fields  = get_option( 'erp-company-fields', [] );
        $employee_fields = get_option( 'erp-employee-fields', [] );

        if ( ! empty( $contact_fields ) ) {
            $contact_fields = array_map( function( $field ) {
                return $field['name'];
            }, $contact_fields );

        // ensure field is an array before array_merge
        } else if ( ! is_array( $contact_fields ) ) {
            $contact_fields = [];
        }

        if ( ! empty( $company_fields ) ) {
            $company_fields = array_map( function( $field ) {
                return $field['name'];
            }, $company_fields );

        } else if ( ! is_array( $company_fields ) ) {
            $company_fields = [];
        }

        if ( ! empty( $employee_fields ) ) {
            $employee_fields = array_map( function( $field ) {
                return $field['name'];
            }, $employee_fields );

        } else if ( ! is_array( $employee_fields ) ) {
            $employee_fields = [];
        }

        $fields['contact']['fields']  = array_merge( $fields['contact']['fields'], $contact_fields );
        $fields['company']['fields']  = array_merge( $fields['company']['fields'], $company_fields );
        $fields['employee']['fields'] = array_merge( $fields['employee']['fields'], $employee_fields );

        if ( array_key_exists( 'vendor', $fields ) ) {
            $vendor_fields   = get_option( 'erp-vendor-fields', [] );

            if ( ! empty( $vendor_fields ) && is_array( $vendor_fields ) ) {
                $vendor_fields = array_map( function( $field ) {
                    return $field['name'];
                }, $vendor_fields );
            } else {
                $vendor_fields = [];
            }

            $fields['vendor']['fields']   = array_merge( $fields['vendor']['fields'], $vendor_fields );
        }

        return $fields;
    }

    /**
     * Print custom fields
     *
     * @since 1.1.1
     *
     * @param array        $field
     * @param array|string $value
     *
     * @return void
     */
    public function print_custom_field( $field, $value ) {

        foreach( $field['options'] as $option ) {

            if ( '' != $option['value'] && ( $value == $option['value'] ) ) {

                $value = $option['text'];

                break;
            }

            if ( is_array( $value ) && ! empty( $field['options'] ) ) {
                $selected = array_filter( $field['options'], function ( $option ) use ( $value ) {

                    foreach ( $value as $val ) {
                        if ( $val === $option['value'] ) {
                            return true;
                        }
                    }

                    return false;

                } );

                if ( empty( $selected ) ) {
                    $value = '—';

                } else {
                    $selected_values = wp_list_pluck( $selected, 'text' );

                    $value = implode( ', ', $selected_values );
                }
            }
        }

        $type = isset( $field['type'] ) ? $field['type'] : 'text';

        echo '<li>';
        erp_print_key_value( $field['label'], $value, ' : ', $type );
        echo '</li>';

    }

    /**
     * Export customer field on top section
     *
     * @return void
     */
    public function export_customer_top( $item ) {
        $customer_fields = $this->get_customer_field_by_section( 'top' );
        $this->generate_accounting_html_field( $customer_fields, $item );
    }

    /**
     * Export customer field on bottom section
     *
     * @return void
     */
    public function export_customer_bottom( $item ) {
        $customer_fields = $this->get_customer_field_by_section( 'bottom' );

        $this->generate_accounting_html_field( $customer_fields, $item );
    }

    /**
     * Export customer field on middle section
     *
     * @return void
     */
    public function export_customer_middle( $item ) {
        $customer_fields = $this->get_customer_field_by_section( 'middle' );

        $this->generate_accounting_html_field( $customer_fields, $item );
    }

    /**
     * Get customer field by section
     *
     * @param  string $section
     * @return void
     */
    public function get_customer_field_by_section( $section ) {
        $customer_fields = get_option( 'erp-customer-fields' );
        $filtered_fields = [];

        if ( is_array( $customer_fields ) ) {

            foreach( $customer_fields as $field ) {

                if ( $section == $field['section'] ) {

                    $filtered_fields[] = $field;
                }
            }
        }

        return $filtered_fields;
    }

    /**
     * Export customer field on top section
     *
     * @return void
     */
    public function export_vendor_top( $item ) {
        $customer_fields = $this->get_vendor_field_by_section( 'top' );

        $this->generate_accounting_html_field( $customer_fields, $item );
    }

    /**
     * Export customer field on bottom section
     *
     * @return void
     */
    public function export_vendor_bottom( $item ) {
        $customer_fields = $this->get_vendor_field_by_section( 'bottom' );

        $this->generate_accounting_html_field( $customer_fields, $item );
    }

    /**
     * Export customer field on middle section
     *
     * @return void
     */
    public function export_vendor_middle( $item ) {
        $customer_fields = $this->get_vendor_field_by_section( 'middle' );

        $this->generate_accounting_html_field( $customer_fields, $item );
    }

    /**
     * Get vendor field by section
     *
     * @param  string $section
     * @return void
     */
    public function get_vendor_field_by_section( $section ) {
        $customer_fields = get_option( 'erp-vendor-fields' );
        $filtered_fields = [];

        if ( is_array( $customer_fields ) ) {

            foreach( $customer_fields as $field ) {

                if ( $section == $field['section'] ) {

                    $filtered_fields[] = $field;
                }
            }
        }

        return $filtered_fields;
    }

    /**
     * Generate accounting html form
     *
     * @param  array $fields
     *
     * @return void
     */
    public function generate_accounting_html_field( $fields, $item ) {
        if ( is_array( $fields ) ) {
            foreach ( $fields as $field ) {
                $item_id            = ! empty( $item->id ) ? $item->id : 0;
                $customer_meta_data = erp_people_get_meta( $item_id, 'customer_custom_field' );
                $field_value        = isset( $customer_meta_data[0][ $field['name'] ] ) ? $customer_meta_data[0][ $field['name'] ] : '';

                foreach ( $field['options'] as $option ) {

                    $options[$option['value']] = $option['text'];
                }

                if( 'checkbox' == $field['type'] ) {

                    echo '<li class="erp-form-field">';
                    erp_html_form_input( array(
                        'label'       => $field['label'],
                        'name'        => $field['name'],
                        'id'          => $field['name'],
                        'required'    => 'true' == $field['required'] ? true : false,
                        'type'        => 'multicheckbox',
                        'placeholder' => $field['placeholder'],
                        'help'        => $field['helptext'],
                        'class'       => 'hasinur',
                        'value'       => $field_value,
                        'options'     => $options,
                        'custom_attr' => array( 'style' => '' )
                    ) );
                    echo '</li>';

                } else if ( 'date' == $field['type'] ) {

                    echo '<li class="erp-form-field">';
                    erp_html_form_input( array(
                        'label'    => $field['label'],
                        'name'     => $field['name'],
                        'id'       => $field['name'],
                        'value'    => $field_value,
                        'required' => 'true' == $field['required'] ? true : false,
                        'placeholder' => $field['placeholder'],
                        'help'        => $field['helptext'],
                        'type'     => 'text',
                        'class' => 'regular-text erp-date-field'
                    ) );
                    echo '</li>';
                } else if ( 'select' == $field['type'] ) {
                    echo '<li class="erp-form-field">';
                    erp_html_form_input( array(
                        'label'       => $field['label'],
                        'name'        => $field['name'],
                        'id'          => $field['name'],
                        'placeholder' => $field['placeholder'],
                        'help'        => $field['helptext'],
                        'value'       => $field_value,
                        'required'    => 'true' == $field['required'] ? true : false,
                        'type'        => 'select',
                        'class'       => 'erp-state-select erp-select2',
                        'options'     => array_merge( array( '' => __( '- Select -', 'erp' ) ), $options ),
                        'custom_attr' => array( 'style' => 'width:350px;' )
                        ) );
                    echo '</li>';
                } else {
                    echo '<li class="erp-form-field">';
                    erp_html_form_input( array(
                        'label'         => $field['label'],
                        'name'          => $field['name'],
                        'id'            => $field['name'],
                        'placeholder'   => $field['placeholder'],
                        'help'          => $field['helptext'],
                        'value'         => $field_value,
                        'required'      => 'true' == $field['required'] ? true : false,
                        'type'          => $field['type'],
                        'options'       => $options,
                        'class'         => 'regular-text',
                    ) );
                    echo '</li>';
                }
            }
        }
    }

    /**
     * Save customer or additional field  data
     *
     * @param  integer $customer_id
     * @param  array $post_data
     * @return bool
     */
    public function save_customer_data( $customer_id, $post_data ) {
        erp_people_update_meta( $customer_id, 'customer_custom_field', $post_data );
    }

    /**
     * Save customer data
     *
     * @param  integer $customer_id
     * @param  array $post_data
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function save_acct_customer_data( $people_id, $post_data, $people_type ) {
        if ( 'customer' === $people_type ) {
            $data = $this->get_acct_people_custom_fields_name( 'customer', $post_data['raw_data'] );
            erp_people_update_meta( $people_id, 'customer_custom_field', $data );

        } elseif( 'vendor' === $people_type ) {
            $data = $this->get_acct_people_custom_fields_name( 'vendor', $post_data['raw_data'] );
            erp_people_update_meta( $people_id, 'vendor_custom_field', $data );
        }
    }

    /**
     * People custom fields name
     *
     * @param string $type
     * @param array $post_data
     *
     * @since 1.3.0
     *
     * @return array
     */
    public function get_acct_people_custom_fields_name( $type, $data ) {
        $raw_fields = get_option( 'erp-' . $type . '-fields' );
        $filtered_fields = [];

        if ( is_array( $raw_fields ) ) {
            $keys = array_column( $raw_fields, 'name' );

            foreach ( $keys as $key ) {
                if ( array_key_exists( $key, $data ) ) {
                    $filtered_fields[$key] = $data[$key];
                }
            }
        }

        return $filtered_fields;
    }

    /**
     * Add key to CRM search segment
     *
     * @param string $type
     * @param array $fields
     *
     * @since 1.3.0
     *
     * @return array
     */
    public function add_key_to_segment_search( $fields, $type ) {

        $search_filed = get_option('erp-' . $type . '-fields', []);
        if (!empty($search_filed) && count($search_filed) > 0) {
            foreach ($search_filed as $sf) {
                $fields[$sf['name']] = array(
                    'title'     => $sf['label'],
                    'type'      => 'text',
                    'text'      => '',
                    'condition' => array(
                        ''   => __('is', 'erp-pro'),
                        '!'  => __('is not', 'erp-pro'),
                        '~'  => __('contains', 'erp-pro'),
                        '!~' => __('not contains', 'erp-pro'),
                        '^'  => __('begins with', 'erp-pro'),
                        '$'  => __('ends with', 'erp-pro'),
                    )
                );
            }
        }
        return $fields;
    }


    /**
     * Add key to CRM search segment query
     *
     * @param array $fields
     *
     * @since 1.3.0
     *
     * @return array
     */
    public function add_key_to_segment_query_search( $fields ) {

        $erp_custom_contact_fields = get_option('erp-contact-fields', []);
        $erp_custom_company_fields = get_option('erp-company-fields', []);

        if (!empty($erp_custom_contact_fields) && count($erp_custom_contact_fields) > 0) {
            foreach ($erp_custom_contact_fields as $eccf) {
                $fields[] = $eccf['name'];
            }
        }

        if (!empty($erp_custom_company_fields) && count($erp_custom_company_fields) > 0) {
            foreach ($erp_custom_company_fields as $eccmf) {
                $fields[] = $eccmf['name'];
            }
        }

        return $fields;
    }

}
