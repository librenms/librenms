<?php

/**
 * V-SOL GPON OLT port description parser.
 *
 * Enriches ONU virtual ports (GPON01ONU1, etc.) with subscriber metadata
 * from the OLT's SNMP tables, making them appear on the Customers page.
 *
 * For non-ONU ports, falls back to the default parser behavior.
 *
 * Usage:
 *   lnms config:set port_descr_parser includes/port-descr-parser-vsolution.inc.php
 */

use App\Models\Port;
use App\Models\Transceiver;

return function (string $ifAlias, string $ifIndex = '', string $ifName = '', int $port_id = 0): array {
    static $deviceOsCache = [];
    static $transceiverCache = [];

    // Detect ONU virtual ports by ifName pattern: GPON01ONU1, GPON01ONU2, etc.
    if ($port_id && preg_match('/GPON\d{2}ONU\d+/', $ifName)) {
        if (! isset($deviceOsCache[$port_id])) {
            $port = Port::with('device')->find($port_id);
            $deviceOsCache[$port_id] = $port && $port->device && $port->device->os === 'vsolution';
        }
        if ($deviceOsCache[$port_id]) {
            if (! array_key_exists($port_id, $transceiverCache)) {
                $transceiverCache[$port_id] = Transceiver::where('port_id', $port_id)->first();
            }
            $transceiver = $transceiverCache[$port_id];

            $descr = $transceiver->serial ?? $ifAlias;
            $model = $transceiver->model ?? '';
            $vendor = $transceiver->vendor ?? '';
            $notes = $model ? "$vendor $model" : $ifAlias;

            return [
                'type' => 'cust',
                'descr' => $descr,
                'circuit' => $ifAlias,
                'speed' => '',
                'notes' => $notes,
            ];
        }
    }

    // Default parser fallback for all other ports
    $split = preg_split('/[:\[\]{}()]/', $ifAlias);
    $type = trim($split[0] ?? '');
    $descr = trim($split[1] ?? '');

    if ($type && $descr) {
        return [
            'type' => strtolower($type),
            'descr' => $descr,
            'circuit' => trim(preg_split('/[{}]/', $ifAlias)[1] ?? ''),
            'speed' => trim(preg_split('/[\[\]]/', $ifAlias)[1] ?? ''),
            'notes' => trim(preg_split('/[()]/', $ifAlias)[1] ?? ''),
        ];
    }

    return [];
};
