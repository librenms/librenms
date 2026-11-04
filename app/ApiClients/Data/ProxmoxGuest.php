<?php

namespace App\ApiClients\Data;

use LibreNMS\Enum\PowerState;

/**
 * A QEMU VM or LXC container from the guest lists of a cluster or node of the Proxmox VE API.
 */
final readonly class ProxmoxGuest
{
    /**
     * @param  string|null  $qmpStatus  only the guest list of a node has it, a paused VM has the status running
     * @param  array<string, array<string, int>>  $blockStats  disk activity per disk, only the VM list of a node has it
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public ?string $qmpStatus,
        public int $cpus,
        public float $cpu,
        public int $memory,
        public int $maxMemory,
        public int $disk,
        public int $maxDisk,
        public int $netIn,
        public int $netOut,
        public int $diskRead,
        public int $diskWrite,
        public bool $template,
        public array $blockStats,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['vmid'],
            name: (string) ($data['name'] ?? ''),
            status: (string) ($data['status'] ?? 'stopped'),
            qmpStatus: $data['qmpstatus'] ?? null,
            cpus: (int) ($data['cpus'] ?? 0),
            cpu: (float) ($data['cpu'] ?? 0),
            memory: (int) ($data['mem'] ?? 0),
            maxMemory: (int) ($data['maxmem'] ?? 0),
            disk: (int) ($data['disk'] ?? 0),
            maxDisk: (int) ($data['maxdisk'] ?? 0),
            netIn: (int) ($data['netin'] ?? 0),
            netOut: (int) ($data['netout'] ?? 0),
            diskRead: (int) ($data['diskread'] ?? 0),
            diskWrite: (int) ($data['diskwrite'] ?? 0),
            template: ! empty($data['template']),
            blockStats: $data['blockstat'] ?? [],
        );
    }

    public function powerState(): int
    {
        return PowerState::parse($this->qmpStatus ?? $this->status);
    }

    /**
     * Only a running guest has metrics or a guest agent that can answer.
     */
    public function isRunning(): bool
    {
        return $this->powerState() === PowerState::ON;
    }
}
