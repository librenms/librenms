<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

## Very basic parser to parse classic Observium-type schemes.
## Parser should populate $port_ifAlias array with type, descr, circuit, speed and notes

unset ($port_ifAlias);

echo($this_port['ifAlias']);

list($type,$descr) = preg_split("/[\:\[\]\{\}\(\)]/", $this_port['ifAlias']);
list(,$circuit) = preg_split("/[\{\}]/", $this_port['ifAlias']);
list(,$notes) = preg_split("/[\(\)]/", $this_port['ifAlias']);
list(,$speed) = preg_split("/[\[\]]/", $this_port['ifAlias']);
$descr = trim($descr);

if ($type && $descr)
{
  $type = strtolower($type);
  $port_ifAlias['type']  = $type;
  $port_ifAlias['descr'] = $descr;
  $port_ifAlias['circuit'] = $circuit;
  $port_ifAlias['speed'] = $speed;
  $port_ifAlias['notes'] = $notes;

  if ($debug)
  {
    print_r($port_ifAlias);
  }
}

unset ($port_type, $port_descr, $port_circuit, $port_notes, $port_speed);

?>
