<?php
/*
 * LibreNMS module to Interface with the Component System
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

class component {
    /*
     * These fields are used in the component table. They are returned in the array
     * so that they can be modified but they can not be set as user attributes. We
     * also set their default values.
     */
    private $reserved = array(
        'type'      => '',
        'label'     => '',
        'status'    => 1,
        'ignore'    => 0,
        'disabled'  => 0,
        'error'     => '',
    );

    public function getComponentType($TYPE=null) {
        if (is_null($TYPE)) {
            $SQL = "SELECT DISTINCT `type` as `name` FROM `component` ORDER BY `name`";
            $row = dbFetchRow($SQL, array());
        }
        else {
            $SQL = "SELECT DISTINCT `type` as `name` FROM `component` WHERE `type` = ? ORDER BY `name`";
            $row = dbFetchRow($SQL, array($TYPE));
        }

        if (!isset($row)) {
            // We didn't find any component types
            return false;
        }
        else {
            // We found some..
            return $row;
        }
    }

    public function getComponents($device_id=null,$options=array()) {
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
            $SQL .= " ( ";
            foreach ($options['filter'] as $field => $array) {
                if ($array[0] == 'LIKE') {
                    $SQL .= "`".$field."` LIKE ? AND ";
                    $array[1] = "%".$array[1]."%";
                }
                else {
                    // Equals operator is the default
                    $SQL .= "`".$field."` = ? AND ";
                }
                array_push($PARAM,$array[1]);
            }
            // Strip the last " AND " before closing the bracket.
            $SQL = substr($SQL,0,-5)." )";
        }

        if ($COUNT == 0) {
            // Strip the " WHERE " that we didn't use.
            $SQL = substr($SQL,0,-7);
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
            foreach ($this->reserved as $k => $v) {
                $RESULT[$COMPONENT['device_id']][$COMPONENT['id']][$k] = $COMPONENT[$k];
            }

            // Sort each component array so the attributes are in order.
            ksort($RESULT[$RESULT[$COMPONENT['device_id']][$COMPONENT['id']]]);
            ksort($RESULT[$RESULT[$COMPONENT['device_id']]]);
        }

        // limit    array(start,count)
        if (isset($options['limit'])) {
            $TEMP = array();
            $COUNT = 0;
            // k = device_id, v = array of components for that device_id
            foreach ($RESULT as $k => $v) {
                // k1 = component id, v1 = component array
                foreach ($v as $k1 => $v1) {
                    if ( ($COUNT >= $options['limit'][0]) && ($COUNT < $options['limit'][0]+$options['limit'][1])) {
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

    public function createComponent ($device_id,$TYPE) {
        // Prepare our default values to be inserted.
        $DATA = $this->reserved;

        // Add the device_id and type
        $DATA['device_id']  = $device_id;
        $DATA['type']       = $TYPE;

        // Insert a new component into the database.
        $id = dbInsert($DATA, 'component');

        // Create a default component array based on what was inserted.
        $ARRAY[$id] = $DATA;
        unset ($ARRAY[$id]['device_id']);     // This doesn't belong here.
        return $ARRAY;
    }

    public function deleteComponent ($id) {
        // Delete a component from the database.
        return dbDelete('component', "`id` = ?",array($id));
    }

    public function setComponentPrefs ($device_id,$ARRAY) {
        // Compare the arrays. Update/Insert where necessary.

        $OLD = $this->getComponents($device_id);
        // Loop over each component.
        foreach ($ARRAY as $COMPONENT => $AVP) {

            // Make sure the component already exists.
            if (!isset($OLD[$device_id][$COMPONENT])) {
                // Error. Component doesn't exist in the database.
                continue;
            }

            // Ignore type, we cant change that.
            unset($AVP['type'],$OLD[$device_id][$COMPONENT]['type']);

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
                    unset($AVP[$k],$OLD[$device_id][$COMPONENT][$k]);
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
                $MSG = substr($MSG,0,-1);
                log_event($MSG,$device_id,'component',$COMPONENT);
            }

            // Process our AVP Adds and Updates
            foreach ($AVP as $ATTR => $VALUE) {
                // We have our AVP, lets see if we need to do anything with it.

                if (!isset($OLD[$device_id][$COMPONENT][$ATTR])) {
                    // We have a newly added attribute, need to insert into the DB
                    $DATA = array('component'=>$COMPONENT, 'attribute'=>$ATTR, 'value'=>$VALUE);
                    $id = dbInsert($DATA, 'component_prefs');

                    // Log the addition to the Eventlog.
                    log_event ("Component: " . $AVP[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $ATTR . ", was added with value: " . $VALUE, $device_id, 'component', $COMPONENT);
                }
                elseif ($OLD[$device_id][$COMPONENT][$ATTR] != $VALUE) {
                    // Attribute exists but the value is different, need to update
                    $DATA = array('value'=>$VALUE);
                    dbUpdate($DATA, 'component_prefs', '`component` = ? AND `attribute` = ?', array($COMPONENT, $ATTR));

                    // Add the modification to the Eventlog.
                    log_event("Component: ".$AVP[$COMPONENT]['type']."(".$COMPONENT."). Attribute: ".$ATTR.", was modified from: ".$OLD[$COMPONENT][$ATTR].", to: ".$VALUE,$device_id,'component',$COMPONENT);
                }

            } // End Foreach COMPONENT

            // Process our Deletes.
            $DELETE = array_diff_key($OLD[$device_id][$COMPONENT], $AVP);
            foreach ($DELETE as $KEY => $VALUE) {
                // As the Attribute has been removed from the array, we should remove it from the database.
                dbDelete('component_prefs', "`component` = ? AND `attribute` = ?",array($COMPONENT,$KEY));

                // Log the addition to the Eventlog.
                log_event ("Component: " . $AVP[$COMPONENT]['type'] . "(" . $COMPONENT . "). Attribute: " . $ATTR . ", was deleted.", $COMPONENT);
            }

        }

        return true;
    }

}