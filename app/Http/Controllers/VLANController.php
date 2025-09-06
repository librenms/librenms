<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class VLANController extends Controller
{
    public function showSubmenu()
    {
        // You can pass shared data here if needed for all tabs
        return view('submenu1-1');
    }

public function generateHostsYml($hostname)
{
    $yml = <<<YML
all:
  children:
    alphabridge_devices:
      hosts:
        bridge1:
          ansible_host: "$hostname"
          ansible_user: "admin"
          ansible_password: "admin"
          ansible_connection: local
          become: true
YML;

    file_put_contents('/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml', $yml);
}



public function vlanConfigTab()
{
    // dd(get_current_user());


    $hostname = session('hostname');
    $tod=session('tod');
    // dd($tod);
    $this->generateHostsYml($hostname);

    $playbookPath = '/opt/librenms/librenms-ansible-inventory-plugin/vlan_con.yml';
    $inventoryPath = '/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml';

    $cmd = "ansible-playbook -i {$inventoryPath} {$playbookPath}";
    $output = shell_exec($cmd);

    preg_match('/"output\.stdout":\s*"([^"]+)"/', $output, $matches);
    $vlanOutput = isset($matches[1]) ? stripcslashes($matches[1]) : "";

    $lines = preg_split("/\r\n|\n|\r/", $vlanOutput);
    $vlanRows = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^\d+\s+/', $line)) {
            $parts = preg_split('/\s{2,}/', $line);
            $vlanRows[] = [
                'vlan_id' => $parts[0] ?? '',
                'status' => $parts[1] ?? '',
                'name'   => $parts[2] ?? '',
            ];
        }
    }

    return view('device.tabs.vlan.vlan_config', compact('vlanRows'));
}

public function editVlan($vlanId, Request $request)
{
     $vlanName = $request->query('name', '');
    // Run Ansible command (use full path and escape properly)
    $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/vlan_config_edit1.yml";
    
    $output = shell_exec($command);

    // Extract JSON-like block
    preg_match('/"output.stdout_lines": \[(.*?)\]/s', $output, $matches);

    $lines = [];
    if (!empty($matches[1])) {
        // Clean and explode lines
        $clean = trim(str_replace(['",', '"'], '', $matches[1]));
        $rawLines = array_filter(array_map('trim', explode("\n", $clean)));
        $lines = array_values($rawLines);
    }

    $interfaces = [];
    $skipLines = ['show interface brief', 'AS212XT#'];

    foreach ($lines as $i => $line) {
        if (in_array($line, $skipLines) || empty(trim($line))) continue;

        // Handle multiline description (g0/7 case)
        if (preg_match('/^\s+/', $line)) {
            $line = preg_replace('/\s+/', ' ', trim($line));
            $interfaces[count($interfaces) - 1]['Continuation'] = $line;
            continue;
        }

        $parts = preg_split('/\s{2,}/', $line);
        $interfaces[] = [
            'Raw' => $line,
            'Port' => $parts[0] ?? '',
            'Description' => $parts[1] ?? '',
            'Status' => $parts[2] ?? '',
            'VLAN' => $parts[3] ?? '',
            'Duplex' => $parts[4] ?? '',
            'Speed' => $parts[5] ?? '',
            'Type' => $parts[6] ?? '',
        ];
    }

    return view('device.tabs.vlan.vlan_edit', compact('interfaces', 'vlanId','vlanName'));
}

