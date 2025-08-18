<?php

namespace WeDevs\ERP\Workflow\Models;

use WeDevs\ERP\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model {

    use SoftDeletes;

    protected $primaryKey = 'id';

    protected $table      = 'erp_workflows';

    public $timestamps    = true;

    protected $fillable   = ['name', 'type', 'condition_type', 'events_group', 'event', 'object', 'status', 'conditions_group', 'delay_time', 'delay_period', 'run', 'created_by'];

    protected $dates      = ['deleted_at'];

    /**
     * Get the conditions for the workflow.
     */
    public function conditions() {
        return $this->hasMany( 'WeDevs\ERP\Workflow\Models\Condition' );
    }

    /**
     * Get the actions for the workflow.
     */
    public function actions() {
        return $this->hasMany( 'WeDevs\ERP\Workflow\Models\Action' );
    }
}