<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\InsufficientDataException;
use LibreNMS\Exceptions\UncorrectableNegativeException;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;

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

    public function isValid(): bool
    {
        return $this->mempool_total > 0 && $this->mempool_used !== null && $this->mempool_free !== null && $this->mempool_perc !== null;
    }

    public function fillUsage($used = null, $total = null, $free = null, $percent = null, $multiplier = null): self
    {
        try {
            $multiplier ??= $this->mempool_precision ?: 1;
            $total = Number::correctIntegerOverflow($total) ?? ($this->mempool_total ? $this->mempool_total / $multiplier : null);
            $used = Number::correctIntegerOverflow($used, $total);
            $free = Number::correctIntegerOverflow($free, $total);

            [$this->mempool_total, $this->mempool_used, $this->mempool_free, $this->mempool_perc] = Number::fillMissingRatio(
                $total,
                $used,
                $free,
                $percent,
                0,
                $multiplier,
            );
        } catch (InsufficientDataException|UncorrectableNegativeException $e) {
            Log::info(get_class($e));
            Log::debug($e->getMessage());

            return $this;
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
        $this->attributes['mempool_perc'] = is_numeric($percent) ? round($percent) : null;
    }

    public function getCompositeKey()
    {
        return "$this->mempool_type-$this->mempool_index";
    }
}
