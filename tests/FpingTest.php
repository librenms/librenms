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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Data\Source\Fping;
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

        $this->assertTrue($actual->success());
        $this->assertEquals(3, $actual->transmitted);
        $this->assertEquals(3, $actual->received);
        $this->assertEquals(0, $actual->loss);
        $this->assertEquals(0.62, $actual->min_latency);
        $this->assertEquals(0.93, $actual->max_latency);
        $this->assertEquals(0.71, $actual->avg_latency);
        $this->assertEquals(0, $actual->duplicates);
        $this->assertEquals(0, $actual->exit_code);
    }

    public function testPartialDownPing()
    {
        $output = "192.168.1.7 : xmt/rcv/%loss = 5/3/40%, min/avg/max = 0.13/0.23/0.32\n";
        $this->mockFpingProcess($output, 0);

        $actual = app()->make(Fping::class)->ping('192.168.1.7');

        $this->assertTrue($actual->success());
        $this->assertEquals(5, $actual->transmitted);
        $this->assertEquals(3, $actual->received);
        $this->assertEquals(40, $actual->loss);
        $this->assertEquals(0.13, $actual->min_latency);
        $this->assertEquals(0.32, $actual->max_latency);
        $this->assertEquals(0.23, $actual->avg_latency);
        $this->assertEquals(0, $actual->duplicates);
        $this->assertEquals(0, $actual->exit_code);
    }

    public function testDownPing()
    {
        $output = "192.168.53.1 : xmt/rcv/%loss = 3/0/100%\n";
        $this->mockFpingProcess($output, 1);

        $actual = app()->make(Fping::class)->ping('192.168.53.1');

        $this->assertFalse($actual->success());
        $this->assertEquals(3, $actual->transmitted);
        $this->assertEquals(0, $actual->received);
        $this->assertEquals(100, $actual->loss);
        $this->assertEquals(0.0, $actual->min_latency);
        $this->assertEquals(0.0, $actual->max_latency);
        $this->assertEquals(0.0, $actual->avg_latency);
        $this->assertEquals(0, $actual->duplicates);
        $this->assertEquals(1, $actual->exit_code);
    }

    public function testDuplicatePing()
    {
        $output = <<<'OUT'
192.168.1.2 : duplicate for [0], 84 bytes, 0.91 ms
192.168.1.2 : duplicate for [0], 84 bytes, 0.95 ms
192.168.1.2 : xmt/rcv/%loss = 3/3/0%, min/avg/max = 0.68/0.79/0.91
OUT;

        $this->mockFpingProcess($output, 1);

        $actual = app()->make(Fping::class)->ping('192.168.1.2');

        $this->assertFalse($actual->success());
        $this->assertEquals(3, $actual->transmitted);
        $this->assertEquals(3, $actual->received);
        $this->assertEquals(0, $actual->loss);
        $this->assertEquals(0.68, $actual->min_latency);
        $this->assertEquals(0.91, $actual->max_latency);
        $this->assertEquals(0.79, $actual->avg_latency);
        $this->assertEquals(2, $actual->duplicates);
        $this->assertEquals(1, $actual->exit_code);
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
