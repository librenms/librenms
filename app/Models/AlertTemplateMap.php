<?php
/**
 * app/Models/AlertTemplateMap.php
 *
 * Model for access to alert_template_map table data
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

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertTemplateMap extends BaseModel
{
    protected $table = 'alert_template_map';
    public $timestamps = false;

    // ---- Define Relationships ----

    public function template(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AlertTemplate::class, 'alert_templates_id');
    }
}
