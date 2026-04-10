<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpsecTunnel extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $table = 'ipsec_tunnels';
    protected $primaryKey = 'tunnel_id';
    public $timestamps = false;
}
