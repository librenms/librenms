<?php

namespace PhpAmqpLib\Tests\Functional;

use PhpAmqpLib\Connection\AMQPSocketConnection;

class SocketPublishConsumeTest extends AbstractPublishConsumeTest
{
    protected function createConnection()
    {
        return new AMQPSocketConnection(HOST, PORT, USER, PASS, VHOST);
    }
}
