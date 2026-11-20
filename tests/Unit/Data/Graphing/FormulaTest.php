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
