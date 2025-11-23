<?php
/**
 * ConnectionFinder.php
 *
 * Implementation of RFC 6555 algorithm to find the best IP to connect to
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

use Fiber;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Net\Service\ServiceConnector;
use LibreNMS\Data\Source\Net\Service\UdpCodec;
use Socket;
use Throwable;

class ConnectionFinder {
    private const TIMEOUT_MS = 300; // Short timeout as per RFC 6555 recommendations (around 300ms)

    /** @var Connection[] */
    private array $connections = [];

    private ?string $connectedIp = null;

    /**
     * Attempts to connect to a list of IPs and returns the first one that replies.
     *
     * @param  array  $ips  Ordered list of IPv4 and IPv6 addresses.
     * @param  class-string<ServiceConnector>  $connectorClass
     * @param  UdpCodec  $codec
     * @return string|null The first successfully connected IP address, or null if all fail.
     * @throws Throwable
     */
    public function connect(array $ips, int $port, string $connectorClass, UdpCodec $codec): ?string {
        Log::info("Starting Happy Eyeballs connection attempt to " . count($ips) . " IPs.");
        $startTime = microtime(true);

        // initiate fibers and connectors for each IP
        foreach ($ips as $ip) {
            $connector = new $connectorClass($ip, $port, $codec);
            $connection = new Connection($connector, fn () => $this->fiberWorker($connector));
            $this->connections[$ip] = $connection;
            $connection->fiber->start();
        }

        // The Event Loop
        while (!empty($this->connections) && $this->connectedIp === null) {
            /** @var Socket[] $read **/
            $read = [];
            /** @var Socket[] $write **/
            $write = [];
            /** @var Socket[] $except **/
            $except = [];

            foreach ($this->connections as $connection) {
                $connection->connector->prepSocketSelect($read, $write, $except);
            }

            // Wait for a socket to become writable (connected) or for a timeout
            if (empty($read) && empty($write)) {
                $numChanged = 0;
            } else {
                $numChanged = socket_select($read, $write, $except, seconds: 0, microseconds: self::TIMEOUT_MS * 1000);
            }

            if ($numChanged === false) {
                Log::error("Socket select error: " . socket_strerror(socket_last_error()));
                break;
            } elseif ($numChanged > 0) {
                Log::debug("Socket select returned $numChanged sockets ready.");
                // Resume fibers for sockets that are ready (pass true to indicate readiness)
                foreach ([...$write, ...$read] as $readySocket) {
                    /** @var Socket $readySocket */
                    $readyConnection = $this->getConnection($readySocket);

                    if ($readyConnection->fiber->isSuspended()) {
                        try {
                            Log::debug("Resuming fiber for $readyConnection->connector");
                            $result = $readyConnection->fiber->resume(true);
                            Log::debug("Fiber resume result: " . var_export($result, true));
                            if ($result) {
                                $this->connectedIp ??= $readyConnection->ip;
                                $this->forgetConnection($readyConnection);
                            }
                        } catch (Throwable $e) {
                            Log::error("Exception in fiber resume: " . $e->getMessage());
                            $this->forgetConnection($readyConnection);
                        }
                    }
                }
            }
            // Check for overall time limit
            if ((microtime(true) - $startTime) * 1000 >= self::TIMEOUT_MS) {
                Log::debug("Global timeout reached. Terminating connection attempts.");
                break;
            }
        }

        // Close any remaining open sockets and resume their fibers to clean up (pass false to indicate not ready/timeout)
        foreach ($this->connections as $connection) {
            if ($connection->fiber->isSuspended()) {
                $result = $connection->fiber->resume(false);
                Log::debug("Fiber resume result: " . var_export($result, true));
            }
        }

        return $this->connectedIp;
    }

    protected function getConnection(string|Socket $search): Connection|null
    {
        if (is_string($search)) {
            return $this->connections[$search] ?? null;
        }

        return array_find($this->connections, fn ($connection) => $connection->connector->hasSocket($search));
    }

    protected function forgetConnection(string|Connection $ip): void
    {
        if ($ip instanceof Connection) {
            $ip = $ip->ip;
        }

        unset($this->connections[$ip]);
    }

    protected function fiberWorker(ServiceConnector $connector): void
    {
        $ip = $connector->getIp();
        try {
            $connector->connect();

            Log::debug("%BFiber for $ip started.%n", ['color' => true]);
            do {
                $ready = Fiber::suspend(); // Suspend the fiber to return control to the event loop
                Log::debug("%bFiber for $ip resumed with ready state: " . var_export($ready, true),
                    ['color' => true]);

                // This part runs after the fiber is resumed by the event loop (meaning it's connected or timed out)
                if ($ready === true) {
                    if ($connector->isServiceAvailable()) {
                        Log::info("Successfully connected to the first available IP: $ip");
                        Fiber::suspend($ip); // return IP to the caller
                        return;
                    }
                }
            } while ($ready === true);

        } catch (Throwable $e) {
            throw $e;
        } finally {
            $connector->close();
            $this->forgetConnection($ip);
        }
    }
}
