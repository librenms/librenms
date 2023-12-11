<?php
/*
 * VminfoLibvirt.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\PowerState;

trait VminfoLibvirt
{
    public function discoverVminfo(): Collection
    {
        echo 'LibVirt VM: ';

        if (! Config::get('enable_libvirt')) {
            echo 'not configured';

            return new Collection;
        }

        $vms = new Collection;

        $ssh_ok = 0;

        $userHostname = $this->getDevice()->hostname;
        if (Config::has('libvirt_username')) {
            $userHostname = Config::get('libvirt_username') . '@' . $userHostname;
        }

        foreach (Config::get('libvirt_protocols') as $method) {
            if (Str::contains($method, 'qemu')) {
                $uri = $method . '://' . $userHostname . '/system';
            } else {
                $uri = $method . '://' . $userHostname;
            }

            if (Str::contains($method, 'ssh') && ! $ssh_ok) {
                // Check if we are using SSH if we can log in without password - without blocking the discovery
                // Also automatically add the host key so discovery doesn't block on the yes/no question, and run echo so we don't get stuck in a remote shell ;-)
                exec('ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" ' . $userHostname . ' echo -e', $out, $ret);
                if ($ret != 255) {
                    $ssh_ok = 1;
                }
            }

            if ($ssh_ok || ! Str::contains($method, 'ssh')) {
                // Fetch virtual machine list
                unset($domlist);
                exec(Config::get('virsh') . ' -rc ' . $uri . ' list', $domlist);

                foreach ($domlist as $dom) {
                    [$dom_id] = explode(' ', trim($dom), 2);

                    if (is_numeric($dom_id)) {
                        // Fetch the Virtual Machine information.
                        unset($vm_info_array);
                        exec(Config::get('virsh') . ' -rc ' . $uri . ' dumpxml ' . $dom_id, $vm_info_array);

                        // Example xml:
                        // <domain type='kvm' id='3'>
                        // <name>moo.example.com</name>
                        // <uuid>48cf6378-6fd5-4610-0611-63dd4b31cfd6</uuid>
                        // <memory>1048576</memory>
                        // <currentMemory>1048576</currentMemory>
                        // <vcpu>8</vcpu>
                        // <os>
                        // <type arch='x86_64' machine='pc-0.12'>hvm</type>
                        // <boot dev='hd'/>
                        // </os>
                        // <features>
                        // <acpi/>
                        // (...)
                        // See spec at https://libvirt.org/formatdomain.html

                        // Convert array to string
                        $vm_info_xml = implode($vm_info_array);

                        $xml = simplexml_load_string('<?xml version="1.0"?> ' . $vm_info_xml);
                        Log::debug($xml);

                        // libvirt does not supply this
                        exec(Config::get('virsh') . ' -rc ' . $uri . ' domstate ' . $dom_id, $vm_state);
                        $vmwVmState = PowerState::STATES[strtolower($vm_state[0])] ?? PowerState::UNKNOWN;

                        $vmwVmMemSize = $xml->memory;
                        // Convert memory size to MiB
                        switch ($xml->memory['unit']) {
                            case 'T':
                            case 'TiB':
                                $vmwVmMemSize = $xml->memory * 1048576;
                                break;
                            case 'TB':
                                $vmwVmMemSize = $xml->memory * 1000000;
                                break;
                            case 'G':
                            case 'GiB':
                                $vmwVmMemSize = $xml->memory * 1024;
                                break;
                            case 'GB':
                                $vmwVmMemSize = $xml->memory * 1000;
                                break;
                            case 'M':
                            case 'MiB':
                                break;
                            case 'MB':
                                $vmwVmMemSize = $xml->memory * 1000000 / 1048576;
                                break;
                            case 'KB':
                                $vmwVmMemSize = $xml->memory / 1000;
                                break;
                            case 'b':
                            case 'bytes':
                                $vmwVmMemSize = $xml->memory / 1048576;
                                break;
                            default:
                                // KiB or k or no value
                                $vmwVmMemSize = $xml->memory / 1024;
                                break;
                        }

                        // Save the discovered Virtual Machine.
                        $vms->push(new \App\Models\Vminfo([
                            'vmtype' => 'libvirt',
                            'vmwVmVMID' => $dom_id,
                            'vmwVmState' => $vmwVmState,
                            'vmwVmGuestOS' => '',
                            'vmwVmDisplayName' => $xml->name,
                            'vmwVmMemSize' => $vmwVmMemSize,
                            'vmwVmCpus' => $xml->vcpu['current'] ?? $xml->vcpu,
                        ]));
                    }
                }
            }

            // If we found VMs, don't cycle the other protocols anymore.
            if ($vms->isNotEmpty()) {
                break;
            }
        }

        return $vms;
    }
}
