<?php

/**
 * CommandStartingListener.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Listeners;

use App\Exceptions\RunningAsIncorrectUserException;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Debug;
use Symfony\Component\Console\Output\OutputInterface;

class CommandStartingListener
{
    private array $skip_user_check = [
        'list:bash-completion',
    ];

    /**
     * @throws RunningAsIncorrectUserException
     */
    public function handle(CommandStarting $event): void
    {
        $this->configureOutput($event);
        $this->checkRunningUser($event);
    }

    private function configureOutput(CommandStarting $event): void
    {
        $verbosity = $event->output->getVerbosity();

        if ($verbosity === OutputInterface::VERBOSITY_QUIET) {
            Log::setDefaultDriver('stack'); // this omits stdout
            Debug::setCliQuietOutput();

            return;
        }

        Log::setDefaultDriver('console');

        if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            Debug::set();
            if ($verbosity >= OutputInterface::VERBOSITY_DEBUG) {
                Debug::setVerbose();
            }
        }
    }

    private function checkRunningUser(CommandStarting $event): void
    {
        // Check that we don't run this as the wrong user and break the install
        if (in_array($event->command, $this->skip_user_check)) {
            return;
        }

        if (! function_exists('posix_getpwuid') || ! function_exists('posix_geteuid')) {
            return;
        }

        $current_user = posix_getpwuid(posix_geteuid())['name'];
        $executable = basename((string) ($_SERVER['argv'][0] ?? $_SERVER['SCRIPT_FILENAME'] ?? 'this'));

        if ($current_user == 'root') {
            throw new RunningAsIncorrectUserException("Error: $executable must not run as root.");
        }

        $librenms_user = config('librenms.user');
        if ($librenms_user !== $current_user) {
            throw new RunningAsIncorrectUserException("Error: $executable must be run as the user $librenms_user.");
        }
    }
}
