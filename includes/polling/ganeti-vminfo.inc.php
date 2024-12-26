<?php

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\PowerState;

function uuid_to_vmwVmVMID($uuid) {
    $hex = str_replace('-', '', $uuid);
    $bin = pack('h*', $hex);
    $ints = unpack('L*', $bin);
    # Truncate value so it fits in a mysql int(11)
    return ($ints[1] + $ints[2] + $ints[3] + $ints[4]) % 2147483647;
}

function ganeti_state_to_PowerState($state) {
    if ($state == 'up') {
        return PowerState::ON;
    } elseif ($state == 'down') {
        return PowerState::OFF;
    } else {
        return PowerState::UNKNOWN;
    }
}

// FIXME should do the deletion etc in a common file perhaps? like for the sensors
// Try to discover Libvirt Virtual Machines.
if (DeviceCache::getPrimary()->getAttrib('ganeti_enabled')) {
    $ganeti_vmlist = [];

    $ganeti_user = DeviceCache::getPrimary()->getAttrib('ganeti_user');
    $ganeti_pass = DeviceCache::getPrimary()->getAttrib('ganeti_pass');
    $ssh_ok = 0;

    # https://ganeti.example.com:5080/2/instances?bulk=1
    $instances_bulk_text = file_get_contents("https://" . $ganeti_user . ":" . $ganeti_pass . "@" . $device['hostname'] . ":5080/2/instances?bulk=1");

	if ($instances_bulk_text) {
		$instances_bulk = json_decode($instances_bulk_text, true);

		#| id               | int(10) unsigned     | NO   | PRI | NULL    | auto_increment |
		#| device_id        | int(10) unsigned     | NO   | MUL | NULL    |                |
		#| vm_type          | varchar(16)          | NO   |     | vmware  |                |
		#| vmwVmVMID        | int(11)              | NO   | MUL | NULL    |                |
		#| vmwVmDisplayName | varchar(128)         | NO   |     | NULL    |                |
		#| vmwVmGuestOS     | varchar(128)         | NO   |     | NULL    |                |
		#| vmwVmMemSize     | int(11)              | NO   |     | NULL    |                |
		#| vmwVmCpus        | int(11)              | NO   |     | NULL    |                |
		#| vmwVmState       | smallint(5) unsigned | NO   |     | NULL    |                |

		foreach ($instances_bulk as $ganeti_instance) {
			$vm = array(
				'vm_type' => 'ganeti',
				'device_id' => getidbyname($ganeti_instance['pnode']),
				'vmwVmVMID' => uuid_to_vmwVmVMID($ganeti_instance['uuid']), # truncate this to int(11)
				'vmwVmDisplayName' => $ganeti_instance['name'],
				'vmwVmGuestOS' => $ganeti_instance['os'],
				'vmwVmMemSize' => $ganeti_instance['beparams']['memory'], # or oper_ram ?
				'vmwVmCpus' => $ganeti_instance['beparams']['vcpus'], # or oper_vcpus ?
				'vmwVmState' => ganeti_state_to_PowerState($ganeti_instance['admin_state']),
			);

			$ganeti_vmlist[$vm['vmwVmVMID']] = $vm;

			// Check whether the Virtual Machine is already known for this host.
			$result = dbFetchRow("SELECT * FROM `vminfo` WHERE `vmwVmVMID` = ? AND `vm_type` = 'ganeti' LIMIT 1", [$vm['vmwVmVMID']]);

			if (is_null($result)) {
				$inserted_id = dbInsert($vm, 'vminfo');
				echo '+';
				log_event("Virtual Machine added: ".$vm['vmwVmDisplayName']." (".$vm['vmwVmMemSize']." MB)", $device, 'vm', 3, $inserted_id);
			} else {
				if ($result['vmwVmState'] != $vm['vmwVmState']
					|| $result['vmwVmDisplayName'] != $vm['vmwVmDisplayName']
					|| $result['vmwVmCpus'] != $vm['vmwVmCpus']
					|| $result['vmwVmGuestOS'] != $vm['vmwVmGuestOS']
					|| $result['vmwVmMemSize'] != $vm['vmwVmMemSize']
				) {
					dbUpdate($vm, 'vminfo', "vm_type='ganeti' AND vmwVmVMID=?", [$vm['vmwVmVMID']]);
					echo 'U';
					// FIXME eventlog
				} else {
					echo '.';
				}
			}
		}

		// Get a list of all the known Virtual Machines for this host.
		$sql = "SELECT id, vmwVmVMID, vmwVmDisplayName FROM vminfo WHERE vm_type='ganeti'";

		foreach (dbFetchRows($sql) as $db_vm) {
			// Delete the Virtual Machines that are removed from the host.

			if (!isset($ganeti_vmlist[$db_vm['vmwVmVMID']])) {
				dbDelete('vminfo', '`id` = ?', [$db_vm['id']]);
				echo '-';
				log_event('Virtual Machine removed: ' . $db_vm['vmwVmDisplayName'], $device, 'vm', 4, $db_vm['id']);
			}
		}
	}

    echo "\n";
}//end if
