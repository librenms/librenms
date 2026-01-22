<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\DynamicConfigItem;

final class ConfigItemTest extends TestCase
{
    public function testExecutableValidation(): void
    {
        $executableType = new DynamicConfigItem('testExecutable', [
            'type' => 'executable',
        ]);

        $lnms_path = base_path('lnms'); // this should be executable

        $this->assertTrue($executableType->checkValue($lnms_path));
        $this->assertTrue($executableType->checkValue(base_path() . '/app/../lnms')); //check path manipulation
        $this->assertFalse($executableType->checkValue('/'));
        $this->assertFalse($executableType->checkValue(base_path('LICENSE.txt'))); // non-executable file

        $bad_characters = ['`', ';', '#', '$', '|', '&', '\'', '"', '>', '<', '(', ' '];
        foreach ($bad_characters as $bad) {
            $this->assertFalse($executableType->checkValue($lnms_path . $bad));
        }
    }

    public function testDirectoryValidation(): void
    {
        $executableType = new DynamicConfigItem('testDirectory', [
            'type' => 'directory',
        ]);

        $this->assertTrue($executableType->checkValue(__DIR__));
        $this->assertTrue($executableType->checkValue(__DIR__ . '/../' . basename(__DIR__))); //check path manipulation
        $this->assertTrue($executableType->checkValue('/'));
        $this->assertFalse($executableType->checkValue(__FILE__)); // check file
        $this->assertFalse($executableType->checkValue(base_path('LICENSE.txt'))); // check file

        $bad_characters = ['`', ';', '#', '$', '|', '&', '\'', '"', '>', '<', '(', ' '];
        foreach ($bad_characters as $bad) {
            $this->assertFalse($executableType->checkValue(__DIR__ . $bad));
        }
    }

    public function testArraySubKeyedValidation(): void
    {
        $arraySubKeyedType = new DynamicConfigItem('testArray', [
            'type' => 'array-sub-keyed',
        ]);

        $this->assertTrue($arraySubKeyedType->checkValue(['foo' => ['bar']]));
        $this->assertTrue($arraySubKeyedType->checkValue(['0' => ['bar']]));
        $this->assertTrue($arraySubKeyedType->checkValue([0 => ['bar']]));
        $this->assertTrue($arraySubKeyedType->checkValue(['foo' => []]));
        $this->assertTrue($arraySubKeyedType->checkValue(['foo' => ['bar' => []]]));

        $this->assertTrue($arraySubKeyedType->checkValue([true => []])); // PHP converts it to [1 => []]
        $this->assertTrue($arraySubKeyedType->checkValue([false => []])); // PHP converts it to [[]]

        $this->assertFalse($arraySubKeyedType->checkValue(['foo' => 'bar']));
        $this->assertFalse($arraySubKeyedType->checkValue(['foo' => null]));
        $this->assertFalse($arraySubKeyedType->checkValue(['foo' => false]));
        $this->assertFalse($arraySubKeyedType->checkValue(['' => []]));
        $this->assertFalse($arraySubKeyedType->checkValue([' ' => []]));
        $this->assertFalse($arraySubKeyedType->checkValue([null => []]));
    }

    public function testArrayKeysNotEmptyValidation(): void
    {
        $array_keys_not_empty = new DynamicConfigItem('testArray', [
            'type' => 'array-sub-keyed',
            'validate' => [
                'value' => 'array_keys_not_empty',
                'value.*' => 'array_keys_not_empty',
            ],
        ]);

        $this->assertTrue($array_keys_not_empty->checkValue(['foo' => ['bar']]));
        $this->assertTrue($array_keys_not_empty->checkValue(['0' => ['bar']]));
        $this->assertTrue($array_keys_not_empty->checkValue([0 => ['bar']]));
        $this->assertTrue($array_keys_not_empty->checkValue(['foo' => []]));
        $this->assertTrue($array_keys_not_empty->checkValue(['foo' => ['bar' => []]]));

        $this->assertTrue($array_keys_not_empty->checkValue([true => []])); // PHP converts it to [1 => []]
        $this->assertTrue($array_keys_not_empty->checkValue([false => []])); // PHP converts it to [[]]

        $this->assertFalse($array_keys_not_empty->checkValue(['foo' => 'bar']));
        $this->assertFalse($array_keys_not_empty->checkValue(['foo' => ['' => []]]));
        $this->assertFalse($array_keys_not_empty->checkValue(['foo' => null]));
        $this->assertFalse($array_keys_not_empty->checkValue(['foo' => false]));
        $this->assertFalse($array_keys_not_empty->checkValue(['' => []]));
        $this->assertFalse($array_keys_not_empty->checkValue([' ' => []]));
        $this->assertFalse($array_keys_not_empty->checkValue([null => []]));
    }

