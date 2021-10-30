<?php
/**
 * LnmsCommand.php
 *
 * Convenience class for common command code
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use LibreNMS\Util\Debug;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Validator;

abstract class LnmsCommand extends Command
{
    protected $developer = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDescription(__('commands.' . $this->getName() . '.description'));
    }

    public function isHidden(): bool
    {
        $env = $this->getLaravel() ? $this->getLaravel()->environment() : getenv('APP_ENV');

        return $this->hidden || ($this->developer && $env !== 'production');
    }

    /**
     * Adds an argument. If $description is null, translate commands.command-name.arguments.name
     * If you want the description to be empty, just set an empty string
     *
     * @param  string  $name  The argument name
     * @param  int|null  $mode  The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param  string  $description  A description text
     * @param  string|string[]|null  $default  The default value (for InputArgument::OPTIONAL mode only)
     * @return $this
     *
     * @throws InvalidArgumentException When argument mode is not valid
     */
    public function addArgument($name, $mode = null, $description = null, $default = null)
    {
        // use a generated translation location by default
        if (is_null($description)) {
            $description = __('commands.' . $this->getName() . '.arguments.' . $name);
        }

        parent::addArgument($name, $mode, $description, $default);

        return $this;
    }

    /**
     * Adds an option. If $description is null, translate commands.command-name.arguments.name
     * If you want the description to be empty, just set an empty string
     *
     * @param  string  $name  The option name
     * @param  string|array|null  $shortcut  The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
     * @param  int|null  $mode  The option mode: One of the InputOption::VALUE_* constants
     * @param  string  $description  A description text
     * @param  string|string[]|int|bool|null  $default  The default value (must be null for InputOption::VALUE_NONE)
     * @return $this
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = null, $default = null)
    {
        // use a generated translation location by default
        if (is_null($description)) {
            $description = __('commands.' . $this->getName() . '.options.' . $name);
        }

        parent::addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * Validate the input of this command.  Uses Laravel input validation
     * merging the arguments and options together to check.
     */
    protected function validate(array $rules, array $messages = []): array
    {
        $error_messages = trans('commands.' . $this->getName() . '.validation-errors');
        $validator = Validator::make(
            $this->arguments() + $this->options(),
            $rules,
            is_array($error_messages) ? array_merge($error_messages, $messages) : $messages
        );

        try {
            $validator->validate();

            return $validator->validated();
        } catch (ValidationException $e) {
            collect($validator->getMessageBag()->all())->each(function ($message) {
                $this->error($message);
            });
            exit(1);
        }
    }

    protected function configureOutputOptions(): void
    {
        \Log::setDefaultDriver($this->getOutput()->isQuiet() ? 'stack' : 'console');
        if (($verbosity = $this->getOutput()->getVerbosity()) >= 128) {
            Debug::set();
            if ($verbosity >= 256) {
                Debug::setVerbose();
            }
        }
    }
}
