<?php
namespace WeDevs\AwesomeSupport;

use WeDevs\ERP\Integration;

/**
 * Settings class
 *
 * @since 1.0.0
 *
 * @package WPERP|Awesome Support
 */
class Settings extends Integration {

    /**
     * Constructor function
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->id          = 'awesome_support';
        $this->title       = __( 'Awesome Support', 'erp-pro' );
        $this->description = __( 'Awesome Support Add-on for WP-ERP.', 'erp-pro' );

        $this->init_settings();
        parent::__construct();
    }

    /**
     * Get the title of this setting.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Get the description of this setting.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Initializes settings page for Awesome Support
     *
     * @return array|array[]
     */
    public function init_settings() {
        $life_stages = erp_crm_get_life_stages_dropdown_raw();
        $roles       = [ 'erp_crm_manager', 'erp_crm_agent' ];
        // Previously getting users we used erp_crm_get_crm_user(). But from WP 6.1.1 before plugins loaded User query gets warning.
        $crm_users = $this->get_users_by_role( $roles );
        $users     = [ '' => __( '&mdash; Select Owner &mdash;', 'erp-pro' ) ];

        foreach ( $crm_users as $user ) {
            $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
        }

        $this->form_fields = [
            [
                'title'   => __( 'Customer life stage', 'erp-pro' ),
                'type'    => 'select',
                'options' => $life_stages,
                'id'      => 'erp_awesome_support_ls',
                'desc'    => __( 'When user open a ticket, then which life stage you want to choose for that contact( default : Opportunity )', 'erp-pro' ),
                'class'   => 'erp-select2',
                'tooltip' => true,
                'default' => 'customer',
            ],
            [
                'title'   => __( 'Default Contact Owner', 'erp-pro' ),
                'type'    => 'select',
                'options' => $users,
                'id'      => 'erp_awesome_support_owner',
                'desc'    => __( 'Default contact owner for contact.', 'erp-pro' ),
                'class'   => 'erp-select2',
                'tooltip' => true,
                'default' => 'customer',
            ],
        ];

        return $this->form_fields;
    }


    /**
     * Get users by roles and not in roles. And get in return by fields.
     *
     * @since 1.3.0
     *
     * @param        $roles
     * @param array  $role_not_in
     * @param string $fields
     * @param string $format
     *
     * @return array|null
     */
    public function get_users_by_role( $roles, $role_not_in = [], $fields = '*' ) {
        global $wpdb;

        $fields = is_array( $fields ) ? implode( ',', $fields ) : $fields;
        $role__in_clauses = [ 'relation' => 'OR' ];
        $role_queries = [];
        $blog_id = get_current_blog_id();
        if ( ! empty( $roles ) ) {
            foreach ( $roles as $role ) {
                $role__in_clauses[] = [
                    'key'     => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                    'value'   => '"' . $role . '"',
                    'compare' => 'LIKE',
                ];
            }

            $role_queries['meta_query'][] = $role__in_clauses;
        }

        $role__not_in_clauses = [ 'relation' => 'AND' ];
        if ( ! empty( $role_not_in ) ) {
            foreach ( $role_not_in as $role ) {
                $role__not_in_clauses[] = [
                    'key'     => $wpdb->get_blog_prefix( $blog_id ) . 'capabilities',
                    'value'   => '"' . $role . '"',
                    'compare' => 'NOT LIKE',
                ];
            }

            $role_queries['meta_query'][] = $role__not_in_clauses;
        }

        $meta_query = new \WP_Meta_Query();
        $meta_query->parse_query_vars( $role_queries );

        $mq_sql = $meta_query->get_sql(
            'user',
            $wpdb->users,
            'ID'
        );

        $user_query   = "SELECT {$fields} FROM {$wpdb->users} {$mq_sql['join']} WHERE 1=1 {$mq_sql['where']}";
        $users = $wpdb->get_results( $user_query );

        return ! empty( $users ) ? $users : [];
    }
}
