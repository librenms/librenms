<?php

/**
 * RrdDefinition.php
 *
 * Build a RRD definition.
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\RRD;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\InvalidRrdTypeException;

class RrdDefinition implements \Stringable
{
    private static $types = ['GAUGE', 'DERIVE', 'COUNTER', 'ABSOLUTE', 'DCOUNTER', 'DDERIVE'];
    private $dataSets = [];
    private $sources = [];
    private $invalid_source = [];
    private $skipNameCheck = false;
    private ?int $step = null;
    /** @var string[]|null */
    private ?array $rras = null;

    /**
     * Make a new empty RrdDefinition
     */
    public static function make()
    {
        return new self();
    }

    /**
     * Set the step (seconds between updates), defaults to the rrd.step setting
     */
    public function setStep(int $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function getStep(): int
    {
        return $this->step ?? (int) LibrenmsConfig::get('rrd.step', 300);
    }

    /**
     * Set the round robin archives, defaults to the rrd_rra setting
     *
     * @param  string[]  $rras  RRA:CF:xff:steps:rows
     */
    public function setRras(array $rras): static
    {
        $this->rras = $rras;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRras(): array
    {
        return $this->rras ?? preg_split('/\s+/', trim((string) LibrenmsConfig::get(
            'rrd_rra',
            'RRA:AVERAGE:0.5:1:2016 RRA:AVERAGE:0.5:6:1440 RRA:AVERAGE:0.5:24:1440 RRA:AVERAGE:0.5:288:1440 ' .
            ' RRA:MIN:0.5:1:2016 RRA:MIN:0.5:6:1440     RRA:MIN:0.5:24:1440     RRA:MIN:0.5:288:1440 ' .
            ' RRA:MAX:0.5:1:2016 RRA:MAX:0.5:6:1440     RRA:MAX:0.5:24:1440     RRA:MAX:0.5:288:1440 ' .
            ' RRA:LAST:0.5:1:2016 '
        )), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Add a dataset to this definition.
     * See https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html for more information.
     *
     * @param  string  $name  Textual name for this dataset. Must be [a-zA-Z0-9_], max length 19.
     * @param  string  $type  GAUGE | COUNTER | DERIVE | DCOUNTER | DDERIVE | ABSOLUTE.
     * @param  int  $min  Minimum allowed value.  null means undefined.
     * @param  int  $max  Maximum allowed value.  null means undefined.
     * @param  int  $heartbeat  Heartbeat for this dataset. Uses the global setting if null.
     * @param  string  $source_ds  Dataset to copy data from an existing rrd file
     * @param  string  $source_file  File to copy data from (may be ommitted copy from the current file)
     * @return RrdDefinition
     */
    public function addDataset($name, $type, $min = null, $max = null, $heartbeat = null, $source_ds = null, $source_file = null)
    {
        if (empty($name)) {
            d_echo('DS must be set to a non-empty string.');
        }

        $name = $this->escapeName($name);
        $this->dataSets[$name] = [
            'name' => $name,
            'type' => $this->checkType($type),
            'hb' => $heartbeat ?? LibrenmsConfig::get('rrd.heartbeat'),
            'min' => $min ?? 'U',
            'max' => $max ?? 'U',
            'source_ds' => $source_ds,
            'source_file' => $source_file,
        ];

        return $this;
    }

    /**
     * Get the RRD Definition as it would be passed to rrdtool
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode(' ', $this->getArguments());
    }

    /**
     * Get the sources and data sources as they would be passed to rrdtool create
     *
     * @return string[]
     */
    public function getArguments(): array
    {
        $dataSources = [];
        foreach ($this->dataSets as $ds) {
            $name = $ds['name'] . $this->createSource($ds['source_ds'], $ds['source_file']);
            $dataSources[] = "DS:$name:{$ds['type']}:{$ds['hb']}:{$ds['min']}:{$ds['max']}";
        }

        // sources are collected while building the data sources, which reference them by 1 based index
        $sources = [];
        foreach ($this->sources as $source) {
            array_push($sources, '--source', $source);
        }

        return [...$sources, ...$dataSources];
    }

    /**
     * Everything rrdtool create needs after the file name: step, sources, data sources and archives
     *
     * @return string[]
     */
    public function getCreateArguments(): array
    {
        return ['--step', (string) $this->getStep(), ...$this->getArguments(), ...$this->getRras()];
    }

    /**
     * Order values to match the data sources, values for unknown data sources are dropped
     * and missing values are unknown. With name checking disabled, values are kept in the given order.
     *
     * @param  array<int|string, int|float|string|null>  $fields  data source name => value
     * @return array<int|float|string|null>
     */
    public function orderValues(array $fields): array
    {
        if ($this->skipNameCheck) {
            return array_values($fields);
        }

        $values = [];
        foreach ($fields as $name => $value) {
            $values[$this->escapeName($name)] = $value;
        }

        return array_map(fn ($ds) => $values[$ds] ?? null, array_keys($this->dataSets));
    }

    /**
     * Check if the give dataset name is valid for this definition
     *
     * @param  string  $name
     * @return bool
     */
    public function isValidDataset($name)
    {
        return $this->skipNameCheck || isset($this->dataSets[$this->escapeName($name)]);
    }

    /**
     * Disable checking if the name is valid for incoming data and just assign values
     * based on order
     *
     * @return $this
     */
    public function disableNameChecking()
    {
        $this->skipNameCheck = true;

        return $this;
    }

    private function createSource(?string $ds, ?string $file): string
    {
        if (empty($ds)) {
            return '';
        }

        $output = '=' . $ds;

        // if is file given, find or add it to the sources list
        if ($file) {
            $index = array_search($file, $this->sources);
            if ($index === false) {
                // check if source rrd exists and cache failures
                // using file_exists because source does not seem to support rrdcached
                // so this will only work if we have file access to the old rrd file
                if (isset($this->invalid_source[$file]) || ! file_exists($file)) {
                    $this->invalid_source[$file] = true;

                    return ''; // skip source if file does not exist
                }

                $this->sources[] = $file;
                $index = array_key_last($this->sources);
            }

            $output .= '[' . ($index + 1) . ']'; // rrdcreate sources are 1 based
        }

        return $output;
    }

    /**
     * Check that the data set type is valid.
     *
     * @param  string  $type
     * @return mixed
     *
     * @throws InvalidRrdTypeException
     */
    private function checkType($type)
    {
        if (! in_array($type, self::$types)) {
            $msg = "$type is not valid, must be: " . implode(' | ', self::$types);
            throw new InvalidRrdTypeException($msg);
        }

        return $type;
    }

    /**
     * Remove all invalid characters from the name and truncate to 19 characters.
     *
     * @param  string  $name
     * @return string
     */
    private function escapeName($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);

        return substr((string) $name, 0, 19);
    }
}
