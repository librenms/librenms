<?php

declare(strict_types=1);

namespace LibreNMS\Tests\Unit\Api\OpenApi;

use App\Services\Api\OpenApi\TypeMapper;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\TestCase;

final class TypeMapperTest extends TestCase
{
    private TypeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new TypeMapper();
    }

    public function testMatchFilterTakesPrecedenceOverCasts(): void
    {
        $matches = ['isUp' => MatchFilter::make()->setType('bool')->setColumn('status')];
        $casts = ['isUp' => 'string'];

        $this->assertSame(['type' => 'boolean'], $this->mapper->oasType('isUp', $matches, $casts));
    }

    public function testFallsBackToEloquentCastWhenNoMatchFilter(): void
    {
        $matches = [];
        $casts = ['retries' => 'int', 'created_at' => 'datetime', 'metadata' => 'array'];

        $this->assertSame(['type' => 'integer'], $this->mapper->oasType('retries', $matches, $casts));
        $this->assertSame(['type' => 'string', 'format' => 'date-time'], $this->mapper->oasType('created_at', $matches, $casts));
        $this->assertSame(['type' => 'object'], $this->mapper->oasType('metadata', $matches, $casts));
    }

    public function testStripsCastParameters(): void
    {
        $this->assertSame(['type' => 'number'], $this->mapper->oasType('price', [], ['price' => 'decimal:4']));
    }

    public function testDefaultsToStringWhenUnknown(): void
    {
        $this->assertSame(['type' => 'string'], $this->mapper->oasType('unknown', [], []));
    }

    public function testExtractEnumValuesFromStringRule(): void
    {
        $this->assertSame(
            ['ok', 'warning', 'critical'],
            $this->mapper->extractEnumValues(['required', 'in:ok,warning,critical'])
        );
    }

    public function testExtractEnumValuesFromRuleInObject(): void
    {
        $this->assertSame(
            ['ok', 'warning', 'critical'],
            $this->mapper->extractEnumValues(['required', Rule::in(['ok', 'warning', 'critical'])])
        );
    }

    public function testExtractEnumValuesReturnsNullWhenAbsent(): void
    {
        $this->assertNull($this->mapper->extractEnumValues(['required', 'string', 'max:255']));
    }

    public function testMatchFilterDatetimeIncludesFormat(): void
    {
        $matches = ['createdAt' => MatchFilter::make()->setType('datetime')->setColumn('timestamp')];

        $this->assertSame(
            ['type' => 'string', 'format' => 'date-time'],
            $this->mapper->oasType('createdAt', $matches, [])
        );
    }
}
