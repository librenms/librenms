<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertGroupMap extends Model
{
    protected $table = 'alert_group_map';
    public $timestamps = false;

    /** @return BelongsTo<AlertRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id');
    }

    /** @return BelongsTo<DeviceGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(DeviceGroup::class, 'group_id');
    }
}
