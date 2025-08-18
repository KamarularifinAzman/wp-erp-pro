<?php

namespace WeDevs\ERP\Workflow\Models;

use WeDevs\ERP\Framework\Model;

class Action extends Model {

    protected $primaryKey = 'id';

    protected $table = 'erp_workflow_actions';

    public $timestamps = false;

    protected $fillable = ['name', 'params', 'extra', 'workflow_id'];

    /**
     * Get the workflow that owns the action.
     */
    public function workflow() {
        return $this->belongsTo( 'WeDevs\ERP\Workflow\Models\Workflow' );
    }
}