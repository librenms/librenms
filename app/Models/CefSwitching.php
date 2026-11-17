<?php

/**
 * CefSwitching.php
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class CefSwitching extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'cef_switching';
    protected $primaryKey = 'cef_switching_id';

    protected $fillable = ['device_id', 'entPhysicalIndex', 'afi', 'cef_index', 'cef_path', 'drop', 'punt', 'punt2host', 'drop_prev', 'punt_prev', 'punt2host_prev', 'updated', 'updated_prev'];

    public function getCompositeKey(): string
    {
        return $this->device_id . '-' . (int) $this->entPhysicalIndex . '-' . $this->afi . '-' . (int) $this->cef_index;
    }
}
