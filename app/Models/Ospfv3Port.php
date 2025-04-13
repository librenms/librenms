<?php

/**
 * OspfPort.php
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class Ospfv3Port extends PortRelatedModel implements Keyable
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'ospfv3_instance_id',
        'ospfv3_area_id',
        'port_id',
        'context_name',
        'ospfv3IfIndex',
        'ospfv3IfInstId',
        'ospfv3IfAreaId',
        'ospfv3IfType',
        'ospfv3IfAdminStatus',
        'ospfv3IfRtrPriority',
        'ospfv3IfTransitDelay',
        'ospfv3IfRetransInterval',
        'ospfv3IfHelloInterval',
        'ospfv3IfRtrDeadInterval',
        'ospfv3IfPollInterval',
        'ospfv3IfState',
        'ospfv3IfDesignatedRouter',
        'ospfv3IfBackupDesignatedRouter',
        'ospfv3IfEvents',
        'ospfv3IfDemand',
        'ospfv3IfMetricValue',
        'ospfv3IfLinkScopeLsaCount',
        'ospfv3IfLinkLsaCksumSum',
        'ospfv3IfDemandNbrProbe',
        'ospfv3IfDemandNbrProbeRetransLimit',
        'ospfv3IfDemandNbrProbeInterval',
        'ospfv3IfTEDisabled',
        'ospfv3IfLinkLSASuppression',
    ];

    // ---- Define Relationships ----

    public function area(): BelongsTo
    {
        return $this->belongsTo(Ospfv3Area::class);
    }

    public function getCompositeKey(): string
    {
        return "$this->device_id-$this->ospfv3IfIndex-$this->ospfv3IfInstId-$this->context_name";
    }
}
