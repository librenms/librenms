<?php

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use Log;

class VeeamTrapTest extends SnmpTrapTestCase
{
    public function testVeeamOnBackupJobCompleted(): void
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupJobCompleted
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job 1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment 
VEEAM-MIB::backupJobResult Success";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: Backup Job success - SureBackup Job 1 ';
        Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VEEAM-MIB::traps');
    }

    public function testVeeamOnBackupJobCompletedWarning(): void
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupJobCompleted
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job 1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment 
VEEAM-MIB::backupJobResult Warning";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: Backup Job warning - SureBackup Job 1 ';
        Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VEEAM-MIB::traps');
    }

    public function testVeeamOnBackupJobCompletedFailed(): void
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupJobCompleted
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job 1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment 
VEEAM-MIB::vmBackupResult Failed";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: Backup Job failed - SureBackup Job 1 ';
        Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VEEAM-MIB::traps');
    }

    public function testVeeamOnVmBackupCompleted(): void
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment
VEEAM-MIB::vmBackupResult Success";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: VM Backup Success - Linux taeglich low vmname1';
        Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 1);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VEEAM-MIB::traps');
    }

    public function testVeeamOnVmBackupCompletedWarning(): void
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment
VEEAM-MIB::vmBackupResult Warning";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: VM Backup Warning - Linux taeglich low vmname1';
        Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 4);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VEEAM-MIB::traps');
    }

    public function testVeeamOnVmBackupCompletedFail(): void
    {
        $device = Device::factory()->create();

        $trapText = "$device->hostname
UDP: [$device->ip]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment
VEEAM-MIB::vmBackupResult Failed";

        $trap = new Trap($trapText);

        $message = 'SNMP Trap: VM Backup Failed - Linux taeglich low vmname1';
        Log::shouldReceive('event')->once()->with($message, $device->device_id, 'backup', 5);

        $this->assertTrue(Dispatcher::handle($trap), 'Could not handle VEEAM-MIB::traps');
    }
}
