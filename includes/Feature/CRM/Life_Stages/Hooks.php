<?php

namespace WeDevs\ERP_PRO\Feature\CRM\Life_Stages;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Action and Filter hooks
 *
 * @since 1.0.1
 */
class Hooks {

    use Hooker;

    /**
     * The class constructor
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function __construct() {
        // script hooks
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );

        // life stage hooks
        $this->filter( 'erp_crm_life_stage_counts', 'get_life_stage_counts' );
        $this->filter( 'erp_crm_life_stages', 'set_life_stages', 10, 2 );

        // Reports hooks
        $this->filter( 'erp_crm_growth_report', 'get_growth_report' );
        $this->filter( 'erp_crm_customer_report', 'get_customer_report' );

        // settings hooks
        $this->filter( 'erp_settings_crm_sections', 'crm_life_stage_section' );
        $this->action( 'erp_settings_crm_section_fields', 'crm_life_stage_section_fields', 10, 2 );

        // contacts menu hook
        $this->filter( 'erp_crm_contacts_menu_items', 'contacts_menu_items' );
    }

    /**
     * Includes necessary items in contacts menu
     *
     * @since 1.2.4
     *
     * @param array $dropdown
     *
     * @return array
     */
    public function contacts_menu_items( $dropdown ) {
        $dropdown['crm_life_stages'] = array(
            'title' => esc_html__( 'Life Stages', 'erp-pro' ),
            'caps'  => 'manage_options',
        );

        return $dropdown;
    }

    /**
     * Sets default life stage counts
     *
     * @since 1.0.1
     *
     * @param array $counts
     *
     * @return array
     */
    public function get_life_stage_counts( $counts = [] ) {
        $stage_counts = array();
        $life_stages  = Helpers::get_life_stages();

        foreach ( $life_stages as $life_stage ) {
            $stage_counts[ $life_stage['slug'] ] = 1;
        }

        $stage_counts = wp_parse_args( $counts, $stage_counts );

        return $stage_counts;
    }

    /**
     * Retrives life stages informtion
     *
     * @since 1.0.1
     *
     * @param array $life_stages
     * @param array $counts
     *
     * @return array
     */
    public function set_life_stages() {
        // old version compatibility
        $provided_args = func_get_args();

        $life_stages = $provided_args[0];

        if ( count( $provided_args ) < 2 ) {
            return $life_stages;
        }

        $counts = $provided_args[1];

        $life_stages = Helpers::get_life_stages();

        $ret = [];

        foreach ( $life_stages as $life_stage ) {
            if (  array_key_exists( $life_stage['slug'], $counts )  ) {
                $ret[ $life_stage['slug'] ] = _n( $life_stage['title'], $life_stage['title_plural'], $counts[ $life_stage['slug'] ], 'erp' );
            }
        }
        return $ret;
    }

    /**
     * Retrieves information for growth report chart
     *
     * @since 1.0.1
     *
     * @param array $reports
     *
     * @return array
     */
    public function get_growth_report( $reports ) {
        $life_stages = $this->get_life_stage_counts();

        foreach ( $life_stages as $key => $val ) {
            $life_stages[ $key ] = 0;
        }

        $ret = [];

        foreach ( $reports as $month => $report ) {
            $ret[ $month ] = wp_parse_args( $report, $life_stages );
        }

        return $ret;
    }

    /**
     * Retrieves information for customer report
     *
     * @since 1.0.1
     *
     * @param array $reports
     *
     * @return array
     */
    public function get_customer_report( $data ) {
        $life_stages = $this->get_life_stage_counts();

        foreach ( $life_stages as $key => $val ) {
            $life_stages[ $key ] = 0;
        }

        $ret = wp_parse_args( $data, $life_stages );

        return $ret;
    }

    /**
     * Load admin scripts
     *
     * @since 1.2.4
     *
     * @param $hook_suffix
     *
     * @return void
     */
    public function admin_scripts( $hook_suffix ) {
        $erp_life_stages_global = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'erp-life-stages' ),
            'lsLimit' => apply_filters( 'erp_crm_limit_life_stage', 10 ),
        );

        if ( "wp-erp_page_erp-settings" === $hook_suffix ) {
            wp_enqueue_style(
                'erp-life-stages-settings',
                ERP_PRO_FEATURE_URL . '/CRM/Life_Stages/assets/css/erp-life-stages.css',
                array(
                    'erp-styles',
                    'erp-fontawesome',
                    'erp-sweetalert',
                    'erp-nprogress',
                ),
                ERP_PRO_PLUGIN_VERSION
            );

            wp_register_script(
                'erp-life-stages-settings',
                ERP_PRO_FEATURE_URL . '/CRM/Life_Stages/assets/js/erp-life-stages.js',
                'erp-settings',
                ERP_PRO_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script( 'erp-life-stages-settings' );

            $erp_life_stages_global['i18n']          = $this->i18n();
            $erp_life_stages_global['lsDescription'] = sprintf( __( 'Maximum %d life stages can be added', 'erp-pro' ), $erp_life_stages_global['lsLimit'] );

            wp_localize_script( 'erp-life-stages-settings', 'erpLifeStages', $erp_life_stages_global );
        }

    }

    /**
     * i18n strings for main admin pages
     *
     * @since 1.2.4
     *
     * @return array
     */
    private function i18n() {
        return array(
            'lifeStages'      => __( 'Life Stages', 'erp-pro' ),
            'addLifeStage'    => __( 'Add Life Stage', 'erp-pro' ),
            'addMore'         => __( 'Add more', 'erp-pro' ),
            'deleteLifeStage' => __( 'Delete Life Stage', 'erp-pro' ),
            'updateLifeStage' => __( 'Update Life Stage', 'erp-pro' ),
            'confirmDelete'   => __( 'Are you sure you want to delete this life stage?', 'erp-pro' ),
            'cancel'          => __( 'Cancel', 'erp-pro' ),
            'save'            => __( 'Save', 'erp-pro' ),
            'edit'            => __( 'Edit', 'erp-pro' ),
            'delete'          => __( 'Delete', 'erp-pro' ),
            'title'           => __( 'Title', 'erp-pro' ),
            'titlePlural'     => __( 'Title (Plural)', 'erp-pro' ),
            'slug'            => __( 'Slug', 'erp-pro' ),
            'order'           => __( 'Order', 'erp-pro' ),
            'noLifeStage'     => __( 'No Life Stage Found', 'erp-pro' ),
        );
    }

    /**
     * Settings option for life stages
     *
     * @param array $sections
     *
     * @return array
     */
    public function crm_life_stage_section( $sections ) {
        $sections['crm_life_stages'] = __( 'Life Stages', 'erp' );
        return $sections;
    }

    /**
     * Settings fields for life stages
     *
     * @since 1.2.4
     *
     * @param array  $fields
     * @param string $section
     *
     * @return array
     */
    public function crm_life_stage_section_fields( $fields, $section ) {
        $fields['crm_life_stages'][] = array(
            'type'  => 'life_stage_settings'
        );

        return $fields;
    }
}
