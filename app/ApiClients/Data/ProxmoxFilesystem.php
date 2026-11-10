<?php

namespace App\ApiClients\Data;

/**
 * A filesystem reported by the QEMU guest agent of a VM.
 */
final readonly class ProxmoxFilesystem
{
    /**
     * @param  string  $device  the device of the filesystem, the mounts of one device share it
     */
    public function __construct(
        public string $device,
        public string $type,
        public int $usedBytes,
        public int $totalBytes,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            device: (string) ($data['name'] ?? $data['mountpoint'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            usedBytes: (int) ($data['used-bytes'] ?? 0),
            totalBytes: (int) ($data['total-bytes'] ?? 0),
        );
    }
}
