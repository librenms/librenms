<?php

namespace LibreNMS\Tests\Unit\RRD\Backend;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\File;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * The same scenarios run against every backend with local files and through rrdcached
 * over a unix socket and over tcp, to keep their behavior consistent
 */
abstract class BackendContractTestCase extends TestCase
{
    protected string $directory;
    protected RrdBackendInterface $backend;
    private ?Process $daemon = null;

    abstract protected function makeBackend(?string $rrdcached): RrdBackendInterface;

    /**
     * @return array<string, array{string}>
     */
    public static function modes(): array
    {
        return [
            'local files' => ['local'],
            'rrdcached unix socket' => ['unix'],
            'rrdcached tcp' => ['tcp'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir() . '/librenms-rrd-' . uniqid();
        File::ensureDirectoryExists("$this->directory/rrd/host1");
        LibrenmsConfig::set('rrd_dir', "$this->directory/rrd");
        LibrenmsConfig::set('rrd.heartbeat', 600);
    }

    protected function tearDown(): void
    {
        unset($this->backend); // backends release their process or socket when destroyed
        $this->daemon?->stop(0); // skip the clean shutdown, which waits a second for rrdcached to flush
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    /**
     * Start the backend, with a private rrdcached daemon unless using local files
     */
    protected function start(string $mode): void
    {
        $rrdcached = match ($mode) {
            'unix' => 'unix:' . $this->directory . '/s',
            'tcp' => '127.0.0.1:' . $this->freePort(),
            default => null,
        };

        if ($rrdcached) {
            $this->startDaemon($rrdcached);
        }

        LibrenmsConfig::set('rrdcached', $rrdcached ?? '');
        $this->backend = $this->makeBackend($rrdcached);
    }

    #[DataProvider('modes')]
    public function testCreateAndExists(string $mode): void
    {
        $this->start($mode);
        $rrd = RrdPath::make('host1', 'test.rrd');

        $this->assertFalse($this->backend->exists($rrd));

        $this->create($rrd);

        $this->assertFileExists($rrd->fullPath());
        $this->assertTrue($this->backend->exists($rrd));
    }

    #[DataProvider('modes')]
    public function testCreateFromSource(string $mode): void
    {
        // write the source directly, so it is on disk and not queued in rrdcached
        $source = RrdPath::make('host1', 'source.rrd');
        $timestamp = time() + 300;
        $this->rrdtool('create', $source->fullPath(), '--step', '300', 'DS:a:GAUGE:600:U:U', 'RRA:AVERAGE:0.5:1:10');
        $this->rrdtool('update', $source->fullPath(), "$timestamp:42");
        $this->start($mode);

        $rrd = RrdPath::make('host1', 'test.rrd');
        $this->backend->create($rrd, RrdDefinition::make()
            ->addDataset('copied', 'GAUGE', source_ds: 'a', source_file: $source)
            ->setStep(300)
            ->setRras(['RRA:AVERAGE:0.5:1:10']));

        // the test daemon shares storage, so sources are used in every mode
        $this->assertTrue($this->backend->exists($rrd));
        $this->assertSame((string) $timestamp, $this->info($rrd)['last_update'] ?? null);
    }

    #[DataProvider('modes')]
    public function testCreateWithMissingSource(string $mode): void
    {
        $this->start($mode);
        $rrd = RrdPath::make('host1', 'test.rrd');
        $this->backend->create($rrd, RrdDefinition::make()
            ->addDataset('copied', 'GAUGE', source_ds: 'a', source_file: RrdPath::make('host1', 'missing.rrd'))
            ->setStep(300)
            ->setRras(['RRA:AVERAGE:0.5:1:10']));

        $this->assertTrue($this->backend->exists($rrd));
        $this->assertArrayHasKey('ds[copied].type', $this->info($rrd));
    }

    #[DataProvider('modes')]
    public function testCreateExistingFileIsLeftAlone(string $mode): void
    {
        $this->start($mode);
        $rrd = RrdPath::make('host1', 'test.rrd');
        $this->create($rrd);
        $this->create($rrd);

        $this->assertFileExists($rrd->fullPath());
    }

    #[DataProvider('modes')]
    public function testUpdate(string $mode): void
    {
        $this->start($mode);
        $rrd = RrdPath::make('host1', 'test.rrd');
        $this->create($rrd);

        $this->backend->update($rrd, [5, null]);
        $this->backend->update($rrd, [6, 1], time() + 300);

        $this->assertFileExists($rrd->fullPath());
    }

    #[DataProvider('modes')]
    public function testUpdateMissingFile(string $mode): void
    {
        $this->start($mode);
        $this->expectException(RrdNotFoundException::class);
        $this->backend->update(RrdPath::make('host1', 'missing.rrd'), [5, 6]);
    }

    #[DataProvider('modes')]
    public function testTune(string $mode): void
    {
        $this->start($mode);
        $rrd = RrdPath::make('host1', 'test.rrd');
        $this->create($rrd);

        $this->backend->tune($rrd, ['a' => ['max' => 100], 'b' => ['min' => 1, 'max' => 200]]);

        $info = $this->info($rrd);
        $this->assertSame('1.0000000000e+02', $info['ds[a].max']);
        $this->assertSame('1.0000000000e+00', $info['ds[b].min']);
        $this->assertSame('2.0000000000e+02', $info['ds[b].max']);

        $this->backend->tune($rrd, ['a' => ['max' => null]]);
        $this->assertSame('NaN', $this->info($rrd)['ds[a].max']);
    }

    #[DataProvider('modes')]
    public function testTuneMissingFile(string $mode): void
    {
        $this->start($mode);
        $this->expectException(RrdNotFoundException::class);
        $this->backend->tune(RrdPath::make('host1', 'missing.rrd'), ['a' => ['max' => 100]]);
    }

    #[DataProvider('modes')]
    public function testList(string $mode): void
    {
        $this->start($mode);
        foreach (['port-id2.rrd', 'port-id1.rrd', 'sensor-temp.rrd'] as $file) {
            $this->create(RrdPath::make('host1', $file));
        }

        $files = array_map(fn (RrdPath $path) => $path->relativePath(), $this->backend->list('host1', 'port'));
        sort($files);

        $this->assertSame(['host1/port-id1.rrd', 'host1/port-id2.rrd'], $files);
        $this->assertCount(3, $this->backend->list('host1'));
    }

    #[DataProvider('modes')]
    public function testListMissingHost(string $mode): void
    {
        $this->start($mode);
        $this->assertSame([], $this->backend->list('missing-host'));
    }

    #[DataProvider('modes')]
    public function testGraph(string $mode): void
    {
        $this->start($mode);
        $rrd = RrdPath::make('host1', 'test.rrd');
        $this->create($rrd);

        $image = $this->backend->graph(['--start', '-1h', "DEF:a=$rrd:a:AVERAGE", 'LINE1:a#ff0000'], 'Pacific/Auckland');

        $this->assertStringStartsWith("\x89PNG", $image);
    }

    #[DataProvider('modes')]
    public function testGraphError(string $mode): void
    {
        $this->start($mode);
        $this->expectException(RrdGraphException::class);
        $this->backend->graph(['--start', '-1h', 'DEF:a=' . RrdPath::make('host1', 'missing.rrd') . ':a:AVERAGE', 'LINE1:a#ff0000']);
    }

    protected function create(RrdPath $rrd): void
    {
        $definition = RrdDefinition::make()
            ->addDataset('a', 'GAUGE')
            ->addDataset('b', 'COUNTER')
            ->setStep(300)
            ->setRras(['RRA:AVERAGE:0.5:1:10']);
        $this->backend->create($rrd, $definition);
    }

    /**
     * @return array<string, string>
     */
    private function info(RrdPath $rrd): array
    {
        $info = [];
        foreach (explode("\n", trim($this->rrdtool('info', $rrd->fullPath()))) as $line) {
            [$key, $value] = explode(' = ', $line, 2);
            $info[$key] = $value;
        }

        return $info;
    }

    /**
     * Run rrdtool directly on the files, without rrdcached
     */
    private function rrdtool(string ...$arguments): string
    {
        if (! is_executable((string) LibrenmsConfig::get('rrdtool', '/usr/bin/rrdtool'))) {
            $this->markTestSkipped('rrdtool is needed to inspect rrd files');
        }

        $process = new Process([LibrenmsConfig::get('rrdtool', '/usr/bin/rrdtool'), ...$arguments]);
        $process->mustRun();

        return $process->getOutput();
    }

    private function startDaemon(string $address): void
    {
        $binary = (new ExecutableFinder)->find('rrdcached');
        if ($binary === null) {
            $this->markTestSkipped('rrdcached is not installed');
        }

        // -g foreground, -B restrict to the base dir, -R allow creating directories
        $this->daemon = new Process([$binary, '-g', '-l', $address, '-b', "$this->directory/rrd", '-B', '-R', '-p', "$this->directory/rrdcached.pid"]);
        $this->daemon->start();

        $stream = str_starts_with($address, 'unix:') ? 'unix://' . substr($address, 5) : "tcp://$address";
        for ($i = 0; $i < 50; $i++) {
            $socket = @stream_socket_client($stream);
            if ($socket) {
                fclose($socket);

                return;
            }
            usleep(100000);
        }

        $this->fail('rrdcached did not start: ' . $this->daemon->getErrorOutput());
    }

    private function freePort(): int
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr(strrchr((string) stream_socket_get_name($server, false), ':'), 1);
        fclose($server);

        return $port;
    }
}
