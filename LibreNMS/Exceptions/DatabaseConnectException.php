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

class DatabaseConnectException extends \Exception
{
    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error connecting to database: ' . $this->getMessage(),
            ]);
        } else {
            return response()->view('errors.generic', [
                'title' => 'Error connecting to database.',
                'content' => $this->getMessage(),
            ]);
        }
    }
}
