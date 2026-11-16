<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use LibreNMS\RRD\Backend\PhpRrd;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\Backend\Rrdtool;
use LibreNMS\Tests\TestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Rrd\CheckRrdBackend;

final class CheckRrdBackendTest extends TestCase
{
    public function testRrdtoolIsNotChecked(): void
    {
        LibrenmsConfig::set('rrd.backend', 'rrdtool');

        $this->assertFalse((new CheckRrdBackend)->enabled());
        $this->assertInstanceOf(Rrdtool::class, app(RrdBackendInterface::class));
    }

    public function testPhpRrdBackend(): void
    {
        LibrenmsConfig::set('rrd.backend', 'php-rrd');
        LibrenmsConfig::set('rrdcached', 'unix:/run/rrdcached.sock');

        if (! extension_loaded('rrd')) {
            $this->assertSame(ValidationResult::FAILURE, (new CheckRrdBackend)->validate()->getStatus());

            return;
        }

        $this->assertSame(ValidationResult::SUCCESS, (new CheckRrdBackend)->validate()->getStatus());
        $this->assertInstanceOf(PhpRrd::class, app(RrdBackendInterface::class));
    }
}
