<?php

/**
 * ProbeResult.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Method;

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
