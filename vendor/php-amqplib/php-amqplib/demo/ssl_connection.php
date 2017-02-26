<?php

include(__DIR__ . '/config.php');
use PhpAmqpLib\Connection\AMQPSSLConnection;

define('CERTS_PATH',
  '/git/rabbitmqinaction/av_scratchwork/openssl');

$ssl_options = array(
      'cafile' => CERTS_PATH . '/rmqca/cacert.pem',
      'local_cert' => CERTS_PATH . '/phpcert.pem',
      'verify_peer' => true
  );

$conn = new AMQPSSLConnection(HOST, PORT, USER, PASS, VHOST, $ssl_options);

function shutdown($conn)
{
    $conn->close();
}

register_shutdown_function('shutdown', $conn);

while (true) {}
