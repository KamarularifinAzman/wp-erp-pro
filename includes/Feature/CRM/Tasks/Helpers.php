<?php
namespace WeDevs\ERP_PRO\Feature\CRM\Tasks;

/**
 * Helpers class
 *
 * Class contains miscellaneous helper methods
 *
 * @since 1.0.1
 */
class Helpers {

    /**
     * Retrieves crm tasks
     *
     * @since 1.0.1
     *
     * @param array $args
     *
     * @return array
     */
    public static function get_crm_tasks( $args = [] ) {
        global $wpdb;

        $defaults = [
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'status'  => 'all',
            'start'   => '',
            'end'     => '',
            'user_id' => 0,
            'q'       => '',
        ];

        $args = wp_parse_args( $args, $defaults );

        $sql = "SELECT SQL_CALC_FOUND_ROWS c.*,
                       c.id as id,
                       c.user_id as contact_id,
                       c.message as task,
                       c.start_date as due_date,
                       JSON_UNQUOTE( JSON_EXTRACT( CONVERT( FROM_BASE64( c.extra ) using utf8mb4 ), '$.task_title' ) ) as title,
                       c.done_at as done_at,
                       c.created_by as assigned_by,
                       c.created_at as created_at,
                       group_concat( t.user_id SEPARATOR ', ' ) as assigned_to
                FROM {$wpdb->prefix}erp_crm_customer_activities as c
                JOIN {$wpdb->prefix}erp_crm_activities_task as t
                ON c.id = t.activity_id
                WHERE c.type = %s";

        $values[] = 'tasks';

        if ( $args['user_id'] ) {
            $sql .= " AND t.user_id = %d";
            $values[] = $args['user_id'];
        }

        if ( $args['contact_id'] ) {
            $sql .= " AND c.user_id = %d";
            $values[] = $args['contact_id'];
        }

        if ( $args['status'] ) {
            $curr_date = gmdate( 'Y-m-d h:i:s', strtotime( current_time( 'mysql' ) ) );

            if ( 'done' === $args['status'] ) {
                $sql .= " AND c.done_at IS NOT NULL";

            } else if ( 'pending' === $args['status'] ) {
                $sql .= " AND c.done_at IS NULL AND c.start_date > %s";
                $values[] = $curr_date;

            } else if ( 'due' === $args['status'] ) {
                $sql .= " AND c.done_at IS NULL AND c.start_date <= %s";
                $values[] = $curr_date;
            }
        }

        if ( ! empty( $args['date'] ) && ! empty( $args['date']['start'] ) ) {
            $start_date = gmdate( 'Y-m-d 00:00:00', strtotime( $args['date']['start'] ) );

            if ( $args['date']['end'] ) {
                $end_date = gmdate( 'Y-m-d 23:59:59', strtotime( $args['date']['end'] ) );
            } else {
                $end_date = gmdate( 'Y-m-d 23:59:59', strtotime( $args['date']['start'] ) );
            }

            $sql .= " AND c.start_date BETWEEN %s AND %s";

            $values[] = $start_date;
            $values[] = $end_date;
        }

        if ( $args['q'] ) {
            $sql .= " AND JSON_UNQUOTE( JSON_EXTRACT( CONVERT( FROM_BASE64( c.extra ) using utf8mb4 ), '$.task_title' ) ) LIKE %s";
            $values[] = '%' . $args['q'] . '%';
        }

        $sql .= " GROUP BY c.id ORDER BY {$args['orderby']} {$args['order']}";

        if ( $args['number'] ) {
            $sql .= " LIMIT %d, %d";

            if ( $args['offset'] ) {
                $values[] = $args['offset'];
            } else {
                $values[] = 0;
            }

            $values[] = $args['number'];
        }

        $tasks      = $wpdb->get_results( $wpdb->prepare( $sql, $values ) );
        $total_rows = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) );

        return [ 'data' => $tasks, 'total' => $total_rows ];
    }
}
