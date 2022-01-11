<?php

namespace LibreNMS\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * @mixin \LibreNMS\Tests\TestCase
 */
trait MockGuzzleClient
{
    /**
     * @var MockHandler
     */
    private $guzzleMockHandler;

    /**
     * @var array
     */
    private $guzzleConfig;

    /**
     * @var array
     */
    private $guzzleHistory = [];

    /**
     * Create a Guzzle MockHandler and bind Client with the handler to the Laravel container
     *
     * @param  array  $queue  Sequential Responses to give to the client.
     * @param  array  $config  Guzzle config settings.
     */
    public function mockGuzzleClient(array $queue, array $config = []): MockHandler
    {
        $this->guzzleConfig = $config;
        $this->guzzleMockHandler = new MockHandler($queue);

        $this->app->bind(Client::class, function () {
            $handlerStack = HandlerStack::create($this->guzzleMockHandler);
            $handlerStack->push(Middleware::history($this->guzzleHistory));

            return new Client(array_merge($this->guzzleConfig, ['handler' => $handlerStack]));
        });

        return $this->guzzleMockHandler;
    }

    /**
     * Get the request and response history to inspect
     *
     * @return array
     */
    public function guzzleHistory(): array
    {
        return $this->guzzleHistory;
    }

    /**
     * Get the request history to inspect
     *
     * @return \GuzzleHttp\Psr7\Request[]
     */
    public function guzzleRequestHistory(): array
    {
        return array_column($this->guzzleHistory, 'request');
    }

    /**
     * Get the response history to inspect
     *
     * @return \GuzzleHttp\Psr7\Response[]
     */
    public function guzzleResponseHistory(): array
    {
        return array_column($this->guzzleHistory, 'response');
    }
}
