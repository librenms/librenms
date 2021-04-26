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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use LibreNMS\Alerting\QueryBuilderFluentParser;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Config;

class QueryBuilderTest extends TestCase
{
    private $data_file = 'tests/data/misc/querybuilder.json';

    public function testHasQueryData()
    {
        $this->assertNotEmpty(
            $this->loadQueryData(),
            "Could not load query builder test data from $this->data_file"
        );
    }

    /**
     * @dataProvider loadQueryData
     * @param string $legacy
     * @param array $builder
     * @param string $display
     * @param string $sql
     */
    public function testQueryConversion($legacy, $builder, $display, $sql, $query)
    {
        if (! empty($legacy)) {
            // some rules don't have a legacy representation
            $this->assertEquals($builder, QueryBuilderParser::fromOld($legacy)->toArray());
        }
        $qb = QueryBuilderFluentParser::fromJson($builder);
        $this->assertEquals($display, $qb->toSql(false));
        $this->assertEquals($sql, $qb->toSql());

        $qbq = $qb->toQuery();
        $this->assertEquals($query[0], $qbq->toSql(), 'Fluent SQL does not match');
        $this->assertEquals($query[1], $qbq->getBindings(), 'Fluent bindings do not match');
    }

    public function loadQueryData()
    {
        $base = Config::get('install_dir');
        $data = file_get_contents("$base/$this->data_file");

        return json_decode($data, true);
    }
}
