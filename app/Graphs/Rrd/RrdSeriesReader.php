<?php

namespace App\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\SeriesSum;
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
     * Values are in the units of the series. The lowest and highest of formulas and sums are those of their averages, so they are left out.
     *
     * @param  array<string, DataSeries|SeriesSum>  $series  sums add up series listed before them
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
            $id = 'ds' . count($ids);
            if ($item instanceof SeriesSum) {
                $members = [];
                foreach ($item->series as $member) {
                    if (isset($ids[$member])) {
                        $members[] = $ids[$member] . 'avg';
                    }
                }
                if ($members === []) {
                    continue;
                }

                $ids[$key] = $id;
                $definition[] = RrdSeriesDefinition::sumDef($id . 'avg', $members);
                $definition[] = "XPORT:{$id}avg:{$id}avg";
                continue;
            }

            $filename = $this->rrdFiles->pathFor($item->metric);
            if ($filename === null) {
                continue;
            }

            $ids[$key] = $id;
            $consolidations = $item->field instanceof Formula ? ['avg' => 'AVERAGE'] : self::CONSOLIDATIONS;
            foreach ($consolidations as $name => $cf) {
                array_push($definition, ...RrdSeriesDefinition::valueDefs($id . $name, $filename, $item, $cf));
                $definition[] = "XPORT:$id$name:$id$name";
            }
        }

        if ($ids === []) {
            return new SeriesData;
        }

        $export = $this->backend->xport(['--start', (string) $from, '--end', (string) $to, '--maxrows', (string) max(self::MIN_POINTS, $points), ...$definition]);

        $values = [];
        foreach ($ids as $key => $id) {
            $read = fn (string $name) => isset($export[$id . $name]) ? array_values($export[$id . $name]) : null;
            $avg = $read('avg') ?? [];
            $min = $read('min');
            $max = $read('max');

            $values[$key] = [
                'avg' => $avg,
                'min' => $min === $avg ? null : $min,
                'max' => $max === $avg ? null : $max,
            ];
        }

        return new SeriesData(array_keys(Arr::first($export) ?? []), $values);
    }
}
