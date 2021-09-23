<?php
/*
 * DataGroup.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data;

use App\Exceptions\InvalidDataDefinitionException;
use LibreNMS\Enum\DataRateType;
use LibreNMS\Enum\DataType;

abstract class DataGroup
{
    private $name;
    private $dataSets = [];
    private $tags = [];
    private $annotations = [];
    private $timestamp;

    protected function __construct($name)
    {
        $this->name = $name;
    }

//    abstract public static function make(...$vars): DataGroup; // allow different defs?

    public function getName()
    {
        return $this->name;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Add dataset (convenience for nicer code)
     *
     * @param $name
     * @param  int  $rate
     * @param  int  $type
     * @param  null  $min
     * @param  null  $max
     * @return static
     */
    protected function addDataSet($name, $rate = DataRateType::NONE, $type = DataType::INT, $min = null, $max = null): DataGroup
    {
        $this->addDataSetObject(new DataSet($name, $rate, $type, $min, $max));

        return $this;
    }

    protected function addDataSetObject(DataSet $dataSet): DataGroup
    {
        $this->dataSets[$dataSet->name] = $dataSet;

        return $this;
    }

    /**
     * Get DataSet to make some additional changes to it
     *
     * @param  string  $name
     * @return DataSet|null
     */
    public function getDataSet($name)
    {
        return $this->dataSets[$name] ?? null;
    }

    public function getDataSets()
    {
        return $this->dataSets;
    }

    /**
     * Fill data.  Must use numeric keys in the same order as the definition or keys that match the DataSet names.
     *
     * @param  array  $data keys should match the dataset name or be in the same order
     * @return \App\Data\DataGroup
     */
    public function fillData(array $data): DataGroup
    {
        return $this->setData($data, time());
    }

    /**
     * Fill data to be stored with a timestamp.  Must use numeric keys in the same order as the definition or keys that match the DataSet names.
     *
     * @param  array  $data keys should match the dataset name or be in the same order
     * @param  int  $timestamp  in seconds
     * @return \App\Data\DataGroup
     */
    public function setData(array $data, int $timestamp): DataGroup
    {
        $this->timestamp = $timestamp;
        $position = 0;
        /** @var \App\Data\DataSet $ds */
        foreach ($this->dataSets as $name => $ds) {
            $ds->setValue(array_key_exists($name, $data) ? $data[$name] : (array_key_exists($position, $data) ? $data[$position] : null));
            $position++;
        }
        return $this;
    }

    /**
     * tags to uniquely to identify this data, generally should not change
     *
     * @param  array  $tags
     */
    protected function addTag($key, $value): DataGroup
    {
        $this->tags[(string) $key] = (string) $value;
        return $this;
    }

    /**
     * additional value pairs of info for this data, may change
     *
     * @param  array  $fields
     */
    protected function addAnnotation($key, $value): DataGroup
    {
        $this->annotations[(string) $key] = (string) $value;
        return $this;
    }

    /**
     * Validate this data group, should be used by the test suite only to reduce runtime overhead
     *
     * @throws \App\Exceptions\InvalidDataDefinitionException
     */
    public function validate()
    {
        if ($this->nameIsInvalid($this->name)) {
            throw new InvalidDataDefinitionException("Invalid DS name '$this->name'");
        }

        foreach ($this->tags as $name => $value) {
            if ($this->nameIsInvalid($name)) {
                throw new InvalidDataDefinitionException("DS $this->name: Invalid tag name '$name'");
            }
            if ($this->nameIsInvalid($value)) {
                throw new InvalidDataDefinitionException("DS $this->name: Invalid tag value for $name '$value'");
            }
        }

        foreach ($this->annotations as $name => $value) {
            if ($this->nameIsInvalid($name)) {
                throw new InvalidDataDefinitionException("DS $this->name: Invalid field name '$name'");
            }
            if ($this->nameIsInvalid($value)) {
                throw new InvalidDataDefinitionException("DS $this->name: Invalid field value for $name '$value'");
            }
        }
    }

    private function nameIsInvalid($string): bool
    {
        return ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $string);
    }
}
