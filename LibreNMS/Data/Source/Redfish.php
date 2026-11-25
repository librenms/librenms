<?php

/**
 * Redfish.php
 *
 * Query sensor readings from a Redfish service (BMC) over HTTPS
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
 * @copyright  2026 Kevin Zink
 * @author     Kevin Zink <kevin.zink@mpi-hd.mpg.de>
 */

namespace LibreNMS\Data\Source;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\RedfishConnectionFailed;
use LibreNMS\Polling\Method\Config\RedfishConfig;
use LibreNMS\Util\Http;
use LibreNMS\Util\Rewrite;

/**
 * @phpstan-type Reading array{name: string, class: string, value: float|null, warn_low: float|null, warn_high: float|null, crit_low: float|null, crit_high: float|null, chassis: string, source: string}
 */
class Redfish
{
    public function __construct(
        private readonly RedfishConfig $method,
    ) {
    }

    public static function init(?Device $device = null, ?RedfishConfig $config = null): ?self
    {
        $device ??= DeviceCache::getPrimary();
        if ($config === null && ! $device->polling()->isEnabled(PollingMethodType::Redfish)) {
            return null;
        }

        $redfish = $config ?? $device->polling()->get(PollingMethodType::Redfish);
        if (! $redfish instanceof RedfishConfig || ! $redfish->hostname) {
            return null;
        }

        return new static($redfish);
    }

    /**
     * The sensor readings of all chassis, crawled from the thermal and power resources and the
     * per-sensor resources of the Sensors collection of each chassis.
     * The check runs this crawl, discovery and polling read the readings it fetched.
     *
     * @return list<Reading>
     *
     * @throws RedfishConnectionFailed
     */
    public function sensors(): array
    {
        $chassis = $this->get('/Chassis');
        if ($chassis === null) {
            throw new RedfishConnectionFailed("Failed to connect to Redfish on {$this->method->hostname}");
        }

        $memberIds = $chassis['MemberIds'] ?? array_map(
            fn (array $member): string => basename((string) ($member['@odata.id'] ?? '')),
            (array) ($chassis['Members'] ?? [])
        );

        $readings = [];
        foreach ($memberIds as $id) {
            $readings = [...$readings, ...$this->chassisReadings((string) $id)];
        }

        return $this->uniqueNames($readings);
    }

    /**
     * @param  array<string, mixed>  $sensor
     * @return Reading
     */
    private function reading(string $chassisId, array $sensor, string $class, string $readingField, string $resourcePath): array
    {
        $name = (string) ($sensor['Name'] ?? "Unknown $class");

        return [
            'name' => $name,
            'class' => $class,
            'value' => $this->value($sensor[$readingField] ?? null),
            'warn_low' => null,
            'warn_high' => null,
            'crit_low' => null,
            'crit_high' => null,
            'chassis' => $chassisId,
            'source' => "$resourcePath/$name",
        ];
    }

    private function value(mixed $reading): ?float
    {
        return is_numeric($reading) ? (float) $reading : null;
    }

    /**
     * Not every chassis exposes thermal or power resources, or reports values in them:
     * some BMCs only populate the per-sensor resources of the Sensors collection, others
     * (e.g. Dell) report the values and thresholds only in the per-sensor resources.
     *
     * @return list<Reading>
     *
     * @throws RedfishConnectionFailed
     */
    private function chassisReadings(string $chassisId): array
    {
        // the chassis resource references its sensor resources, fall back to the standard paths
        $resource = $this->get("/Chassis/$chassisId") ?? [];
        $thermalPath = $this->linkedPath($resource['Thermal'] ?? null, "/Chassis/$chassisId/Thermal");
        $powerPath = $this->linkedPath($resource['Power'] ?? null, "/Chassis/$chassisId/Power");
        $sensorsPath = $this->linkedPath($resource['Links']['Sensors'] ?? null, "/Chassis/$chassisId/Sensors");

        $readings = [];

        $thermal = $this->get($thermalPath) ?? [];
        foreach ((array) ($thermal['Temperatures'] ?? []) as $sensor) {
            $readings[] = $this->reading($chassisId, $sensor, 'temperature', 'ReadingCelsius', $thermalPath);
        }
        foreach ((array) ($thermal['Fans'] ?? []) as $sensor) {
            $readings[] = $this->reading($chassisId, $sensor, 'fanspeed', 'ReadingRPM', $thermalPath);
        }

        $power = $this->get($powerPath) ?? [];
        foreach ((array) ($power['PowerSupplies'] ?? []) as $sensor) {
            $readings[] = $this->reading($chassisId, $sensor, 'power', 'ReadingWatts', $powerPath);
        }

        return $this->mergeReadings($readings, $this->sensorReadings($chassisId, $sensorsPath));
    }

