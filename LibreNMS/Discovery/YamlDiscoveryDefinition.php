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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery;

use App\View\SimpleTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
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
        return $this->fields[$name]?->value;
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

    public function discover($yaml, $attributes = []): Collection
    {
        $models = new Collection;
        $fetchedData = $this->preFetch($yaml);

        foreach ($yaml['data'] ?? [] as $yamlItem) {
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
                        ->partition(fn($oid) => Oid::of($oid)->isNumeric())->toArray();
            }

            $snmp_data = [];
            if (! empty($numeric_oids)) {
                $snmpQuery = SnmpQuery::numeric();
                if (isset($yamlItem['snmp_flags'])) {
                    $snmpQuery->options($yamlItem['snmp_flags']);
                }
                $snmp_data = $snmpQuery->get($numeric_oids)->values();
                $fetchedData = array_merge($fetchedData, $snmp_data);
            }

            if (! empty($oids)) {
                $snmpQuery = SnmpQuery::enumStrings()->numericIndex();
                if (isset($yamlItem['snmp_flags'])) {
                    $snmpQuery->options($yamlItem['snmp_flags']);
                }
                $response = $snmpQuery->walk($oids);
                $response->valuesByIndex($snmp_data); // load into the $snmp_data array
                $response->valuesByIndex($fetchedData); // load into the $fetchedData array
            }

            $count = 0;

            foreach ($snmp_data as $index => $snmpItem) {
                if (YamlDiscovery::canSkipItem(null, $index, $yamlItem, $yaml, $fetchedData)) {
                    echo 's';
                    continue;
                }

                $count++;
                $modelAttributes = $attributes; // default attributes

                /** @var YamlDiscoveryField $field */
                foreach ($this->fields as $field) {
                    // fill attributes
                    $field->calculateValue($yamlItem, $fetchedData, $index, $count);
                    $modelAttributes[$field->model_column] = $field->value;
                }

                $this->fillNumericOids($modelAttributes, $yamlItem, $index);

                $newModel = new $this->model($modelAttributes);

                if ($this->afterEachCallback) {
                    call_user_func($this->afterEachCallback, $newModel, $this, $yamlItem, $index, $count);
                }

                $models->push($newModel);
            }
        }

        return $models;
    }

    private function preFetch(array $yaml): array
    {
        if (empty($yaml['pre-cache']['oids'])) {
            return [];
        }

        $query = SnmpQuery::enumStrings()->numericIndex();

        if (isset($yaml['pre-cache']['snmp_flags'])) {
            $query->options($yaml['pre-cache']['snmp_flags']);
        }

        return $query->walk($yaml['pre-cache']['oids'])->valuesByIndex();
    }

    private function fillNumericOids(array &$modelAttributes, array $yaml, int|string $index): void
    {
        foreach($this->fields as $field) {
            if($field->isOid) {
                $num_oid = null;

                if (call_user_func($field->should_poll, $this)) {
                    $yaml_num_oid_field_name = $field->key . '_num_oid';

                    if (isset($yaml[$yaml_num_oid_field_name])) {
                        $num_oid = SimpleTemplate::parse($yaml[$yaml_num_oid_field_name], ['index' => $index]);
                    } elseif (isset($yaml[$field->key])) {
                        if (Oid::of($yaml[$field->key])->isNumeric()) {
                            // if numeric, assume it is a scalar, not a table
                            $num_oid = $yaml[$field->key];
                        } else {
                            Log::critical("$yaml_num_oid_field_name should be added to the discovery yaml to increase discovery performance");

                            $num_oid = Oid::of($yaml[$field->key] . '.' . $index)->toNumeric(cache: 0);  // don't cache because of idiotic vendors naming MIBs the same
                        }
                    }
                }

                $modelAttributes[$field->model_column . '_oid'] = $num_oid;
            }
        }
    }
}
