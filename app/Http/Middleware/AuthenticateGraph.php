<?php

/*
 * AuthenticateGraph.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Middleware;

use App\Data\Graphing\GraphAccess;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticateGraph
{
    /** @var string[] */
    protected $auth = [
        LegacyExternalAuth::class,
        Authenticate::class,
        VerifyTwoFactor::class,
        LoadUserPreferences::class,
    ];

    public function __construct(private readonly GraphAccess $access)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $relative
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next, $relative = null): Response
    {
        // if user is logged in, allow
        if ($request->user() !== null) {
            return $next($request);
        }

        // bypass normal auth if signed, the signature vouches for the graph
        if ($request->hasValidSignature($relative !== 'relative')) {
            return $this->access->trusted(fn () => $next($request));
        }

        // bypass normal auth if ip is allowed (or all IPs)
        if ($this->access->allowsGuests($request)) {
            return $next($request);
        }

        // unauthenticated, force login
        throw new AuthenticationException('Unauthenticated.');
    }
}
