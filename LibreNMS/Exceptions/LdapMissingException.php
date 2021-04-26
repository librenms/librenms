<?php
/**
 * LdapMissingException.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

class LdapMissingException extends AuthenticationException
{
    private const DEFAULT_MESSAGE = 'PHP does not support LDAP, please install or enable the PHP LDAP extension';

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, false, $code, $previous);
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @return \Illuminate\Http\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = trans('exceptions.ldap_missing.title');
        $message = ($this->message == self::DEFAULT_MESSAGE) ? trans('exceptions.ldap_missing.message') : $this->getMessage();

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: $message",
        ]) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $message,
        ]);
    }
}
