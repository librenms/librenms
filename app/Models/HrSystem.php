<?php

namespace App\Models;

class HrSystem extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $table = 'hrSystem';
    protected $fillable = ['hrSystemNumUsers', 'hrSystemProcesses', 'hrSystemMaxProcesses'];

    protected $primaryKey = 'hrSystem_id';
}
