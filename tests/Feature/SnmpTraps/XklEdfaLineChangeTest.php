<?php
/**
 * XklEdfaLineChangeTest.php
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

final class XklEdfaLineChangeTest extends SnmpTrapTestCase
{
    public function testXklEdfaLineChangeUp(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFALineChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAAmplificationState.1 up
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

		'Output EDFA changed state to up',
		'Failed to handle XklEdfaLineChange up state trap.',
		[Severity::Ok],
		);
	}

	public function testXklEdfaLineChangeUnused(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFALineChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAAmplificationState.1 unused
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

		'Output EDFA changed state to unused',
		'Failed to handle XklEdfaLineChange unused state trap.',
		[Severity::Info],
		);
	}

	public function testXklEdfaLineChangeWarning(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFALineChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAAmplificationState.1 warning
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

		'Output EDFA changed state to warning',
		'Failed to handle XklEdfaLineChange warning state trap.',
		[Severity::Warning],
		);
	}

	public function testXklEdfaLineChangeUnknown(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFALineChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAAmplificationState.1 unknown
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

		'Output EDFA changed state to unknown',
		'Failed to handle XklEdfaLineChange Unknown state trap.',
		[Severity::Info],
		);
	}

	public function testXklEdfaLineChangeDefault(): void
    {
	    $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFALineChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAAmplificationState.1 alarm
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

		'Output EDFA changed state to alarm',
		'Failed to handle XklEdfaLineChange default state trap.',
		[Severity::Error],
		);
	}
}
