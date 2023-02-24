<?php

namespace LibreNMS\Tests\Feature\SnmpTraps;

class VeeamTrapTest extends SnmpTrapTestCase
{
    public function testVeeamBackupJobCompleted(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupJobCompleted
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job 1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::backupJobComment comment
VEEAM-MIB::backupJobResult Success
TRAP,
            'SNMP Trap: Backup Job Success - SureBackup Job 1 - comment',
            'Could not handle VEEAM-MIB::traps job completed',
            [1, 'backup']
        );
    }

    public function testVeeamBackupJobCompletedWarning(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupJobCompleted
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job 1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::backupJobComment comment
VEEAM-MIB::backupJobResult Warning
TRAP,
            'SNMP Trap: Backup Job Warning - SureBackup Job 1 - comment',
            'Could not handle VEEAM-MIB::traps job completed warning',
            [4, 'backup'],
        );
    }

    public function testVeeamBackupJobCompletedFailed(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onBackupJobCompleted
VEEAM-MIB::backupJobId 7a1b3549-c4c7-4629-84d6-74e24fee8011
VEEAM-MIB::backupJobName SureBackup Job 1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::backupJobComment comment
VEEAM-MIB::backupJobResult Failed
TRAP,
            'SNMP Trap: Backup Job Failed - SureBackup Job 1 - comment',
            'Could not handle VEEAM-MIB::traps job completed failed',
            [5, 'backup'],
        );
    }

    public function testVeeamVmBackupCompleted(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment comment
VEEAM-MIB::vmBackupResult Success
TRAP,
            'SNMP Trap: VM backup Success - vmname1 Job: Linux taeglich low - comment',
            'Could not handle VEEAM-MIB::traps backup completed',
            [1, 'backup'],
        );
    }

    public function testVeeamVmBackupCompletedWarning(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment comment
VEEAM-MIB::vmBackupResult Warning
TRAP,
            'SNMP Trap: VM backup Warning - vmname1 Job: Linux taeglich low - comment',
            'Could not handle VEEAM-MIB::traps backup completed warning',
            [4, 'backup'],
        );
    }

    public function testVeeamVmBackupCompletedFailed(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:46024->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:13:08:37.60
SNMPv2-MIB::snmpTrapOID.0 VEEAM-MIB::onVmBackupCompleted
VEEAM-MIB::backupJobName Linux taeglich low
VEEAM-MIB::vmName vmname1
VEEAM-MIB::sourceHostName hostname
VEEAM-MIB::vmBackupComment comment
VEEAM-MIB::vmBackupResult Failed
TRAP,
            'SNMP Trap: VM backup Failed - vmname1 Job: Linux taeglich low - comment',
            'Could not handle VEEAM-MIB::traps backup completed failed',
            [5, 'backup'],
        );
    }
}
