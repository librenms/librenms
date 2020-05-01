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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2015 Aaron Daniels <aaron@daniels.id.au>
 * @author     Aaron Daniels <aaron@daniels.id.au>
 */

namespace LibreNMS;

use App\Models\ComponentPref;
use App\Models\ComponentStatusLog;

class Component
{
    /*
     * These fields are used in the component table. They are returned in the array
     * so that they can be modified but they can not be set as user attributes. We
     * also set their default values.
     */
    private $reserved = array(
        'type'      => '',
        'label'     => '',
        'status'    => 0,
        'ignore'    => 0,
        'disabled'  => 0,
        'error'     => '',
    );

    public function getComponentCount($device_id = null)
    {
        $counts =  \App\Models\Component::query()->when($device_id, function ($query, $device_id) {
            $query->where('device_id', $device_id);
        })->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type');

        return $counts->isEmpty() ? false : $counts->all();
    }

    public function getComponentType($TYPE = null)
    {
        if (is_null($TYPE)) {
            $SQL = "SELECT DISTINCT `type` as `name` FROM `component` ORDER BY `name`";
            $row = dbFetchRow($SQL, array());
        } else {
            $SQL = "SELECT DISTINCT `type` as `name` FROM `component` WHERE `type` = ? ORDER BY `name`";
            $row = dbFetchRow($SQL, array($TYPE));
        }

        if (!isset($row)) {
            // We didn't find any component types
            return false;
        } else {
            // We found some..
            return $row;
        }
    }

    public function getComponents($device_id = null, $options = array())
    {
        $query = \App\Models\Component::query()
            ->with('prefs');

        // Device_id is shorthand for filter C.device_id = $device_id.
        if (!is_null($device_id)) {
            $options['filter']['device_id'] = array('=', $device_id);
        }

        // Type is shorthand for filter type = $type.
        if (isset($options['type'])) {
            $options['filter']['type'] = array('=', $options['type']);
        }

        $validFields = ['device_id','type','id','label','status','disabled','ignore','error'];

        // filter   field => array(operator,value)
        //          Filters results based on the field, operator and value
        foreach (array_intersect_key($options['filter'], array_flip($validFields)) as $field => $filter) {
            $op = $filter[0];
            $value = $op == 'LIKE' ? "%{$filter[1]}%" : $filter[1] ;
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
        $sql_query = "SELECT status, count(status) as count FROM component WHERE";
        $sql_param = array();
        $add = 0;

        if (!is_null($device)) {
            // Add a device filter to the SQL query.
            $sql_query .= " `device_id` = ?";
            $sql_param[] = $device;
            $add++;
        }

        if ($add == 0) {
            // No filters, remove " WHERE" -6
            $sql_query = substr($sql_query, 0, strlen($sql_query)-6);
        }
        $sql_query .= " GROUP BY status";
        d_echo("SQL Query: ".$sql_query);

        // $service is not null, get only what we want.
        $result = dbFetchRows($sql_query, $sql_param);

        // Set our defaults to 0
        $count = array(0 => 0, 1 => 0, 2 => 0);
        // Rebuild the array in a more convenient method
        foreach ($result as $v) {
            $count[$v['status']] = $v['count'];
        }

        d_echo("Component Count by Status: ".print_r($count, true)."\n");
        return $count;
    }

    public function getComponentStatusLog($component_id, $start, $end)
    {
        if (($component_id == null) || ($start == null) || ($end == null)) {
            // Error...
            d_echo("Required arguments are missing. Component ID: ".$component_id.", Start: ".$start.", End: ".$end."\n");
            return false;
        }

        // Create our return array.
        $return = array();

        // 1. find the previous value, this is the value when $start occurred.
        $sql_query = "SELECT status FROM `component_statuslog` WHERE `component_id` = ? AND `timestamp` < ? ORDER BY `id` desc LIMIT 1";
        $sql_param = array($component_id, $start);
        $result = dbFetchRow($sql_query, $sql_param);
        if ($result == false) {
            $return['initial'] = false;
        } else {
            $return['initial'] = $result['status'];
        }

        // 2. Then we just need a list of all the entries for the time period.
        $sql_query = "SELECT status, `timestamp`, message FROM `component_statuslog` WHERE `component_id` = ? AND `timestamp` >= ? AND `timestamp` < ? ORDER BY `timestamp`";
        $sql_param = array($component_id, $start,$end);
        $return['data'] = dbFetchRows($sql_query, $sql_param);

        d_echo("Status Log Data: ".print_r($return, true)."\n");
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
            \Log::debug("Failed to create component status log");
        }

        return 0;
    }

