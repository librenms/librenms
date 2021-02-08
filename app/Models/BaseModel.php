<?php
/**
 * BaseModel.php
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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * Check if query is already joined with a table
     *
     * @param Builder $query
     * @param string $table
     * @return bool
     */
    public static function isJoined($query, $table)
    {
        $joins = $query->getQuery()->joins;
        if ($joins == null) {
            return false;
        }
        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function to determine if user has access based on device permissions
     *
     * @param Builder $query
     * @param User $user
     * @param string $table
     * @return Builder
     */
    protected function hasDeviceAccess($query, User $user, $table = null)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        if (is_null($table)) {
            $table = $this->getTable();
        }

        return $query->whereIn("$table.device_id", \Permissions::devicesForUser($user));
    }

    /**
     * Helper function to determine if user has access based on port permissions
     *
     * @param Builder $query
     * @param User $user
     * @param string $table
     * @return Builder
     */
    protected function hasPortAccess($query, User $user, $table = null)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        if (is_null($table)) {
            $table = $this->getTable();
        }

        return $query->where(function ($query) use ($table, $user) {
            return $query->whereIn("$table.port_id", \Permissions::portsForUser($user))
                ->orWhereIn("$table.device_id", \Permissions::devicesForUser($user));
        });
    }
}
