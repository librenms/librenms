<?php
/**
 * DeviceGraph.php
 *
 * Device Graphs for device
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */

namespace App\Models;

use LibreNMS\Config;

class DeviceGraph extends BaseModel
{
    protected $primaryKey = 'device_id';
    public $timestamps = false;
    protected $hidden = ['device_id'];
    protected $appends = ['descr'];

    /**
     * Attribute to dynamically get the description for the graph
     * @return String
     */
    public function getDescrAttribute()
    {
        return Config::get("graph_types.device.{$this->attributes['graph']}.descr");
    }


    // Relationships
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device');
    }
}
