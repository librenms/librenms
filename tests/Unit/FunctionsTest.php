<?php

/**
 * FunctionsTest.php
 *
 * tests functions in includes/functions.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\DeviceAttrib;
use Illuminate\Support\Facades\Log;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Enum\IntegerType;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Number;
use LibreNMS\Util\StringHelpers;

final class FunctionsTest extends TestCase
{
    public function testHex2Bin(): void
    {
        $this->assertEquals('Big 10 UP', hex2bin('426967203130205550'));
    }

    public function testSnmpHexstring(): void
    {
        $input = '4c 61 72 70 69 6e 67 20 34 20 55 00 0a';
        $this->assertEquals("Larping 4 U\n", StringHelpers::hexToAscii($input, ' '));
    }

    public function testDynamicDiscoveryGetValue(): void
    {
        $pre_cache = [
            'firstdata' => [
                0 => ['temp' => 1],
                1 => ['temp' => 2],
            ],
            'high' => [
                0 => ['high' => 3],
                1 => ['high' => 4],
            ],
            'table' => [
                0 => ['first' => 5, 'second' => 6],
                1 => ['first' => 7, 'second' => 8],
            ],
            'single' => ['something' => 9],
            'oneoff' => 10,
            'singletable' => [
                11 => ['singletable' => 'Pickle'],
            ],
            'doubletable' => [
                12 => ['doubletable' => 'Mustard'],
                13 => ['doubletable' => 'BBQ'],
            ],
        ];

        $data = ['value' => 'temp', 'oid' => 'firstdata'];
        $this->assertNull(YamlDiscovery::getValueFromData('missing', 0, $data, $pre_cache));
        $this->assertSame('yar', YamlDiscovery::getValueFromData('default', 0, $data, $pre_cache, 'yar'));
        $this->assertSame(2, YamlDiscovery::getValueFromData('value', 1, $data, $pre_cache));

        $data = ['oid' => 'high'];
        $this->assertSame(3, YamlDiscovery::getValueFromData('high', 0, $data, $pre_cache));

        $data = ['oid' => 'table'];
        $this->assertSame(5, YamlDiscovery::getValueFromData('first', 0, $data, $pre_cache));
        $this->assertSame(7, YamlDiscovery::getValueFromData('first', 1, $data, $pre_cache));
        $this->assertSame(6, YamlDiscovery::getValueFromData('second', 0, $data, $pre_cache));
        $this->assertSame(8, YamlDiscovery::getValueFromData('second', 1, $data, $pre_cache));

        $this->assertSame(9, YamlDiscovery::getValueFromData('single', 0, $data, $pre_cache));
        $this->assertSame(10, YamlDiscovery::getValueFromData('oneoff', 3, $data, $pre_cache));
        $this->assertSame('Pickle', YamlDiscovery::getValueFromData('singletable', 11, $data, $pre_cache));
        $this->assertSame('BBQ', YamlDiscovery::getValueFromData('doubletable', 13, $data, $pre_cache));
    }

    public function testReplaceValuesAggregate(): void
    {
        $pre_cache = [
            'MIB::scalar' => [0 => ['MIB::scalar' => 1]],
            'MIB::table' => [
                1 => ['MIB::licenses' => 10, 'MIB::name' => 'a'],
                2 => ['MIB::licenses' => 1, 'MIB::name' => 'b'],
                3 => ['MIB::licenses' => 5, 'MIB::name' => 'c'],
            ],
            'MIB::flat' => [1 => '4', 2 => '6'],
        ];

        $data = [
            'oid' => 'MIB::scalar',
            'sum' => '{{ sum(MIB::licenses) }}',
            'avg' => '{{ avg(MIB::licenses) }}',
            'min' => '{{ min( MIB::licenses ) }}',
            'max' => '{{ max($MIB::licenses) }}',
            'filtered' => '{{ avg(MIB::licenses)|round(1) }}',
            'text' => 'Total {{ sum(MIB::licenses) }} of {{ max(MIB::licenses) }}',
            'missing' => '{{ sum(MIB::missing) }}',
            'unknown' => '{{ foobar(MIB::licenses) }}',
            'missing_default' => '{{ sum(MIB::missing)|default(0) }}',
            'flat' => '{{ sum(MIB::flat) }}',
        ];

        $this->assertSame('16', YamlDiscovery::replaceValues('sum', '0', 0, $data, $pre_cache));
        $this->assertStringStartsWith('5.333', YamlDiscovery::replaceValues('avg', '0', 0, $data, $pre_cache));
        $this->assertSame('1', YamlDiscovery::replaceValues('min', '0', 0, $data, $pre_cache));
        $this->assertSame('10', YamlDiscovery::replaceValues('max', '0', 0, $data, $pre_cache));
        $this->assertSame('5.3', YamlDiscovery::replaceValues('filtered', '0', 0, $data, $pre_cache));
        $this->assertSame('Total 16 of 10', YamlDiscovery::replaceValues('text', '0', 0, $data, $pre_cache));
        $this->assertSame('', YamlDiscovery::replaceValues('missing', '0', 0, $data, $pre_cache));
        // not an aggregate function, so the template is kept
        $this->assertSame('{{ foobar(MIB::licenses) }}', YamlDiscovery::replaceValues('unknown', '0', 0, $data, $pre_cache));
        $this->assertSame('0', YamlDiscovery::replaceValues('missing_default', '0', 0, $data, $pre_cache));
        // tables of plain values keyed by index
        $this->assertSame('10', YamlDiscovery::replaceValues('flat', '0', 0, $data, $pre_cache));
        // the result is not mistaken for the name:N sub-index syntax
        $data['aps'] = 'APs:{{ sum(MIB::licenses) }}';
        $this->assertSame('APs:16', YamlDiscovery::replaceValues('aps', '0', 0, $data, $pre_cache));

        // aggregating a column of the table being discovered uses all rows, not the current row
        $data['oid'] = 'MIB::table';
        $this->assertSame('16', YamlDiscovery::replaceValues('sum', '1', 0, $data, $pre_cache));

        // YamlDiscoveryDefinition passes the rows without the table level
        $this->assertSame('16', YamlDiscovery::replaceValues('sum', '1', 0, $data, $pre_cache['MIB::table']));
    }

    public function testAggregateFilters(): void
    {
        $pre_cache = ['MIB::table' => [1 => ['MIB::x' => 10], 2 => ['MIB::x' => 1], 3 => ['MIB::x' => 5.5]]];

        foreach ([
            '{{ sum(MIB::x) }}' => '16.5',
            '{{ sum(MIB::x)|round }}' => '17',
            '{{ avg(MIB::x)|round }}' => '6',
            '{{ avg(MIB::x)|round(2) }}' => '5.5',
            '{{ avg(MIB::x)|round(1)|number_format(2) }}' => '5.50',
            '{{ min(MIB::x)|round }}' => '1',
            '{{ max(MIB::x)|default(0)|round }}' => '10',
            '{{ sum(MIB::missing)|default(0)|round }}' => '0',
            '{{ sum( MIB::x )|round }}' => '17',
            '{{ sum($MIB::x)|round }}' => '17',
            '{{ sum(MIB::x)|round }} of {{ max(MIB::x) }}' => '17 of 10',
        ] as $template => $expected) {
            $data = ['oid' => 'MIB::table', 'field' => $template];
            $this->assertSame($expected, YamlDiscovery::replaceValues('field', '1', 0, $data, $pre_cache), $template);
        }
    }

    public function testAggregateDoesNotParseFiltersInsideParentheses(): void
    {
        $pre_cache = ['DELL-MM-MIB-SMIv2::dmmPSUTable' => [1 => ['DELL-MM-MIB-SMIv2::dmmPSUCapacity' => 3000]]];
        $data = ['oid' => 'DELL-MM-MIB-SMIv2::dmmPSUTable', 'high_limit' => '{{ sum(DELL-MM-MIB-SMIv2::dmmPSUCapacity|round) }}'];

        $log = Log::spy();
        $this->assertSame('', YamlDiscovery::replaceValues('high_limit', '1', 0, $data, $pre_cache));
        $log->shouldHaveReceived('debug')->with('YamlDiscovery: oid DELL-MM-MIB-SMIv2::dmmPSUCapacity|round not found')->once();
    }

    public function testUnknownTemplateFilters(): void
    {
        $this->assertSame([], YamlDiscovery::unknownFilters('{{ sum(MIB::x) }} {{ avg(MIB::x)|round(1) }} {{ $index|upper }}'));
        $this->assertSame(['foobar'], YamlDiscovery::unknownFilters('{{ max(MIB::x)|foobar }}'));
        $this->assertSame(['median'], YamlDiscovery::unknownFilters('{{ median(MIB::x) }}'));
        $this->assertSame(['sum'], YamlDiscovery::unknownFilters('{{ MIB::x|sum }}'));
        // filters inside the parentheses are part of the column name, not filters
        $this->assertSame([], YamlDiscovery::unknownFilters('{{ sum(MIB::x|foobar) }}'));
    }

    public function testReplaceValuesFiltersOtherTable(): void
    {
        $pre_cache = [
            'MIB::table' => [1 => ['MIB::value' => 3.14159]],
            'MIB::other' => [1 => ['MIB::other' => 2.71828, 'MIB::label' => 'xyz']],
        ];

        $data = [
            'oid' => 'MIB::table',
            'same' => '{{ MIB::value|round(1) }}',
            'other' => '{{ MIB::other|round(1) }}',
            'label' => '{{ MIB::label|upper }}',
            'default' => '{{ MIB::missing|default("none") }}',
        ];

        $this->assertSame('3.1', YamlDiscovery::replaceValues('same', '1', 1, $data, $pre_cache));
        $this->assertSame('2.7', YamlDiscovery::replaceValues('other', '1', 1, $data, $pre_cache));
        $this->assertSame('XYZ', YamlDiscovery::replaceValues('label', '1', 1, $data, $pre_cache));
        $this->assertSame('none', YamlDiscovery::replaceValues('default', '1', 1, $data, $pre_cache));
    }

    public function testNumberCast(): void
    {
        $this->assertSame(-14.3, Number::cast(-14.3));
        $this->assertSame(0, Number::cast('b -35')); // cast must start with the number as old style php cast did
        $this->assertSame(0, Number::cast('0 43 51'));
        $this->assertSame(14.35, Number::cast('14.35 a'));
        $this->assertSame(-43.332, Number::cast('-43.332 a'));
        $this->assertSame(-12325234523.43, Number::cast('-12325234523.43asdf'));
        $this->assertSame(1, Number::cast(1.0));
        $this->assertSame(2, Number::cast('2.000'));
    }

    public function testNumberAsUnsigned(): void
    {
        $this->assertSame(42, Number::constrainInteger('42', IntegerType::Int32));  /** @phpstan-ignore-line */
        $this->assertSame(2147483647, Number::constrainInteger(2147483647, IntegerType::Int32));
        $this->assertSame(-2147483648, Number::constrainInteger(2147483648, IntegerType::Int32));
        $this->assertSame(-2147483647, Number::constrainInteger(2147483649, IntegerType::Int32));
        $this->assertSame(-1, Number::constrainInteger(4294967295, IntegerType::Int32));
        $this->assertSame(-3757, Number::constrainInteger(61779, IntegerType::Int16));
        $this->assertSame(0, Number::constrainInteger(0, IntegerType::Uint32));
        $this->assertSame(42, Number::constrainInteger(42, IntegerType::Uint32));
        $this->assertSame(4294967252, Number::constrainInteger(-42, IntegerType::Uint32));
        $this->assertSame(2147483648, Number::constrainInteger(-2147483646, IntegerType::Uint32));
        $this->assertSame(2147483647, Number::constrainInteger(-2147483647, IntegerType::Uint32));
        $this->assertSame(2147483646, Number::constrainInteger(-2147483648, IntegerType::Uint32));
        $this->assertSame(2147483645, Number::constrainInteger(-2147483649, IntegerType::Uint32));
    }

    public function testNumberAsUnsignedValueExceedsMaxUnsignedValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Exceeds the maximum representable value for a 16-bit unsigned integer
        Number::constrainInteger(4294967296, IntegerType::Int16);
    }

    public function testPortFillMissingAndTrimRespectsIfAliasOverrideWithoutIfName(): void
    {
        $device = $this->fakeDeviceWithAttribs(['ifName:Port 1' => 1]);

        // device without ifXTable: no ifName or ifAlias, override stored under the ifName filled from ifDescr
        $port = ['ifDescr' => 'Port 1', 'ifName' => '', 'ifAlias' => ''];
        port_fill_missing_and_trim($port, $device);

        $this->assertSame('Port 1', $port['ifName']);
        $this->assertSame('Port 1', $port['ifDescr']);
        $this->assertArrayNotHasKey('ifAlias', $port);
    }

    public function testPortFillMissingAndTrimFillsIfAliasWithoutOverride(): void
    {
        $device = $this->fakeDeviceWithAttribs([]);

        $port = ['ifDescr' => 'Port 1', 'ifName' => '', 'ifAlias' => ''];
        port_fill_missing_and_trim($port, $device);

        $this->assertSame('Port 1', $port['ifName']);
        $this->assertSame('Port 1', $port['ifAlias']);
    }

    public function testPortFillMissingAndTrimRespectsIfAliasOverride(): void
    {
        $device = $this->fakeDeviceWithAttribs(['ifName:Gi0/1' => 1]);

        $port = ['ifDescr' => 'GigabitEthernet0/1', 'ifName' => 'Gi0/1', 'ifAlias' => ''];
        port_fill_missing_and_trim($port, $device);

        $this->assertSame('Gi0/1', $port['ifName']);
        $this->assertArrayNotHasKey('ifAlias', $port);
    }

    private function fakeDeviceWithAttribs(array $attribs): array
    {
        $device = new Device(['hostname' => 'test.example.com']);
        $device->device_id = 1;
        $device->setRelation('attribs', collect($attribs)->map(fn ($value, $type) => new DeviceAttrib([
            'attrib_type' => $type,
            'attrib_value' => $value,
        ]))->values());
        DeviceCache::fake($device);

        return ['device_id' => $device->device_id];
    }
}
