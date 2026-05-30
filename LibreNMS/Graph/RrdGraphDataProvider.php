<?php

/**
 * RrdGraphDataProvider.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tristan Rhodes
 * @author     Tristan Rhodes <tristan.rhodes@gmail.com>
 */

namespace LibreNMS\Graph;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Graph\Definitions\Device\PollerPerfGraph;
use Symfony\Component\Process\Process;

/**
 * Reads time-series data for a registered graph definition out of RRD files and
 * returns it as a normalized {@see GraphDataResult}. The definition map below is
 * intentionally tiny and grows as further graph families are added in later PRs.
 */
class RrdGraphDataProvider
{
    /** @var array<string, class-string> */
    private const DEFINITIONS = [
        PollerPerfGraph::GRAPH_TYPE => PollerPerfGraph::class,
    ];

    public function __construct(
        private readonly Rrd $rrd,
    ) {
    }

    public function query(GraphQuery $query): GraphDataResult
    {
        $deviceId = $query->entities['device_id'] ?? null;
        if ($deviceId === null) {
            throw new \RuntimeException(
                "GraphQuery is missing 'device_id' in entities for graph type '{$query->graphType}'."
            );
        }

        $definitionClass = self::DEFINITIONS[$query->graphType] ?? null;
        if ($definitionClass === null) {
            throw new \RuntimeException(
                "Graph type '{$query->graphType}' is not yet supported by the JSON graph data API."
            );
        }

        $device = Device::findOrFail($deviceId);
        $def = new $definitionClass();
        $entities = array_merge(['hostname' => $device->hostname], $query->entities);

        $result = new GraphDataResult(
            id:       $query->graphType . ':' . ($entities['hostname'] ?? ''),
            type:     $query->graphType,
            title:    $def->title($entities),
            subtitle: $def->subtitle($entities),
            unit:     $def->unit($entities),
            from:     $query->from,
            to:       $query->to,
            step:     $query->step,
        );
        $result->setDisplay(array_merge(
            ['renderer' => 'timeseries', 'legend' => true, 'tooltip' => true],
            $def->display()
        ));

        $this->fillSeries($result, $def, $query, $entities);

        return $result;
    }

    /** @param array<string, mixed> $entities */
    private function fillSeries(GraphDataResult $result, PollerPerfGraph $def, GraphQuery $query, array $entities): void
    {
        $groups = [];
        foreach ($def->series($entities) as $seriesDef) {
            $binding = $seriesDef->binding(RrdMetricBinding::SOURCE);
            if (! $binding instanceof RrdMetricBinding) {
                $result->addWarning("Series '{$seriesDef->key}' has no RRD binding; empty series returned.");
                $result->addSeries($this->emptySeries($seriesDef));
                continue;
            }

            $step = $binding->step ?? $query->step;
            $consolidation = strtoupper($binding->consolidation);
            $rrdFile = $this->rrd->name($entities['hostname'] ?? '', $binding->rrdName);
            $key = implode(':', [$rrdFile, $step, $consolidation]);
            $groups[$key][] = [$seriesDef, $binding, $rrdFile, $step, $consolidation];
        }

        $fetchFailed = false;
        foreach ($groups as $entries) {
            [, , $rrdFile, $step, $consolidation] = $entries[0];

            $stepQuery = $step !== $query->step ? $query->withStep($step) : $query;

            try {
                $allData = $this->fetchRrdData($rrdFile, $stepQuery, $consolidation);

                foreach ($entries as [$seriesDef, $binding]) {
                    $series = $this->emptySeries($seriesDef);
                    foreach ($this->pointsForBinding($allData, $binding) as [$tsMs, $value]) {
                        $series->addPoint($tsMs, round($value, 4));
                    }
                    $result->addSeries($series);
                }
            } catch (\RuntimeException $e) {
                Log::debug('RRD graph data fetch failed: ' . $e->getMessage());
                $fetchFailed = true;
                foreach ($entries as [$seriesDef]) {
                    $result->addSeries($this->emptySeries($seriesDef));
                }
            }
        }

        $result->setSource(RrdMetricBinding::SOURCE);
        if ($fetchFailed) {
            $result->setEmptyReason('rrd_fetch_failed');
            $result->addWarning('One or more RRD files could not be read; empty series returned.');
        }
    }

