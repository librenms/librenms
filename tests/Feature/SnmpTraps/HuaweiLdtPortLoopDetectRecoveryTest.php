<?php
/**
 * HuaweiLdtPortLoopDetectRecoveryTest.php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class HuaweiLdtPortLoopDetectRecoveryTest extends SnmpTrapTestCase
{
    /**
     * Test HuaweiLdtPortLoopRecoveryDetect.php handler
     *
     * @return void
     */
    public function testHuaweiLdtPortLoopRecoveryDetect(): void
    {
        $this->assertTrapLogsMessage('{{ hostname }}
UDP: [{{ ip }}]:44289->[1.1.1.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 82:19:24:56.09
SNMPv2-MIB::snmpTrapOID.0 HUAWEI-LDT-MIB::hwLdtPortLoopDetectRecovery
HUAWEI-LDT-MIB::hwLPortLoopDetectIfName GigabitEthernet0/0/1
HUAWEI-LDT-MIB::hwPortLoopDetectStatus normal',
            'Loop Detect Recovery GigabitEthernet0/0/1 (Status normal)',
            'Could not handle HUAWEI-LDT-MIB::HuaweiLdtPortLoopDetectRecovery trap',
            [Severity::Ok, 'loop', 'GigabitEthernet0/0/1']
        );
    }
}
