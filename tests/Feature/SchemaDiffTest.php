<?php

namespace LibreNMS\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\Schema\Adapters\AdapterFactory;
use LibreNMS\DB\Schema\SchemaDiff;
use LibreNMS\Tests\TestCase;

class SchemaDiffTest extends TestCase
{
    use RefreshDatabase;

    private SchemaDiff $diff;

    protected function setUp(): void
    {
        parent::setUp();
        $connection = DB::connection();
        $adapter = AdapterFactory::create($connection);
        $this->diff = new SchemaDiff($connection, $adapter);
    }

    public function test_it_detects_missing_table(): void
    {
        $master = [
            'new_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                ],
                'Indexes' => [
                    'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, [], []);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: missing table (new_table)', $changes[0]['description']);
        $this->assertStringContainsString('create table `new_table`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_extra_table(): void
    {
        $changes = $this->diff->compare([], [], ['extra_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: extra table (extra_table)', $changes[0]['description']);
        $this->assertStringContainsString('drop table `extra_table`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_missing_column(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'new_col', 'Type' => 'varchar(255)', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: missing column (test_table/new_col)', $changes[0]['description']);
        $this->assertStringContainsString('alter table `test_table` add `new_col`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_extra_column(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'extra_col', 'Type' => 'varchar(255)', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: extra column (test_table/extra_col)', $changes[0]['description']);
        $this->assertStringContainsString('alter table `test_table` drop `extra_col`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_incorrect_column(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'col', 'Type' => 'varchar(255)', 'Null' => false, 'Extra' => ''],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'col', 'Type' => 'varchar(100)', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: incorrect column (test_table/col)', $changes[0]['description']);
        $this->assertStringContainsString('alter table `test_table` modify `col`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_missing_index(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']],
                'Indexes' => [
                    'test_idx' => ['Name' => 'test_idx', 'Columns' => ['id'], 'Unique' => false, 'Type' => 'BTREE'],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']],
                'Indexes' => [],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: missing index (test_table/test_idx)', $changes[0]['description']);
        $this->assertStringContainsString('alter table `test_table` add index `test_idx`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_missing_constraint(): void
    {
        $master = [
            'table1' => ['Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']]],
            'table2' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'fk_id', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
                'Constraints' => [
                    'fk_name' => [
                        'name' => 'fk_name',
                        'foreign_key' => 'fk_id',
                        'table' => 'table1',
                        'key' => 'id',
                        'extra' => 'ON DELETE CASCADE',
                    ],
                ],
            ],
        ];
        $current = [
            'table1' => ['Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']]],
            'table2' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'fk_id', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['table1', 'table2']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: missing constraint (table2/fk_name)', $changes[0]['description']);
        $this->assertStringContainsString('alter table `table2` add constraint `fk_name` foreign key (`fk_id`) references `table1` (`id`) on delete cascade', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_incorrect_index(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']],
                'Indexes' => [
                    'test_idx' => ['Name' => 'test_idx', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']],
                'Indexes' => [
                    'test_idx' => ['Name' => 'test_idx', 'Columns' => ['id'], 'Unique' => false, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: incorrect index (test_table/test_idx)', $changes[0]['description']);
        $sql = strtolower(implode(' ', (array) $changes[0]['sql']));
        $this->assertStringContainsString('drop index `test_idx`', $sql);
        $this->assertStringContainsString('add unique `test_idx`(`id`)', $sql);
    }

    public function test_it_detects_extra_index(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']],
                'Indexes' => [
                    'extra_idx' => ['Name' => 'extra_idx', 'Columns' => ['id'], 'Unique' => false, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: extra index (test_table/extra_idx)', $changes[0]['description']);
        $this->assertStringContainsString('drop index `extra_idx`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_incorrect_constraint(): void
    {
        $master = [
            'table1' => ['Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']]],
            'table2' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'fk_id', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
                'Constraints' => [
                    'fk_name' => [
                        'name' => 'fk_name',
                        'foreign_key' => 'fk_id',
                        'table' => 'table1',
                        'key' => 'id',
                        'extra' => 'ON DELETE CASCADE',
                    ],
                ],
            ],
        ];
        $current = [
            'table1' => ['Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']]],
            'table2' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'fk_id', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
                'Constraints' => [
                    'fk_name' => [
                        'name' => 'fk_name',
                        'foreign_key' => 'fk_id',
                        'table' => 'table1',
                        'key' => 'id',
                        'extra' => 'ON DELETE RESTRICT',
                    ],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['table1', 'table2']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: incorrect constraint (table2/fk_name)', $changes[0]['description']);
        $sql = strtolower(implode(' ', (array) $changes[0]['sql']));
        $this->assertStringContainsString('drop foreign key `fk_name`', $sql);
        $this->assertStringContainsString('add constraint `fk_name` foreign key (`fk_id`) references `table1` (`id`) on delete cascade', $sql);
    }

    public function test_it_detects_extra_constraint(): void
    {
        $master = [
            'table1' => ['Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']]],
            'table2' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'fk_id', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];
        $current = [
            'table1' => ['Columns' => [['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => '']]],
            'table2' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'fk_id', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
                'Constraints' => [
                    'fk_extra' => [
                        'name' => 'fk_extra',
                        'foreign_key' => 'fk_id',
                        'table' => 'table1',
                        'key' => 'id',
                        'extra' => 'ON DELETE CASCADE',
                    ],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['table1', 'table2']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: extra constraint (table2/fk_extra)', $changes[0]['description']);
        $this->assertStringContainsString('drop foreign key `fk_extra`', strtolower(implode(' ', (array) $changes[0]['sql'])));
    }

    public function test_it_detects_missing_column_in_middle(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'second', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                    ['Field' => 'third', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'third', 'Type' => 'int', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        $this->assertCount(1, $changes);
        $this->assertEquals('Database: missing column (test_table/second)', $changes[0]['description']);
        if (DB::getDriverName() === 'mysql') {
            $this->assertStringContainsString('after `id`', strtolower(implode(' ', (array) $changes[0]['sql'])));
        }
    }

    public function test_it_matches_json_to_longtext_on_mysql(): void
    {
        $master = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'data', 'Type' => 'json', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];
        $current = [
            'test_table' => [
                'Columns' => [
                    ['Field' => 'data', 'Type' => 'longtext', 'Null' => true, 'Extra' => ''],
                ],
            ],
        ];

        $changes = $this->diff->compare($master, $current, ['test_table']);

        if (str_contains(DB::getDriverName(), 'mysql')) {
            $this->assertCount(0, $changes, 'json should match longtext on MySQL/MariaDB');
        } else {
            $this->assertCount(1, $changes);
        }
    }
}
