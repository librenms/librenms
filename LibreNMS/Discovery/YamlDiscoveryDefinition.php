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

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
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
        $fetchedData = [];  // TODO preCache?
//        dd($yaml);

        foreach ($yaml['data'] ?? [] as $yamlItem) {
            $oids = Arr::only($yamlItem, collect($this->fields)->where('isOid')->keys()->all());
            $response = SnmpQuery::enumStrings()->walk($oids);
            $fetchedData = array_replace_recursive($fetchedData, $response->table(100)); // merge into cached data
            $count = 0;

            foreach ($response->valuesByIndex() as $index => $snmpItem) {
                if (YamlDiscovery::canSkipItem(null, $index, $yaml, [], $fetchedData)) {
                    echo 's';
                    continue;
                }

                $count++;
                $modelAttributes = $attributes; // default attributes

                // fill attributes
                foreach ($this->fields as $field) {
                    $field->value = $field->handle($yamlItem, $fetchedData, $index);
                    $modelAttributes[$field->model_column] = $field->value;
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
}
