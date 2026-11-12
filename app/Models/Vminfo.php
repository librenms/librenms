<?php

namespace App\Models;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Models\Traits\Filterable;
use App\Observers\VminfoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PowerState;
use LibreNMS\Interfaces\Models\HasMetrics;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Interfaces\VminfoMetricSource;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;

#[ObservedBy([VminfoObserver::class])]
class Vminfo extends DeviceRelatedModel implements HasMetrics, Keyable
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
        'cluster_id',
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
    public static function filterFieldDefinitions(): array
    {
        return [
            [
                'key' => 'device_id',
                'label' => __('Host'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ],
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
        ];
    }

    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['vmwVmDisplayName', 'device.hostname', 'device.sysName'], $query, $value, $config);
    }

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

        if ($domain = LibrenmsConfig::get('mydomain')) {
            $where[] = $device->hostname . '.' . $domain;
        }

        return $query->whereIn('vmwVmDisplayName', $where);
    }

    /**
     * VMs whose source records the graphed metrics, other VMs have no graphs.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeRecordsMetrics(Builder $query): Builder
    {
        $registry = app(PollingMethodRegistry::class);
        $types = collect($registry->providing(VminfoMetricSource::class))
            ->flatMap(function (PollingMethodType $type) use ($registry): array {
                /** @var class-string<VminfoMetricSource> $source */
                $source = $registry->get($type)->dataSourceClass();

                return $source::vmTypes();
            })->unique()->values()->all();

        return $query->whereIn('vm_type', $types);
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
        return "$this->cluster_id-$this->vm_type-$this->vmwVmVMID";  // cluster_id null on non-proxmox
    }

    public function metricIdentity(string $measurement): MetricIdentity
    {
        // a VM in a cluster keeps its metrics when it migrates to another device
        $group = $this->cluster_id === null ? ['hostname' => (string) $this->device?->hostname] : ['cluster' => $this->cluster_id];

        return new MetricIdentity($measurement, [...$group, 'type' => $this->vm_type, 'id' => $this->vmwVmVMID]);
    }
}
