<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class HrDevice extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'hrDevice';
    protected $primaryKey = 'hrDevice_id';
    protected $fillable = [
        'hrDeviceIndex',
        'hrDeviceDescr',
        'hrDeviceType',
        'hrDeviceErrors',
        'hrDeviceStatus',
        'hrProcessorLoad',
    ];

    public function getCompositeKey(): int
    {
        return (int) $this->hrDeviceIndex;
    }
}
