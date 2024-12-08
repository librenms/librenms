<?php
/*
 * StateTranslation.php
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class StateTranslation extends Model implements Keyable
{
    const CREATED_AT = null;
    const UPDATED_AT = 'state_lastupdated';
    protected $primaryKey = 'state_translation_id';
    protected $fillable = [
        'state_descr',
        'state_draw_graph',
        'state_value',
        'state_generic_value',
    ];

    public function stateIndex(): BelongsTo
    {
        return $this->belongsTo(StateIndex::class, 'state_index_id', 'state_index_id');
    }

    public function getCompositeKey()
    {
        return $this->state_value;
    }
}
