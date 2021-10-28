<?php

/**
 * IPMICommand.php
 *
 * IPMI Command
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
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\IPMI;

use Exception;
use LibreNMS\Util\Debug;
use Symfony\Component\Process\Process;

/**
 * Represents an executable IPMICommand.
 */
final class IPMICommand
{
    /**
     * @var array $command The command used passed to the executing process.
     */
    private $command;

    /**
     * @var Process $proc The process used for executing the command.
     */
    private $proc;

    /**
     * Create a new instance of the IPMICommand class.
     *
     * @param  array  $command  The command to run and its arguments.
     */
    public function __construct(array $command)
    {
        $this->command = $command;
    }

    /**
     * Executes the command and returns output.
     *
     * @return null|string The standard output of the command. Null if exit code is greater than 0.
     */
    public function execute(): ?string
    {
        if ($this->proc != null) {
            throw new Exception('The command has already been executed.');
        }

        $this->printInput($this->command);

        $this->proc = new Process($this->command);
        $this->proc->run();

        $this->printOutput([$this->proc->getErrorOutput(), $this->proc->getOutput()]);

        return $this->proc->getExitCode() > 0 ? null : $this->proc->getOutput();
    }

    /**
     * Gets a value indicating whether the command returned an error.
     */
    public function hasError(): bool
    {
        return ! $this->proc->isRunning()
            && $this->proc->getExitCode() > 0;
    }

    private static function printInput(array $input): void
    {
        if (! (Debug::isVerbose() || Debug::isEnabled())) {
            return;
        }

        $patterns = [
            '/-U [\S]+/',
            '/-P [\S]+/',
            '/-H [\S]+/',
        ];
        $replacements = [
            '-U USER',
            '-P PASSWORD',
            '-H HOSTNAME',
        ];

        $filtered = preg_replace($patterns, $replacements, join(' ', $input));
        c_echo('IPMI[%c' . $filtered . "%n]\n");
    }

    private function printOutput(array $outErr): void
    {
        d_echo($outErr[0] . PHP_EOL);

        if ($this->proc->getExitCode()) {
            d_echo('Exitcode: ' . $this->proc->getExitCode());
            d_echo($outErr[1]);
        }
    }
}
