<?php

/**
 * GraphException.php
 *
 * A graph could not be produced. The short text is shown instead of the message on small graphs.
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

namespace LibreNMS\Exceptions;

use Throwable;

class GraphException extends \Exception
{
    public function __construct(string $message = '', public readonly ?string $shortText = null, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
