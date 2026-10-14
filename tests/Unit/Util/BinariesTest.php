<?php

namespace LibreNMS\Tests\Unit\Util;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Binaries;

final class BinariesTest extends TestCase
{
    private const BINARY = 'librenms-binaries-test';

    private string $dir;
    private string|false $path;
    private Repository $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = getenv('PATH');
        $this->dir = sys_get_temp_dir() . '/librenms-binaries-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        putenv('PATH=' . $this->dir);
        $this->cache = new Repository(new ArrayStore);
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

        $binaries = new Binaries($this->cache);
        $this->assertSame($file, $binaries->find(self::BINARY));
        $this->assertSame($file, $binaries->path(self::BINARY));
    }

    public function testMissingBinary(): void
    {
        $binaries = new Binaries($this->cache);
        $this->assertNull($binaries->find(self::BINARY));
        $this->assertSame(self::BINARY, $binaries->path(self::BINARY));
    }

    public function testEmptyPathDoesNotSearchWorkingDirectory(): void
    {
        $this->makeBinary(self::BINARY);
        putenv('PATH=');
        $cwd = getcwd();
        chdir($this->dir);

        try {
            $this->assertNull((new Binaries($this->cache))->find(self::BINARY));
        } finally {
            chdir((string) $cwd);
        }
    }

    public function testUsesCachedPath(): void
    {
        $file = $this->makeBinary(self::BINARY);
        (new Binaries($this->cache))->find(self::BINARY);

        // no longer in the PATH, but still installed
        putenv('PATH=');
        $this->assertSame($file, (new Binaries($this->cache))->find(self::BINARY));
    }

    public function testCachedPathIsRevalidated(): void
    {
        $file = $this->makeBinary(self::BINARY);
        (new Binaries($this->cache))->find(self::BINARY);

        unlink($file);
        $this->assertNull((new Binaries($this->cache))->find(self::BINARY));

        // detected again once it is installed
        $this->makeBinary(self::BINARY);
        $this->assertSame($file, (new Binaries($this->cache))->find(self::BINARY));
    }

    public function testVersionIsCachedUntilBinaryChanges(): void
    {
        $file = $this->makeBinary('rrdtool', 'echo "RRDtool 1.8.0  Copyright by Tobias Oetiker"');
        $this->assertSame('1.8.0', (new Binaries($this->cache))->rrdtoolVersion());

        // same signature, the cached version is used
        $mtime = (int) filemtime($file);
        file_put_contents($file, "#!/bin/sh\necho \"RRDtool 1.9.0  Copyright by Tobias Oetiker\"\n");
        touch($file, $mtime);
        clearstatcache();
        $this->assertSame('1.8.0', (new Binaries($this->cache))->rrdtoolVersion());

        // upgraded
        touch($file, $mtime + 60);
        clearstatcache();
        $this->assertSame('1.9.0', (new Binaries($this->cache))->rrdtoolVersion());
    }

    public function testNetSnmpVersion(): void
    {
        $this->makeBinary('snmpget', 'echo "NET-SNMP version: 5.7.3" >&2');
        $binaries = new Binaries($this->cache);
        $this->assertSame('5.7.3', $binaries->netSnmpVersion());

        $binaries->setVersion('snmpget', '5.9.4');
        $this->assertSame('5.9.4', $binaries->netSnmpVersion());
    }

    public function testMissingVersionIsNotCached(): void
    {
        $binaries = new Binaries($this->cache);
        $binaries->setPath('rrdtool', null); // it may be installed on this system
        $this->assertSame('', $binaries->rrdtoolVersion());

        $this->makeBinary('rrdtool', 'echo "RRDtool 1.8.0  Copyright by Tobias Oetiker"');
        $this->assertSame('1.8.0', (new Binaries($this->cache))->rrdtoolVersion());
    }

    private function makeBinary(string $name, string $script = 'exit 0'): string
    {
        $file = "$this->dir/$name";
        file_put_contents($file, "#!/bin/sh\n$script\n");
        chmod($file, 0755);

        return $file;
    }
}
