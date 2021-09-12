<?php
/**
 * SyncsModels.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\DB;

use Illuminate\Support\Collection;

trait SyncsModels
{
    /**
     * Sync several models for a device's relationship
     * Model must implement \LibreNMS\Interfaces\Models\Keyable interface
     *
     * @param  \App\Models\Device  $device
     * @param  string  $relationship
     * @param  \Illuminate\Support\Collection  $models  \LibreNMS\Interfaces\Models\Keyable
     * @return \Illuminate\Support\Collection
     */
    protected function syncModels($device, $relationship, $models): Collection
    {
        $models = $models->keyBy->getCompositeKey();
        $existing = $device->$relationship->keyBy->getCompositeKey();

        foreach ($existing as $exist_key => $exist_value) {
            if ($models->offsetExists($exist_key)) {
                // update or restore soft-deleted model
                if ($exist_value->trashed()) {
                    $exist_value->restore();
                }
                $exist_value->fill($models->get($exist_key)->getAttributes())->save();
            } else {
                // delete
                $exist_value->delete();
                $existing->forget($exist_key);
            }
        }

        $new = $models->diffKeys($existing);
        $device->$relationship()->saveMany($new);

        return $existing->merge($new);
    }

    /**
     * Combine a list of existing and potentially new models
     * If the model exists fill any new data from the new models
     *
     * @param  \Illuminate\Support\Collection  $existing  \LibreNMS\Interfaces\Models\Keyable
     * @param  \Illuminate\Support\Collection  $discovered  \LibreNMS\Interfaces\Models\Keyable
     * @return \Illuminate\Support\Collection
     */
    protected function fillNew(Collection $existing, Collection $discovered): Collection
    {
        $all = $existing->keyBy->getCompositeKey();
        foreach ($discovered as $new) {
            if ($found = $all->get($new->getCompositeKey())) {
                $found->fill($new->getAttributes());
            } else {
                $all->put($new->getCompositeKey(), $new);
            }
        }

        return $all;
    }
}
