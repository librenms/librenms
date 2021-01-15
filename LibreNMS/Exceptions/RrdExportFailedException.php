<?php
/*
 * RrdExportFailedException.php
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

namespace LibreNMS\Exceptions;

use Exception;

class RrdExportFailedException extends Exception
{
    private $command;
    private $error_output;
    private $output;
    private $json_error;

    public function __construct($command, $error_output, $output, $json_error, $previous = null)
    {
        parent::__construct("Failed to export RRD data with [$command]: \n $error_output\n$output \n $json_error", 0, $previous);

        $this->command = $command;
        $this->error_output = $error_output;
        $this->output = $output;
        $this->json_error = $json_error;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return string
     */
    public function getErrorOutput()
    {
        return $this->error_output;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return string
     */
    public function getJsonError()
    {
        return $this->json_error;
    }
}
