<?php

namespace App\Models;

use App\Models\Traits\Billable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\BillableSource;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\OS\Timos;
use LibreNMS\Util\Url;

/**
 * @property-read string $encap_display
 */
class MplsSap extends DeviceRelatedModel implements BillableSource, Keyable
{
    use Billable;
    use HasFactory;
    protected $primaryKey = 'sap_id';
    public $timestamps = false;
    protected $fillable = [
        'svc_id',
        'svc_oid',
        'sapPortId',
        'ifName',
        'sapEncapValue',
        'device_id',
        'sapRowStatus',
        'sapType',
        'sapDescription',
        'sapAdminStatus',
        'sapOperStatus',
        'sapLastMgmtChange',
        'sapLastStatusChange',
        'sapIngressOctets',
        'sapEgressOctets',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     */
    public function getCompositeKey(): string
    {
        return $this->svc_oid . '-' . $this->sapPortId . '-' . $this->sapEncapValue;
    }

    // ---- Accessors/Mutators ----

    /**
     * Human readable outer.inner encapsulation, the stored value is the raw TmnxEncapVal
     * (or '*' for a dot1q wildcard SAP)
     */
    protected function encapDisplay(): Attribute
    {
        return Attribute::make(
            get: fn () => is_numeric($this->sapEncapValue) ? Timos::decodeEncapVal((int) $this->sapEncapValue) : (string) $this->sapEncapValue,
        );
    }

    // ---- Billing ----

    public static function billingTypeName(): string
    {
        return 'Nokia SAP';
    }

    public static function billingSelectType(): string
    {
        return 'mpls-sap';
    }

    public static function billingApiKey(): string
    {
        return 'mpls_saps';
    }

    public static function billingApiFields(): array
    {
        return ['device_id', 'sap_id', 'svc_oid', 'ifName', 'sapEncapValue', 'sapDescription'];
    }

    public static function filterBillingActive(Builder $query): void
    {
        $query->where('mpls_saps.sapOperStatus', 'up');
    }

    public function getBillingCounters(): ?array
    {
        // counters are refreshed by the mpls poller module, the device poll time is the closest we have
        $time = $this->device?->last_polled?->getTimestamp();
        if ($this->sapIngressOctets === null || $this->sapEgressOctets === null || ! $time) {
            return null;
        }

        return [(int) $this->sapIngressOctets, (int) $this->sapEgressOctets, $time];
    }

    public function getBillingSpeed(): ?int
    {
        return null; // a SAP has no ifSpeed, rely on counter wrap detection only
    }

    public function getBillingLabel(): string
    {
        return "{$this->ifName}:{$this->encap_display} (service {$this->svc_oid})" . ($this->sapDescription ? ' - ' . $this->sapDescription : '');
    }

    public function getBillingLink(): string
    {
        return Url::graphPopup([
            'device' => $this->device_id,
            'page' => 'graphs',
            'type' => 'device_sap',
            'traffic_id' => $this->getSapIndex(),
        ], e($this->getBillingLabel()));
    }

    public function getBillingRrd(): ?array
    {
        return [
            'filename' => \Rrd::name($this->device->hostname, \LibreNMS\Data\Store\Rrd::safeName('sap-' . $this->getSapIndex())),
            'ds_in' => 'sapIngressBits',
            'ds_out' => 'sapEgressBits',
            'multiplier' => 1,
        ];
    }

    /**
     * svc.port.encap index of the sap (snmp tables and rrd name), a wildcard encapsulation is 4095
     */
    private function getSapIndex(): string
    {
        return $this->svc_oid . '.' . $this->sapPortId . '.' . ($this->sapEncapValue == '*' ? '4095' : $this->sapEncapValue);
    }

    // ---- Define Relationships ----
    /**
     * @return HasMany<MplsSdpBind, $this>
     */
    public function binds(): HasMany
    {
        return $this->hasMany(MplsSdpBind::class, 'svc_id');
    }

    /**
     * @return BelongsTo<MplsService, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(MplsService::class, 'svc_id');
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'ifName', 'ifName');
    }
}
