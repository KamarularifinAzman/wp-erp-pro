<?php

/**
 * Get All Data from Assets table
 *
 * @since 1.0
 * @return mixed
 */
function erp_hr_assets_get_all( $args = [] ) {

    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $sql       = "SELECT *, id as item_id,
                  (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = item_id ) AS multiple_item_total,
                  (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = item_id AND status = 'stock' ) AS available_item_total
                  FROM {$wpdb->prefix}erp_hr_assets WHERE parent = 0";

    if ( isset( $args['category_id'] ) && !empty( $args['category_id'] ) ) {
        $sql .= ' AND category_id = ' . $args['category_id'];
    }

    $sql .= ' ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'];

    $last_changed = erp_cache_get_last_changed( 'hrm', 'assets', 'erp-asset' );
    $cache_key    = 'erp-get-assets-' . md5( serialize( $args ) ) . " : $last_changed";
    $assets       = wp_cache_get( $cache_key, 'erp-asset' );

    if ( false === $assets ) {
        $result = $wpdb->get_results( $sql );

        $employees  = erp_hr_get_employees( ['no_object' => true] );
        $emp_sorted = [];
        $cat_sorted = erp_hr_get_asset_categories();

        foreach ( $employees as $employee ) {
            $emp_sorted[$employee->user_id] = $employee->display_name;
        }

        foreach ( $result as $single ) {
            $assets[] = [
                'id'                   => $single->id,
                'type'                 => $single->asset_type,
                'category_id'          => $single->category_id,
                'item_code'            => $single->item_code,
                'item_group'           => $single->item_group,
                'manufacturer'         => $single->manufacturer,
                'model_no'             => $single->model_no,
                'item_serial'          => $single->item_serial,
                'item_desc'            => $single->item_desc,
                'date_reg'             => $single->date_reg ? erp_format_date( $single->date_reg ) : '&mdash;',
                'date_expiry'          => $single->date_expiry && '0000-00-00' != $single->date_expiry ? erp_format_date( $single->date_expiry ) : '&mdash;',
                'date_warranty'        => $single->date_warranty && '0000-00-00' != $single->date_warranty ? erp_format_date( $single->date_warranty ) : '&mdash;',
                'category'             => $single->category_id ? $cat_sorted[$single->category_id] : '',
                'multiple_item_total'  => $single->multiple_item_total ? intval( $single->multiple_item_total ) : 1,
                'available_item_total' => $single->available_item_total ? intval( $single->available_item_total ) : ( 'stock' == $single->status ? 1 : 0 )
            ];
        }

        $assets = erp_array_to_object( $assets );

        wp_cache_set( $cache_key, $assets, 'erp-asset' );
    }

    return $assets;
}

/**
 * Get Assets table Item Count
 *
 * @since 1.0
 *
 * @return int
 */
function erp_hr_assets_get_count( $args ) {

    global $wpdb;

    $sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = 0";

    if ( isset( $args['category_id'] ) && !empty( $args['category_id'] ) ) {
        $sql .= ' AND category_id = ' . $args['category_id'];
    }

    $count = $wpdb->get_var( $sql );

    return $count;
}

/**
 * Get the raw category dropdown
 *
 * @since 1.0
 * @return array
 */
function erp_hr_assets_get_categories_dropdown() {

    global $wpdb;

    $result           = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_hr_assets_category" );
    $categories['-1'] = __( '&mdash; Select Category &mdash;', 'erp-pro' );
    if ( $result ) {
        foreach ( $result as $category ) {
            $categories[$category->id] = stripslashes( $category->cat_name );
        }
    }

    return $categories;
}

/**
 * Get all statuses list for Request table
 *
 * @since 1.0
 * @return array
 */
function erp_hr_assets_get_request_statuses() {
    return [
        '-1'       => __( '&mdash; All Category &mdash;', 'erp-pro' ),
        'pending'  => __( 'Pending', 'erp-pro' ),
        'approved' => __( 'Approved', 'erp-pro' ),
        'rejected' => __( 'Rejected', 'erp-pro' ),
    ];
}

