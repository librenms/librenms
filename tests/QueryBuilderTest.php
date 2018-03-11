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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

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
     *
     * @dataProvider loadQueryData
     * @param string $legacy
     * @param array $builder
     * @param string $display
     * @param string $sql
     */
    public function testQueryConversion($legacy, $builder, $display, $sql)
    {
        if (!empty($legacy)) {
            // some rules don't have a legacy representation
            $this->assertEquals($builder, QueryBuilderParser::fromOld($legacy)->toArray());
        }
        $this->assertEquals($display, QueryBuilderParser::fromJson($builder)->toSql(false));
        $this->assertEquals($sql, QueryBuilderParser::fromJson($builder)->toSql());
    }

    public function loadQueryData()
    {
        $base = Config::get('install_dir');
        $data = file_get_contents("$base/$this->data_file");
        return json_decode($data, true);
    }
}
