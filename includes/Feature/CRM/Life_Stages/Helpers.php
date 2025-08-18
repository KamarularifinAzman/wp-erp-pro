<?php

namespace WeDevs\ERP_PRO\Feature\CRM\Life_Stages;

/**
 * Helpers class
 *
 * Class contains miscellaneous helper methods
 *
 * @since 1.0.1
 */
class Helpers {

    /**
     * Inserts life stage
     *
     * @param array $args
     *
     * @return int|WP_Error
     */
    public static function insert_life_stage( $args ) {
        global $wpdb;

        $defaults = [
            'title'        => '',
            'title_plural' => '',
            'slug'         => '',
            'position'     => intval( self::get_max_life_stage_order() ) + 1,
        ];

        $args  = wp_parse_args( $args, $defaults );
        $table = $wpdb->prefix . 'erp_people_life_stages';

        $format = [
            '%s',
            '%s',
            '%s',
            '%d'
        ];

        $insert_id = $wpdb->insert( $table, $args, $format );

        return $insert_id;
    }

    /**
     * Updates life stage
     *
     * @param int $id
     * @param array $args
     *
     * @return int|WP_Error
     */
    public static function update_life_stage( $id, $args ) {
        global $wpdb;

        $table        = $wpdb->prefix . 'erp_people_life_stages';
        $where        = [ 'id'    => $id ];
        $data_format  = [ '%s', '%s' ];
        $where_format = [ '%d' ];

        $update_id    = $wpdb->update( $table, $args, $where, $data_format, $where_format );

        return $update_id;
    }

    /**
     * Deletes life stage
     *
     * @param int $id
     * @param array $slug
     *
     * @return int|WP_Error
     */
    public static function delete_life_stage( $id, $slug = '' ) {
        global $wpdb;

        $table     = $wpdb->prefix . 'erp_people_life_stages';
        $where     = [ 'id' => $id ];

        $delete_id = $wpdb->delete( $table, $where );

        $wpdb->update(
            $wpdb->prefix . 'erp_peoples',
            [ 'life_stage' => null ],
            [ 'life_stage' => $slug ],
            [ '%s' ],
            [ '%s' ]
        );

        return $delete_id;
    }

    /**
     * Checks if life stage slug exists
     *
     * @since 1.0.1
     *
     * @param string $slug
     * @param int $id
     *
     * @return bool
     */
    public static function exist_life_stage_slug( $slug, $id = '' ) {
        global $wpdb;

        $slug = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id
                        FROM {$wpdb->prefix}erp_people_life_stages
                        WHERE slug = %s
                        AND id != %d
                        LIMIT 0,1",
                        $slug,
                        $id
                    )
                );

        if ( $slug ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if life stage title exists
     *
     * @since 1.0.1
     *
     * @param string $title
     * @param int $id
     *
     * @return bool
     */
    public static function exist_life_stage_title( $title, $id = 0 ) {
        global $wpdb;

        $result = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id
                        FROM {$wpdb->prefix}erp_people_life_stages
                        WHERE title = %s
                        OR title_plural = %s
                        LIMIT 0,1",
                        $title,
                        $title
                    )
                );

        if ( $result ) {
            if ( $id && intval( $result ) === intval( $id ) ) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Retrieves life stages
     *
     * @since 1.0.1
     *
     * @return array $life_stages
     */
    public static function get_life_stages() {
        global $wpdb;

        $life_stages = $wpdb->get_results(
            "SELECT *
            FROM {$wpdb->prefix}erp_people_life_stages
            ORDER BY position",
            ARRAY_A
        );

        return $life_stages;
    }

    /**
     * Retrieves the number of total life stages
     *
     * @since 1.0.1
     *
     * @return int
     */
    public static function count_life_stages() {
        global $wpdb;

        $count = $wpdb->get_var(
            "SELECT COUNT(id)
            FROM {$wpdb->prefix}erp_people_life_stages"
        );

        return absint( $count );
    }

    /**
     * Retrieves max life stage order
     *
     * @since 1.0.1
     *
     * @return int
     */
    public static function get_max_life_stage_order() {
        global $wpdb;

        $order = $wpdb->get_var(
            "SELECT MAX(position)
            FROM {$wpdb->prefix}erp_people_life_stages"
        );

        return absint( $order );
    }
}
