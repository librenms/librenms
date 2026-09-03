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
}
