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

use App\Models\Device;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Models\Keyable;

trait SyncsModels
{
    /**
     * Sync several models for a device's relationship
     * Model must implement \LibreNMS\Interfaces\Models\Keyable interface
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parentModel
     * @param  string  $relationship
     * @param  \Illuminate\Support\Collection<Keyable>  $models  \LibreNMS\Interfaces\Models\Keyable
     * @return \Illuminate\Support\Collection
     */
    protected function syncModels($parentModel, $relationship, $models, $existing = null): Collection
    {
        $models = $models->keyBy->getCompositeKey();
        $existing = ($existing ?? $parentModel->$relationship)->groupBy->getCompositeKey();

        foreach ($existing as $exist_key => $existing_rows) {
            if ($models->offsetExists($exist_key)) {
                // update
                foreach ($existing_rows as $index => $existing_row) {
                    if ($index == 0) {
                        // fill attributes, ignoring mutators and fillable
                        $merged = array_merge($existing_row->getAttributes(), $models->get($exist_key)->getAttributes());
                        $existing_row->setRawAttributes($merged);
                        $existing_row->save();
                    } else {
                        // delete extra rows at this key
                        $existing_row->delete();
                        $existing_rows->forget($index);
                    }
                }
            } else {
                // delete
                $existing_rows->each->delete();
                $existing->forget($exist_key);
            }
        }

        $new = $models->diffKeys($existing);
        if (is_a($parentModel->$relationship(), HasManyThrough::class)) {
            // if this is a distant relation, the models need the intermediate relationship set
            // just save assuming things are correct
            $new->each->save();
        } else {
            $parentModel->$relationship()->saveMany($new);
        }

        return $existing->map->first()->merge($new);
    }

    /**
     * Sync a sub-group of models to the database
     *
     * @param  Collection<Keyable>  $models
     */
    public function syncModelsByGroup(Device $device, string $relationship, Collection $models, array $where): Collection
    {
        $filter = function ($models, $params) {
            foreach ($params as $key => $value) {
                $models = $models->where($key, '=', $value);
            }

            return $models;
        };

        return $this->syncModels($device, $relationship, $models->when($where, $filter), $device->$relationship->when($where, $filter));
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
