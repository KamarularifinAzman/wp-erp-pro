<?php
namespace WeDevs\AdvancedLeave\Forward;

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\LeaveEntitlement;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
}

/**
 * Class LeaveCarryForwardBgProcess
 * @package WeDevs\ERP_PRO\HR\Leave
 */
class LeaveCarryForwardBgProcess extends \WP_Background_Process {

    /**
     * Background process id, must be unique.
     *
     * @var string
     */
    protected $action = 'erp_pro_carry_forward_bg';


    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param int $entitlement_id Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $entitlement_id ) {
        global $wpdb;

        $entitlement = LeaveEntitlement::find( $entitlement_id );

        if ( ! $entitlement ) {
            error_log(
                print_r(
                    array(
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'Leave Carry Forward: Invalid entitlement id: ' . $entitlement_id,
                    ),
                    true
                )
            );
            return false;
        }

        // check day out is set to non-zero value
        if ( $entitlement->day_in <= 0 ) {
            $entitlement->description = '0';
            $entitlement->save();
            return false;
        }

        // check expire dates
        $expire_date = erp_current_datetime()
            ->setTimestamp( $entitlement->financial_year->start_date )
            ->modify( '+ ' . absint( $entitlement->description ) . ' days' );

        $today = erp_current_datetime();

        // bail out if expire date is smaller than current date
        if ( $expire_date > $today ) {
            return false;
        }

        // get approved leave request for current user for this leave
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT(rq.id), rq.days FROM {$wpdb->prefix}erp_hr_leave_request_details as dtl
                    left JOIN {$wpdb->prefix}erp_hr_leave_requests as rq on rq.id = dtl.leave_request_id
                    where rq.last_status = %d and rq.leave_id = %d and rq.user_id = %d and dtl.f_year = %d and dtl.leave_date <= %d",
                array( 1, $entitlement->leave_id, $entitlement->user_id, $entitlement->f_year, $expire_date->getTimestamp()  )
            )
        );

        $leave_days = array_sum( wp_list_pluck( $results, 'days' ) );

        if ( $leave_days < $entitlement->day_in ) {
            // expire remaining leaves
            $day_out = $entitlement->day_in - $leave_days;

            $new_entl = new LeaveEntitlement();
            $new_entl->user_id      = $entitlement->user_id;
            $new_entl->leave_id     = $entitlement->leave_id;
            $new_entl->f_year       = $entitlement->f_year;
            $new_entl->created_by   = $entitlement->created_by;
            $new_entl->trn_id       = $entitlement->trn_id;
            $new_entl->trn_type     = 'carry_forward_leave_expired';
            $new_entl->day_out      = $day_out;
            $new_entl->description  = 'generated';
            $new_entl->save();

            $entitlement->description = '0';
            $entitlement->save();

        }

        return false;
    }

    /**
     * Complete
     */
    protected function complete() {
        parent::complete();
    }
}
