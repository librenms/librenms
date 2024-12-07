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
use LibreNMS\Util\Graph;

class RrdGraphException extends Exception
{
    /** @var string */
    protected $image_output;
    /** @var string|null */
    private $short_text;
    /** @var int|string|null */
    private $width;
    /** @var int|string|null */
    private $height;

    /**
     * @param  string  $error
     * @param  string|null  $short_text
     * @param  int|string|null  $width
     * @param  int|string|null  $height
     * @param  int  $exit_code
     * @param  string  $image_output
     */
    public function __construct($error, $short_text = null, $width = null, $height = null, $exit_code = 0, $image_output = '')
    {
        parent::__construct($error, $exit_code);
        $this->short_text = $short_text;
        $this->image_output = $image_output;
        $this->width = $width;
        $this->height = $height;
    }

    public function getImage(): string
    {
        return $this->image_output;
    }

    public function generateErrorImage(): string
    {
        return Graph::error(
            $this->getMessage(),
            $this->short_text,
            empty($this->width) ? 300 : (int) $this->width,
            empty($this->height) ? null : (int) $this->height,
        );
    }
}
