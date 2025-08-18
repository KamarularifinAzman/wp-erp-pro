<?php

use \WeDevs\ORM\Eloquent\Facades\DB;
use \WeDevs\ERP\HRM\Models\FinancialYear;
use \WeDevs\ERP\HRM\Models\LeaveEntitlement;
use WeDevs\ERP\HRM\Models\LeaveEncashmentRequest;

/**
 * Get forward leaves row count
 *
 * @since 1.0.0
 *
 * @return int
 */
// function erp_pro_hr_leave_count_forward_leaves() {
//     return LeaveEncashmentRequest::count();
// }

function erp_pro_hr_leave_get_encash_requests( $args ) {

    global $wpdb;

    $prev_f_year = erp_pro_hr_leave_get_prev_financial_year();
    $defaults = array(
        'number'    => 0,
        'offset'    => 0,
        'orderby'   => 'id',
        'order'     => 'ASC',
        'f_year_id' => isset( $prev_f_year ) ? $prev_f_year->id : 0
    );

    $args = wp_parse_args( $args, $defaults );

    $exist = LeaveEncashmentRequest::where('f_year', $args['f_year_id'] )->first();

    if ( ! $exist ) {
        return array();
    }

    $query = "SELECT SQL_CALC_FOUND_ROWS rq.*, policy.name as policy_name, u.display_name as employee_name  FROM {$wpdb->prefix}erp_hr_leave_encashment_requests as rq";
    $query .= " LEFT JOIN {$wpdb->prefix}erp_hr_leaves as policy on policy.id = rq.leave_id";
    $query .= " LEFT JOIN {$wpdb->users} AS u ON u.ID = rq.user_id";
    $query .= " WHERE f_year = " . $args['f_year_id'];
    $query .= " ORDER BY rq.id asc";

    $offset = absint( $args['offset'] );
    $number = absint( $args['number'] );
    $limit = $args['number'] == '-1' ? '' : " LIMIT {$offset}, {$number}";

    $query .= $limit;

    $return['data'] = $wpdb->get_results( $query );
    $return['total'] = $wpdb->get_var( "SELECT FOUND_ROWS()" );

    return $return;
}


/**
 * Get previous financial year
 *
 * @since 1.0.0
 *
 * @return object|null
 */
function erp_pro_hr_leave_get_prev_financial_year() {
    // get current financial year
    $current_f_year = erp_hr_get_financial_year_from_date();

    if ( $current_f_year ) {
        $prev_date = erp_current_datetime()->setTimestamp( $current_f_year->start_date )->modify( '-1 days' );

        // get previous financial year
        return erp_hr_get_financial_year_from_date( $prev_date->getTimestamp() );
    }

    return null;
}

/**
 * Get users available leaves
 */
