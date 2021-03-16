<?php

namespace App\Models;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Models\Keyable;

class Mempool extends DeviceRelatedModel implements Keyable
{
    protected $table = 'mempools';
    protected $primaryKey = 'mempool_id';
    public $timestamps = false;
    protected $fillable = [
        'mempool_perc_warn',
        'mempool_index',
        'entPhysicalIndex',
        'mempool_type',
        'mempool_class',
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
        'mempool_largestfree',
        'mempool_lowestfree',
    ];
    protected $attributes = [
        'mempool_precision' => 1,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (! $this->exists) {
            // only allow mempool_perc_warn to be filled for new mempools
            unset($this->fillable[array_search('mempool_perc_warn', $this->fillable)]);
        }
    }

    public function isValid()
    {
        return $this->mempool_total > 0;
    }

    public function fillUsage($used = null, $total = null, $free = null, $percent = null)
    {
        try {
            $total = $this->correctNegative($total);
            $used = $this->correctNegative($used, $total);
            $free = $this->correctNegative($free, $total);
        } catch (\Exception $e) {
            d_echo($e->getMessage());

            return $this; // unhandled negative
        }

        $this->mempool_total = $this->calculateTotal($total, $used, $free);
        $this->mempool_used = $used * $this->mempool_precision;
        $this->mempool_free = $free * $this->mempool_precision;
        $percent = $this->normalizePercent($percent); // don't assign to model or it loses precision
        $this->mempool_perc = $percent;

        if (! $this->mempool_total) {
            if (! $percent && $percent !== 0.0) {
                // could not calculate total, can't calculate other values
                return $this;
            }
            $this->mempool_total = 100; // only have percent, mark total as 100
        }

        if ($used === null) {
            $this->mempool_used = $free !== null
                ? $this->mempool_total - $this->mempool_free
                : round($this->mempool_total * ($percent ? ($percent / 100) : 0));
        }

        if ($free === null) {
            $this->mempool_free = $used !== null
                ? $this->mempool_total - $this->mempool_used
                : round($this->mempool_total * ($percent ? (1 - ($percent / 100)) : 1));
        }

        if ($percent == null) {
            $this->mempool_perc = $this->mempool_used / $this->mempool_total * 100;
        }

        return $this;
    }

    /**
     * Set the mempool class.  If no class is given, try to detect it from available data.
     *
     * @param  string  $class
     * @param  string  $default
     * @return \App\Models\Mempool
     */
    public function setClass($class = null, $default = 'system')
    {
        if ($class) {
            $this->mempool_class = $class;

            return $this;
        }

        $memoryClasses = [
            'virtual' => ['virtual'],
            'swap' => ['swap'],
            'buffers' => ['buffers'],
            'cached' => ['cache'],
            'system' => ['shared real memory metrics'],
            'shared' => ['shared'],
        ];

        $descr = strtolower($this->mempool_descr);

        foreach ($memoryClasses as $class => $search) {
            if (Str::contains($descr, $search)) {
                $this->mempool_class = $class;

                return $this;
            }
        }

        if ($default == 'virtual' && Str::contains($this->mempool_descr, ['/dev/'])) {
            $this->mempool_class = 'swap';

            return $this;
        }

        $this->mempool_class = $default;

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

    private function correctNegative($value, $max = null)
    {
        $int_max = 4294967296;
        if ($value < 0) {
            // assume unsigned/signed issue
            $value = $int_max + $value;
            if (($max && $value > $max) || $value > $int_max) {
                throw new \Exception('Uncorrectable negative value');
            }
        }

        return $value;
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
        if ($percent === null) {
            return null;
        }

        $percent = floatval($percent);

        while ($percent > 100) {
            $percent = $percent / 10;
        }

        return $percent;
    }
}
