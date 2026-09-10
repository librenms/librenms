<?php

namespace App\Models;

/**
 * Eloquent model for `alert_operation_transport_map`.
 */
class AlertOperationTransportMap extends BaseModel
{
    protected $table = 'alert_operation_transport_map';

    public $timestamps = false;

    protected $fillable = [
        'segment_id',
        'transport_or_group_id',
        'target_type',
    ];
}
