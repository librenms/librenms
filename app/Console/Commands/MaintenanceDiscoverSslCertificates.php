<?php

namespace App\Console\Commands;

use AcmePhp\Ssl\Exception\CertificateParsingException;
use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\SslCertificate;
use Jalle19\CertificateParser\Parser;
use Jalle19\CertificateParser\Provider\Exception\ProviderException;
use Jalle19\CertificateParser\Provider\StreamContext;
use Jalle19\CertificateParser\Provider\StreamSocketProvider;
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

        $parser = new Parser();
        $context = new StreamContext();
        $context->setVerifyPeerName(false);

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

            try {
                $provider = new StreamSocketProvider($host, $port, $timeout, $context);
                $results = $parser->parse($provider);
            } catch (ProviderException|CertificateParsingException $e) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("  {$host}:{$port} – " . $e->getMessage());
                }
                $failed++;
                continue;
            }

            $data = array_merge(SslCertificate::attributesFromParserResults($results), [
                'device_id' => $device->device_id,
                'host' => $host,
                'port' => $port,
                'last_checked_at' => now(),
                'disabled' => false,
            ]);

            $existing = SslCertificate::where('device_id', $device->device_id)
                ->where('host', $host)
                ->where('port', $port)
                ->first();

            if ($existing) {
                $changes = SslCertificate::formatAttributeChanges($existing->only(['subject', 'issuer', 'valid_to', 'valid_from', 'fingerprint', 'days_until_expiry']), $data);
                // Always persist bookkeeping fields like last_checked_at, even if there are no meaningful changes.
                $existing->update($data);
                if ($changes !== '') {
                    $updated++;
                    Eventlog::log("SSL certificate updated: {$host}:{$port} – {$changes}", $device->device_id, 'ssl-certificate', Severity::Info, $existing->id);
                }
            } else {
                $cert = SslCertificate::create($data);
                $created++;
                $msg = "SSL certificate discovered: {$host}:{$port} – Subject: {$data['subject']}, Issuer: {$data['issuer']}, Valid until: " . ($data['valid_to'] ?? 'N/A') . ', Days until expiry: ' . ($data['days_until_expiry'] ?? 'N/A');
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
