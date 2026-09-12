<?php

/**
 * Lcossx3.php
 *
 * LANCOM LCOS SX GS-23xx series
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link https://www.librenms.org
 *
 * @author Marcus Kuchler - NETHEON S.M.PC
 */

namespace LibreNMS\OS;

use App\Models\Mempool;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;
use SnmpQuery;

class Lcossx3 extends OS implements MempoolsDiscovery, ProcessorDiscovery, ProcessorPolling
{
    /**
     * Return the model-specific GS-23xx system information base OID.
     *
     * Example:
     *
     * sysObjectID:
     * .1.3.6.1.4.1.2356.800.3.2327
     *
     * System information base:
     * .1.3.6.1.4.1.2356.800.3.2327.1.1
     */
    private function getSystemInfoBaseOid(): string
    {
        return rtrim((string) $this->getDevice()->sysObjectID, '.') . '.1.1';
    }

    /**
     * Return the GS-23xx CPU load OID.
     */
    private function getProcessorOid(): string
    {
        return $this->getSystemInfoBaseOid() . '.13.0';
    }

    /**
     * Return the GS-23xx total RAM OID.
     *
     * Example value:
     * 64 M
     */
    private function getMemoryTotalOid(): string
    {
        return $this->getSystemInfoBaseOid() . '.8.0';
    }

    /**
     * Return the GS-23xx free RAM OID.
     *
     * The value is reported in KiB.
     */
    private function getMemoryFreeOid(): string
    {
        return $this->getSystemInfoBaseOid() . '.1501.0';
    }

    /**
     * Convert a GS-23xx memory size string to KiB.
     *
     * Supported examples:
     *
     * 64 M
     * 128 M
     * 512 K
     * 1 G
     * 64 MB
     * 128 MiB
     */
    private function parseMemorySizeToKiB(string $value): ?int
    {
        $value = trim($value);
        $value = trim($value, "\"'");

        if (! preg_match('/^([\d.]+)\s*([KMG])(?:I?B|B)?$/i', $value, $matches)) {
            return null;
        }

        $amount = (float) $matches[1];
        $unit = strtoupper($matches[2]);

        return match ($unit) {
            'K' => (int) round($amount),
            'M' => (int) round($amount * 1024),
            'G' => (int) round($amount * 1024 * 1024),
            default => null,
        };
    }

    /**
     * Convert the GS-23xx CPU string to individual CPU averages.
     *
     * Expected GS-23xx format:
     *
     * CPU Load (100ms, 1s, 10s) : 24%, 20%, 18%
     *
     * Returned array:
     *
     * [
     *     '100ms' => 24,
     *     '1s' => 20,
     *     '10s' => 18,
     * ]
     */
    private function convertProcessorData(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $firstEntry = reset($input);

        if (is_array($firstEntry)) {
            $rawValue = reset($firstEntry);
        } else {
            $rawValue = $firstEntry;
        }

        if (! is_scalar($rawValue)) {
            return [];
        }

        $rawValue = trim((string) $rawValue);
        $rawValue = trim($rawValue, "\"'");

        if (preg_match(
            '/CPU\s+Load\s*\(\s*100ms\s*,\s*1s\s*,\s*10s\s*\)\s*:\s*(\d+)%\s*,\s*(\d+)%\s*,\s*(\d+)%/i',
            $rawValue,
            $matches
        )) {
            return [
                '100ms' => (int) $matches[1],
                '1s' => (int) $matches[2],
                '10s' => (int) $matches[3],
            ];
        }

        /*
         * Compatibility fallback for LCOS-SX-style output:
         *
         * 100ms:87%, 1s:49%, 10s:42%
         */
        $processorData = [];

        foreach (explode(',', $rawValue) as $cpuPart) {
            $cpuValues = explode(':', $cpuPart, 2);

            if (count($cpuValues) !== 2) {
                continue;
            }

            $cpuName = trim($cpuValues[0]);
            $cpuValue = trim(str_replace('%', '', $cpuValues[1]));

            if ($cpuName === '' || ! is_numeric($cpuValue)) {
                continue;
            }

            $processorData[$cpuName] = (int) $cpuValue;
        }

        return $processorData;
    }

    /**
     * Discover GS-23xx processors.
     *
     * The switch reports three CPU averages:
     *
     * 100 ms
     * 1 second
     * 10 seconds
     */
    public function discoverProcessors(): array
    {
        $processorOid = $this->getProcessorOid();
        $processorValue = SnmpQuery::get($processorOid)->value();

        if ($processorValue === '') {
            return [];
        }

        $processorValues = $this->convertProcessorData([$processorValue]);

        if ($processorValues === []) {
            return [];
        }

        $processors = [];
        $index = 0;

        foreach ($processorValues as $processorName => $processorUsage) {
            $processors[] = Processor::discover(
                'lcossx3',
                $this->getDeviceId(),
                $processorOid,
                $index,
                'Processor ' . $processorName,
                1,
                $processorUsage,
                100
            );

            $index++;
        }

        return $processors;
    }

    /**
     * Poll GS-23xx processors.
     */
    public function pollProcessors(array $processors): array
    {
        $processorOid = $this->getProcessorOid();
        $processorValue = SnmpQuery::get($processorOid)->value();

        if ($processorValue === '') {
            return [];
        }

        $processorValues = array_values(
            $this->convertProcessorData([$processorValue])
        );

        if ($processorValues === []) {
            return [];
        }

        $pollData = [];

        foreach ($processors as $processor) {
            $processorId = $processor['processor_id'];
            $processorIndex = (int) $processor['processor_index'];

            if (! array_key_exists($processorIndex, $processorValues)) {
                continue;
            }

            $pollData[$processorId] = $processorValues[$processorIndex];
        }

        return $pollData;
    }

    /**
     * Discover the main GS-23xx memory pool.
     *
     * Total RAM is supplied as a DisplayString, for example:
     *
     * 64 M
     *
     * Free RAM is supplied as an Integer32 in KiB.
     */
    public function discoverMempools(): Collection
    {
        $totalOid = $this->getMemoryTotalOid();
        $freeOid = $this->getMemoryFreeOid();

        $totalValue = SnmpQuery::get($totalOid)->value();
        $freeValue = SnmpQuery::get($freeOid)->value();

        if (
            $totalValue === ''
            || $freeValue === ''
        ) {
            return new Collection();
        }

        $totalKiB = $this->parseMemorySizeToKiB(
            (string) $totalValue
        );

        $freeValue = trim((string) $freeValue);
        $freeValue = trim($freeValue, "\"'");

        if (! preg_match('/^-?\d+$/', $freeValue)) {
            return new Collection();
        }

        $freeKiB = (int) $freeValue;

        if (
            $totalKiB === null
            || $totalKiB <= 0
            || $freeKiB < 0
            || $freeKiB > $totalKiB
        ) {
            return new Collection();
        }

        $mempool = new Mempool([
            'mempool_index' => 0,
            'mempool_type' => 'lcossx3',
            'mempool_class' => 'system',
            'mempool_descr' => 'Main Memory',
            'mempool_precision' => 1024,
            'mempool_free_oid' => $freeOid,
            'mempool_perc_warn' => 90,
        ]);

        /*
         * totalKiB and freeKiB are supplied in KiB.
         *
         * The multiplier converts the values to bytes before they are
         * stored in the LibreNMS database.
         */
        $mempool->fillUsage(
            null,
            $totalKiB,
            $freeKiB,
            null,
            1024
        );

        return collect()->push($mempool);
    }
}
