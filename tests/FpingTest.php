<?php
/**
 * FpingTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Fping;
use Symfony\Component\Process\Process;

class FpingTest extends TestCase
{
    public function testUpPing()
    {
        $output = "192.168.1.3 : xmt/rcv/%loss = 3/3/0%, min/avg/max = 0.62/0.71/0.93\n";
        $this->mockFpingProcess($output, 0);

        $expected = [
            'xmt' => 3,
            'rcv' => 3,
            'loss' => 0,
            'min' => 0.62,
            'max' => 0.93,
            'avg' => 0.71,
            'dup' => 0,
            'exitcode' => 0,
        ];

        $actual = app()->make(Fping::class)->ping('192.168.1.3');

        $this->assertSame($expected, $actual);
    }

    public function testPartialDownPing()
    {
        $output = "192.168.1.7 : xmt/rcv/%loss = 5/3/40%, min/avg/max = 0.13/0.23/0.32\n";
        $this->mockFpingProcess($output, 0);

        $expected = [
            'xmt' => 5,
            'rcv' => 3,
            'loss' => 40,
            'min' => 0.13,
            'max' => 0.32,
            'avg' => 0.23,
            'dup' => 0,
            'exitcode' => 0,
        ];

        $actual = app()->make(Fping::class)->ping('192.168.1.7');

        $this->assertSame($expected, $actual);
    }

    public function testDownPing()
    {
        $output = "192.168.53.1 : xmt/rcv/%loss = 3/0/100%\n";
        $this->mockFpingProcess($output, 1);

        $expected = [
            'xmt' => 3,
            'rcv' => 0,
            'loss' => 100,
            'min' => 0.0,
            'max' => 0.0,
            'avg' => 0.0,
            'dup' => 0,
            'exitcode' => 1,
        ];

        $actual = app()->make(Fping::class)->ping('192.168.53.1');

        $this->assertSame($expected, $actual);
    }

    public function testDuplicatePing()
    {
        $output = <<<'OUT'
192.168.1.2 : duplicate for [0], 84 bytes, 0.91 ms
192.168.1.2 : duplicate for [0], 84 bytes, 0.95 ms
192.168.1.2 : xmt/rcv/%loss = 3/3/0%, min/avg/max = 0.68/0.79/0.91
OUT;

        $this->mockFpingProcess($output, 1);

        $expected = [
            'xmt' => 3,
            'rcv' => 3,
            'loss' => 0,
            'min' => 0.68,
            'max' => 0.91,
            'avg' => 0.79,
            'dup' => 2,
            'exitcode' => 1,
        ];

        $actual = app()->make(Fping::class)->ping('192.168.1.2');

        $this->assertSame($expected, $actual);
    }

    private function mockFpingProcess($output, $exitCode)
    {
        $process = \Mockery::mock(Process::class);
        $process->shouldReceive('getCommandLine', 'run');
        $process->shouldReceive('getErrorOutput')->andReturn($output);
        $process->shouldReceive('getExitCode')->andReturn($exitCode);

        $this->app->bind(Process::class, function ($app, $params) use ($process) {
            return $process;
        });

        return $process;
    }
}