public function addVlan(Request $request)
{
    
    // Run Ansible command (use full path and escape properly)
    $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/vlan_config_edit1.yml";
    
    $output = shell_exec($command);

    // Extract JSON-like block
    preg_match('/"output.stdout_lines": \[(.*?)\]/s', $output, $matches);

    $lines = [];
    if (!empty($matches[1])) {
        // Clean and explode lines
        $clean = trim(str_replace(['",', '"'], '', $matches[1]));
        $rawLines = array_filter(array_map('trim', explode("\n", $clean)));
        $lines = array_values($rawLines);
    }

    $interfaces = [];
    $skipLines = ['show interface brief', 'AS212XT#'];

    foreach ($lines as $i => $line) {
        if (in_array($line, $skipLines) || empty(trim($line))) continue;

        // Handle multiline description (g0/7 case)
        if (preg_match('/^\s+/', $line)) {
            $line = preg_replace('/\s+/', ' ', trim($line));
            $interfaces[count($interfaces) - 1]['Continuation'] = $line;
            continue;
        }

        $parts = preg_split('/\s{2,}/', $line);
        $interfaces[] = [
            'Raw' => $line,
            'Port' => $parts[0] ?? '',
            'Description' => $parts[1] ?? '',
            'Status' => $parts[2] ?? '',
            'VLAN' => $parts[3] ?? '',
            'Duplex' => $parts[4] ?? '',
            'Speed' => $parts[5] ?? '',
            'Type' => $parts[6] ?? '',
        ];
    }

    return view('device.tabs.vlan.vlan_add', compact('interfaces'));
}

public function storeVlan(Request $request)
{
    $data = $request->json()->all();

    $vlanId = $data['vlanId'] ?? null;

    $vlanName = $data['vlanName'] ?? null;
    $portVlans = $data['port_vlan'] ?? [];

    if (!$vlanId) {
        return response()->json(['message' => 'VLAN ID and Name are required'], 422);
    }

    // foreach ($portVlans as $port => $vlan) {
        if ($vlanId) {
            $safeVlan = escapeshellarg($vlanId);
            $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/vlan_batch.yml -e add_vlan={$safeVlan}";
            shell_exec($command);
        }
    // }

    return response()->json(['message' => 'VLAN configuration applied successfully.']);
}


public function deleteBatch(Request $request)
{
    
    $vlanIds = $request->input('vlan_ids');

    if (!is_array($vlanIds) || empty($vlanIds)) {
        return response()->json(['success' => false, 'message' => 'No VLANs selected.']);
    }

    foreach ($vlanIds as $vlanId) {
        $safeVlan = escapeshellarg($vlanId);

        // Run Ansible delete command (adjust to your actual playbook)
        $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/vlan_batch.yml -e del_vlan={$safeVlan}";
        shell_exec($command);
    }

    return response()->json(['success' => true, 'message' => 'Selected VLANs deleted successfully.']);
}








   




    public function vlanBatchTab()
    {
     $command = 'ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/vlan_show.yml';
    $output = shell_exec($command);

    $vlanList = [];

    // Extract line containing "vlan 1,10,12-13,..."
    if (preg_match('/vlan\s+([0-9,\-\s]+)/i', $output, $matches)) {
        $vlanRaw = trim($matches[1]); // e.g. "1,10,12-13,20"

        $vlanParts = explode(',', $vlanRaw);

        foreach ($vlanParts as $part) {
            $part = trim($part);
            if (strpos($part, '-') !== false) {
                [$start, $end] = explode('-', $part);
                $vlanList = array_merge($vlanList, range((int)$start, (int)$end));
            } else {
                $vlanList[] = (int)$part;
            }
        }

        $vlanList = array_unique($vlanList);
        sort($vlanList);
    }
    return view('device.tabs.vlan.vlan_batch_config', compact('vlanList'));
    }

    public function storeBatchVlan(Request $request)
{
     $add = trim($request->input('vlanAdd'));
    $delete = trim($request->input('vlanDelete'));

    if (empty($add) && empty($delete)) {
        return response()->json([
            'success' => false,
            'message' => 'Please enter VLANs to add or delete.'
        ]);
    }

    $baseCommand = 'ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/vlan_batch.yml';

    $resultOutput = [];

    // Add VLANs
    if (!empty($add)) {
        $safeAdd = escapeshellarg($add); // Quote-safe input
        $addCommand = $baseCommand . " -e add_vlan={$safeAdd}";
        $output = shell_exec($addCommand);
        $resultOutput['add'] = $output ?: 'Add VLAN command failed or returned no output.';
    }

    // Delete VLANs
    if (!empty($delete)) {
        $safeDelete = escapeshellarg($delete); // Quote-safe input
        $deleteCommand = $baseCommand . " -e del_vlan={$safeDelete}";
        $output = shell_exec($deleteCommand);
        $resultOutput['delete'] = $output ?: 'Delete VLAN command failed or returned no output.';
    }

    return response()->json([
        'success' => true,
        'message' => 'VLAN changes applied successfully.',
        'output'  => $resultOutput // Optional: return raw output if needed
    ]);
   
}






