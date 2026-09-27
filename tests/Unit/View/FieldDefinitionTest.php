<?php

namespace LibreNMS\Tests\Unit\View;

use App\View\FieldSchema\FieldDefinition;
use Illuminate\Validation\Rules\In;
use LibreNMS\Tests\TestCase;

final class FieldDefinitionTest extends TestCase
{
    public function testDefaultAndPlaceholder(): void
    {
        $withDefault = FieldDefinition::make('port', 'number')->default(161);
        $this->assertSame(161, $withDefault->getDefault());
        $this->assertSame('161', $withDefault->getPlaceholder());

        $withCallableDefault = FieldDefinition::make('timeout', 'number')->default(fn (): int => 5);
        $this->assertSame(5, $withCallableDefault->getDefault());

        $withPlaceholder = FieldDefinition::make('hostname', 'text')->placeholder('device hostname');
        $this->assertNull($withPlaceholder->getDefault());
        $this->assertSame('device hostname', $withPlaceholder->getPlaceholder());

        // a select defaults to its first option
        $this->assertSame('a', FieldDefinition::make('choice', 'select')->options(['a' => 'A', 'b' => 'B'])->getDefault());
    }

    public function testRulesAreGeneratedFromTheField(): void
    {
        $this->assertSame(['nullable', 'integer', 'min:1', 'max:10'], FieldDefinition::make('retries', 'number')->min(1)->max(10)->getRules());
        $this->assertSame(['nullable', 'numeric', 'min:0.1'], FieldDefinition::make('timeout', 'number')->min(0.1)->cast('float')->getRules());
        $this->assertSame(['nullable', 'string'], FieldDefinition::make('hostname', 'text')->getRules());

        $selectRules = FieldDefinition::make('transport', 'select')->options(['udp' => 'UDP', 'tcp' => 'TCP'])->getRules();
        $this->assertSame('nullable', $selectRules[0]);
        $this->assertInstanceOf(In::class, $selectRules[1]);
        $this->assertSame('in:"udp","tcp"', (string) $selectRules[1]);

        // explicit rules replace the generated ones
        $this->assertSame(['required'], FieldDefinition::make('port', 'number')->min(1)->rules(['required'])->getRules());
    }

    public function testCastValue(): void
    {
        $this->assertSame(161, FieldDefinition::make('port', 'number')->castValue('161'));
        $this->assertSame(2.5, FieldDefinition::make('timeout', 'number')->cast('float')->castValue('2.5'));
        $this->assertSame('udp', FieldDefinition::make('transport', 'select')->castValue('udp'));
        $this->assertNull(FieldDefinition::make('port', 'number')->castValue(null));

        $bool = FieldDefinition::make('bulk', 'select')->cast('bool');
        $this->assertTrue($bool->castValue('1'));
        $this->assertFalse($bool->castValue('false'));
        $this->assertNull($bool->castValue('maybe'));
    }

    public function testVisibleIfExpression(): void
    {
        $field = FieldDefinition::make('community', 'password')->visibleIf(['version' => ['$in' => ['v1', 'v2c']], 'enabled' => true]);

        $this->assertSame('["v1","v2c"].includes(formData["version"]) && formData["enabled"] === true', $field->buildVisibleIfExpression());
        $this->assertNull(FieldDefinition::make('version', 'select')->buildVisibleIfExpression());
    }
}
