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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace LibreNMS\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class AlertingTest extends TestCase
{
    public function testJsonAlertCollection()
    {
        $rules = get_rules_from_json();
        $this->assertIsArray($rules);
        foreach ($rules as $rule) {
            $this->assertIsArray($rule);
        }
    }

    public function testTransports()
    {
        foreach ($this->getTransportFiles() as $file => $_unused) {
            $parts = explode('/', $file);
            $transport = ucfirst(str_replace('.php', '', array_pop($parts)));
            $class = 'LibreNMS\\Alert\\Transport\\' . $transport;
            if (! class_exists($class)) {
                $this->assertTrue(false, "The transport $transport does not exist");
            } else {
                $methods = ['deliverAlert', 'configTemplate', 'contact' . $transport];
                foreach ($methods as $method) {
                    if (! method_exists($class, $method)) {
                        $this->assertTrue(false, "The transport $transport does not have the method $method");
                    }
                }
            }
        }

        $this->expectNotToPerformAssertions();
    }

    private function getTransportFiles()
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('LibreNMS/Alert/Transport'));

        return new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    }
}
