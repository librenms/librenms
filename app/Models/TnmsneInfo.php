<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TnmsneInfo extends DeviceRelatedModel
{
    protected $primaryKey = 'tnmsne_info_id';
    protected $table = 'tnmsneinfo';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'neID',
        'neType',
        'neName',
        'neLocation',
        'neAlarm',
        'neOpMode',
        'neOpState',
    ];
}
   // ---- Define Relationships ----

    public function alarms()
    {
        return $this->hasMany('App\Models\TnmsAlarm', 'tnmsne_info_id');
    }
