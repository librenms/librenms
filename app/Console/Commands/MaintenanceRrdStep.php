<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use Exception;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\RRD\RrdProcess;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceRrdStep extends LnmsCommand
{
    protected $name = 'maintenance:rrd-step';

    private int $systemStep;
    private int $icmpStep;
    private int $systemHeartbeat;

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device', InputArgument::REQUIRED);
        $this->addOption('confirm', null, InputOption::VALUE_NONE);
    }

    public function handle(RrdProcess $rrdProcess): int
    {
        $this->configureOutputOptions();

        $this->systemStep = (int) LibrenmsConfig::get('rrd.step', 300);
        $this->icmpStep = (int) LibrenmsConfig::get('ping_rrd_step', $this->systemStep);
        $this->systemHeartbeat = (int) LibrenmsConfig::get('rrd.heartbeat', $this->systemStep * 2);

        $hostname = (string) $this->argument('device');
        if ($hostname !== 'all') {
            $hostname = DeviceCache::get($hostname)->hostname; // hostname may be numeric
        }

        if (empty($hostname)) {
            $this->error(__('commands.maintenance:rrd-step.errors.invalid'));

            return 1;
        }

        if (! $this->option('confirm') && ! $this->confirm(__('commands.maintenance:rrd-step.confirm_backup'))) {
            return 0;
        }

        $files = Rrd::getRrdFiles($hostname === 'all' ? '' : $hostname);

        [$converted, $skipped, $failed] = [0, 0, 0];

        foreach ($files as $file) {
            $rrdFile = basename($file, '.rrd');

            [$step, $heartbeat] = $rrdFile === 'icmp-perf'
                ? [$this->icmpStep, $this->icmpStep * 2]
                : [$this->systemStep, $this->systemHeartbeat];

            try {
                $this->checkRrdFile($rrdProcess, $file, $step, $heartbeat);

                if ($this->getOutput()->isVerbose()) {
                    $this->info(__('commands.maintenance:rrd-step.skipping', ['file' => $file, 'step' => $step]));
                }
                $skipped++;
                continue;
            } catch (\RuntimeException $e) {
                $this->line($e->getMessage()); // inconsistent data found
            } catch (RrdException $e) {
                $this->error($e->getMessage());

                return 1;
            }

            try {
                $this->getOutput()->write(__('commands.maintenance:rrd-step.converting', ['file' => $file]) . ' ');

                $xmlContent = $rrdProcess->run("dump $file");
                $modifiedXml = $this->modifyXml($xmlContent, $step, $heartbeat);
                $rrdProcess->run("restore -f - $file\n$modifiedXml\x1A");

                $this->info('[OK]');
                $converted++;
            } catch (Exception $e) {
                $this->error('[FAIL]: ' . $e->getMessage());
                $failed++;
            }
        }

        $this->line(__('commands.maintenance:rrd-step.summary', [
            'converted' => $converted,
            'failed' => $failed,
            'skipped' => $skipped,
        ]));

        return $failed > 0 ? 1 : 0;
    }

    /**
     * @throws RrdException
     */
    private function checkRrdFile(RrdProcess $rrdProcess, string $file, int $step, int $heartbeat): void
    {
        $rrdInfo = $rrdProcess->run("info $file");

        if (! preg_match('/step = (\d+)/', $rrdInfo, $stepMatches) || $stepMatches[1] != $step) {
            throw new \RuntimeException(__('commands.maintenance:rrd-step.mismatched_heartbeat', ['file' => $file, 'ds' => 'step', 'hb' => $step]));
        }

        preg_match_all('/minimal_heartbeat = (\d+)/', $rrdInfo, $heartbeatMatches);

        foreach ($heartbeatMatches[1] as $dsHeartbeat) {
            if ((int) $dsHeartbeat !== $heartbeat) {
                throw new \RuntimeException(__('commands.maintenance:rrd-step.mismatched_heartbeat', ['file' => $file, 'ds' => $dsHeartbeat, 'hb' => $heartbeat]));
            }
        }
    }

    private function modifyXml(string $xmlContent, int $step, int $heartbeat): string
    {
        $xmlContent = (string) preg_replace('#<step>\d+</step>#', "<step>$step</step>", $xmlContent);
        $xmlContent = (string) preg_replace('#<minimal_heartbeat>\d+</minimal_heartbeat>#', "<minimal_heartbeat>$heartbeat</minimal_heartbeat>", $xmlContent);

        return $xmlContent;
    }
}
