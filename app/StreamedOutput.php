<?php
/**
 * StreamedOutput.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App;

use RuntimeException;
use Symfony\Component\Console\Output\StreamOutput;

class StreamedOutput extends StreamOutput
{
    protected function doWrite($message, $newline)
    {
        if (false === @fwrite($this->getStream(), $message) || ($newline && (false === @fwrite($this->getStream(), PHP_EOL)))) {
            throw new RuntimeException('Unable to write output.');
        }

        echo $message . PHP_EOL;

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
