<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class Mempool extends DeviceRelatedModel implements Keyable
{
    protected $table = 'mempools';
    protected $primaryKey = 'mempool_id';
    public $timestamps = false;
    protected $fillable = [
        'mempool_index',
        'entPhysicalIndex',
        'hrDeviceIndex',
        'mempool_type',
        'mempool_precision',
        'mempool_descr',
        'mempool_perc',
        'mempool_free',
        'mempool_total',
        'mempool_perc_warn',
        'mempool_largestfree',
        'mempool_lowestfree',
    ];

    public function setMempoolPercAttribute($percent)
    {
        $this->attributes['mempool_perc'] = round($percent);
    }

    public function getCompositeKey()
    {
        return "$this->mempool_type-$this->mempool_index";
    }
}
