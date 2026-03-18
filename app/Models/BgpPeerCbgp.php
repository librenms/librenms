<?php

namespace App\Models;

class BgpPeerCbgp extends DeviceRelatedModel
{
    protected $table = 'bgpPeers_cbgp';
    public $timestamps = false;
    public $incrementing = false;
}
