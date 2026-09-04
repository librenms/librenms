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

namespace LibreNMS\Tests;

use LibreNMS\Alerting\QueryBuilderFluentParser;
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
        $this->assertEquals($display, $qb->toSql(false));
        $this->assertEquals($sql, $qb->toSql());

        $qbq = $qb->toQuery();
        $this->assertEquals($query[0], $qbq->toSql(), 'Fluent SQL does not match');
        $this->assertEquals($query[1], $qbq->getBindings(), 'Fluent bindings do not match');
    }

    public function testRequiredTablesAreJoined(): void
    {
        // a rule that only references devices, as a dynamic port group matching on device attributes would be
        $builder = [
            'condition' => 'AND',
            'rules' => [
                [
                    'id' => 'devices.os',
                    'field' => 'devices.os',
                    'type' => 'string',
                    'input' => 'text',
                    'operator' => 'equal',
                    'value' => 'ios',
                ],
            ],
            'valid' => true,
        ];

        // without requiring ports, nothing changes: ports is not part of the query
        $plain = QueryBuilderFluentParser::fromJson($builder);
        $this->assertNotContains('ports', $plain->getTables());
        $this->assertStringNotContainsString('ports', $plain->toQuery()->toSql());

        // requiring ports joins it in so port_id can be selected
        $required = QueryBuilderFluentParser::fromJson($builder)->requireTables('ports');
        $this->assertContains('ports', $required->getTables());
        $this->assertStringContainsString(
            'left join `ports` on `devices`.`device_id` = `ports`.`device_id`',
            $required->toQuery()->toSql()
        );

        // and the where clause is untouched
        $this->assertEquals($plain->toQuery()->getBindings(), $required->toQuery()->getBindings());
    }

    public static function loadQueryData(): array
    {
        $base = realpath(__DIR__ . '/..');
        $data = file_get_contents("$base/" . self::$data_file);

        return json_decode($data, true);
    }
}
