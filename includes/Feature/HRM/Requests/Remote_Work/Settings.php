<?php
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Remote_Work;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Settings handler class
 * 
 * @since 1.2.0
 */
class Settings {

    use Hooker;

    /**
     * CLass constructor
     * 
     * @since 1.2.0
     */
    function __construct() {
		$this->filter( 'erp_settings_hr_sections', 'add_remote_work_section' );
		$this->filter( 'erp_settings_hr_section_fields', 'add_remote_work_section_fields', 10, 2 );
    }

    /**
     * Adds remote work section in hr settings tab
     * 
     * @since 1.2.0
     *
     * @param array $sections
     * 
     * @return array
     */
    public function add_remote_work_section( $sections ) {
        $index = array_search( 'financial', array_keys( $sections ) );

        $index = false === $index ? count( $sections ) : $index + 1;

        $sections = array_slice( $sections, 0, $index ) + [
            'remote_work' => __( 'Remote Work', 'erp-pro' )
        ] + array_slice( $sections, $index );

		return $sections;
	}

    /**
     * Adds remote work settings section fields
     * 
     * @since 1.2.0
     *
     * @param array $fields
     * @param string $section
     * 
     * @return array
     */
    public function add_remote_work_section_fields( $fields, $section ) {
		
        if ( 'remote_work' == $section ) {
			$fields['remote_work'] = [
				[
					'title' => __( 'Remote Work', 'erp-pro' ),
					'type'  => 'title',
                    'desc'  => __( 'Remote work settings for this company.', 'erp-pro' ),
					'id'    => 'erp_hr_remote_work_title'
				],
				[
					'title' => __( 'Enable Remote Work', 'erp-pro' ),
					'type'  => 'checkbox',
					'id'    => 'erp_hr_remote_work_enable',
					'desc'  => __( 'Enable working from home facility for employees?', 'erp-pro' )
				],
                [
                    'type' => 'sectionend',
                    'id'   => 'script_styling_options'
                ]
			];
		}

		return $fields;
	}
}