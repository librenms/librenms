<?php

// Parser should populate $port_ifAlias array with type, descr, circuit, speed and notes
unset($port_ifAlias);

echo $this_port['ifAlias'];

[$type,$descr] = preg_split('/[\:\[\]\{\}\(\)]/', $this_port['ifAlias']);
[,$circuit] = preg_split('/[\{\}]/', $this_port['ifAlias']);
[,$notes] = preg_split('/[\(\)]/', $this_port['ifAlias']);
[,$speed] = preg_split('/[\[\]]/', $this_port['ifAlias']);
$descr = trim($descr);

if ($type && $descr) {
    $type = strtolower($type);
    $port_ifAlias['type'] = $type;
    $port_ifAlias['descr'] = $descr;
    $port_ifAlias['circuit'] = $circuit;
    $port_ifAlias['speed'] = substr($speed, 0, 32);
    $port_ifAlias['notes'] = $notes;

    d_echo($port_ifAlias);
}

unset($port_type, $port_descr, $port_circuit, $port_notes, $port_speed);
