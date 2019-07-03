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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Config;

class RrdtoolTest extends TestCase
{

    public function testBuildCommandLocal()
    {
        Config::set('rrdcached', '');
        Config::set('rrdtool_version', '1.4');
        Config::set('rrd_dir', '/opt/librenms/rrd');

        $cmd = rrdtool_build_command('create', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('create /opt/librenms/rrd/f o', $cmd);

        $cmd = rrdtool_build_command('tune', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('tune /opt/librenms/rrd/f o', $cmd);

        $cmd = rrdtool_build_command('update', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('update /opt/librenms/rrd/f o', $cmd);


        Config::set('rrdtool_version', '1.6');

        $cmd = rrdtool_build_command('create', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('create /opt/librenms/rrd/f o -O', $cmd);

        $cmd = rrdtool_build_command('tune', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('tune /opt/librenms/rrd/f o', $cmd);

        $cmd = rrdtool_build_command('update', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('update /opt/librenms/rrd/f o', $cmd);
    }

    public function testBuildCommandRemote()
    {
        Config::set('rrdcached', 'server:42217');
        Config::set('rrdtool_version', '1.4');
        Config::set('rrd_dir', '/opt/librenms/rrd');

        $cmd = rrdtool_build_command('create', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('create /opt/librenms/rrd/f o', $cmd);

        $cmd = rrdtool_build_command('tune', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('tune /opt/librenms/rrd/f o', $cmd);

        $cmd = rrdtool_build_command('update', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('update f o --daemon server:42217', $cmd);


        Config::set('rrdtool_version', '1.6');

        $cmd = rrdtool_build_command('create', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('create f o -O --daemon server:42217', $cmd);

        $cmd = rrdtool_build_command('tune', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('tune f o --daemon server:42217', $cmd);

        $cmd = rrdtool_build_command('update', '/opt/librenms/rrd/f', 'o');
        $this->assertEquals('update f o --daemon server:42217', $cmd);
    }

    public function testBuildCommandException()
    {
        Config::set('rrdcached', '');
        Config::set('rrdtool_version', '1.4');

        $this->expectException('LibreNMS\Exceptions\FileExistsException');
        // use this file, since it is guaranteed to exist
        rrdtool_build_command('create', __FILE__, 'o');
    }
}
