<?php

use LibreNMS\Config;

if (Config::get('enable_ports_adsl')) {
    //discover if any port has dsl data.
    $adsl_stats = snmpwalk_cache_oid($device, 'adslMibObjects', [], 'ADSL-LINE-MIB');
    $vdsl_stats = snmpwalk_cache_oid($device, 'xdsl2LineEntry', [], 'VDSL2-LINE-MIB');
    d_echo($adsl_stats);
    d_echo($vdsl_stats);

    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], ['type'=>'ports']);
    $components = $components[$device['device_id']];
    $adsl_id = false;
    $vdsl_id = false;

    foreach ($components as $tmp_component_id => $tmp_component) {
        if ($tmp_component['name'] == 'adsl-port') {
            $adsl_id = $tmp_component_id;
        }
        if ($tmp_component['name'] == 'vdsl-port') {
            $vdsl_id = $tmp_component_id;
        }
    }

    if (!empty($adsl_stats)) {
        // create a component (if not existing already) to keep track of it during polling and avoid polling non existing OIDs
        if (! $adsl_id) {
            $component_data = [
                'label' => 'adsl-port',
                'name'  => 'adsl-port',
            ];
            $new_component = $component->createComponent($device['device_id'], 'ports');
            $component_id = key($new_component);
            $components[$component_id] = array_merge($new_component[$component_id], $component_data);
        };
    } else {
        if ($adsl_id) {
            //remove it cause it is gone
            $component->deleteComponent($adsl_id);
        }
    }

    if (!empty($vdsl_stats)) {
        // create a component to keep track of it during polling and avoid polling non existing OIDs
        if (! $vdsl_id) {
            $component_data = [
                'label' => 'vdsl-port',
                'name'  => 'vdsl-port',
            ];
            $new_component = $component->createComponent($device['device_id'], 'ports');
            $component_id = key($new_component);
            $components[$component_id] = array_merge($new_component[$component_id], $component_data);
        };
    } else {
        if ($vdsl_id) {
            //remove it cause it is gone
            $component->deleteComponent($vdsl_id);
        }
    }
    //save components
    $component->setComponentPrefs($device['device_id'], $components);
} else {
    // remove any xdsl component to avoid polling
    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], ['type'=>'ports']);
    $components = $components[$device['device_id']];
    foreach ($components as $tmp_component_id => $tmp_component) {
        if ($tmp_component['name'] == 'adsl-port') {
            $component->deleteComponent($tmp_component_id);
        }
        if ($tmp_component['name'] == 'vdsl-port') {
            $component->deleteComponent($tmp_component_id);
        }
    }
    //save components
    $component->setComponentPrefs($device['device_id'], $components);
}