/**
 * Get Asset Categories
 *
 * @since 1.0
 *
 * @return array
 */
function erp_hr_get_asset_categories() {

    global $wpdb;

    $cat_sorted = [];
    $categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_hr_assets_category", ARRAY_A );

    foreach ( $categories as $category ) {
        $cat_sorted[$category['id']] = $category['cat_name'];
    }

    return $cat_sorted;
}

/**
 * Get an Asset Category
 *
 * @since 1.1.3
 *
 * @param int $id
 *
 * @return string
 */
function erp_hr_get_asset_category( $id ) {
    global $wpdb;

    $cat_name = $wpdb->get_var( $wpdb->prepare( "SELECT cat_name from {$wpdb->prefix}erp_hr_assets_category WHERE id = %d", $id ) );

    return $cat_name;
}

/**
 * Get Asset Type
 *
 * @return array
 */
function erp_hr_assets_get_asset_type() {

    return [
        'single'   => __( 'Single Item', 'erp-pro' ),
        'variable' => __( 'Multiple Items', 'erp-pro' ),
    ];
}

/**
 * Get All assets or by Employee
 *
 * @since 1.0
 *
 * @return object
 */
function erp_hr_get_assets( $employee = '', $args = '' ) {

    global $wpdb;

    $sql = "SELECT req.reply_msg, his.status AS status, his.return_note, his.id AS id, ass.item_group, ass.item_code AS item_id, his.date_given, his.date_return_proposed, ass.model_no AS item_name
            FROM {$wpdb->prefix}erp_hr_assets_history AS his
            LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass
            ON ass.id = his.item_id
            LEFT JOIN {$wpdb->prefix}erp_hr_assets_request AS req
            ON req.allott_id = his.id";

    if ( $employee ) {
        $sql .= " WHERE allotted_to = $employee";
    }

    if ( isset( $args['category'] ) && !empty( $args['category'] ) ) {
        $sql .= " WHERE category = " . $args['category'];
    }

    $assets = $wpdb->get_results( $sql );

    return $assets;
}

/**
 * Gets a single asset
 *
 * @since 1.1.3
 *
 * @param int $id
 *
 * @return array
 */
function erp_hr_get_asset( $id ) {
    global $wpdb;

    $cache_key = "erp-asset-by-$id";
    $item      = wp_cache_get( $cache_key, 'erp-asset' );
    return $item;

    if( false === $item ) {
        $item = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT *
                FROM {$wpdb->prefix}erp_hr_assets
                WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        wp_cache_set( $cache_key, $item, 'erp-asset' );
    }

    return $item;
}

/**
 * Empty Asset Field values
 *
 * @since 1.0
 *
 * @return array
 */
function erp_hr_get_empty_asset() {

    return [
        'item_name'   => '',
        'item_desc'   => '',
        'category'    => '',
        'is_alloted'  => '',
        'given_to'    => '',
        'date_given'  => '',
        'date_return' => '',
    ];
}

/**
 * Handles Asset Remove
 *
 * @since 1.0
 */
function erp_hr_asset_remove( $id ) {
    global $wpdb;

    if ( $id ) {

        $wpdb->delete( $wpdb->prefix . 'erp_hr_assets', [ 'ID' => $id ] );
        $wpdb->delete( $wpdb->prefix . 'erp_hr_assets', [ 'parent' => $id ] );
        $wpdb->delete( $wpdb->prefix . 'erp_hr_assets_history', [ 'item_group' => $id ] );
        $wpdb->delete( $wpdb->prefix . 'erp_hr_assets_request', [ 'item_group' => $id ] );

        erp_assets_purge_cache( [ 'list' => 'assets,requests,allottments', 'asset_id' => $id ] );

        return true;
    }

    return false;
}

/**
 * Insert Category
 *
 * @since 1.0
 *
 * @return array
 */
