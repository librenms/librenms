<?php

namespace LibreNMS\Tests\Unit\Validations;

use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Util\Binaries;
use LibreNMS\Validations\Programs;
use LibreNMS\Validator;
use Mockery;
use Mockery\MockInterface;

final class LegacyBinarySettingsTest extends InMemoryDbTestCase
{
    private string $dir;
    private string|false $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = getenv('PATH');
        $this->dir = sys_get_temp_dir() . '/librenms-legacy-binaries-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        putenv("PATH=$this->dir");
    }

    protected function tearDown(): void
    {
        putenv($this->path === false ? 'PATH' : "PATH=$this->path");
        array_map(unlink(...), glob("$this->dir/*") ?: []);
        rmdir($this->dir);

        parent::tearDown();
    }

    public function testFailsWhenSettingDiffers(): void
    {
        $setting = $this->makeBinary('snmpwalk-custom');
        LibrenmsConfig::set('snmpwalk', $setting);
        $found = $this->makeBinary('snmpwalk');

        $validator = $this->validator('snmpwalk');
        $validator->shouldReceive('fail')->once()->withArgs(fn (string $message, string $fix) => $message === "snmpwalk is set to $setting in the LibreNMS config. Support for this setting will be removed, then $found will be used."
            && str_contains($fix, "add SNMPWALK_PATH=$setting to .env, then remove \$config['snmpwalk'] from config.php.")
            && str_contains($fix, "To use $found, remove"));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testFailsWhenNotFoundWithoutSetting(): void
    {
        if ((new Binaries)->search('nfdump') !== null) {
            $this->markTestSkipped('nfdump is installed in a standard bin directory');
        }

        LibrenmsConfig::set('nfdump', $this->makeBinary('nfdump-custom'));

        $validator = $this->validator('nfdump');
        $validator->shouldReceive('fail')->once()->withArgs(fn (string $message, string $fix) => str_ends_with($message, 'then nfdump will not be found.')
            && ! str_contains($fix, 'To use'));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testNoOverrideForFping6(): void
    {
        LibrenmsConfig::set('fping6', $this->makeBinary('fping6-custom'));
        $this->makeBinary('fping6');

        $validator = $this->validator('fping6');
        $validator->shouldReceive('fail')->once()->withArgs(fn (string $message, string $fix) => ! str_contains($fix, '.env') && str_starts_with($fix, 'To use'));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testSettingNameDiffersFromBinary(): void
    {
        $setting = $this->makeBinary('sendmail-custom');
        LibrenmsConfig::set('email_sendmail_path', $setting);
        $this->makeBinary('sendmail');

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fail')->withArgs(fn (string $message) => ! str_starts_with($message, 'email_sendmail_path is set to '))->zeroOrMoreTimes();
        $validator->shouldReceive('fail')->once()->withArgs(fn (string $message, string $fix) => str_starts_with($message, "email_sendmail_path is set to $setting ")
            && str_contains($fix, "add SENDMAIL_PATH=$setting to .env, then remove \$config['email_sendmail_path'] from config.php."));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testRemoveStoredSetting(): void
    {
        $setting = $this->makeBinary('snmpget-custom');
        LibrenmsConfig::persist('snmpget', $setting);

        $validator = $this->validator('snmpget');
        $validator->shouldReceive('fail')->once()->withArgs(fn (string $message, string $fix) => str_contains($fix, 'then remove the setting with: lnms config:set snmpget.'));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testConfigFileOverridesStoredSetting(): void
    {
        LibrenmsConfig::persist('snmpget', $this->makeBinary('snmpget-stored'));
        $setting = $this->makeBinary('snmpget-file');
        LibrenmsConfig::set('snmpget', $setting); // as config.php would

        $validator = $this->validator('snmpget');
        $validator->shouldReceive('fail')->once()->withArgs(fn (string $message, string $fix) => str_starts_with($message, "snmpget is set to $setting ")
            && str_contains($fix, "then remove \$config['snmpget'] from config.php.")
            && ! str_contains($fix, 'lnms config:set'));

        (new Programs)->checkLegacySettings($validator);
    }

    public function testNoFailureWhenSettingIsFound(): void
    {
        $file = $this->makeBinary('rrdtool');
        symlink($file, "$this->dir/linked-rrdtool");
        LibrenmsConfig::set('rrdtool', "$this->dir/linked-rrdtool");

        $validator = $this->validator('rrdtool');
        $validator->shouldNotReceive('fail');

        (new Programs)->checkLegacySettings($validator);
    }

    public function testNoFailureWhenSetInEnv(): void
    {
        LibrenmsConfig::set('virsh', $this->makeBinary('virsh-custom'));
        config(['librenms.binaries.virsh' => $this->makeBinary('virsh-env')]);

        $validator = $this->validator('virsh');
        $validator->shouldNotReceive('fail');

        (new Programs)->checkLegacySettings($validator);
    }

    /**
     * Settings of other binaries on this system may be reported, ignore them
     */
    private function validator(string $binary): Validator&MockInterface
    {
        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fail')->withArgs(fn (string $message) => ! str_starts_with($message, "$binary is set to "))->zeroOrMoreTimes();

        return $validator;
    }

    private function makeBinary(string $name): string
    {
        $file = "$this->dir/$name";
        file_put_contents($file, "#!/bin/sh\nexit 0\n");
        chmod($file, 0755);

        return $file;
    }
}
