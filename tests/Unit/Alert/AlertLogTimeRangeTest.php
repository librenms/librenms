<?php

namespace LibreNMS\Tests\Unit\Alert;

use App\Http\Controllers\Table\AlertLogController;
use App\Http\Parsers\AlertLogDetailParser;
use App\Models\AlertLog;
use Illuminate\Http\Request;
use LibreNMS\Tests\TestCase;

final class AlertLogTimeRangeTest extends TestCase
{
    public function testFixedRangeFiltersAlertLogTime(): void
    {
        $controller = new TestableAlertLogController(new AlertLogDetailParser);
        $this->assertSame('nullable|date_or_relative', $controller->validationRules()['from']);
        $this->assertSame('nullable|date_or_relative', $controller->validationRules()['to']);

        $query = AlertLog::query();
        $filters = $controller->rangeFilters();
        $filters['from']($query, '2026-10-01T00:00:00Z');
        $filters['to']($query, '2026-10-02T00:00:00Z');

        $this->assertStringContainsString('alert_log.time_logged >= FROM_UNIXTIME(?)', $query->toSql());
        $this->assertStringContainsString('alert_log.time_logged <= FROM_UNIXTIME(?)', $query->toSql());
        $this->assertSame([1790812800, 1790899200], $query->getBindings());
    }

    public function testRelativeRangeRemainsRolling(): void
    {
        $query = AlertLog::query();
        $filters = (new TestableAlertLogController(new AlertLogDetailParser))->rangeFilters();
        $filters['from']($query, '-1d');

        $this->assertEqualsWithDelta(time() - 86400, $query->getBindings()[0], 2);
    }
}

class TestableAlertLogController extends AlertLogController
{
    /** @return array<string, string> */
    public function validationRules(): array
    {
        return $this->rules();
    }

    /** @return array<int|string, mixed> */
    public function rangeFilters(): array
    {
        return $this->filterFields(new Request);
    }
}
