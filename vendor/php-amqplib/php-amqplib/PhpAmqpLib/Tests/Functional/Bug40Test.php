<?php

namespace PhpAmqpLib\Tests\Functional;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Bug40Test extends \PHPUnit_Framework_TestCase
{
    protected $exchange_name = 'test_exchange';
    protected $queue_name1 = null;
    protected $queue_name2 = null;
    protected $q1msgs = 0;

    public function setUp()
    {
        $this->conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
        $this->ch = $this->conn->channel();
        $this->ch2 = $this->conn->channel();

        $this->ch->exchange_declare($this->exchange_name, 'direct', false, false, false);
        list($this->queue_name1,,) = $this->ch->queue_declare();
        list($this->queue_name2,,) = $this->ch->queue_declare();
        $this->ch->queue_bind($this->queue_name1, $this->exchange_name, $this->queue_name1);
        $this->ch->queue_bind($this->queue_name2, $this->exchange_name, $this->queue_name2);
    }

    public function testFrameOrder()
    {
        $msg = new AMQPMessage("test message");
        $this->ch->basic_publish($msg, $this->exchange_name, $this->queue_name1);
        $this->ch->basic_publish($msg, $this->exchange_name, $this->queue_name1);
        $this->ch->basic_publish($msg, $this->exchange_name, $this->queue_name2);

        $this->ch->basic_consume(
            $this->queue_name1,
            "",
            false,
            true,
            false,
            false,
            array($this, 'process_msg1')
        );

        while (count($this->ch->callbacks)) {
            $this->ch->wait();
        }
    }

    public function process_msg1($msg)
    {
        $delivery_info = $msg->delivery_info;
        $this->q1msgs++;

        if ($this->q1msgs < 2) {
            $this->ch2->basic_consume(
                $this->queue_name2,
                "",
                false,
                true,
                false,
                false,
                array($this, 'process_msg2')
            );
        }

        while (count($this->ch2->callbacks)) {
            $this->ch2->wait();
        }

        if ($this->q1msgs == 2) {
            $delivery_info['channel']->basic_cancel($delivery_info['consumer_tag']);
        }

    }

    public function process_msg2($msg)
    {
        $delivery_info = $msg->delivery_info;
        $delivery_info['channel']->basic_cancel($delivery_info['consumer_tag']);
    }

    public function tearDown()
    {
        $this->ch->exchange_delete($this->exchange_name);
        $this->ch->close();
        $this->conn->close();
    }
}
