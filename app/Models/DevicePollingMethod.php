<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodConfigInterface;
use LibreNMS\Polling\PollingMethodFactory;

class DevicePollingMethod extends Model
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
    ): self {
        $definition = $type->definition();

        /** @var self $method */
        $method = static::firstOrNew([
            'device_id' => $device->device_id,
            'method_type' => $type,
        ]);

        $affectsAvail = $affectsAvailability ?? $definition->defaultAffectsAvailability();
        $method->enabled = $enabled;
        $method->affects_availability = $affectsAvail;
        $method->settings = $definition->resolveValues($settings, $method->settings ?? []);

        $method->save();

        return $method;
    }

    /**
     * Build an unsaved, transient in-memory DevicePollingMethod model.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $secretData
     */
    public static function transient(
        PollingMethodType $type,
        array $settings = [],
        array $secretData = [],
        ?Device $device = null,
        ?bool $affectsAvailability = null,
        bool $enabled = true,
    ): self {
        $definition = $type->definition();
        $resolvedSettings = $definition->resolveValues($settings);
        $affectsAvail = $affectsAvailability ?? $definition->defaultAffectsAvailability();

        $method = new static([
            'method_type' => $type,
            'enabled' => $enabled,
            'affects_availability' => $affectsAvail,
            'settings' => $resolvedSettings,
        ]);

        if ($device !== null) {
            $method->device_id = $device->device_id;
            $method->setRelation('device', $device);
        }

        if ($definition->secretDefinition() !== null && ! empty($secretData)) {
            $secret = new Secret([
                'secret_type' => $type->value,
                'description' => $device ? strtoupper($type->value) . ' ' . $device->hostname : '',
                'default' => false,
                'data' => $secretData,
            ]);
            $method->setRelation('secret', $secret);
        }

        return $method;
    }

    public function toConfig(): PollingMethodConfigInterface
    {
        $this->loadMissing('secret');

        return app(PollingMethodFactory::class)->make($this);
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
