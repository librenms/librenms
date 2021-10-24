<?php

namespace App\Models;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Models\Keyable;

class Storage extends DeviceRelatedModel implements Keyable
{
    protected $table = 'storage';
    protected $primaryKey = 'storage_id';
    protected $fillable = [
        'storage_mib',
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
        'storage_deleted',
    ];

    public function getCompositeKey()
    {
        return "$this->storage_mib-$this->storage_index";
    }

    public function isValid(string $os)
    {
        // filter by mounts ignores
        foreach (\LibreNMS\Config::getCombined($os, 'ignore_mount', []) as $im) {
            if ($im == $this->storage_descr) {
                d_echo("ignored $this->storage_descr (matched: $im)\n");

                return false;
            }
        }

        foreach (\LibreNMS\Config::getCombined($os, 'ignore_mount_string', []) as $ims) {
            if (Str::contains($this->storage_descr, $ims)) {
                d_echo("ignored $this->storage_descr (matched: $ims)\n");

                return false;
            }
        }

        foreach (\LibreNMS\Config::getCombined($os, 'ignore_mount_regexp', []) as $imr) {
            if (preg_match($imr, $this->storage_descr)) {
                d_echo("ignored $this->storage_descr (matched: $imr)\n");

                return false;
            }
        }

        // filter by type
        if (\LibreNMS\Config::get('ignore_mount_removable', false) && $this->storage_type == 'hrStorageRemovableDisk') {
            d_echo("skip(removable)\n");
            return false;
        }

        if (\LibreNMS\Config::get('ignore_mount_network', false) && $this->storage_type == 'hrStorageNetworkDisk') {
            d_echo("skip(network)\n");
            return false;
        }

        if (\LibreNMS\Config::get('ignore_mount_optical', false) && $this->storage_type == 'hrStorageCompactDisc') {
            d_echo("skip(cd)\n");
            return false;
        }

        return true;
    }
}
