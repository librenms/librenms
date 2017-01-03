<?php

namespace PhpAmqpLib\Tests\Functional;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractPublishConsumeTest extends \PHPUnit_Framework_TestCase
{
    protected $exchange_name = 'test_exchange';
    protected $queue_name = null;

    public function setUp()
    {
        $this->conn = $this->createConnection();
        $this->ch = $this->conn->channel();

        $this->ch->exchange_declare($this->exchange_name, 'direct', false, false, false);
        list($this->queue_name,,) = $this->ch->queue_declare();
        $this->ch->queue_bind($this->queue_name, $this->exchange_name, $this->queue_name);
    }

    abstract protected function createConnection();

    public function testPublishConsume()
    {
        $this->msg_body = 'foo bar baz äëïöü';

        $msg = new AMQPMessage($this->msg_body, array(
            'content_type' => 'text/plain',
            'delivery_mode' => 1,
            'correlation_id' => 'my_correlation_id',
            'reply_to' => 'my_reply_to'
        ));

        $this->ch->basic_publish($msg, $this->exchange_name, $this->queue_name);

        $this->ch->basic_consume(
            $this->queue_name,
            getmypid(),
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

        //delivery tests
        $this->assertEquals(getmypid(), $delivery_info['consumer_tag']);
        $this->assertEquals($this->queue_name, $delivery_info['routing_key']);
        $this->assertEquals($this->exchange_name, $delivery_info['exchange']);
        $this->assertEquals(false, $delivery_info['redelivered']);

        //msg property tests
        $this->assertEquals('text/plain', $msg->get('content_type'));
        $this->assertEquals('my_correlation_id', $msg->get('correlation_id'));
        $this->assertEquals('my_reply_to', $msg->get('reply_to'));

        $this->setExpectedException('OutOfBoundsException');
        $msg->get('no_property');
    }

    public function tearDown()
    {
        $this->ch->exchange_delete($this->exchange_name);
        $this->ch->close();
        $this->conn->close();
    }
}
