<?php

namespace WeDevs\ERP\Workflow\Models;

use WeDevs\ERP\Framework\Model;

class Condition extends Model {

    protected $primaryKey = 'id';

    protected $table = 'erp_workflow_conditions';

    public $timestamps = false;

    protected $fillable = ['condition_name', 'operator', 'value', 'parent_id', 'workflow_id'];

    /**
     * Get the workflow that owns the condition.
     */
    public function workflow() {
        return $this->belongsTo( 'WeDevs\ERP\Workflow\Models\Workflow' );
    }
}