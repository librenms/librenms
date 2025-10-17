<?php

namespace LibreNMS\Validations\Rrd;

use App\Facades\LibrenmsConfig;
use FilesystemIterator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class CheckRrdSteps implements Validation, ValidationFixer
{
    const DEFAULT_RRD_STEP = 300;
    const DEFAULT_PING_RRD_STEP = 300;

    private int $rrd_step;
    private int $ping_rrd_step;
    private string|false $rrdcached;
    private string $rrd_dir;
    private InputStream $input;
    private ?Process $rrdtool = null;

    public function __construct() {
        $this->rrd_step = (int) LibrenmsConfig::get('rrd.step', self::DEFAULT_RRD_STEP);
        $this->ping_rrd_step = (int) LibrenmsConfig::get('ping_rrd_step', self::DEFAULT_PING_RRD_STEP);
        $this->rrdcached = LibrenmsConfig::get('rrdcached', false);
        $this->rrd_dir = Str::finish(LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd'), '/');
        $this->input = new InputStream();
    }

    public function validate(): ValidationResult
    {
        $total = 0;
        $bad_step_files = [];
        $bad_files = [];

        $this->rrdtool = new Process([LibrenmsConfig::get('rrdtool', 'rrdtool'), '-']);
        $this->rrdtool->setInput($this->input);
        $this->rrdtool->setTimeout(15);
        $this->rrdtool->start();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->rrd_dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'rrd') {
                $total++;
                $rrd_file = $file->getPathname();
                $rrd_name = $this->getRelativePath($rrd_file);

                try {
                    $step = $this->getStep($rrd_file);
                    $target_step = $file->getBasename('.rrd') === 'icmp-perf' ? $this->ping_rrd_step : $this->rrd_step;

                    if ($step !== $target_step) {
                        $bad_step_files[] = $rrd_name . ': step is ' . $step . ', should be ' . $target_step;
                    }
                } catch (\Exception $e) {
                    $bad_files[] = $rrd_name . ': ' . $e->getMessage();
                }
            }
        }

        $this->input->write("\n\n");
        $this->rrdtool->stop();

        if (! empty($bad_step_files)) {
            return ValidationResult::fail('Some RRD files have the incorrect step. ' . count($bad_step_files) . "/$total")
                ->setList('RRD files with incorrect step (backup rrd files before applying fix)', $bad_step_files)
                ->setFixer(__CLASS__, is_writable($this->rrd_dir))
                ->setFix('lnms maintenance:rrd-step all');
        }

        if (! empty($bad_files)) {
            return ValidationResult::fail('Errors reading RRD files. ' . count($bad_files) . "/$total")
//                ->setFix('rm ' . implode(' ', array_map(fn($file) => escapeshellarg($this->rrd_dir . explode(':', $file, 2)[0]), $bad_files)))
                ->setList('Error running rrdinfo on files', $bad_files);
        }

        return ValidationResult::ok("All $total RRD files have the correct step.");
    }

    public function getStep(string $file): int
    {
        $step = 0;

        $this->rrdtool->clearOutput();
        $this->input->write($this->infoCommand($file) . "\n");

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

    public function fix(): bool
    {
        $return = Artisan::call('maintenance:rrd-step', ['device' => 'all', '--confirm']);

        return $return === 0;
    }

    private function infoCommand(string $file): string
    {
        $daemon = '';
        if ($this->rrdcached) {
            $daemon = " --daemon $this->rrdcached";
            $file = $this->getRelativePath($file);
        }

        return sprintf('info %s%s', $file, $daemon);
    }

    private function getRelativePath(string $file): string
    {
        return Str::chopStart($file, $this->rrd_dir);
    }
}