    /**
     * Merge the readings of the thermal and power resources with the readings of the Sensors
     * collection: entries without a value and without thresholds are dropped (e.g. the status
     * entries of the Power resource), for the rest the per-sensor resources take precedence,
     * they carry the thresholds and the current values.
     *
     * @param  list<Reading>  $readings
     * @param  list<Reading>  $collectionReadings
     * @return list<Reading>
     */
    private function mergeReadings(array $readings, array $collectionReadings): array
    {
        /** @var array<string, Reading> $collection */
        $collection = [];
        foreach ($collectionReadings as $reading) {
            if ($this->hasData($reading)) {
                $collection[$reading['name']] = $reading;
            }
        }

        $merged = [];
        foreach ($readings as $reading) {
            if (! $this->hasData($reading)) {
                continue;
            }

            $merged[] = $collection[$reading['name']] ?? $reading;
        }

        foreach ($collection as $reading) {
            if (! in_array($reading['name'], array_column($merged, 'name'), true)) {
                $merged[] = $reading;
            }
        }

        return $merged;
    }

    /**
     * @param  Reading  $reading
     */
    private function hasData(array $reading): bool
    {
        return $reading['value'] !== null
            || $reading['warn_low'] !== null
            || $reading['warn_high'] !== null
            || $reading['crit_low'] !== null
            || $reading['crit_high'] !== null;
    }

    /**
     * The per-sensor resources of the chassis Sensors collection, with their thresholds.
     *
     * @return list<Reading>
     *
     * @throws RedfishConnectionFailed
     */
    private function sensorReadings(string $chassisId, string $sensorsPath): array
    {
        $collection = $this->get($sensorsPath);
        if ($collection === null) {
            return [];
        }

        $paths = isset($collection['MemberIds'])
            ? array_map(fn (mixed $memberId): string => "$sensorsPath/$memberId", (array) $collection['MemberIds'])
            : array_map(
                fn (array $member): string => $this->memberPath($member['@odata.id'] ?? null, $sensorsPath),
                (array) ($collection['Members'] ?? [])
            );

        $readings = [];
        foreach ($paths as $path) {
            /** @var array<string, mixed>|null $sensor */
            $sensor = $this->get($path);
            if ($sensor === null) {
                continue; // a sensor can disappear between the collection and the request
            }

            $reading = $this->sensorReading($chassisId, $path, $sensor);
            if ($reading !== null) {
                $readings[] = $reading;
            }
        }

        return $readings;
    }

    /**
     * The reading of a single sensor resource, null for reading types that are not mapped to a sensor class.
     *
     * @param  array<string, mixed>  $sensor
     * @return Reading|null
     */
    private function sensorReading(string $chassisId, string $path, array $sensor): ?array
    {
        $class = match ((string) ($sensor['ReadingType'] ?? '')) {
            'Temperature' => 'temperature',
            'Rotational' => 'fanspeed',
            'Power', 'InstantaneousPower' => 'power',
            default => null,
        };
        if ($class === null) {
            return null;
        }

        $thresholds = (array) ($sensor['Thresholds'] ?? []);

        return [
            'name' => (string) ($sensor['Name'] ?? "Unknown $class"),
            'class' => $class,
            'value' => $this->value($sensor['Reading'] ?? null),
            'warn_low' => $this->value($thresholds['LowerCaution']['Reading'] ?? null),
            'warn_high' => $this->value($thresholds['UpperCaution']['Reading'] ?? null),
            'crit_low' => $this->value($thresholds['LowerCritical']['Reading'] ?? null),
            'crit_high' => $this->value($thresholds['UpperCritical']['Reading'] ?? null),
            'chassis' => $chassisId,
            'source' => $path,
        ];
    }

