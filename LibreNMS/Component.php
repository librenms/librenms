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
        if (is_null($device_id)) {
            // SELECT type, count(*) as count FROM component GROUP BY type
            $SQL = "SELECT `type` as `name`, count(*) as count FROM `component` GROUP BY `type`";
            $rows = dbFetchRows($SQL, array());
        } else {
            $SQL = "SELECT `type` as `name`, count(*) as count FROM `component` WHERE `device_id` = ? GROUP BY `type`";
            $rows = dbFetchRows($SQL, array($device_id));
        }

        if (isset($rows)) {
            // We found some, lets re-process to make more accessible
            $result = array();
            foreach ($rows as $value) {
                $result[$value['name']] = $value['count'];
            }
            return $result;
        }
        // We didn't find any components
        return false;
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
        // Define our results array, this will be set even if no rows are returned.
        $RESULT = array();
        $PARAM = array();

        // Our base SQL Query, with no options.
        $SQL = "SELECT `C`.`id`,`C`.`device_id`,`C`.`type`,`C`.`label`,`C`.`status`,`C`.`disabled`,`C`.`ignore`,`C`.`error`,`CP`.`attribute`,`CP`.`value` FROM `component` as `C` LEFT JOIN `component_prefs` as `CP` on `C`.`id`=`CP`.`component` WHERE ";

        // Device_id is shorthand for filter C.device_id = $device_id.
        if (!is_null($device_id)) {
            $options['filter']['device_id'] = array('=', $device_id);
        }

        // Type is shorthand for filter type = $type.
        if (isset($options['type'])) {
            $options['filter']['type'] = array('=', $options['type']);
        }

        // filter   field => array(operator,value)
        //          Filters results based on the field, operator and value
        $COUNT = 0;
        if (isset($options['filter'])) {
            $COUNT++;
            $validFields = array('device_id','type','id','label','status','disabled','ignore','error');
            $SQL .= " ( ";
            foreach ($options['filter'] as $field => $array) {
                // Only add valid fields to the query
                if (in_array($field, $validFields)) {
                    if ($array[0] == 'LIKE') {
                        $SQL .= "`C`.`".$field."` LIKE ? AND ";
                        $array[1] = "%".$array[1]."%";
                    } else {
                        // Equals operator is the default
                        $SQL .= "`C`.`".$field."` = ? AND ";
                    }
                    array_push($PARAM, $array[1]);
                }
            }
            // Strip the last " AND " before closing the bracket.
            $SQL = substr($SQL, 0, -5)." )";
        }

        if ($COUNT == 0) {
            // Strip the " WHERE " that we didn't use.
            $SQL = substr($SQL, 0, -7);
        }

        // sort     column direction
        //          Add SQL sorting to the results
        if (isset($options['sort'])) {
            $SQL .= " ORDER BY ".$options['sort'];
        }

        // Get our component records using our built SQL.
        $COMPONENTS = dbFetchRows($SQL, $PARAM);

        // if we have no components we need to return nothing
        if (count($COMPONENTS) == 0) {
            return $RESULT;
        }

        // Add the AVP's to the array.
        foreach ($COMPONENTS as $COMPONENT) {
            if ($COMPONENT['attribute'] != "") {
                // if this component has attributes, set them in the array.
                $RESULT[$COMPONENT['device_id']][$COMPONENT['id']][$COMPONENT['attribute']] = $COMPONENT['value'];
            }
        }

        // Populate our reserved fields into the Array, these cant be used as user attributes.
        foreach ($COMPONENTS as $COMPONENT) {
            $component_device_id = (int)$COMPONENT['device_id'];
            foreach ($this->reserved as $k => $v) {
                $RESULT[$component_device_id][$COMPONENT['id']][$k] = $COMPONENT[$k];
            }

            // Sort each component array so the attributes are in order.
            if (is_array($RESULT[$RESULT[$component_device_id][$COMPONENT['id']]])) {
                ksort($RESULT[$RESULT[$component_device_id][$COMPONENT['id']]]);
            }
            if (is_array($RESULT[$RESULT[$component_device_id]])) {
                ksort($RESULT[$RESULT[$component_device_id]]);
            }
        }

        // limit    array(start,count)
        if (isset($options['limit'])) {
            $TEMP = array();
            $COUNT = 0;
            // k = device_id, v = array of components for that device_id
            foreach ($RESULT as $k => $v) {
                // k1 = component id, v1 = component array
                foreach ($v as $k1 => $v1) {
                    if (($COUNT >= $options['limit'][0]) && ($COUNT < $options['limit'][0]+$options['limit'][1])) {
                        $TEMP[$k][$k1] = $v1;
                    }
                    // We are counting components.
                    $COUNT++;
                }
            }
            $RESULT = $TEMP;
        }

        return $RESULT;
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

    public function createComponent($device_id, $TYPE)
    {
        // Prepare our default values to be inserted.
        $DATA = $this->reserved;

        // Add the device_id and type
        $DATA['device_id']  = $device_id;
        $DATA['type']       = $TYPE;

        // Insert a new component into the database.
        $id = dbInsert($DATA, 'component');

        // Add a default status log entry - we always start ok.
        $this->createStatusLogEntry($id, 0, 'Component Created');

        // Create a default component array based on what was inserted.
        $ARRAY = array();
        $ARRAY[$id] = $DATA;
        unset($ARRAY[$id]['device_id']);     // This doesn't belong here.
        return $ARRAY;
    }

    public function createStatusLogEntry($component_id, $status, $message)
    {
        // Add an entry to the statuslog table for a particular component.
        $DATA = array(
            'component_id'  => $component_id,
            'status'        => $status,
            'message'       => $message,
        );

        return dbInsert($DATA, 'component_statuslog');
    }

    public function deleteComponent($id)
    {
        // Delete a component from the database.
        return dbDelete('component', "`id` = ?", array($id));
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
                if (isset($AVP[$k])) {
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
                dbUpdate($UPDATE, 'component', '`id` = ?', array($COMPONENT));

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
                    dbInsert($DATA, 'component_prefs');

                    // Log the addition to the Eventlog.
                    log_event("Component: " . $AVP[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $ATTR . ", was added with value: " . $VALUE, $device_id, 'component', 3, $COMPONENT);
                } elseif ($OLD[$device_id][$COMPONENT][$ATTR] != $VALUE) {
                    // Attribute exists but the value is different, need to update
                    $DATA = array('value'=>$VALUE);
                    dbUpdate($DATA, 'component_prefs', '`component` = ? AND `attribute` = ?', array($COMPONENT, $ATTR));

                    // Add the modification to the Eventlog.
                    log_event("Component: " . $AVP[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $ATTR . ", was modified from: " . $OLD[$COMPONENT][$ATTR] . ", to: " . $VALUE, $device_id, 'component', 3, $COMPONENT);
                }
            } // End Foreach AVP

            // Process our Deletes.
            $DELETE = array_diff_key($OLD[$device_id][$COMPONENT], $AVP);
            foreach ($DELETE as $KEY => $VALUE) {
                // As the Attribute has been removed from the array, we should remove it from the database.
                dbDelete('component_prefs', "`component` = ? AND `attribute` = ?", array($COMPONENT,$KEY));

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
