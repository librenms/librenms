<?php
/**
 * MaximumExecutionTimeExceeded.php
 *
 * Show nice explanation if the user hits their configured PHP max_execution_time
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

namespace LibreNMS\Exceptions;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Exceptions\UpgradeableException;
use Symfony\Component\Debug\Exception\FatalErrorException;

class MaximumExecutionTimeExceeded extends \Exception implements UpgradeableException
{
    /**
     * Try to convert the given Exception to a FilePermissionsException
     *
     * @param \Exception $exception
     * @return static|null
     */
    public static function upgrade($exception)
    {
        // cannot write to storage directory
        if ($exception instanceof FatalErrorException &&
            Str::startsWith($exception->getMessage(), 'Maximum execution time of')) {
            return new static($exception->getMessage(), $exception->getCode(), $exception);
        }

        return null;
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = preg_match('/ (\d+) /', $this->message, $matches)
            ? trans_choice('exceptions.maximum_execution_time_exceeded.title', $matches[1], ['seconds' => $matches[1]])
            : $this->getMessage();

        $message = trans('exceptions.maximum_execution_time_exceeded.message');

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: $message",
        ]) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $message,
        ]);
    }
}
