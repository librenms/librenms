<?php

namespace LibreNMS\Tests\Unit\Alert;

use LibreNMS\Alert\Transport;
use LibreNMS\Interfaces\Alert\AlertTransport;
use LibreNMS\Tests\TestCase;
use LogicException;

final class TransportEntryPointTest extends TestCase
{
    public function testBuiltInTransportsImplementAlertTransport(): void
    {
        foreach (array_keys(Transport::list()) as $type) {
            $class = Transport::getClass($type);
            $this->assertContains(AlertTransport::class, class_implements($class), "$class should implement AlertTransport");
        }
    }

    public function testTransportWithoutEntryPointThrows(): void
    {
        $transport = new class extends Transport
        {
            public static function configTemplate(): array
            {
                return [];
            }
        };

        $this->expectException(LogicException::class);
        $transport->deliverAlert(['hostname' => 'router1']);
    }
}
