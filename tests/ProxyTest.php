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

use LibreNMS\Config;
use LibreNMS\Util\Http;
use LibreNMS\Util\Version;

class ProxyTest extends TestCase
{
    public function testClientAgentIsCorrect(): void
    {
        $this->assertEquals('LibreNMS/' . Version::VERSION, Http::client()->getOptions()['headers']['User-Agent']);
    }

    public function testProxyIsNotSet(): void
    {
        Config::set('http_proxy', '');
        Config::set('https_proxy', '');
        Config::set('no_proxy', '');
        $client_options = Http::client()->getOptions();
        $this->assertEmpty($client_options['proxy']['http']);
        $this->assertEmpty($client_options['proxy']['https']);
        $this->assertEmpty($client_options['proxy']['no']);
    }

    public function testProxyIsSet(): void
    {
        Config::set('http_proxy', 'http://proxy:5000');
        Config::set('https_proxy', 'tcp://proxy:5183');
        Config::set('no_proxy', 'localhost,127.0.0.1,::1,.domain.com');
        $client_options = Http::client()->getOptions();
        $this->assertEquals('http://proxy:5000', $client_options['proxy']['http']);
        $this->assertEquals('tcp://proxy:5183', $client_options['proxy']['https']);
        $this->assertEquals([
            'localhost',
            '127.0.0.1',
            '::1',
            '.domain.com',
        ], $client_options['proxy']['no']);
    }

    public function testProxyIsSetFromEnv(): void
    {
        Config::set('http_proxy', '');
        Config::set('https_proxy', '');
        Config::set('no_proxy', '');

        putenv('HTTP_PROXY=someproxy:3182');
        putenv('HTTPS_PROXY=https://someproxy:3182');
        putenv('NO_PROXY=.there.com');

        $client_options = Http::client()->getOptions();
        $this->assertEquals('someproxy:3182', $client_options['proxy']['http']);
        $this->assertEquals('https://someproxy:3182', $client_options['proxy']['https']);
        $this->assertEquals([
            '.there.com',
        ], $client_options['proxy']['no']);

        putenv('http_proxy=otherproxy:3182');
        putenv('https_proxy=otherproxy:3183');
        putenv('no_proxy=dontproxymebro');

        $client_options = Http::client()->getOptions();
        $this->assertEquals('otherproxy:3182', $client_options['proxy']['http']);
        $this->assertEquals('otherproxy:3183', $client_options['proxy']['https']);
        $this->assertEquals([
            'dontproxymebro',
        ], $client_options['proxy']['no']);
    }
}
