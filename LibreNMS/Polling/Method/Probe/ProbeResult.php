<?php

namespace LibreNMS\Polling\Method\Probe;

final readonly class ProbeResult
{
    /**
     * @param  array<string, mixed>  $stats
     */
    public function __construct(
        private bool $success,
        private array $stats = [],
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get all additional statistics information.
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return $this->stats;
    }

    /**
     * Get a specific stat by key, returning default if not set.
     */
    public function stat(string $key, mixed $default = null): mixed
    {
        return $this->stats[$key] ?? $default;
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    public static function success(array $stats = []): self
    {
        return new self(true, $stats);
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    public static function failure(array $stats = []): self
    {
        return new self(false, $stats);
    }
}
