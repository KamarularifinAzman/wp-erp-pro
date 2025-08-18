<?php

namespace WeDevs\ERP_PRO\Updates\BP;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
}

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Recruitment_Experience_Type_Migrator extends \WP_Background_Process {

    /**
     * Background process name
     *
     * @var string
     */
    protected $action = 'erp_hr_rec_experience_type_migrator';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param int $recruitment_post_id ID of post type 'erp_hr_recruitment'
     *
     * @return mixed
     */
    protected function task( $recruitment_post_id ) {
        $experience_type = get_post_meta( $recruitment_post_id, '_experience_type', true );

        if ( in_array( $experience_type, [ 'required', 'preferred' ], true ) ) {
            return false;
        }

        $update_value = 'required' === strtolower( $experience_type ) ? 'required' : 'preferred'; //if we can't find 'required' or 'preferred', we default to 'preferred'

        update_post_meta( $recruitment_post_id, '_experience_type', $update_value );

        return false;
    }
}

global $erp_recruitment_type_migrator_1_2_8;

$erp_recruitment_type_migrator_1_2_8 = new Recruitment_Experience_Type_Migrator();