function wp_erp_hr_asset_category_insert( $cat_name ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'erp_hr_assets_category';
    $data       = [ 'cat_name' => $cat_name ];
    $result     = $wpdb->insert( $table_name, $data );

    if ( $result ) {
        return array(
            'value' => $wpdb->insert_id,
            'text'  => $cat_name
        );
    }
}

/**
 *  Edit Asset Category
 *
 * @since 1.0
 *
 * @return bool
 */
function wp_erp_hr_asset_category_edit( $row_id, $cat_name ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'erp_hr_assets_category';
    $data       = [ 'cat_name' => $cat_name ];
    $result     = $wpdb->update( $table_name, $data, [ 'ID' => $row_id ] );

    if ( $result ) {
        return [
            'value' => $row_id,
            'text'  => $cat_name
        ];
    }
}

/**
 * Get allottment table data
 *
 * @return array
 */
function erp_hr_allottment_get_all( $args ) {

    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $sql       = "SELECT  his.id,
                          his.is_returnable,
                          his.allotted_to,
                          his.return_note,
                          ass.id AS item_id,
                          ass.item_code AS item_code,
                          ass.item_group AS item_group,
                          his.item_group AS item_group_id,
                          ass.model_no, his.date_given,
                          his.date_return_proposed,
                          his.date_return_real,
                          his.date_request_return,
                          his.status
                  FROM {$wpdb->prefix}erp_hr_assets_history AS his
                  LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass ON ass.id = his.item_id";

    if ( isset( $args['status'] ) && !empty( $args['status'] ) && 'all' != $args['status'] ) {
        $sql .= " WHERE his.status = '" . $args['status'] . "'";
    }

    $sql        .= ' ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'];

    $last_changed = erp_cache_get_last_changed( 'hrm', 'allottments', 'erp-asset' );
    $cache_key    = 'erp-get-allottments-' . md5( serialize( $args ) ) . " : $last_changed";
    $list         = wp_cache_get( $cache_key, 'erp-asset' );

    if ( false == $list ) {
        $result     = $wpdb->get_results( $sql );

        $employees  = erp_hr_get_employees( [ 'no_object' => true ] );
        $emp_sorted = [];
        $list       = [];

        foreach ( $employees as $employee ) {
            $emp_sorted[$employee->user_id] = $employee->display_name;
        }

        foreach ( $result as $single ) {

            $list[] = [
                'id'                   => $single->id,
                'item_id'              => $single->item_id,
                'date_given'           => erp_format_date( $single->date_given ),
                'employee_id'          => $single->allotted_to,
                'employee_name'        => $emp_sorted[$single->allotted_to],
                'item_group_id'        => $single->item_group_id,
                'item_group'           => $single->item_group,
                'item_code'            => $single->item_code,
                'model_no'             => $single->model_no ? $single->model_no : '&mdash;',
                'date_return_proposed' => $single->date_return_proposed && '0000-00-00' != $single->date_return_proposed ? $single->date_return_proposed : '&mdash;',
                'date_return_real'     => $single->date_return_real && '0000-00-00' != $single->date_return_real ? $single->date_return_real : '&mdash;',
                'date_request_return'  => $single->date_request_return && '0000-00-00' != $single->date_request_return ? $single->date_request_return : '&mdash;',
                'is_returnable'        => $single->is_returnable,
                'return_note'          => $single->return_note,
                'status'               => $single->status
            ];
        }

        $list = erp_array_to_object( $list );

        wp_cache_set( $cache_key, $list, 'erp-asset' );
    }

    return $list;
}

/**
 * Get allottment count
 *
 * @return int
 */
function erp_hr_allottment_get_count( $args ) {
    global $wpdb;

    $sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets_history";

    if ( isset( $args['status'] ) && !empty( $args['status'] ) ) {
        $sql .= " WHERE status = '" . $args['status'] . "'";
    }

    $count = $wpdb->get_var( $sql );

    return $count;
}

/**
 * Get All Asset Request Data
 *
 * @reuturn object
 */
