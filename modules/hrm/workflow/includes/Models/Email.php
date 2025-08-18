<?php

namespace WeDevs\ERP\Workflow\Models;

use WeDevs\ERP\Framework\Model;

class Email extends Model {

    protected $primaryKey = 'id';

    protected $table = 'erp_workflow_emails';

    public $timestamps = false;

    protected $fillable = ['subject', 'from', 'to', 'body', 'tags', 'processed', 'created_at'];
}