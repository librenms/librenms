<?php

namespace App\Data\Graphing;

use InvalidArgumentException;

/**
 * A value calculated from fields of one metric, in reverse polish notation.
 * Strings are field names or one of the operators + - * /, numbers are constants.
 * For example the percent of lost packets: new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*')
 */
final readonly class Formula
{
    public const array OPERATORS = ['+', '-', '*', '/'];

    /** @var list<string|int|float> */
    public array $tokens;

    public function __construct(string|int|float ...$tokens)
    {
        $depth = 0;
        foreach ($tokens as $token) {
            $depth += in_array($token, self::OPERATORS, true) ? -1 : 1;

            if ($depth < 1) {
                throw new InvalidArgumentException('Formula operator without two values: ' . implode(',', $tokens));
            }
        }

        if ($depth !== 1) {
            throw new InvalidArgumentException('Formula does not calculate one value: ' . implode(',', $tokens));
        }

        $this->tokens = array_values($tokens);
    }

    /**
     * The fields the formula reads, each once
     *
     * @return list<string>
     */
    public function fields(): array
    {
        return array_values(array_unique(array_filter($this->tokens, fn ($token) => is_string($token) && ! in_array($token, self::OPERATORS, true))));
    }
}
