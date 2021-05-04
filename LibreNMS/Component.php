<?php
/**
 * Component.php
 *
 * LibreNMS module to Interface with the Component System
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
 * @copyright  2015 Aaron Daniels <aaron@daniels.id.au>
 * @author     Aaron Daniels <aaron@daniels.id.au>
 */

namespace LibreNMS;

use App\Models\ComponentPref;
use App\Models\ComponentStatusLog;
use Illuminate\Support\Arr;
use Log;

class Component
{
    /*
     * These fields are used in the component table. They are returned in the array
     * so that they can be modified but they can not be set as user attributes. We
     * also set their default values.
     */
    private $reserved = [
        'type' => '',
        'label' => '',
        'status' => 0,
        'ignore' => 0,
        'disabled' => 0,
        'error' => '',
    ];

    public function getComponentCount($device_id = null)
    {
        $counts = \App\Models\Component::query()->when($device_id, function ($query, $device_id) {
            return $query->where('device_id', $device_id);
        })->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type');

        return $counts->isEmpty() ? false : $counts->all();
    }

    public function getComponentType($TYPE = null)
    {
        if (is_null($TYPE)) {
            $SQL = 'SELECT DISTINCT `type` as `name` FROM `component` ORDER BY `name`';
            $row = dbFetchRow($SQL, []);
        } else {
            $SQL = 'SELECT DISTINCT `type` as `name` FROM `component` WHERE `type` = ? ORDER BY `name`';
            $row = dbFetchRow($SQL, [$TYPE]);
        }

        if (! isset($row)) {
            // We didn't find any component types
            return false;
        } else {
            // We found some..
            return $row;
        }
    }

    public function getComponents($device_id = null, $options = [])
    {
        $query = \App\Models\Component::query()
            ->with('prefs');

        // Device_id is shorthand for filter C.device_id = $device_id.
        if (! is_null($device_id)) {
            $options['filter']['device_id'] = ['=', $device_id];
        }

        // Type is shorthand for filter type = $type.
        if (isset($options['type'])) {
            $options['filter']['type'] = ['=', $options['type']];
        }

        $validFields = ['device_id', 'type', 'id', 'label', 'status', 'disabled', 'ignore', 'error'];

        // filter   field => array(operator,value)
        //          Filters results based on the field, operator and value
        foreach (array_intersect_key($options['filter'], array_flip($validFields)) as $field => $filter) {
            $op = $filter[0];
            $value = $op == 'LIKE' ? "%{$filter[1]}%" : $filter[1];
            $query->where($field, $op, $value);
        }

        // sort     column direction
        //          Add SQL sorting to the results
        if (isset($options['sort'])) {
            [$column, $direction] = explode(' ', $options['sort']);
            $query->orderBy($column, $direction);
        }

        // limit    array(start,count)
        if (isset($options['limit'])) {
            $query->offset($options['limit'][0])->limit($options['limit'][1]);
        }

        // get and format results as expected by receivers
        return $query->get()->groupBy('device_id')->map(function ($group) {
            return $group->keyBy('id')->map(function ($component) {
                return $component->prefs->pluck('value', 'attribute')
                    ->merge($component->only(array_keys($this->reserved)));
            });
        })->toArray();
    }

    public function getComponentStatus($device = null)
    {
        $sql_query = 'SELECT status, count(status) as count FROM component WHERE';
        $sql_param = [];
        $add = 0;

        if (! is_null($device)) {
            // Add a device filter to the SQL query.
            $sql_query .= ' `device_id` = ?';
            $sql_param[] = $device;
            $add++;
        }

        if ($add == 0) {
            // No filters, remove " WHERE" -6
            $sql_query = substr($sql_query, 0, strlen($sql_query) - 6);
        }
        $sql_query .= ' GROUP BY status';
        d_echo('SQL Query: ' . $sql_query);

        // $service is not null, get only what we want.
        $result = dbFetchRows($sql_query, $sql_param);

        // Set our defaults to 0
        $count = [0 => 0, 1 => 0, 2 => 0];
        // Rebuild the array in a more convenient method
        foreach ($result as $v) {
            $count[$v['status']] = $v['count'];
        }

        d_echo('Component Count by Status: ' . print_r($count, true) . "\n");

        return $count;
    }

