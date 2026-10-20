<?php

/**
 * FiberPool.php
 *
 * Run tasks concurrently in fibers.
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

namespace LibreNMS\Scan;

use Fiber;

class FiberPool
{
    /**
     * Run a task for each item, at most $concurrency at a time. A task waits with FiberPool::waitUntil().
     *
     * @template TItem
     * @template TResult
     *
     * @param  iterable<TItem>  $items
     * @param  callable(TItem): TResult  $task
     * @param  callable(TItem, TResult): void  $onResult
     */
    public function run(iterable $items, callable $task, int $concurrency, callable $onResult): void
    {
        $pending = (fn () => yield from $items)();
        /** @var array<int, array{TItem, Fiber<TItem, null, TResult, null>}> $active */
        $active = [];

        while ($pending->valid() || $active !== []) {
            while (count($active) < $concurrency && $pending->valid()) {
                $fiber = new Fiber($task);
                $active[] = [$pending->current(), $fiber];
                $fiber->start($pending->current());
                $pending->next();
            }

            foreach ($active as $key => [$item, $fiber]) {
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                }

                if ($fiber->isTerminated()) {
                    $onResult($item, $fiber->getReturn());
                    unset($active[$key]);
                }
            }

            if ($active !== []) {
                usleep(1000);
            }
        }
    }

    /**
     * Wait until the condition is true, other tasks run in the meantime.
     *
     * @param  callable(): bool  $condition
     */
    public static function waitUntil(callable $condition): void
    {
        while (! $condition()) {
            if (Fiber::getCurrent() === null) {
                usleep(1000);
            } else {
                Fiber::suspend();
            }
        }
    }
}