public function interfaceVlanAttrTab()
{
    $command = 'ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/show_vlan_interface.yml';
    $output = shell_exec($command);

    $interfaces = [];

    if (preg_match('/"output\.stdout":\s*"((?:[^"\\\\]|\\\\.)*)"/s', $output, $matches)) {
        $stdout = stripcslashes($matches[1]);
        $lines = explode("\n", $stdout);

        $start = false;
        $i = 0;

        while ($i < count($lines)) {
            $line = trim($lines[$i]);

            // Start after header
            if (preg_match('/^Port\s+Description\s+Status\s+Vlan/i', $line)) {
                $start = true;
                $i++;
                continue;
            }

            if ($start && $line !== '') {
                $nextLine = $lines[$i + 1] ?? '';

                // Combine current + next line if next doesn't start with port
                if (preg_match('/^([a-z]+\d+\/?\d*)/', $line)) {
                    $mergedLine = $line;

                    if (!empty($nextLine) && !preg_match('/^[a-z]+\d+\/?\d*/i', trim($nextLine))) {
                        $mergedLine .= ' ' . trim($nextLine);
                        $i++; // Skip next line
                    }

                    // Now split
                    $parts = preg_split('/\s+/', $mergedLine);

                    $port = $parts[0];
                    $second = $parts[1] ?? '';

                    // If second value is 'up' or 'down', then description is missing
                    if (in_array(strtolower($second), ['up', 'down'])) {
                        $description = '';
                        $status = $parts[1] ?? '';
                        $vlan = $parts[2] ?? '';
                        $duplex = $parts[3] ?? '';
                        $speed = $parts[4] ?? '';
                        $type = $parts[5] ?? '';
                    } else {
                        $description = $parts[1] ?? '';
                        $status = $parts[2] ?? '';
                        $vlan = $parts[3] ?? '';
                        $duplex = $parts[4] ?? '';
                        $speed = $parts[5] ?? '';
                        $type = $parts[6] ?? '';
                    }

                    $interfaces[] = [
                        'Port' => $port,
                        'Description' => $description,
                        'Status' => $status,
                        'VLAN' => $vlan,
                        'Duplex' => $duplex,
                        'Speed' => $speed,
                        'Type' => $type,
                    ];
                }
            }

            $i++;
        }
    }

    return view('device.tabs.vlan.interface_vlan_attr', compact('interfaces'));
}







public function editVlanInterface(Request $request)
{
    
    $port = $request->input('port');    // e.g. g0/7
    $pvid = $request->input('pvid');    // e.g. 200
    $mode = $request->input('mode');    // e.g. access

    if (str_contains(strtolower($pvid), 'trunk')) {
        $mode = 'Trunk';
    } elseif (empty($mode)) {
        $mode = 'Access';
    }

    

    $playbookPath = '/opt/librenms/librenms-ansible-inventory-plugin/show_vlan_allowed_range.yml';
    $inventoryPath = '/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml';
    $cmd = "ansible-playbook -i {$inventoryPath} {$playbookPath} -e \"port_name={$port}\"";

    $output = shell_exec($cmd);

    preg_match('/"output\.stdout":\s*"([^"]+)"/', $output, $matches);
    $vlanOutput = isset($matches[1]) ? stripcslashes($matches[1]) : "";

    $lines = preg_split("/\r\n|\n|\r/", $vlanOutput);

    // dd($lines);

   $vlanAllowed = null;
    $vlanUntagged = null;
    $pvid = null;

    // Loop through lines to extract values
    foreach ($lines as $line) {
        $line = trim($line);

        if (stripos($line, 'switchport trunk vlan-allowed') !== false) {
            $vlanAllowed = trim(str_replace('switchport trunk vlan-allowed', '', $line));
        }

        if (stripos($line, 'switchport trunk vlan-untagged') !== false) {
            $vlanUntagged = trim(str_replace('switchport trunk vlan-untagged', '', $line));
        }

        if (stripos($line, 'switchport pvid') !== false) {
            $pvid = trim(str_replace('switchport pvid', '', $line));
        }
    }

    // Set default for vlanAllowed if not found
    if (empty($vlanAllowed)) {
        $vlanAllowed = '1–4094';
    }

    // Set default for vlanUntagged
    if (empty($vlanUntagged)) {
        $vlanUntagged = $pvid ?: '1'; // if pvid is not set, use '1'
    }
    return view('device.tabs.vlan.vlan_interface_edit', compact('port', 'pvid', 'mode','vlanAllowed', 'vlanUntagged'));
}

