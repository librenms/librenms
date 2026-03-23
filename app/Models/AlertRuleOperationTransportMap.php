<?php

namespace App\Models;

/**
 * Eloquent model for `alert_rule_operation_transport_map`.
 *
 * This is the operation-level transport/group mapping used by alert rule operations.
 */
class AlertRuleOperationTransportMap extends BaseModel
{
    protected $table = 'alert_rule_operation_transport_map';

    public $timestamps = false;

    protected $fillable = [
        'operation_id',
        'transport_or_group_id',
        'target_type',
    ];
}

