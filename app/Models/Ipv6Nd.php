<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;

class Ipv6Nd extends Model implements Keyable
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

    public function getCompositeKey(): string
    {
        return $this->getAttribute('port_id') . '_' . $this->getAttribute('ipv6_address');
    }
}
