<?php

namespace Tests\Unit;

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
}
