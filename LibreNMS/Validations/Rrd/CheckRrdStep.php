<?php

namespace LibreNMS\Validations\Rrd;

use App\Facades\LibrenmsConfig;
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
    private string $rrdcached;
    private string $rrd_dir;
    private string $rrdtool_exec;
    private array $env = [];
    private InputStream $input;
    private ?Process $rrdtool = null;

    public function __construct()
    {
        $this->rrd_step = (int) LibrenmsConfig::get('rrd.step', self::DEFAULT_RRD_STEP);
        $this->ping_rrd_step = (int) LibrenmsConfig::get('ping_rrd_step', self::DEFAULT_PING_RRD_STEP);
        $this->rrdtool_exec = LibrenmsConfig::get('rrdtool', 'rrdtool');
        $this->rrdcached = (string) LibrenmsConfig::get('rrdcached', '');
        $this->rrd_dir = LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd');
        $this->input = new InputStream();

        if ($this->rrdcached) {
            $this->env['RRDCACHED_ADDRESS'] = $this->rrdcached;
        }
    }

    public function enabled(): bool
    {
        return LibrenmsConfig::get('rrd.step') !== self::DEFAULT_RRD_STEP || LibrenmsConfig::get('ping_rrd_step') !== self::DEFAULT_PING_RRD_STEP;
    }

    public function validate(): ValidationResult
    {
        $bad_step_files = [];
        $bad_files = [];

        $this->rrdtool = new Process(
            command: [$this->rrdtool_exec, '-'],
            cwd: $this->rrd_dir,
            env: $this->env,
        );
        $this->rrdtool->setInput($this->input);
        $this->rrdtool->setTimeout(15);
        $this->rrdtool->start();

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

    private function getStep(string $file): int
    {
        $output = $this->runCommand("info $file", 'step = ');

        if (preg_match('/step = (\d+)/', $output, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * @return string[]
     */
    private function listFiles(): array
    {
        $dir = $this->rrdcached ? '/' : $this->rrd_dir;
        $output = $this->runCommand("list --recursive $dir");
        $files = explode("\n", $output);
        array_pop($files); // remove OK

        return $files;
    }

    private function runCommand(string $command, string $waitFor = 'OK u:'): string
    {
        $this->rrdtool->clearOutput();
        $this->input->write("$command\n");
        $this->rrdtool->waitUntil(function ($type, $buffer) use ($waitFor) {
            if ($type === Process::ERR) {
                throw new \Exception($buffer);
            }

            if (str_contains($buffer, 'ERROR: ')) {
                preg_match('/ERROR: (.*)/', $buffer, $matches);
                throw new \Exception($matches[1]);
            }

            return str_contains($buffer, $waitFor);
        });

        return rtrim($this->rrdtool->getOutput());
    }
}