    /**
     * @param array<string, list<array{int, float|null}>> $allData
     * @return list<array{int, float}>
     */
    private function pointsForBinding(array $allData, RrdMetricBinding $binding): array
    {
        if (! is_string($binding->ds)) {
            return [];
        }

        $points = [];
        foreach ($allData[$binding->ds] ?? [] as [$tsMs, $value]) {
            if ($value === null || ! is_finite($value)) {
                continue;
            }

            if ($binding->transform !== null) {
                $value = ($binding->transform)($value);
            }

            if ($value !== null && is_finite($value)) {
                $points[] = [$tsMs, (float) $value];
            }
        }

        return $points;
    }

    private function emptySeries(GraphSeriesDefinition $seriesDef): GraphSeries
    {
        return new GraphSeries(
            name:        $seriesDef->name,
            key:         $seriesDef->key,
            unit:        $seriesDef->unit,
            type:        $seriesDef->type,
            area:        $seriesDef->area,
            stack:       $seriesDef->stack,
            color:       $seriesDef->color,
            lineColor:   $seriesDef->lineColor,
            areaOpacity: $seriesDef->areaOpacity,
            lineOpacity: $seriesDef->lineOpacity,
            lineWidth:   $seriesDef->lineWidth,
            negate:      $seriesDef->negate,
            yAxisIndex:  $seriesDef->yAxisIndex,
        );
    }

    /**
     * Fetch all data sources from one RRD file for the given time range.
     *
     * @return array<string, list<array{int, float|null}>>
     */
    private function fetchRrdData(string $rrdFile, GraphQuery $query, string $consolidation): array
    {
        $command = $this->rrd->buildCommand('fetch', $rrdFile, [
            $consolidation,
            '--start', (string) $query->from,
            '--end', (string) $query->to,
            '--resolution', (string) $query->step,
        ]);

        return self::parseRrdFetchOutput($this->executeRrdFetch($command));
    }

    /**
     * @param string[] $command
     */
    private function executeRrdFetch(array $command): string
    {
        $rrdtool = LibrenmsConfig::get('rrdtool', 'rrdtool');
        $rrdDir = LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd');

        $proc = new Process(array_merge([$rrdtool], $command), $rrdDir);
        $proc->run();

        if (! $proc->isSuccessful()) {
            throw new \RuntimeException('rrdtool fetch failed: ' . $proc->getErrorOutput());
        }

        return $proc->getOutput();
    }

    /**
     * Parse rrdtool fetch output into per-DS data arrays.
     *
     * @return array<string, list<array{int, float|null}>>
     */
    public static function parseRrdFetchOutput(string $output): array
    {
        $lines = explode("\n", trim($output));
        $header = array_shift($lines);
        if ($header === null || trim($header) === '') {
            return [];
        }

        if (($lines[0] ?? null) !== null && trim($lines[0]) === '') {
            array_shift($lines);
        }

        $dsNames = preg_split('/\s+/', trim($header));
        $result = array_fill_keys($dsNames, []);

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            [$tsRaw, $valuesRaw] = explode(':', $line, 2);
            $values = preg_split('/\s+/', trim($valuesRaw));
            $tsMs = (int) trim($tsRaw) * 1000;

            foreach ($dsNames as $i => $ds) {
                $val = $values[$i] ?? null;
                // rrdtool renders unknown/non-finite samples as NaN/-nan/nan/inf depending on
                // platform; is_numeric() rejects them all so they become a gap (null) rather
                // than (float) casting "-nan" to 0.0 and fabricating data the legacy graph omits.
                $result[$ds][] = [
                    $tsMs,
                    ($val === null || ! is_numeric($val)) ? null : (float) $val,
                ];
            }
        }

        return $result;
    }
}
