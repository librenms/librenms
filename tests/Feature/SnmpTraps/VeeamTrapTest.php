<?php

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;

class VeeamTrapTest extends SnmpTrapTestCase
{
    public function testVeeamOnVmBackupCompleted()
    {
        $device = Device::factory()->create();
        
        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted 
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName  vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupResult Success
VEEAM-MIB::vmBackupComment";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: VM Backup success - Linux copy - vmname1';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle onVmBackupCompleted');
    }

    public function testVeeamOnBackupCompleted()
    {
        $device = Device::factory()->create();
        
        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupCompleted 
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupResult Success
VEEAM-MIB::vmBackupComment ";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: Backup success - SureBackup Job 1';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 2);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle onBackupCompleted');
    }

    public function testVeeamOnBackupCompletedFails()
    {
        $device = Device::factory()->create();
        
        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupCompleted 
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupResult Failed
VEEAM-MIB::vmBackupComment ";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: Backup failed - SureBackup Job 1';
        \Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle onBackupCompleted');
    }

}
