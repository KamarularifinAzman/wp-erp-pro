<?php
namespace WeDevs\HrFrontend;

/**
 * Settings class
 */
class DashboardSettings {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_scripts'] );

        // Add a section to HR Settings
        add_filter( 'erp_settings_hr_sections', [ $this, 'add_hr_frontend_sections' ] );

        // Add fields to ERP Settings frontend section
        add_filter( 'erp_settings_hr_section_fields', [ $this, 'add_hr_frontend_section_fields' ], 11, 2 );
    }

    /**
     * Register & Enqueue settings scripts
     *
     * @since 2.1.3
     *
     * @return void
     */
    public function enqueue_settings_scripts() {
        if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'erp-settings' ) {
            wp_register_script( 'erp-hrm-frontend-settings', ERP_DASHBOARD_ASSETS . '/js/settings.js', [ 'erp-settings' ], false, true );
            wp_enqueue_script( 'erp-hrm-frontend-settings' );
        }
    }

    /**
     * Add Attendance Sections to ERP Settings Page
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function add_hr_frontend_sections( $sections ) {

        $sections ['hr_frontend'] = __( 'HR Frontend', 'erp-pro' );
        return $sections;
    }

    /**
     * Add fields to HR Frontend Section in ERP Fields
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function add_hr_frontend_section_fields( $fields, $section ) {
        if ( 'hr_frontend' == $section ) {

            $fields['hr_frontend'] = [
                [
                    'title' => __( 'HR Frontend', 'erp-pro' ),
                    'type'  => 'title',
                    'id'    => 'hr_frontend_title',
                    'desc'  => __( 'HR Frontend Settings', 'erp-pro' )
                ],
                [
                    'title'   => __( 'HR Dashbord Slug', 'erp-pro' ),
                    'type'    => 'text',
                    'id'      => 'hr_frontend_slug',
                    'desc'    => __( 'Your custom slug', 'erp-pro' ),
                    'default' => 'wp-erp'
                ],
                [
                    'title'   => __( 'HR Dashbord Title', 'erp-pro' ),
                    'type'    => 'text',
                    'id'      => 'hr_frontend_dashboard_title',
                    'desc'    => __( 'Your custom title', 'erp-pro' ),
                    'default' => __( 'WP ERP', 'erp-pro' )
                ],
                [
                    'title'   => __( 'HR Dashbord Logo', 'erp-pro' ),
                    'type'    => 'image',
                    'id'      => 'hr_frontend_logo',
                    'desc'    => __( 'Your dashbord custom logo', 'erp-pro' )
                ],
                [
                    'title'   => __( 'Redirect to frontend', 'erp-pro' ),
                    'type'    => 'checkbox',
                    'id'      => 'hr_frontend_redirect',
                    'desc'    => __( 'Check if you want to redirect to frontend', 'erp-pro' )
                ],
            ];

            $fields['hr_frontend'][] = [
                'type'  => 'sectionend',
                'id'    => 'script_styling_options'
            ];

        }

        return $fields;
    }

}
