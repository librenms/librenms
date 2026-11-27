<?php

/**
 * MikrotikTransceiverTest.php
 *
 * Tests MikroTik transceiver data normalization.
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
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Unit\OS\Traits;

use App\Models\Device;
use LibreNMS\OS\Traits\MikrotikTransceiver;
use LibreNMS\Tests\TestCase;

final class MikrotikTransceiverTest extends TestCase
{
    private MikrotikTransceiverHarness $harness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->harness = new MikrotikTransceiverHarness;
    }

    public function testParsesCurrentMibData(): void
    {
        $this->assertSame([
            'type' => 'sfp',
            'vendor' => 'CISCO-PUREOPTICS',
            'model' => 'SFP-10G-LR-BXU',
            'serial' => 'M5AW8471',
            'distance' => null,
            'wavelength' => 1270,
            'connector' => 'lc',
        ], $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalModulePresent' => 'true',
            'MIKROTIK-MIB::mtxrOpticalVendorName' => 'CISCO-PUREOPTICS',
            'MIKROTIK-MIB::mtxrOpticalVendorPartNumber' => 'SFP-10G-LR-BXU',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => 'M5AW8471',
            'MIKROTIK-MIB::mtxrOpticalType' => 'sfp',
            'MIKROTIK-MIB::mtxrOpticalConnectorType' => 'lc',
            'MIKROTIK-MIB::mtxrOpticalLinkLengthCopperOM4' => '0',
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '1270.00',
        ]));
    }

    public function testParsesEnumStringsAndDistance(): void
    {
        $this->assertSame([
            'type' => 'sfp',
            'vendor' => 'BROCADE',
            'model' => 'SFP-H10GB-CU1M',
            'serial' => 'SOPCU1m#C770',
            'distance' => 1,
            'wavelength' => null,
            'connector' => 'copperPigtail',
        ], $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalModulePresent' => 1,
            'MIKROTIK-MIB::mtxrOpticalVendorName' => ' BROCADE ',
            'MIKROTIK-MIB::mtxrOpticalVendorPartNumber' => 'SFP-H10GB-CU1M',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => 'SOPCU1m#C770',
            'MIKROTIK-MIB::mtxrOpticalType' => 'sfp',
            'MIKROTIK-MIB::mtxrOpticalConnectorType' => 'copperPigtail',
            'MIKROTIK-MIB::mtxrOpticalLinkLengthCopperOM4' => 1,
            'MIKROTIK-MIB::mtxrOpticalWavelength' => 0,
        ]));
    }

    public function testUnknownEnumsBecomeNull(): void
    {
        $this->assertSame([
            'type' => null,
            'vendor' => 'CISCO-AGILENT',
            'model' => 'QFBR-5766LP',
            'serial' => 'SERIAL',
            'distance' => null,
            'wavelength' => 850,
            'connector' => null,
        ], $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalModulePresent' => 1,
            'MIKROTIK-MIB::mtxrOpticalVendorName' => 'CISCO-AGILENT',
            'MIKROTIK-MIB::mtxrOpticalVendorPartNumber' => 'QFBR-5766LP',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => 'SERIAL',
            'MIKROTIK-MIB::mtxrOpticalType' => 3,
            'MIKROTIK-MIB::mtxrOpticalConnectorType' => 'unknown',
            'MIKROTIK-MIB::mtxrOpticalWavelength' => 850.00,
            'MIKROTIK-MIB::mtxrOpticalTemperature' => 0,
            'MIKROTIK-MIB::mtxrOpticalSupplyVoltage' => 0,
            'MIKROTIK-MIB::mtxrOpticalTxBiasCurrent' => 0,
            'MIKROTIK-MIB::mtxrOpticalTxPower' => 0,
            'MIKROTIK-MIB::mtxrOpticalRxPower' => 0,
        ]));
    }

    public function testRejectsEmptySlot(): void
    {
        $this->assertNull($this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalModulePresent' => 'false',
        ]));
    }

    public function testAcceptsDataFromOlderMib(): void
    {
        $this->assertSame([
            'type' => null,
            'vendor' => 'OEM',
            'model' => null,
            'serial' => 'S180300350827',
            'distance' => null,
            'wavelength' => null,
            'connector' => null,
        ], $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalVendorName' => 'OEM',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => 'S180300350827',
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '65535.00',
        ]));
    }

    public function testRejectsEmptySlotFromOlderMib(): void
    {
        $this->assertNull($this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '0',
            'MIKROTIK-MIB::mtxrOpticalTxBiasCurrent' => '0',
            'MIKROTIK-MIB::mtxrOpticalSupplyVoltage' => '.000',
            'MIKROTIK-MIB::mtxrOpticalTemperature' => '4294967168',
            'MIKROTIK-MIB::mtxrOpticalVendorName' => '',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => '',
        ]));
    }

    public function testRejectsOlderRouterosEmptyCageWithZeroTemperature(): void
    {
        $this->assertNull($this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '.00',
            'MIKROTIK-MIB::mtxrOpticalTxBiasCurrent' => '0',
            'MIKROTIK-MIB::mtxrOpticalSupplyVoltage' => '.000',
            'MIKROTIK-MIB::mtxrOpticalTemperature' => '0',
            'MIKROTIK-MIB::mtxrOpticalVendorName' => '',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => '',
            'MIKROTIK-MIB::mtxrOpticalTxPower' => '0',
            'MIKROTIK-MIB::mtxrOpticalRxPower' => '0',
        ]));
    }

    public function testKeepsLegacyModuleWithInvalidWavelength(): void
    {
        $this->assertSame([
            'type' => null,
            'vendor' => null,
            'model' => null,
            'serial' => null,
            'distance' => null,
            'wavelength' => null,
            'connector' => null,
        ], $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '16653.93',
        ]));
    }

    public function testClearsWavelengthBelow600(): void
    {
        $transceiver = $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalVendorName' => 'FS',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => 'SERIAL',
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '256.00',
        ]);

        $this->assertNotNull($transceiver);
        $this->assertNull($transceiver['wavelength']);
    }

    public function testRejectsInvalidWavelength(): void
    {
        $transceiver = $this->harness->parse([
            'MIKROTIK-MIB::mtxrOpticalVendorName' => 'FS',
            'MIKROTIK-MIB::mtxrOpticalVendorSerial' => 'SERIAL',
            'MIKROTIK-MIB::mtxrOpticalWavelength' => '16653.93',
        ]);

        $this->assertNotNull($transceiver);
        $this->assertNull($transceiver['wavelength']);
    }
}

/**
 * Harness so PHPStan analyzes the trait against a host that provides getDevice().
 *
 * @internal
 */
final class MikrotikTransceiverHarness
{
    use MikrotikTransceiver {
        parseMikrotikTransceiver as public parse;
    }

    public function getDevice(): Device
    {
        return new Device;
    }
}
