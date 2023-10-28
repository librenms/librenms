<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InsufficientDataException;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;

class Storage extends DeviceRelatedModel implements Keyable
{
    protected $table = 'storage';
    protected $primaryKey = 'storage_id';
    public $timestamps = false;
    protected $fillable = [
        'type',
        'storage_index',
        'storage_type',
        'storage_descr',
        'storage_size',
        'storage_size_oid',
        'storage_units',
        'storage_used',
        'storage_used_oid',
        'storage_free',
        'storage_free_oid',
        'storage_perc',
        'storage_perc_oid',
        'storage_perc_warn',
    ];

    public function getCompositeKey()
    {
        return "$this->type-$this->storage_index";
    }

    public function fillUsage($used = null, $total = null, $free = null, $percent = null): self
    {
        try {
            [$this->storage_size, $this->storage_used, $this->storage_free, $this->storage_perc]
                = Number::fillMissingRatio($total, $used, $free, $percent, 0, $this->storage_units);
        } catch (InsufficientDataException $e) {
            Log::debug($e->getMessage());

            return $this; //
        }

        return $this;
    }

    public function isValid(string $os): bool
    {
        // filter by mounts ignores
        foreach (\LibreNMS\Config::getCombined($os, 'ignore_mount') as $im) {
            if ($im == $this->storage_descr) {
                Log::debug("ignored $this->storage_descr (matched: $im)\n");

                return false;
            }
        }

        foreach (\LibreNMS\Config::getCombined($os, 'ignore_mount_string') as $ims) {
            if (str_contains($this->storage_descr, $ims)) {
                Log::debug("ignored $this->storage_descr (matched: $ims)\n");

                return false;
            }
        }

        foreach (\LibreNMS\Config::getCombined($os, 'ignore_mount_regexp') as $imr) {
            if (preg_match($imr, $this->storage_descr)) {
                Log::debug("ignored $this->storage_descr (matched: $imr)\n");

                return false;
            }
        }

        // filter by type
        if (\LibreNMS\Config::get('ignore_mount_removable', false) && $this->storage_type == 'hrStorageRemovableDisk') {
            Log::debug("skip(removable)\n");
            return false;
        }

        if (\LibreNMS\Config::get('ignore_mount_network', false) && $this->storage_type == 'hrStorageNetworkDisk') {
            Log::debug("skip(network)\n");
            return false;
        }

        if (\LibreNMS\Config::get('ignore_mount_optical', false) && $this->storage_type == 'hrStorageCompactDisc') {
            Log::debug("skip(cd)\n");
            return false;
        }

        return true;
    }
}
