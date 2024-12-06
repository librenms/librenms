<?php
/**
 * DynamicInputOption.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console;

use Symfony\Component\Console\Input\InputOption;

class DynamicInputOption extends InputOption
{
    /** @var callable|null */
    private $descriptionCallable;
    /** @var callable|null */
    private $defaultCallable;

    public function __construct(string $name, $shortcut = null, int $mode = null, string $description = '', $default = null, ?callable $defaultCallable = null, ?callable $descriptionCallable = null)
    {
        $this->descriptionCallable = $descriptionCallable;
        $this->defaultCallable = $defaultCallable;

        parent::__construct($name, $shortcut, $mode, $description, $default);
    }

    public function getDescription(): string
    {
        $description = parent::getDescription();

        if (is_callable($this->descriptionCallable)) {
            $description .= ' [' . implode(', ', call_user_func($this->descriptionCallable)) . ']';
        }

        return $description;
    }

    public function getDefault(): array|string|int|float|bool|null
    {
        if (is_callable($this->defaultCallable)) {
            return call_user_func($this->defaultCallable);
        }

        return parent::getDefault();
    }
}
