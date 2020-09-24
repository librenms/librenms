<?php
/**
 * OspfTos.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 */

namespace App\Models;

class OspfTos extends PortRelatedModel
{
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'ospf_port_id',
        'context_name',
        'ospfIfMetricIpAddress',
        'ospfIfMetricAddressLessIf',
        'ospfIfMetricTOS',
        'ospfIfMetricValue',
        'ospfIfMetricStatus',
    ];

    // ---- Define Relationships ----

    public function ospfPort()
    {
        return $this->belongsTo(\App\Models\OspfPort::class, 'device_id', 'ospf_port_id');
    }

    public function device()
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id');
    }
}
