<?php

namespace LibreNMS\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CacheExpirationIndexMigrationTest extends DBTestCase
{
    private string $previousConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousConnection = DB::getDefaultConnection();
        config(['database.connections.cache_migration' => array_replace(config('database.connections.testing'), [
            'prefix' => 'cache_expiration_test_',
            'prefix_indexes' => false,
        ])]);
        DB::setDefaultConnection('cache_migration');

        (require base_path('database/migrations/2020_09_18_223431_create_cache_table.php'))->up();
        (require base_path('database/migrations/2020_09_24_000500_create_cache_locks_table.php'))->up();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        DB::purge('cache_migration');
        DB::setDefaultConnection($this->previousConnection);

        parent::tearDown();
    }

    /**
     * @param  list<string>  $indexedTables
     */
    #[DataProvider('existingIndexes')]
    public function testMissingExpirationIndexesAreAddedWithoutDuplicatingExistingOnes(array $indexedTables): void
    {
        foreach ($indexedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->index('expiration');
            });
        }

        $this->migration()->up();

        $this->assertTrue(Schema::hasIndex('cache', 'cache_expiration_index'));
        $this->assertTrue(Schema::hasIndex('cache_locks', 'cache_locks_expiration_index'));
    }

    public function testLegacyLockTableUsesDynamicRowFormatAndKeepsLocks(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('InnoDB row formats require MySQL or MariaDB.');
        }

        DB::statement('ALTER TABLE `cache_expiration_test_cache_locks` MODIFY `key` VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL, ROW_FORMAT=COMPACT');
        $lock = ['key' => str_repeat('a', 255), 'owner' => 'existing-owner', 'expiration' => 2000000000];
        DB::table('cache_locks')->insert($lock);

        $this->migration()->up();

        $status = DB::selectOne('SHOW TABLE STATUS WHERE Name = ?', ['cache_expiration_test_cache_locks']);
        $this->assertSame('Dynamic', $status->Row_format);
        $this->assertTrue(Schema::hasIndex('cache_locks', 'cache_locks_expiration_index'));
        $this->assertDatabaseHas('cache_locks', $lock);
    }

    /**
     * @return array<string, array{list<string>}>
     */
    public static function existingIndexes(): array
    {
        return [
            'neither index exists' => [[]],
            'cache index exists' => [['cache']],
            'cache locks index exists' => [['cache_locks']],
            'both indexes exist' => [['cache', 'cache_locks']],
        ];
    }

    private function migration(): object
    {
        return require base_path('database/migrations/2026_09_22_000001_add_expiration_index_to_cache_table.php');
    }
}
