<?php

/* Copyright (C) 2014 Nicolas Armando <nicearma@yahoo.com> and Mathieu Millet <htam-net@github.net>
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
* along with this program. If not, see <https://www.gnu.org/licenses/>. */

// This one only will work with the CISCO-CONTEXT-MAPPING-MIB V2 of cisco
use LibreNMS\Config;

if (Config::get('enable_vrf_lite_cisco')) {
    $ids = [];

    // For the moment only will be cisco and the version 3
    if ($device['os_group'] == 'cisco' && $device['snmpver'] == 'v3') {
        $mib = 'SNMP-COMMUNITY-MIB';
        $mib = 'CISCO-CONTEXT-MAPPING-MIB';
        //-Osq because if i put the n the oid from the first command is not the same of this one
        $listVrf = snmp_walk($device, 'cContextMappingVrfName', ['-Osq', '-Ln'], $mib, null);
        $listVrf = str_replace('cContextMappingVrfName.', '', $listVrf);
        $listVrf = str_replace('"', '', $listVrf);
        $listVrf = trim($listVrf);

        d_echo("\n[DEBUG]\nUsing $mib\n[/DEBUG]\n");
        d_echo("\n[DEBUG List Vrf only name]\n$listVrf\n[/DEBUG]\n");

        foreach (explode("\n", $listVrf) as $lineVrf) {
            $tmpVrf = explode(' ', $lineVrf, 2);
            //the $tmpVrf[0] will be the context
            if (count($tmpVrf) == 2 && ! empty($tmpVrf[1])) {
                $tableVrf[$tmpVrf[0]]['vrf_name'] = $tmpVrf[1];
            }
        }
        unset($listVrf);

        $listIntance = snmp_walk($device, 'cContextMappingProtoInstName', ['-Osq', '-Ln'], $mib, null);
        $listIntance = str_replace('cContextMappingProtoInstName.', '', $listIntance);
        $listIntance = str_replace('"', '', $listIntance);
        $listIntance = trim($listIntance);

        d_echo("\n[DEBUG]\nUsing $mib\n[/DEBUG]\n");
        d_echo("\n[DEBUG]\n List Intance only names\n$listIntance\n[/DEBUG]\n");

        foreach (explode("\n", $listIntance) as $lineIntance) {
            $tmpIntance = explode(' ', $lineIntance, 2);
            //the $tmpIntance[0] will be the context and $tmpIntance[1] the intance
            if (count($tmpIntance) == 2 && ! empty($tmpIntance[1])) {
                $tableVrf[$tmpIntance[0]]['intance_name'] = $tmpIntance[1];
            }
        }
        unset($listIntance);

        foreach ((array) $tableVrf as $context => $vrf) {
            if (\LibreNMS\Util\Debug::isEnabled()) {
                echo "\n[DEBUG]\nRelation:t" . $context . 't' . $vrf['intance'] . 't' . $vrf['vrf'] . "\n[/DEBUG]\n";
            }

            $tmpVrf = dbFetchRow('SELECT * FROM vrf_lite_cisco WHERE device_id = ? and context_name=?', [
                $device['device_id'],
                $context,
            ]);
            if (! empty($tmpVrf)) {
                $ids[$tmpVrf['vrf_lite_cisco_id']] = $tmpVrf['vrf_lite_cisco_id'];
                $vrfUpdate = [];

                foreach ($vrfUpdate as $key => $value) {
                    if ($vrf[$key] != $value) {
                        $vrfUpdate[$key] = $value;
                    }
                }
                if (! empty($vrfUpdate)) {
                    dbUpdate($vrfUpdate, 'vrf_lite_cisco', 'vrf_lite_cisco_id=?', [
                        $tmp['vrf_lite_cisco_id'],
                    ]);
                }
            } else {
                $id = dbInsert([
                    'device_id' => $device['device_id'],
                    'context_name' => $context,
                    'intance_name' => $vrf['intance_name'],
                    'vrf_name' => $vrf['vrf_name'],
                ], 'vrf_lite_cisco');
                $ids[$id] = $id;
            }
        }
        unset($tableVrf);
    }

    //get all vrf_lite_cisco, this will used where the value depend of the context, be careful with the order that you call this module, if the module is disabled the context search will not work
    $tmpVrfC = dbFetchRows('SELECT * FROM vrf_lite_cisco WHERE device_id = ? ', [
        $device['device_id'], ]);
    $device['vrf_lite_cisco'] = $tmpVrfC;

    //Delete all vrf that chaged
    foreach ($tmpVrfC as $vrfC) {
        unset($ids[$vrfC['vrf_lite_cisco_id']]);
    }
    if (! empty($ids)) {
        foreach ($ids as $id) {
            dbDelete('vrf_lite_cisco', 'vrf_lite_cisco_id = ? ', [$id]);
        }
    }
    unset($ids);
    unset($tmpVrfC);
} // enable_vrf_lite_cisco