function erp_pro_hr_leave_get_users_available_leaves( $args = array() ) {
    global $wpdb;

    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $prev_f_year = erp_pro_hr_leave_get_prev_financial_year();

    $defaults = array(
        'number'    => 0,
        'offset'    => 0,
        'orderby'   => 'id',
        'order'     => 'ASC',
        'f_year_id' => isset( $prev_f_year ) ? $prev_f_year->id : 0
    );

    $args = wp_parse_args( $args, $defaults );

    $entitlements = LeaveEntitlement::select( array(
        'f_year',
        'user_id',
        'leave_id',
        DB::raw('sum(day_in) - sum(day_out) as available')
    ) )
        ->where('f_year', $args['f_year_id'])
        ->groupBy('user_id', 'leave_id')
        ->having('available', '>', 0)
        ->get();

    $formatted_data = array();

    foreach( $entitlements as $key => $entitlement ) {
        $policy = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    en.trn_id,
                    p.carryover_uses_limit,
                    p.forward_default,
                    p.carryover_days,
                    p.encashment_days
                FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en
                LEFT JOIN  {$wpdb->prefix}erp_hr_leave_policies AS p ON p.id = en.trn_id
                WHERE en.trn_type = 'leave_policies' AND en.user_id = %d
                AND en.leave_id = %d AND p.leave_id = %d
                AND p.f_year = %d",
                $entitlement->user_id,
                $entitlement->leave_id,
                $entitlement->leave_id,
                $entitlement->f_year
            )
        );

        if ( null === $policy || ! is_object( $policy ) ) {
            continue;
        }

        $carry_days  = (float) $policy->carryover_days;
        $encash_days = (float) $policy->encashment_days;

        if ( ! $carry_days && ! $encash_days ) {
            continue;
        }

        $encashment = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT encash.* FROM {$wpdb->prefix}erp_hr_leave_encashment_requests AS encash
                WHERE encash.user_id = %d AND encash.leave_id = %d AND encash.f_year = %d",
                $entitlement->user_id,
                $entitlement->leave_id,
                $entitlement->f_year
            )
        );

        $formatted_data[$key]['leave_id']             = $entitlement->leave_id;
        $formatted_data[$key]['policy_name']          = $entitlement->leave->name;
        $formatted_data[$key]['user_id']              = $entitlement->user_id;
        $formatted_data[$key]['employee_name']        = $entitlement->employee->user->display_name;
        $formatted_data[$key]['pay_rate']             = $entitlement->employee->pay_rate;
        $formatted_data[$key]['f_year_id']            = $entitlement->f_year;
        $formatted_data[$key]['available']            = $entitlement->available;
        $formatted_data[$key]['carryover_uses_limit'] = $policy->carryover_uses_limit;
        $formatted_data[$key]['max_carry_days']       = $carry_days;
        $formatted_data[$key]['max_encash_days']      = $encash_days;
        $formatted_data[$key]['forward_default']      = $policy->forward_default;
        $formatted_data[$key]['encash_days']          = isset( $encashment) ? $encashment->encash_days : 0;
        $formatted_data[$key]['forward_days']         = isset( $encashment ) ? $encashment->forward_days : 0;
        $formatted_data[$key]['amount']               = isset( $encashment ) ? $encashment->amount : 0;
        $formatted_data[$key]['total']                = isset( $encashment) ? $encashment->total : 0;
    }

    return erp_array_to_object( $formatted_data );
}

/**
 * Generate users forward leaves
 *
 * @since 1.0.0
 *
 * @return object
 */
function erp_pro_hr_leave_generate_users_forward_leaves( $items ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    /**
     * Markdown ( https://dillinger.io/ )
### Encashment (default)

| Available | Max Encash | Max Carry | Encash | Carry | Waste |
|----|---|---|---|---|---|
| 10 | 8 | 6 | 8 | 2 | 4 |
|  4 | 3 | 2 | 3 | 1 | 0 |
|  6 | 2 | 1 | 2 | 1 | 3 |
    */

    foreach ( $items as $key => $item ) {
        if ( $item->forward_default === 'encashment' ) {
            $days = (float) $item->available - (float) $item->max_encash_days;

            if ( $days > 0 ) {
                $items[$key]->forward_days = ( $days > $item->max_carry_days ) ? $item->max_carry_days : $days;

                $items[$key]->encash_days = $item->max_encash_days;
                $items[$key]->amount = $item->pay_rate / 30;
                $items[$key]->total = $items[$key]->amount * $item->max_encash_days;
            } else {
                $items[$key]->encash_days = $item->available;
                $items[$key]->amount = $item->pay_rate / 30;
                $items[$key]->total = $items[$key]->amount * $item->available;
            }
        } else {
            $days = (float) $item->available - (float) $item->max_carry_days;

            if ( $days > 0 ) {
                $items[$key]->forward_days = $item->max_carry_days;

                $items[$key]->encash_days = ( $days > $item->max_encash_days ) ? $item->max_encash_days : $days;
                $items[$key]->amount = $item->pay_rate / 30;
                $items[$key]->total = $items[$key]->amount * $items[$key]->encash_days;
            } else {
                $items[$key]->forward_days = $item->available;
            }
        }
    }

    return $items;
}

/**
 * Apply generated forward leaves
 */
