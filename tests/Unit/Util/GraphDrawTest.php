<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Data\Graphing\GraphParameters;
use LibreNMS\Data\Graphing\MissingRrds;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Graph;

final class GraphDrawTest extends TestCase
{
    private const MISSING = "opening '/opt/librenms/rrd/host1/port-id2.rrd': No such file or directory";

    public function testDrawsWithoutRetry(): void
    {
        $builds = 0;
        $image = $this->draw(function () use (&$builds) {
            $builds++;

            return ['DEF:a=host1/port-id1.rrd:INOCTETS:AVERAGE'];
        }, fn () => 'image');

        $this->assertSame('image', $image);
        $this->assertSame(1, $builds);
    }

    public function testOptionalSeriesIsLeftOut(): void
    {
        $drawn = [];
        $image = $this->draw(function (MissingRrds $missing_rrds) {
            $options = [];
            foreach (['host1/port-id1.rrd', 'host1/port-id2.rrd'] as $rrd) {
                if (! $missing_rrds->has($rrd)) {
                    $options[] = "DEF:a=$rrd:INOCTETS:AVERAGE";
                }
            }

            return $options;
        }, function (array $options) use (&$drawn) {
            $drawn[] = $options;
            if (in_array('DEF:a=host1/port-id2.rrd:INOCTETS:AVERAGE', $options)) {
                throw new RrdGraphException('rrdcached@127.0.0.1:42217: rrd_fetch_r failed: ' . self::MISSING);
            }

            return 'image';
        });

        $this->assertSame('image', $image);
        $this->assertSame(['DEF:a=host1/port-id1.rrd:INOCTETS:AVERAGE'], end($drawn), 'drawn without the missing series');
    }

    public function testDefaultNoDataText(): void
    {
        $e = $this->drawException(fn () => ['DEF:a=host1/port-id2.rrd:INOCTETS:AVERAGE'], self::MISSING);

        $this->assertSame('No Data file port-id2.rrd', $e->getMessage());
    }

    public function testCustomNoDataText(): void
    {
        $e = $this->drawException(function (MissingRrds $missing_rrds, ?string &$no_data_text) {
            $no_data_text = 'Waiting for the first poll';

            return ['DEF:a=host1/port-id2.rrd:INOCTETS:AVERAGE'];
        }, self::MISSING);

        $this->assertSame('Waiting for the first poll', $e->getMessage());
    }

    public function testOtherErrorsAreDrawErrors(): void
    {
        $e = $this->drawException(fn () => ['DEF:a=host1/port-id1.rrd:INOCTETS:AVERAGE'], "No DS called 'INOCTETS' in 'host1/port-id1.rrd'");

        $this->assertStringStartsWith('Error: No DS called', $e->getMessage());
    }

    public function testErrorAfterLeavingOutAllSeriesIsNoData(): void
    {
        $e = $this->drawException(fn (MissingRrds $missing_rrds) => $missing_rrds->has('host1/port-id2.rrd') ? ['COMMENT:nothing'] : ['DEF:a=host1/port-id2.rrd:INOCTETS:AVERAGE'], fn (array $options) => $options === ['COMMENT:nothing'] ? 'can\'t make a graph without contents' : self::MISSING);

        $this->assertSame('No Data file port-id2.rrd', $e->getMessage());
    }

    public function testFindsAllMissingFilesWithOneRebuild(): void
    {
        $files = ['host1/a.rrd', 'host1/b.rrd', 'host1/c\:d.rrd', 'host1/e.rrd'];
        $missing = ['/opt/librenms/rrd/host1/b.rrd', '/opt/librenms/rrd/host1/c:d.rrd'];
        $builds = 0;
        $draws = 0;

        $image = $this->draw(function (MissingRrds $missing_rrds) use ($files, &$builds) {
            $builds++;
            $options = [];
            foreach ($files as $i => $file) {
                if (! $missing_rrds->has(str_replace('\:', ':', $file))) {
                    $options[] = "DEF:v$i=$file:INOCTETS:AVERAGE";
                    $options[] = "LINE1:v$i#ff0000";
                }
            }

            return $options;
        }, function (array $options) use ($missing, &$draws) {
            $draws++;
            foreach ($options as $option) {
                foreach ($missing as $file) {
                    if (str_contains(str_replace('\:', ':', $option), substr($file, strlen('/opt/librenms/rrd/')))) {
                        throw new RrdGraphException("opening '$file': No such file or directory");
                    }
                }
            }

            return 'image';
        });

        $this->assertSame('image', $image);
        $this->assertSame(2, $builds, 'the graph is only rebuilt once');
        $this->assertSame(4, $draws, 'first draw, two probes, final draw');
    }

    public function testAllSeriesMissingIsNoData(): void
    {
        $e = $this->drawException(function (MissingRrds $missing_rrds) {
            if ($missing_rrds->has('host1/port-id2.rrd')) {
                // what getRrdOptions() does when a graph has nothing left to draw
                throw new RrdGraphException('No Data file port-id2.rrd', 'No Data');
            }

            return ['DEF:a=host1/port-id2.rrd:INOCTETS:AVERAGE'];
        }, self::MISSING);

        $this->assertSame('No Data file port-id2.rrd', $e->getMessage());
    }

    public function testMissingFileParsing(): void
    {
        $this->assertSame('/opt/librenms/rrd/host1/port-id2.rrd', (new RrdGraphException(self::MISSING))->missingFile());
        $this->assertNull((new RrdGraphException('Something else'))->missingFile());
    }

    public function testMissingRrdsMatchesRelativePaths(): void
    {
        $missing = new MissingRrds();
        $this->assertTrue($missing->add('/opt/librenms/rrd/host1/port-id2.rrd'));
        $this->assertFalse($missing->add('/opt/librenms/rrd/host1/port-id2.rrd'));

        $this->assertTrue($missing->has('/opt/librenms/rrd/host1/port-id2.rrd'));
        $this->assertTrue($missing->has('host1/port-id2.rrd'));
        $this->assertFalse($missing->has('st1/port-id2.rrd'));
        $this->assertFalse($missing->has('host1/port-id1.rrd'));
    }

    private function draw(\Closure $build, \Closure $draw): string
    {
        return Graph::drawRrdGraph($build, $draw, new GraphParameters(['type' => 'port_bits', 'width' => 300, 'height' => 100]));
    }

    /**
     * @param  string|\Closure  $error  error message, or a function of the options returning one
     */
    private function drawException(\Closure $build, string|\Closure $error): RrdGraphException
    {
        try {
            $this->draw($build, function (array $options) use ($error): void {
                throw new RrdGraphException(is_string($error) ? $error : $error($options));
            });
        } catch (RrdGraphException $e) {
            return $e;
        }

        $this->fail('Expected an RrdGraphException');
    }
}
