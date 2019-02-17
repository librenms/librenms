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
    private $patchSaveDir = '/tmp/librenms_patches';
    private $prNumber;

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.test:pull-request.description'));

        $this->addArgument(
            'pull-request',
            InputArgument::REQUIRED,
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
        $this->prNumber = (int)$this->argument('pull-request'); // make sure pull-request is an integer
        $remove = $this->option('remove');
        $type = $remove ? 'remove' : 'apply';

        $success = $remove ? $this->removePatch() : $this->applyPatch();

        if ($success) {
            ComposerHelper::install(!$this->getOutput()->isVerbose());
            OSDefinition::updateCache(true);
            $this->info(__("commands.test:pull-request.success.$type", ['number' => $this->prNumber]));
            return 0;
        }

        $this->error(__("commands.test:pull-request.failed.$type", ['number' => $this->prNumber]));
        return 1;
    }

    private function applyPatch()
    {
        $patch_exists = file_exists($this->getPatchPath());
        if ($patch_exists || $this->downloadPatch()) {
            $process = $this->runGitApply();

            if ($process->isSuccessful()) {
                return true;
            }

            if ($patch_exists && Git::hasModifiedFiles()) {
                // patch may already be applied
                $this->warn(__('commands.test:pull-request.already-applied'));
                return false;
            }

            $this->deletePatch();
            return $this->handleError($process->getErrorOutput());
        }

        $this->error(__('commands.test:pull-request.download_failed'));
        return false;
    }

    private function removePatch()
    {
        if (file_exists($this->getPatchPath()) || $this->downloadPatch()) {
            $process = $this->runGitApply(['--reverse']);
            Log::debug('Remove: ' . $process->getExitCode() . ' ' . $process->getOutput() . PHP_EOL . $process->getErrorOutput());
            $this->deletePatch(); // always delete the patch, if we fail to remove it, we will suggest lnms clean:files

            return $process->isSuccessful() || $this->handleError($process->getErrorOutput());
        }

        $this->error(__('commands.test:pull-request.download_failed'));
        return false;
    }

    private function getPatchUrl()
    {
        return "https://patch-diff.githubusercontent.com/raw/librenms/librenms/pull/$this->prNumber.diff";
    }

    private function getPatchPath()
    {
        return $this->patchSaveDir . "/$this->prNumber.diff";
    }

    private function deletePatch(): void
    {
        if (file_exists($this->getPatchPath())) {
            unlink($this->getPatchPath());
        }
    }

    private function downloadPatch()
    {
        if (!is_dir($this->patchSaveDir)) {
            mkdir($this->patchSaveDir, 0777, true);
        }

        $path = $this->getPatchPath();
        $uri = $this->getPatchUrl();

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($uri, ['sink' => $path]);
        } catch (TransferException $e) {
            Log::error('Error downloading: ' . $e->getMessage());
            $this->deletePatch();
            return false;
        }

        \Log::debug("Downloaded $path from $uri, code: " . $response->getStatusCode());
        if ($this->getOutput()->isDebug()) {
            \Log::debug(file_get_contents($this->getPatchPath()));
        }

        return $response->getStatusCode() == 200;
    }

    private function handleError($errorOutput)
    {
        if (str_contains($errorOutput, 'error: unrecognized input')) {
            $this->error(__('commands.test:pull-request.download_failed'));
            $this->deletePatch();
        } else {
            $this->line($errorOutput);
        }

        return false;
    }

    private function runGitApply($extra = [])
    {
        $verbose = $this->getOutput()->isVeryVerbose() ? ['--verbose'] : [];
        $command = array_merge(['git', 'apply'], $extra, $verbose, [$this->getPatchPath()]);
        $process = new Process($command, Config::installDir());
        $process->run();
        return $process;
    }
}
