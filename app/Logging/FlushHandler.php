<?php

/*
 * Calls PHP flush to force any output in the PHP buffer to be sent immediately
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
 * @copyright  2024 Steven Wilton
 * @author Steven Wilton <swilton@fluentit.au>
 */

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class FlushHandler extends AbstractProcessingHandler
{
    public function __construct()
    {
    }

    /**
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        flush();
    }
}
