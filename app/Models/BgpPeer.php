<?php
/**
 * BgpPeer.php
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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BgpPeer extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'bgpPeers';
    protected $primaryKey = 'bgpPeer_id';

    // ---- Query scopes ----

    public function scopeInAlarm(Builder $query)
    {
        return $query->where(function (Builder $query) {
            $query->where('bgpPeerAdminStatus', 'start')
                ->orWhere('bgpPeerAdminStatus', 'running');
        })->where('bgpPeerState', '!=', 'established');
    }

    // ---- Define Relationships ----

    public function ipv4Iface(): HasOne
    {
        return $this->hasOne(\App\Models\Ipv4Address::class, 'port_id', 'bgpPeerIface');
    }

    public function ipv6Iface(): HasOne
    {
        return $this->hasOne(\App\Models\Ipv6Address::class, 'port_id', 'bgpPeerIface');
    }
}
