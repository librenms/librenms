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

    private ?PollingMethodConfig $configCache = null;

    public function setAttribute($key, $value)
    {
        $this->invalidateConfigCache();

        return parent::setAttribute($key, $value);
    }

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
        $method->settings = $definition->filterOverrides($settings, $method->settings ?? []);

        $method->save();
        $method->invalidateConfigCache();

        return $method;
    }

    /**
     * Build an unsaved, transient in-memory DevicePollingMethod model.
     *
     * @param  array<string, mixed>  $settings
     * @param  SnmpSecretData|IpmiSecretData|array<string, mixed>|null  $secretData
     */
    public static function transient(
        PollingMethodType $type,
        array $settings = [],
        SnmpSecretData|IpmiSecretData|array|null $secretData = null,
        ?Device $device = null,
        ?bool $affectsAvailability = null,
        bool $enabled = true,
        ?Secret $secret = null,
    ): self {
        $definition = $type->definition();
        $filteredSettings = $definition->filterOverrides($settings);
        $affectsAvail = $affectsAvailability ?? $definition->defaultAffectsAvailability();

        $method = new static([
            'method_type' => $type,
            'enabled' => $enabled,
            'affects_availability' => $affectsAvail,
            'settings' => $filteredSettings,
        ]);

        if ($device !== null) {
            $method->device_id = $device->device_id;
            $method->setRelation('device', $device);
        }

        if ($secret !== null) {
            $method->setRelation('secret', $secret);
            $method->secret_id = $secret->id;
        } elseif ($definition->secretDefinition() !== null && ! empty($secretData)) {
            $dataArray = $secretData instanceof SnmpSecretData || $secretData instanceof IpmiSecretData
                ? $secretData->toArray()
                : $secretData;

            $createdSecret = new Secret([
                'secret_type' => $type->value,
                'description' => $device ? strtoupper($type->value) . ' ' . $device->hostname : '',
                'data' => $dataArray,
            ]);
            $method->setRelation('secret', $createdSecret);
        }

        return $method;
    }

    public function secretData(): SnmpSecretData|IpmiSecretData|null
    {
        return $this->secret?->toSecretData();
    }

    public function toConfig(): PollingMethodConfig
    {
        if ($this->configCache !== null) {
            return $this->configCache;
        }

        $class = $this->method_type->definition()->class();

        return $this->configCache = $class::fromPollingMethod($this);
    }

    public function invalidateConfigCache(): void
    {
        $this->configCache = null;
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
