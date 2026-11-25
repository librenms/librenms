<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class EntPhysical extends DeviceRelatedModel implements Keyable
{
    use Filterable;
    use HasFactory;

    protected $table = 'entPhysical';
    protected $primaryKey = 'entPhysical_id';
    public $timestamps = false;
    protected $fillable = [
        'entPhysicalIndex',
        'entPhysicalDescr',
        'entPhysicalContainedIn',
        'entPhysicalClass',
        'entPhysicalName',
        'entPhysicalSerialNum',
        'entPhysicalModelName',
        'entPhysicalMfgName',
        'entPhysicalMfgDate',
        'entPhysicalVendorType',
        'entPhysicalParentRelPos',
        'entPhysicalHardwareRev',
        'entPhysicalFirmwareRev',
        'entPhysicalSoftwareRev',
        'entPhysicalIsFRU',
        'entPhysicalAlias',
        'entPhysicalAssetID',
        'ifIndex',
    ];

    protected array $filterable = [
        'search',
        'device_id',
        'part_type',
        'entPhysicalDescr',
        'entPhysicalModelName',
        'entPhysicalSerialNum',
    ];

    protected function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['entPhysicalName', 'entPhysicalDescr', 'entPhysicalModelName', 'entPhysicalSerialNum'], $query, $value, $config);
    }

    public function getCompositeKey(): int
    {
        return (int) $this->entPhysicalIndex;
    }
}
