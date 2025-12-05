<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceRrdStep extends LnmsCommand
{
    protected $name = 'maintenance:rrd-step';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device', InputArgument::REQUIRED);
        $this->addOption('confirm', null, InputOption::VALUE_NONE);
    }

    public function handle(): int
    {
        $this->configureOutputOptions();

        $hostname = (string) $this->argument('device');
        if ($hostname !== 'all') {
            $hostname = DeviceCache::get($hostname)->hostname;
        }

        if (empty($hostname)) {
            $this->error(__('commands.maintenance:rrd-step.errors.invalid'));

            return 1;
        }

        if (! $this->option('confirm') && ! $this->confirm(__('commands.maintenance:rrd-step.confirm_backup'))) {
            return 0;
        }

        $systemStep = (int) LibrenmsConfig::get('rrd.step', 300);
        $icmpStep = (int) LibrenmsConfig::get('ping_rrd_step', $systemStep);
        $systemHeartbeat = (int) LibrenmsConfig::get('rrd.heartbeat', $systemStep * 2);
        $rrdtool = (string) LibrenmsConfig::get('rrdtool', 'rrdtool');
        $daemon = LibrenmsConfig::get('rrdcached') ? '-d ' . escapeshellarg(LibrenmsConfig::get('rrdcached')) : '';
        $tmpPath = (string) LibrenmsConfig::get('temp_dir', '/tmp');
        $rrdDir = (string) LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd');

        $files = glob($hostname === 'all'
            ? Str::finish($rrdDir, '/') . '*/*.rrd'
            : Rrd::dirFromHost($hostname) . '/*.rrd') ?: [];

        [$converted, $skipped, $failed] = [0, 0, 0];

        foreach ($files as $file) {
            $random = rtrim($tmpPath, '/') . '/' . mt_rand() . '.xml';
            $rrdFile = basename($file, '.rrd');

            [$step, $heartbeat] = $rrdFile === 'icmp-perf'
                ? [$icmpStep, $icmpStep * 2]
                : [$systemStep, $systemHeartbeat];

            $rrdInfo = shell_exec("$rrdtool info $daemon " . escapeshellarg($file)) ?? '';

            if (preg_match('/step = (\d+)/', $rrdInfo, $stepMatches) && $stepMatches[1] == $step) {
                preg_match_all('/minimal_heartbeat = (\d+)/', $rrdInfo, $heartbeatMatches);

                $allOk = true;
                foreach ($heartbeatMatches[1] as $dsHeartbeat) {
                    if ((int) $dsHeartbeat === (int) $heartbeat) {
                        continue;
                    }

                    $allOk = false;
                    $this->line(__('commands.maintenance:rrd-step.mismatched_heartbeat', ['file' => $file, 'ds' => $dsHeartbeat, 'hb' => $heartbeat]));
                    break;
                }

                if ($allOk) {
                    if ($this->getOutput()->isVerbose()) {
                        $this->info(__('commands.maintenance:rrd-step.skipping', ['file' => $file, 'step' => $step]));
                    }
                    $skipped++;
                    continue;
                }
            }

            $this->getOutput()->write(__('commands.maintenance:rrd-step.converting', ['file' => $file]) . ' ');

            $commands = [
                "$rrdtool dump $daemon " . escapeshellarg($file) . ' > ' . escapeshellarg($random),
                "sed -i 's/<step>\\([0-9]*\\)/<step>$step/' " . escapeshellarg($random),
                "sed -i 's/<minimal_heartbeat>\\([0-9]*\\)/<minimal_heartbeat>$heartbeat/' " . escapeshellarg($random),
                "$rrdtool restore $daemon -f " . escapeshellarg($random) . ' ' . escapeshellarg($file),
                'rm -f ' . escapeshellarg($random),
            ];

            exec(implode(' && ', $commands), $output, $code);

            if ($code === 0) {
                $this->info('[OK]');
                $converted++;
            } else {
                $this->error('[FAIL]');
                $failed++;
            }
        }

        $this->line(__('commands.maintenance:rrd-step.summary', ['converted' => $converted, 'failed' => $failed, 'skipped' => $skipped]));

        return $failed > 0 ? 1 : 0;
    }
}
