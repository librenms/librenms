<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LibreNMS\Interfaces\Models\BillableSource;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\OS\Timos;

/**
 * @property-read string $encap_display
 */
class MplsSap extends DeviceRelatedModel implements BillableSource, Keyable
{
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

    public static function boot()
    {
        parent::boot();

        static::deleting(function (MplsSap $sap): void {
            $sap->bills()->detach();
        });
    }

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

    public function getBillingInOctets(): ?int
    {
        return $this->sapIngressOctets === null ? null : (int) $this->sapIngressOctets;
    }

    public function getBillingOutOctets(): ?int
    {
        return $this->sapEgressOctets === null ? null : (int) $this->sapEgressOctets;
    }

    public function getBillingCounterTime(): ?int
    {
        // counters are refreshed by the mpls poller module, the device poll time is the closest we have
        return $this->device?->last_polled?->getTimestamp();
    }

    public function getBillingSpeed(): ?int
    {
        return null; // a SAP has no ifSpeed, rely on counter wrap detection only
    }

    public function getBillingLabel(): string
    {
        return "SAP {$this->ifName}:{$this->encap_display} (service {$this->svc_oid}) on " . $this->device?->displayName();
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

    /**
     * @return MorphToMany<Bill, $this, BillCounter>
     */
    public function bills(): MorphToMany
    {
        return $this->morphToMany(Bill::class, 'source', 'bill_counters', 'source_id', 'bill_id')
            ->using(BillCounter::class);
    }
}
