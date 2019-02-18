<?php
/**
 * DuskUnsafeException.php
 *
 * Dusk is installed and the application is in production
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

class DuskUnsafeException extends \Exception
{
    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = 'It is unsafe to run Dusk in production';
        $message = 'Run ./scripts/composer_wrapper.php install --no-dev to remove Dusk or if you are a developer set the appropriate APP_ENV';

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => "$title: $message",
            ]);
        }

        return response()->view('errors.generic', [
            'title' => $title,
            'content' => $message,
        ]);
    }
}
