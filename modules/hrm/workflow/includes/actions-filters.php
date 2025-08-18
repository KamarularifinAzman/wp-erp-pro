<?php

$workflow = new \WeDevs\ERP\Workflow\Workflows();

$workflow->define_hooks();

// Logging the executed workflow for the reports
add_action( 'erp_wf_run_workflow', function ( $workflow ) {
    $data = [
        'workflow_id' => $workflow->id,
        'created_at'  => current_time( 'mysql' )
    ];

    \WeDevs\ERP\Workflow\Models\Log::create( $data );
} );