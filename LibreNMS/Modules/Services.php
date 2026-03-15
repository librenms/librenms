<?php

/**
 * Services.php
 *
 * Discover services exposed by devices.
 *
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Modules;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\ServiceTemplateController;
use App\Models\Device;
use App\Models\Service;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Services as ServicesHelper;
use SnmpQuery;

class Services implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        $device = $os->getDevice();

        if (! $status->isEnabledAndDeviceUp($device)) {
            return false;
        }

        // Run discovery when either templates or service autodiscovery are enabled.
        return LibrenmsConfig::get('discover_services_templates')
            || (LibrenmsConfig::get('discover_services') && $device->type === 'server');
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDeviceArray();

        if (LibrenmsConfig::get('discover_services_templates')) {
            (new ServiceTemplateController())->applyDeviceAll($device['device_id']);
        }

        if (! LibrenmsConfig::get('discover_services')) {
            return;
        }

        if (($device['type'] ?? null) !== 'server') {
            return;
        }

        // FIXME: use /etc/services?
        $known_services = [
            22 => 'ssh',
            25 => 'smtp',
            53 => 'dns',
            80 => 'http',
            110 => 'pop',
            143 => 'imap',
        ];

        // Only lookup services listening on 0.0.0.0
        SnmpQuery::enumStrings()->hideMib()->walk('TCP-MIB::tcpConnState.0.0.0.0')->mapTable(function ($tcpConnState, $tcpConnLocalAddress, $tcpConnLocalPort, $tcpConnRemAddress, $tcpConnRemPort) use ($device, $known_services) {
            if (empty($tcpConnState['tcpConnState']) || $tcpConnState['tcpConnState'] != 'listen') {
                return null;
            }

            if ($tcpConnLocalPort !== null && isset($known_services[$tcpConnLocalPort])) {
                ServicesHelper::discover($device, $known_services[$tcpConnLocalPort]);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        // Services currently only support discovery, not polling.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, \LibreNMS\Interfaces\Data\DataStorageInterface $datastore): void
    {
        // no polling
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return Service::query()->where('device_id', $device->device_id)->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        // no automated cleanup, services are managed elsewhere
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return null; // no database dump support for services module
    }
}
