<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

/**
 * @property-read int|null $mac_count
 */
class PortsFdb extends PortRelatedModel implements Keyable
{
    use HasFactory, Filterable;

    /** @var list<string> */
    protected array $filterable = ['vlan'];
    protected $table = 'ports_fdb';
    protected $primaryKey = 'ports_fdb_id';
    public $timestamps = true;
    protected $fillable = [
        'port_id',
        'mac_address',
        'vlan',
        'device_id',
    ];

    public function getCompositeKey(): string
    {
        return "$this->vlan-$this->mac_address";
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Ipv4Mac, $this>
     */
    public function ipv4Addresses(): HasMany
    {
        return $this->hasMany(Ipv4Mac::class, 'mac_address', 'mac_address');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Ipv6Nd, $this>
     */
    public function ipv6Addresses(): HasMany
    {
        return $this->hasMany(Ipv6Nd::class, 'mac_address', 'mac_address');
    }
}
