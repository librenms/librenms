<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Alert\Transport;

class AlertTransport extends Model
{
    use HasFactory;

    protected $primaryKey = 'transport_id';
    public $timestamps = false;
    protected $casts = [
        'is_default' => 'boolean',
        'transport_config' => 'array',
    ];

    public function instance(): Transport
    {
        $class = Transport::getClass($this->transport_type);
        return new $class($this);
    }
}
