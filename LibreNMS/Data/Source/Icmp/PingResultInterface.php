<?php

namespace LibreNMS\Data\Source\Icmp;

use LibreNMS\Enum\FpingExitCode;

interface PingResultInterface
{
    /**
     * Ping result was successful.
     * fping didn't have an error and we got at least one ICMP packet back.
     */
    public function isAlive(): bool;

    /**
     * Change the exit code to 0, this may be appropriate when a non-fatal error was encountered
     */
    public function ignoreFailure(): void;

    /**
     * Get the hostname/IP pinged.
     */
    public function getHost(): ?string;

    /**
     * Get the return code from fping.
     */
    public function getExitCode(): FpingExitCode;
}
