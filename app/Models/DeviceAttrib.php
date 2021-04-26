<?php
/**
 * DeviceAttrib.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

class DeviceAttrib extends DeviceRelatedModel
{
    protected $table = 'devices_attribs';
    protected $primaryKey = 'attrib_id';
    public $timestamps = false;
    protected $fillable = ['attrib_type', 'attrib_value'];
//    protected $casts = ['attrib_value' => 'array'];
}
