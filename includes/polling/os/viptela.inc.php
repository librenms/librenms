<?php

// from https://github.com/neokjames

$version = snmp_walk($device, ".1.3.6.1.4.1.41916.11.1.2", "-OQv");
$serial = snmp_walk($device, ".1.3.6.1.4.1.41916.3.1.1.1.5.1.0", "-OQv");

$hardware_switch = snmp_walk($device, ".1.3.6.1.4.1.41916.11.1.47", "-OQv");
switch ($hardware_switch) {
    case 1:
        $hardware = "Viptela vSmart Controller";
        break;
    case 2:
        $hardware = "Viptela vManage NMS";
        break;
    case 3:
        $hardware = "Viptela vBond Orchestrator";
        break;
    case 4:
        $hardware = "Viptela vEdge-1000";
        break;
    case 5:
        $hardware = "Viptela vEdge-2000";
        break;
    case 6:
        $hardware = "Viptela vEdge-100";
        break;
    case 7:
        $hardware = "Viptela vEdge-100-W2";
        break;
    case 8:
        $hardware = "Viptela vEdge-100-WM";
        break;
    case 9:
        $hardware = "Viptela vEdge-100-M2";
        break;
    case 10:
        $hardware = "Viptela vEdge-100-M";
        break;
    case 11:
        $hardware = "Viptela vEdge-100-B";
        break;
    case 12:
        $hardware = "Viptela vEdge Cloud";
        break;
    case 13:
        $hardware = "Viptela vContainer";
        break;
    case 14:
        $hardware = "Viptela vEdge-5000";
        break;
}
