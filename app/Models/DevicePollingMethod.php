<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\PollingMethodType;

class DevicePollingMethod extends DeviceRelatedModel
{
    /** @use HasFactory<\Database\Factories\DevicePollingMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'method_type',
        'enabled',
        'affects_availability',
        'secret_id',
        'settings',
        'last_check_successful',
        'last_check_message',
        'last_check_changed_at',
    ];

    /**
     * Match the database column defaults.
     */
    protected $attributes = [
        'enabled' => true,
        'affects_availability' => false,
    ];

    protected $casts = [
        'method_type' => PollingMethodType::class,
        'enabled' => 'boolean',
        'affects_availability' => 'boolean',
        'settings' => 'array',
        'last_check_successful' => 'boolean',
        'last_check_changed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // the check result is only written when it changes, so it is not updated on every ping and poll
        static::saving(function (DevicePollingMethod $deviceMethod): void {
            if ($deviceMethod->isDirty('last_check_successful')) {
                $deviceMethod->last_check_changed_at = now();
            }
        });
    }

    /** @return BelongsTo<Secret, $this> */
    public function secret(): BelongsTo
    {
        return $this->belongsTo(Secret::class);
    }
}
