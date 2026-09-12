<?php

/*
 * VminfoProxmox.php
 *
 * -Description-
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 */

namespace LibreNMS\OS\Traits;

use App\Models\Vminfo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PowerState;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\Exceptions\JsonAppWrongVersionException;
use LibreNMS\Util\Oid;
use SnmpQuery;

trait VminfoProxmox
{
    /**
     * @return Collection<int, Vminfo>
     */
    public function discoverVminfo(): Collection
    {
        Log::info('Proxmox VM: ');

        /*
         * Fetch the guest list from the proxmox extend script. The first line
         * holds the cluster name for the application module. The json follows.
         *
         * {
         *   "version": 2,
         *   "error": 0,
         *   "errorString": "",
         *   "data": {
         *     "cluster": "pve01",
         *     "node": "pve01",
         *     "vms": [
         *       {"vmid": 100, "name": "web01", "type": "qemu", "status": "running", "cpus": 4, "mem": 8589934592,
         *        "ports": [{"dev": "net0", "in": 4298988750, "out": 1207239278}]}
         *     ]
         *   }
         * }
         */

        $output = SnmpQuery::get('NET-SNMP-EXTEND-MIB::nsExtendOutputFull.' . Oid::encodeString('proxmox'))->value();

        if (empty($output)) {
            Log::debug('Proxmox: the proxmox extend returned nothing');

            return new Collection;
        }

        $lines = explode("\n", trim(str_replace('<<<app-proxmox>>>', '', $output)));
        array_shift($lines); // the cluster name
        $json = trim(implode("\n", $lines));

        if (! str_starts_with($json, '{')) {
            Log::info('Proxmox: the proxmox script is too old, update it to report the guests');

            return new Collection;
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new JsonAppParsingFailedException('vmwinfo: invalid json', $json);
        }

        if (! isset($data['version'], $data['error'], $data['errorString'], $data['data'])) {
            throw new JsonAppMissingKeysException('vmwinfo: missing one or more required keys', $json, $data);
        }

        if ($data['version'] < 2) {
            throw new JsonAppWrongVersionException('vmwinfo: version ' . $data['version'] . ' is too old', $json, $data);
        }

        if ($data['error']) {
            throw new JsonAppExtendErroredException('vmwinfo: ' . $data['errorString'], $json, $data);
        }

        $vms = [];

        foreach ($data['data']['vms'] ?? [] as $vm) {
            if (! is_array($vm) || ! isset($vm['vmid'])) {
                continue;
            }

            $vms[] = new Vminfo([
                'vm_type' => match ($vm['type'] ?? '') {
                    'lxc' => 'proxmox-lxc',
                    default => 'proxmox-qemu',
                },
                'vmwVmVMID' => (string) $vm['vmid'],
                'vmwVmDisplayName' => $vm['name'] ?? ('VM ' . $vm['vmid']),
                'vmwVmGuestOS' => ($vm['type'] ?? '') === 'lxc' ? 'LXC container' : 'QEMU guest',
                'vmwVmMemSize' => (int) round(($vm['mem'] ?? 0) / 1024 / 1024),
                'vmwVmCpus' => (int) ($vm['cpus'] ?? 0),
                'vmwVmState' => match ($vm['status'] ?? '') {
                    'running' => PowerState::ON,
                    'stopped' => PowerState::OFF,
                    'paused', 'suspended' => PowerState::SUSPENDED,
                    default => PowerState::UNKNOWN,
                },
            ]);
        }

        return new Collection($vms);
    }
}
