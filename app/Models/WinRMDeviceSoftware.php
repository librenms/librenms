<?php

namespace App\Models;

use App\Models\WinRMSoftware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WinRMDeviceSoftware extends Model
{
    use HasFactory;

    protected $table = 'winrm_device_software';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function softwareDetails(): HasOne
    {
        return $this->hasOne('App\Models\WinRMSoftware', 'id', 'software_id');
    }
}
