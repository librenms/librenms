<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\PortSecurityStatus;
use LibreNMS\Interfaces\Models\Keyable;

class PortSecurity extends DeviceRelatedModel implements Keyable
{
    use Filterable;
    use HasFactory;

    protected $table = 'port_security';
    public $timestamps = false;
    protected $fillable = [
        'port_id',
        'device_id',
        'port_security_enable',
        'status',
        'max_addresses',
        'address_count',
        'violation_action',
        'violation_count',
        'last_mac_address',
        'sticky_enable',
    ];

    /**
     * @return array<string, string>
     */
    protected $casts = [
        'port_security_enable' => 'boolean',
        'sticky_enable' => 'boolean',
    ];

    protected array $filterable = [
        'device_id',
        'port_security_enable',
        'status',
        'max_addresses',
        'address_count',
        'violation_action',
        'violation_count',
        'last_mac_address',
        'sticky_enable',
        'search',
        'port.ifName',
        'port.ifDescr',
        'port.ifAlias',
        'device.hostname',
    ];

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: string[], params?: array<string, string|int>}>
     */
    public static function filterFieldDefinitions(?int $deviceId = null): array
    {
        $fields = [];

        if ($deviceId === null) {
            $fields[] = [
                'key' => 'device_id',
                'label' => __('Device'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ];
            $fields[] = [
                'key' => 'device.hostname',
                'label' => __('Hostname'),
                'type' => 'text',
            ];
        }

        return array_merge($fields, [
            [
                'key' => 'search',
                'label' => __('Port name'),
                'type' => 'text',
                'search' => true,
            ],
            [
                'key' => 'port_security_enable',
                'label' => __('Enabled'),
                'type' => 'boolean',
            ],
            [
                'key' => 'status',
                'label' => __('Status'),
                'type' => 'select',
                'options' => [
                    PortSecurityStatus::SECURE_UP,
                    PortSecurityStatus::SECURE_DOWN,
                    PortSecurityStatus::SHUTDOWN,
                ],
            ],
            [
                'key' => 'address_count',
                'label' => __('Current MACs'),
                'type' => 'number',
            ],
            [
                'key' => 'max_addresses',
                'label' => __('Max MACs'),
                'type' => 'number',
            ],
            [
                'key' => 'violation_action',
                'label' => __('Violation Action'),
                'type' => 'select',
                'options' => [
                    'shutdown',
                    'dropNotify',
                    'drop',
                ],
            ],
            [
                'key' => 'violation_count',
                'label' => __('Violations'),
                'type' => 'number',
            ],
            [
                'key' => 'last_mac_address',
                'label' => __('Mac address'),
                'type' => 'text',
            ],
            [
                'key' => 'sticky_enable',
                'label' => __('Sticky'),
                'type' => 'boolean',
            ],
        ]);
    }

    public function getCompositeKey(): int
    {
        return (int) $this->port_id;
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    /**
     * Search port name fields (ifName, ifDescr, ifAlias).
     */
    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['port.ifName', 'port.ifDescr', 'port.ifAlias'], $query, $value, $config);
    }
}
