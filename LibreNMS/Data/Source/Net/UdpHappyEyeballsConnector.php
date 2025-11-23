<?php
/**
 * UdpHappyEyeballsConnector.php
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

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Net\Service\UdpCodec;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Promise\Promise;

class UdpHappyEyeballsConnector
{
    private $resolver;
    private $datagramFactory;
    private $connectionAttemptDelay = 0.250; // 250ms as per RFC 8305
    private $timeout = 5.0;
    private $responseTimeout = 2.0;

    public function __construct(?ResolverInterface $resolver = null, float $timeout = 5.0, float $responseTimeout = 2.0)
    {
        $this->resolver = $resolver;
        $this->datagramFactory = new \React\Datagram\Factory();
        $this->timeout = $timeout;
        $this->responseTimeout = $responseTimeout;
    }

    public function connect(string $hostname, int $port, UdpCodec $requestMessage): Promise
    {
        $deferred = new Deferred();

        // If it's already an IP, just connect directly
        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            return $this->connectToAddress($hostname, $port, $requestMessage);
        }

        // Resolve both IPv4 and IPv6 (handle missing records gracefully)
        $mode = LibrenmsConfig::get('dns.resolution_mode');

        $ipv4Promise = $this->resolver && $mode !== 'ipv6_only'
            ? $this->resolver->resolveAll($hostname, \React\Dns\Model\Message::TYPE_A)->then(null, function() { return []; })
            : \React\Promise\resolve([]);

        $ipv6Promise = $this->resolver && $mode !== 'ipv4_only'
            ? $this->resolver->resolveAll($hostname, \React\Dns\Model\Message::TYPE_AAAA)->then(null, function() { return []; })
            : \React\Promise\resolve([]);

        \React\Promise\all([$ipv4Promise, $ipv6Promise])->then(
            function ($results) use ($port, $requestMessage, $deferred, $hostname) {
                [$ipv4Addresses, $ipv6Addresses] = $results;

                // Make sure we have at least one address
                if (empty($ipv4Addresses) && empty($ipv6Addresses)) {
                    $deferred->reject(new \RuntimeException("DNS resolution failed: No addresses found for $hostname"));
                    return;
                }

                $this->attemptConnections($ipv4Addresses, $ipv6Addresses, $port, $requestMessage, $deferred);
            }
        );

        return $deferred->promise();
    }

    private function attemptConnections(array $ipv4Addresses, array $ipv6Addresses, int $port, UdpCodec $requestMessage, Deferred $deferred)
    {
        $attempts = [];
        $timers = [];
        $resolved = false;

        $cleanup = function () use (&$attempts, &$timers, &$resolved) {
            $resolved = true;
            foreach ($attempts as $attempt) {
                if (isset($attempt['timer'])) {
                    Loop::cancelTimer($attempt['timer']);
                }
            }
            foreach ($timers as $timer) {
                Loop::cancelTimer($timer);
            }
        };

        // Interleave addresses per RFC 8305
        $addresses = $this->interleaveAddresses($ipv6Addresses, $ipv4Addresses);

        if (empty($addresses)) {
            $deferred->reject(new \RuntimeException("No addresses to connect to"));
            return;
        }

        $attemptIndex = 0;
        $attemptConnection = function () use (&$attemptConnection, &$attempts, &$timers, &$resolved, &$attemptIndex, $addresses, $port, $requestMessage, $deferred, $cleanup) {
            if ($resolved || $attemptIndex >= count($addresses)) {
                return;
            }

            $address = $addresses[$attemptIndex];
            $index = $attemptIndex;
            $attemptIndex++;

            $this->connectToAddress($address, $port, $requestMessage)->then(
                function ($result) use ($deferred, $cleanup, &$resolved) {
                    if (!$resolved) {
                        $cleanup();
                        $deferred->resolve($result);
                    }
                },
                function ($error) use ($deferred, $cleanup, &$resolved, &$attempts, $index, $addresses) {
                    $attempts[$index]['error'] = $error;

                    // Check if all attempts have failed
                    if (!$resolved && count($attempts) === count($addresses)) {
                        $allFailed = true;
                        foreach ($attempts as $attempt) {
                            if (!isset($attempt['error'])) {
                                $allFailed = false;
                                break;
                            }
                        }
                        if ($allFailed) {
                            $cleanup();
                            $deferred->reject(new \RuntimeException("All connection attempts failed"));
                        }
                    }
                }
            );

            $attempts[$index] = ['address' => $address];

            // Schedule next attempt
            if ($attemptIndex < count($addresses)) {
                $timers[] = Loop::addTimer($this->connectionAttemptDelay, $attemptConnection);
            }
        };

        // Start first attempt immediately
        $attemptConnection();

        // Overall timeout
        $timers[] = Loop::addTimer($this->timeout, function () use ($deferred, $cleanup, &$resolved) {
            if (!$resolved) {
                $cleanup();
                $deferred->reject(new \RuntimeException("Connection timeout"));
            }
        });
    }

    private function connectToAddress(string $address, int $port, UdpCodec $requestMessage): Promise
    {
        $deferred = new Deferred();

        $uri = (strpos($address, ':') !== false) ? "[$address]:$port" : "$address:$port";

        $this->datagramFactory->createClient($uri)->then(
            function (\React\Datagram\Socket $client) use ($deferred, $requestMessage) {
                $timer = null;
                $resolved = false;

                $client->on('message', function ($message) use ($client, $deferred, $requestMessage, &$timer, &$resolved) {
                    if (!$resolved && $requestMessage->validateResponse($message)) {
                        $resolved = true;
                        if ($timer) {
                            Loop::cancelTimer($timer);
                        }
                        $deferred->resolve([
                            'socket' => $client,
                            'response' => $message,
                            'address' => $client->getRemoteAddress()
                        ]);
                    }
                });

                // Send request
                $client->send($requestMessage->getPayload());

                // Timeout for this specific connection
                $timer = Loop::addTimer($this->responseTimeout, function () use ($client, $deferred, &$resolved) {
                    if (!$resolved) {
                        $resolved = true;
                        $client->close();
                        $deferred->reject(new \RuntimeException("UDP request timeout"));
                    }
                });
            },
            function ($error) use ($deferred) {
                $deferred->reject($error);
            }
        );

        return $deferred->promise();
    }

    private function interleaveAddresses(array $ipv6, array $ipv4): array
    {
        $result = [];
        $maxCount = max(count($ipv6), count($ipv4));

        // respect the dns.resolution_mode setting
        [$first, $last] = match (LibrenmsConfig::get('dns.resolution_mode')) {
            'ipv4_only' => [$ipv4, []],
            'ipv6_only' => [$ipv6, []],
            'prefer_ipv4' => [$ipv4, $ipv6],
            default => [$ipv6, $ipv4],
        };


        for ($i = 0; $i < $maxCount; $i++) {
            if (isset($first[$i])) {
                $result[] = $first[$i];
            }
            if (isset($last[$i])) {
                $result[] = $last[$i];
            }
        }

        Log::debug("Resolved addresses: " . implode(", ", $result));

        return $result;
    }
}
