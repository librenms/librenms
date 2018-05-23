<?php
/**
 * IPv4Address.php
 *
 * IPv4 Address for a port
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

namespace App\Models\IP;

use LibreNMS\Config;
use App\Models\BaseModel;

class IPv4Address extends BaseModel
{
    protected $table = 'ipv4_addresses';

    protected $primaryKey = 'ipv4_address_id';

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function port()
    {
        return $this->belongsTo('App\Models\Port');
    }
}