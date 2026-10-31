<?php

namespace App\Data\Graphing;

/**
 * Graph input with strictly typed access.
 * Values that are missing or do not parse as the requested type are null.
 */
final readonly class GraphVars
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
        $value = $this->vars[$key] ?? null;

        return is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;
    }

    public function float(string $key): ?float
    {
        $value = $this->vars[$key] ?? null;

        return is_scalar($value) ? filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;
    }

    /**
     * Accepts true/false, 1/0, yes/no and on/off
     */
    public function bool(string $key): ?bool
    {
        $value = $this->vars[$key] ?? null;

        return is_scalar($value) ? filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) : null;
    }

    public function string(string $key): ?string
    {
        $value = $this->vars[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }
}