public function runVlanAttributeinterface(Request $request)
{
   dd($request);
   
    $data = $request->only([
        'port_name',
        'pvid',
        'mode',
        'vlan_allowed_range',
        'vlan_untagged_range',
        'add_vlan_allowed_range',
        'remove_vlan_allowed_range',
        'add_vlan_untagged_range',
        'remove_vlan_untagged_range',
    ]);

    $inventoryPath = '/opt/librenms/librenms-ansible-inventory-plugin/hosts.yml';
    $playbookPath = '/opt/librenms/librenms-ansible-inventory-plugin/interface_vlan_attributes_config.yml';

    $extraVars = http_build_query($data, '', ' ');
    $cmd = "ansible-playbook -i {$inventoryPath} {$playbookPath} --extra-vars \"$extraVars\" 2>&1";

    $output = shell_exec($cmd);

    return response()->json([
        'status' => 'success',
        'output' => $output,
    ]);
}








public function voiceVlanTab()
{
    $command = 'ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/show_voice_vlan.yml';
    $output = shell_exec($command);

    $voiceVlans = [];

    if (preg_match('/"output.stdout":\s*"([^"]+)"/', $output, $matches)) {
        $stdout = stripcslashes($matches[1]);
        $lines = explode("\n", $stdout);

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, 'voice-vlan mac-address') === 0) {
                preg_match('/voice-vlan mac-address\s+([^\s]+)\s+mask\s+([^\s]+)/', $line, $macMatch);
                if (!empty($macMatch[1]) && !empty($macMatch[2])) {
                    $voiceVlans[] = [
                        'mac' => $macMatch[1],
                        'mask' => $macMatch[2]
                    ];
                }
            }
        }
    }

    return view('device.tabs.vlan.voice_vlan', compact('voiceVlans'));
}

    public function addVoiceVlan(Request $request)
    {
        
        return view('device.tabs.vlan.voice_vlan_add');
    }

    public function storeVoiceVlan(Request $request)
{
    $request->validate([
        'macAdd' => 'required|regex:/^([0-9a-fA-F]{4}\.){2}[0-9a-fA-F]{4}$/',
        'maskAdd' => 'required|regex:/^([0-9a-fA-F]{4}\.){2}[0-9a-fA-F]{4}$/',
    ]);

    $macAddress = escapeshellarg($request->macAdd);
    $macMask = escapeshellarg($request->maskAdd);

    

    $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/voice_vlan.yml -e \"action=add mac_address={$macAddress} mac_mask={$macMask}\"";
    $output = shell_exec($command);

    if (strpos($output, 'failed=0') !== false) {
        return response()->json(['success' => true, 'message' => 'Voice VLAN MAC added successfully.']);
    }

    return response()->json(['success' => false, 'message' => 'Failed to apply configuration.']);
}

public function deleteVoiceVlan(Request $request)
{
    $items = $request->input('items');

    if (!is_array($items) || empty($items)) {
        return response()->json(['success' => false, 'message' => 'No entries selected.']);
    }

    foreach ($items as $entry) {
        $mac = escapeshellarg($entry['mac']);
        $mask = escapeshellarg($entry['mask']);

        $command = "ansible-playbook -i /opt/librenms/librenms-ansible-inventory-plugin/hosts.yml /opt/librenms/librenms-ansible-inventory-plugin/voice_vlan.yml -e \"action=delete mac_address={$mac} mac_mask={$mask}\"";
        shell_exec($command);
    }

    return response()->json(['success' => true, 'message' => 'Selected voice VLAN MACs deleted.']);
}







    public function interfaceVoiceVlanTab()
    {
        return view('device.tabs.vlan.interface_voice_vlan');
    }
}
