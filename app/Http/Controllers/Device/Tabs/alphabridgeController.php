<?php

/**
 * LatencyController.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Smokeping;
use App\Models\PortVlan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Device\Tabs\PortsController;
use App\Models\Port;

class alphabridgeController implements DeviceTab
{

    protected $PortsController;

    public function __construct(PortsController $PortsController)
    {
        $this->PortsController = $PortsController;
    }
    // public function someMethod()
    // {
    //     $data = $this->someController->getData();
    //     return response()->json($data);
    // }

    public function configureVlan(Request $request)
    {
        
        $request->validate([
            'host' => 'nullable|string',
            'mode'=>'nullable|string',
            'vlan_id' => 'nullable',
            'interface' => 'nullable|string',
            'pvid' => 'nullable',
        ]);

        $device = $request->host;
        $mode = $request->mode;
        $vlanId = $request->vlan_id;
        $interface = $request->interface;
        $pvid = $request->pvid;

        // Define the Ansible command properly
        $process = new Process([
            'ansible-playbook',
            '-i', '/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml',
            '/opt/librenms/librenms-ansible-inventory-plugin/vlan_config.yml',
            '-e', "vlan_id=$vlanId mode=$mode interface=$interface pvid=$pvid","-vvv"
        ]);

        // Set timeout to prevent hanging
        $process->setTimeout(300); // 5 minutes

        try {
            $process->run();

            // Check if execution failed
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return back()->with('success', "VLAN $vlanId created on $interface pvid $pvid is applied for $device.");
        } catch (ProcessFailedException $e) {
            return back()->with('error', 'Failed to configure VLAN. ' . $e->getMessage());
        }
    }


    // public function runPlaybook(Request $request)
    // {
    //     $host = $request->input('host'); 
    //     $username = $request->input('username'); 
    //     $password = $request->input('password'); 
    //     $cmd = $request->input('cmd');  

    //     // Ansible command to run playbook dynamically
    //     $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/alphabridge1_config.yml \
    //                 --inventory=/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml \
    //                 -e 'ansible_host={$host} ansible_user={$username} ansible_password={$password} command_to_run=\"{$cmd}\"'";

    //     // Execute and get output
    //     $output = shell_exec($command);

    //     return response()->json(['output' => nl2br($output)]);
    // }


    // public function runPlaybook(Request $request)
    // {

    //     // $user = shell_exec('whoami');
    //     // return response()->json(['php_user' => trim($user)]);

    //     //working 
    //     $command="ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/alphabridge_config.yml -vvv";
    //     $output = shell_exec($command);
    //     return response()->json(['message' => 'Ansible playbook started.', 'output' => $output]);
    //     //close





    //     // $request->validate([
    //     //     'host'     => 'required|ip',
    //     //     'username' => 'required|string',
    //     //     'password' => 'required|string',
    //     //     'cmd'      => 'required|string',
    //     // ]);

    //     // $hostsFilePath = "/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml";
    //     // $playbookPath = "/opt/librenms/librenms-ansible-inventory-plugin/alphabridge_config.yml";

    //     // if (!File::exists($hostsFilePath) || !File::exists($playbookPath)) {
    //     //     return response()->json(['error' => 'Required files not found'], 404);
    //     // }

    //     // // Set correct permissions
    //     // // Set correct permissions (without world-writable issue)
    //     // shell_exec("sudo find /opt/librenms/librenms-ansible-inventory-plugin -type d -exec chmod 755 {} +");
    //     // shell_exec("sudo find /opt/librenms/librenms-ansible-inventory-plugin -type f -exec chmod 644 {} +");
    //     // shell_exec("sudo chown -R www-data:www-data /opt/librenms/librenms-ansible-inventory-plugin");

    //     // // Run Ansible Playbook
    //     // $process = new Process([
    //     //     'sudo',
    //     //     '-u',
    //     //     'www-data',
    //     //     'ansible-playbook',
    //     //     '-i',
    //     //     '/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml',
    //     //     '/opt/librenms/librenms-ansible-inventory-plugin/alphabridge_config.yml',
    //     //     '-vvv'
    //     // ]);

    //     // $process->setWorkingDirectory('/opt/librenms/librenms-ansible-inventory-plugin');
    //     // $process->run();

    //     // // Capture and log outputs
    //     // Log::error("STDOUT: " . $process->getOutput());
    //     // Log::error("STDERR: " . $process->getErrorOutput());

    //     // if (!$process->isSuccessful()) {
    //     //     return response()->json([
    //     //         'error' => 'Playbook execution failed!',
    //     //         'output' => $process->getErrorOutput() ?: 'No error details captured.',
    //     //     ], 500);
    //     // }

    //     // return response()->json([
    //     //     'success' => 'Playbook executed successfully!',
    //     //     'output' => $process->getOutput(),
    //     // ]);
    // }






















    // close

    public function submenu1()
    {
        return view('device.menu.submenu1');
    }

    public function submenu2()
    {
        return view('device.menu.submenu2');
    }

    public function submenu3()
    {
        return view('device.menu.submenu3');
    }

    public function visible(Device $device): bool
    {
        return $device->vlans()->exists();
    }

    public function slug(): string
    {
        return 'alphabridge';
    }

    public function icon(): string
    {
        return 'fa fa-audio-description';
    }

    public function name(): string
    {
        return __('Alphabridge');
    }

    public function data(Device $device, Request $request): array
    {
        
        
        $ports=Port::where('device_id',$device->device_id)->get();
        session(['hostname' => $device->hostname]);
        session(['tod' => $device->os]);
        return [
            'vlans' => self::getVlans($device),
            'hostname' => $device->hostname,
            'device' => $device,
            'ports' => $ports,
        ];
    }

    private static function getVlans(Device $device)
    {
        // port.device needed to prevent loading device multiple times
        $portVlan = PortVlan::where('ports_vlans.device_id', $device->device_id)
            ->join('vlans', function ($join) {
                $join
                    ->on('ports_vlans.vlan', 'vlans.vlan_vlan')
                    ->on('vlans.device_id', 'ports_vlans.device_id');
            })
            ->join('ports', function ($join) {
                $join
                    ->on('ports_vlans.port_id', 'ports.port_id');
            })
            ->with(['port.device'])
            ->select('ports_vlans.*', 'vlans.vlan_name')->orderBy('vlan_vlan')->orderBy('ports.ifName')->orderBy('ports.ifDescr')
            ->get()->sortBy(['vlan', 'port']);

        $data = $portVlan->groupBy('vlan');

        return $data;
    }
}
