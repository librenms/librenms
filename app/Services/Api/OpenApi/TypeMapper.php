<?php

declare(strict_types=1);

namespace App\Services\Api\OpenApi;

use Binaryk\LaravelRestify\Filters\Filter;

class TypeMapper
{
    /**
     * Resolve an OpenAPI type for a repository field.
     *
     * Precedence:
     *   1. MatchFilter::setType() for the matching key (text|bool|integer|datetime|...)
     *   2. Eloquent $casts on the underlying model
     *   3. string (default)
     *
     * @param  array<string, Filter>  $matches
     * @param  array<string, string>  $casts
     * @return array{type: string, format?: string}
     */
    public function oasType(string $fieldName, array $matches, array $casts): array
    {
        if (isset($matches[$fieldName])) {
            return $this->fromMatchType($matches[$fieldName]->getType());
        }

        if (isset($casts[$fieldName])) {
            return $this->fromCast($casts[$fieldName]);
        }

        return ['type' => 'string'];
    }

    /**
     * @return array{type: string, format?: string}
     */
    private function fromMatchType(string $matchType): array
    {
        return match ($matchType) {
            'bool', 'boolean' => ['type' => 'boolean'],
            'integer', 'int' => ['type' => 'integer'],
            'number', 'decimal', 'float' => ['type' => 'number'],
            'datetime' => ['type' => 'string', 'format' => 'date-time'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'array' => ['type' => 'array'],
            default => ['type' => 'string'],
        };
    }

    /**
     * Extract an enumerated value list from Laravel validation rules.
     *
     * Recognises both plain-string rules ("in:ok,warning,critical") and
     * Rule::in([...]) objects, whose __toString form is in:"ok","warning",...
     *
     * @param  array<int, mixed>  $rules
     * @return string[]|null
     */
    public function extractEnumValues(array $rules): ?array
    {
        foreach ($rules as $rule) {
            $str = match (true) {
                is_string($rule) => $rule,
                is_object($rule) && method_exists($rule, '__toString') => (string) $rule,
                default => null,
            };
            if (! is_string($str) || ! preg_match('/^in:(.+)$/i', $str, $m)) {
                continue;
            }
            $values = array_values(array_filter(array_map(
                static fn (string $v): string => trim($v, " \t\n\r\0\x0B\"'"),
                explode(',', $m[1])
            ), static fn (string $v): bool => $v !== ''));

            return $values === [] ? null : $values;
        }

        return null;
    }

    /**
     * @return array{type: string, format?: string}
     */
    private function fromCast(string $cast): array
    {
        $base = strtolower(strtok($cast, ':') ?: $cast);

        return match ($base) {
            'bool', 'boolean' => ['type' => 'boolean'],
            'int', 'integer' => ['type' => 'integer'],
            'real', 'float', 'double', 'decimal' => ['type' => 'number'],
            'datetime', 'immutable_datetime', 'timestamp' => ['type' => 'string', 'format' => 'date-time'],
            'date', 'immutable_date' => ['type' => 'string', 'format' => 'date'],
            'array', 'collection', 'json', 'object', 'encrypted:array', 'encrypted:json' => ['type' => 'object'],
            default => ['type' => 'string'],
        };
    }
}
