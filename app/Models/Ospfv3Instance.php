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

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ospfv3Instance extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'ospfv3_instance_id',
        'context_name',
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
    ];

    // ---- Define Relationships ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(\App\Models\UserWidget::class, 'dashboard_id');
    }
}
