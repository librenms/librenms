<?php

namespace App\Graphs\Rrd;

use App\Data\TimeSeries\SeriesData;
use App\Data\TimeSeries\SeriesValues;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Interfaces\Data\SeriesReaderInterface;
use LibreNMS\RRD\Backend\RrdBackendInterface;

/**
 * Reads series from rrd files with one rrdtool xport, which consolidates them to the same points
 */
class RrdSeriesReader implements SeriesReaderInterface
{
    // rrdtool does not export fewer rows
    private const int MIN_POINTS = 10;

    /** @var array<string, string> rrd consolidation function by the suffix of the exported value */
    private const array CONSOLIDATIONS = ['avg' => 'AVERAGE', 'min' => 'MIN', 'max' => 'MAX'];

    public function __construct(
        private readonly RrdFileResolver $rrdFiles,
        private readonly RrdBackendInterface $backend,
    ) {
    }

    /**
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
            foreach (self::CONSOLIDATIONS as $suffix => $cf) {
                array_push($definition, "DEF:$id$suffix=$filename:$item->field:$cf", "XPORT:$id$suffix:$id$suffix");
            }
        }

        if ($ids === []) {
            return new SeriesData;
        }

        $export = $this->backend->xport(['--start', (string) $from, '--end', (string) $to, '--maxrows', (string) max(self::MIN_POINTS, $points), ...$definition]);

        $values = [];
        foreach ($ids as $key => $id) {
            $column = fn (string $suffix): array => $this->scale($export->columns[$id . $suffix] ?? [], $series[$key]->scale);

            $values[$key] = new SeriesValues($column('avg'), $column('min'), $column('max'));
        }

        return new SeriesData($export->timestamps, $values);
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
