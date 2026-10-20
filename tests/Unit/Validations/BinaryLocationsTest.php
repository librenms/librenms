<?php

namespace LibreNMS\Tests\Unit\Validations;

use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Programs;
use LibreNMS\Validator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

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
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => $message === "rrdtool: the old lookup finds $configured, the new lookup finds $detected. A future version will use the new lookup."
            && str_contains($fix, 'https://github.com/librenms/librenms/issues/new'));

        (new Programs)->checkBinaryLocations($validator);
    }

    public function testWarnsWhenNotDetected(): void
    {
        LibrenmsConfig::set('virsh', $this->makeBinary('custom-virsh'));
        config(['librenms.binaries.virsh' => '/nonexistent/virsh']);

        $validator = $this->validator('virsh');
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message) => str_contains($message, 'the new lookup finds nothing.'));

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

    #[DataProvider('movedBinaries')]
    public function testMovedBinary(string $binary, ?string $env): void
    {
        $configured = $this->makeBinary("custom-$binary");
        LibrenmsConfig::set($binary, $configured);
        $this->makeBinary($binary);

        $validator = $this->validator($binary);
        $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => str_ends_with($message, 'and uses it.')
            && ($env === null ? ! str_contains($fix, '.env') : str_contains($fix, "$env=$configured in .env")));

        (new Programs)->checkBinaryLocations($validator);
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function movedBinaries(): array
    {
        return [
            'cron, only used by validation' => ['cron', null],
            'systemctl, only used by validation' => ['systemctl', null],
            'traceroute' => ['traceroute', 'TRACEROUTE_PATH'],
        ];
    }

    /**
     * Other binaries installed on this system may be located differently, ignore them
     */
    private function validator(string $binary): Validator&MockInterface
    {
        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('warn')->withArgs(fn (string $message) => ! str_starts_with($message, "$binary: "))->zeroOrMoreTimes();

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
