<?php
/**
 * Config.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

class Config extends BaseModel
{
    public $timestamps = false;
    protected $table = 'config';
    public $primaryKey = 'config_name';
    public $incrementing = false;
    protected $fillable = [
        'config_name',
        'config_value',
        'config_default',
        'config_descr',
        'config_group',
        'config_sub_group',
    ];
    protected $attributes = [
        'config_default' => '',
        'config_descr' => '',
        'config_group' => '',
        'config_sub_group' => '',
    ];

    /**
     * Get the config_value (type cast)
     *
     * @param string $value
     * @return mixed
     */
    public function getConfigValueAttribute($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return (int)$value;
        } elseif (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return (float)$value;
        } elseif (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    public function setConfigValueAttribute($value)
    {
        if (is_bool($value)) {
            $this->attributes['config_value'] = $value ? 'true' : 'false';
        } else {
            $this->attributes['config_value'] = $value;
        }
    }
}
