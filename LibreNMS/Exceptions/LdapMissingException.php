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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

class LdapMissingException extends AuthenticationException
{
    public function __construct(
        string $message = 'PHP does not support LDAP, please install or enable the PHP LDAP extension',
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, false, $code, $previous);
    }

    /**
     * Render the exception into an HTTP or JSON response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render(\Illuminate\Http\Request $request)
    {
        $title = __('PHP LDAP support missing');
        $message = __($this->getMessage());

        return $request->wantsJson() ? response()->json([
            'status' => 'error',
            'message' => "$title: $message",
        ]) : response()->view('errors.generic', [
            'title' => $title,
            'content' => $message,
        ]);
    }
}
