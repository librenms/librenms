<?php
/**
 * BaseConnector.php
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

namespace LibreNMS\Data\Source\Net\Service;

use Fiber;
use Illuminate\Support\Facades\Log;
use Socket;
use Stringable;
use Throwable;

abstract class BaseConnector implements ServiceConnector, Stringable
{
    private readonly Fiber $fiber;
    protected ?Socket $socket = null;
    /**
     * @var true
     */
    private bool $wait_read = false;
    private bool $wait_write = false;

    public function __construct(
        public readonly string $ip,
        public readonly int $port,
    )
    {
        $this->fiber = new Fiber(fn() => $this->fiberWorker());
    }

    final protected function createSocket(int $type, int $protocol): void
    {
        $domain = $this->isIpv6() ? AF_INET6 : AF_INET;
        $socket = socket_create($domain, $type, $protocol);

        if ($socket === false) {
            throw new \RuntimeException("Failed to create socket for $this->ip:$this->port ".socket_strerror(socket_last_error()));
        }

        $this->socket = $socket;

        // Set non-blocking mode for asynchronous connect
        socket_set_nonblock($this->socket);
    }

    protected function waitForRead(): void
    {
        $this->wait_read = true;
    }

    protected function waitForWrite(): void
    {
        $this->wait_write = true;
    }

    public function prepSocketSelect(array &$read, array &$write, array &$except): void
    {
        if ($this->wait_read && $this->socket) {
            $read[] = $this->socket;
        }
        if ($this->wait_write && $this->socket) {
            $write[] = $this->socket;
        }
    }

    final public function close(): void
    {
        if ($this->socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    final public function hasSocket(Socket $socket): bool
    {
        return $this->socket === $socket;
    }

    final public function getIp(): string
    {
        return $this->ip;
    }

    final public function getFiber(): Fiber
    {
        return $this->fiber;
    }

    protected function fiberWorker(): void
    {
        try {
            $this->connect();

            Log::debug("%BFiber for $this->ip started.%n", ['color' => true]);
            do {
                $ready = Fiber::suspend(); // Suspend the fiber to return control to the event loop
                Log::debug("%bFiber for $this->ip resumed with ready state: " . var_export($ready, true),
                    ['color' => true]);

                // This part runs after the fiber is resumed by the event loop (meaning it's connected or timed out)
                if ($ready === true) {
                    if ($this->isServiceAvailable()) {
                        Log::info("Successfully connected to the first available IP: $this->ip");
                        Fiber::suspend($this->ip);
                        return;
                    }
                }
            } while ($ready === true);

        } catch (Throwable $e) {
            throw $e;
        } finally {
            $this->close();
        }
    }

    final public function __toString(): string
    {
        if ($this->isIpv6()) {
            return "[{$this->ip}]:$this->port";
        }

        return "$this->ip:$this->port";
    }

    private function isIpv6(): bool
    {
        return str_contains($this->ip, ':');
    }
}
