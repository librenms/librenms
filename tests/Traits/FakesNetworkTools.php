<?php

namespace LibreNMS\Tests\Traits;

use App\Facades\LibrenmsConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Util\Binaries;
use PHPUnit\Framework\Attributes\After;

/**
 * Fake snmpget and fping, so scans can be tested without a network.
 */
trait FakesNetworkTools
{
    /** @var string[] */
    private array $fakeToolFiles = [];

    /**
     * A fake snmpget, the community says how it replies: "<seconds>-<exit code>", exit code 0 is a reply.
     */
    protected function fakeSnmpget(): void
    {
        $this->setBinary('snmpget', $this->fakeTool(<<<'SH'
            #!/bin/sh
            while [ $# -gt 0 ] && [ "$1" != -c ]; do shift; done
            sleep "${2%-*}"
            exit "${2#*-}"
            SH));
    }

    /**
     * SNMP credentials for the fake snmpget.
     *
     * @param  array<int, string>  $communities  "<seconds>-<exit code>" by secret id
     * @return array<int, SnmpConfig>
     */
    protected function fakeSnmpConfigs(array $communities): array
    {
        return array_map(
            fn (string $community) => SnmpConfig::make((new SnmpPollingMethod)->defaults(), new SnmpSecretData(community: $community)),
            $communities,
        );
    }

    /**
     * A fake bulk fping, only the given addresses are alive.
     *
     * @param  string[]  $alive
     * @return string a file with the number of addresses of each fping run, one per line
     */
    protected function fakeFping(array $alive): string
    {
        $aliveFile = $this->fakeTool(implode(PHP_EOL, $alive) . PHP_EOL);
        $log = $this->fakeTool('');

        $this->setBinary('fping', $this->fakeTool(<<<SH
            #!/bin/sh
            count=0
            while read -r ip; do
              count=\$((count + 1))
              if grep -qxF "\$ip" $aliveFile; then echo "\$ip is alive"; else echo "\$ip is unreachable"; fi
            done
            echo \$count >> $log
            SH));

        return $log;
    }

    /**
     * Use the binary for both the config setting and Binaries.
     */
    protected function setBinary(string $binary, string $path): void
    {
        LibrenmsConfig::set($binary, $path);
        config(["librenms.binaries.$binary" => $path]);
        app()->forgetInstance(Binaries::class); // it caches the paths it found
    }

    #[After]
    protected function removeFakeTools(): void
    {
        array_map(unlink(...), $this->fakeToolFiles);
        $this->fakeToolFiles = [];
    }

    private function fakeTool(string $contents): string
    {
        $file = (string) tempnam(sys_get_temp_dir(), 'librenms-fake');
        file_put_contents($file, $contents);
        chmod($file, 0755);
        $this->fakeToolFiles[] = $file;

        return $file;
    }
}
