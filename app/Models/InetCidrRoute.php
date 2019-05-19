<?php

namespace App\Models;

class InetCidrRoute extends DeviceRelatedModel
{
    protected $table = 'inetCidrRoute';
    protected $primaryKey = 'inetCidrRoute_id';
    static $translateProto = [
        '0-undefined',
        '1-other',
        '2-local',
        '3-netmgmt',
        '4-icmp',
        '5-egp',
        '6-ggp',
        '7-hello',
        '8-rip',
        '9-isIs',
        '10-esIs',
        '11-ciscoIgrp',
        '12-bbnSpfIgp',
        '13-ospf',
        '14-bgp',
        '15-idpr',
        '16-ciscoEigrp',
        '17-dvmrp'
    ];

    static $translateType = [
        '0-undefined',
        '1-other',
        '2-reject',
        '3-local',
        '4-remote',
        '5-blackhole',
    ];

    public $timestamps = true;

    // ---- Define Relationships ----
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }

    public function port()
    {
        return $this->belongsTo('App\Models\Port', 'port_id', 'port_id');
    }
}
