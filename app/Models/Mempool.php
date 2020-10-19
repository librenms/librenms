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
        'mempool_type',
        'mempool_precision',
        'mempool_descr',
        'mempool_perc',
        'mempool_perc_oid',
        'mempool_used',
        'mempool_used_oid',
        'mempool_free',
        'mempool_free_oid',
        'mempool_total',
        'mempool_total_oid',
        'mempool_perc_warn',
        'mempool_largestfree',
        'mempool_lowestfree',
    ];

    public function fillUsage($used = null, $total = null, $free = null, $percent = null)
    {
        $this->mempool_total = $this->calculateTotal($total, $used, $free);
        $this->mempool_used = $used * $this->mempool_precision;
        $this->mempool_free = $free * $this->mempool_precision;
        $percent = $this->normalizePercent($percent); // don't assign to model or it loses precision
        $this->mempool_perc = $percent;

        if (! $this->mempool_total) {
            // could not calculate total, can't calculate other values
            return $this;
        }

        if ($used === null) {
            $this->mempool_used = $free !== null
                ? $this->mempool_total - $this->mempool_free
                : $this->mempool_total * ($percent / 100);
        }

        if ($free === null) {
            $this->mempool_free = $used !== null
                ? $this->mempool_total - $this->mempool_used
                : $this->mempool_total * (1 - ($percent / 100));
        }

        if ($percent == null) {
            $this->mempool_perc = $this->mempool_used / $this->mempool_total * 100;
        }

        return $this;
    }

    public function setMempoolPercAttribute($percent)
    {
        $this->attributes['mempool_perc'] = round($percent);
    }

    public function getCompositeKey()
    {
        return "$this->mempool_type-$this->mempool_index";
    }

    private function calculateTotal($total, $used, $free)
    {
        if ($total !== null) {
            return $total * $this->mempool_precision;
        }

        if ($used !== null && $free !== null) {
            return ($used + $free) * $this->mempool_precision;
        }

        return $this->mempool_total; // don't change the value it may have been set in discovery
    }

    private function normalizePercent($percent)
    {
        while ($percent > 100) {
            $percent = $percent / 10;
        }

        return $percent;
    }
}
