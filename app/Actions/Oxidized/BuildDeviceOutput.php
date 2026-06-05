<?php

namespace App\Actions\Oxidized;

use App\Facades\LibrenmsConfig;
use App\Models\Device;

class BuildDeviceOutput
{
    public function execute(Device $device): array
    {
        $output = [
            'hostname' => $device->hostname,
            'os' => $device->os,
            'ip' => $device->ip,
        ];

        $custom_ssh_port = $device->getAttrib('override_device_ssh_port');
        if (! empty($custom_ssh_port)) {
            $output['ssh_port'] = $custom_ssh_port;
        }

        $custom_telnet_port = $device->getAttrib('override_device_telnet_port');
        if (! empty($custom_telnet_port)) {
            $output['telnet_port'] = $custom_telnet_port;
        }

        // Pre-populate the group with the default
        if (
            LibrenmsConfig::get('oxidized.group_support') === true
            && ! empty(LibrenmsConfig::get('oxidized.default_group'))
        ) {
            $output['group'] = LibrenmsConfig::get('oxidized.default_group');
        }

        foreach (LibrenmsConfig::get('oxidized.maps', []) as $maps_column => $maps) {
            // Based on Oxidized group support we can apply groups by setting group_support to true
            if ($maps_column == 'group' && LibrenmsConfig::get('oxidized.group_support', true) !== true) {
                continue;
            }

            foreach ($maps as $field_type => $fields) {
                if ($field_type == 'sysname') {
                    // fix typo in previous code forcing users to use sysname instead of sysName
                    $value = $device->sysName;
                } elseif ($field_type == 'location') {
                    $value = $device->location?->location;
                } else {
                    $value = $device->$field_type;
                }

                foreach ($fields as $field) {
                    if (isset($field['regex']) && preg_match($field['regex'] . 'i', (string) $value)) {
                        // compatibility with old format
                        $output[$maps_column] = $field['value'] ?? $field[$maps_column];
                        break;
                    } elseif (isset($field['match']) && $field['match'] == $value) {
                        // compatibility with old format
                        $output[$maps_column] = $field['value'] ?? $field[$maps_column];
                        break;
                    }
                }
            }
        }

        return $output;
    }
}
