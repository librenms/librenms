<?php

/*
 * VminfoXcpNg.php
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
use SnmpQuery;

trait VminfoXcpNg
{
    public function discoverVmInfo(): Collection
    {
        Log::info('XCP-ng VM: ');

        /*
         * Fetch the Virtual Machine information.
         *
         *  XCP-NG-VMINFO-MIB::xcpNgVmDisplayName.1 = STRING: Control domain on host: lab01
         *  XCP-NG-VMINFO-MIB::xcpNgVmDisplayName.2 = STRING: ubuntu-24.04-test
         *  XCP-NG-VMINFO-MIB::xcpNgVmGuestOS.2 = STRING: name: Ubuntu 24.04.4 LTS; uname: 6.8.0-101-generic; distro: ubuntu; major: 24; minor: 04
         *  XCP-NG-VMINFO-MIB::xcpNgVmMemSize.1 = INTEGER: 1840 megabytes
         *  XCP-NG-VMINFO-MIB::xcpNgVmMemSize.2 = INTEGER: 2048 megabytes
         *  XCP-NG-VMINFO-MIB::xcpNgVmState.1 = INTEGER: running(1)
         *  XCP-NG-VMINFO-MIB::xcpNgVmState.2 = INTEGER: running(1)
         *  XCP-NG-VMINFO-MIB::xcpNgVmVMID.1 = STRING: 17a0d811-9c79-451e-a7a5-02e3526e12d8
         *  XCP-NG-VMINFO-MIB::xcpNgVmVMID.2 = STRING: 12bde200-e58f-9ca0-af4a-dc033b5dd03c
         *  XCP-NG-VMINFO-MIB::xcpNgVmCpus.1 = INTEGER: 4
         *  XCP-NG-VMINFO-MIB::xcpNgVmCpus.2 = INTEGER: 1
         */

        $vm_info = SnmpQuery::mibDir('librenms')->hideMib()->walk('XCP-NG-VMINFO-MIB::xcpNgVmTable');

        return $vm_info->mapTable(function ($data, $index) {
            if (str_starts_with((string) ($data['xcpNgVmDisplayName'] ?? ''), 'Control domain on host: ')) {
                return null;
            }

            $state = match ((int) $data['xcpNgVmState']) {
                1 => PowerState::ON,
                2, 5 => PowerState::OFF,
                3, 4 => PowerState::SUSPENDED,
                default => PowerState::UNKNOWN,
            };

            return new Vminfo([
                'vm_type' => 'xcp-ng',
                'vmwVmVMID' => $data['xcpNgVmVMID'] ?? (string) $index,
                'vmwVmDisplayName' => $data['xcpNgVmDisplayName'] ?? null,
                'vmwVmGuestOS' => $data['xcpNgVmGuestOS'] ?? null,
                'vmwVmMemSize' => $data['xcpNgVmMemSize'] ?? null,
                'vmwVmCpus' => $data['xcpNgVmCpus'] ?? null,
                'vmwVmState' => $state,
            ]);
        })->filter();
    }
}
