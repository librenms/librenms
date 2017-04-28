<?php
/**
 * RrdDefinitonTest.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\RRD\RrdDefinition;

class RrdDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {

        $this->assertEmpty((string)new RrdDefinition());
    }

    /**
     * @expectedException \LibreNMS\Exceptions\InvalidRrdTypeException
     */
    public function testWrongType()
    {
        global $config;
        $config['rrd']['step'] = 300;
        $config['rrd']['heartbeat'] = 600;
        $def = new RrdDefinition();
        $def->addDataset('badtype', 'Something unexpected');
    }

    public function testNameEscaping()
    {
        global $config;
        $config['rrd']['step'] = 300;
        $config['rrd']['heartbeat'] = 600;
        $expected = 'DS:bad_name-is_too_lon:GAUGE:600:0:100 ';
        $def = RrdDefinition::make()->addDataset('b a%d$_n:a^me-is_too_lon%g.', 'GAUGE', 0, 100, 600);

        $this->assertEquals($expected, (string)$def);
    }

    public function testCreation()
    {
        global $config;
        $config['rrd']['step'] = 300;
        $config['rrd']['heartbeat'] = 600;
        $expected = 'DS:pos:COUNTER:600:0:125000000000 ' .
                    'DS:unbound:DERIVE:600:U:U ';

        $def = new RrdDefinition();
        $def->addDataset('pos', 'COUNTER', 0, 125000000000);
        $def->addDataset('unbound', 'DERIVE');

        $this->assertEquals($expected, $def);
    }

    public function testEmptyData()
    {
        $rrd_def = RrdDefinition::make()
            ->addDataset('one', 'GAUGE')
            ->addDataset('two', 'COUNTER');

        $this->assertSame(array('one' => 'U', 'two' => 'U'), $rrd_def->getData());

        $rrd_def = RrdDefinition::make();
        $rrd_def->setValue('something', 1);
        $this->assertSame(array(), $rrd_def->getData());

        $rrd_def = RrdDefinition::make();
        $rrd_def->setData(array('else' => 1));
        $this->assertSame(array(), $rrd_def->getData());
    }

    public function testGetData()
    {
        $expected = array('one' => 234, 'two' => 543);
        $rrd_def = RrdDefinition::make()
            ->addDataset('one', 'GAUGE')
            ->addDataset('two', 'COUNTER');

        $rrd_def->setValue('one', 234);
        $rrd_def->setValue('two', 543);

        $this->assertSame($expected, $rrd_def->getData());
    }

    public function testSetValue()
    {
        $rrd_def = RrdDefinition::make()->addDataset('non-existent', 'GAUGE');
        $rrd_def->setValue('something-else', 1);
        $rrd_def->setValue(100, 1);
        $this->assertSame(array('non-existent' => 'U'), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('int', 'GAUGE');
        $rrd_def->setValue('int', 1337);
        $this->assertSame(array('int' => 1337), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('float', 'GAUGE');
        $rrd_def->setValue('float', 1.42);
        $this->assertSame(array('float' => 1.42), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('numeric', 'GAUGE');
        $rrd_def->setValue('numeric', '1024.42');
        $this->assertSame(array('numeric' => 1024.42), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('string', 'GAUGE');
        $rrd_def->setValue('string', 'string');
        $this->assertSame(array('string' => 'U'), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('negative', 'GAUGE');
        $rrd_def->setValue('negative', -42);
        $this->assertSame(array('negative' => -42), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('null', 'GAUGE');
        $rrd_def->setValue('null', null);
        $this->assertSame(array('null' => 'U'), $rrd_def->getData());

        $rrd_def = RrdDefinition::make()->addDataset('numeric_index', 'GAUGE');
        $rrd_def->setValue(0, 1);
        $this->assertSame(array('numeric_index' => 1), $rrd_def->getData());
    }

    public function testSetData()
    {
        $expected = array('one' => 1, 'two' => 2, 'three' => 3);
        $rrd_def = RrdDefinition::make()
            ->addDataset('one', 'GAUGE')
            ->addDataset('two', 'COUNTER')
            ->addDataset('three', 'COUNTER');
        $rrd_def->setData($expected);
        $this->assertSame($expected, $rrd_def->getData());

        $rrd_def = RrdDefinition::make()
            ->addDataset('one', 'GAUGE')
            ->addDataset('two', 'COUNTER')
            ->addDataset('three', 'COUNTER');

        $rrd_def->setData(array('three' => 3, 'one' => 1));
        $this->assertSame(array('one' => 1, 'two' => 'U', 'three' => 3), $rrd_def->getData());

        $rrd_def->setData(array(1 => 2));
        $this->assertSame($expected, $rrd_def->getData());

        $rrd_def = RrdDefinition::make()
            ->addDataset('one', 'GAUGE')
            ->addDataset('two', 'COUNTER')
            ->addDataset('three', 'COUNTER');

        $rrd_def->setData(array_values($expected));
        $this->assertSame($expected, $rrd_def->getData());
    }
}
