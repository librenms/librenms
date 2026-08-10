<?php

namespace LibreNMS\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Database\CheckSchemaStructure;

class CheckSchemaStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_correct_schema(): void
    {
        $validator = new CheckSchemaStructure();
        $result = $validator->validate();

        $this->assertEquals(\LibreNMS\ValidationResult::SUCCESS, $result->getStatus(), 'Schema validation failed: ' . $result->getMessage());
    }

    public function test_it_detects_extra_table(): void
    {
        DB::statement('CREATE TABLE `extra_table` (`id` int primary key NOT NULL)');

        try {
            $validator = new CheckSchemaStructure();
            $result = $validator->validate();

            $this->assertEquals(\LibreNMS\ValidationResult::FAILURE, $result->getStatus());
            $this->assertStringContainsString('extra table (extra_table)', $result->getMessage());
        } finally {
            DB::statement('DROP TABLE `extra_table`');
        }
    }

    public function test_it_detects_missing_table(): void
    {
        $table = 'missing_table';
        $schema = [
            $table => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                ],
                'Indexes' => [
                    'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $schemaFile = $this->createTempSchema($schema);

        try {
            $validator = new CheckSchemaStructure($schemaFile);
            $result = $validator->validate();

            $this->assertEquals(\LibreNMS\ValidationResult::FAILURE, $result->getStatus());
            $this->assertStringContainsString("missing table ($table)", $result->getMessage());
        } finally {
            unlink($schemaFile);
        }
    }

    public function test_it_warns_on_missing_schema_file(): void
    {
        $validator = new CheckSchemaStructure('/non/existent/path.yaml');
        $result = $validator->validate();

        $this->assertEquals(\LibreNMS\ValidationResult::WARNING, $result->getStatus());
        $this->assertStringContainsString("We haven't detected the db_schema.yaml file", $result->getMessage());
    }

    public function test_it_can_fix_schema(): void
    {
        $table = 'test_table_fix';
        DB::statement("CREATE TABLE `$table` (`id` int primary key NOT NULL)");

        $schema = [
            $table => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'new_col', 'Type' => 'varchar(255)', 'Null' => true, 'Extra' => ''],
                ],
                'Indexes' => [
                    'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $schemaFile = $this->createTempSchema($schema);

        try {
            $validator = new CheckSchemaStructure($schemaFile);
            $result = $validator->validate();
            $this->assertEquals(\LibreNMS\ValidationResult::FAILURE, $result->getStatus());

            // Only run the fix for our table to avoid dropping real tables
            $changes = (new \LibreNMS\DB\Schema(DB::connection()))->compare($schemaFile);
            foreach ($changes as $change) {
                if (str_contains($change['description'], "($table")) {
                    foreach ((array) $change['sql'] as $query) {
                        DB::statement($query);
                    }
                }
            }

            $result = $validator->validate();
            $messages = explode("\n", $result->getMessage());
            $realErrors = array_filter($messages, fn ($m) => str_contains($m, "($table/new_col)"));
            $this->assertEmpty($realErrors, 'Fix failed to add the missing column');
        } finally {
            DB::statement("DROP TABLE `$table`");
            unlink($schemaFile);
        }
    }

    private function createTempSchema(array $schema): string
    {
        $schemaFile = tempnam(sys_get_temp_dir(), 'schema');
        file_put_contents($schemaFile, \Symfony\Component\Yaml\Yaml::dump($schema));

        return $schemaFile;
    }
}