function erp_hr_asset_request_all( $args = [] ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'user_id'    => null,
    );

    $args      = wp_parse_args( $args, $defaults );
    $sql       = "SELECT req.*, cat.cat_name, ass.category_id AS category, ass.item_code, ass.model_no, ass.status AS item_status, ass.item_group AS item,
                  ( SELECT CONCAT( item_code, '&mdash;', model_no) FROM {$wpdb->prefix}erp_hr_assets WHERE id = req.given_item_id) AS item_given
                  FROM {$wpdb->prefix}erp_hr_assets_request AS req
                  LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass ON ass.id = req.item_group
                  LEFT JOIN {$wpdb->prefix}erp_hr_assets_category AS cat ON ass.category_id = cat.id";

    if ( ! empty( $args['user_id'] ) ) {
        $sql .= " WHERE req.user_id = '" . $args['user_id'] . "'";
    }

    if ( isset( $args['status'] ) && ! empty( $args['status'] ) ) {
        $sql .= empty( $args['user_id'] ) ? ' WHERE ' : ' AND ';

        $sql .= "req.status = '" . $args['status'] . "'";
    }

    if ( ! empty( $args['date'] ) && ! empty( $args['date']['start'] ) ) {
        $start_date     = erp_current_datetime()->modify( $args['date']['start'] )->format( 'Y-m-d' );

        $end_date       = ! empty( $args['date']['end'] )
                          ? erp_current_datetime()->modify( $args['date']['end'] )->format( 'Y-m-d' )
                          : erp_current_datetime()->format( 'Y-m-d' );

        $sql .= ( empty( $args['user_id'] ) && empty( $args['status'] ) ) ? ' WHERE ' : ' AND ';
        $sql .= "(req.date_requested BETWEEN '{$start_date}' AND '{$end_date}')";
    }

    $sql .= ' ORDER BY ' . $args['orderby'] .' ' . $args['order'];

    if ( ! empty( $args['number'] ) && '-1' != $args['number'] ) {
        $sql .= ' LIMIT ' . $args['offset'] . ', ' . $args['number'];
    }

    $last_changed = erp_cache_get_last_changed( 'hrm', 'requests', 'erp-asset' );
    $cache_key    = 'erp-get-requests-' . md5( serialize( $args ) ) . " : $last_changed";
    $list         = wp_cache_get( $cache_key, 'erp-asset' );

    if ( false == $list ) {
        $result     = $wpdb->get_results( $sql );

        $employees  = erp_hr_get_employees( [ 'no_object' => true, 'number' => -1 ] );
        $emp_sorted = [];
        $list       = [];

        foreach ( $employees as $employee ) {
            $emp_sorted[$employee->user_id] = $employee->display_name;
        }

        foreach ( $result as $single ) {
            $employee = get_userdata( $single->user_id );

            $list[] = [
                'id'               => $single->id,
                'category'         => $single->category,
                'cat_name'         => $single->cat_name,
                'item_name'        => $single->item_group,
                'item'             => $single->item ? $single->item : $single->request_desc,
                'req_item_id'      => $single->item_id,
                'item_given'       => $single->item_code,
                'user_id'          => $single->user_id,
                'employee_name'    => $employee->display_name,
                'item_description' => 'on' == $single->not_in_list ? $single->request_desc : $single->cat_name . '&mdash;' . $single->item_group,
                'available'        => 'stock' == $single->item_status ? 'yes' : 'no',
                'date_requested'   => erp_format_date( $single->date_requested ),
                'date_replied'     => $single->date_replied && '0000-00-00' != $single->date_replied ? erp_format_date( $single->date_replied ) : '&mdash;',
                'status'           => $single->status
            ];
        }

        wp_cache_set( $cache_key, $result, 'erp-asset-request' );

        $list = erp_array_to_object( $list );

        wp_cache_set( $cache_key, $list, 'erp-asset' );
    }

    return $list;
}


/**
 * Get Asset Request Count
 *
 * @return int
 */
function erp_hr_asset_request_count( $args ) {
    global $wpdb;

    $sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets_request";

    if ( isset( $args['status'] ) && !empty( $args['status'] ) ) {
        $sql .= " WHERE status = '" . $args['status'] . "'";
    }

    $count = $wpdb->get_var( $sql );

    return $count;
}

