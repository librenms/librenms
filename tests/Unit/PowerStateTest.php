<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Enum\PowerState;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PowerStateTest extends TestCase
{
    #[DataProvider('states')]
    public function testParse(string $state, int $expected): void
    {
        $this->assertSame($expected, PowerState::parse($state));
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function states(): array
    {
        return [
            'vmware off' => ['poweredOff', PowerState::OFF],
            'vmware on' => ['poweredOn', PowerState::ON],
            'vmware suspended' => ['suspended', PowerState::SUSPENDED],
            'libvirt off' => ['shut off', PowerState::OFF],
            'libvirt on' => ['running', PowerState::ON],
            'libvirt paused' => ['paused', PowerState::SUSPENDED],
            'proxmox stopped' => ['stopped', PowerState::OFF],
            'unknown' => ['crashed', PowerState::UNKNOWN],
        ];
    }
}
