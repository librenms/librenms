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

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use LibreNMS\RRD\Backend\Rrdtool;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Tests\TestCase;
use Mockery;

final class RrdtoolTest extends TestCase
{
    /** @var string[] */
    private array $commands = [];
    private RrdProcess $process;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('rrdcached', '');
        LibrenmsConfig::set('rrd_dir', '/opt/librenms/rrd');
        LibrenmsConfig::set('rrd.heartbeat', 600);

        $process = Mockery::mock(RrdProcess::class);
        $process->shouldReceive('run')->andReturnUsing(function (string $command) {
            $this->commands[] = $command;

            return '';
        });
        $process->shouldReceive('stop');
        $this->process = $process;
    }

    public function testCreateAddsNoOverwrite(): void
    {
        $this->backend()->create(RrdPath::make('host', 'f.rrd'), $this->definition()->setRras(['RRA:AVERAGE:0.5:1:10']));

        $this->assertSame(['create /opt/librenms/rrd/host/f.rrd --step 300 DS:a:GAUGE:600:U:U RRA:AVERAGE:0.5:1:10 -O'], $this->commands);
    }

    public function testUpdateAndTune(): void
    {
        $rrd = RrdPath::make('host', 'f.rrd');

        $this->backend()->update($rrd, [1, null, 'x', 2.5]);
        $this->backend()->update($rrd, [3], 1700000000);
        $this->backend()->tune($rrd, ['a' => ['max' => 100], 'b' => ['min' => 0, 'max' => null]]);

        $this->assertSame([
            'update /opt/librenms/rrd/host/f.rrd N:1:U:U:2.5',
            'update /opt/librenms/rrd/host/f.rrd 1700000000:3',
            'tune /opt/librenms/rrd/host/f.rrd --maximum a:100 --minimum b:0 --maximum b:U',
        ], $this->commands);
    }

    private function backend(): Rrdtool
    {
        return new Rrdtool(null, $this->process);
    }

    private function definition(): RrdDefinition
    {
        return RrdDefinition::make()->addDataset('a', 'GAUGE')->setStep(300)->setRras([]);
    }
}
