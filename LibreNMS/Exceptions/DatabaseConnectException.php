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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
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
     * @param \Exception $exception
     * @return static|null
     */
    public static function upgrade($exception)
    {
        // connect exception, convert to our standard connection exception
        return $exception instanceof QueryException && in_array($exception->getCode(), [1044, 1045, 2002]) ?
            new static(
                config('app.debug') ? $exception->getMessage() : $exception->getPrevious()->getMessage(),
                $exception->getCode(),
                $exception
            ) :
            null;
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @return \Illuminate\Http\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = trans('exceptions.database_connect.title');

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: " . $this->getMessage(),
        ]) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $this->getMessage(),
        ]);
    }
}
