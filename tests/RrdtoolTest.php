<?php

/**
 * RrdtoolTest.php
 *
 * Tests functionality of our rrdtool wrapper
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdProcess;
use Psr\Log\NullLogger;

final class RrdtoolTest extends TestCase
{
    public function testBuildCommandLocal(): void
    {
        LibrenmsConfig::set('rrdcached', '');
        LibrenmsConfig::set('rrdtool_version', '1.4');
        LibrenmsConfig::set('rrd_dir', '/opt/librenms/rrd');

        $cmd = $this->buildCommandProxy('create', '/opt/librenms/rrd/f', ['o']);
        $this->assertEquals(['create', '/opt/librenms/rrd/f', 'o'], $cmd);

        $cmd = $this->buildCommandProxy('tune', '/opt/librenms/rrd/f', ['o']);
        $this->assertEquals(['tune', '/opt/librenms/rrd/f', 'o'], $cmd);

        $cmd = $this->buildCommandProxy('update', '/opt/librenms/rrd/f', ['o']);
        $this->assertEquals(['update', '/opt/librenms/rrd/f', 'o'], $cmd);

        LibrenmsConfig::set('rrdtool_version', '1.6');

        $cmd = $this->buildCommandProxy('create', '/opt/librenms/rrd/f', ['o']);
        $this->assertEquals(['create', '/opt/librenms/rrd/f', 'o', '-O'], $cmd);

        $cmd = $this->buildCommandProxy('tune', '/opt/librenms/rrd/f', ['o']);
        $this->assertEquals(['tune', '/opt/librenms/rrd/f', 'o'], $cmd);

        $cmd = $this->buildCommandProxy('update', '/opt/librenms/rrd/f', ['options']);
        $this->assertEquals(['update', '/opt/librenms/rrd/f', 'options'], $cmd);
    }

    public function testBuildCommandException(): void
    {
        LibrenmsConfig::set('rrdcached', '');
        LibrenmsConfig::set('rrdtool_version', '1.4');

        $this->expectException(\LibreNMS\Exceptions\RrdFileExistsException::class);
        // use this file, since it is guaranteed to exist
        $this->buildCommandProxy('create', __FILE__, ['o']);
    }

    public function testRealpathOnStderrThrowsRrdNotFound(): void
    {
        // rrdtool reports a missing file on stderr as "realpath(...): No such file or directory"
        $this->runWithFakeRrdtool(
            '<?php fwrite(STDERR, "realpath(/192.168.2.8/x.rrd): No such file or directory\n");',
            function (RrdProcess $process): void {
                $this->expectException(RrdNotFoundException::class);
                $process->run('last /192.168.2.8/x.rrd');
            }
        );
    }

    public function testOtherStderrThrowsRrdException(): void
    {
        $this->runWithFakeRrdtool(
            '<?php fwrite(STDERR, "some other fatal error\n");',
            function (RrdProcess $process): void {
                try {
                    $process->run('last /192.168.2.8/x.rrd');
                    $this->fail('Expected RrdException');
                } catch (RrdException $e) {
                    $this->assertNotInstanceOf(RrdNotFoundException::class, $e);
                }
            }
        );
    }

    private function runWithFakeRrdtool(string $script, callable $test): void
    {
        $rrdDir = sys_get_temp_dir() . '/librenms-rrdprocess-' . bin2hex(random_bytes(6));
        mkdir($rrdDir);
        $rrdtool = $rrdDir . '/rrdtool';
        file_put_contents($rrdtool, "#!/usr/bin/env php\n" . $script);
        chmod($rrdtool, 0755);

        LibrenmsConfig::set('rrdcached', '');
        LibrenmsConfig::set('rrd_dir', $rrdDir);
        LibrenmsConfig::set('rrdtool', $rrdtool);

        $process = new RrdProcess(new NullLogger(), 1);

        try {
            $test($process);
        } finally {
            $process->stop();
            unlink($rrdtool);
            rmdir($rrdDir);
        }
    }

    private function buildCommandProxy(string $command, string $filename, array $options): array
    {
        $mock = $this->mock(Rrd::class)->makePartial(); // avoid constructor
        // @phpstan-ignore method.protected
        $mock->loadConfig(); // load config every time to clear cached settings

        return $mock->buildCommand($command, $filename, $options);
    }
}