    public function getComponentStatusLog($component_id, $start, $end)
    {
        if (($component_id == null) || ($start == null) || ($end == null)) {
            // Error...
            d_echo('Required arguments are missing. Component ID: ' . $component_id . ', Start: ' . $start . ', End: ' . $end . "\n");

            return false;
        }

        // Create our return array.
        $return = [];

        // 1. find the previous value, this is the value when $start occurred.
        $sql_query = 'SELECT status FROM `component_statuslog` WHERE `component_id` = ? AND `timestamp` < ? ORDER BY `id` desc LIMIT 1';
        $sql_param = [$component_id, $start];
        $result = dbFetchRow($sql_query, $sql_param);
        if ($result == false) {
            $return['initial'] = false;
        } else {
            $return['initial'] = $result['status'];
        }

        // 2. Then we just need a list of all the entries for the time period.
        $sql_query = 'SELECT status, `timestamp`, message FROM `component_statuslog` WHERE `component_id` = ? AND `timestamp` >= ? AND `timestamp` < ? ORDER BY `timestamp`';
        $sql_param = [$component_id, $start, $end];
        $return['data'] = dbFetchRows($sql_query, $sql_param);

        d_echo('Status Log Data: ' . print_r($return, true) . "\n");

        return $return;
    }

    public function createComponent($device_id, $type)
    {
        $component = \App\Models\Component::create(['device_id' => $device_id, 'type' => $type]);

        // Add a default status log entry - we always start ok.
        $this->createStatusLogEntry($component->id, 0, 'Component Created');

        // Create a default component array based on what was inserted.
        return [$component->id => $component->only(array_keys($this->reserved))];
    }

    public function createStatusLogEntry($component_id, $status, $message)
    {
        try {
            return ComponentStatusLog::create(['component_id' => $component_id, 'status' => $status, 'message' => $message])->id;
        } catch (\Exception $e) {
            Log::debug('Failed to create component status log');
        }

        return 0;
    }

    public function deleteComponent($id)
    {
        // Delete a component from the database.
        return \App\Models\Component::destroy($id);
    }

    public function setComponentPrefs($device_id, $updated)
    {
        $updated = Arr::wrap($updated);
        \App\Models\Component::whereIn('id', array_keys($updated))
            ->with('prefs')
            ->get()
            ->each(function (\App\Models\Component $component) use ($device_id, $updated) {
                $update = $updated[$component->id];
                unset($update['type']);  // can't change type

                // update component attributes
                $component->fill($update);
                if ($component->isDirty()) {
                    // Log the update to the Eventlog.
                    $message = "Component $component->id has been modified: ";
                    $message .= collect($component->getDirty())->map(function ($value, $key) {
                        return "$key => $value";
                    })->implode(',');

                    // If the Status has changed we need to add a log entry
                    if ($component->isDirty('status')) {
                        Log::debug('Status Changed - Old: ' . $component->getOriginal('status') . ", New: $component->status\n");
                        $this->createStatusLogEntry($component->id, $component->status, $component->error);
                    }
                    $component->save();

                    Log::event($message, $component->device_id, 'component', 3, $component->id);
                }

                // update preferences
                $prefs = collect($updated[$component->id])->filter(function ($value, $attr) {
                    return ! array_key_exists($attr, $this->reserved);
                });

                $invalid = $component->prefs->keyBy('id');

                foreach ($prefs as $attribute => $value) {
                    $existing = $component->prefs->firstWhere('attribute', $attribute);
                    if ($existing) {
                        $invalid->forget($existing->id);
                        $existing->fill(['value' => $value]);
                        if ($existing->isDirty()) {
                            Log::event("Component: $component->type($component->id). Attribute: $attribute, was modified from: " . $existing->getOriginal('value') . ", to: $value", $device_id, 'component', 3, $component->id);
                            $existing->save();
                        }
                    } else {
                        $component->prefs()->save(new ComponentPref(['attribute' => $attribute, 'value' => $value]));
                        Log::event("Component: $component->type($component->id). Attribute: $attribute, was added with value: $value", $component->device_id, 'component', 3, $component->id);
                    }
                }

                foreach ($invalid as $pref) {
                    $pref->delete();
                    Log::event("Component: $component->type($component->id). Attribute: $pref->attribute, was deleted.", $component->device_id, 'component', 4);
                }
            });

        return true;
    }

    /**
     * Get the component id for the first component in the array
     * Only set $device_id if using the array from getCompenents(), which is keyed by device_id
     *
     * @param array $component_array
     * @param int $device_id
     * @return int the component id
     */
    public function getFirstComponentID($component_array, $device_id = null)
    {
        if (! is_null($device_id) && isset($component_array[$device_id])) {
            $component_array = $component_array[$device_id];
        }

        foreach ($component_array as $id => $array) {
            return $id;
        }

        return -1;
    }
}
