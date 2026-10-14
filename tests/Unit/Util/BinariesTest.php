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

        $this->assertSame($file, Binaries::find(self::BINARY));
        $this->assertSame($file, Binaries::path(self::BINARY));
    }

    public function testMissingBinary(): void
    {
        $this->assertNull(Binaries::find(self::BINARY));
        $this->assertSame(self::BINARY, Binaries::path(self::BINARY));
    }

    public function testNotExecutable(): void
    {
        $file = $this->makeBinary(self::BINARY);
        chmod($file, 0644);

        $this->assertNull(Binaries::find(self::BINARY));
    }

    public function testRelativePathEntriesAreSkipped(): void
    {
        $this->makeBinary(self::BINARY);
        putenv('PATH=:.:relative:/opt/test/bin/');
        $cwd = getcwd();
        chdir($this->dir);

        try {
            $this->assertNull(Binaries::find(self::BINARY));
            $this->assertSame(['/opt/test/bin/', '/usr/local/sbin', '/usr/local/bin', '/usr/sbin', '/usr/bin', '/sbin', '/bin'], Binaries::searchDirs());
        } finally {
            chdir((string) $cwd);
        }
    }

    public function testConfiguredPathIsUsed(): void
    {
        $this->makeBinary(self::BINARY);
        $configured = $this->makeBinary('custom');
        config(['librenms.binaries.' . self::BINARY => $configured]);

        $this->assertSame($configured, Binaries::find(self::BINARY));
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
                $this->assertNull(Binaries::find(self::BINARY), $configured);
            }
        } finally {
            chdir((string) $cwd);
        }
    }

    private function makeBinary(string $name): string
    {
        $file = "$this->dir/$name";
        file_put_contents($file, "#!/bin/sh\nexit 0\n");
        chmod($file, 0755);

        return $file;
    }
}
