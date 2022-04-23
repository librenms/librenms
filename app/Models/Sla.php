<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class Sla extends DeviceRelatedModel implements Keyable
{
    protected $table = 'slas';
    protected $primaryKey = 'sla_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'sla_nr',
        'owner',
        'tag',
        'rtt_type',
        'rtt',
        'status',
        'opstatus',
        'deleted',
    ];
    protected $attributes = [ // default values
        'deleted' => 0,
    ];

    public function getCompositeKey()
    {
        return "$this->owner-$this->tag";
    }
}
