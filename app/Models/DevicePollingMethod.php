<?php

namespace App\Models;

use App\Casts\PollingMethodTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;

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
     * Not saved. While a device is added without a secret: only try default credentials of this type, null for all types.
     */
    public ?SecretType $defaultSecretType = null;

    /**
     * Match the database column defaults.
     */
    protected $attributes = [
        'enabled' => true,
        'affects_availability' => false,
    ];

    protected $casts = [
        'method_type' => PollingMethodTypeCast::class,
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

    public function typeName(): string
    {
        return (string) ($this->attributes['method_type'] ?? '');
    }

    public function typeLabel(): string
    {
        return $this->method_type === PollingMethodType::Unknown
            ? __('poller.unknown_type', ['type' => $this->typeName()])
            : (string) $this->method_type?->label();
    }

    /** @return BelongsTo<Secret, $this> */
    public function secret(): BelongsTo
    {
        return $this->belongsTo(Secret::class);
    }
}
