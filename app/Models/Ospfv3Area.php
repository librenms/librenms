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

class Ospfv3Area extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $fillable = [
        'device_id',
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
    ];
}
