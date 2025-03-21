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

class Ospfv3Port extends PortRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'port_id',
        'ospfv3_instance_id',
        'ospfv3_port_id',
        'context_name',
        'ospfv3IfIndex',
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
    ];

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id');
    }
}
