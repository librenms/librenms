<?php

namespace App\Models;

class HrSystem extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $table = 'hrSystem';
    protected $fillable = ['device_id', 'hrSystemNumUsers', 'hrSystemProcesses', 'hrSystemMaxProcesses'];

    protected $primaryKey = 'hrSystem_id';
}
