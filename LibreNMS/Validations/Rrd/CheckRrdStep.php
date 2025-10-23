<?php

namespace LibreNMS\Validations\Rrd;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class CheckRrdStep implements Validation
{
    const DEFAULT_RRD_STEP = 300;
    const DEFAULT_PING_RRD_STEP = 300;

    private int $rrd_step;
    private int $ping_rrd_step;
    private string|false $rrdcached;
    private string $rrd_dir;
    private InputStream $input;
    private ?Process $rrdtool = null;

    public function __construct()
    {
        $this->rrd_step = (int) LibrenmsConfig::get('rrd.step', self::DEFAULT_RRD_STEP);
        $this->ping_rrd_step = (int) LibrenmsConfig::get('ping_rrd_step', self::DEFAULT_PING_RRD_STEP);
        $this->rrdcached = LibrenmsConfig::get('rrdcached', false);
        $this->rrd_dir = Str::finish(LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd'), '/');
        $this->input = new InputStream();
    }

    public function validate(): ValidationResult
    {
        $bad_step_files = [];
        $bad_files = [];

        $this->rrdtool = new Process(
            command: [LibrenmsConfig::get('rrdtool', 'rrdtool'), '-'],
            cwd: $this->rrd_dir,
            env: $this->rrdcached ? ['RRDCACHED_ADDRESS' => $this->rrdcached] : [],
        );
        $this->rrdtool->setInput($this->input);
        $this->rrdtool->setTimeout(15);
        $this->rrdtool->start();

        $rrd_files = DB::table('devices')
            ->pluck('hostname')
            ->flatMap(fn ($hostname) => Rrd::getRrdFiles(['hostname' => $hostname]))
            ->map(fn ($file) => Str::chopStart($file, $this->rrd_dir))
            ->all();

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

        $this->input->write("\n\n");
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

    public function getStep(string $file): int
    {
        $step = 0;

        $this->rrdtool->clearOutput();
        $this->input->write("info $file\n");

        $this->rrdtool->waitUntil(function ($type, $buffer) use (&$step) {
            if ($type === Process::ERR) {
                throw new \Exception($buffer);
            }

            if (preg_match('/step = (\d+)/', $buffer, $matches)) {
                $step = (int) $matches[1];

                return true;
            }

            if (preg_match('/ERROR: (.*)/', $buffer, $matches)) {
                throw new \Exception($matches[1]);
            }

            return false;
        });

        return $step;
    }

    public function enabled(): bool
    {
        return LibrenmsConfig::get('rrd.step') !== self::DEFAULT_RRD_STEP || LibrenmsConfig::get('ping_rrd_step') !== self::DEFAULT_PING_RRD_STEP;
    }
}
