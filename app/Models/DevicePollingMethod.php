<?php

namespace App\Models;

use App\Observers\DevicePollingMethodObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

#[ObservedBy([DevicePollingMethodObserver::class])]
class DevicePollingMethod extends Model
{
    /** @use HasFactory<\Database\Factories\DevicePollingMethodFactory> */
    use HasFactory;

    protected $with = [
        'secret',
    ];

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
        'settings' => 'array',
        'last_checked_at' => 'datetime',
        'last_check_successful' => 'boolean',
    ];

    /**
     * Save or update a DevicePollingMethod row settings for a device.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function saveForDevice(
        Device $device,
        PollingMethodType $type,
        array $settings = [],
        bool $enabled = true,
        ?bool $affectsAvailability = null,
        ?\LibreNMS\Polling\Method\Definitions\PollingMethodDefinition $definition = null,
    ): self {
        $definition ??= app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require($type);

        /** @var self $method */
        $method = static::firstOrNew([
            'device_id' => $device->device_id,
            'method_type' => $type,
        ]);

        $affectsAvail = $affectsAvailability ?? $definition->defaultAffectsAvailability();
        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvail;
        $method->settings = $definition->filterOverrides($settings, $method->settings ?? []);

        $method->save();

        return $method;
    }

    public function secretData(): SnmpSecretData|IpmiSecretData|null
    {
        return $this->secret?->toSecretData();
    }

    public function toConfig(): PollingMethodConfig
    {
        $class = app(\LibreNMS\Polling\Method\PollingMethodRegistry::class)->require($this->method_type)->class();

        return $class::fromPollingMethod($this);
    }

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
