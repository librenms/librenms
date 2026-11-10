<?php

/**
 * GraphAccess.php
 *
 * Decides who may view graphs: users by the permission to view the graphed entity,
 * guests only when anonymous graphs are permitted or a caller vouched for the request
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Graphing;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

#[Scoped]
class GraphAccess
{
    private bool $trusted = false;

    /**
     * Run the callback with guests allowed to view graphs.
     * Only for callers that verified access themselves, such as a signed url or server side rendering of alerts.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function trusted(Closure $callback): mixed
    {
        $previous = $this->trusted;
        $this->trusted = true;

        try {
            return $callback();
        } finally {
            $this->trusted = $previous;
        }
    }

    /**
     * Can the current user view graphs of the model.
     * Guests may when anonymous graphs are allowed, users need permission to view the model.
     */
    public function canView(Model $model): bool
    {
        if (Auth::guest()) {
            return $this->allowsGuests();
        }

        return Gate::allows('view', $model);
    }

    /**
     * There is no user and anonymous graphs are allowed.
     * The starting authorization of legacy graph auth files, which check permissions when it is false.
     */
    public function guestAllowed(): bool
    {
        return Auth::guest() && $this->allowsGuests();
    }

    /**
     * Guests may view graphs: trusted by the caller, allow_unauth_graphs, or the client ip is in allow_unauth_graphs_cidr
     */
    public function allowsGuests(?Request $request = null): bool
    {
        if ($this->trusted) {
            return true;
        }

        if (LibrenmsConfig::get('allow_unauth_graphs', false)) {
            d_echo("Unauthorized graphs allowed\n");

            return true;
        }

        $ip = ($request ?? request())->getClientIp();
        if ($ip === null) {
            return false;
        }

        try {
            $client_ip = IP::parse($ip);
            foreach (LibrenmsConfig::get('allow_unauth_graphs_cidr', []) as $range) {
                if ($client_ip->inNetwork($range)) {
                    d_echo("Unauthorized graphs allowed from $range\n");

                    return true;
                }
            }
        } catch (InvalidIpException) {
            d_echo("Client IP ($ip) is invalid.\n");
        }

        return false;
    }
}
