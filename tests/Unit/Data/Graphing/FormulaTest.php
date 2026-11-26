<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\Formula;
use InvalidArgumentException;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FormulaTest extends TestCase
{
    public function test_fields_are_the_names_that_are_not_operators(): void
    {
        $this->assertSame(['xmt', 'rcv'], new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*')->fields());
    }

    public function test_it_evaluates_with_field_values(): void
    {
        $loss = new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*');

        $this->assertSame(25.0, $loss->evaluate(['xmt' => 4, 'rcv' => 3]));
        $this->assertNull($loss->evaluate(['xmt' => 0, 'rcv' => 0]));
        $this->assertNull($loss->evaluate(['xmt' => 4, 'rcv' => null]));
        $this->assertNull(new Formula('a', 'b', '-')->evaluate(['a' => INF, 'b' => INF]));
    }

    /**
     * @param  list<string|int|float>  $tokens
     */
    #[DataProvider('invalidFormulas')]
    public function test_it_rejects_formulas_that_do_not_calculate_one_value(array $tokens): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Formula(...$tokens);
    }

    /**
     * @return array<string, array{list<string|int|float>}>
     */
    public static function invalidFormulas(): array
    {
        return [
            'empty' => [[]],
            'operator first' => [['+', 'a', 'b']],
            'missing operator' => [['a', 'b']],
            'extra operator' => [['a', 'b', '+', '-']],
        ];
    }
}
