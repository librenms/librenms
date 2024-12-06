<?php
/*
 * Result.php
 *
 * Tally attempts and completions
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling;

class Result
{
    private int $attempted = 0;
    private int $completed = 0;

    public function markAttempted(): void
    {
        $this->attempted++;
    }

    public function markCompleted(bool $success = true): void
    {
        if ($success) {
            $this->completed++;
        }
    }

    public function hasNoAttempts(): bool
    {
        return $this->attempted == 0;
    }

    public function hasNoCompleted(): bool
    {
        return $this->completed == 0;
    }

    public function hasAnyCompleted(): bool
    {
        return $this->completed > 0;
    }

    public function hasMultipleCompleted(): bool
    {
        return $this->completed > 1;
    }

    public function getCompleted(): int
    {
        return $this->completed;
    }

    public function getAttempted(): int
    {
        return $this->attempted;
    }
}
