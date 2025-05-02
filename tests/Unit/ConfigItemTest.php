<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\DynamicConfigItem;

class ConfigItemTest extends TestCase
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
}
