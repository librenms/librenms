<?php
/*
 * RrdGraphException.php
 *
 * Exception generated when there is an error creating the graph image
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

class RrdGraphException extends Exception
{
    protected $image_output;

    public function __construct($error, $exit_code, $image_output)
    {
        parent::__construct($error, $exit_code);
        $this->image_output = $image_output;
    }

    public function getImage()
    {
        return $this->image_output;
    }
}
