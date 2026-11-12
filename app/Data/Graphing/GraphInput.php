<?php

namespace App\Data\Graphing;

/**
 * Graph input with strictly typed access.
 * Values that are missing or do not parse as the requested type are null.
 */
final readonly class GraphInput
{
    /**
     * @param  array<string, mixed>  $vars
     */
    public function __construct(private array $vars)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->vars;
    }

    public function int(string $key): ?int
    {
        return $this->filter($key, FILTER_VALIDATE_INT);
    }

    public function float(string $key): ?float
    {
        return $this->filter($key, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Accepts true/false, 1/0, yes/no and on/off
     */
    public function bool(string $key): ?bool
    {
        return $this->filter($key, FILTER_VALIDATE_BOOL);
    }

    public function string(string $key): ?string
    {
        $value = $this->vars[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    private function filter(string $key, int $filter): mixed
    {
        $value = $this->vars[$key] ?? null;

        return is_scalar($value) ? filter_var($value, $filter, FILTER_NULL_ON_FAILURE) : null;
    }
}
