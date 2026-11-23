<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\OS\Gaia;
use LibreNMS\Tests\TestCase;

final class GaiaVirtualSystemTest extends TestCase
{
    public function testEveryInterfaceInAVirtualSystemIsAVirtualSystem(): void
    {
        $this->assertTrue(Gaia::isVirtualSystem(['1.0' => '1', '2.0' => '1', '3.0' => '1']));
    }

    public function testVs0InterfaceAmongVirtualSystemInterfacesIsNotAVirtualSystem(): void
    {
        $this->assertFalse(Gaia::isVirtualSystem(['1.0' => '0', '2.0' => '1', '3.0' => '2']));
    }

    public function testVs0IsNotAVirtualSystem(): void
    {
        $this->assertFalse(Gaia::isVirtualSystem(['1.0' => '0', '2.0' => '0']));
    }

    public function testNoVsidIsNotAVirtualSystem(): void
    {
        $this->assertFalse(Gaia::isVirtualSystem([]));
    }
}
