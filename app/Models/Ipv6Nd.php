<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ipv6Nd extends Model
{
    use HasFactory;
    protected $table = 'ipv6_nd';
    protected $fillable = [
        'port_id',
        'device_id',
        'mac_address',
        'ipv6_address',
        'context_name',
    ];
}
