<?php

namespace LibreNMS\Tests\Unit\Validations;

use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Programs;
use LibreNMS\Validator;
use Mockery;
use Mockery\MockInterface;

final class BinaryLocationsTest extends TestCase
{
    private string $dir;
    private string|false $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = getenv('PATH');
        $this->dir = sys_get_temp_dir() . '/librenms-binary-locations-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        putenv("PATH=$this->dir:/usr/bin:/bin"); // whereis is run by the shell
    }

    protected function tearDown(): void
    {
        putenv($this->path === false ? 'PATH' : "PATH=$this->path");
        array_map(unlink(...), glob("$this->dir/*") ?: []);
        rmdir($this->dir);

        parent::tearDown();
    }

    public function testWarnsWhenLocationDiffers(): void
    {
        $configured = $this->makeBinary('custom-rrdtool');
        LibrenmsConfig::set('rrdtool', $configured);
        $detected = $this->makeBinary('rrdtool');

        $validator = $this->validator('rrdtool');
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => str_starts_with($message, "rrdtool is $configured")
            && str_contains($message, "$detected is found on this node. A future version would use that.")
            && str_contains($fix, 'https://github.com/librenms/librenms/issues/new'));

        (new Programs)->checkBinaryLocations($validator);
    }

    public function testWarnsWhenNotDetected(): void
    {
        LibrenmsConfig::set('virsh', $this->makeBinary('custom-virsh'));
        config(['librenms.binaries.virsh' => '/nonexistent/virsh']);

        $validator = $this->validator('virsh');
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message) => str_contains($message, 'but nothing is found'));

        (new Programs)->checkBinaryLocations($validator);
    }

    public function testNoWarningWhenSame(): void
    {
        $file = $this->makeBinary('rrdtool');
        symlink($file, "$this->dir/linked-rrdtool");
        LibrenmsConfig::set('rrdtool', "$this->dir/linked-rrdtool");

        $validator = $this->validator('rrdtool');
        $validator->shouldNotReceive('warn');

        (new Programs)->checkBinaryLocations($validator);
    }

    public function testMovedBinaryExplainsOverride(): void
    {
        $configured = $this->makeBinary('custom-traceroute');
        LibrenmsConfig::set('traceroute', $configured);
        $this->makeBinary('traceroute');

        $validator = $this->validator('traceroute');
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => str_ends_with($message, 'is found on this node and used.')
            && str_contains($fix, "TRACEROUTE_PATH=$configured in .env"));

        (new Programs)->checkBinaryLocations($validator);
    }

    /**
     * Other binaries installed on this system may be located differently, ignore them
     */
    private function validator(string $binary): Validator&MockInterface
    {
        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('warn')->withArgs(fn (string $message) => ! str_starts_with($message, "$binary is "))->zeroOrMoreTimes();

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
