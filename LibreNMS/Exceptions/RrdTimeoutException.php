<?php

/**
 * RrdTimeoutException.php
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
 */

namespace LibreNMS\Exceptions;

use Symfony\Component\Process\Exception\ProcessTimedOutException;

class RrdTimeoutException extends RrdStoreException
{
    public static function fromProcessTimeout(ProcessTimedOutException $e, string $command): self
    {
        $reason = $e->isIdleTimeout()
            ? 'rrdtool did not respond within ' . $e->getExceededTimeout() . ' seconds'
            : 'rrdtool did not respond: the process lifetime of ' . $e->getExceededTimeout() . ' seconds ran out';

        return new self("$reason, running: $command", 0, $e);
    }
}
