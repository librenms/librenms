<?php
/**
 * ApplyPullRequest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use GuzzleHttp\Exception\TransferException;
use LibreNMS\ComposerHelper;
use LibreNMS\Config;
use LibreNMS\Util\Git;
use LibreNMS\Util\OSDefinition;
use Log;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class ApplyPullRequest extends LnmsCommand
{
    protected $name = 'test:pull-request';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.test:pull-request.description'));

        $this->addArgument(
            'PR Number',
            InputArgument::OPTIONAL,
            __('commands.test:pull-request.arguments.pull-request', ['url' => 'https://github.com/librenms/librenms/pull/'])
        );

        $this->addOption('remove', 'r', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $prNumber = (int)$this->argument('PR Number'); // make sure pull-request is an integer
        $remove = $this->option('remove');
        $type = $remove ? 'remove' : 'apply';

        $success = $remove ? $this->removePatch($prNumber) : $this->applyPatch($prNumber);

        if ($success) {
            ComposerHelper::install(!$this->getOutput()->isVerbose());
            OSDefinition::updateCache(true);
            $this->info(__("commands.test:pull-request.success.$type", ['number' => $prNumber]));
            return 0;
        }

        $this->error(__("commands.test:pull-request.failed.$type", ['number' => $prNumber]));
        return 1;
    }

    /**
     * Get the patch save directory
     *
     * @param string $extra string to append to path (should not include leading slash)
     * @return string
     */
    public static function getPatchSaveDir($extra = '')
    {
        return Config::installDir() . '/storage/framework/testing/' . $extra;
    }

    private function applyPatch($prNumber)
    {
        if (!$prNumber) {
            $this->error(__('commands.test:pull-request.pr-required'));
            exit(1);
        }

        $patch_exists = file_exists($this->getPatchPath($prNumber));
        if ($patch_exists || $this->downloadPatch($prNumber)) {
            $process = $this->runGitApply($prNumber);

            if ($process->isSuccessful()) {
                return true;
            }

            if ($patch_exists && Git::hasModifiedFiles()) {
                // patch may already be applied
                $this->warn(__('commands.test:pull-request.already-applied'));
                return false;
            }

            $this->deletePatch($prNumber);
            return $this->handleError($prNumber, $process->getErrorOutput());
        }

        $this->error(__('commands.test:pull-request.download_failed'));
        return false;
    }

    private function removePatch($number): bool
    {
        $patches = $this->getPatchNumbersToRemove($number);
        if (empty($patches)) {
            $this->warn(__('commands.test:pull-request.none-to-remove'));
            exit;
        }

        $result = false;

        foreach ($patches as $prNumber) {
            $process = $this->runGitApply($prNumber, ['--reverse']);
            Log::debug('Remove: ' . $process->getExitCode() . ' ' . $process->getOutput() . PHP_EOL . $process->getErrorOutput());
            $this->deletePatch($prNumber); // always delete the patch, if we fail to remove it, we will suggest lnms clean:files

            $result = $result && ($process->isSuccessful() || $this->handleError($prNumber, $process->getErrorOutput()));
        }

        $this->error(__('commands.test:pull-request.download_failed'));
        return $result;
    }

    private function getPatchUrl($number): string
    {
        return "https://patch-diff.githubusercontent.com/raw/librenms/librenms/pull/$number.diff";
    }

    private function getPatchPath($number): string
    {
        return self::getPatchSaveDir("/$number.diff");
    }

    private function deletePatch($number): void
    {
        if (file_exists($this->getPatchPath($number))) {
            unlink($this->getPatchPath($number));
        }
    }

    private function downloadPatch($number): bool
    {
        if (!is_dir(self::getPatchSaveDir())) {
            mkdir(self::getPatchSaveDir(), 0777, true);
        }

        $path = $this->getPatchPath($number);
        $uri = $this->getPatchUrl($number);

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($uri, ['sink' => $path]);
        } catch (TransferException $e) {
            Log::error('Error downloading: ' . $e->getMessage());
            $this->deletePatch($number);
            return false;
        }

        \Log::debug("Downloaded $path from $uri, code: " . $response->getStatusCode());
        if ($this->getOutput()->isDebug()) {
            \Log::debug(file_get_contents($this->getPatchPath($number)));
        }

        return $response->getStatusCode() == 200;
    }

    private function handleError($number, $errorOutput): bool
    {
        if (str_contains($errorOutput, 'error: unrecognized input')) {
            $this->error(__('commands.test:pull-request.download_failed'));
            $this->deletePatch($number);
        } else {
            $this->line($errorOutput);
        }

        return false;
    }

    private function runGitApply($number, $extra = []): Process
    {
        $verbose = $this->getOutput()->isVeryVerbose() ? ['--verbose'] : [];
        $command = array_merge(['git', 'apply'], $extra, $verbose, [$this->getPatchPath($number)]);
        $process = new Process($command, Config::installDir());
        $process->run();
        return $process;
    }

    private function getPatchNumbersToRemove($prNumber): array
    {
        if ($prNumber) {
            return (file_exists($this->getPatchPath($prNumber)) || $this->downloadPatch($prNumber)) ? [$prNumber] : [];
        }

        return array_map(function ($file) {
            return basename($file, '.diff');
        }, glob(self::getPatchSaveDir('*.diff')));
    }
}
