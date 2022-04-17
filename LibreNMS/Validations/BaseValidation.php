<?php
/**
 * BaseValidation.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationGroup;
use LibreNMS\Validator;

abstract class BaseValidation implements ValidationGroup
{
    protected $completed = false;
    protected static $RUN_BY_DEFAULT = true;
    protected $directory = null;
    protected $name = null;

    public function validate(Validator $validator)
    {
        if ($this->directory) {
            foreach (glob(__DIR__ . "/$this->directory/*.php") as $file) {
                $base = basename($file, '.php');
                $class = __NAMESPACE__ . "\\$this->directory\\$base";
                $validation = new $class;
                if ($validation instanceof Validation && $validation->enabled()) {
                    $validator->result($validation->validate(), $this->name);
                }
            }
        }
    }

    /**
     * Returns if this test should be run by default or not.
     *
     * @return bool
     */
    public function isDefault()
    {
        return static::$RUN_BY_DEFAULT;
    }

    /**
     * Returns true if this group has been run
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * Mark this group as completed
     *
     * @return void
     */
    public function markCompleted()
    {
        $this->completed = true;
    }
}
