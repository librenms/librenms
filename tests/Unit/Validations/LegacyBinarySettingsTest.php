<?php

namespace LibreNMS\Tests\Unit\Validations;

use App\Facades\Binaries;
use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Programs;
use LibreNMS\Validations\Rrd\CheckRrdVersion;
use LibreNMS\Validator;
use Mockery;

final class LegacyBinarySettingsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/librenms-legacy-' . bin2hex(random_bytes(4));
        mkdir($this->dir);

        foreach (['fping', 'fping6', 'ipmitool', 'nfdump', 'rrdtool', 'snmpbulkwalk', 'snmpget', 'snmpgetnext', 'snmptranslate', 'snmptrap', 'snmpwalk', 'traceroute', 'virsh'] as $setting) {
            LibrenmsConfig::forget($setting);
        }
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("$this->dir/*") ?: []);
        rmdir($this->dir);

        parent::tearDown();
    }

    public function testWarnsWhenSetLocationIsNotUsed(): void
    {
        $legacy = $this->makeBinary('legacy-rrdtool');
        LibrenmsConfig::set('rrdtool', $legacy);
        Binaries::setPath('rrdtool', $this->makeBinary('rrdtool'));

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => str_contains($message, $legacy)
            && str_contains($fix, "ln -s $legacy /usr/local/bin/rrdtool"));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testNoWarningWhenLinked(): void
    {
        $legacy = $this->makeBinary('legacy-rrdtool');
        symlink($legacy, "$this->dir/rrdtool");
        LibrenmsConfig::set('rrdtool', $legacy);
        Binaries::setPath('rrdtool', "$this->dir/rrdtool");

        $validator = Mockery::mock(Validator::class);
        $validator->shouldNotReceive('warn');

        (new Programs)->checkLegacySettings($validator);
    }

    public function testNoWarningWhenNotOnThisPoller(): void
    {
        LibrenmsConfig::set('rrdtool', '/nonexistent/rrdtool');
        Binaries::setPath('rrdtool', $this->makeBinary('rrdtool'));

        $validator = Mockery::mock(Validator::class);
        $validator->shouldNotReceive('warn');

        (new Programs)->checkLegacySettings($validator);
    }

    public function testRrdVersion(): void
    {
        Binaries::setVersion('rrdtool', '1.8.0');
        LibrenmsConfig::set('rrdcached', 'localhost:42217');
        LibrenmsConfig::forget('rrdcached_version');
        LibrenmsConfig::forget('rrdtool_version');
        $result = (new CheckRrdVersion)->validate();
        $this->assertSame(ValidationResult::SUCCESS, $result->getStatus());

        // the old setting limited features for an older rrdcached
        LibrenmsConfig::set('rrdtool_version', '1.5.5');
        $result = (new CheckRrdVersion)->validate();
        $this->assertSame(ValidationResult::WARNING, $result->getStatus());
        $this->assertStringContainsString('rrdcached_version 1.5.5', (string) $result->getFix());

        // replaced
        LibrenmsConfig::set('rrdcached_version', '1.5.5');
        $result = (new CheckRrdVersion)->validate();
        $this->assertSame(ValidationResult::SUCCESS, $result->getStatus());

        LibrenmsConfig::set('rrdcached_version', '1.4');
        $result = (new CheckRrdVersion)->validate();
        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());

        // not used without rrdcached
        LibrenmsConfig::set('rrdcached', '');
        $result = (new CheckRrdVersion)->validate();
        $this->assertSame(ValidationResult::SUCCESS, $result->getStatus());

        Binaries::setVersion('rrdtool', '1.4');
        $result = (new CheckRrdVersion)->validate();
        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
    }

    private function makeBinary(string $name): string
    {
        $file = "$this->dir/$name";
        file_put_contents($file, "#!/bin/sh\nexit 0\n");
        chmod($file, 0755);

        return $file;
    }
}