    public function deleteComponent($id)
    {
        // Delete a component from the database.
        return \App\Models\Component::destroy($id);
    }

    public function setComponentPrefs($device_id, $ARRAY)
    {
        // Compare the arrays. Update/Insert where necessary.

        $OLD = $this->getComponents($device_id);
        // Loop over each component.
        foreach ((array)$ARRAY as $COMPONENT => $AVP) {
            // Make sure the component already exists.
            if (!isset($OLD[$device_id][$COMPONENT])) {
                // Error. Component doesn't exist in the database.
                continue;
            }

            // Ignore type, we cant change that.
            unset($AVP['type'], $OLD[$device_id][$COMPONENT]['type']);

            // If the Status has changed we need to add a log entry
            if ($AVP['status'] != $OLD[$device_id][$COMPONENT]['status']) {
                d_echo("Status Changed - Old: ".$OLD[$device_id][$COMPONENT]['status'].", New: ".$AVP['status']."\n");
                $this->createStatusLogEntry($COMPONENT['id'], $AVP['status'], $AVP['error']);
            }

            // Process our reserved components first.
            $UPDATE = array();
            foreach ($this->reserved as $k => $v) {
                // does the reserved field exist, if not skip.
                if (array_key_exists($k, $AVP)) {
                    // Has the value changed?
                    if ($AVP[$k] != $OLD[$device_id][$COMPONENT][$k]) {
                        // The value has been modified, add it to our update array.
                        $UPDATE[$k] = $AVP[$k];
                    }

                    // Unset the reserved field. We don't want to insert it below.
                    unset($AVP[$k], $OLD[$device_id][$COMPONENT][$k]);
                }
            }

            // Has anything changed, do we need to update?
            if (count($UPDATE) > 0) {
                // We have data to update
                \App\Models\Component::where('id', $COMPONENT)->update($UPDATE);

                // Log the update to the Eventlog.
                $MSG = "Component ".$COMPONENT." has been modified: ";
                foreach ($UPDATE as $k => $v) {
                    $MSG .= $k." => ".$v.",";
                }
                $MSG = substr($MSG, 0, -1);
                log_event($MSG, $device_id, 'component', 3, $COMPONENT);
            }

            // Process our AVP Adds and Updates
            foreach ($AVP as $ATTR => $VALUE) {
                // We have our AVP, lets see if we need to do anything with it.

                if (!isset($OLD[$device_id][$COMPONENT][$ATTR])) {
                    // We have a newly added attribute, need to insert into the DB
                    $DATA = array('component'=>$COMPONENT, 'attribute'=>$ATTR, 'value'=>$VALUE);
                    ComponentPref::create($DATA);

                    // Log the addition to the Eventlog.
                    log_event("Component: " . $ARRAY[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $ATTR . ", was added with value: " . $VALUE, $device_id, 'component', 3, $COMPONENT);
                } elseif ($OLD[$device_id][$COMPONENT][$ATTR] != $VALUE) {
                    // Attribute exists but the value is different, need to update
                    $DATA = array('value'=>$VALUE);
                    ComponentPref::where(['component' => $COMPONENT, 'attribute' => $ATTR])->update($DATA);

                    // Add the modification to the Eventlog.
                    log_event("Component: " . $AVP[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $ATTR . ", was modified from: " . $OLD[$device_id][$COMPONENT][$ATTR] . ", to: " . $VALUE, $device_id, 'component', 3, $COMPONENT);
                }
            } // End Foreach AVP

            // Process our Deletes.
            $DELETE = array_diff_key($OLD[$device_id][$COMPONENT], $AVP);
            foreach ($DELETE as $KEY => $VALUE) {
                // As the Attribute has been removed from the array, we should remove it from the database.
                ComponentPref::where(['component' => $COMPONENT, 'attribute' => $KEY])->delete();

                // Log the addition to the Eventlog.
                log_event("Component: " . $AVP[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $KEY . ", was deleted.", 4, $COMPONENT);
            }
        }

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
        if (!is_null($device_id) && isset($component_array[$device_id])) {
            $component_array = $component_array[$device_id];
        }

        foreach ($component_array as $id => $array) {
            return $id;
        }
        return -1;
    }
}
