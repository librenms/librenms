<?php

/**
 * ConfigTest.php
 *
 * Tests for App\Facades\Config
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\ConfigRepository;
use App\Facades\LibrenmsConfig;

final class ConfigTest extends TestCase
{
    private \ReflectionProperty $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new \ReflectionProperty(ConfigRepository::class, 'config');
    }

    public function testGetBasic(): void
    {
        $dir = realpath(__DIR__ . '/..');
        $this->assertEquals($dir, LibrenmsConfig::get('install_dir'));
    }

    public function testSetBasic(): void
    {
        $instance = $this->app->make('librenms-config');
        LibrenmsConfig::set('basics', 'first');
        $this->assertEquals('first', $this->config->getValue($instance)['basics']);
    }

    public function testGet(): void
    {
        $this->setConfig(function (&$config): void {
            $config['one']['two']['three'] = 'easy';
        });

        $this->assertEquals('easy', LibrenmsConfig::get('one.two.three'));
    }

    public function testGetDeviceSetting(): void
    {
        $device = ['set' => true, 'null' => null];
        $this->setConfig(function (&$config): void {
            $config['null'] = 'notnull!';
            $config['noprefix'] = true;
            $config['prefix']['global'] = true;
        });

        $this->assertNull(LibrenmsConfig::getDeviceSetting($device, 'unset'), 'Non-existing settings should return null');
        $this->assertTrue(LibrenmsConfig::getDeviceSetting($device, 'set'), 'Could not get setting from device array');
        $this->assertTrue(LibrenmsConfig::getDeviceSetting($device, 'noprefix'), 'Failed to get setting from global config');
        $this->assertEquals(
            'notnull!',
            LibrenmsConfig::getDeviceSetting($device, 'null'),
            'Null variables should defer to the global setting'
        );
        $this->assertTrue(
            LibrenmsConfig::getDeviceSetting($device, 'global', 'prefix'),
            'Failed to get setting from global config with a prefix'
        );
        $this->assertEquals(
            'default',
            LibrenmsConfig::getDeviceSetting($device, 'something', 'else', 'default'),
            'Failed to return the default argument'
        );
    }

    public function testGetOsSetting(): void
    {
        $this->setConfig(function (&$config): void {
            $config['os']['nullos']['fancy'] = true;
            $config['fallback'] = true;
        });

        $this->assertNull(LibrenmsConfig::getOsSetting(null, 'unset'), '$os is null, should return null');
        $this->assertNull(LibrenmsConfig::getOsSetting('nullos', 'unset'), 'Non-existing settings should return null');
        $this->assertFalse(LibrenmsConfig::getOsSetting('nullos', 'unset', false), 'Non-existing settings should return $default');
        $this->assertTrue(LibrenmsConfig::getOsSetting('nullos', 'fancy'), 'Failed to get setting');
        $this->assertNull(LibrenmsConfig::getOsSetting('nullos', 'fallback'), 'Incorrectly loaded global setting');

        // load yaml
        $this->assertSame('ios', LibrenmsConfig::getOsSetting('ios', 'os'));
        $this->assertGreaterThan(500, count(LibrenmsConfig::get('os')), 'Not all OS were loaded from yaml');
    }

    public function testGetCombined(): void
    {
        $this->setConfig(function (&$config): void {
            $config['num'] = ['one', 'two'];
            $config['withprefix']['num'] = ['four', 'five'];
            $config['os']['nullos']['num'] = ['two', 'three'];
            $config['assoc'] = ['a' => 'same', 'b' => 'same'];
            $config['withprefix']['assoc'] = ['a' => 'prefix_same', 'd' => 'prefix_same'];
            $config['os']['nullos']['assoc'] = ['b' => 'different', 'c' => 'still same'];
            $config['os']['nullos']['osset'] = 'ossetting';
            $config['gset'] = 'fallbackone';
            $config['withprefix']['gset'] = 'fallbacktwo';
        });

        $this->assertSame(['default'], LibrenmsConfig::getCombined('nullos', 'non-existent', '', ['default']), 'Did not return default value on non-existent key');
        $this->assertSame(['ossetting'], LibrenmsConfig::getCombined('nullos', 'osset', '', ['default']), 'Did not return OS value when global value is not set');
        $this->assertSame(['fallbackone'], LibrenmsConfig::getCombined('nullos', 'gset', '', ['default']), 'Did not return global value when OS value is not set');
        $this->assertSame(['default'], LibrenmsConfig::getCombined('nullos', 'non-existent', 'withprefix.', ['default']), 'Did not return default value on non-existent key');
        $this->assertSame(['ossetting'], LibrenmsConfig::getCombined('nullos', 'osset', 'withprefix.', ['default']), 'Did not return OS value when global value is not set');
        $this->assertSame(['fallbacktwo'], LibrenmsConfig::getCombined('nullos', 'gset', 'withprefix.', ['default']), 'Did not return global value when OS value is not set');

        $combined = LibrenmsConfig::getCombined('nullos', 'num');
        sort($combined);
        $this->assertEquals(['one', 'three', 'two'], $combined);

        $combined = LibrenmsConfig::getCombined('nullos', 'num', 'withprefix.');
        sort($combined);
        $this->assertEquals(['five', 'four', 'three', 'two'], $combined);

        $this->assertSame(['a' => 'same', 'b' => 'different', 'c' => 'still same'], LibrenmsConfig::getCombined('nullos', 'assoc'));
        // should associative not ignore same values (d=>prefix_same)?  are associative arrays actually used?
        $this->assertSame(['a' => 'prefix_same', 'b' => 'different', 'c' => 'still same'], LibrenmsConfig::getCombined('nullos', 'assoc', 'withprefix.'));
    }

    public function testSet(): void
    {
        $instance = $this->app->make('librenms-config');
        LibrenmsConfig::set('you.and.me', "I'll be there");

        $this->assertEquals("I'll be there", $this->config->getValue($instance)['you']['and']['me']);
    }

    public function testSetPersist(): void
    {
        $this->dbSetUp();

        $key = 'testing.persist';

        $query = \App\Models\Config::query()->where('config_name', $key);

        $query->delete();
        $this->assertFalse($query->exists(), "$key should not be set, clean database");
        LibrenmsConfig::persist($key, 'one');
        $this->assertEquals('one', $query->value('config_value'));
        LibrenmsConfig::persist($key, 'two');
        $this->assertEquals('two', $query->value('config_value'));

        $this->dbTearDown();
    }

    public function testHas(): void
    {
        LibrenmsConfig::set('long.key.setting', 'no one cares');
        LibrenmsConfig::set('null', null);

        $this->assertFalse(LibrenmsConfig::has('null'), 'Keys set to null do not count as existing');
        $this->assertTrue(LibrenmsConfig::has('long'), 'Top level key should exist');
        $this->assertTrue(LibrenmsConfig::has('long.key.setting'), 'Exact exists on value');
        $this->assertFalse(LibrenmsConfig::has('long.key.setting.nothing'), 'Non-existent child setting');

        $this->assertFalse(LibrenmsConfig::has('off.the.wall'), 'Non-existent key');
        $this->assertFalse(LibrenmsConfig::has('off.the'), 'Config:has() should not modify the config');
    }

    public function testGetNonExistent(): void
    {
        $this->assertNull(LibrenmsConfig::get('There.is.no.way.this.is.a.key'));
        $this->assertFalse(LibrenmsConfig::has('There.is.no'));  // should not add kes when getting
    }

    public function testGetNonExistentNested(): void
    {
        $this->assertNull(LibrenmsConfig::get('cheese.and.bologna'));
    }

    public function testGetSubtree(): void
    {
        LibrenmsConfig::set('words.top', 'August');
        LibrenmsConfig::set('words.mid', 'And Everything');
        LibrenmsConfig::set('words.bot', 'After');
        $expected = [
            'top' => 'August',
            'mid' => 'And Everything',
            'bot' => 'After',
        ];

        $this->assertEquals($expected, LibrenmsConfig::get('words'));
    }

    /**
     * Pass an anonymous function which will be passed the config variable to modify before it is set
     *
     * @param  callable  $function
     */
    private function setConfig($function)
    {
        $instance = $this->app->make('librenms-config');
        $config = $this->config->getValue($instance);
        $function($config);
        $this->config->setValue($instance, $config);
    }

    public function testForget(): void
    {
        LibrenmsConfig::set('forget.me', 'now');
        $this->assertTrue(LibrenmsConfig::has('forget.me'));

        LibrenmsConfig::forget('forget.me');
        $this->assertFalse(LibrenmsConfig::has('forget.me'));
    }

    public function testForgetSubtree(): void
    {
        LibrenmsConfig::set('forget.me.sub', 'yep');
        $this->assertTrue(LibrenmsConfig::has('forget.me.sub'));

        LibrenmsConfig::forget('forget.me');
        $this->assertFalse(LibrenmsConfig::has('forget.me.sub'));
    }
}
