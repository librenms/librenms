<?php

namespace LibreNMS\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Database\CheckSchemaStructure;

class CheckSchemaStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_correct_schema()
    {
        $validator = new CheckSchemaStructure();
        $result = $validator->validate();

        $this->assertEquals(\LibreNMS\ValidationResult::SUCCESS, $result->getStatus(), 'Schema validation failed: ' . $result->getMessage());
    }

    public function test_it_detects_extra_table()
    {
        DB::statement('CREATE TABLE `extra_table` (`id` int primary key)');

        try {
            $validator = new CheckSchemaStructure();
            $result = $validator->validate();

            $this->assertEquals(\LibreNMS\ValidationResult::FAILURE, $result->getStatus());
            $this->assertStringContainsString('extra table (extra_table)', $result->getMessage());
        } finally {
            DB::statement('DROP TABLE `extra_table`');
        }
    }

    public function test_it_detects_missing_column()
    {
        $table = 'test_table';
        DB::statement("CREATE TABLE `$table` (`id` int primary key)");

        $schema = [
            $table => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'missing_col', 'Type' => 'varchar(255)', 'Null' => true, 'Extra' => ''],
                ],
                'Indexes' => [
                    'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $schemaFile = tempnam(sys_get_temp_dir(), 'schema');
        file_put_contents($schemaFile, \Symfony\Component\Yaml\Yaml::dump($schema));

        try {
            $validator = new CheckSchemaStructure($schemaFile);
            $result = $validator->validate();

            $this->assertEquals(\LibreNMS\ValidationResult::FAILURE, $result->getStatus());
            $this->assertStringContainsString("missing column ($table/missing_col)", $result->getMessage());
        } finally {
            DB::statement("DROP TABLE `$table`");
            unlink($schemaFile);
        }
    }

    public function test_it_detects_incorrect_column()
    {
        $table = 'test_table_incorrect';
        DB::statement("CREATE TABLE `$table` (`id` int primary key, `col` varchar(100))");

        $schema = [
            $table => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'col', 'Type' => 'varchar(255)', 'Null' => false, 'Extra' => ''],
                ],
                'Indexes' => [
                    'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $schemaFile = tempnam(sys_get_temp_dir(), 'schema');
        file_put_contents($schemaFile, \Symfony\Component\Yaml\Yaml::dump($schema));

        try {
            $validator = new CheckSchemaStructure($schemaFile);
            $result = $validator->validate();

            $this->assertEquals(\LibreNMS\ValidationResult::FAILURE, $result->getStatus());
            $this->assertStringContainsString("incorrect column ($table/col)", $result->getMessage());
        } finally {
            DB::statement("DROP TABLE `$table`");
            unlink($schemaFile);
        }
    }

    public function test_it_detects_json_column()
    {
        $table = 'test_table_json';
        DB::statement("DROP TABLE IF EXISTS `$table` ");
        DB::statement("CREATE TABLE `$table` (`id` int primary key, `data` json)");

        $schema = [
            $table => [
                'Columns' => [
                    ['Field' => 'id', 'Type' => 'int', 'Null' => false, 'Extra' => ''],
                    ['Field' => 'data', 'Type' => 'json', 'Null' => true, 'Extra' => ''],
                ],
                'Indexes' => [
                    'PRIMARY' => ['Name' => 'PRIMARY', 'Columns' => ['id'], 'Unique' => true, 'Type' => 'BTREE'],
                ],
            ],
        ];

        $schemaFile = tempnam(sys_get_temp_dir(), 'schema');
        file_put_contents($schemaFile, \Symfony\Component\Yaml\Yaml::dump($schema));

        try {
            $validator = new CheckSchemaStructure($schemaFile);
            $result = $validator->validate();

            // Filter out extra table messages because we are using a partial schema file
            $messages = explode("\n", $result->getMessage());
            $realErrors = array_filter($messages, fn ($m) => ! str_contains($m, 'extra table') && ! str_contains($m, 'detected that your database schema may be wrong'));

            $this->assertEmpty($realErrors, 'JSON column validation failed: ' . implode("\n", $realErrors));
        } finally {
            DB::statement("DROP TABLE `$table`");
            unlink($schemaFile);
        }
    }
}
