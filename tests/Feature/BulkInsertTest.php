<?php

namespace LibreNMS\Tests\Feature;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\BulkInsert;
use LibreNMS\Tests\TestCase;
use PDO;
use RuntimeException;

final class BulkInsertTest extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = DB::connection('testing_memory');
        $this->db->getSchemaBuilder()->create('bulk_rows', function (Blueprint $table): void {
            $table->integer('value')->unique();
            $table->string('label')->nullable()->default('database default');
        });
    }

    public function testEmptyInputDoesNotExecuteAnyQueries(): void
    {
        $this->db->enableQueryLog();
        BulkInsert::insert($this->db->table('bulk_rows'), []);
        $this->assertSame([], $this->db->getQueryLog());
    }

    public function testColumnsAreAlignedAndDefaultsAreNotReplacedWithNull(): void
    {
        BulkInsert::insert($this->db->table('bulk_rows'), [
            ['value' => 1],
            ['label' => null, 'value' => 2],
            ['value' => 3, 'label' => 'three'],
            ['label' => 'four', 'value' => 4],
        ]);

        $this->assertSame(['database default', null, 'three', 'four'], $this->db->table('bulk_rows')->orderBy('value')->pluck('label')->all());
    }

    public function testSqliteBindingLimitAndStreamingInput(): void
    {
        $bindings = [];
        $this->db->listen(function (QueryExecuted $query) use (&$bindings): void {
            if (str_starts_with($query->sql, 'insert')) {
                $bindings[] = count($query->bindings);
            }
        });
        $rows = static function () {
            for ($i = 1; $i <= 2051; $i++) {
                yield ['value' => $i, 'label' => 'entry'];
            }
        };
        $inserted = 0;
        BulkInsert::insert($this->db->table('bulk_rows'), $rows(), function (int $count) use (&$inserted): void {
            $inserted += $count;
        });

        $this->assertSame(2051, $inserted);
        $this->assertSame(2051, $this->db->table('bulk_rows')->count());
        $this->assertCount(5, $bindings);
        $this->assertLessThanOrEqual(999, max($bindings));
    }

    public function testLargeStringsAreSplitIntoBoundedStatements(): void
    {
        $rows = [];
        for ($i = 0; $i < 10; $i++) {
            $rows[] = ['value' => $i, 'label' => str_repeat('x', 100000)];
        }
        $chunks = [];
        BulkInsert::insert($this->db->table('bulk_rows'), $rows, function (int $count) use (&$chunks): void {
            $chunks[] = $count;
        });

        $this->assertSame([2, 2, 2, 2, 2], $chunks);
        $this->assertSame(100000, strlen($this->db->table('bulk_rows')->value('label')));
    }

    public function testInsertErrorsAreNotIgnoredOrReportedAsProgress(): void
    {
        $inserted = 0;
        try {
            BulkInsert::insert($this->db->table('bulk_rows'), [['value' => 1], ['value' => 1]], function (int $count) use (&$inserted): void {
                $inserted += $count;
            });
            $this->fail('A duplicate key must throw');
        } catch (QueryException) {
            $this->assertSame(0, $inserted);
            $this->assertSame(0, $this->db->table('bulk_rows')->count());
        }
    }

    public function testQueryConnectionAndTablePrefixAreRespected(): void
    {
        $other = new SQLiteConnection(new PDO('sqlite::memory:'), ':memory:', 'prefixed_', ['driver' => 'sqlite']);
        $other->getSchemaBuilder()->create('bulk_rows', function (Blueprint $table): void {
            $table->integer('value');
        });
        BulkInsert::insert($other->table('bulk_rows'), [['value' => 42]]);

        $this->assertSame(42, $other->table('bulk_rows')->value('value'));
        $this->assertSame(1, (int) $other->getPdo()->query('SELECT COUNT(*) FROM prefixed_bulk_rows')->fetchColumn());
        $this->assertSame(0, $this->db->table('bulk_rows')->count());
    }

    public function testEarlierChunksRemainWhenTheInputGeneratorFails(): void
    {
        $rows = static function () {
            for ($i = 1; $i <= 1000; $i++) {
                yield ['value' => $i];
            }
            throw new RuntimeException('input failed');
        };
        $chunks = [];
        try {
            BulkInsert::insert($this->db->table('bulk_rows'), $rows(), function (int $count) use (&$chunks): void {
                $chunks[] = $count;
            });
            $this->fail('The generator error must propagate');
        } catch (RuntimeException $e) {
            $this->assertSame('input failed', $e->getMessage());
            $this->assertSame([999], $chunks);
            $this->assertSame(999, $this->db->table('bulk_rows')->count());
        }
    }
}
