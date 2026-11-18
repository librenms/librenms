<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\TimeSeries\SeriesData;
use Illuminate\Support\Arr;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\RRD\Backend\RrdBackendInterface;

/**
 * One rrdtool xport reads all series, which gives them the same points.
 */
class RrdSeriesReader
{
    // rrdtool does not export fewer rows
    private const int MIN_POINTS = 10;

    /** @var array<string, string> */
    private const array CONSOLIDATIONS = ['avg' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'];

    public function __construct(
        private readonly RrdFileResolver $rrdFiles,
        private readonly RrdBackendInterface $backend,
    ) {
    }

    /**
     * @param  array<string, DataSeries>  $series
     * @param  int  $from  unix time
     * @param  int  $to  unix time
     * @param  int  $points  longer ranges are consolidated to about this many
     *
     * @throws RrdException
     */
    public function read(array $series, int $from, int $to, int $points): SeriesData
    {
        $definition = [];
        $ids = [];

        foreach ($series as $key => $item) {
            $filename = $this->rrdFiles->pathFor($item->metric);
            if ($filename === null) {
                continue;
            }

            $ids[$key] = $id = 'ds' . count($ids);
            foreach (self::CONSOLIDATIONS as $name => $cf) {
                array_push($definition, "DEF:$id$name=$filename:$item->field:$cf", "XPORT:$id$name:$id$name");
            }
        }

        if ($ids === []) {
            return new SeriesData;
        }

        $export = $this->backend->xport(['--start', (string) $from, '--end', (string) $to, '--maxrows', (string) max(self::MIN_POINTS, $points), ...$definition]);

        $values = [];
        foreach ($ids as $key => $id) {
            $read = fn (string $name) => array_values($export[$id . $name] ?? []);
            $avg = $read('avg');
            $min = $read('min');
            $max = $read('max');

            $values[$key] = [
                'avg' => $this->scale($avg, $series[$key]->scale),
                'min' => $min === $avg ? null : $this->scale($min, $series[$key]->scale),
                'max' => $max === $avg ? null : $this->scale($max, $series[$key]->scale),
            ];
        }

        return new SeriesData(array_keys(Arr::first($export) ?? []), $values);
    }

    /**
     * @param  list<float|null>  $values
     * @return list<float|null>
     */
    private function scale(array $values, float $scale): array
    {
        if ($scale == 1) {
            return $values;
        }

        return array_map(fn (?float $value) => $value === null ? null : $value * $scale, $values);
    }
}
