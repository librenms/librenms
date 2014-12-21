<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2012, Adam Armstrong - http://www.observium.org
 *
 * @package alerting
 * @author Adam Armstrong <adama@memetic.org>
 *
 */


/**
 * Build a cache of default alert conditions
 *
 * @return array
*/

function cache_conditions_global() {
  $cache = array();
  foreach (dbFetchRows("SELECT * FROM `alert_conditions_global`") as $entry)
  {
    $cache[$entry['type']][$entry['subtype']][$entry['metric']][] = array('operator' => $entry['operator'], 'value' => $entry['value'],
                                                                          'severity' => $entry['severity'], 'alerter' => $entry['alerter'], 'enable' => $entry['enable']);
  }
  return $cache;
}

/**
 * Build a cache of device-specific alert conditions
 *
 * @return array
 * @param device_id
*/

function cache_conditions_device($device_id) {

  $cache = array();
  foreach (dbFetchRows("SELECT * FROM `alert_conditions` WHERE `device_id` = ?", array($device_id)) as $entry)
  {
    $cache[$entry['type']][$entry['subtype']][$entry['entity']][$entry['metric']][] = array('condition' => $entry['operator'], 'value' => $entry['value'],
                                                                                            'severity' => $entry['severity'], 'alerter' => $entry['alerter'],
                                                                                            'enable' => $entry['enable']);
  }
  return $cache;
}

/**
 * Compare two values
 *
 * @return integer
 * @param value_a
 * @param condition
 * @param value_b
*/

function test_condition($value_a, $condition, $value_b)
{
      switch($condition)
      {
        case ">":
         if($value_a > $value_b) { $alert = 1; } else { $alert = 0; }
         break;
        case "<":
         if($value_a < $value_b) { $alert = 1; } else { $alert = 0; }
         break;
        case "!=":
         if($value_a != $value_b) { $alert = 1; } else { $alert = 0; }
         break;
        case "=":
         if($value_a = $value_b) { $alert = 1; } else { $alert = 0; }
         break;
        default:
         $alert = -1;
         break;
      }
      return $alert;
}

/**
 * Check entity data against alert conditions
 *
 * @param value_a
 * @param condition
 * @param value_b
*/

function check_entity($device, $entity_type, $entity_id, $data)
{
  global $glo_conditions;
  global $dev_conditions;

  if(!empty($entity_id)) { echo(" $entity_id"); }

  foreach($data as $name => $value)
  {
    foreach($dev_conditions[$entity_type][$entity_id][$name] as $condition)
    {
      $alert = test_condition($value, $condition['condition'], $condition['value']);
      if($alert == 1)
      {
        echo("ALERT ");
      } else {
        echo("OK ");
      }
    }
  }
}



?>
