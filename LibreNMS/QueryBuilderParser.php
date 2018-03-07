<?php
/**
 * QueryBuilderParser.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;


class QueryBuilderParser
{
    private $builder = [];
    private $rules = [];

    private $operators = [
        'equal' => "=",
        'not_equal' => "!=",
        'in' => "IN (?)",
        'not_in' => "NOT IN (_REP_)",
        'less' => "<",
        'less_or_equal' => "<=",
        'greater' => ">",
        'greater_or_equal' => ">=",
        'begins_with' => "ILIKE",
        'not_begins_with' => "NOT ILIKE",
        'contains' => "ILIKE",
        'not_contains' => "NOT ILIKE",
        'ends_with' => "ILIKE",
        'not_ends_with' => "NOT ILIKE",
        'is_empty' => "=''",
        'is_not_empty' => "!=''",
        'is_null' => "IS NULL",
        'is_not_null' => "IS NOT NULL",
        'regex' => 'REGEXP',
    ];

    private $like_operators = [
        'begins_with',
        'not_begins_with',
        'contains',
        'not_contains',
        'ends_with',
        'not_ends_with',
    ];


    private function __construct(array $builder)
    {
        $this->builder = $builder;
    }

    public static function fromJson(array $json)
    {
        return new static($json);
    }

    public static function fromOld($query)
    {
        $rules = [];
        $tables = [];
        preg_match_all('/%[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+/', $query, $fields);


        var_dump($fields);

        return new static($rules);
    }

    public function toSql()
    {
        $jsonResult = array("data" => array());
        $getAllResults = false;
        $result = "";
        $params = array();


        if(!array_key_exists('condition', $this->builder)) {
            throw new \Exception("Invalid data, missing condition");
        }

        $global_bool_operator = $this->builder['condition'];

        $counter = 0;
        $total = count($this->builder['rules']);

        foreach($this->builder['rules'] as $index => $rule) {
            if(array_key_exists('condition', $rule)) {
                $result .= $this->parseGroup($rule, $params);
                $total--;
                if($counter < $total) {
                    $result .= " $global_bool_operator ";
                }
            } else {
                $result .= $this->parseRule($rule, $params);
                $total--;
                if($counter < $total) {
                    $result .= " $global_bool_operator ";
                }
            }
        }

        return $result;
    }

    public function toJson()
    {
        return $this->rules;
    }

    /**
     * Parse a group of conditions */
    private function parseGroup($rule, &$param) {
        $parseResult = "(";
        $bool_operator = $rule['condition'];
        // counters to avoid boolean operator at the end of the cycle
        // if there are no more conditions
        $counter = 0;
        $total = count($rule['rules']);

        foreach($rule['rules'] as $i => $r) {
            if(array_key_exists('condition', $r)) {
                $parseResult .= "\n".$this->parseGroup($r, $param);
            } else {
                $parseResult .= $this->parseRule($r, $param);
                $total--;
                if($counter < $total)
                    $parseResult .= " ".$bool_operator." ";
            }
        }

        return $parseResult.")";
    }

    /**
     * Parsing of a single condition */
    private function parseRule($rule, &$param) {

        global $fields, $operators;

        $parseResult = "";
        $parseResult .= $fields[$rule['id']]." ";

        if($this->isLikeOp($rule['operator'])) {
            $parseResult .= $this->setLike($rule['operator'], $rule['value'], $param);
        } else {
            $param[] = array($rule['type'][0] => $rule['value']);
            $parseResult .= $operators[$rule['operator']]." ?";
        }
        return $parseResult;
    }

    private function isLikeOp($operator)
    {
        $like_ops = [
            ''
        ];

        return in_array($operator, $like_ops);
    }


}
