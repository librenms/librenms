<?php

/*
 * Action.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App;

class Action
{
    /**
     * Execute an action and return the results
     *
     * @param  string  $action
     * @param  mixed  ...$parameters
     * @return mixed
     */
    public static function execute(string $action, ...$parameters)
    {
        return app($action, self::namedParameters($action, $parameters))->execute();
    }

    /**
     * Map positional parameters to named keys using reflection so Laravel's
     * IoC container resolves them by name rather than falling back to its
     * type bindings.
     */
    private static function namedParameters(string $action, array $parameters): array
    {
        if (empty($parameters)) {
            return [];
        }

        $constructor = (new \ReflectionClass($action))->getConstructor();

        if (! $constructor) {
            return $parameters;
        }

        $named = [];
        foreach ($constructor->getParameters() as $i => $param) {
            if (array_key_exists($i, $parameters)) {
                $named[$param->getName()] = $parameters[$i];
            }
        }

        return $named;
    }
}
