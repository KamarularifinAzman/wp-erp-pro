<?php

namespace WeDevs\ERP\Workflow\Models;

use WeDevs\ERP\Framework\Model;

class Log extends Model {

    protected $primaryKey = 'id';

    protected $table = 'erp_workflow_logs';

    public $timestamps = false;

    protected $fillable = ['workflow_id', 'created_at'];

    /**
     * Get the workflow that owns the log.
     */
    public function workflow() {
        return $this->belongsTo( 'WeDevs\ERP\Workflow\Models\Workflow' );
    }
}