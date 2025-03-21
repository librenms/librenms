<?php

/**
 * OspfArea.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class Ospfv3Area extends DeviceRelatedModel implements Keyable
{
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'ospfv3_instance_id',
        'context_name',
        'ospfv3AreaId',
        'ospfv3AreaImportAsExtern',
        'ospfv3AreaSpfRuns',
        'ospfv3AreaBdrRtrCount',
        'ospfv3AreaAsBdrRtrCount',
        'ospfv3AreaScopeLsaCount',
        'ospfv3AreaScopeLsaCksumSum',
        'ospfv3AreaSummary',
        'ospfv3AreaStubMetric',
        'ospfv3AreaStubMetricType',
        'ospfv3AreaNssaTranslatorRole',
        'ospfv3AreaNssaTranslatorState',
        'ospfv3AreaNssaTranslatorStabInterval',
        'ospfv3AreaNssaTranslatorEvents',
        'ospfv3AreaTEEnabled',
    ];

    // ---- Define Relationships ----

    public function ospfv3Ports(): HasMany
    {
        return $this->hasMany(Ospfv3Port::class);
    }

    public function getCompositeKey(): string
    {
        return "$this->device_id-$this->ospfv3AreaId-$this->context_name";
    }
}
