<?php

namespace App\Models;

class HrSystem extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $table = 'hrSystem';
    protected $fillable = ['key', 'value'];

    protected $primaryKey = 'hrSystem_id';

    public static function boot()
    {
        parent::boot();

        static::saving(function (HrSystem $hrsystem) {
            $hrsystem->value_prev = $hrsystem->getRawOriginal('value');
        });
    }


}
