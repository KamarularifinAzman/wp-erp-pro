<?php

use WeDevs\ERP\HRM\Models\LeavesUnpaid;

/**
 * Get unpaid leaves
 *
 * @param $args array
 *
 * @since 1.0.0
 *
 * @return array
 */
function erp_pro_hr_leave_get_unpaid_leaves( $args = array() ) {

    global $wpdb;

    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $defaults = array(
        'number'  => 99,
        'offset'  => 0,
        'f_year'  => 0,
        'orderby' => 'id',
        'order'   => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );

    $unpaids = LeavesUnpaid::select( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'SQL_CALC_FOUND_ROWS *' ))
            ->skip( $args['offset'] )
            ->take( $args['number'] )
            ->orderBy( $args['orderby'], $args['order'] );

    if ( ! empty ( $args['f_year'] ) ) {
        $unpaids->where( 'f_year', $args['f_year'] );
    }

    $unpaids = $unpaids->get();

    $total_row_found = absint( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );

    $formatted_data = array();

    foreach( $unpaids as $key => $unpaid ) {
        $formatted_data[$key]['id']            = $unpaid->id;
        $formatted_data[$key]['policy_name']   = $unpaid->leave->name;
        $formatted_data[$key]['user_id']       = $unpaid->user_id;
        $formatted_data[$key]['employee_name'] = $unpaid->employee->user->display_name;
        $formatted_data[$key]['days']          = $unpaid->days;
        $formatted_data[$key]['f_year']        = $unpaid->financial_year->fy_name;
        $formatted_data[$key]['start_date']    = erp_format_date( $unpaid->leave_request->start_date );
        $formatted_data[$key]['end_date']      = erp_format_date( $unpaid->leave_request->end_date );
        $formatted_data[$key]['amount']        = $unpaid->amount;
        $formatted_data[$key]['total']         = $unpaid->total;
    }

    return array( 'data' => erp_array_to_object( $formatted_data ), 'total' => $total_row_found );
}

/**
 * Get unpaid leaves row count
 *
 * @since 1.0.0
 *
 * @return int
 */
function erp_pro_hr_leave_count_unpaid_leaves() {
    return LeavesUnpaid::count();
}

/**
 * Update unpaid leave amount
 *
 * @return void
 */
function erp_pro_hr_leave_unpaid_update_amount( $f_year, $salary_type ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $pay_amount = 0;
    $unpaids    = LeavesUnpaid::where('f_year', $f_year)->get();

    foreach ( $unpaids as $unpaid ) {
        if ( $salary_type === 'pay_rate' ) {
            $pay_rate = isset( $unpaid->employee->pay_rate ) ? $unpaid->employee->pay_rate : 0;
            $pay_amount = $pay_rate / 30; // pay per day
        }

        $unpaid->update([
            'amount' => $pay_amount,
            'total' => $unpaid->days * $pay_amount,
        ]);
    }
}

/**
 * Update single unpaid leave amount
 *
 * @param $id int
 * @param $amount float
 *
 * @since 1.0.0
 *
 * @return void
 */
function erp_pro_hr_leave_unpaid_update_single_amount( $id, $amount ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $unpaid = LeavesUnpaid::find( $id );

    $unpaid->update([
        'amount' => $amount,
        'total' => $amount * (int) $unpaid->days
    ]);

    return $unpaid->total;
}

/**
 * Unpaid leave export
 *
 * @param $f_year int
 *
 * @since 1.0.0
 *
 * @return string
 */
function erp_pro_hr_leave_unpaid_leaves_export( $f_year ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    header('Content-Type: application/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="unpaid-leaves.csv"');

    $unpaids = LeavesUnpaid::where('f_year', $f_year)->get();

    $fp = fopen( 'php://output', 'w' );
    fputcsv( $fp, array(
        //esc_html__('ID', 'erp-pro'),
        esc_html__('Policy Name', 'erp-pro'),
        //esc_html__('User ID', 'erp-pro'),
        esc_html__('Employee Name', 'erp-pro'),
        esc_html__('Days', 'erp-pro'),
        esc_html__('Year', 'erp-pro'),
        esc_html__('Start Date', 'erp-pro'),
        esc_html__('End Date', 'erp-pro'),
        esc_html__('Amount', 'erp-pro'),
        esc_html__('Total', 'erp-pro')
    ) );

    foreach ( $unpaids as $unpaid ) {
        $formatted_data = array();

        $formatted_data['id']            = $unpaid->id;
        $formatted_data['policy_name']   = $unpaid->leave->name;
        $formatted_data['user_id']       = $unpaid->user_id;
        $formatted_data['employee_name'] = $unpaid->employee->user->display_name;
        $formatted_data['days']          = $unpaid->days;
        $formatted_data['f_year']        = $unpaid->financial_year->fy_name;
        $formatted_data['start_date']    = erp_format_date( $unpaid->leave_request->start_date );
        $formatted_data['end_date']      = erp_format_date( $unpaid->leave_request->end_date );
        $formatted_data['amount']        = $unpaid->amount;
        $formatted_data['total']         = $unpaid->total;

        fputcsv( $fp, array_values($formatted_data) );
    }

    fclose( $fp );

    exit();
}

