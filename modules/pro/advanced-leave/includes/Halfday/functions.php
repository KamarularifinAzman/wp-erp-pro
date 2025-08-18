<?php

use WeDevs\ERP\HRM\Models\LeaveEntitlement;

function erp_pro_hr_leave_check_halfday_availability( $entitle_id ) {
    $entitle = LeaveEntitlement::find( $entitle_id );

    if ( ! isset( $entitle->policy ) ) {
        return false;
    }

    if ( $entitle->policy->halfday_enable === '1' ) {
        return true;
    }

    return false;
}
