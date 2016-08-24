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

class RrdtoolTest extends \PHPUnit_Framework_TestCase
{

    public function testBuildCommandLocal()
    {
        global $config;
        $config['rrdcached'] = '';
        $config['rrdtool_version'] = '1.4';
        $config['rrd_dir'] = '/opt/librenms/rrd';

        $file = '/opt/librenms/rrd/f';
        $options = 'DEF:time_o=/opt/librenms/rrd/overmind/agent.rrd:time:AVERAGE CDEF:time=time_o,1000,/';


        $cmd = rrdtool_build_command('create', $file, $options);
        $this->assertEquals("create $file $options", $cmd);
        
        $cmd = rrdtool_build_command('tune', $file, $options);
        $this->assertEquals("tune $file $options", $cmd);
        
        $cmd = rrdtool_build_command('update', $file, $options);
        $this->assertEquals("update $file $options", $cmd);


        $config['rrdtool_version'] = '1.6';

        $cmd = rrdtool_build_command('create', $file, $options);
        $this->assertEquals("create $file $options -O", $cmd);

        $cmd = rrdtool_build_command('tune', $file, $options);
        $this->assertEquals("tune $file $options", $cmd);

        $cmd = rrdtool_build_command('update', $file, $options);
        $this->assertEquals("update $file $options", $cmd);
    }

    public function testBuildCommandRemote()
    {
        global $config;
        $config['rrdcached'] = 'server:42217';
        $config['rrdtool_version'] = '1.4';
        $config['rrd_dir'] = '/opt/librenms/rrd';

        $file = '/opt/librenms/rrd/f';
        $expected_file = 'f';
        $options = 'DEF:time_o=/opt/librenms/rrd/overmind/agent.rrd:time:AVERAGE CDEF:time=time_o,1000,/';
        $expected_options = 'DEF:time_o=overmind/agent.rrd:time:AVERAGE CDEF:time=time_o,1000,/';

        $cmd = rrdtool_build_command('create', $file, $options);
        $this->assertEquals("create $file $options", $cmd);

        $cmd = rrdtool_build_command('tune', $file, $options);
        $this->assertEquals("tune $file $options", $cmd);

        $cmd = rrdtool_build_command('update', $file, $options);
        $this->assertEquals("update $expected_file $expected_options --daemon server:42217", $cmd);


        $config['rrdtool_version'] = '1.6';

        $cmd = rrdtool_build_command('create', $file, $options);
        $this->assertEquals("create $expected_file $expected_options -O --daemon server:42217", $cmd);

        $cmd = rrdtool_build_command('tune', $file, $options);
        $this->assertEquals("tune $expected_file $expected_options --daemon server:42217", $cmd);

        $cmd = rrdtool_build_command('update', $file, $options);
        $this->assertEquals("update $expected_file $expected_options --daemon server:42217", $cmd);

    }

    public function testBuildCommandException()
    {
        global $config;
        $config['rrdcached'] = '';
        $config['rrdtool_version'] = '1.4';

        $this->setExpectedException('LibreNMS\Exceptions\FileExistsException');
        // use this file, since it is guaranteed to exist
        rrdtool_build_command('create', __FILE__, 'o');
    }

}