    public function testMapValidation(): void
    {
        $mapType = new DynamicConfigItem('testMap', [
            'type' => 'map',
        ]);

        // Valid cases - flat key-value pairs with scalar values
        $this->assertTrue($mapType->checkValue(['foo' => 'bar']));
        $this->assertTrue($mapType->checkValue(['65332' => 'Cymru FullBogon Feed']));
        $this->assertTrue($mapType->checkValue(['key1' => 'value1', 'key2' => 'value2']));
        $this->assertTrue($mapType->checkValue(['0' => 'zero']));
        $this->assertTrue($mapType->checkValue([0 => 'zero']));
        $this->assertTrue($mapType->checkValue(['foo' => ''])); // empty string value is ok
        $this->assertTrue($mapType->checkValue([])); // empty array is ok

        // Invalid cases
        $this->assertFalse($mapType->checkValue(['foo' => ['nested']])); // arrays not allowed as values
        $this->assertFalse($mapType->checkValue(['foo' => ['bar' => 'baz']])); // nested arrays not allowed
        $this->assertFalse($mapType->checkValue(['' => 'value'])); // empty key
        $this->assertFalse($mapType->checkValue([' ' => 'value'])); // whitespace-only key
        $this->assertFalse($mapType->checkValue('not an array'));
        $this->assertFalse($mapType->checkValue(null));
    }

    public function testNestedMapValidation(): void
    {
        $nestedMapType = new DynamicConfigItem('testNestedMap', [
            'type' => 'nested-map',
        ]);

        // Valid cases - nested key-value structures
        $this->assertTrue($nestedMapType->checkValue(['foo' => ['bar' => 'baz']]));
        $this->assertTrue($nestedMapType->checkValue(['provider' => ['client_id' => '123', 'secret' => 'abc']]));
        $this->assertTrue($nestedMapType->checkValue(['foo' => []])); // empty sub-array is ok
        $this->assertTrue($nestedMapType->checkValue([])); // empty array is ok
        $this->assertTrue($nestedMapType->checkValue(['0' => ['bar']]));
        $this->assertTrue($nestedMapType->checkValue([0 => ['bar']]));

        // Invalid cases
        $this->assertFalse($nestedMapType->checkValue(['foo' => 'bar'])); // value must be array
        $this->assertFalse($nestedMapType->checkValue(['foo' => null]));
        $this->assertFalse($nestedMapType->checkValue(['foo' => false]));
        $this->assertFalse($nestedMapType->checkValue(['' => []])); // empty key
        $this->assertFalse($nestedMapType->checkValue([' ' => []])); // whitespace-only key
        $this->assertFalse($nestedMapType->checkValue('not an array'));
    }

    public function testCheckKeyValidation(): void
    {
        // Basic key validation without regex
        $mapType = new DynamicConfigItem('testMap', [
            'type' => 'map',
        ]);

        $this->assertTrue($mapType->checkKey('foo'));
        $this->assertTrue($mapType->checkKey('65332'));
        $this->assertTrue($mapType->checkKey('0'));
        $this->assertTrue($mapType->checkKey(0));
        $this->assertTrue($mapType->checkKey('key with spaces'));
        $this->assertTrue($mapType->checkKey('/^regex$/'));

        // Invalid keys - empty or whitespace-only
        $this->assertFalse($mapType->checkKey(''));
        $this->assertFalse($mapType->checkKey(' '));
        $this->assertFalse($mapType->checkKey('   '));
        $this->assertFalse($mapType->checkKey("\t"));
        $this->assertFalse($mapType->checkKey("\n"));
    }

    public function testCheckKeyRegexValidation(): void
    {
        // Map with regex key validation
        $regexMapType = new DynamicConfigItem('testRegexMap', [
            'type' => 'map',
            'validate' => [
                'key' => 'regex',
            ],
        ]);

        // Valid regex patterns
        $this->assertTrue($regexMapType->checkKey('/^foo$/'));
        $this->assertTrue($regexMapType->checkKey('/test/i'));
        $this->assertTrue($regexMapType->checkKey('/^cpu interface/'));
        $this->assertTrue($regexMapType->checkKey('#pattern#'));
        $this->assertTrue($regexMapType->checkKey('~pattern~'));

        // Invalid regex patterns
        $this->assertFalse($regexMapType->checkKey('/unclosed'));
        $this->assertFalse($regexMapType->checkKey('/invalid[/'));
        $this->assertFalse($regexMapType->checkKey('/bad(regex/'));
        $this->assertFalse($regexMapType->checkKey(''));
        $this->assertFalse($regexMapType->checkKey(' '));
    }
}
