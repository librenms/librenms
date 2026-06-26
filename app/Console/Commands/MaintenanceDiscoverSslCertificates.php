<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\SslCertificate;
use LibreNMS\Enum\Severity;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceDiscoverSslCertificates extends LnmsCommand
{
    protected $name = 'maintenance:discover-ssl-certificates';

    public function __construct()
    {
        parent::__construct();
        $this->addOption('device', 'd', InputOption::VALUE_OPTIONAL, __('Device spec to discover: device_id, hostname, or all'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deviceSpec = $this->option('device') ?? 'all';

        $query = Device::query()->where('disabled', 0);
        if ($deviceSpec !== 'all') {
            $query->whereDeviceSpec($deviceSpec);
        }
        $devices = $query->get();

        if ($devices->isEmpty()) {
            $this->warn(__('commands.maintenance:discover-ssl-certificates.no_devices'));

            return 0;
        }

        $port = 443;
        $timeout = 10;
        $created = 0;
        $updated = 0;
        $failed = 0;
        $skipHosts = array_map(strtolower(...), (array) LibrenmsConfig::get('ssl_certificates.skip_hosts', []));

        /** @var \App\Models\Device $device */
        foreach ($devices as $device) {
            $host = $device->pollerTarget();
            if (empty($host)) {
                continue;
            }
            if (in_array(strtolower($host), $skipHosts, true)) {
                continue;
            }

            $existing = SslCertificate::where('device_id', $device->device_id)
                ->where('host', $host)
                ->where('port', $port)
                ->first();

            $cert = $existing ?? new SslCertificate([
                'device_id' => $device->device_id,
                'host' => $host,
                'port' => $port,
                'disabled' => false,
            ]);

            try {
                $cert->updateFromHost($timeout);
            } catch (\Throwable $e) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("  $host:$port – " . $e->getMessage());
                }
                $failed++;
                continue;
            }

            if ($existing) {
                $changes = $cert->getTrackedChanges();
                $cert->save();
                if ($changes !== '') {
                    $updated++;
                    Eventlog::log("SSL certificate updated: {$host}:{$port} – {$changes}", $device->device_id, 'ssl-certificate', Severity::Info, $existing->id);
                }
            } else {
                $cert->save();
                $created++;
                $msg = "SSL certificate discovered: {$host}:{$port} – Subject: {$cert->subject}, Issuer: {$cert->issuer}, Valid until: " . ($cert->valid_to?->format('Y-m-d H:i:s') ?? 'N/A') . ', Days until expiry: ' . ($cert->days_until_expiry ?? 'N/A');
                Eventlog::log($msg, $device->device_id, 'ssl-certificate', Severity::Info, $cert->id);
            }
        }

        $this->line(__('commands.maintenance:discover-ssl-certificates.summary', [
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
        ]));

        return 0;
    }
}
