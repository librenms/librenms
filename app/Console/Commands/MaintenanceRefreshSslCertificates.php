<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use App\Models\SslCertificate;
use LibreNMS\Enum\Severity;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceRefreshSslCertificates extends LnmsCommand
{
    protected $name = 'maintenance:refresh-ssl-certificates';

    public function __construct()
    {
        parent::__construct();
        $this->addOption('id', null, InputOption::VALUE_OPTIONAL, __('Certificate ID to refresh (omit to refresh all enabled)'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = $this->option('id');

        $query = SslCertificate::query()->where('disabled', 0);
        if ($id !== null && is_numeric($id)) {
            $query->where('id', $id);
        }
        $certificates = $query->get();

        $skipHosts = array_map(strtolower(...), (array) LibrenmsConfig::get('ssl_certificates.skip_hosts', []));
        if ($skipHosts !== []) {
            $certificates = $certificates->filter(fn (SslCertificate $cert) => ! in_array(strtolower($cert->host), $skipHosts, true));
        }

        if ($certificates->isEmpty()) {
            $this->warn(__('commands.maintenance:refresh-ssl-certificates.none'));

            return 0;
        }

        $timeout = 10;
        $refreshed = 0;
        $failed = 0;

        /** @var \App\Models\SslCertificate $cert */
        foreach ($certificates as $cert) {
            try {
                $cert->updateFromHost($timeout);
            } catch (\Throwable $e) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("  $cert->host:$cert->port – " . $e->getMessage());
                }
                $failed++;
                continue;
            }

            $changes = $cert->getTrackedChanges();
            $cert->save();
            if ($changes !== '') {
                $refreshed++;
                Eventlog::log("SSL certificate refreshed: {$cert->host}:{$cert->port} – {$changes}", $cert->device_id, 'ssl-certificate', Severity::Info, $cert->id);
            }
        }

        $this->line(__('commands.maintenance:refresh-ssl-certificates.summary', [
            'refreshed' => $refreshed,
            'failed' => $failed,
        ]));

        return 0;
    }
}
