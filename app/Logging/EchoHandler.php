<?php

/*
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
 * @copyright  2024 Steven Wilton
 * @author Steven Wilton <swilton@fluentit.au>
 */

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class EchoHandler extends AbstractProcessingHandler
{
    private $color_convert;

    public function __construct() {
        $this->color_convert = new \Console_Color2();
    }

    /**
     * @return void
     */
    protected function write (LogRecord $record): void
    {
        echo  preg_replace('/\033\[[\d;]+m/', '', $this->color_convert->convert($record->message)) . PHP_EOL;
        flush();
    }
}
