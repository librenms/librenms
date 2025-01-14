<?php
/**
 * CiscoNSTrapTest.php
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
 * @copyright  2024 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class CiscoNSTrapTest extends SnmpTrapTestCase
{
    /**
     * Test Axis Video trap handlers
     *
     * @return void
     */
    public function testCiscoNSDBFull(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.2.4.101]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (2940) 0:00:29.40
SNMPv2-MIB::snmpTrapOID.0 CISCO-NS-MIB::fcNameServerDatabaseFull',
            'Cisco Nameserver entry failed, database full',
            'Could not handle CISCO-NS-MIB::fcNameServerDatabaseFull test trap',
            [Severity::Error],
        );
    }

    public function testCiscoNSEntryAdd(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:51988->[10.2.4.101]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (706805) 1:57:48.05
SNMPv2-MIB::snmpTrapOID.0 CISCO-NS-MIB::fcNameServerEntryAdd',
            'Cisco Nameserver database entry added',
            'Could not handle CISCO-NS-MIB::fcNameServerEntryAdd test trap',
            [Severity::Notice],
        );
    }

    public function testCiscoNSEntryDelete(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.2.4.101]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (101642184) 11 days, 18:20:21.84
SNMPv2-MIB::snmpTrapOID.0 CISCO-NS-MIB::fcNameServerEntryDelete',
            'Cisco Nameserver database entry deleted',
            'Could not handle CISCO-NS-MIB::fcNameServerEntryDelete test trap',
            [Severity::Notice],
        );
    }

    public function testCiscoNSRejectRegNotify(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
[UDP: [{{ ip }}]:49563->[10.2.4.101]:162]:
SNMPv2-MIB::sysUpTime.0 = Timeticks: (101642184) 11 days, 18:20:21.84
SNMPv2-MIB::snmpTrapOID.0 CISCO-NS-MIB::fcNameServerRejectRegNotify
CISCO-NS-MIB::fcNameServerRejectReasonCode logicalError
CISCO-NS-MIB::fcNameServerRejReasonCodeExp nodeNameNotRegistered',
            'Cisco Nameserver rejected a registration request with error code logicalError due to nodeNameNotRegistered',
            'Could not handle CISCO-NS-MIB::fcNameServerRejectRegNotify test trap',
            [Severity::Warning],
        );
    }
}
