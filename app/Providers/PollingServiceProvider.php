<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LibreNMS\Polling\Method\Definitions\IcmpPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\IpmiPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\SnmpPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\UnixAgentPollingMethodDefinition;
use LibreNMS\Polling\Secrets\Definitions\IpmiSecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;

class PollingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('polling.method.snmp', SnmpPollingMethodDefinition::class);
        $this->app->singleton('polling.secret.snmp', SnmpSecretDefinition::class);

        $this->app->singleton('polling.method.icmp', IcmpPollingMethodDefinition::class);

        $this->app->singleton('polling.method.ipmi', IpmiPollingMethodDefinition::class);
        $this->app->singleton('polling.secret.ipmi', IpmiSecretDefinition::class);

        $this->app->singleton('polling.method.unixagent', UnixAgentPollingMethodDefinition::class);
    }
}
