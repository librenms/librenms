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
 * @copyright  2021 Otto Reinikainen
 * @author     Otto Reinikainen <otto@ottorei.fi>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class IsisAdjacency extends PortRelatedModel
{
    use HasFactory;

    //public $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'port_id',
        'ifIndex',
        'isisISAdjState',
        'isisISAdjNeighSysType',
        'isisISAdjNeighSysID',
        'isisISAdjNeighPriority',
        'isisISAdjLastUpTime',
        'isisISAdjAreaAddress',
        'isisISAdjIPAddrType',
        'isisISAdjIPAddrAddress',
    ];

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo(\App\Models\Port::class, 'device_id');
    }
}
