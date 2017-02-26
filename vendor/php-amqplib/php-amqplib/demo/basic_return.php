<?php

require_once __DIR__.'/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// declare  exchange but don`t bind any queue
$channel->exchange_declare('hidden_exchange', 'topic');

$msg = new AMQPMessage("Hello World!");

echo " [x] Sent non-mandatory ...";
$channel->basic_publish($msg,
    'hidden_exchange',
    'rkey');
echo " done.\n";

$wait = true;

$return_listener = function ($reply_code, $reply_text,
    $exchange, $routing_key, $msg) use ($wait) {
    $GLOBALS['wait'] = false;

    echo "return: ",
    $reply_code, "\n",
    $reply_text, "\n",
    $exchange, "\n",
    $routing_key, "\n",
    $msg->body, "\n";
};

$channel->set_return_listener($return_listener);

echo " [x] Sent mandatory ... ";
$channel->basic_publish($msg,
    'hidden_exchange',
    'rkey',
    true );
echo " done.\n";

while ($wait) {
    $channel->wait();
}

$channel->close();
$connection->close();
