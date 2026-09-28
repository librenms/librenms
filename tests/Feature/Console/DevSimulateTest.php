<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Console\Commands\DevSimulate;
use App\Models\Device;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\Snmpsim;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class DevSimulateTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testAddedDeviceCanBePolledWithTheCurrentCommunityAndPort(): void
    {
        $this->addDevice('first-community', new Snmpsim(port: 1161));
        $config = Device::where('hostname', 'snmpsim')->firstOrFail()->polling()->snmp();
        $this->assertSame('first-community', $config->community);
        $this->assertSame('v2c', $config->version);
        $this->assertSame(1161, $config->port);

        // running again with another community and port updates the device
        $this->addDevice('second-community', new Snmpsim(port: 1162));
        $config = Device::where('hostname', 'snmpsim')->firstOrFail()->polling()->snmp();
        $this->assertSame('second-community', $config->community);
        $this->assertSame(1162, $config->port);
    }

    /**
     * Add the device like dev:simulate does after snmpsim started, without starting snmpsim.
     */
    private function addDevice(string $community, Snmpsim $snmpsim): void
    {
        $command = new DevSimulate;
        $command->setLaravel($this->app);
        $input = new ArrayInput([], $command->getDefinition());
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, new BufferedOutput));

        (new \ReflectionProperty($command, 'snmpsim'))->setValue($command, $snmpsim);

        (new \ReflectionMethod($command, 'addDevice'))->invoke($command, $community);
    }
}
