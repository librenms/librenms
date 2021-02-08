<?php
/**
 * RRDRecursiveFilterIterator.php
 *
 * Reursive Filter Iterator to iterate directories and locate .rrd files.
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
 * @copyright  2016
 * @author
 */

namespace LibreNMS;

/**
 * Reursive Filter Iterator to iterate directories and locate .rrd files.
 *
 * @method bool isDir()
 *
 **/
class RRDRecursiveFilterIterator extends \RecursiveFilterIterator
{
    public function accept()
    {
        $filename = $this->current()->getFilename();
        if ($filename[0] === '.') {
            // Ignore hidden files and directories
            return false;
        }
        if ($this->isDir()) {
            // We want to search into directories
            return true;
        }
        // Matches files with .rrd in the filename.
        // We are only searching rrd folder, but there could be other files and we don't want to cause a stink.
        return strpos($filename, '.rrd') !== false;
    }
}
