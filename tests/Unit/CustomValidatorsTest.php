<?php

namespace LibreNMS\Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Tests\TestCase;

final class CustomValidatorsTest extends TestCase
{
    private function assertRulePasses($value, string $rule): void
    {
        $validator = Validator::make(['field' => $value], ['field' => $rule]);

        $this->assertTrue(
            $validator->passes(),
            sprintf('Expected rule "%s" to pass for value: %s', $rule, var_export($value, true))
        );
    }

    private function assertRuleFails($value, string $rule): void
    {
        $validator = Validator::make(['field' => $value], ['field' => $rule]);

        $this->assertFalse(
            $validator->passes(),
            sprintf('Expected rule "%s" to fail for value: %s', $rule, var_export($value, true))
        );
        $this->assertArrayHasKey('field', $validator->errors()->toArray());
    }

    public function testAlphaSpaceValidation(): void
    {
        $this->assertRulePasses('Alpha 123_', 'alpha_space');
        $this->assertRulePasses(' spaced words ', 'alpha_space');
        $this->assertRulePasses('umlaut ok', 'alpha_space');
        $this->assertRulePasses('', 'alpha_space');

        $this->assertRuleFails('dash-separated', 'alpha_space');
        $this->assertRuleFails('punctuation!', 'alpha_space');
    }

    public function testIpOrHostnameValidation(): void
    {
        $this->assertRulePasses('192.168.1.1', 'ip_or_hostname');
        $this->assertRulePasses('192.168.1.1/24', 'ip_or_hostname');
        $this->assertRulePasses('2001:db8::1', 'ip_or_hostname');
        $this->assertRulePasses('2001:db8::1/64', 'ip_or_hostname');
        $this->assertRulePasses('example.com', 'ip_or_hostname');

//        $this->assertRuleFails('999.999.999.999', 'ip_or_hostname'); Unsure about this case
        $this->assertRuleFails('exa mple.com', 'ip_or_hostname');
        $this->assertRuleFails('example.com/24', 'ip_or_hostname');
    }

    public function testIsRegexValidation(): void
    {
        $this->assertRulePasses('/[a-z]+/', 'is_regex');
        $this->assertRulePasses('/^[a-z0-9_-]+$/i', 'is_regex');
        $this->assertRulePasses('~^\\w+\\s+\\d+$~', 'is_regex');
        $this->assertRulePasses('#^foo\\#bar$#', 'is_regex');
        $this->assertRulePasses('', 'is_regex');

        $this->assertRuleFails('/[a-z+/', 'is_regex');
        $this->assertRuleFails('not-a-regex', 'is_regex');
        $this->assertRuleFails('/[a-z]+/z', 'is_regex');
    }

    public function testZeroOrExistsValidation(): void
    {
        // use simple in-memory db, full db testing is much slower (20s or more)
        $original_connection = config('database.default');
        config([
            'database.connections.validator_testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'validator_testing',
        ]);

        DB::purge('validator_testing');
        DB::connection('validator_testing')->getPdo();

        Schema::connection('validator_testing')->create('validator_items', function (Blueprint $table): void {
            $table->increments('id');
        });

        try {
            $row_id = DB::table('validator_items')->insertGetId(['id' => null]);

            $this->assertRulePasses(0, 'zero_or_exists:validator_items,id');
            $this->assertRulePasses('0', 'zero_or_exists:validator_items,id');
            $this->assertRulePasses($row_id, 'zero_or_exists:validator_items,id');

            $this->assertRuleFails($row_id + 1, 'zero_or_exists:validator_items,id');
        } finally {
            config(['database.default' => $original_connection]);
            DB::disconnect('validator_testing');
        }
    }

    public function testUrlOrXmlValidation(): void
    {
        $this->assertRulePasses('https://example.com/path?x=1', 'url_or_xml');
        $this->assertRulePasses('<root><child/></root>', 'url_or_xml');

        $this->assertRuleFails('not a url or xml', 'url_or_xml');
        $this->assertRuleFails('<root>', 'url_or_xml');
        $this->assertRuleFails(123, 'url_or_xml');
    }

    public function testArrayKeysNotEmptyValidation(): void
    {
        $this->assertRulePasses(['foo' => 'bar'], 'array_keys_not_empty');
        $this->assertRulePasses(['0' => 'bar', 1 => 'baz'], 'array_keys_not_empty');

        $this->assertRuleFails(['' => 'bar'], 'array_keys_not_empty');
        $this->assertRuleFails([' ' => 'bar'], 'array_keys_not_empty');
        $this->assertRuleFails('not-an-array', 'array_keys_not_empty');
    }

    public function testDateOrRelativeValidation(): void
    {
        $this->assertRulePasses('123456789', 'date_or_relative');
        $this->assertRulePasses('1699999999', 'date_or_relative');
        $this->assertRulePasses('1234567890123', 'date_or_relative');
        $this->assertRulePasses('2h', 'date_or_relative');
        $this->assertRulePasses('-3d', 'date_or_relative');
        $this->assertRulePasses('+4w', 'date_or_relative');
        $this->assertRulePasses('2023-05-01', 'date_or_relative');

        $this->assertRuleFails('10z', 'date_or_relative');
        $this->assertRuleFails('12345678901234', 'date_or_relative');
        $this->assertRuleFails('not-a-date', 'date_or_relative');
    }
}
