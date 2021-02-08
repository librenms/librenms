<?php
/**
 * SchemaTest.php
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

use LibreNMS\DB\Schema;

class SchemaTest extends TestCase
{
    private $mock_schema = [
        'bills' => [
            'Columns' => [
                ['Field' => 'bill_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
            ],
            'Indexes' => ['bill_id' => ['Name' => 'bill_id', 'Columns' => ['bill_id'], 'Unique' => true, 'Type' => 'BTREE']],
        ],
        'bill_ports' => [
            'Columns' => [
                ['Field' => 'bill_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => ''],
                ['Field' => 'port_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => ''],
            ],
        ],
        'devices' => [
            'Columns' => [
                ['Field' => 'device_id', 'Type' => 'int(11) unsigned', 'Null' => false, 'Extra' => 'auto_increment'],
                ['Field' => 'location_id', 'Type' => 'int(11)', 'Null' => true, 'Extra' => ''],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['device_id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
        'locations' => [
            'Columns' => [
                ['Field' => 'id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
        'ports' => [
            'Columns' => [
                ['Field' => 'port_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
                ['Field' => 'device_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => '', 'Default' => '0'],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['port_id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
        'sensors' => [
            'Columns' => [
                ['Field' => 'sensor_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
                ['Field' => 'device_id', 'Type' => 'int(11) unsigned', 'Null' => false, 'Extra' => '', 'Default' => '0'],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['sensor_id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
        'sensors_to_state_indexes' => [
            'Columns' => [
                ['Field' => 'sensors_to_state_translations_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
                ['Field' => 'sensor_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => ''],
                ['Field' => 'state_index_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => ''],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['sensors_to_state_translations_id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
        'state_indexes' => [
            'Columns' => [
                ['Field' => 'state_index_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['state_index_id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
        'state_translations' => [
            'Columns' => [
                ['Field' => 'state_translation_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => 'auto_increment'],
                ['Field' => 'state_index_id', 'Type' => 'int(11)', 'Null' => false, 'Extra' => ''],
            ],
            'Indexes' => [
                'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['state_translation_id'], 'Unique' => true, 'Type' => 'BTREE'],
            ],
        ],
    ];

    /**
     * @return Schema
     */
    private function getSchemaMock()
    {
        // use a Mock so we don't have to rely on the schema being stable.

        $schema = $this->getMockBuilder(Schema::class)
            ->setMethods(['getSchema'])
            ->getMock();

        $schema->method('getSchema')->willReturn($this->mock_schema);

        /** @var $schema Schema Mock of Schema */
        return $schema;
    }

    public function testTableRelationships()
    {
        // mock getSchema
        $schema = $this->getSchemaMock();

        $expected = [
            'bills' => [],
            'bill_ports' => ['bills', 'ports'],
            'devices' => ['locations'],
            'locations' => [],
            'ports' => ['devices'],
            'sensors' => ['devices'],
            'sensors_to_state_indexes' => ['sensors', 'state_indexes'],
            'state_indexes' => [],
            'state_translations' => ['state_indexes'],
        ];

        $this->assertEquals($expected, $schema->getTableRelationships());
    }

    public function testFindRelationshipPath()
    {
        $schema = $this->getSchemaMock();

        $this->assertEquals(['devices'], $schema->findRelationshipPath('devices'));
        $this->assertEquals(['locations', 'devices'], $schema->findRelationshipPath('locations'));
        $this->assertEquals(['devices', 'ports'], $schema->findRelationshipPath('ports'));
        $this->assertEquals(['devices', 'ports', 'bill_ports'], $schema->findRelationshipPath('bill_ports'));
        $this->assertEquals(['devices', 'ports', 'bill_ports', 'bills'], $schema->findRelationshipPath('bills'));
        $this->assertEquals(
            ['devices', 'sensors', 'sensors_to_state_indexes', 'state_indexes', 'state_translations'],
            $schema->findRelationshipPath('state_translations')
        );
    }
}
