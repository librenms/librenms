<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class Sla extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
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

    public function getCompositeKey(): string
    {
        return "$this->owner-$this->tag";
    }
}
