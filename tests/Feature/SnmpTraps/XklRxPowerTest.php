<?php
/**
 * XklRxPowerTest.php
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
 * @link       http://librenms.org
 * @copyright  2026 Heath Barnhart
 * @author     Heath Barnhart hbarnhart@kanren.net
 */
 
namespace LibreNMS\Tests\Feature\SnmpTraps;
use LibreNMS\Enum\Severity;

final class XklRxPowerTest extends SnmpTrapTestCase
{
    public function testXklRxPowerOK(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklRxPowerOK
XKL-MIB::xklTransportIndex.1 1
XKL-MIB::xklTransportDescr.1 Client 0\/0 (N\/A)
XKL-MIB::xklTransportReceivePower.1 16 1\/10 dBm
TRAP,

		'Transciever Wave 0 (N\/A) receive power OK. Current value: 16 1/10 dBm',
		'Failed to handle XklRxPowerOK trap.',
		[Severity::Ok],
		);
	}

	public function testXklRxPowerLoWarn(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklRxPowerLoWarn
XKL-MIB::xklTransportIndex.33 33
XKL-MIB::xklTransportDescr.33 Wave 0 (N\/A)
XKL-MIB::xklTransportReceivePower.33 -237 1\/10 dBm
XKL-MIB::xklTransportRxPowerLoWarnThresh.33 -230 1\/10 dBm
TRAP,

		'Transciever Wave 0 (N\/A) is below recieve warning threshold . Current value: -237 1/10 dBm',
		'Failed to handle XklRxPowerLoWarn trap.',
		[Severity::Warning],
		);
	}

    public function testXklRxPowerLoAlrm(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklRxPowerLoAlrm
"XKL-MIB::xklTransportIndex.33 33"
"XKL-MIB::xklTransportDescr.33 Wave 0 (N\/A)"
"XKL-MIB::xklTransportReceivePower.33 -400 1\/10 dBm"
"XKL-MIB::xklTransportRxPowerLoAlrmThresh.33 -282 1\/10 dBm"
TRAP,

		'Transciever Wave 0 (N\/A) is below recieve alarm threshold -282 1/10 dBm. Current value: -400 1/10 dBm',
		'Failed to handle XklRxPowerLoAlrm trap.',
		[Severity::Error],
		);
	}

	public function testXklRxPowerHiWarn(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklRxPowerHiWan
"XKL-MIB::xklTransportIndex.33 33"
"XKL-MIB::xklTransportDescr.33 Wave 0 (N\/A)"
"XKL-MIB::xklTransportReceivePower.33 -5 1\/10 dBm"
"XKL-MIB::xklTransportRxPowerLoAlrmThresh.33 -10 1\/10 dBm"
TRAP,

		'Transciever Wave 0 (N\/A) is below recieve warning threshold -10 1/10 dBm. Current value: -5 1/10 dBm',
		'Failed to handle XklRxPowerHiWarn trap.',
		[Severity::Warning],
		);
	}

	public function testXklRxPowerHiAlrm(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklRxPowerHiAlrm
"XKL-MIB::xklTransportIndex.33 33"
"XKL-MIB::xklTransportDescr.33 Wave 0 (N\/A)"
"XKL-MIB::xklTransportReceivePower.33 20 1\/10 dBm"
"XKL-MIB::xklTransportRxPowerLoAlrmThresh.33 10 1\/10 dBm"
TRAP,

		'Transciever Wave 0 (N\/A) is above recieve alarm threshold -10 1/10 dBm. Current value: 20 1/10 dBm',
		'Failed to handle XklRxPowerHiAlrm trap.',
		[Severity::Error],
		);
	}
}
