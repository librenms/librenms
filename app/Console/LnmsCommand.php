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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
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

    public function isHidden()
    {
        $env = $this->getLaravel() ? $this->getLaravel()->environment() : getenv('APP_ENV');

        return $this->hidden || ($this->developer && $env !== 'production');
    }

    /**
     * Adds an argument. If $description is null, translate commands.command-name.arguments.name
     * If you want the description to be empty, just set an empty string
     *
     * @param string               $name        The argument name
     * @param int|null             $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param string               $description A description text
     * @param string|string[]|null $default     The default value (for InputArgument::OPTIONAL mode only)
     *
     * @throws InvalidArgumentException When argument mode is not valid
     *
     * @return $this
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
     * @param string                        $name        The option name
     * @param string|array|null             $shortcut    The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
     * @param int|null                      $mode        The option mode: One of the InputOption::VALUE_* constants
     * @param string                        $description A description text
     * @param string|string[]|int|bool|null $default     The default value (must be null for InputOption::VALUE_NONE)
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     *
     * @return $this
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
     *
     * @param array $rules
     * @param array $messages
     */
    protected function validate($rules, $messages = [])
    {
        $validator = Validator::make($this->arguments() + $this->options(), $rules, $messages);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            collect($validator->getMessageBag()->all())->each(function ($message) {
                $this->error($message);
            });
            exit(1);
        }
    }
}
