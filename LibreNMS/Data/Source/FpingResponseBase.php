<?php

namespace LibreNMS\Data\Source;

abstract class FpingResponseBase
{
    public const SUCCESS = 0;
    public const UNREACHABLE = 1;
    public const INVALID_HOST = 2;
    public const INVALID_ARGS = 3;
    public const SYS_CALL_FAIL = 4;

    /**
     * @param  int  $exit_code  Return code from fping
     * @param  string|null  $host  Hostname/IP pinged
     */
    public function __construct(
        public int $exit_code,
        public readonly ?string $host = null
    ) {
    }

    /**
     * Ping result was successful.
     * fping didn't have an error and we got at least one ICMP packet back.
     */
    abstract public function isAlive(): bool;

    /**
     * Change the exit code to 0, this may be appropriate when a non-fatal error was encountered
     */
    public function ignoreFailure(): void
    {
        $this->exit_code = self::SUCCESS;
    }
}
