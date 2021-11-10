<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\PollingMethodType;

class DevicePollingMethod extends Model
{
    protected $fillable = [
        'device_id',
        'method_type',
        'enabled',
        'affects_availability',
        'secret_id',
        'settings',
        'last_checked_at',
        'last_check_successful',
    ];

    protected $casts = [
        'method_type' => PollingMethodType::class,
        'enabled' => 'boolean',
        'affects_availability' => 'boolean',
        'settings'  => 'array',
        'last_checked_at' => 'datetime',
        'last_check_successful' => 'boolean',
    ];

    /** @return BelongsTo<Device, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /** @return BelongsTo<Secret, $this> */
    public function secret(): BelongsTo
    {
        return $this->belongsTo(Secret::class);
    }
}
