<?php

/**
 * SnmprecSnmpBackend.php
 *
 * SnmpBackendInterface implementation that retrieves SNMP data from .snmprec fixture files.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Mocks;

use Exception;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpTranslatorInterface;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Oid;
use Log;

class SnmprecSnmpBackend implements SnmpBackendInterface
{
    /** @var array<string, array<string, array{0: string, 1: string}>>|null */
    private static ?array $cache = null;
    /** @var array<string, string> */
    private static array $translateCache = [];
    private readonly SnmpTranslatorInterface $translator;

    public function __construct(?SnmpTranslatorInterface $translator = null)
    {
        $this->translator = $translator ?? resolve(SnmpTranslatorInterface::class);
    }

    public static function clearCache(): void
    {
        self::$cache = null;
        self::$translateCache = [];
    }

    /**
     * snmpget one or more OIDs
     *
     * @param  string[]  $oids
     */
    public function get(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        $community = $this->community($config, $options, $target);
        $snmprec = $this->getSnmprec($community);
        $results = [];

        foreach ($oids as $oid) {
            $num_oid = $this->translateNumber($oid, $options);
            // real snmpget reports missing OIDs inline with exit code 0; SnmpResponse::isValid() keys off this text
            $data = $snmprec[$num_oid] ?? ['4', 'No Such Instance currently exists at this OID'];

            Log::debug("[SNMP] snmpget $community $num_oid: ");

            [$key, $val] = $this->formatEntry($oid, $num_oid, $num_oid, $data[0], $data[1], $options);
            $results[$key] = $val;
        }

        return new SnmpResponse($results);
    }

    /**
     * snmpwalk a single OID subtree
     */
    public function walk(string $target, string $oid, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        $community = $this->community($config, $options, $target);
        $dev = $this->getSnmprec($community);
        $num_oid = $this->translateNumber($oid, $options);
        $results = [];

        $prefix = $num_oid . '.';
        foreach ($dev as $key => $data) {
            if ($key === $num_oid || str_starts_with($key, $prefix)) {
                [$formattedKey, $formattedVal] = $this->formatEntry($oid, $num_oid, $key, $data[0], $data[1], $options);
                $results[$formattedKey] = $formattedVal;
            }
        }

        Log::debug("[SNMP] snmpwalk $community $num_oid");

        return new SnmpResponse($results);
    }

    /**
     * snmpgetnext for one or more OIDs
     *
     * @param  string[]  $oids
     */
    public function next(string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options): SnmpResponse
    {
        $community = $this->community($config, $options, $target);
        $dev = $this->getSnmprec($community);
        $results = [];

        foreach ($oids as $oid) {
            $num_oid = $this->translateNumber($oid, $options);

            Log::debug("[SNMP] snmpnext $community $num_oid: ");
            while (str_contains($num_oid, '.')) {
                $found = false;
                $prefix = $num_oid . '.';
                foreach ($dev as $key => $data) {
                    if ($key === $num_oid || str_starts_with($key, $prefix)) {
                        [$formattedKey, $formattedVal] = $this->formatEntry($oid, $num_oid, $key, $data[0], $data[1], $options);
                        $results[$formattedKey] = $formattedVal;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    break;
                }

                $num_oid = substr($num_oid, 0, strrpos($num_oid, '.'));
            }
        }

        return new SnmpResponse($results);
    }

    private function community(SnmpConfig $config, SnmpQueryOptions $options, string $target = ''): string
    {
        $community = $config->community ?: $target;

        if (! empty($options->context)) {
            $community = $community !== '' ? $community . '_' . $options->context : $options->context;
        }

        return $community;
    }

    /**
     * Format a key-value entry based on query options
     *
     * @return array{0: string, 1: string}
     */
    private function formatEntry(string $oid, string $num_oid, string $key, string $type, string $data, SnmpQueryOptions $options): array
    {
        $isNumeric = $options->oidFormat === SnmpOidOutput::Numeric;

        if ($isNumeric) {
            $val = $type === '6' ? ".$data" : $data;

            return [".$key", $val]; // net-snmp -On prints numeric OIDs with a leading dot
        }

        $oidObj = new Oid($oid);
        $indexSuffix = substr($key, strlen($num_oid));

        if ($type === '6') {
            $mib = $oidObj->getMib();
            $translateOptions = clone $options;
            if ($mib) {
                $translateOptions->mibs = array_unique(array_merge($translateOptions->mibs, [$mib]));
            }
            $data = $this->translate($data, $translateOptions);
        }

        if (! empty($oidObj->oid) && $oidObj->isNumeric()) {
            $oid = $this->translate($oidObj->oid, $options);
        }

        return ["$oid$indexSuffix", $data];
    }

    private function cacheSnmprec(string $file): void
    {
        if (isset(self::$cache[$file])) {
            return;
        }
        self::$cache[$file] = [];

        $path = base_path("/tests/snmpsim/$file.snmprec");
        if (! file_exists($path)) {
            return;
        }

        $data = file_get_contents($path);
        $line = strtok($data, "\r\n");
        while ($line !== false) {
            if (trim($line) !== '') {
                $parts = explode('|', $line, 3);
                if (count($parts) === 3) {
                    [$oid, $type, $itemData] = $parts;
                    if ($type == '4') {
                        $itemData = trim($itemData);
                    } elseif ($type == '6') {
                        $itemData = trim($itemData, '.');
                    } elseif ($type == '4x') {
                        // MacAddress type is stored as hex string, but we don't understand mibs
                        if (
                            str_starts_with($oid, '1.3.6.1.2.1.2.2.1.6') // IF-MIB::ifPhysAddress
                            || str_starts_with($oid, '1.3.6.1.2.1.17.1.1.0') // BRIDGE-MIB::dot1dBaseBridgeAddress.0
                            || str_starts_with($oid, '1.3.6.1.4.1.890.1.5.13.13.8.1.1.20') // IES5206-MIB::slotModuleMacAddress
                        ) {
                            $itemData = Mac::parse($itemData)->readable();
                        } else {
                            $hex = trim($itemData);
                            if (strlen($hex) % 2 !== 0) {
                                $hex = '0' . $hex;
                            }
                            $decoded = @hex2bin($hex);
                            $itemData = $decoded !== false ? $decoded : $itemData;
                        }
                    }

                    self::$cache[$file][$oid] = [$type, $itemData];
                }
            }
            $line = strtok("\r\n");
        }
    }

    /**
     * Get all data of the specified $community from the snmprec cache
     *
     * @param  string  $community  snmp community to return
     * @return array<string, array{0: string, 1: string}> array of the data containing: [$oid][$type, $data]
     *
     * @throws Exception this $community is not cached
     */
    private function getSnmprec(string $community): array
    {
        if (! isset(self::$cache[$community])) {
            $this->cacheSnmprec($community);
        }

        if (isset(self::$cache[$community])) {
            return self::$cache[$community];
        }

        throw new Exception("SNMPREC: community $community not cached");
    }

    private function translate(string $oid, SnmpQueryOptions $options): string
    {
        $cacheKey = $oid . "\0" . $options->oidFormat->name . "\0" . implode(':', $options->mibDirs) . "\0" . implode(':', $options->mibs);

        if (isset(self::$translateCache[$cacheKey])) {
            return self::$translateCache[$cacheKey];
        }

        $number = $this->translator->translate($oid, $options);

        return self::$translateCache[$cacheKey] = $number;
    }

    /**
     * Get the numeric oid of an oid
     * The leading dot is omitted by default to be compatible with snmpsim
     *
     * @param  string  $oid  the oid to translate
     * @return string the oid in numeric format (1.3.4.5)
     *
     * @throws Exception Could not translate the oid
     */
    private function translateNumber(string $oid, SnmpQueryOptions $options): string
    {
        if (Oid::of($oid)->isNumeric()) {
            return ltrim($oid, '.');
        }

        $translateOptions = clone $options;
        $translateOptions->oidFormat = SnmpOidOutput::Numeric;

        $number = $this->translate($oid, $translateOptions);

        if (empty($number)) {
            throw new Exception('Could not translate oid: ' . $oid . PHP_EOL);
        }

        return ltrim($number, '.');
    }
}
