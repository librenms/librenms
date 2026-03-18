<?php

namespace LibreNMS\Data\Source\Icmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Enum\AddressFamily;

class FpingCommandBuilder
{
    private array $args = [];
    private ?AddressFamily $addressFamily = null;

    public function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function forAddressFamily(AddressFamily $addressFamily): self
    {
        $this->addressFamily = $addressFamily;

        return $this;
    }

    public function withCount(int $count): self
    {
        array_push($this->args, '-c', $count);

        return $this;
    }

    public function withInterval(int $interval): self
    {
        array_push($this->args, '-p', $interval);

        return $this;
    }

    public function withTimeout(int $timeout): self
    {
        array_push($this->args, '-t', $timeout);

        return $this;
    }

    public function withRetries(int $retries): self
    {
        array_push($this->args, '-r', $retries);

        return $this;
    }

    public function withTos(int $tos): self
    {
        array_push($this->args, '-O', $tos);

        return $this;
    }

    public function showElapsedTimes(): self
    {
        $this->args[] = '-e';

        return $this;
    }

    public function quiet(): self
    {
        $this->args[] = '-q';

        return $this;
    }

    public function fromFile(string $filename): self
    {
        array_push($this->args, '-f', $filename);

        return $this;
    }

    public function build(string ...$hosts): array
    {
        $base = LibrenmsConfig::fpingCommand($this->addressFamily ?? AddressFamily::IPv4);

        return array_merge($base, $this->args, $hosts);
    }
}
