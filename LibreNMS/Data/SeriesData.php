<?php
/*
 * Series.php
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

namespace LibreNMS\Data;

use Iterator;
use JsonSerializable;

class SeriesData implements Iterator, JsonSerializable
{
    private $data = [];
    private $iter_pos = 0;

    /**
     * @param  array  $labels  array of series names in the same order the data will be sent
     * @return \LibreNMS\Data\SeriesData
     */
    public static function make(array $labels): SeriesData
    {
        $new = new static();
        foreach ($labels as $index => $label) {
            $new->data[$index]['name'] = $label;
        }
        return $new;
    }

    /**
     * Append a point of data usually this contains a timestamp and one more data values
     * index should be the same as the label indexes
     * @param array $values
     * @return $this
     */
    public function appendPoint(...$values): SeriesData
    {
        foreach ($values as $index => $value) {
            $this->data[$index]['values'][] = $value;
        }
        return $this;
    }

    public function getSeries($index)
    {
        return $this->data[$index]['values'] ?? [];
    }

    /**
     * @access protected
     */
    public function current()
    {
        return array_map(function ($series) {
            return $series['values'][$this->iter_pos] ?? null;
        }, $this->data);
    }

    /**
     * @access protected
     */
    public function next()
    {
        $this->iter_pos++;
    }

    /**
     * @access protected
     */
    public function key()
    {
        return $this->iter_pos;
    }

    /**
     * @access protected
     */
    public function valid()
    {
        foreach ($this->data as $series) {
            if (isset($series['values'][$this->iter_pos])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @access protected
     */
    public function rewind()
    {
        $this->iter_pos = 0;
    }

    public function jsonSerialize()
    {
        return json_encode($this->data);
    }
}
