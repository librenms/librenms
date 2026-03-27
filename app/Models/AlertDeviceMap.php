<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertDeviceMap extends DeviceRelatedModel
{
    protected $table = 'alert_device_map';
    public $timestamps = false;

    /** @return BelongsTo<AlertRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id');
    }
}
