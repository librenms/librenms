<?php

namespace App\Services;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\RRD\RrdProcess;

class CurrentRrdMetricService
{
    public function __construct(
        private readonly Rrd $rrd,
        private readonly RrdProcess $rrdProcess,
    ) {
    }

    public function filename(string $hostname, string|array $rrdName): string
    {
        return $this->rrd->name($hostname, $rrdName);
    }

    public function latestAverage(string $filename, string $dataset = 'value', ?int $now = null): ?RrdSample
    {
        return $this->latestAverages($filename, [$dataset], $now)[$dataset] ?? null;
    }

    /** Fetch one RRD once even when several datasets are needed. */
    public function latestAverages(string $filename, array $datasets, ?int $now = null): array
    {
        $now ??= time();
        $step = (int) LibrenmsConfig::get('rrd.step', 300);
        $maxAge = max($step * 3, 900);
        $start = $now - max($step * 6, 1800);
        $command = implode(' ', $this->rrd->buildCommand('fetch', $filename, [
            'AVERAGE',
            '--start',
            (string) $start,
            '--end',
            (string) $now,
        ]));

        try {
            $output = $this->rrdProcess->run($command);
        } catch (RrdException) {
            return [];
        }

        $result = [];
        foreach ($datasets as $dataset) {
            $result[$dataset] = self::parseLatestValue($output, $dataset, $now, $maxAge);
        }

        return $result;
    }

    public static function parseLatestValue(string $output, string $dataset, int $now, int $maxAge): ?RrdSample
    {
        $lines = preg_split('/\R/', trim($output)) ?: [];
        $datasets = [];
        $latest = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($datasets === [] && ! preg_match('/^\d+:/', $line)) {
                $datasets = preg_split('/\s+/', $line) ?: [];

                continue;
            }

            if (! preg_match('/^(\d+):\s+(.+)$/', $line, $matches)) {
                continue;
            }

            $timestamp = (int) $matches[1];
            $values = preg_split('/\s+/', trim($matches[2])) ?: [];
            $index = array_search($dataset, $datasets, true);
            if ($index === false || ! isset($values[$index]) || ! is_numeric($values[$index])) {
                continue;
            }

            $value = (float) $values[$index];
            if (! is_finite($value)) {
                continue;
            }

            $latest = new RrdSample($timestamp, [$dataset => $value]);
        }

        if ($latest === null || $latest->timestamp < $now - $maxAge || $latest->timestamp > $now + $maxAge) {
            return null;
        }

        return $latest;
    }
}
