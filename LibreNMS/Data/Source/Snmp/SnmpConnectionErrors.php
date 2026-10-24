<?php

/**
 * SnmpConnectionErrors.php
 *
 * Collects queries the agent did not answer while running a callback, so callers that only
 * receive parsed data can tell a device without data from a device that did not answer.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Snmp;

use App\Events\SnmpQueryExecuted;
use Illuminate\Contracts\Events\Dispatcher;

class SnmpConnectionErrors
{
    private static ?Dispatcher $listeningTo = null;

    /** @var string[]|null errors of the running callback, null when not collecting */
    private static ?array $errors = null;

    /**
     * Run the callback and return its result with the errors of the queries the agent did not answer
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return array{0: T, 1: string[]}
     */
    public static function during(callable $callback): array
    {
        $events = app('events');
        if (self::$listeningTo !== $events) {
            $events->listen(SnmpQueryExecuted::class, function (SnmpQueryExecuted $event): void {
                if (self::$errors !== null && $event->response->hasConnectionError()) {
                    self::$errors[] = implode(',', $event->oids) . ': ' . trim($event->response->stderr);
                }
            });
            self::$listeningTo = $events;
        }

        $outer = self::$errors;
        self::$errors = [];

        try {
            $result = $callback();

            return [$result, self::$errors];
        } finally {
            self::$errors = $outer === null ? null : [...$outer, ...self::$errors];
        }
    }
}
