<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Binaries;

final class BinariesTest extends TestCase
{
    private const BINARY = 'librenms-binaries-test';

    private string $dir;
    private string|false $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = getenv('PATH');
        $this->dir = sys_get_temp_dir() . '/librenms-binaries-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        putenv('PATH=' . $this->dir);
    }

    protected function tearDown(): void
    {
        putenv($this->path === false ? 'PATH' : "PATH=$this->path");
        array_map(unlink(...), glob("$this->dir/*") ?: []);
        rmdir($this->dir);

        parent::tearDown();
    }

    public function testFindsBinaryInPath(): void
    {
        $file = $this->makeBinary(self::BINARY);

        $this->assertSame($file, (new Binaries)->find(self::BINARY));
        $this->assertSame($file, (new Binaries)->path(self::BINARY));
    }

    public function testMissingBinary(): void
    {
        $this->assertNull((new Binaries)->find(self::BINARY));
        $this->assertSame(self::BINARY, (new Binaries)->path(self::BINARY));
    }

    public function testNotExecutable(): void
    {
        $file = $this->makeBinary(self::BINARY);
        chmod($file, 0644);

        $this->assertNull((new Binaries)->find(self::BINARY));
    }

    public function testRelativePathEntriesAreSkipped(): void
    {
        $this->makeBinary(self::BINARY);
        putenv('PATH=:.:relative:/opt/test/bin/');
        $cwd = getcwd();
        chdir($this->dir);

        try {
            $this->assertNull((new Binaries)->find(self::BINARY));
            $this->assertSame(['/opt/test/bin/', '/usr/local/sbin', '/usr/local/bin', '/usr/sbin', '/usr/bin', '/sbin', '/bin'], (new Binaries)->searchDirs());
        } finally {
            chdir((string) $cwd);
        }
    }

    public function testConfiguredPathIsUsed(): void
    {
        $this->makeBinary(self::BINARY);
        $configured = $this->makeBinary('custom');
        config(['librenms.binaries.' . self::BINARY => $configured]);

        $this->assertSame($configured, (new Binaries)->find(self::BINARY));
    }

    public function testInvalidConfiguredPathIsNotSearched(): void
    {
        $this->makeBinary(self::BINARY);
        $this->makeBinary('custom');
        $cwd = getcwd();
        chdir($this->dir);

        try {
            foreach (['/nonexistent/' . self::BINARY, 'custom', $this->dir] as $configured) {
                config(['librenms.binaries.' . self::BINARY => $configured]);
                $this->assertNull((new Binaries)->find(self::BINARY), $configured);
            }
        } finally {
            chdir((string) $cwd);
        }
    }

    public function testFoundOncePerScope(): void
    {
        $file = $this->makeBinary(self::BINARY);
        $this->assertSame($file, resolve(Binaries::class)->find(self::BINARY));

        unlink($file);
        $this->assertSame($file, resolve(Binaries::class)->find(self::BINARY));

        // a new job or request
        $this->app->forgetScopedInstances();
        $this->assertNull(resolve(Binaries::class)->find(self::BINARY));
    }

    private function makeBinary(string $name): string
    {
        $file = "$this->dir/$name";
        file_put_contents($file, "#!/bin/sh\nexit 0\n");
        chmod($file, 0755);

        return $file;
    }
}