/**
 * Get allottment empty data
 *
 * @return array
 */
function erp_asset_get_allottment_empty_data() {
    return [

    ];
}

/**
 * Get asset requests
 *
 * @return array
 */
function erp_hr_get_asset_requests( $employee_id = 0 ) {

    global $wpdb;

    $sql = "SELECT req.*, ass.item_group AS item_name, cat.cat_name as category_name
            FROM {$wpdb->prefix}erp_hr_assets_request AS req
            LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass
            ON req.item_group = ass.id
            LEFT JOIN {$wpdb->prefix}erp_hr_assets_category AS cat
            ON ass.category_id = cat.id
            WHERE req.status != 'approved'";

    if ( $employee_id ) {
        $sql .= " AND user_id = $employee_id";
    }

    $result = $wpdb->get_results( $sql );

    return $result;
}

/**
 * Gets a single asset request
 *
 * @since 1.1.3
 *
 * @param int $req_id
 *
 * @return array
 */
function erp_get_asset_req_by_id( $req_id ) {
    global $wpdb;

    $request = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
            FROM {$wpdb->prefix}erp_hr_assets_request
            WHERE id = %d",
            $req_id
        ),
        ARRAY_A
    );

    return $request;
}

/**
 * Gets a single asset allotment
 *
 * @since 1.1.3
 *
 * @param int $allot_id
 *
 * @return array
 */
function erp_get_asset_allot_by_id( $allot_id ) {
    global $wpdb;

    $request = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT *
            FROM {$wpdb->prefix}erp_hr_assets_history
            WHERE `status` = %s
            AND id = %d",
            'allotted',
            $allot_id
        ),
        ARRAY_A
    );

    return $request;
}

/**
 * Allottment remove
 *
 * @return bool
 */
function erp_hr_asset_allottment_remove( $row_id = 0 ) {

    global $wpdb;

    if ( $row_id ) {
        $item_id = $wpdb->get_var( $wpdb->prepare( "SELECT item_id FROM {$wpdb->prefix}erp_hr_assets_history WHERE id = %d", $row_id ) );

        if ( $item_id ) {
            $wpdb->update( $wpdb->prefix . 'erp_hr_assets', [ 'status' => 'stock' ], [ 'ID' => $item_id, 'status' => 'allotted' ] );
        }

        $deleted = $wpdb->delete( $wpdb->prefix . 'erp_hr_assets_history', [ 'ID' => $row_id ] );

        erp_assets_purge_cache( [ 'list' => 'allottments', 'allott_id' => $row_id ] );

        if ( $deleted ) {
            return true;
        }
    }

}

/**
 * Get query times
 *
 * @since 1.0
 *
 * @return array
 */
function erp_asset_get_query_times() {
    return [
        '-1'           => __( '&mdash; All Time &mdash;', 'erp-pro' ),
        'this_month'   => __( 'This Month', 'erp-pro' ),
        'last_month'   => __( 'Last Month', 'erp-pro' ),
        'this_quarter' => __( 'This Quarter', 'erp-pro' ),
        'last_quarter' => __( 'Last Quarter', 'erp-pro' ),
        'this_year'    => __( 'This Year', 'erp-pro' ),
        'last_year'    => __( 'last Year', 'erp-pro' )
    ];
}

/**
 * Get start and end date from a specific time
 *
 * @return  array
 */