    /**
     * A Redfish reference (e.g. the Thermal link of a chassis resource) as path relative to /redfish/v1,
     * the standard path when the reference is missing or does not point at this service.
     */
    private function linkedPath(mixed $reference, string $default): string
    {
        if (! is_string($reference) || $reference === '') {
            return $default;
        }

        $path = parse_url($reference, PHP_URL_PATH) ?? '';
        if (str_starts_with($path, '/redfish/v1/')) {
            return substr($path, strlen('/redfish/v1'));
        }

        return $default;
    }

    /**
     * The path of a collection member, relative to /redfish/v1.
     */
    private function memberPath(mixed $reference, string $sensorsPath): string
    {
        $path = is_string($reference) ? (parse_url($reference, PHP_URL_PATH) ?? '') : '';
        if (str_starts_with($path, '/redfish/v1/')) {
            return substr($path, strlen('/redfish/v1'));
        }

        return $sensorsPath . '/' . basename($path !== '' ? $path : (string) $reference);
    }

    /**
     * The same sensor name can exist in several chassis, prefix it with the chassis to keep the names unique.
     *
     * @param  list<Reading>  $readings
     * @return list<Reading>
     */
    private function uniqueNames(array $readings): array
    {
        $counts = [];
        foreach ($readings as $reading) {
            $counts[$reading['name']] = ($counts[$reading['name']] ?? 0) + 1;
        }

        return array_map(
            fn (array $reading): array => array_merge($reading, ['name' => $counts[$reading['name']] > 1
                ? "Chassis {$reading['chassis']} {$reading['name']}"
                : $reading['name']]),
            $readings
        );
    }

    /**
     * @return array<string, mixed>|null null when the resource is missing or the BMC cannot serve it
     *
     * @throws RedfishConnectionFailed
     */
    private function get(string $path): ?array
    {
        $client = Http::client()->timeout($this->method->timeout)->withHeaders(['Accept' => 'application/json']);
        if ($this->method->insecure) {
            $client = $client->withOptions(['verify' => false]);
        }
        if ($this->method->username !== '') {
            $client = $client->withBasicAuth($this->method->username, $this->method->password);
        }

        Log::debug("Redfish[{$this->method->hostname}] $path");

        try {
            /** @var Response $response */
            $response = $client->get($this->base() . $path);
        } catch (ConnectionException $e) {
            throw new RedfishConnectionFailed("Failed to connect to Redfish on {$this->method->hostname}: " . $e->getMessage());
        }

        // 404 and 5xx mean the resource is missing or the BMC cannot serve it (e.g. Dell answers 501
        // for resources it does not implement), treat it as absent instead of failing the check
        if ($response->status() === 404 || $response->status() >= 500) {
            return null;
        }

        if (! $response->successful()) {
            throw new RedfishConnectionFailed(match ((int) $response->status()) {
                401, 403 => "Redfish authentication failed on {$this->method->hostname} (HTTP {$response->status()})",
                default => "Redfish $path failed on {$this->method->hostname} (HTTP {$response->status()})",
            });
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RedfishConnectionFailed("Redfish $path on {$this->method->hostname} did not return a JSON object");
        }

        return $json;
    }

    private function base(): string
    {
        $defaultPort = $this->method->scheme === 'http' ? 80 : 443;
        $port = $this->method->port === null || $this->method->port === $defaultPort ? '' : ':' . $this->method->port;

        return $this->method->scheme . '://' . Rewrite::addIpv6Brackets($this->method->hostname) . $port . '/redfish/v1';
    }
}