function erp_pro_hr_leave_apply_users_forward_leaves( $items ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $prev_f_year = erp_pro_hr_leave_get_prev_financial_year();
    $curr_f_year = erp_hr_get_financial_year_from_date();

    if ( ! isset( $prev_f_year ) || ! isset( $curr_f_year ) ) {
        return;
    }

    $exists = LeaveEncashmentRequest::where('f_year', $prev_f_year->id )->first();

    if ( $exists ) {
        return;
    }

    $current_user_id = get_current_user_id();

    foreach ( $items as $item ) {
        $encashment = LeaveEncashmentRequest::create(
            array(
                'user_id'      => $item->user_id,
                'leave_id'     => $item->leave_id,
                'f_year'       => $prev_f_year->id,
                'approved_by'  => $current_user_id,
                'encash_days'  => $item->encash_days,
                'forward_days' => $item->forward_days,
                'amount'       => $item->amount,
                'total'        => $item->total
            )
        );

        if ( $item->encash_days ) {
            LeaveEntitlement::create(
                array(
                    'user_id'     => $item->user_id,
                    'leave_id'    => $item->leave_id,
                    'trn_type'    => 'leave_encashment_requests',
                    'f_year'      => $prev_f_year->id,
                    'trn_id'      => $encashment->id,
                    'day_in'      => 0,
                    'day_out'     => $item->encash_days,
                    'description' => 'Leave encash',
                    'created_by'  => $current_user_id
                )
            );
        }

        if ( $item->forward_days ) {
            // add to previous f_year
            LeaveEntitlement::create(
                array(
                    'user_id'     => $item->user_id,
                    'leave_id'    => $item->leave_id,
                    'trn_type'    => 'leave_encashment_requests',
                    'f_year'      => $prev_f_year->id,
                    'trn_id'      => $encashment->id,
                    'day_in'      => 0,
                    'day_out'     => $item->forward_days,
                    'description' => 'Leave forward',
                    'created_by'  => $current_user_id
                )
            );

            // add to current f_year
            LeaveEntitlement::create(
                array(
                    'user_id'     => $item->user_id,
                    'leave_id'    => $item->leave_id,
                    'trn_type'    => 'leave_encashment_requests',
                    'f_year'      => $curr_f_year->id,
                    'trn_id'      => $encashment->id,
                    'day_in'      => $item->forward_days,
                    'day_out'     => 0,
                    'description' => $item->carryover_uses_limit,
                    'created_by'  => $current_user_id
                )
            );
        }
    }
}


/**
 * Forward leave export
 *
 * @since 1.0.0
 *
 * @return string
 */
function erp_pro_hr_leave_export_encash_requests( $f_year_id ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    header('Content-Type: application/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="unpaid-leaves.csv"');

    $requests = LeaveEncashmentRequest::where('f_year', $f_year_id)->where('encash_days', '<>', 0)->get();

    $fp = fopen( 'php://output', 'w' );
    fputcsv( $fp, array(
        esc_html__('ID', 'erp-pro'),
        esc_html__('Employee', 'erp-pro'),
        esc_html__('Leave', 'erp-pro'),
        esc_html__('Approver', 'erp-pro'),
        esc_html__('Encash days', 'erp-pro'),
        esc_html__('Leave Year', 'erp-pro'),
        esc_html__('Amount', 'erp-pro'),
        esc_html__('Total', 'erp-pro'),
        esc_html__('Created Date', 'erp-pro')
    ) );

    foreach( $requests as $request ) {
        $formatted_data = array();

        $formatted_data['id']           = $request->id;
        $formatted_data['employee']     = $request->employee->user->display_name;
        $formatted_data['leave_name']   = $request->leave->name;
        $formatted_data['approved_by']  = $request->approver->display_name;
        $formatted_data['encash_days']  = $request->encash_days;
        $formatted_data['year_name']    = $request->financial_year->fy_name;
        $formatted_data['amount']       = $request->amount;
        $formatted_data['total']        = $request->total;
        $formatted_data['created_date'] = explode(' ', $request->created_at)[0];

        fputcsv( $fp, array_values($formatted_data) );
    }

    fclose( $fp );
}

