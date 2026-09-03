<?php

namespace LibreNMS\Syslog;

class Entry
{
    public function __construct(
        public string $host,
        public string $facility,
        public string $priority,
        public string $level,
        public string $tag,
        public string $timestamp,
        public string $msg,
        public string $program,
        public ?int $device_id = null,
    ) {}

    public static function fromArray(array $entry): static
    {
        return new static(
            $entry['host'] ?? '',
            $entry['facility'] ?? '',
            $entry['priority'] ?? '',
            $entry['level'] ?? '',
            $entry['tag'] ?? '',
            $entry['timestamp'] ?? '',
            $entry['msg'] ?? '',
            $entry['program'] ?? '',
            $entry['device_id'] ?? null,
        );
    }
}
