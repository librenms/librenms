<?php

/**
 * FileWriteFailedException.php
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
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

use Throwable;

class FileWriteFailedException extends \Exception
{
    /**
     * @param  string  $file
     */
    public function __construct(protected $file_path, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Failed to write file: {$this->file_path}", $code, $previous);
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = trans('exceptions.file_write_failed.title');
        $message = trans('exceptions.file_write_failed.message', ['file' => $this->file_path]);

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: $message",
        ], 500) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $message,
        ], 500);
    }
}
