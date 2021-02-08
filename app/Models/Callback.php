<?php
/**
 * Callback.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Callback extends Model
{
    public $timestamps = false;
    protected $table = 'callback';
    protected $primaryKey = 'callback_id';
    protected $fillable = ['name', 'value'];

    public static function get($name)
    {
        return static::query()->where('name', $name)->value('value');
    }

    public static function set($name, $value)
    {
        return static::query()->updateOrCreate(['name' => $name], ['name' => $name, 'value' => $value]);
    }
}
