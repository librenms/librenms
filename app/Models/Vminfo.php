<?php

namespace App\Models;

use App\Facades\LibrenmsConfig;
use App\Models\Traits\Filterable;
use App\Observers\VminfoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use LibreNMS\Enum\PowerState;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;

#[ObservedBy([VminfoObserver::class])]
class Vminfo extends DeviceRelatedModel implements Keyable
{
    use Filterable;
    use HasFactory;

    protected $table = 'vminfo';
    public $timestamps = false;
    protected $fillable = [
        'vm_type',
        'vmwVmVMID',
        'vmwVmDisplayName',
        'vmwVmGuestOS',
        'vmwVmMemSize',
        'vmwVmCpus',
        'vmwVmState',
    ];

    /** @var list<string> */
    protected array $filterable = [
        'device_id',
        'vm_type',
        'vmwVmGuestOS',
        'vmwVmState',
        'vmwVmCpus',
        'vmwVmMemSize',
        'search',
    ];

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: string[], search?: bool}>
     */
    public static function filterFieldDefinitions(?int $deviceId = null): array
    {
        $fields = [];

        if ($deviceId === null) {
            $fields[] = [
                'key' => 'device_id',
                'label' => __('Host'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ];
        }

        return array_merge($fields, [
            [
                'key' => 'search',
                'label' => __('VM Name'),
                'type' => 'text',
                'search' => true,
            ],
            [
                'key' => 'vmwVmState',
                'label' => __('Power Status'),
                'type' => 'select',
                'options' => ['on', 'off', 'suspended', 'unknown'],
            ],
            [
                'key' => 'vm_type',
                'label' => __('Type'),
                'type' => 'text',
            ],
            [
                'key' => 'vmwVmGuestOS',
                'label' => __('Operating System'),
                'type' => 'text',
            ],
            [
                'key' => 'vmwVmCpus',
                'label' => __('vCPUs'),
                'type' => 'number',
            ],
            [
                'key' => 'vmwVmMemSize',
                'label' => __('Memory (MB)'),
                'type' => 'number',
            ],
        ]);
    }

    /**
     * Search the name of the VM and the host it runs on.
     */
    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['vmwVmDisplayName', 'device.hostname', 'device.sysName'], $query, $value, $config);
    }

    /**
     * Accept the power state by name instead of by number.
     */
    public function filterVmwVmState(Builder $query, mixed $value, array $config): void
    {
        $this->applyMappedFilter($query, $value, $config, fn (Builder $q, $state) => $q->where('vmwVmState', match ($state) {
            'on' => PowerState::ON,
            'off' => PowerState::OFF,
            'suspended' => PowerState::SUSPENDED,
            default => PowerState::UNKNOWN,
        }));
    }

    public function getStateLabelAttribute(): array
    {
        return Html::powerStateLabel($this->vmwVmState);
    }

    public function getMemoryFormattedAttribute(): string
    {
        return Number::formatBi($this->vmwVmMemSize * 1024 * 1024);
    }

    public function getOperatingSystemAttribute(): string
    {
        if (Str::contains($this->vmwVmGuestOS, 'tools not installed')) {
            return 'Unknown (VMware Tools not installed)';
        } elseif (Str::contains($this->vmwVmGuestOS, 'tools not running')) {
            return 'Unknown (VMware Tools not running)';
        } elseif (empty($this->vmwVmGuestOS)) {
            return '(Unknown)';
        } else {
            return Rewrite::vmwareGuest($this->vmwVmGuestOS);
        }
    }

    protected function scopeGuessFromDevice(Builder $query, Device $device): Builder
    {
        $where = [$device->hostname];

        if (LibrenmsConfig::get('mydomain')) {
            $where[] = $device->hostname . '.' . LibrenmsConfig::get('mydomain');
        }

        return $query->whereIn('vmwVmDisplayName', $where);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Device, $this>
     */
    public function parentDevice(): HasOne
    {
        return $this->hasOne(Device::class, 'hostname', 'vmwVmDisplayName');
    }

    public function getCompositeKey(): string
    {
        return "$this->vm_type-$this->vmwVmVMID";
    }
}
