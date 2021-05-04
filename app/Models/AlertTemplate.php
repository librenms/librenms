<?php
/**
 * app/Models/AlertTemplate.php
 *
 * Model for access to alert_templates table data
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AlertTemplate extends BaseModel
{
    public $timestamps = false;

    // ---- Define Relationships ----

    public function map()
    {
        return $this->hasMany(\App\Models\AlertTemplateMap::class, 'alert_templates_id', 'id');
    }

    public function alert_rules(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\AlertRule::class, \App\Models\AlertTemplateMap::class, 'alert_templates_id', 'id', 'id', 'alert_rule_id')
                    ->select(['id' => 'alert_rules.id', 'name' => 'alert_rules.name'])
                    ->orderBy('alert_rules.name');
    }
}
