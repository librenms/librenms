<?php
/**
 * DatabaseConnectException.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

use Illuminate\Database\QueryException;
use LibreNMS\Interfaces\Exceptions\UpgradeableException;

class DatabaseConnectException extends \Exception implements UpgradeableException
{
    /**
     * Try to convert the given Exception to a DatabaseConnectException
     *
     * @param \Exception $e
     * @return static
     */
    public static function upgrade($e)
    {
        if ($e instanceof QueryException) {
            // connect exception, convert to our standard connection exception
            if (config('app.debug')) {
                // get message form PDO exception, it doesn't contain the query
                $message = $e->getMessage();
            } else {
                $message = $e->getPrevious()->getMessage();
            }

            if (in_array($e->getCode(), [1044, 1045, 2002])) {
                // this Exception has it's own render function
                return new static($message, $e->getCode(), $e);
            }
        }

        return null;
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = __('Error connecting to database');

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: " . $this->getMessage(),
        ]) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $this->getMessage(),
        ]);
    }
}
