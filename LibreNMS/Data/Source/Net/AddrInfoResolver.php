<?php
/**
 * AddrInfoResolver.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Net;

use React\Dns\Model\Message;
use React\Dns\Model\Record;
use React\Dns\Query\ExecutorInterface;
use React\Dns\Query\Query;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class AddrInfoResolver implements ExecutorInterface
{
    private array $cache = [];

    /**
     * @return PromiseInterface<Message>
     */
    public function query(Query $query): PromiseInterface
    {
        // Promise constructor needs a function with $resolve and $reject parameters
        return new Promise(function ($resolve, $reject) use ($query): void {
            try {
                $cacheKey = $query->describe();

                if (!array_key_exists($cacheKey, $this->cache)) {
                    $hints = match($query->type) {
                        Message::TYPE_A => ['ai_family' => AF_INET, 'ai_socktype' => SOCK_DGRAM],
                        Message::TYPE_AAAA => ['ai_family' => AF_INET6, 'ai_socktype' => SOCK_DGRAM],
                        default => ['ai_socktype' => SOCK_DGRAM],
                    };

                    $info = socket_addrinfo_lookup($query->name, null, $hints);
                    $this->cache[$cacheKey] = $info === false ? [] : array_map(socket_addrinfo_explain(...), $info);
                }

                $answers = array_map(function ($info) use ($query) {
                    if ($info['ai_family'] === AF_INET6) {
                        return new Record($query->name, Message::TYPE_AAAA, Message::CLASS_IN, 0, $info['ai_addr']['sin6_addr']);
                    }

                    return new Record($query->name, Message::TYPE_A, Message::CLASS_IN, 0, $info['ai_addr']['sin_addr']);
                }, $this->cache[$cacheKey]);

                $message = Message::createResponseWithAnswersForQuery($query, $answers);

                // Call $resolve with the result
                $resolve($message);

            } catch (\Throwable $e) {
                // Call $reject if there's an error
                $reject($e);
            }
        });
    }
}
