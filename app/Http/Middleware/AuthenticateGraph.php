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

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

class AuthenticateGraph
{
    /** @var string[] */
    protected $auth = [
        \App\Http\Middleware\LegacyExternalAuth::class,
        \App\Http\Middleware\Authenticate::class,
        \App\Http\Middleware\VerifyTwoFactor::class,
        \App\Http\Middleware\LoadUserPreferences::class,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $relative
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next, $relative = null): Response
    {
        // if user is logged in, allow
        if (\Auth::check()) {
            return $next($request);
        }

        // bypass normal auth if signed
        if ($request->hasValidSignature($relative !== 'relative')) {
            return $next($request);
        }

        // bypass normal auth if ip is allowed (or all IPs)
        if ($this->isAllowed($request)) {
            return $next($request);
        }

        // unauthenticated, force login
        throw new AuthenticationException('Unauthenticated.');
    }

    protected function isAllowed(Request $request): bool
    {
        if (Config::get('allow_unauth_graphs', false)) {
            d_echo("Unauthorized graphs allowed\n");

            return true;
        }

        $ip = $request->getClientIp();
        try {
            $client_ip = IP::parse($ip);
            foreach (Config::get('allow_unauth_graphs_cidr', []) as $range) {
                if ($client_ip->inNetwork($range)) {
                    d_echo("Unauthorized graphs allowed from $range\n");

                    return true;
                }
            }
        } catch (InvalidIpException $e) {
            d_echo("Client IP ($ip) is invalid.\n");
        }

        return false;
    }
}
