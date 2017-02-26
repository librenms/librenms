<?php

include(__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPConnection;

$exchange = 'router';
$queue = 'haqueue';
$specific_queue = 'specific-haqueue';

$consumer_tag = 'consumer';

$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

$ch->queue_declare('test11', false, true, false, false, false,
    array(
        "x-dead-letter-exchange" => array("S", "t_test1"),
        "x-message-ttl" => array("I", 15000),
        "x-expires" => array("I", 16000)
    ));

$ch->close();
$conn->close();
