<?php

namespace PhpAmqpLib\Tests\Functional;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FileTransferTest extends \PHPUnit_Framework_TestCase
{
    protected $exchange_name = 'test_exchange';
    protected $queue_name = null;

    public function setUp()
    {
        $this->conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
        $this->ch = $this->conn->channel();

        $this->ch->exchange_declare($this->exchange_name, 'direct', false, false, false);
        list($this->queue_name,,) = $this->ch->queue_declare();
        $this->ch->queue_bind($this->queue_name, $this->exchange_name, $this->queue_name);
    }

    public function testSendFile()
    {
        $this->msg_body = file_get_contents(__DIR__.'/fixtures/data_1mb.bin');

        $msg = new AMQPMessage($this->msg_body, array('delivery_mode' => 1));

        $this->ch->basic_publish($msg, $this->exchange_name, $this->queue_name);

        $this->ch->basic_consume(
            $this->queue_name,
            '',
            false,
            false,
            false,
            false,
            array($this, 'process_msg')
        );

        while (count($this->ch->callbacks)) {
            $this->ch->wait();
        }
    }

    public function process_msg($msg)
    {
        $delivery_info = $msg->delivery_info;

        $delivery_info['channel']->basic_ack($delivery_info['delivery_tag']);
        $delivery_info['channel']->basic_cancel($delivery_info['consumer_tag']);

        $this->assertEquals($this->msg_body, $msg->body);
    }

    public function tearDown()
    {
        $this->ch->exchange_delete($this->exchange_name);
        $this->ch->close();
        $this->conn->close();
    }
}
