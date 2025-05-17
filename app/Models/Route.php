<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class Route extends PortRelatedModel implements Keyable
{
    protected $table = 'route';
    protected $primaryKey = 'route_id';
    protected $fillable = [
        'created_at',
        'updated_at',
        'device_id',
        'port_id',
        'context_name',
        'inetCidrRouteIfIndex',
        'inetCidrRouteType',
        'inetCidrRouteProto',
        'inetCidrRouteNextHopAS',
        'inetCidrRouteMetric1',
        'inetCidrRouteDestType',
        'inetCidrRouteDest',
        'inetCidrRouteNextHopType',
        'inetCidrRouteNextHop',
        'inetCidrRoutePolicy',
        'inetCidrRoutePfxLen',
    ];

    //ipCidrRouteProto from ipForward Mib
    public static $translateProto = [
        'undefined',
        'other',
        'local',
        'netmgmt',
        'icmp',
        'egp',
        'ggp',
        'hello',
        'rip',
        'isIs',
        'esIs',
        'ciscoIgrp',
        'bbnSpfIgp',
        'ospf',
        'bgp',
        'idpr',
        'ciscoEigrp',
        'dvmrp',
    ];

    public static $translateType = [
        'undefined',
        'other',
        'reject',
        'local',
        'remote',
        'blackhole',
    ];

    public $timestamps = true;

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id', 'port_id');
    }

    public function getCompositeKey(): string
    {
        return
        $this->context_name . '-' .
        $this->inetCidrRouteIfIndex . '-' .
        $this->inetCidrRouteDest . '-' .
        $this->inetCidrRouteNextHop . '-' .
        $this->inetCidrRoutePfxLen;
    }
}
