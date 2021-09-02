<?php
/**
 * ConfigTest.php
 *
 * Tests for LibreNMS\Config
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Config;

class ConfigTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new \ReflectionProperty(Config::class, 'config');
        $this->config->setAccessible(true);
    }

    public function testGetBasic()
    {
        $dir = realpath(__DIR__ . '/..');
        $this->assertEquals($dir, Config::get('install_dir'));
    }

    public function testSetBasic()
    {
        Config::set('basics', 'first');
        $this->assertEquals('first', $this->config->getValue()['basics']);
    }

    public function testGet()
    {
        $this->setConfig(function (&$config) {
            $config['one']['two']['three'] = 'easy';
        });

        $this->assertEquals('easy', Config::get('one.two.three'));
    }

    public function testGetDeviceSetting()
    {
        $device = ['set' => true, 'null' => null];
        $this->setConfig(function (&$config) {
            $config['null'] = 'notnull!';
            $config['noprefix'] = true;
            $config['prefix']['global'] = true;
        });

        $this->assertNull(Config::getDeviceSetting($device, 'unset'), 'Non-existing settings should return null');
        $this->assertTrue(Config::getDeviceSetting($device, 'set'), 'Could not get setting from device array');
        $this->assertTrue(Config::getDeviceSetting($device, 'noprefix'), 'Failed to get setting from global config');
        $this->assertEquals(
            'notnull!',
            Config::getDeviceSetting($device, 'null'),
            'Null variables should defer to the global setting'
        );
        $this->assertTrue(
            Config::getDeviceSetting($device, 'global', 'prefix'),
            'Failed to get setting from global config with a prefix'
        );
        $this->assertEquals(
            'default',
            Config::getDeviceSetting($device, 'something', 'else', 'default'),
            'Failed to return the default argument'
        );
    }

    public function testGetOsSetting()
    {
        $this->setConfig(function (&$config) {
            $config['os']['nullos']['fancy'] = true;
            $config['fallback'] = true;
        });

        $this->assertNull(Config::getOsSetting(null, 'unset'), '$os is null, should return null');
        $this->assertNull(Config::getOsSetting('nullos', 'unset'), 'Non-existing settings should return null');
        $this->assertFalse(Config::getOsSetting('nullos', 'unset', false), 'Non-existing settings should return $default');
        $this->assertTrue(Config::getOsSetting('nullos', 'fancy'), 'Failed to get setting');
        $this->assertNull(Config::getOsSetting('nullos', 'fallback'), 'Incorrectly loaded global setting');
    }

    public function testGetCombined()
    {
        $this->setConfig(function (&$config) {
            $config['num'] = ['one', 'two'];
            $config['os']['nullos']['num'] = ['two', 'three'];
            $config['assoc'] = ['a' => 'same', 'b' => 'same'];
            $config['os']['nullos']['assoc'] = ['b' => 'different', 'c' => 'still same'];
            $config['os']['nullos']['osset'] = true;
            $config['gset'] = true;
        });

        $this->assertTrue(Config::getCombined('nullos', 'non-existent', true), 'Did not return default value on non-existent key');
        $this->assertTrue(Config::getCombined('nullos', 'osset', false), 'Did not return OS value when global value is not set');
        $this->assertTrue(Config::getCombined('nullos', 'gset', false), 'Did not return global value when OS value is not set');

        $combined = Config::getCombined('nullos', 'num');
        sort($combined);
        $this->assertEquals(['one', 'three', 'two'], $combined);

        $this->assertSame(['a' => 'same', 'b' => 'different', 'c' => 'still same'], Config::getCombined('nullos', 'assoc'));
    }

    public function testSet()
    {
        Config::set('you.and.me', "I'll be there");

        $this->assertEquals("I'll be there", $this->config->getValue()['you']['and']['me']);
    }

    public function testSetPersist()
    {
        $this->dbSetUp();

        $key = 'testing.persist';

        $query = \App\Models\Config::query()->where('config_name', $key);

        $query->delete();
        $this->assertFalse($query->exists(), "$key should not be set, clean database");
        Config::persist($key, 'one');
        $this->assertEquals('one', $query->value('config_value'));
        Config::persist($key, 'two');
        $this->assertEquals('two', $query->value('config_value'));

        $this->dbTearDown();
    }

    public function testHas()
    {
        Config::set('long.key.setting', 'no one cares');
        Config::set('null', null);

        $this->assertFalse(Config::has('null'), 'Keys set to null do not count as existing');
        $this->assertTrue(Config::has('long'), 'Top level key should exist');
        $this->assertTrue(Config::has('long.key.setting'), 'Exact exists on value');
        $this->assertFalse(Config::has('long.key.setting.nothing'), 'Non-existent child setting');

        $this->assertFalse(Config::has('off.the.wall'), 'Non-existent key');
        $this->assertFalse(Config::has('off.the'), 'Config:has() should not modify the config');
    }

    public function testGetNonExistent()
    {
        $this->assertNull(Config::get('There.is.no.way.this.is.a.key'));
        $this->assertFalse(Config::has('There.is.no'));  // should not add kes when getting
    }

    public function testGetNonExistentNested()
    {
        $this->assertNull(Config::get('cheese.and.bologna'));
    }

    public function testGetSubtree()
    {
        Config::set('words.top', 'August');
        Config::set('words.mid', 'And Everything');
        Config::set('words.bot', 'After');
        $expected = [
            'top' => 'August',
            'mid' => 'And Everything',
            'bot' => 'After',
        ];

        $this->assertEquals($expected, Config::get('words'));
    }

    /**
     * Pass an anonymous function which will be passed the config variable to modify before it is set
     * @param callable $function
     */
    private function setConfig($function)
    {
        $config = $this->config->getValue();
        $function($config);
        $this->config->setValue($config);
    }

    public function testForget()
    {
        Config::set('forget.me', 'now');
        $this->assertTrue(Config::has('forget.me'));

        Config::forget('forget.me');
        $this->assertFalse(Config::has('forget.me'));
    }

    public function testForgetSubtree()
    {
        Config::set('forget.me.sub', 'yep');
        $this->assertTrue(Config::has('forget.me.sub'));

        Config::forget('forget.me');
        $this->assertFalse(Config::has('forget.me.sub'));
    }
}
