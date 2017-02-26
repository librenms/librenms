<?php

// Only one consumer per queue is allowed.
// Set $queue name to test exclusiveness

include(__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPConnection;

$exchange = 'fanout_exclusive_example_exchange';
$queue = '';    // if empty let RabbitMQ create a queue name
                // set a queue name and run multiple instances
                // to test exclusiveness
$consumer_tag = 'consumer'. getmypid ();

$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

/*
    name: $queue    // should be unique in fanout exchange. Let RabbitMQ create
                    // a queue name for us
    passive: false  // don't check if a queue with the same name exists
    durable: false // the queue will not survive server restarts
    exclusive: true // the queue can not be accessed by other channels
    auto_delete: true //the queue will be deleted once the channel is closed.
*/
list($queue_name, ,)=$ch->queue_declare($queue, false, false, true, true);

/*
    name: $exchange
    type: direct
    passive: false // don't check if a exchange with the same name exists
    durable: false // the exchange will not survive server restarts
    auto_delete: true //the exchange will be deleted once the channel is closed.
*/

$ch->exchange_declare($exchange, 'fanout', false, false, true);

$ch->queue_bind($queue_name, $exchange);

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
    nowait: don't wait for a server response. In case of error the server will raise a channel
            exception
    callback: A PHP Callback
*/

$ch->basic_consume($queue_name, $consumer_tag, false, false, true, false, 'process_message');

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
