<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpsecTunnel extends Model
{
    protected $table = 'ipsec_tunnels';
    protected $primaryKey = 'tunnel_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'tunnel_index',
        'peer_port',
        'peer_addr',
        'local_addr',
        'local_port',
        'tunnel_name',
        'tunnel_status',
    ];
}
