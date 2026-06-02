<?php

/**
 * AlertingTest.php
 *
 * Tests for alerting functionality.
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace LibreNMS\Tests;

use LibreNMS\Alert\AlertUtil;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

final class AlertingTest extends TestCase
{
    public function testJsonAlertCollection(): void
    {
        $rules = get_rules_from_json();
        $this->assertIsArray($rules);
        foreach ($rules as $rule) {
            $this->assertIsArray($rule);
        }
    }

    public function testExtractIdFieldsForFault(): void
    {
        $fields = AlertUtil::extractIdFieldsForFault([
            'id' => 9,
            'port_id' => 5,
            'device_id' => 1,
            'location_id' => 2,
            'ifName' => 'eth0',
        ]);

        $this->assertContains('id', $fields);
        $this->assertContains('port_id', $fields);
        $this->assertContains('device_id', $fields);
        // location_id is intentionally excluded, and non-id fields are not considered.
        $this->assertNotContains('location_id', $fields);
        $this->assertNotContains('ifName', $fields);
    }

    public function testGenerateComparisonKeyForFault(): void
    {
        $row = ['device_id' => 1, 'port_id' => 5, 'ifName' => 'eth0'];
        $key = AlertUtil::generateComparisonKeyForFault($row, AlertUtil::extractIdFieldsForFault($row));

        // The same entity always yields the same dedupe key, distinct from other ports.
        $this->assertSame('1|5', $key);
        $this->assertNotSame($key, AlertUtil::generateComparisonKeyForFault(
            ['device_id' => 1, 'port_id' => 6],
            AlertUtil::extractIdFieldsForFault(['device_id' => 1, 'port_id' => 6])
        ));
    }

    public function testEntityForFault(): void
    {
        // Known columns map to the registered morph aliases.
        $this->assertSame(['interface', 5], AlertUtil::entityForFault(['device_id' => 1, 'port_id' => 5]));
        $this->assertSame(['sensor', 9], AlertUtil::entityForFault(['device_id' => 1, 'sensor_id' => 9]));
        // Unknown *_id columns fall back to the stripped column name.
        $this->assertSame(['bgpPeer', 7], AlertUtil::entityForFault(['device_id' => 1, 'bgpPeer_id' => 7]));
        // A device-level row (no entity id) resolves to no specific entity.
        $this->assertSame([null, null], AlertUtil::entityForFault(['device_id' => 1, 'ifName' => 'eth0']));
    }

    public function testTransports(): void
    {
        foreach ($this->getTransportFiles() as $file => $_unused) {
            $parts = explode('/', (string) $file);
            $transport = ucfirst(str_replace('.php', '', array_pop($parts)));
            $class = 'LibreNMS\\Alert\\Transport\\' . $transport;
            $this->assertTrue(class_exists($class), "The transport $transport does not exist");
            $this->assertInstanceOf(\LibreNMS\Interfaces\Alert\Transport::class, new $class);
        }
    }

    private function getTransportFiles(): RegexIterator
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('LibreNMS/Alert/Transport'));

        return new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);
    }
}
