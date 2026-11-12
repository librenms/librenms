<?php

namespace LibreNMS\Tests\Unit\DB;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LibreNMS\DB\BulkTableSync;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Tests\TestCase;

final class BulkTableSyncTest extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.bulk_sync' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $this->db = DB::connection('bulk_sync');
        $this->db->getSchemaBuilder()->create('entries', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('scope');
            $table->string('name')->nullable();
            $table->string('group')->nullable();
            $table->string('value')->nullable();
            $table->integer('score')->nullable();
            $table->string('protected')->default('keep');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('bulk_sync');
        parent::tearDown();
    }

    public function testHistoryAndOtherScopesAreUntouchedAndCreationTimeIsPreserved(): void
    {
        $rows = [['name' => 'a', 'group' => null, 'value' => 'old'], ['name' => 'b', 'group' => null, 'value' => 'history']];
        $this->sync()->sync($rows, '2026-01-01 00:00:00');
        $this->sync(2)->sync($rows, '2026-01-01 00:00:00');
        $before = $this->db->table('entries')->orderBy('id')->get();

        $counts = $this->sync()->sync([['name' => 'a', 'group' => null, 'value' => 'new', 'protected' => 'overwrite', 'scope' => 2]], '2026-01-02 00:00:00');

        $after = $this->db->table('entries')->orderBy('id')->get();
        $this->assertSame(['inserted' => 0, 'updated' => 1, 'unchanged' => 0, 'deleted' => 0], $counts);
        $this->assertSame($before[0]->id, $after[0]->id);
        $this->assertSame($before[0]->created_at, $after[0]->created_at);
        $this->assertSame('2026-01-02 00:00:00', $after[0]->updated_at);
        $this->assertSame('new', $after[0]->value);
        $this->assertSame('keep', $after[0]->protected);
        $this->assertSame(1, $after[0]->scope);
        foreach ([1, 2, 3] as $index) {
            $this->assertEquals($before[$index], $after[$index]);
        }
    }

    public function testNullKeysAreDistinctFromEmptyStringsAndZeroAndDoNotEscapeScope(): void
    {
        $rows = [];
        foreach ([null, '', '0'] as $group) {
            $rows[] = ['name' => 'a', 'group' => $group, 'value' => 'first'];
        }
        $this->sync()->sync($rows);
        $this->sync(2)->sync($rows);
        $rows = array_map(fn ($row) => array_replace($row, ['value' => 'second']), $rows);
        $counts = $this->sync()->sync($rows);

        $this->assertSame(3, $counts['updated']);
        $this->assertSame(6, $this->db->table('entries')->count());
        $this->assertSame(3, $this->db->table('entries')->where('scope', 1)->where('value', 'second')->count());
        $this->assertSame(3, $this->db->table('entries')->where('scope', 2)->where('value', 'first')->count());
    }

    public function testOnlyObservedDuplicatesAreDeletedAndLastInputWins(): void
    {
        $row = ['scope' => 1, 'name' => 'a', 'group' => null, 'value' => 'old'];
        $id = $this->db->table('entries')->insertGetId($row);
        $this->db->table('entries')->insert([$row, $row, array_replace($row, ['group' => 'history']), array_replace($row, ['group' => 'history'])]);

        $counts = $this->sync()->sync([
            ['name' => 'a', 'group' => null, 'value' => 'ignored'],
            ['name' => 'a', 'group' => null, 'value' => 'last'],
        ]);

        $this->assertSame(2, $counts['deleted']);
        $this->assertSame(1, $counts['updated']);
        $this->assertSame('last', $this->db->table('entries')->where('id', $id)->value('value'));
        $this->assertSame(3, $this->db->table('entries')->count());
    }

    public function testSparseUpdatesAndQuotedValuesIncludingNullAndZero(): void
    {
        $this->sync()->sync([
            ['name' => 'a', 'group' => 'g', 'value' => 'old', 'score' => 1],
            ['name' => 'b', 'group' => 'g', 'value' => 'keep', 'score' => 2],
            ['name' => 'c', 'group' => 'g', 'value' => 'old', 'score' => 3],
        ]);
        $quoted = "quote' ? \\ and unicode é";
        $counts = $this->sync()->sync([
            ['name' => 'a', 'group' => 'g', 'value' => null, 'score' => '0'],
            ['name' => 'b', 'group' => 'g', 'score' => null],
            ['name' => 'c', 'group' => 'g', 'value' => $quoted],
        ]);
        $rows = $this->db->table('entries')->get()->keyBy('name');

        $this->assertSame(3, $counts['updated']);
        $this->assertNull($rows['a']->value);
        $this->assertSame(0, $rows['a']->score);
        $this->assertSame('keep', $rows['b']->value);
        $this->assertNull($rows['b']->score);
        $this->assertSame($quoted, $rows['c']->value);
        $this->assertSame(3, $rows['c']->score);
    }

    public function testMergeOnlyChangesWritableValuesAndUnchangedRowsAreTouched(): void
    {
        $row = ['name' => 'a', 'group' => 'g', 'value' => 'known', 'score' => 42];
        $this->sync()->sync([$row], '2026-01-01 00:00:00');
        $counts = $this->sync()->sync([array_replace($row, ['value' => null])], '2026-01-02 00:00:00',
            fn ($new, $old) => array_replace($new, ['value' => $old['value'], 'scope' => 2]));

        $entry = $this->db->table('entries')->sole();
        $this->assertSame(1, $counts['unchanged']);
        $this->assertSame('a', $entry->name);
        $this->assertSame(1, $entry->scope);
        $this->assertSame('known', $entry->value);
        $this->assertSame('2026-01-02 00:00:00', $entry->updated_at);
    }

    public function testBatchesLargeInputsAndVariedUpdates(): void
    {
        $rows = [];
        for ($i = 1; $i <= 2005; $i++) {
            $rows[] = ['name' => "entry-$i", 'group' => 'g', 'value' => 'initial', 'score' => $i];
        }
        $this->db->enableQueryLog();
        $counts = $this->sync()->sync($rows);
        $this->assertSame(2005, $counts['inserted']);
        $this->assertCount(6, $this->db->getQueryLog()); // three matching reads and three inserts
        $this->db->flushQueryLog();

        $rows = array_map(fn ($row) => array_replace($row, ['value' => $row['name'], 'score' => $row['score'] + 1]), $rows);
        $counts = $this->sync()->sync($rows);
        $this->assertSame(2005, $counts['updated']);
        $this->assertCount(6, $this->db->getQueryLog());
        $this->assertSame(2005, $this->db->table('entries')->count());
        $this->assertSame(2006, $this->db->table('entries')->where('name', 'entry-2005')->value('score'));
    }

    public function testEmptyInputDoesNoQueries(): void
    {
        $this->db->enableQueryLog();
        $this->assertSame(['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'deleted' => 0], $this->sync()->sync([]));
        $this->assertCount(0, $this->db->getQueryLog());
    }

    public function testUnchangedRowsOnlyTouchLastSeen(): void
    {
        $rows = [
            ['name' => 'a', 'group' => 'g', 'value' => 'keep', 'score' => 0],
            ['name' => 'b', 'group' => 'g', 'value' => 'keep', 'score' => 0],
        ];
        $this->sync()->sync($rows, '2026-01-01 00:00:00');
        $this->db->enableQueryLog();
        $counts = $this->sync()->sync($rows, '2026-01-02 00:00:00');
        $queries = $this->db->getQueryLog();
        $this->db->disableQueryLog();

        $this->assertSame(2, $counts['unchanged']);
        $this->assertCount(2, $queries);
        $this->assertStringStartsWith('update', $queries[1]['query']);
        $this->assertStringNotContainsString('"value"', $queries[1]['query']);
        $this->assertStringNotContainsString('"score"', $queries[1]['query']);
        $this->assertSame(2, $this->db->table('entries')->where('updated_at', '2026-01-02 00:00:00')->count());
    }

    public function testChangedRowsShareOneUpsertAndUnchangedRowsOneUpdate(): void
    {
        $rows = [];
        for ($i = 0; $i < 30; $i++) {
            $rows[] = ['name' => "entry-$i", 'group' => 'g', 'value' => 'keep', 'score' => 0];
        }
        $this->sync()->sync($rows, '2026-01-01 00:00:00');
        $ids = $this->db->table('entries')->pluck('id')->all();
        foreach ($rows as $i => &$row) {
            $row['score'] = $i < 2 ? 0 : ($i < 17 ? 100 : intdiv($i, 3));
        }
        unset($row);
        $this->db->enableQueryLog();
        $counts = $this->sync()->sync($rows, '2026-01-02 00:00:00');
        $queries = $this->db->getQueryLog();
        $this->db->disableQueryLog();

        $this->assertSame(28, $counts['updated']);
        $this->assertSame(2, $counts['unchanged']);
        $this->assertCount(3, $queries); // one read, one last-seen update, one upsert
        $this->assertStringStartsWith('update', $queries[1]['query']);
        $this->assertStringStartsWith('insert', $queries[2]['query']);
        $this->assertSame($ids, $this->db->table('entries')->pluck('id')->all());
        $this->assertSame(30, $this->db->table('entries')->where('value', 'keep')
            ->where('created_at', '2026-01-01 00:00:00')->where('updated_at', '2026-01-02 00:00:00')->count());
        foreach ($rows as $row) {
            $this->assertSame($row['score'], $this->db->table('entries')->where('name', $row['name'])->value('score'));
        }
    }

    public function testNullLookupValueIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sync()->sync([['name' => null, 'group' => 'g']]);
    }

    public function testLookupColumnMustBeWritable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sync(lookup: 'scope');
    }

    public function testModelWithoutTimestampsIsRejected(): void
    {
        $parent = new class extends Model {};
        $this->expectException(InvalidArgumentException::class);
        new BulkTableSync($parent->hasMany(BulkSyncEntryWithoutTimestamps::class, 'scope'), 'name');
    }

    public function testBooleansCompareAsIntegers(): void
    {
        $this->sync()->sync([['name' => 'a', 'group' => 'g', 'score' => false]]);
        $counts = $this->sync()->sync([['name' => 'a', 'group' => 'g', 'score' => false]]);

        $this->assertSame(1, $counts['unchanged']);
        $this->assertSame(0, $this->db->table('entries')->value('score'));
    }

    private function sync(int $scope = 1, string $lookup = 'name'): BulkTableSync
    {
        $parent = new class extends Model {};
        $parent->setConnection('bulk_sync');
        $parent->setRawAttributes(['id' => $scope]);

        return new BulkTableSync($parent->hasMany(BulkSyncEntry::class, 'scope'), $lookup);
    }
}

class BulkSyncEntry extends Model implements Keyable
{
    protected $table = 'entries';
    protected $fillable = ['name', 'group', 'value', 'score'];

    public function getCompositeKey(): string
    {
        return json_encode([$this->getAttribute('group'), $this->getAttribute('name')], JSON_THROW_ON_ERROR);
    }
}

class BulkSyncEntryWithoutTimestamps extends BulkSyncEntry
{
    public $timestamps = false;
}
