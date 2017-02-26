<?php

include(__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPConnection;

$exchange = 'router';
$queue = 'haqueue';
$specific_queue = 'specific-haqueue';

$consumer_tag = 'consumer';

$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

/*
    The following code is the same both in the consumer and the producer.
    In this way we are sure we always have a queue to consume from and an
        exchange where to publish messages.
*/

$ha_connection = array(
    'x-ha-policy' => array(
        'S', 'all'
    ),
);

$ha_specific_connection = array(
    'x-ha-policy' => array(
        'S', 'nodes'
    ),
    'x-ha-policy-params' => array(
        'A', array(
            'rabbit@' . HOST,
            'hare@' . HOST,
        ),
    ),
);

/*
    name: $queue
    passive: false
    durable: true // the queue will survive server restarts
    exclusive: false // the queue can be accessed in other channels
    auto_delete: false //the queue won't be deleted once the channel is closed.
    nowait: false // Doesn't wait on replies for certain things.
    parameters: array // How you send certain extra data to the queue declare
*/
$ch->queue_declare($queue, false, false, false, false, false, $ha_connection);
$ch->queue_declare($specific_queue, false, false, false, false, false, $ha_specific_connection);

/*
    name: $exchange
    type: direct
    passive: false
    durable: true // the exchange will survive server restarts
    auto_delete: false //the exchange won't be deleted once the channel is closed.
*/

$ch->exchange_declare($exchange, 'direct', false, true, false);

$ch->queue_bind($queue, $exchange);
$ch->queue_bind($specific_queue, $exchange);

function process_message($msg)
{
    echo "\n--------\n";
    echo $msg->body;
    echo "\n--------\n";

    $msg->delivery_info['channel']->
        basic_ack($msg->delivery_info['delivery_tag']);

    // Send a message with the string "quit" to cancel the consumer.
    if ($msg->body === 'quit') {
        $msg->delivery_info['channel']->
            basic_cancel($msg->delivery_info['consumer_tag']);
    }
}

/*
    queue: Queue from where to get the messages
    consumer_tag: Consumer identifier
    no_local: Don't receive messages published by this consumer.
    no_ack: Tells the server if the consumer will acknowledge the messages.
    exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
    nowait:
    callback: A PHP Callback
*/

$ch->basic_consume($queue, $consumer_tag, false, false, false, false, 'process_message');

function shutdown($ch, $conn)
{
    $ch->close();
    $conn->close();
}
register_shutdown_function('shutdown', $ch, $conn);

// Loop as long as the channel has callbacks registered
while (count($ch->callbacks)) {
    $ch->wait();
}
