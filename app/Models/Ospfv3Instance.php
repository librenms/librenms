<?php

/**
 * OspfInstance.php
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

class Ospfv3Instance extends DeviceRelatedModel implements Keyable
{
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'context_name',
        'router_id',
        'ospfv3RouterId',
        'ospfv3AdminStatus',
        'ospfv3VersionNumber',
        'ospfv3AreaBdrRtrStatus',
        'ospfv3ASBdrRtrStatus',
        'ospfv3AsScopeLsaCount',
        'ospfv3AsScopeLsaCksumSum',
        'ospfv3ExtLsaCount',
        'ospfv3OriginateNewLsas',
        'ospfv3RxNewLsas',
        'ospfv3ExtAreaLsdbLimit',
        'ospfv3ExitOverflowInterval',
        'ospfv3ReferenceBandwidth',
        'ospfv3RestartSupport',
        'ospfv3RestartInterval',
        'ospfv3RestartStrictLsaChecking',
        'ospfv3RestartStatus',
        'ospfv3RestartAge',
        'ospfv3RestartExitReason',
        'ospfv3StubRouterSupport',
        'ospfv3StubRouterAdvertisement',
        'ospfv3DiscontinuityTime',
        'ospfv3RestartTime',
    ];

    // ---- Define Relationships ----

    public function areas(): HasMany
    {
        return $this->hasMany(Ospfv3Area::class);
    }

    public function nbrs(): HasMany
    {
        return $this->hasMany(Ospfv3Nbr::class);
    }

    public function ospfv3Ports(): HasMany
    {
        return $this->hasMany(Ospfv3Port::class);
    }

    public function getCompositeKey(): string
    {
        return "$this->device_id-$this->context_name";
    }
}
