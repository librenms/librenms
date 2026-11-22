<?php

/**
 * QueryBuilderTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Alerting\QueryBuilderFluentParser;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class QueryBuilderTest extends TestCase
{
    private static string $data_file = 'tests/data/misc/querybuilder.json';

    public function testHasQueryData(): void
    {
        $this->assertNotEmpty(
            $this->loadQueryData(),
            'Could not load query builder test data from ' . self::$data_file
        );
    }

    /**
     * @param  string  $legacy
     * @param  array  $builder
     * @param  string  $display
     * @param  string  $sql
     */
    #[DataProvider('loadQueryData')]
    public function testQueryConversion($legacy, $builder, $display, $sql, $query): void
    {
        $qb = QueryBuilderFluentParser::fromJson($builder);
        $this->assertTrue($qb->isValid());
        $this->assertEquals($display, $qb->toSql(false));
        $this->assertEquals($sql, $qb->toSql());

        $qbq = $qb->toQuery();
        $this->assertEquals($query[0], $qbq->toSql(), 'Fluent SQL does not match');
        $this->assertEquals($query[1], $qbq->getBindings(), 'Fluent bindings do not match');
    }

    /**
     * @param  array<mixed>  $builder
     */
    #[DataProvider('invalidBuilders')]
    public function testInvalidBuilder(array $builder): void
    {
        $this->assertFalse(QueryBuilderFluentParser::fromJson($builder)->isValid());
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function invalidBuilders(): array
    {
        $rule = ['field' => 'devices.status', 'operator' => 'equal', 'value' => 0];

        return [
            'empty' => [[]],
            'unknown condition' => [['condition' => 'XOR', 'rules' => [$rule]]],
            'no rules' => [['condition' => 'AND', 'rules' => []]],
            'field without table' => [['condition' => 'AND', 'rules' => [['field' => 'status'] + $rule]]],
            'unsupported operator' => [['condition' => 'AND', 'rules' => [['operator' => 'in', 'value' => 'up'] + $rule]]],
            'quoted value' => [['condition' => 'AND', 'rules' => [['value' => '0" OR "1'] + $rule]]],
            'missing field' => [['condition' => 'AND', 'rules' => [['operator' => 'equal', 'value' => 0]]]],
            'unknown operator' => [['condition' => 'AND', 'rules' => [['operator' => 'drop'] + $rule]]],
            'missing value' => [['condition' => 'AND', 'rules' => [['field' => 'devices.status', 'operator' => 'equal']]]],
            'invalid nested group' => [['condition' => 'AND', 'rules' => [$rule, ['condition' => 'OR', 'rules' => []]]]],
            'rule with a condition' => [['condition' => 'AND', 'rules' => [['condition' => 'OR'] + $rule]]],
            'list value' => [['condition' => 'AND', 'rules' => [['value' => [1, 2]] + $rule]]],
            'single between value' => [['condition' => 'AND', 'rules' => [['operator' => 'between', 'value' => 5] + $rule]]],
            'nested between value' => [['condition' => 'AND', 'rules' => [['operator' => 'between', 'value' => [[1], 2]] + $rule]]],
        ];
    }

    public static function loadQueryData(): array
    {
        $data = file_get_contents(self::basePath(self::$data_file));

        return json_decode($data, true);
    }
}
