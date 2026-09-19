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
        private ?string $errorMessage = null,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage ?? (is_string($this->stat('error')) ? $this->stat('error') : null);
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
    public static function failure(array $stats = [], ?string $errorMessage = null): self
    {
        return new self(false, $stats, $errorMessage);
    }
}
