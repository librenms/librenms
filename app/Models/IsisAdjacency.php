<?php

/**
 * IsisAdjacency.php
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
 *
 * @copyright  2021 Otto Reinikainen
 * @author     Otto Reinikainen <otto@ottorei.fi>
 */

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class IsisAdjacency extends PortRelatedModel implements Keyable
{
    use Filterable;
    use HasFactory;

    //public $primaryKey = 'id';
    public $timestamps = false;

    /**
     * Columns the v1 API may filter and sort on, see App\Http\Controllers\Api\V1\IsisAdjacencyController.
     *
     * @var list<string>
     */
    protected array $filterable = [
        'id',
        'device_id',
        'port_id',
        'ifIndex',
        'index',
        'isisISAdjState',
        'isisISAdjNeighSysID',
        'isisISAdjIPAddrAddress',
        'search',
    ];

    protected $fillable = [
        'device_id',
        'index',
        'port_id',
        'ifIndex',
        'isisCircAdminState',
        'isisISAdjState',
        'isisISAdjNeighSysType',
        'isisISAdjNeighSysID',
        'isisISAdjNeighPriority',
        'isisISAdjLastUpTime',
        'isisISAdjAreaAddress',
        'isisISAdjIPAddrType',
        'isisISAdjIPAddrAddress',
        'isisCircLevelMetricL1',
        'isisCircLevelWideMetricL1',
        'isisCircLevelMetricL2',
        'isisCircLevelWideMetricL2',
    ];

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function getCompositeKey(): string
    {
        return "$this->ifIndex-$this->index";
    }

    /**
     * The v1 API search filter: neighbour system ID or IP address.
     */
    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['isisISAdjNeighSysID', 'isisISAdjIPAddrAddress'], $query, $value, $config);
    }
}