function erp_asset_get_start_end_date( $time = '' ) {

    $duration = [];

    if ( $time ) {

        switch ( $time ) {

            case 'today':

                $start_date = current_time( "Y-m-d" );
                $end_date   = $start_date;
                break;

            case 'yesterday':

                $today      = strtotime( current_time( "Y-m-d" ) );
                $start_date = date( "Y-m-d", strtotime( "-1 days", $today ) );
                $end_date   = $start_date;
                break;

            case 'last_7_days':

                $end_date   = current_time( "Y-m-d" );
                $start_date = date( "Y-m-d", strtotime( "-6 days", strtotime( $end_date ) ) );
                break;

            case 'this_month':

                $start_date = date( "Y-m-d", strtotime( "first day of this month" ) );
                $end_date   = date( "Y-m-d", strtotime( "last day of this month" ) );
                break;

            case 'last_month':

                $start_date = date( "Y-m-d", strtotime( "first day of previous month" ) );
                $end_date   = date( "Y-m-d", strtotime( "last day of previous month" ) );
                break;

            case 'this_quarter':

                $current_month = date( 'm' );
                $current_year  = date( 'Y' );

                if ( $current_month >= 1 && $current_month <= 3 ){

                    $start_date = date( 'Y-m-d', strtotime( '1-January-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '31-March-'.$current_year ) );

                } else  if ( $current_month >= 4 && $current_month <= 6 ){

                    $start_date = date( 'Y-m-d', strtotime( '1-April-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '30-June-'.$current_year ) );

                } else  if ( $current_month >= 7 && $current_month <= 9){

                    $start_date = date( 'Y-m-d', strtotime( '1-July-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '30-September-'.$current_year ) );

                } else  if ( $current_month >= 10 && $current_month <= 12 ){

                    $start_date = date( 'Y-m-d', strtotime( '1-October-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '31-December-'.$current_year ) );
                }
                break;

            case 'last_quarter':

                $current_month = date( 'm' );
                $current_year  = date( 'Y' );

                if ( $current_month >= 1 && $current_month <= 3 ) {

                    $start_date = date( 'Y-m-d', strtotime( '1-October-'.( $current_year-1 ) ) );
                    $end_date   = date( 'Y-m-d', strtotime( '31-December-'.( $current_year-1 ) ) );

                } else if( $current_month >=4 && $current_month <= 6){

                    $start_date = date( 'Y-m-d', strtotime( '1-January-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '31-March-'.$current_year ) );

                } else if( $current_month >= 7 && $current_month <= 9){

                    $start_date = date( 'Y-m-d', strtotime( '1-April-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '30-June-'.$current_year ) );

                } else if( $current_month >= 10 && $current_month <= 12 ){

                    $start_date = date( 'Y-m-d', strtotime( '1-July-'.$current_year ) );
                    $end_date   = date( 'Y-m-d', strtotime( '30-September-'.$current_year ) );
                }
                break;

            case 'last_year':

                $start_date = date( "Y-01-01", strtotime( "-1 year" ) );
                $end_date   = date( "Y-12-31", strtotime( "-1 year" ) );
                break;

            case 'this_year':

                $start_date = date( "Y-01-01" );
                $end_date   = date( "Y-12-31" );
                break;

            default:
                break;
        }
    }

    $duration   = [
        'start' => $start_date,
        'end'   => $end_date
    ];

    return $duration;
}

/**
 * Allotment get status count
 *
 * @return array
 */
function erp_asset_get_allotment_status_count() {

    global $wpdb;

    $counts = [];
    $statuses = [
        'all'              => __( 'All', 'erp-pro' ),
        'allotted'         => __( 'Allotted', 'erp-pro' ),
        'returned'         => __( 'Returned', 'erp-pro' ),
        'dissmissed'       => __( 'Dismissed', 'erp-pro' ),
        'requested_return' => __( 'Requested Return', 'erp-pro' )
    ];

    foreach ($statuses as $status => $label ) {

        $counts[ $status ] = [ 'count' => 0, 'label' => $label ];
    }

    $cache_key = 'erp-asset-allotment-status-counts';
    $results = wp_cache_get( $cache_key, 'erp-asset' );

    if ( false === $results ) {

        $results = $wpdb->get_results( 'SELECT status, COUNT(ID) AS num FROM ' . $wpdb->prefix . 'erp_hr_assets_history GROUP BY status', ARRAY_A );

        wp_cache_set( $cache_key, $results, 'erp-asset' );
    }

    foreach ( $results as $row ) {

        if ( array_key_exists( $row['status'], $counts ) ) {

            $counts[ $row['status'] ]['count'] = (int) $row['num'];
        }
    }

    $count_all = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets_history" );

    if ( $count_all ) {
        $counts['all']['count'] = (int) $count_all;
    }

    return $counts;
}

/**
 * Request get status count
 *
 * @return array
 */
function erp_asset_get_request_status_count() {

    global $wpdb;

    $counts = [];
    $statuses = [
        'all'      => __( 'All', 'erp-pro' ),
        'pending'  => __( 'Pending', 'erp-pro' ),
        'approved' => __( 'Approved', 'erp-pro' ),
        'rejected' => __( 'Rejected', 'erp-pro' )
    ];

    foreach ($statuses as $status => $label ) {

        $counts[ $status ] = [ 'count' => 0, 'label' => $label ];
    }

    $cache_key = 'erp-asset-allotment-status-counts';
    $results = wp_cache_get( $cache_key, 'erp-asset-allotment' );

    if ( false === $results ) {

        $results = $wpdb->get_results( 'SELECT status, COUNT(ID) AS num FROM ' . $wpdb->prefix . 'erp_hr_assets_request GROUP BY status', ARRAY_A );

        wp_cache_set( $cache_key, $results, 'erp-asset-allotment' );
    }

    foreach ( $results as $row ) {

        if ( array_key_exists( $row['status'], $counts ) ) {

            $counts[ $row['status'] ]['count'] = (int) $row['num'];
        }
    }

    $count_all = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets_request" );

    if ( $count_all ) {
        $counts['all']['count'] = (int) $count_all;
    }

    return $counts;
}


function erp_asset_request_reject( $row_id, $reject_reason = '' ) {

    global $wpdb;

    $wpdb->update( $wpdb->prefix . 'erp_hr_assets_request', [
        'date_replied' => current_time( 'Y-m-d' ),
        'reply_msg'    => $reject_reason,
        'status'       => 'rejected'
    ], [ 'ID' => $row_id, 'status' => 'pending' ]);

    $sql = "SELECT req.request_desc, req.date_requested, req.date_replied, u.user_email, u.display_name, ass.item_group, ass.item_code, ass.model_no
                    FROM {$wpdb->prefix}erp_hr_assets_request AS req
                    LEFT JOIN $wpdb->users AS u
                    ON req.user_id = u.id
                    LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass
                    ON req.item_id = ass.id
                    WHERE req.id = %d";

    $data = $wpdb->get_row( $wpdb->prepare( $sql, $row_id ) );
    $data->reject_reason = $reject_reason;

    $emailer = wperp()->emailer->get_email( 'Asset_Request_Reject' );

    if ( is_a( $emailer, '\WeDevs\ERP\Email') ) {
        $emailer->trigger( $data );
    }
}

function erp_asset_url( $slug = '' ) {
    if ( version_compare( WPERP_VERSION , '1.4.0', '<' ) ) {
        return admin_url( 'admin.php?page=' . $slug );
    }

    return admin_url( 'admin.php?page=erp-hr&section=asset&sub-section=' . $slug );
}

/**
 * Purge cache data for Assets addons
 *
 * Remove all cache for Assets addons
 *
 * @since 1.1.1
 *
 * @param array $args
 *
 * @return void
 */
function erp_assets_purge_cache( $args = [] ) {

    $group = 'erp-asset';

    if ( isset( $args['asset_id'] ) ) {
        wp_cache_delete( "erp-asset-by-" . $args['asset_id'], $group );
    }

    if ( isset( $args['asset_reqquest_id'] ) ) {
        wp_cache_delete( "erp-asset-reqquest-by-" . $args['asset_reqquest_id'], $group );
    }

    if ( isset( $args['list'] ) ) {

        if( $args['list'] === 'allottments' ) {
            wp_cache_delete('erp-asset-allotment-status-counts', $group);
        }

        $args['list'] = 'assets,requests,allottments';

        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => $args['list'] ] );
    }
}
