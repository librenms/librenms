<?php
/**
 * ProxyTest.php
 *
 * Tests Util\Proxy classes
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
 */

namespace LibreNMS\Tests;

use LibreNMS\Util\Proxy;

class ProxyTest extends TestCase
{
    public function testShouldBeUsed(): void
    {
        $this->assertTrue(Proxy::shouldBeUsed('http://example.com/foobar'));
        $this->assertTrue(Proxy::shouldBeUsed('foo/bar'));
        $this->assertTrue(Proxy::shouldBeUsed('192.168.0.1'));
        $this->assertTrue(Proxy::shouldBeUsed('2001:db8::8a2e:370:7334'));

        $this->assertFalse(Proxy::shouldBeUsed('http://localhost/foobar'));
        $this->assertFalse(Proxy::shouldBeUsed('localhost/foobar'));
        $this->assertFalse(Proxy::shouldBeUsed('127.0.0.1'));
        $this->assertFalse(Proxy::shouldBeUsed('127.0.0.1:1337'));
        $this->assertFalse(Proxy::shouldBeUsed('::1'));
    }
}
