<?php
/**
 * DeviceGroup.php
 *
 * Dynamic groups of devices
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use App\Util;
use DB;
use Settings;

class DeviceGroup extends BaseModel
{
    public $timestamps = false;
    protected $appends = ['patternSql'];
    protected $fillable = ['name', 'desc', 'pattern', 'params'];
    protected $casts = ['params' => 'array'];

    // ---- Helper Functions ----

    public function updateRelations()
    {
        // we need an id to add relationships
        if (is_null($this->id)) {
            $this->save();
        }

        $device_ids = $this->getDeviceIdsRaw();

        // update the relationships (deletes and adds as needed)
        $this->devices()->sync($device_ids);
    }

    /**
     * Get an array of the device ids from this group by re-querying the database with
     * either the specified pattern or the saved pattern of this group
     *
     * @param string $statement Optional, will use the pattern from this group if not specified
     * @param array $params array of paremeters
     * @return array
     */
    public function getDeviceIdsRaw($statement = null, $params = null)
    {
        if (is_null($statement)) {
            $statement = $this->pattern;
        }

        if (is_null($params)) {
            if (empty($this->params)) {
                if (!starts_with($statement, '%')) {
                    // can't build sql
                    return [];
                }
            } else {
                $params = $this->params;
            }
        }

        $statement = $this->applyGroupMacros($statement);
        $tables = $this->getTablesFromPattern($statement);

        $query = null;
        if (count($tables) == 1) {
            $query = DB::table($tables[0])->select('device_id')->distinct();
        } else {
            $query = DB::table('devices')->select('devices.device_id')->distinct();

            foreach ($tables as $table) {
                // skip devices table, we used that as the base.
                if ($table == 'devices') {
                    continue;
                }

                $query = $query->join($table, 'devices.device_id', '=', $table.'.device_id');
            }
        }

        // match the device ids
        if (is_null($params)) {
            return $query->whereRaw($statement)->pluck('device_id')->toArray();
        } else {
            return $query->whereRaw($statement, $params)->pluck('device_id')->toArray();
        }
    }

    /**
     * Process Macros
     *
     * @param string $pattern Rule to process
     * @param int $x Recursion-Anchor, do not pass
     * @return string|boolean
     */
    public static function applyGroupMacros($pattern, $x = 1)
    {
        if (!str_contains($pattern, 'macros.')) {
            return $pattern;
        }

        foreach (Settings::get('alert.macros.group', []) as $macro => $value) {
            $value = str_replace(['%', '&&', '||'], ['', 'AND', 'OR'], $value);  // this might need something more complex
            if (!str_contains($macro, ' ')) {
                $pattern = str_replace('macros.'.$macro, '('.$value.')', $pattern);
            }
        }

        if (str_contains($pattern, 'macros.')) {
            if (++$x < 30) {
                $pattern = self::applyGroupMacros($pattern, $x);
            } else {
                return false;
            }
        }
        return $pattern;
    }

    /**
     * Extract an array of tables in a pattern
     *
     * @param string $pattern
     * @return array
     */
    private function getTablesFromPattern($pattern)
    {
        preg_match_all('/[A-Za-z_]+(?=\.[A-Za-z_]+ )/', $pattern, $tables);
        if (is_null($tables)) {
            return [];
        }
        return array_keys(array_flip($tables[0])); // unique tables only
    }

    /**
     * Convert a v1 device group pattern to sql that can be ingested by jQuery-QueryBuilder
     *
     * @param $pattern
     * @return array
     */
    private function convertV1Pattern($pattern)
    {
        $pattern = rtrim($pattern, ' &&');
        $pattern = rtrim($pattern, ' ||');

        $ops = ['=', '!=', '<', '<=', '>', '>='];
        $parts = str_getcsv($pattern, ' '); // tokenize the pattern, respecting quoted parts
        $out = "";

        $count = count($parts);
        for ($i = 0; $i < $count; $i++) {
            $cur = $parts[$i];

            if (starts_with($cur, '%')) {
                // table and column or macro
                $out .= substr($cur, 1).' ';
            } elseif (substr($cur, -1) == '~') {
                // like operator
                $content = $parts[++$i]; // grab the content so we can format it

                if (starts_with($cur, '!')) {
                    // prepend NOT
                    $out .= 'NOT ';
                }

                $out .= "LIKE('".$this->convertRegexToLike($content)."') ";
            } elseif ($cur == '&&') {
                $out .= 'AND ';
            } elseif ($cur == '||') {
                $out .= 'OR ';
            } elseif (in_array($cur, $ops)) {
                // pass-through operators
                $out .= $cur.' ';
            } else {
                // user supplied input
                $out .= "'".trim($cur, '"\'')."' "; // TODO: remove trim, only needed with invalid input
            }
        }
        return rtrim($out);
    }

    /**
     * Convert sql regex to like, many common uses can be converted
     * Should only be used to convert v1 patterns
     *
     * @param $pattern
     * @return string
     */
    private function convertRegexToLike($pattern)
    {
        $startAnchor = starts_with($pattern, '^');
        $endAnchor = ends_with($pattern, '$');

        $pattern = trim($pattern, '^$');

        $wildcards = ['@', '.*'];
        if (str_contains($pattern, $wildcards)) {
            // contains wildcard
            $pattern = str_replace($wildcards, '%', $pattern);
        }

        // add ends appropriately
        if ($startAnchor && !$endAnchor) {
            $pattern .= '%';
        } elseif (!$startAnchor && $endAnchor) {
            $pattern = '%'.$pattern;
        }

        // if there are no wildcards, assume substring
        if (!str_contains($pattern, '%')) {
            $pattern = '%'.$pattern.'%';
        }

        return $pattern;
    }

    // ---- Accessors/Mutators ----

    /**
     * Returns an sql formatted string
     * Mostly, this is for ingestion by JQuery-QueryBuilder
     *
     * @return string
     */
    public function getPatternSqlAttribute()
    {
        $sql = $this->pattern;

        // fill in parameters
        foreach ((array)$this->params as $value) {
            if (!is_numeric($value) && !starts_with($value, "'")) {
                $value = "'".$value."'";
            }
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

    /**
     * Custom mutator for params attribute
     * Allows already encoded json to pass through
     *
     * @param array|string $params
     */
    public function setParamsAttribute($params)
    {
        if (!Util::isJson($params)) {
            $params = json_encode($params);
        }

        $this->attributes['params'] = $params;
    }

    /**
     * Check if the stored pattern is v1
     * Convert it to v2 for display
     * Currently, it will only be updated in the database if the user saves the rule in the ui
     *
     * @param $pattern
     * @return string
     */
    public function getPatternAttribute($pattern)
    {
        // If this is a v1 pattern, convert it to sql
        if (starts_with($pattern, '%')) {
            return $this->convertV1Pattern($pattern);
        }

        return $pattern;
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        if (!$this->isJoined($query, 'device_group_device')) {
            $query->join('device_group_device', 'device_group_device.device_group_id', 'device_groups.id');
        }

        return $this->hasDeviceAccess($query, $user, 'device_group_device');
    }

    // ---- Define Relationships ----

    public function rules()
    {
        return $this->belongsToMany('App\Models\AlertRule', 'alert_group_map', 'group_id', 'rule_id');
    }

    public function devices()
    {
        return $this->belongsToMany('App\Models\Device', 'device_group_device', 'device_group_id', 'device_id');
    }
}
