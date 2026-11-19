<?php

/*
 * LibreNMS module to capture statistics from the CISCO-NTP-MIB
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

namespace LibreNMS\Applications;

use App\Models\Application;
use Illuminate\Http\Request;
use LibreNMS\Component;

class Ntp extends ApplicationDefinition
{
    public const string TYPE = 'ntp';
    protected const ?string NAME = 'NTP';

    private const array PEER_GRAPHS = [
        'stratum' => 'Stratum',
        'offset' => 'Offset',
        'delay' => 'Delay',
        'dispersion' => 'Dispersion',
    ];

    private const array PEER_VIEWS = [
        'all' => 'All',
        'error' => 'Error',
    ];

    public static function peers(?int $deviceId = null): array
    {
        return (new Component)->getComponents($deviceId, [
            'type' => self::TYPE,
            'filter' => ['ignore' => ['=', 0]],
        ]);
    }

    public static function peerGraphType(string $key): ?string
    {
        return isset(self::PEER_GRAPHS[$key]) ? 'device_ntp_' . $key : null;
    }

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $page->view = 'apps.ntp-peers';
        $page->viewData = ['peers' => self::peers($application->device_id)[$application->device_id] ?? []];

        foreach (self::PEER_GRAPHS as $key => $title) {
            $page->addGraph($key === 'stratum' ? 'NTP Stratum' : $title, self::peerGraphType($key), ['device' => $application->device_id], $key);
        }
    }

    public function fleetView(): string
    {
        return 'apps.ntp-fleet';
    }

    public function fleetData(Request $request): array
    {
        $peerView = $request->query('view', 'all');
        $peerGraph = $request->query('graph', 'none');
        $graphs = ['none' => 'No Graphs'] + self::PEER_GRAPHS;
        $peerView = isset(self::PEER_VIEWS[$peerView]) ? $peerView : 'all';
        $peerGraph = isset($graphs[$peerGraph]) ? $peerGraph : 'none';

        return [
            'peerView' => $peerView,
            'peerGraph' => $peerGraph,
            'peerViewOptions' => $this->fleetOptions('view', self::PEER_VIEWS, ['graph' => $peerGraph]),
            'peerGraphOptions' => $this->fleetOptions('graph', $graphs, ['view' => $peerView]),
        ];
    }

    private function fleetOptions(string $key, array $options, array $context): array
    {
        $links = [];
        foreach ($options as $value => $text) {
            $links[$value] = [
                'text' => $text,
                'link' => route('apps.index', ['app' => self::TYPE, $key => $value] + $context),
            ];
        }

        return $links;
    }
}
