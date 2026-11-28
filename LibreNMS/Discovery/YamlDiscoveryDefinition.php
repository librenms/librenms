<?php

/*
 * YamlDiscoveryDefiniton.php
 *
 * -Description-
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery;

use App\Models\Eventlog;
use App\View\SimpleTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Snmp\SnmpQueryInterface;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
use LibreNMS\Exceptions\InvalidOidException;
use LibreNMS\Exceptions\SnmpConnectionException;
use LibreNMS\Util\Oid;
use SnmpQuery;

class YamlDiscoveryDefinition
{
    /**
     * @var \LibreNMS\Discovery\Yaml\YamlDiscoveryField[]
     */
    private array $fields = [];

    /**
     * @var callable|null
     */
    private $afterEachCallback = null;

    public function __construct(
        private readonly string $model,
    ) {
    }

    public static function make(string $model): static
    {
        return new static($model);
    }

    public function addField(YamlDiscoveryField $field): static
    {
        $this->fields[$field->key] = $field;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $name): ?YamlDiscoveryField
    {
        return $this->fields[$name] ?? null;
    }

    public function getFieldCurrentValue(string $name): mixed
    {
        return $this->fields[$name]->value;
    }

    public function afterEach(callable $callable): static
    {
        $this->afterEachCallback = $callable;

        return $this;
    }

    protected function resetValues(): void
    {
        foreach ($this->fields as $field) {
            $field->value = null;
        }
    }

    /**
     * Discover models from a yaml section.
     * Without walked data each item fetches its own oids, with walked data (see walkAll()) items read their rows from it.
     *
     * @param  array<string, mixed>  $yaml
     * @param  array<string, mixed>  $attributes  default attributes of the models
     * @param  array<string, array<int|string, mixed>>|null  $walked
     */
    public function discover($yaml, $attributes = [], ?array $walked = null): Collection
    {
        $models = new Collection;
        $fetchedData = $walked ?? $this->preFetch($yaml);

        foreach ($yaml['data'] ?? [] as $yamlItem) {
            $snmp_data = $walked === null ? $this->fetchItem($yamlItem, $fetchedData) : $this->walkedRows($yamlItem, $walked);
            $count = 0;

            foreach ($snmp_data as $index => $snmpItem) {
                if ($this->canSkipItem($snmpItem, $index, $yamlItem, $yaml, $fetchedData)) {
                    Log::debug('Data at index ' . $index . ' skipped due to skip_values');
                    Log::info('x');
                    continue;
                }

                $count++;
                $modelAttributes = $attributes; // default attributes

                /** @var YamlDiscoveryField $field */
                foreach ($this->fields as $field) {
                    // fill attributes
                    if ($field->model_column !== null) {
                        $field->calculateValue($yamlItem, $fetchedData, $index, $count);
                        $modelAttributes[$field->model_column] = $field->value;
                    }
                }

                // if no pollable oid found, skip this index
                if (! $this->fillNumericOids($modelAttributes, $yamlItem, $index)) {
                    Log::debug("skipping index $index, no pollable oid found");
                    continue;
                }

                $newModel = new $this->model($modelAttributes);

                if ($this->afterEachCallback) {
                    call_user_func($this->afterEachCallback, $newModel, $this, $yamlItem, $index, $count);
                }

                $models->push($newModel);
            }
        }

        return $models;
    }

    /**
     * Walk the oids of every item of the given yaml sections once, so items can use the data of other items and sections.
     * Oids that items reference (in templates, fields or skip_values) are walked too when they are not in the walked data:
     * oids that include a MIB name and the given oids other parts of the yaml walk.
     * Oids are fetched as snmp_flags of the item define, by default with numeric indexes and enums, without display hints.
     * Walks are cached for the device, so sections can be walked separately.
     *
     * @param  iterable<array{data?: list<array<string, mixed>>}>  $sections
     * @param  string[]  $knownOids  oids other parts of the yaml walk, they may be referenced without a MIB name
     * @return array<string, array<int|string, mixed>> the walked data of each oid, by index
     *
     * @throws SnmpConnectionException when the device did not answer
     */
    public static function walkAll(iterable $sections, array $knownOids = []): array
    {
        $walked = [];
        $items = [];

        foreach ($sections as $section) {
            foreach ($section['data'] ?? [] as $item) {
                $items[] = $item;
                foreach (Arr::wrap($item['oid'] ?? []) as $oid) {
                    self::walkOid($walked, $oid, $item);
                }
            }
        }

        $columns = [];
        foreach ($walked as $rows) {
            foreach ($rows as $row) {
                $columns += array_flip(array_keys((array) $row));
            }
        }

        foreach ($items as $item) {
            foreach (self::references($item) as $oid) {
                if (! isset($walked[$oid]) && ! isset($columns[$oid]) && (str_contains($oid, '::') || in_array($oid, $knownOids))) {
                    Log::debug("Walking $oid, it is referenced by the yaml");
                    self::walkOid($walked, $oid, []);
                }
            }
        }

        return $walked;
    }

    /**
     * @param  array<string, array<int|string, mixed>>  $walked
     * @param  array<string, mixed>  $item
     */
    /**
     * @throws SnmpConnectionException
     */
    private static function walkOid(array &$walked, string $oid, array $item): void
    {
        if (array_key_exists($oid, $walked)) {
            return;
        }

        if (isset($item['snmp_flags'])) {
            $flags = Arr::wrap($item['snmp_flags']);
        } else {
            // mib names are kept in the output when the oid includes one
            $flags = [str_contains($oid, '::') ? '-OteQUSab' : '-OteQUsab', '-Pu'];
        }

        if (! isset($item['snmp_no_Ih_flag'])) {
            $flags[] = '-Ih';
        }

        $walked[$oid] = [];
        SnmpQuery::cache()->options($flags)->bulk($item['snmp_bulk'] ?? true)->walk($oid)->throwIfConnectionError()->valuesByIndex($walked[$oid]);
    }

    /**
     * The oids a yaml item uses to fill its fields
     *
     * @param  array<string, mixed>  $item
     * @return string[]
     */
    private static function references(array $item): array
    {
        $references = [];

        foreach ($item as $key => $value) {
            if (in_array($key, ['oid', 'num_oid', 'states', 'snmp_flags'], true)) {
                continue;
            }

            if ($key === 'skip_values') {
                foreach (Arr::wrap($value) as $skip) {
                    if (is_array($skip) && isset($skip['oid']) && $skip['oid'] !== 'index') {
                        // an oid with an index is looked up by the index
                        $references[] = explode('.', (string) $skip['oid'], 2)[0];
                    }
                }

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            if (preg_match_all('/{{\s*\$?([^\s}|]+)/', $value, $matches)) {
                foreach ($matches[1] as $name) {
                    if (! preg_match('/^(index|count|subindex\d+|index_string.*|InetAddress_.*)$/', $name)) {
                        $references[] = $name;
                    }
                }
            } elseif (preg_match('/^[\w-]+::[\w.-]+(:\d+(-\d+)?)?$/', $value)) {
                $references[] = $value;
            }
        }

        // sub index references (name:1-3) are walked by name
        return array_unique(array_map(fn ($reference) => preg_replace('/:\d+(-\d+)?$/', '', $reference), $references));
    }

    /**
     * Fetch the rows of a yaml item, loading the data into $fetchedData too.
     *
     * @param  array<string, mixed>  $yamlItem
     * @param  array<int|string, mixed>  $fetchedData
     * @return array<int|string, array<string, mixed>|string> rows by index, the value itself for numeric oids
     */
    private function fetchItem(array $yamlItem, array &$fetchedData): array
    {
        // if a table is given fetch it otherwise, fetch scalar or table columns individually
        if (isset($yamlItem['oid'])) {
            if (Oid::of($yamlItem['oid'])->isNumeric()) {
                $oids = [];
                $numeric_oids = $yamlItem['oid'];
            } else {
                $oids = $yamlItem['oid'];
                $numeric_oids = [];
            }
        } else {
            [$numeric_oids, $oids] = collect($yamlItem)->only(collect($this->fields)->where('isOid')->keys())
                ->partition(fn ($oid) => Oid::of($oid)->isNumeric())->toArray();
        }

        $snmp_data = [];
        if (! empty($numeric_oids)) {
            $snmp_data = $this->snmpQuery($yamlItem)->numeric()->get($numeric_oids)->values();
            $fetchedData = array_merge($fetchedData, $snmp_data);
        }

        if (! empty($oids)) {
            $response = $this->snmpQuery($yamlItem, enumStrings: true)->walk($oids);
            $response->valuesByIndex($snmp_data); // load into the $snmp_data array
            $response->valuesByIndex($fetchedData); // load into the $fetchedData array
        }

        return $snmp_data;
    }

    /**
     * The rows of a yaml item from walked data, the rows of all oids of the item are merged.
     *
     * @param  array<string, mixed>  $yamlItem
     * @param  array<string, array<int|string, mixed>>  $walked
     * @return array<int|string, array<string, mixed>>
     */
    private function walkedRows(array $yamlItem, array $walked): array
    {
        $rows = [];

        foreach (Arr::wrap($yamlItem['oid'] ?? []) as $oid) {
            foreach ($walked[$oid] ?? [] as $index => $row) {
                $rows[$index] = array_merge($rows[$index] ?? [], (array) $row);
            }
        }

        return $rows;
    }

    /**
     * Check the skip rules of the yaml against the value of a row.
     * Rows of numeric oids are the value itself, they have no value to compare.
     *
     * @param  array<string, mixed>|string  $row
     * @param  array<string, mixed>  $yamlItem
     * @param  array<string, mixed>  $yaml
     * @param  array<int|string, mixed>  $fetchedData
     */
    protected function canSkipItem(array|string $row, int|string $index, array $yamlItem, array $yaml, array $fetchedData): bool
    {
        $value = is_array($row) ? $row[$yamlItem['value'] ?? $yamlItem['oid'] ?? ''] ?? null : null;

        return YamlDiscovery::canSkipItem($value, $index, $yamlItem, $yaml, $fetchedData);
    }

    private function preFetch(array $yaml): array
    {
        $data = $yaml['additional_oids'] ?? $yaml['pre-cache'] ?? [];

        if (empty($data['oids'])) {
            return [];
        }

        if (isset($yaml['pre-cache'])) {
            Eventlog::log('This device discovery yaml is using deprecated pre-cache key, use additional_oids instead.  pre-cache will be removed in a future version.');
        }

        return $this->snmpQuery($data, enumStrings: true)->walk($data['oids'])->valuesByIndex();
    }

    /**
     * Build an snmp query for a yaml section. Indexes are numeric (so numeric oids can be built from them) unless
     * snmp_flags override the options. Display hints are always disabled to match the raw values seen when polling.
     *
     * @param  array{snmp_flags?: string|string[]}  $yaml
     */
    private function snmpQuery(array $yaml, bool $enumStrings = false): SnmpQueryInterface
    {
        if (isset($yaml['snmp_flags'])) {
            return SnmpQuery::options($yaml['snmp_flags'])->displayHints(false);
        }

        $query = $enumStrings ? SnmpQuery::enumStrings() : SnmpQuery::make();

        return $query->numericIndex()->displayHints(false);
    }

    private function fillNumericOids(array &$modelAttributes, array $yaml, int|string $index): bool
    {
        $num_oid_found = false;

        foreach ($this->fields as $field) {
            if ($field->isOid) {
                $num_oid = null;
                [$field_name, $column_name] = $field->getNumericNames();

                if (call_user_func($field->should_poll, $this)) {
                    if (isset($yaml[$field_name])) {
                        $num_oid = SimpleTemplate::parse($yaml[$field_name], ['index' => $index]);
                        $num_oid_found = true;
                    } elseif (isset($yaml[$field->key])) {
                        if (Oid::of($yaml[$field->key])->isNumeric()) {
                            // if numeric, assume it is a scalar, not a table
                            $num_oid = $yaml[$field->key];
                            $num_oid_found = true;
                        } else {
                            Log::debug("$field_name should be added to the discovery yaml to increase discovery performance");
                            try {
                                // if index is numeric, exclude it so we can cache the translation
                                if (Oid::of($index)->isNumeric()) {
                                    $num_oid = Oid::of($yaml[$field->key])->toNumeric() . '.' . $index;
                                } else {
                                    $num_oid = Oid::of($yaml[$field->key] . '.' . $index)->toNumeric();
                                }

                                $num_oid_found = true;
                            } catch (InvalidOidException $e) {
                                Log::critical($e->getMessage());
                            }
                        }
                    }
                }

                $modelAttributes[$column_name] = $num_oid;
            }
        }

        return $num_oid_found;
    }
}
