<?php

namespace LibreNMS\Validations\Rrd;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Interfaces\Validation;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\ValidationResult;

class CheckRrdStep implements Validation
{
    const DEFAULT_RRD_STEP = 300;
    const DEFAULT_PING_RRD_STEP = 300;

    private int $ping_rrd_step;
    private int $rrd_step;

    private RrdProcess $rrdtool;

    public function __construct()
    {
        $this->ping_rrd_step = (int) LibrenmsConfig::get('ping_rrd_step', self::DEFAULT_PING_RRD_STEP);
        $this->rrd_step = (int) LibrenmsConfig::get('rrd.step', self::DEFAULT_RRD_STEP);

        $this->rrdtool = app(RrdProcess::class, ['timeout' => 30]);
    }

    public function enabled(): bool
    {
        return LibrenmsConfig::get('rrd.step') !== self::DEFAULT_RRD_STEP || LibrenmsConfig::get('ping_rrd_step') !== self::DEFAULT_PING_RRD_STEP;
    }

    public function validate(): ValidationResult
    {
        $bad_step_files = [];
        $bad_files = [];

        $rrd_files = $this->listFiles();

        foreach ($rrd_files as $rrd_file) {
            try {
                $step = $this->getStep($rrd_file);
                $target_step = str_ends_with($rrd_file, '/icmp-perf.rrd') ? $this->ping_rrd_step : $this->rrd_step;

                if ($step !== $target_step) {
                    $bad_step_files[] = __('validation.validations.rrd.CheckRrdStep.list_bad_step_item', ['file' => $rrd_file, 'step' => $step, 'target' => $target_step]);
                }
            } catch (\Exception $e) {
                $bad_files[] = $rrd_file . ': ' . $e->getMessage();
            }
        }

        $this->rrdtool->stop();

        $total = count($rrd_files);

        if (! empty($bad_step_files)) {
            return ValidationResult::fail(__('validation.validations.rrd.CheckRrdStep.fail', ['bad' => count($bad_step_files), 'total' => $total]))
                ->setList(__('validation.validations.rrd.CheckRrdStep.list_bad_step_title'), $bad_step_files)
                ->setFix('lnms maintenance:rrd-step all');
        }

        if (! empty($bad_files)) {
            return ValidationResult::fail(__('validation.validations.rrd.CheckRrdStep.fail_bad_files', ['bad' => count($bad_files), 'total' => $total]))
                ->setList(__('validation.validations.rrd.CheckRrdStep.list_bad_files_title'), $bad_files);
        }

        return ValidationResult::ok(__('validation.validations.rrd.CheckRrdStep.ok', ['total' => $total]));
    }

    /**
     * @throws RrdException
     */
    private function getStep(string $file): int
    {
        $output = $this->rrdtool->run("info $file", 'step = ');

        if (preg_match('/step = (\d+)/', $output, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * @return string[]
     *
     * @throws RrdException
     */
    private function listFiles(): array
    {
        $dir = LibrenmsConfig::get('rrdcached') ? '/' : LibrenmsConfig::get('rrd_dir');
        $output = $this->rrdtool->run("list --recursive $dir");
        $files = explode("\n", $output);
        array_pop($files); // remove OK

        return $files;
    }
}
