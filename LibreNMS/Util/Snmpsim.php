<?php
/**
 * Snmpsim.php
 *
 * Light wrapper around Snmpsim
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class Snmpsim extends Process
{
    public readonly string $snmprec_dir;

    public function __construct(
        public readonly string $ip = '127.1.6.1',
        public readonly int $port = 1161,
        public readonly ?string $log_method = null)
    {
        $this->snmprec_dir = base_path('tests/snmpsim');

        $cmd = [
            $this->getVenvPath('bin/snmpsim-command-responder-lite'),
            "--data-dir={$this->snmprec_dir}",
            "--agent-udpv4-endpoint={$this->ip}:{$this->port}",
            '--log-level=error',
        ];

        if ($this->log_method !== null) {
            $cmd[] = "--logging-method=$this->log_method";
        }

        parent::__construct($cmd, base_path());
        $this->setTimeout(null); // no timeout by default
    }

    public function waitForStartup(): string
    {
        $listen = $this->ip . ':' . $this->port;
        $this->waitUntil(function ($type, $buffer) use ($listen, &$last) {
            $last = $buffer;

            return $type == Process::ERR && str_contains($buffer, $listen);
        });

        return trim($last);
    }

    public function isVenvSetUp(): bool
    {
        if (! is_executable($this->getVenvPath('bin/snmpsim-command-responder-lite'))) {
            return false;
        }

        // check that snmpsim package actually exists
        $pipCheck = new Process([$this->getVenvPath('bin/pip'), 'show', 'snmpsim']);
        $pipCheck->run();

        return $pipCheck->isSuccessful();
    }

    public function setupVenv($print_output = false): void
    {
        $snmpsim_venv_path = $this->getVenvPath();

        if (! $this->isVenvSetUp()) {
            Log::info('Setting up snmpsim virtual env in ' . $snmpsim_venv_path);

            $setupProcess = new Process(['/usr/bin/env', 'python3', '-m', 'venv', $snmpsim_venv_path]);
            $setupProcess->setTty($print_output);
            $setupProcess->run();

            if (! $setupProcess->isSuccessful()) {
                Log::info($setupProcess->getOutput());
                Log::error($setupProcess->getErrorOutput());
            }

            $installProcess = new Process([$snmpsim_venv_path . '/bin/pip', 'install', 'snmpsim>=1.1.7']);
            $installProcess->setTty($print_output);
            $installProcess->run();

            if (! $installProcess->isSuccessful()) {
                Log::info($installProcess->getOutput());
                Log::error($installProcess->getErrorOutput());
            }
        }
    }

    public function getVenvPath(string $subdir = ''): string
    {
        return base_path('.python_venvs/snmpsim/' . $subdir);
    }
}
