<?php
/**
 * Package.php
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

use LibreNMS\Interfaces\Models\Keyable;

class Package extends DeviceRelatedModel implements Keyable
{
    public $timestamps = false;
    protected $primaryKey = 'pkg_id';
    protected $fillable = [
        'name',
        'manager',
        'status',
        'version',
        'build',
        'arch',
        'size',
    ];

    public function getCompositeKey()
    {
        return "$this->manager-$this->name-$this->arch";
    }

    public function __toString()
    {
        return $this->name . ' (' . $this->arch . ') version ' . $this->version . ($this->build ? "-$this->build" : '');
    }

    public function isValid(): bool
    {
        return $this->name && $this->manager && $this->arch && $this->version;
    }
}
